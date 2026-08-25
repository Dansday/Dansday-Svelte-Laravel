export const MAX_ARTICLES = 12;
export const MAX_PROJECTS = 12;
export const MAX_ACTIVITY = 20;
export const MAX_ABOUT_ROWS = 25;
export const MAX_TOP_REPOS = 10;

export const DEFAULT_ARTICLES = 6;
export const DEFAULT_PROJECTS = 6;
export const DEFAULT_ACTIVITY = 10;

export type Section = Record<string, unknown>;

export function fail(reason: string, extra: Record<string, unknown> = {}) {
	return { ok: false as const, reason, ...extra };
}

export function stripHtml(html: unknown): string {
	return String(html ?? '')
		.replace(/<[^>]*>/g, '')
		.replace(/&[a-z]+;/gi, ' ')
		.replace(/\s+/g, ' ')
		.trim();
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
