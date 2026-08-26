export const MAX_ARTICLES = 12;
export const MAX_PROJECTS = 12;
export const MAX_ACTIVITY = 20;
export const MAX_ABOUT_ROWS = 25;
export const MAX_TOP_REPOS = 10;

export const MAX_EXCERPT_CHARS = 1400;
export const MAX_EXCERPT_BUDGET = 9000;
export const MAX_READ_CHARS = 14000;

export const DEFAULT_ARTICLES = 6;
export const DEFAULT_PROJECTS = 6;
export const DEFAULT_ACTIVITY = 10;

export type Section = Record<string, unknown>;

export function fail(reason: string, extra: Record<string, unknown> = {}) {
	return { ok: false as const, reason, ...extra };
}

const ENTITIES: Record<string, string> = {
	amp: '&',
	lt: '<',
	gt: '>',
	quot: '"',
	apos: "'",
	nbsp: ' ',
	mdash: '\u2014',
	ndash: '\u2013',
	minus: '\u2212',
	hellip: '\u2026',
	rsquo: '\u2019',
	lsquo: '\u2018',
	rdquo: '\u201d',
	ldquo: '\u201c'
};

export function stripHtml(html: unknown): string {
	return String(html ?? '')
		.replace(/<(?:br|\/p|\/h[1-6]|\/li|\/pre|\/blockquote)[^>]*>/gi, '\n')
		.replace(/<[^>]*>/g, '')
		.replace(/&#(\d+);/g, (_, code) => String.fromCodePoint(Number(code)))
		.replace(/&#x([0-9a-f]+);/gi, (_, code) => String.fromCodePoint(parseInt(code, 16)))
		.replace(/&([a-z]+);/gi, (whole, name) => ENTITIES[String(name).toLowerCase()] ?? whole)
		.replace(/[ \t]+/g, ' ')
		.replace(/ ?\n ?/g, '\n')
		.replace(/\n{3,}/g, '\n\n')
		.trim();
}

export function excerptAround(text: string, needle: string, max: number): string {
	if (max <= 0) return '';
	if (text.length <= max) return text;

	const words = needle
		.split(/[\s\-]+/)
		.map((word) => word.trim().toLowerCase())
		.filter((word) => word.length > 2);

	const lower = text.toLowerCase();
	let at = -1;
	for (const word of words) {
		const found = lower.indexOf(word);
		if (found !== -1 && (at === -1 || found < at)) at = found;
	}

	if (at === -1) return text.slice(0, max).trimEnd() + '\u2026';

	const start = Math.max(0, at - Math.floor(max / 3));
	const end = Math.min(text.length, start + max);
	return (start > 0 ? '\u2026' : '') + text.slice(start, end).trim() + (end < text.length ? '\u2026' : '');
}

export function sectionOn(section: Section, key: string): boolean {
	return section[key] !== false && section[key] !== 0;
}

export function clampLimit(raw: unknown, max: number, fallback: number): number {
	const n = Number(raw);
	if (!Number.isFinite(n) || n <= 0) return fallback;
	return Math.min(Math.floor(n), max);
}

export function buildDateFilter(args: Record<string, any>): { clause: string; params: (string | number)[] } {
	const conditions: string[] = [];
	const params: (string | number)[] = [];
	if (args?.startDate) {
		conditions.push('created_at >= ?');
		params.push(String(args.startDate));
	}
	if (args?.endDate) {
		conditions.push('created_at <= ?');
		params.push(String(args.endDate) + ' 23:59:59');
	}
	return { clause: conditions.length > 0 ? ' AND ' + conditions.join(' AND ') : '', params };
}

export function keyword(args: Record<string, any>): string {
	return String(args?.keyword ?? '').trim();
}

export function nextStep(showing: number, total: number, subject: string): string {
	if (total > showing) {
		return `Answer from these ${showing} of ${total} ${subject}, they are the closest matches. If the user wants more, call this again with a higher limit or a narrower keyword — do not guess at the ones you cannot see.`;
	}
	return `These are all the ${subject} that match. Answer from them and do not call this tool again with the same arguments.`;
}
