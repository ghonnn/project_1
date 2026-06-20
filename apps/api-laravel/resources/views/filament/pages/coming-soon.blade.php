<x-filament-panels::page>
    <section class="overflow-hidden border border-slate-700 bg-slate-900/80">
        <div class="bg-slate-700/70 px-4 py-3 text-sm font-bold uppercase text-white">
            {{ $title }}
        </div>

        <div class="space-y-4 p-4">
            <p class="text-sm text-slate-300">{{ $description }}</p>

            @if (count($plannedFeatures))
                <div>
                    <div class="text-xs font-semibold uppercase text-slate-400">Fungsi yang direncanakan</div>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-slate-300">
                        @foreach ($plannedFeatures as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="text-xs text-slate-500">
                Menu ini sudah terdaftar di navigasi sebagai placeholder. Detail kebutuhan ada di
                docs/PRD_NEX_OSS_BSS_Voucher_Mitra_NetworkOps_Addendum.md.
            </p>
        </div>
    </section>
</x-filament-panels::page>
