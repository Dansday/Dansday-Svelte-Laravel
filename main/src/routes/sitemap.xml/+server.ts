import { BASE_URL } from '$env/static/private';
import { fetchSection, fetchArticles, fetchProjects } from '$lib/server/data';
import type { RequestHandler } from './$types';

function escapeXml(unsafe: string): string {
	return unsafe.replace(
		/[&<"'>]/g,
		(match) =>
			({
				'&': '&amp;',
				'<': '&lt;',
				'"': '&quot;',
				"'": '&apos;',
				'>': '&gt;'
			})[match] || match
	);
}

const notDisabled = (v: unknown) => v !== 0 && v !== false;

export const GET: RequestHandler = async () => {
	let section: Record<string, unknown> = {};
	let articleUrlData: Array<{ loc: string; lastmod?: string; changefreq: string; priority: number }> = [];
	let projectUrlData: Array<{ loc: string; lastmod?: string; changefreq: string; priority: number }> = [];

	try {
		section = (await fetchSection()) as Record<string, unknown>;
	} catch {}

	if (notDisabled(section.articles_enable)) {
		try {
			const articles = await fetchArticles();
			articleUrlData = articles.map((row) => ({
				loc: `${BASE_URL}/articles/${row.slug as string}`,
				lastmod: row.updated_at != null ? new Date(row.updated_at as string).toISOString() : (row.created_at != null ? new Date(row.created_at as string).toISOString() : undefined),
				changefreq: 'daily',
				priority: 0.8
			}));
		} catch {}
	}

	if (notDisabled(section.projects_enable)) {
		try {
			const { projects } = await fetchProjects();
			projectUrlData = (projects ?? []).map((row) => ({
				loc: `${BASE_URL}/projects/${row.id as number}`,
				lastmod: row.updated_at != null ? new Date(row.updated_at as string).toISOString() : undefined,
				changefreq: 'daily',
				priority: 0.6
			}));
		} catch {}
	}

	const ABOUTS_CHILDREN = [
		{ slug: 'experience' as const, enableKey: 'experience_enable' },
		{ slug: 'services' as const, enableKey: 'services_enable' },
		{ slug: 'skills' as const, enableKey: 'skills_enable' },
		{ slug: 'testimonial' as const, enableKey: 'testimonial_enable' }
	] as const;
	const aboutsUrlData: Array<{ loc: string; changefreq: string; priority: number; lastmod: string }> = [];
	const aboutOn = notDisabled(section.about_enable);
	const enabledAboutsChildren = ABOUTS_CHILDREN.filter((c) => notDisabled(section[c.enableKey]));
	if (aboutOn && enabledAboutsChildren.length > 0) {
		for (const { slug } of enabledAboutsChildren) {
			aboutsUrlData.push({
				loc: `${BASE_URL}/abouts/${slug}`,
				changefreq: 'daily',
				priority: 0.5,
				lastmod: new Date().toISOString()
			});
		}
	}

	const allUrlData = [
		{ loc: `${BASE_URL}/`, changefreq: 'daily', priority: 1.0, lastmod: new Date().toISOString() },
		...(notDisabled(section.projects_enable) ? [{ loc: `${BASE_URL}/projects`, changefreq: 'daily' as const, priority: 1.0, lastmod: new Date().toISOString() }] : []),
		...(notDisabled(section.articles_enable) ? [{ loc: `${BASE_URL}/articles`, changefreq: 'daily' as const, priority: 1.0, lastmod: new Date().toISOString() }] : []),
		...aboutsUrlData,
		...articleUrlData,
		...projectUrlData
	];

	const urlElements = allUrlData
		.map(({ loc, lastmod, changefreq, priority }) => {
			const lastmodElement = lastmod ? `<lastmod>${lastmod}</lastmod>` : '';
			return `
    <url>
      <loc>${escapeXml(loc)}</loc>${lastmodElement}
      <changefreq>${changefreq}</changefreq>
      <priority>${priority.toFixed(1)}</priority>
    </url>`;
		})
		.join('');

	return new Response(
		`<?xml version="1.0" encoding="UTF-8" ?>
		<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
			${urlElements}
		</urlset>`.trim(),
		{
			headers: {
				'Content-Type': 'application/xml',
				'Cache-Control': 'max-age=3600'
			}
		}
	);
};
