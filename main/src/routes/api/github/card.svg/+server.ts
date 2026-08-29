import type { RequestHandler } from './$types';

const WIDTH = 495;
const HEIGHT = 195;

const COLORS = {
	bg: '#070707',
	border: '#262626',
	title: '#F97316',
	label: '#A4A4A4',
	value: '#C1C1C1',
	muted: '#868686'
};

function escapeXml(value: string): string {
	return value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;');
}

function compact(n: number): string {
	if (n >= 1000) return `${(n / 1000).toFixed(n >= 10000 ? 0 : 1)}k`.replace('.0k', 'k');
	return String(n);
}

function row(label: string, value: string, y: number, delay: number): string {
	return `
    <g class="row" style="animation-delay:${delay}ms">
      <text x="28" y="${y}" class="label">${escapeXml(label)}</text>
      <text x="467" y="${y}" class="value" text-anchor="end">${escapeXml(value)}</text>
    </g>`;
}

function card(name: string, stats: Record<string, number>, range: string): string {
	const rows = [
		row('Commits this year', compact(stats.totalCommits), 78, 150),
		row('Pull requests', compact(stats.totalPRs), 103, 250),
		row('Code reviews', compact(stats.totalReviews), 128, 350),
		row('Issues', compact(stats.totalIssues), 153, 450),
		row('Contributions all time', compact(stats.allTime), 178, 550)
	].join('');

	return `<svg xmlns="http://www.w3.org/2000/svg" width="${WIDTH}" height="${HEIGHT}" viewBox="0 0 ${WIDTH} ${HEIGHT}" role="img" aria-label="GitHub statistics for ${escapeXml(name)}">
  <style>
    .title { font: 600 17px 'Segoe UI', Ubuntu, Sans-Serif; fill: ${COLORS.title} }
    .range { font: 400 11px 'Segoe UI', Ubuntu, Sans-Serif; fill: ${COLORS.muted} }
    .label { font: 400 14px 'Segoe UI', Ubuntu, Sans-Serif; fill: ${COLORS.label} }
    .value { font: 600 14px 'Segoe UI', Ubuntu, Sans-Serif; fill: ${COLORS.value} }
    .row { opacity: 0; animation: fade 0.4s ease-in-out forwards }
    @keyframes fade { from { opacity: 0; transform: translateX(-8px) } to { opacity: 1; transform: translateX(0) } }
  </style>
  <rect x="0.5" y="0.5" width="${WIDTH - 1}" height="${HEIGHT - 1}" rx="6" fill="${COLORS.bg}" stroke="${COLORS.border}"/>
  <text x="28" y="38" class="title">${escapeXml(name)}</text>
  <text x="28" y="54" class="range">${escapeXml(range)}</text>
  ${rows}
</svg>`;
}

export const GET: RequestHandler = async ({ fetch }) => {
	let name = 'GitHub';
	let range = '';
	let stats = { totalCommits: 0, totalPRs: 0, totalReviews: 0, totalIssues: 0, allTime: 0 };

	try {
		const res = await fetch('/api/github');
		if (res.ok) {
			const data = await res.json();
			name = data?.user?.name ?? name;
			range = data?.stats?.yearRange ? `${data.stats.yearRange} ${data.currentYear ?? ''}`.trim() : '';
			stats = {
				totalCommits: data?.stats?.totalCommits ?? 0,
				totalPRs: data?.stats?.totalPRs ?? 0,
				totalReviews: data?.stats?.totalReviews ?? 0,
				totalIssues: data?.stats?.totalIssues ?? 0,
				allTime: data?.stats?.allTime ?? 0
			};
		}
	} catch (err) {
		console.error('[GitHub card]', err);
	}

	return new Response(card(name, stats, range), {
		headers: {
			'Content-Type': 'image/svg+xml; charset=utf-8',
			'Cache-Control': 'public, max-age=1800, s-maxage=1800'
		}
	});
};
