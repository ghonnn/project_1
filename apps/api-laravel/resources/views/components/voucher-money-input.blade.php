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
        class="relative"
    >
        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center text-sm font-semibold text-gray-500">Rp</span>
        <input
            type="text"
            inputmode="numeric"
            x-bind:value="format(value)"
            x-on:input="sync($event)"
            placeholder="{{ $placeholder }}"
            class="w-full rounded-lg border-gray-300 pl-14 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
        />
    </div>
</label>
