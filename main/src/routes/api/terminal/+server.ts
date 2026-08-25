import { json } from '@sveltejs/kit';
import { fetchGeneral, fetchSection } from '$lib/server/data';
import { createRetrieval } from '$lib/server/terminal/search';
import { buildDataNote, buildTerminalTools, runTerminalTool } from '$lib/server/terminal/tools';
import OpenAI from 'openai';
import type { RequestHandler } from './$types';
import loggerProvider from '../../../../otel/logger.js';

const MAX_RECENT = 10;
const MAX_TOOL_ITERATIONS = 8;

function normalizeBaseUrl(rawUrl: string, suffix: string): string {
	const trimmed = rawUrl.trim().replace(/\/+$/, '');
	return trimmed.endsWith(suffix) ? trimmed.slice(0, -suffix.length) : trimmed;
}

function buildCompletionParams(model: string, reasoning: string) {
	const useThinking = reasoning !== 'none';
	const modelLower = model.toLowerCase();
	const isGemini = modelLower.includes('gemini');

	const thinkingKwargs = (() => {
		if (!useThinking) return {};
		if (modelLower.includes('glm')) return { chat_template_kwargs: { enable_thinking: true, clear_thinking: false } };
		if (modelLower.includes('nemotron')) return { chat_template_kwargs: { enable_thinking: true }, reasoning_budget: -1 };
		if (modelLower.includes('qwen')) return { chat_template_kwargs: { enable_thinking: true } };
		if (modelLower.includes('deepseek') || modelLower.includes('kimi')) return { chat_template_kwargs: { thinking: true } };
		return {};
	})();

	return {
		model,
		...(useThinking ? { reasoning_effort: (isGemini && reasoning === 'xhigh' ? 'high' : reasoning) as any } : {}),
		...(!isGemini ? { frequency_penalty: 1.2 } : {}),
		...thinkingKwargs
	};
}

async function summarizeOlder(openai: OpenAI, model: string, older: any[]): Promise<string> {
	const conversationText = older
		.filter((m) => (m.role === 'user' || m.role === 'assistant') && m.content)
		.map((m) => `${m.role}: ${typeof m.content === 'string' ? m.content : JSON.stringify(m.content)}`)
		.join('\n');

	if (!conversationText.trim()) return '';

	const completion = await openai.chat.completions.create({
		model,
		messages: [
			{
				role: 'system',
				content:
					'Summarize this conversation history in 2-4 concise sentences. Focus on what was discussed, what questions were asked, and what answers were given. Keep it factual and brief.'
			},
			{ role: 'user', content: conversationText }
		]
	});

	return completion.choices?.[0]?.message?.content?.trim() ?? '';
}

export const POST: RequestHandler = async ({ request }) => {
	try {
		const { messages } = await request.json();

		if (!Array.isArray(messages) || messages.length === 0) {
			return json({ error: 'Invalid messages array' }, { status: 400 });
		}

		const generalData = await fetchGeneral();
		const openaiUrl = (generalData.ai_url as string | null)?.trim() ?? '';
		const openaiKey = (generalData.ai_key as string | null)?.trim() ?? '';
		const openaiModel = (generalData.ai_model as string | null)?.trim() ?? '';
		const terminalPrompt = (generalData.ai_terminal_prompt as string | null)?.trim() ?? '';
		const terminalReasoning = (generalData.ai_terminal_reasoning as string | null) ?? 'none';

		if (!openaiUrl || !openaiKey || !openaiModel) {
			return json({
				response: 'Error: AI Terminal is not configured. Please set the OpenAI URL, Key, and Model in the admin settings.'
			});
		}

		const openai = new OpenAI({ baseURL: normalizeBaseUrl(openaiUrl, '/chat/completions'), apiKey: openaiKey });

		const embeddingUrl = (generalData.embedding_url as string | null)?.trim() ?? '';
		const embeddingKey = (generalData.embedding_key as string | null)?.trim() ?? '';
		const embeddingModel = (generalData.embedding_model as string | null)?.trim() ?? '';
		const embeddingClient =
			embeddingUrl && embeddingKey && embeddingModel ? new OpenAI({ baseURL: normalizeBaseUrl(embeddingUrl, '/embeddings'), apiKey: embeddingKey }) : null;

		const retrieval = createRetrieval({ client: embeddingClient, model: embeddingModel }, { client: openai, model: openaiModel });

		const section = await fetchSection();
		const tools = buildTerminalTools(section);

		const today = new Date().toISOString().slice(0, 10);
		const dataNote = buildDataNote(tools);
		const systemContent = [terminalPrompt.replaceAll('{{today}}', today), dataNote].filter(Boolean).join('\n\n');

		let conversationMessages: OpenAI.Chat.ChatCompletionMessageParam[] = messages;
		if (messages.length > MAX_RECENT) {
			const summary = await summarizeOlder(openai, openaiModel, messages.slice(0, -MAX_RECENT));
			conversationMessages = [
				...(summary
					? [
							{ role: 'user' as const, content: `[Previous conversation summary: ${summary}]` },
							{ role: 'assistant' as const, content: 'Understood.' }
						]
					: []),
				...messages.slice(-MAX_RECENT)
			];
		}

		conversationMessages = conversationMessages.filter((m) => m.role !== 'system');

		const loop: OpenAI.Chat.ChatCompletionMessageParam[] = [
			...(systemContent ? [{ role: 'system' as const, content: systemContent }] : []),
			...conversationMessages
		];

		const completionParams = buildCompletionParams(openaiModel, terminalReasoning);
		const toolResultCache = new Map<string, string>();

		let fullResponse = '';
		for (let i = 0; i < MAX_TOOL_ITERATIONS; i++) {
			const offerTools = tools.length > 0 && i < MAX_TOOL_ITERATIONS - 1;

			const completion = await openai.chat.completions.create({
				...completionParams,
				...(offerTools ? { tools, tool_choice: 'auto' as const } : {}),
				messages: loop
			} as any);

			const message = completion.choices?.[0]?.message;
			if (!message) break;

			if (typeof message.content === 'string' && message.content.trim()) fullResponse = message.content;

			if (!message.tool_calls || message.tool_calls.length === 0) break;

			loop.push(message);

			for (const call of message.tool_calls as any[]) {
				const cacheKey = `${call.function.name}:${call.function.arguments ?? ''}`;
				if (!toolResultCache.has(cacheKey)) {
					let args: Record<string, any> = {};
					try {
						args = call.function.arguments ? JSON.parse(call.function.arguments) : {};
					} catch {
						args = {};
					}
					toolResultCache.set(cacheKey, await runTerminalTool(call.function.name, args, section, retrieval));
				}
				loop.push({ role: 'tool', tool_call_id: call.id, content: toolResultCache.get(cacheKey) as string });
			}
		}

		if (loggerProvider) {
			const logger = loggerProvider.getLogger('terminal');
			const userMessage = messages[messages.length - 1]?.content ?? '';
			const cleaned = fullResponse
				.replace(/<reasoning>[\s\S]*?<\/reasoning>/gi, '')
				.replace(/<think>[\s\S]*?<\/think>/gi, '')
				.replace(/\(no output\)\s*/g, '')
				.trim();
			logger.emit({
				body: 'AI Terminal Interaction',
				attributes: {
					'terminal.user_input': userMessage,
					'terminal.system_prompt': systemContent,
					'terminal.ai_response': cleaned
				}
			});
		}

		return json({ response: fullResponse });
	} catch (error: any) {
		console.error('Terminal API Error:', error);
		return json({ response: `Error: ${error.message}` });
	}
};
