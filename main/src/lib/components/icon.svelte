<script lang="ts">
	import type { SVGAttributes } from 'svelte/elements';
	import iconsUrl from '$lib/assets/icons.svg';

	interface $$Props extends Omit<SVGAttributes<SVGSVGElement>, 'class'> {
		name: string;
		title?: string;
	}

	let { name, title, ...props }: $$Props = $props();
</script>

<svg role="img" class="size-[14px]" {...props}>
	{#if title}<title>{title}</title>{/if}
	<use xlink:href={`${iconsUrl}#${name}`} href={`${iconsUrl}#${name}`} />
</svg>
