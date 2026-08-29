export function makeSlug(name: string): string {
	return (name ?? '')
		.toLowerCase()
		.normalize('NFKD')
		.replace(/[̀-ͯ]/g, '')
		.replace(/#/g, ' sharp ')
		.replace(/\+/g, ' plus ')
		.replace(/&/g, ' and ')
		.replace(/[^a-z0-9\s-]/g, '')
		.replace(/[\s-]+/g, '-')
		.replace(/^-+|-+$/g, '');
}
