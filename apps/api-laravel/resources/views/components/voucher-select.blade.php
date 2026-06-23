@props([
    'label',
    'model',
    'options' => [],
])

<label class="block">
    <span class="mb-1 block text-sm font-semibold text-gray-700">{{ $label }}</span>
    <select
        wire:model.live="{{ $model }}"
        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
    >
        <option value="">Pilih {{ strtolower($label) }}</option>
        @foreach ($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>
</label>
