import { redirect } from '@sveltejs/kit';
import { fetchArticlesWithCategories } from '$lib/server/data';
import type { PageServerLoad } from './$types';
import { makeSlug as slug } from '$lib/slug';

export const load: PageServerLoad = async ({ params, parent }) => {
	const data = await parent();
	const section = (data.section ?? {}) as Record<string, unknown>;
	if (section.articles_enable !== 1 && section.articles_enable !== true) {
		throw redirect(302, '/');
	}
	try {
		const { articles, articles_categories } = await fetchArticlesWithCategories();
		const categories = (articles_categories ?? []) as Array<{ id: number; name: string }>;
		const byId = Object.fromEntries(categories.map((c) => [c.id, c]));
		const allItems = (articles ?? []).map((row: Record<string, unknown>) => {
			const catId = row.category_id as number | undefined;
			const cat = catId ? byId[catId] : null;
			return {
				slug: slug(row.title as string),
				title: row.title as string,
				description: (row.short_desc as string) || '',
				publishedDate:
					row.created_at != null
						? new Date(row.created_at as string).toLocaleDateString('en-US', {
								month: 'short',
								day: 'numeric',
								year: 'numeric'
							})
						: '',
				poster: (row.image as string) || '',
				category_slug: cat ? slug(cat.name) : null
			};
		});
		const articleList = allItems.filter((a) => a.category_slug === params.category);
		return { articles: articleList };
	} catch {
		return { articles: [] };
	}
};
