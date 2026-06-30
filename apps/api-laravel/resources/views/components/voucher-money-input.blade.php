@props([
    'label',
    'model',
    'placeholder' => '0',
])

<label class="block">
    <span class="mb-1 block text-sm font-semibold text-gray-700">{{ $label }}</span>
    <div
        x-data="{
            value: $wire.entangle('{{ $model }}').live,
            format(input) {
                const raw = String(input ?? '').replace(/[^\d]/g, '');
                return raw === '' ? '' : new Intl.NumberFormat('id-ID').format(Number(raw));
            },
            sync(event) {
                this.value = this.format(event.target.value);
                event.target.value = this.value;
            }
        }"
        x-init="value = format(value)"
        class="flex rounded-lg shadow-sm"
    >
        <span class="inline-flex w-14 shrink-0 items-center justify-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-sm font-semibold text-gray-600">Rp</span>
        <input
            type="text"
            inputmode="numeric"
            x-bind:value="format(value)"
            x-on:input="sync($event)"
            placeholder="{{ $placeholder }}"
            class="w-full rounded-l-none rounded-r-lg border-gray-300 text-right text-sm tabular-nums shadow-none focus:border-emerald-500 focus:ring-emerald-500"
        />
    </div>
</label>
