import { env } from '$env/dynamic/private';

function getBase(): string {
	const url = env.ADMIN_API_URL?.replace(/\/$/, '') ?? '';
	if (!url) throw new Error('ADMIN_API_URL is not set');
	return url;
}

async function fetchApi<T>(path: string): Promise<T> {
	const base = getBase();
	const res = await fetch(`${base}/api${path}`);
	if (!res.ok) {
		if (res.status === 404) throw new Error('Not found');
		throw new Error(`API error ${res.status}`);
	}
	return res.json() as Promise<T>;
}

export async function fetchGeneral(): Promise<Record<string, unknown>> {
	return fetchApi<Record<string, unknown>>('/main/general');
}

export async function fetchHome(): Promise<Record<string, unknown>> {
	return fetchApi<Record<string, unknown>>('/main/home');
}

export async function fetchSection(): Promise<Record<string, unknown>> {
	return fetchApi<Record<string, unknown>>('/main/section');
}

export async function fetchAbouts(): Promise<Record<string, unknown>> {
	return fetchApi<Record<string, unknown>>('/main/abouts');
}

export async function fetchArticles(): Promise<Array<Record<string, unknown>>> {
	return fetchApi<Array<Record<string, unknown>>>('/main/articles');
}

export async function fetchProjects(): Promise<{
	projects: Array<Record<string, unknown>>;
	projects_categories: Array<Record<string, unknown>>;
}> {
	return fetchApi('/main/projects');
}

export async function fetchArticle(slug: string): Promise<Record<string, unknown>> {
	return fetchApi<Record<string, unknown>>(`/main/article/${encodeURIComponent(slug)}`);
}

export async function fetchProject(id: number): Promise<Record<string, unknown>> {
	return fetchApi<Record<string, unknown>>(`/main/project/${id}`);
}
