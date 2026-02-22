export function resolveImageUrl(url: string | null | undefined, baseUrl: string): string {
	if (!url || typeof url !== 'string') return '';
	const base = baseUrl.replace(/\/$/, '');
	if (url.startsWith('http://') || url.startsWith('https://')) return url;
	return url.startsWith('/') ? `${base}${url}` : `${base}/${url}`;
}
