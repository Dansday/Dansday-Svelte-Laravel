<script lang="ts">
	import { page } from '$app/state';
	import { MediaQuery } from 'svelte/reactivity';

	interface $$Props {
		title: string;
		description: string;
		image?: string;
	}

	let { title, description, image = '' }: $$Props = $props();

	let isMobile = $derived(new MediaQuery('(max-width: 1024px)').current);
</script>

<svelte:head>
	<title>{title}</title>
	<meta name="description" content={description} />
	<meta name="theme-color" content={isMobile ? '#262626' : '#454545'} />

	<meta property="og:url" content={page.url.origin} />
	<meta property="og:type" content="website" />
	<meta property="og:title" content={title} />
	<meta property="og:description" content={description} />
	{#if image}
		<meta property="og:image" content={image} />
	{/if}

	<meta name="twitter:card" content="summary_large_image" />
	<meta property="twitter:domain" content={page.url.origin} />
	{#if image}
		<meta property="twitter:url" content={image} />
		<meta name="twitter:image" content={image} />
	{/if}
	<meta name="twitter:title" content={title} />
	<meta name="twitter:description" content={description} />
</svelte:head>
