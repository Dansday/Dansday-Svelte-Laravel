import { redirect } from '@sveltejs/kit';
import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent }) => {
	const { posts } = await parent();
	const first = posts[0]?.slug;
	if (first) redirect(308, `/abouts/${first}`);
	redirect(308, '/');
};
