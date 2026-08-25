import type OpenAI from 'openai';
import { query } from '../db';

const SEMANTIC_TOP_N = 50;
const SEMANTIC_THRESHOLD = 0.5;
const RRF_K = 40;
const SEMANTIC_WEIGHT = 2.0;
const MAX_FT_ROWS = 200;
const MAX_QUERY_WORDS = 30;

interface CachedEmbedding {
	table_name: string;
	row_id: number;
	vector: number[];
	norm: number;
}

export interface ClientOpts {
	client: OpenAI | null;
	model: string;
}

export interface Retrieval {
	embedding: ClientOpts;
	llm: ClientOpts;
	embeddings: Promise<CachedEmbedding[]> | null;
	expansions: Map<string, Promise<string[]>>;
	vectors: Map<string, Promise<number[] | null>>;
	scores: Map<string, Promise<Map<number, number>>>;
}

export function createRetrieval(embedding: ClientOpts, llm: ClientOpts): Retrieval {
	return { embedding, llm, embeddings: null, expansions: new Map(), vectors: new Map(), scores: new Map() };
}

function vectorNorm(v: number[]): number {
	let sum = 0;
	for (let i = 0; i < v.length; i++) sum += v[i] * v[i];
	return Math.sqrt(sum);
}

function cosineSimilarityWithNorm(a: number[], b: number[], normB: number): number {
	let dot = 0;
	let normA = 0;
	for (let i = 0; i < a.length; i++) {
		dot += a[i] * b[i];
		normA += a[i] * a[i];
	}
	const denom = Math.sqrt(normA) * normB;
	return denom === 0 ? 0 : dot / denom;
}

function loadEmbeddings(r: Retrieval): Promise<CachedEmbedding[]> {
	if (!r.embeddings) {
		r.embeddings = query<{ table_name: string; row_id: number; vector: string }>('SELECT table_name, row_id, vector FROM embeddings')
			.then((rows) =>
				rows.map((row) => {
					const vector = JSON.parse(row.vector) as number[];
					return { table_name: row.table_name, row_id: row.row_id, vector, norm: vectorNorm(vector) };
				})
			)
			.catch(() => []);
	}
	return r.embeddings;
}

function expandQuery(r: Retrieval, text: string): Promise<string[]> {
	const cached = r.expansions.get(text);
	if (cached) return cached;

	const pending = (async () => {
		if (!r.llm.client || !text) return [text];
		try {
			const res = await r.llm.client.chat.completions.create({
				model: r.llm.model,
				messages: [
					{
						role: 'user',
						content: `Generate 3-5 short search query variants for a portfolio site search. Return only the variants as a JSON array of strings, nothing else.\nOriginal query: "${text}"`
					}
				],
				max_tokens: 100
			});
			const content = res.choices?.[0]?.message?.content?.trim() ?? '';
			const match = content.match(/\[[\s\S]*\]/);
			if (match) {
				const parsed = JSON.parse(match[0]);
				if (Array.isArray(parsed)) return [text, ...parsed.filter((s: unknown): s is string => typeof s === 'string')];
			}
		} catch {}
		return [text];
	})();

	r.expansions.set(text, pending);
	return pending;
}

export async function fullTextQuery(r: Retrieval, text: string): Promise<string> {
	const variants = await expandQuery(r, text);
	const words = [
		...new Set(
			variants
				.flatMap((v) => v.split(/[\s\-]+/))
				.map((w) => w.replace(/[+><~*"@()]/g, ''))
				.filter((w) => w.length > 0)
				.slice(0, MAX_QUERY_WORDS)
		)
	];
	return words.map((w) => `${w}*`).join(' ');
}

function queryVector(r: Retrieval, text: string): Promise<number[] | null> {
	const cached = r.vectors.get(text);
	if (cached) return cached;

	const pending = (async () => {
		if (!r.embedding.client) return null;
		try {
			const hyde = await r.embedding.client.chat.completions
				.create({
					model: r.embedding.model,
					messages: [{ role: 'user', content: `Write a short portfolio item (1–2 sentences) that would be a perfect answer to: "${text}"` }],
					max_tokens: 80
				})
				.catch(() => null);
			const hydeText = hyde?.choices?.[0]?.message?.content?.trim() ?? text;
			const response = await r.embedding.client.embeddings.create({ model: r.embedding.model, input: hydeText });
			return response.data[0]?.embedding ?? null;
		} catch {
			return null;
		}
	})();

	r.vectors.set(text, pending);
	return pending;
}

function semanticScores(r: Retrieval, text: string, table: string): Promise<Map<number, number>> {
	const cacheKey = `${table}|${text}`;
	const cached = r.scores.get(cacheKey);
	if (cached) return cached;

	const pending = (async () => {
		const scores = new Map<number, number>();
		const vector = await queryVector(r, text);
		if (!vector) return scores;

		const best = new Map<number, number>();
		for (const row of await loadEmbeddings(r)) {
			if (row.table_name !== table) continue;
			const similarity = cosineSimilarityWithNorm(vector, row.vector, row.norm);
			if (similarity < SEMANTIC_THRESHOLD) continue;
			const existing = best.get(row.row_id);
			if (existing === undefined || similarity > existing) best.set(row.row_id, similarity);
		}

		[...best.entries()]
			.sort((a, b) => b[1] - a[1])
			.slice(0, SEMANTIC_TOP_N)
			.forEach(([rowId], index) => scores.set(rowId, 1 / (RRF_K + index + 1)));

		return scores;
	})();

	r.scores.set(cacheKey, pending);
	return pending;
}

export interface HybridOpts {
	table: string;
	fields: string;
	where: string;
	ftFields: string[];
	keyword: string;
	dateClause: string;
	dateParams: (string | number)[];
	extraClause?: string;
	extraParams?: (string | number)[];
	limit: number;
}

export async function hybridSearch<T extends { id: number }>(r: Retrieval, opts: HybridOpts): Promise<{ rows: T[]; total: number }> {
	const base = `FROM ${opts.table} WHERE ${opts.where}${opts.extraClause ?? ''}${opts.dateClause}`;
	const baseParams = [...(opts.extraParams ?? []), ...opts.dateParams];

	if (!opts.keyword) {
		const [counted, rows] = await Promise.all([
			query<{ cnt: number }>(`SELECT COUNT(*) as cnt ${base}`, baseParams),
			query<T>(`SELECT ${opts.fields} ${base} ORDER BY created_at DESC LIMIT ?`, [...baseParams, opts.limit])
		]);
		return { rows, total: Number(counted[0]?.cnt ?? rows.length) };
	}

	const ft = await fullTextQuery(r, opts.keyword);
	const matchExpr = `MATCH(${opts.ftFields.join(', ')}) AGAINST(? IN BOOLEAN MODE)`;

	const matched = ft
		? await query<T & { relevance?: number }>(`SELECT ${opts.fields}, ${matchExpr} AS relevance ${base} AND ${matchExpr} ORDER BY relevance DESC LIMIT ?`, [
				ft,
				...baseParams,
				ft,
				MAX_FT_ROWS
			])
		: [];

	const scores = await semanticScores(r, opts.keyword, opts.table);
	const seen = new Set(matched.map((row) => row.id));
	const missing = [...scores.keys()].filter((id) => !seen.has(id));

	let extra: T[] = [];
	if (missing.length > 0) {
		const placeholders = missing.map(() => '?').join(',');
		extra = await query<T>(`SELECT ${opts.fields} ${base} AND id IN (${placeholders})`, [...baseParams, ...missing]);
	}

	const ranked = [...matched, ...extra].map((row, index) => {
		const bm25 = (row as T & { relevance?: number }).relevance ? 1 / (RRF_K + index + 1) : 0;
		return { row: row as T, score: bm25 + SEMANTIC_WEIGHT * (scores.get(row.id) ?? 0) };
	});
	ranked.sort((a, b) => b.score - a.score);

	return { rows: ranked.slice(0, opts.limit).map((entry) => entry.row), total: ranked.length };
}
