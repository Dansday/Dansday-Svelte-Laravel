import { fetchArticlesWithCategories } from '$lib/server/data';
import type { LayoutServerLoad } from './$types';

function slug(name: string) {
	return name
		.toLowerCase()
		.replace(/\s+/g, '-')
		.replace(/^-+|-+$/g, '');
}

export const load: LayoutServerLoad = async () => {
	try {
		const { articles, articles_categories } = await fetchArticlesWithCategories();
		const categories = (articles_categories ?? []) as Array<{ id: number; name: string }>;
		const articleList = (articles ?? []) as Array<{ category_id?: number }>;
		const categoryFilterList: Array<{ id: number; name: string; slug: string }> = [];
		for (const cat of categories) {
			const hasArticle = articleList.some((a) => a.category_id === cat.id);
			if (hasArticle) categoryFilterList.push({ id: cat.id, name: cat.name, slug: slug(cat.name) });
		}
		return { categoryFilterList };
	} catch {
		return { categoryFilterList: [] };
	}
};
