import { fetchGeneral, fetchHome } from '$lib/server/data';
import { env } from '$env/dynamic/private';
import type { RequestHandler } from './$types';

function resolveUrl(url: string | null | undefined, base: string): string {
	if (!url || typeof url !== 'string') return '';
	const b = base.replace(/\/$/, '');
	if (url.startsWith('http://') || url.startsWith('https://')) return url;
	const path = url.startsWith('/') ? url : `/${url}`;
	if (path.startsWith('/uploads/')) return `${b}${path}`;
	return `${b}${path}`;
}

export const GET: RequestHandler = async () => {
	try {
		const publicBase = env.ADMIN_PUBLIC_URL?.replace(/\/$/, '') ?? '';
		const [generalData, home] = await Promise.all([fetchGeneral(), fetchHome()]);
		
		const general = (generalData as Record<string, unknown>) ?? {};
		const homeRecord = (home as Record<string, unknown>) ?? {};
		const siteName = (general.title as string) ?? (homeRecord.title as string) ?? '';
		
		const defaultFavicon = resolveUrl((general.image_favicon as string) ?? null, publicBase);
		
		let icons: Array<{ src: string; sizes: string; type: string }> = [];
		if (defaultFavicon && general.image_favicon && typeof general.image_favicon === 'string') {
			const dirUrl = defaultFavicon.substring(0, defaultFavicon.lastIndexOf('/'));
			icons = [
				{
					src: `${dirUrl}/web-app-manifest-192x192.png`,
					sizes: '192x192',
					type: 'image/png'
				},
				{
					src: `${dirUrl}/web-app-manifest-512x512.png`,
					sizes: '512x512',
					type: 'image/png'
				}
			];
		}

		const manifest = {
			name: siteName,
			short_name: siteName,
			theme_color: '#ffffff',
			background_color: '#ffffff',
			display: 'standalone',
			icons
		};

		return new Response(JSON.stringify(manifest), {
			headers: {
				'Content-Type': 'application/manifest+json',
				'Cache-Control': 'public, max-age=3600'
			}
		});
	} catch {
		return new Response(JSON.stringify({}), {
			headers: {
				'Content-Type': 'application/manifest+json',
				'Cache-Control': 'public, max-age=3600'
			}
		});
	}
};
