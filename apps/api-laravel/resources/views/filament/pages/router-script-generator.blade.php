<x-filament-panels::page>
    <x-filament-panels::form wire:submit="generate">
        {{ $this->form }}

        <div class="flex gap-3">
            <x-filament::button type="submit" icon="heroicon-o-command-line">
                Generate script
            </x-filament::button>
        </div>
    </x-filament-panels::form>

    @if ($script)
        <x-filament::section>
            <x-slot name="heading">
                MikroTik Script
            </x-slot>

            <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-100 dark:bg-black">{{ $script }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
