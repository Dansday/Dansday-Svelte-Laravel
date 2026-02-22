import { error } from '@sveltejs/kit';
import { createHighlighter } from 'shiki';
import { experienceToCodeLines, servicesToCodeLines, skillsToCodeLines, testimonialsToCodeLines } from '$lib/abouts-code';
import type { PageServerLoad } from './$types';

const VALID_SLUGS = ['experience', 'services', 'skills', 'testimonial'] as const;

export const load: PageServerLoad = async ({ params, parent }) => {
	const slug = params.slug?.toLowerCase();
	if (!slug || !VALID_SLUGS.includes(slug as (typeof VALID_SLUGS)[number])) {
		error(404, 'Not found');
	}

	const data = await parent();
	const general = (data.general ?? {}) as Record<string, unknown>;
	const siteName = (data.siteName as string) ?? '';
	const design_skills = (data.design_skills ?? []) as Array<Record<string, unknown>>;
	const dev_skills = (data.dev_skills ?? []) as Array<Record<string, unknown>>;
	const edu_experiences = (data.edu_experiences ?? []) as Array<Record<string, unknown>>;
	const emp_experiences = (data.emp_experiences ?? []) as Array<Record<string, unknown>>;
	const testimonials = (data.testimonials ?? []) as Array<Record<string, unknown>>;
	const services = (data.services ?? []) as Array<Record<string, unknown>>;

	const titles: Record<string, string> = {
		experience: 'Experience',
		services: 'Services',
		skills: 'Skills',
		testimonial: 'Testimonials'
	};
	const meta = {
		title: titles[slug] ?? slug,
		description: (general.description as string) ?? ''
	};

	let codeLines: string[];
	switch (slug) {
		case 'experience':
			codeLines = experienceToCodeLines(edu_experiences, emp_experiences);
			break;
		case 'services':
			codeLines = servicesToCodeLines(services);
			break;
		case 'skills':
			codeLines = skillsToCodeLines(design_skills, dev_skills);
			break;
		case 'testimonial':
			codeLines = testimonialsToCodeLines(testimonials);
			break;
		default:
			codeLines = [];
	}

	const code = codeLines.join('\n');
	const highlighter = await createHighlighter({
		themes: ['poimandres'],
		langs: ['typescript']
	});
	const html = highlighter.codeToHtml(code, {
		lang: 'typescript',
		theme: 'poimandres'
	});
	highlighter.dispose();

	return { content: { html }, meta, siteName };
};
