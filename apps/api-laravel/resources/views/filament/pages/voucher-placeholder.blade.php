<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ strtoupper($title) }}</x-slot>

        <div class="text-sm text-gray-500">
            Modul {{ $title }} sudah masuk ke menu Voucher. Struktur data hotspot/voucher bisa disambungkan pada tahap berikutnya.
        </div>
    </x-filament::section>
</x-filament-panels::page>
