<x-filament-panels::page>
    <div class="space-y-4">
        @if (count($this->stats()))
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->stats() as $stat)
                    @php
                        $colors = [
                            'info' => '#0ea5e9',
                            'success' => '#22c55e',
                            'warning' => '#f59e0b',
                            'danger' => '#ef4444',
                            'gray' => '#64748b',
                        ];
                        $color = $colors[$stat['color']] ?? '#0ea5e9';
                    @endphp
                    <div class="flex items-center gap-4 border border-slate-700 bg-slate-900/70 p-4">
                        <div class="flex h-16 w-16 items-center justify-center text-white" style="background: {{ $color }}">
                            <x-dynamic-component :component="$stat['icon']" class="h-9 w-9" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-slate-400">{{ $stat['label'] }}</div>
                            <div class="mt-2 text-xl font-semibold text-white">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <section class="overflow-hidden border border-slate-700 bg-slate-900/80">
            <div class="bg-slate-700/70 px-4 py-3 text-sm font-bold uppercase text-white">
                {{ $this->tableTitle() }}
            </div>

            <div class="space-y-3 p-4">
                @if (count($this->toolbarActions()))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($this->toolbarActions() as $action)
                            @php
                                $colors = [
                                    'info' => '#0ea5e9',
                                    'success' => '#22c55e',
                                    'warning' => '#f59e0b',
                                    'danger' => '#ef4444',
                                    'gray' => '#64748b',
                                ];
                                $color = $colors[$action['color']] ?? '#0ea5e9';
                            @endphp
                            <button type="button" class="rounded px-3 py-1.5 text-xs font-bold uppercase text-white" style="background: {{ $color }}">
                                {{ $action['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="flex flex-wrap gap-2">
                    <select class="rounded border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-slate-200">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                    <input class="min-w-64 flex-1 rounded border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-slate-200" placeholder="Search..." />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-left text-xs text-slate-300">
                        <thead class="border-y border-slate-700 uppercase text-slate-200">
                            <tr>
                                <th class="w-8 px-3 py-3"><input type="checkbox" /></th>
                                @foreach ($this->columns() as $column)
                                    <th class="whitespace-nowrap px-3 py-3">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->rows() as $row)
                                <tr class="border-b border-slate-800">
                                    <td class="px-3 py-3"><input type="checkbox" /></td>
                                    @foreach ($row as $cell)
                                        <td class="whitespace-nowrap px-3 py-3">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($this->columns()) + 1 }}" class="px-3 py-8 text-center text-slate-400">
                                        {{ $this->emptyText() }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span>Showing {{ count($this->rows()) ? '1 to '.count($this->rows()) : '0 to 0' }} of {{ count($this->rows()) }} entries</span>
                    <div class="flex gap-1">
                        <button class="rounded border border-slate-700 px-3 py-1">Previous</button>
                        <button class="rounded bg-primary-600 px-3 py-1 text-white">1</button>
                        <button class="rounded border border-slate-700 px-3 py-1">Next</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
