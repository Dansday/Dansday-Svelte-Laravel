<script lang="ts">
	import { onMount } from 'svelte';

	interface Props {
		id: string;
	}

	let { id }: Props = $props();

	onMount(() => {
		if (!id || typeof id !== 'string' || !id.trim()) return;
		const script1 = document.createElement('script');
		script1.async = true;
		script1.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id.trim())}`;
		document.head.appendChild(script1);
		const script2 = document.createElement('script');
		script2.textContent = `
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', ${JSON.stringify(id.trim())});
		`;
		document.head.appendChild(script2);
	});
</script>
