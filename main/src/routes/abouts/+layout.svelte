<script lang="ts">
	import { page } from '$app/state';
	import Icon from '$lib/components/icon.svelte';
	import type { LayoutProps } from './$types';

	let { children, data }: LayoutProps = $props();
</script>

<section class="flex min-h-0 flex-1 flex-col overflow-hidden px-3 lg:px-4">
	<nav class="border-ash-600 bg-ash-700/80 flex shrink-0 items-center gap-0 overflow-x-auto border-b select-none" aria-label="Abouts sections">
		{#each data.posts as post (post.slug)}
			<a
				href={`/abouts/${post.slug}`}
				class="data-[active=true]:border-ash-400 data-[active=true]:bg-ash-800 data-[active=true]:text-ash-200 data-[active=false]:text-ash-400 data-[active=false]:hover:text-ash-300 flex shrink-0 items-center gap-1.5 border-b-2 px-3 py-2 text-sm leading-none transition-colors data-[active=false]:border-transparent"
				data-active={page.params.slug === post.slug}
				data-sveltekit-preload-data
			>
				<Icon name="ts" />
				{post.slug}.ts
			</a>
		{/each}
	</nav>
	<div class="min-h-0 flex-1 overflow-hidden">
		{@render children()}
	</div>
</section>
