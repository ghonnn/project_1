@props([
    'label',
    'model',
    'type' => 'text',
    'placeholder' => null,
])

<label class="block">
    <span class="mb-1 block text-sm font-semibold text-gray-700">{{ $label }}</span>
    <input
        type="{{ $type }}"
        wire:model="{{ $model }}"
        placeholder="{{ $placeholder }}"
        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
    />
</label>
