import { env } from '$env/dynamic/private';
import type OpenAI from 'openai';
import { encode as toToon } from '@toon-format/toon';
import { fetchGeneral, fetchHome } from '../data';
import { query } from '../db';
import { hybridSearch, type Retrieval } from './search';
import {
	DEFAULT_ACTIVITY,
	DEFAULT_ARTICLES,
	DEFAULT_PROJECTS,
	MAX_ABOUT_ROWS,
	MAX_ACTIVITY,
	MAX_ARTICLES,
	MAX_PROJECTS,
	MAX_TOP_REPOS,
	buildDateFilter,
	clampLimit,
	fail,
	keyword,
	nextStep,
	sectionOn,
	stripHtml,
	type Section
} from './shared';

const ACTIVITY_TYPES = ['commit', 'pr', 'review', 'issue'] as const;
const COUNT_TYPES = ['article', 'project', 'skill', 'experience', 'service', 'testimonial', 'commit', 'pr', 'review', 'issue'] as const;

const ARTICLES_DESCRIPTION =
	'Search the articles published on this site — the writing, the posts, the notes. Returns the matching titles with their short summary, category and publish date, best match first. Use it for "what has he written about X", "any posts on Y", "show me the latest articles". This reads articles only: for shipped work call search_projects, for the day-to-day GitHub trail call search_activity, and if the question is only "how many" call count instead.';

const PROJECTS_DESCRIPTION =
	'Search the projects in this portfolio — the shipped work, each with its category and short summary. Use it for "what has he built", "any projects using X", "show me his work". This reads projects only: for the writing call search_articles, for commits and pull requests call search_activity, and if the question is only "how many" call count instead.';

const ACTIVITY_DESCRIPTION =
	'Search the GitHub activity trail: the individual commits, pull requests, reviews and issues, each with its repo, lines added and removed, and date. Use it for "what has he been working on lately", "any PRs about X", "what did he do in June". This returns individual events — if the question is "how many", "how active is he" or "which repo does he work on most", call get_activity_stats instead, which answers that in a few numbers rather than a long list.';

const ACTIVITY_STATS_DESCRIPTION =
	'The totals behind the GitHub activity: the overall count, the split across commits, pull requests, reviews and issues, and the busiest repos. Use it for "how many commits", "how active is he", "which repo does he work on most". Answer from these numbers instead of counting events by hand — for the events themselves call search_activity.';

const ABOUT_DESCRIPTION =
	'The about page of the person behind this site: the skills, the work and education history, the services offered, and the client testimonials. Pass a topic to read just one of those, leave it out to read all of them at once. Use it for "what can he do", "where has he worked", "what does he offer", "what do clients say", "tell me about him". This is the personal background — for the work itself call search_projects or search_articles.';

const SITE_DESCRIPTION =
	'Who this site belongs to and how to reach them: the site title and tagline, the site URL, and the social and contact links. Use it for "who is this", "how do I contact him", "what is his GitHub", "does he have a LinkedIn". Call it only when the question is actually about contact or identity, not as a warm-up for every answer.';

const COUNT_DESCRIPTION =
	'How many items exist of each kind — articles, projects, skills, experiences, services, testimonials and GitHub activity. Returns numbers only and never the items themselves, so it is the cheap way to answer "how many". To actually show the items, call the matching search_ tool.';

function keywordParam(what: string) {
	return { type: 'string', description: `What to look for in the ${what}. Leave it out to get the most recent ones.` };
}

function limitParam(max: number, fallback: number) {
	return { type: 'integer', description: `How many to return, 1 to ${max}. Leave it out for ${fallback}.` };
}

const DATE_PARAMS = {
	startDate: { type: 'string', description: 'Only include items from this date onward (YYYY-MM-DD).' },
	endDate: { type: 'string', description: 'Only include items up to this date (YYYY-MM-DD).' }
};

async function categoryNames(table: string, ids: unknown[]): Promise<Map<number, string>> {
	const unique = [...new Set(ids.map((id) => Number(id)).filter((id) => Number.isFinite(id)))];
	if (unique.length === 0) return new Map();
	const placeholders = unique.map(() => '?').join(',');
	const rows = await query<{ id: number; name: string }>(`SELECT id, name FROM ${table} WHERE id IN (${placeholders})`, unique);
	return new Map(rows.map((row) => [row.id, row.name]));
}

interface ContentRow {
	id: number;
	title: string;
	short_desc: string | null;
	category_id: number | null;
	created_at: string;
}

async function runContentSearch(r: Retrieval, args: Record<string, any>, table: 'articles' | 'projects', categoryTable: string, max: number, fallback: number) {
	const df = buildDateFilter(args);
	const { rows, total } = await hybridSearch<ContentRow>(r, {
		table,
		fields: 'id, title, short_desc, category_id, created_at',
		where: 'enable = 1',
		ftFields: ['title', 'description'],
		keyword: keyword(args),
		dateClause: df.clause,
		dateParams: df.params,
		limit: clampLimit(args.limit, max, fallback)
	});

	const categories = await categoryNames(
		categoryTable,
		rows.map((row) => row.category_id)
	);

	return {
		ok: true,
		total,
		showing: rows.length,
		[table]: rows.map((row) => ({
			title: row.title,
			summary: stripHtml(row.short_desc),
			category: row.category_id != null ? (categories.get(Number(row.category_id)) ?? null) : null,
			date: row.created_at
		})),
		next_step: nextStep(rows.length, total, table)
	};
}

interface ActivityRow {
	id: number;
	repo: string;
	title: string;
	type: string;
	additions: number | null;
	deletions: number | null;
	created_at: string;
}

async function runActivitySearch(r: Retrieval, args: Record<string, any>) {
	const df = buildDateFilter(args);
	const type = ACTIVITY_TYPES.includes(args?.type) ? args.type : null;
	const { rows, total } = await hybridSearch<ActivityRow>(r, {
		table: 'github_activity',
		fields: 'id, repo, title, type, additions, deletions, created_at',
		where: '1=1',
		ftFields: ['repo', 'title'],
		keyword: keyword(args),
		dateClause: df.clause,
		dateParams: df.params,
		extraClause: type ? ' AND type = ?' : '',
		extraParams: type ? [type] : [],
		limit: clampLimit(args.limit, MAX_ACTIVITY, DEFAULT_ACTIVITY)
	});

	return {
		ok: true,
		total,
		showing: rows.length,
		activity: rows.map((row) => {
			const item: Record<string, unknown> = { repo: row.repo, title: row.title, type: row.type, date: row.created_at };
			if (row.additions != null) item.additions = row.additions;
			if (row.deletions != null) item.deletions = row.deletions;
			return item;
		}),
		next_step: nextStep(rows.length, total, 'events')
	};
}

function activityFilters(args: Record<string, any>) {
	const df = buildDateFilter(args);
	const type = ACTIVITY_TYPES.includes(args?.type) ? args.type : null;
	const raw = keyword(args);
	const ftQuery = raw
		.split(/[\s\-]+/)
		.map((w) => w.replace(/[+><~*"@()]/g, ''))
		.filter((w) => w.length > 0)
		.map((w) => `${w}*`)
		.join(' ');

	const clause = `${type ? ' AND type = ?' : ''}${ftQuery ? ' AND MATCH(repo, title) AGAINST(? IN BOOLEAN MODE)' : ''}${df.clause}`;
	const params = [...(type ? [type] : []), ...(ftQuery ? [ftQuery] : []), ...df.params];
	return { clause, params };
}

async function runActivityStats(args: Record<string, any>) {
	const { clause, params } = activityFilters(args);

	const [totals, byType, byRepo] = await Promise.all([
		query<{ cnt: number }>(`SELECT COUNT(*) as cnt FROM github_activity WHERE 1=1${clause}`, params),
		query<{ type: string; cnt: number }>(`SELECT type, COUNT(*) as cnt FROM github_activity WHERE 1=1${clause} GROUP BY type ORDER BY cnt DESC`, params),
		query<{ repo: string; cnt: number }>(`SELECT repo, COUNT(*) as cnt FROM github_activity WHERE 1=1${clause} GROUP BY repo ORDER BY cnt DESC LIMIT ?`, [
			...params,
			MAX_TOP_REPOS
		])
	]);

	return {
		ok: true,
		total: Number(totals[0]?.cnt ?? 0),
		byType: byType.map((row) => ({ type: row.type, count: Number(row.cnt) })),
		topRepos: byRepo.map((row) => ({ repo: row.repo, count: Number(row.cnt) })),
		next_step: 'Answer with these numbers. For the individual commits or pull requests behind them, call search_activity.'
	};
}

const ABOUT_SECTIONS: Record<string, { flag: string; table: string; load: () => Promise<unknown[]> }> = {
	skills: {
		flag: 'skills_enable',
		table: 'skill',
		load: async () => {
			const rows = await query<{ title: string; type: string }>('SELECT title, type FROM skill ORDER BY `order` ASC LIMIT ?', [MAX_ABOUT_ROWS]);
			return rows.map((row) => ({ title: row.title, type: row.type }));
		}
	},
	experience: {
		flag: 'experience_enable',
		table: 'experience',
		load: async () => {
			const rows = await query<{ title: string; type: string; period: string; description: string }>(
				'SELECT title, type, period, description FROM experience ORDER BY `order` ASC LIMIT ?',
				[MAX_ABOUT_ROWS]
			);
			return rows.map((row) => ({ title: row.title, type: row.type, period: row.period, description: stripHtml(row.description) }));
		}
	},
	services: {
		flag: 'services_enable',
		table: 'service',
		load: async () => {
			const rows = await query<{ title: string; description: string }>('SELECT title, description FROM service ORDER BY `order` ASC LIMIT ?', [MAX_ABOUT_ROWS]);
			return rows.map((row) => ({ title: row.title, description: stripHtml(row.description) }));
		}
	},
	testimonials: {
		flag: 'testimonial_enable',
		table: 'testimonial',
		load: async () => {
			const rows = await query<{ name: string; company: string; description: string }>(
				'SELECT name, company, description FROM testimonial ORDER BY `order` ASC LIMIT ?',
				[MAX_ABOUT_ROWS]
			);
			return rows.map((row) => ({ name: row.name, company: row.company, description: stripHtml(row.description) }));
		}
	}
};

function aboutTopics(section: Section): string[] {
	if (!sectionOn(section, 'about_enable')) return [];
	return Object.entries(ABOUT_SECTIONS)
		.filter(([, entry]) => sectionOn(section, entry.flag))
		.map(([name]) => name);
}

async function runAbout(section: Section, args: Record<string, any>) {
	const available = aboutTopics(section);
	if (available.length === 0) return fail('about_page_disabled');

	const topic = args?.topic && args.topic !== 'all' ? String(args.topic) : 'all';
	if (topic !== 'all' && !available.includes(topic)) return fail('unknown_topic', { topics: ['all', ...available] });

	const wanted = topic === 'all' ? available : [topic];
	const entries = await Promise.all(wanted.map(async (name) => [name, await ABOUT_SECTIONS[name].load()] as const));

	return {
		ok: true,
		topic,
		...Object.fromEntries(entries),
		next_step:
			topic === 'all'
				? 'Answer from these sections in your own words. Do not call this tool again — you already have every part of the about page.'
				: `Answer from this section. The other parts of the about page are ${available.filter((name) => name !== topic).join(', ') || 'not available'}.`
	};
}

async function runSiteInfo() {
	const [home, general] = await Promise.all([fetchHome(), fetchGeneral()]);
	return {
		ok: true,
		title: home.title,
		description: home.description,
		site_url: (env.BASE_URL ?? '').replace(/\/+$/, ''),
		social_links: general.social_links,
		next_step: 'Answer from this. Do not call this tool again in this conversation, the site details do not change.'
	};
}

async function runCount(section: Section, args: Record<string, any>) {
	const df = buildDateFilter(args);
	const raw = keyword(args);
	const ftQuery = raw
		.split(/[\s\-]+/)
		.map((w) => w.replace(/[+><~*"@()]/g, ''))
		.filter((w) => w.length > 0)
		.map((w) => `${w}*`)
		.join(' ');

	const type = COUNT_TYPES.includes(args?.type) ? String(args.type) : null;
	const wantAll = !type;
	const result: Record<string, unknown> = { ok: true };

	const contentCount = async (table: 'articles' | 'projects') => {
		const ftFilter = ftQuery ? ' AND MATCH(title, description) AGAINST(? IN BOOLEAN MODE)' : '';
		const rows = await query<{ cnt: number }>(`SELECT COUNT(*) as cnt FROM ${table} WHERE enable = 1${ftFilter}${df.clause}`, [
			...(ftQuery ? [ftQuery] : []),
			...df.params
		]);
		return Number(rows[0]?.cnt ?? 0);
	};

	if ((wantAll || type === 'article') && sectionOn(section, 'articles_enable')) result.articles = await contentCount('articles');
	if ((wantAll || type === 'project') && sectionOn(section, 'projects_enable')) result.projects = await contentCount('projects');

	const topics = aboutTopics(section);
	for (const [name, entry] of Object.entries(ABOUT_SECTIONS)) {
		if (!wantAll && type !== name.replace(/s$/, '')) continue;
		if (!topics.includes(name)) continue;
		const rows = await query<{ cnt: number }>(`SELECT COUNT(*) as cnt FROM ${entry.table}`);
		result[name] = Number(rows[0]?.cnt ?? 0);
	}

	if ((wantAll || ACTIVITY_TYPES.includes(type as never)) && sectionOn(section, 'contribute_enable')) {
		const stats = await runActivityStats(args);
		result.activity = { total: stats.total, byType: stats.byType, topRepos: stats.topRepos };
	}

	result.next_step = 'Answer with these numbers only. To show the items themselves, call the matching search_ tool.';
	return result;
}

export function buildTerminalTools(section: Section): OpenAI.Chat.ChatCompletionTool[] {
	const tools: OpenAI.Chat.ChatCompletionTool[] = [];
	const topics = aboutTopics(section);

	const add = (name: string, description: string, properties: Record<string, unknown>) => {
		tools.push({ type: 'function', function: { name, description, parameters: { type: 'object', properties } } });
	};

	if (sectionOn(section, 'articles_enable')) {
		add('search_articles', ARTICLES_DESCRIPTION, {
			keyword: keywordParam('article titles and bodies'),
			...DATE_PARAMS,
			limit: limitParam(MAX_ARTICLES, DEFAULT_ARTICLES)
		});
	}

	if (sectionOn(section, 'projects_enable')) {
		add('search_projects', PROJECTS_DESCRIPTION, {
			keyword: keywordParam('project titles and descriptions'),
			...DATE_PARAMS,
			limit: limitParam(MAX_PROJECTS, DEFAULT_PROJECTS)
		});
	}

	if (sectionOn(section, 'contribute_enable')) {
		add('search_activity', ACTIVITY_DESCRIPTION, {
			keyword: keywordParam('repo names and commit or pull request titles'),
			type: { type: 'string', enum: [...ACTIVITY_TYPES], description: 'Narrow to one kind of event. Leave it out for all of them.' },
			...DATE_PARAMS,
			limit: limitParam(MAX_ACTIVITY, DEFAULT_ACTIVITY)
		});
		add('get_activity_stats', ACTIVITY_STATS_DESCRIPTION, {
			keyword: { type: 'string', description: 'Only count events matching this. Leave it out to count everything.' },
			type: { type: 'string', enum: [...ACTIVITY_TYPES], description: 'Narrow to one kind of event. Leave it out for all of them.' },
			...DATE_PARAMS
		});
	}

	if (topics.length > 0) {
		add('get_about', ABOUT_DESCRIPTION, {
			topic: { type: 'string', enum: ['all', ...topics], description: 'Narrow to one part of the about page. Leave it out for all of it.' }
		});
	}

	add('get_site_info', SITE_DESCRIPTION, {});

	add('count', COUNT_DESCRIPTION, {
		keyword: { type: 'string', description: 'Only count items matching this. Leave it out to count everything.' },
		type: { type: 'string', enum: [...COUNT_TYPES], description: 'Narrow to one kind of item. Leave it out to count every kind.' },
		...DATE_PARAMS
	});

	return tools;
}

export function buildDataNote(tools: OpenAI.Chat.ChatCompletionTool[]): string {
	const names = tools.map((tool) => (tool as { function: { name: string } }).function.name);
	if (names.length === 0) return '';

	return `[System] Everything you know about this site comes from your tools, one area at a time: ${names.join(', ')}. Call only the ones the question actually needs — each returns the newest or closest matches for its own area and nothing else, so do not call them all to answer one question. Never answer from memory about what is on this site, and never invent a title, a repo, a date or a number that a tool did not give you. If a tool comes back empty, say plainly that there is nothing matching rather than filling the gap yourself.`;
}

export async function runTerminalTool(name: string, args: Record<string, any>, section: Section, retrieval: Retrieval): Promise<string> {
	const result = await (async () => {
		switch (name) {
			case 'search_articles':
				return runContentSearch(retrieval, args, 'articles', 'article_categories', MAX_ARTICLES, DEFAULT_ARTICLES);
			case 'search_projects':
				return runContentSearch(retrieval, args, 'projects', 'project_categories', MAX_PROJECTS, DEFAULT_PROJECTS);
			case 'search_activity':
				return runActivitySearch(retrieval, args);
			case 'get_activity_stats':
				return runActivityStats(args);
			case 'get_about':
				return runAbout(section, args);
			case 'get_site_info':
				return runSiteInfo();
			case 'count':
				return runCount(section, args);
			default:
				return fail('unknown_tool', { tool: name });
		}
	})().catch((error: Error) => fail('tool_failed', { tool: name, message: error.message }));

	return toToon(result as Record<string, unknown>);
}
