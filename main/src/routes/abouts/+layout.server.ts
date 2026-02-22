import { redirect } from '@sveltejs/kit';
import type { LayoutServerLoad } from './$types';

const ABOUTS_CHILDREN = [
	{ key: 'experience', orderKey: 'about_experience_order', enableKey: 'experience_enable', title: 'Experience' },
	{ key: 'services', orderKey: 'about_services_order', enableKey: 'services_enable', title: 'Services' },
	{ key: 'skills', orderKey: 'about_skills_order', enableKey: 'skills_enable', title: 'Skills' },
	{ key: 'testimonial', orderKey: 'about_testimonial_order', enableKey: 'testimonial_enable', title: 'Testimonials' }
] as const;

export const load: LayoutServerLoad = async ({ parent }) => {
	const data = await parent();
	const section = (data.section ?? {}) as Record<string, unknown>;
	if (section.about_enable !== 1 && section.about_enable !== true) {
		throw redirect(302, '/');
	}
	const general = (data.general ?? {}) as Record<string, unknown>;
	const description = (general.description as string) ?? '';

	const posts = ABOUTS_CHILDREN.filter((c) => section[c.enableKey] === 1 || section[c.enableKey] === true)
		.map((c) => ({
			slug: c.key,
			title: c.title,
			description
		}))
		.sort(
			(a, b) =>
				(Number(section[ABOUTS_CHILDREN.find((x) => x.key === a.slug)?.orderKey]) ?? 0) -
				(Number(section[ABOUTS_CHILDREN.find((x) => x.key === b.slug)?.orderKey]) ?? 0)
		);

	return { posts };
};
