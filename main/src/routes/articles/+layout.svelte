<script lang="ts">
	import { page } from '$app/state';
	import type { LayoutProps } from './$types';

	let { children, data }: LayoutProps = $props();

	const categoryFilterList = data.categoryFilterList ?? [];
	let activeCategory = $derived(page.params.category ?? '');
</script>

<div class="flex min-h-0 flex-1 flex-col overflow-y-auto bg-[#080808]/80 backdrop-blur-sm">
	<div class="flex-1 px-3 pb-12 lg:px-4">
		{#if categoryFilterList.length}
			<nav class="bg-ash-700 sticky top-0 z-50 mb-2 flex items-center overflow-x-auto select-none" aria-label="Article categories">
				<a
					href="/articles"
					data-active={activeCategory === ''}
					class="text-ash-300 data-[active=true]:bg-ash-300 data-[active=true]:text-ash-800 flex shrink-0 items-center gap-1.5 px-3 py-0.5 leading-none"
					aria-label="All articles"
				>
					All
				</a>
				{#each categoryFilterList as { id, name, slug } (id)}
					<a
						href="/articles/category/{encodeURIComponent(slug)}"
						data-active={activeCategory === slug}
						class="text-ash-300 data-[active=true]:bg-ash-300 data-[active=true]:text-ash-800 flex shrink-0 items-center gap-1.5 px-3 py-0.5 leading-none"
						aria-label="Filter by {name}"
					>
						{name}
					</a>
				{/each}
			</nav>
		{/if}
		{@render children()}
	</div>
</div>
