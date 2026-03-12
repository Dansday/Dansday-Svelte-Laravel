<script lang="ts">
	import { onMount } from 'svelte';
	import Metadata from '$lib/components/metadata.svelte';

	interface CommandHistory {
		command: string;
		output: string[];
		isError?: boolean;
	}

	let history: CommandHistory[] = $state([]);
	let currentInput = $state('');
	let inputElement: HTMLInputElement;

	const username = 'dansday@ai';
	const directory = '~';

	function handleKeydown(event: KeyboardEvent) {
		if (event.key === 'Enter') {
			const command = currentInput.trim();
			if (command) {
				executeCommand(command);
			} else {
				history = [...history, { command: '', output: [] }];
			}
			currentInput = '';
			setTimeout(() => {
				scrollToBottom();
			}, 10);
		}
	}

	function executeCommand(command: string) {
		const args = command.split(' ').filter(Boolean);
		const cmd = args[0].toLowerCase();
		let output: string[] = [];
		let isError = false;

		switch (cmd) {
			case 'help':
				output = [
					'GNU bash, version 5.2.21(1)-release (x86_64-pc-linux-gnu)',
					'These shell commands are defined internally. Type `help\' to see this list.',
					'  help    - Show this help message',
					'  clear   - Clear the terminal screen',
					'  echo    - Print arguments to the standard output',
					'  date    - Display the current date and time',
					'  whoami  - Print the current username'
				];
				break;
			case 'clear':
				history = [];
				return;
			case 'echo':
				output = [args.slice(1).join(' ')];
				break;
			case 'date':
				output = [new Date().toString()];
				break;
			case 'whoami':
				output = ['dansday'];
				break;
			default:
				output = [`${cmd}: command not found`];
				isError = true;
				break;
		}

		history = [...history, { command, output, isError }];
	}

	function scrollToBottom() {
		if (inputElement) {
			inputElement.scrollIntoView({ behavior: 'smooth' });
		}
	}

	onMount(() => {
		if (inputElement) {
			inputElement.focus();
		}
	});

	function focusInput() {
		if (inputElement) {
			inputElement.focus();
		}
	}
</script>

<Metadata title="Terminal | {username}" description="Ubuntu Terminal instance for {username}" />

<section class="flex h-full grow flex-col bg-[#300a24] font-mono text-sm md:text-base" onclick={focusInput} role="presentation">
	<div class="flex-1 overflow-y-auto p-4 sm:p-6 text-ash-100">
		<div class="mb-4">
			<div>Welcome to Ubuntu 24.04 LTS (GNU/Linux 6.6.87.2-microsoft-standard-WSL2 x86_64)</div>
			<br />
			<div> * Documentation:  <a href="https://help.ubuntu.com" target="_blank" class="hover:underline">https://help.ubuntu.com</a></div>
			<div> * Management:     <a href="https://landscape.canonical.com" target="_blank" class="hover:underline">https://landscape.canonical.com</a></div>
			<div> * Support:        <a href="https://ubuntu.com/pro" target="_blank" class="hover:underline">https://ubuntu.com/pro</a></div>
			<br />
			<div>Type 'help' to see available commands.</div>
		</div>

		{#each history as item}
			<div class="mb-1">
				<div class="flex flex-wrap items-center">
					<span class="font-bold text-[#8ae234]">{username}</span>
					<span class="text-white">:</span>
					<span class="font-bold text-[#729fcf]">{directory}</span>
					<span class="text-white mr-2">$</span>
					<span>{item.command}</span>
				</div>
				{#if item.output.length > 0}
					<div class="mt-1 whitespace-pre-wrap {item.isError ? 'text-red-400' : 'text-ash-100'}">
						{#each item.output as line}
							<div>{line}</div>
						{/each}
					</div>
				{/if}
			</div>
		{/each}

		<div class="flex flex-wrap items-center">
			<span class="font-bold text-[#8ae234]">{username}</span>
			<span class="text-white">:</span>
			<span class="font-bold text-[#729fcf]">{directory}</span>
			<span class="text-white mr-2">$</span>
			<input
				bind:this={inputElement}
				bind:value={currentInput}
				onkeydown={handleKeydown}
				class="flex-1 bg-transparent text-white outline-none"
				type="text"
				spellcheck="false"
				autocomplete="off"
				autofocus
			/>
		</div>
	</div>
</section>
