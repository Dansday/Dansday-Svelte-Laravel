import { json } from '@sveltejs/kit';
import { env } from '$env/dynamic/private';
import { embedAllContent, getEmbeddingConfig } from '$lib/server/embedding';
import type { RequestHandler } from './$types';

export const POST: RequestHandler = async ({ request }) => {
	const authHeader = request.headers.get('authorization');
	const expectedToken = env.EMBEDDING_SECRET ?? '';

	if (!expectedToken || authHeader !== `Bearer ${expectedToken}`) {
		return json({ error: 'Unauthorized' }, { status: 401 });
	}

	const config = await getEmbeddingConfig();
	if (!config) {
		return json({ error: 'Embedding not configured. Set embedding_url, embedding_key, and embedding_model in admin.' }, { status: 400 });
	}

	const result = await embedAllContent();
	return json(result);
};

export const GET: RequestHandler = async ({ request }) => {
	const authHeader = request.headers.get('authorization');
	const expectedToken = env.EMBEDDING_SECRET ?? '';

	if (!expectedToken || authHeader !== `Bearer ${expectedToken}`) {
		return json({ error: 'Unauthorized' }, { status: 401 });
	}

	const config = await getEmbeddingConfig();
	return json({
		configured: config !== null,
		url: config?.url ?? null,
		model: config?.model ?? null
	});
};
