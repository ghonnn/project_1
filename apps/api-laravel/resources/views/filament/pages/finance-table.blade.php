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
                    <div class="nex-finance-card flex items-center gap-4 p-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-lg text-white" style="background: {{ $color }}">
                            <x-dynamic-component :component="$stat['icon']" class="h-7 w-7" />
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-500">{{ $stat['label'] }}</div>
                            <div class="mt-2 text-2xl font-bold text-slate-950">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <section class="nex-finance-table overflow-hidden">
            <div class="border-b border-slate-200 bg-white px-5 py-4 text-base font-bold text-slate-950">
                {{ $this->tableTitle() }}
            </div>

            <div class="space-y-4 p-5">
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
                            <button type="button" class="nex-toolbar-button text-white" style="background: {{ $color }}">
                                {{ $action['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="flex flex-wrap gap-2">
                    <select class="h-10 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                    <input class="h-10 min-w-64 flex-1 rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700" placeholder="Search..." />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-left">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="w-8 px-3 py-3"><input type="checkbox" /></th>
                                @foreach ($this->columns() as $column)
                                    <th class="whitespace-nowrap px-3 py-3">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->rows() as $row)
                                <tr class="hover:bg-emerald-50/60">
                                    <td class="px-3 py-3"><input type="checkbox" /></td>
                                    @foreach ($row as $cell)
                                        <td class="whitespace-nowrap px-3 py-3">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($this->columns()) + 1 }}" class="px-3 py-10 text-center font-medium text-slate-500">
                                        {{ $this->emptyText() }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between text-sm font-medium text-slate-500">
                    <span>Showing {{ count($this->rows()) ? '1 to '.count($this->rows()) : '0 to 0' }} of {{ count($this->rows()) }} entries</span>
                    <div class="flex gap-1">
                        <button class="nex-action-button min-w-0 border border-slate-300 bg-white text-slate-700">Previous</button>
                        <button class="nex-action-button min-w-0 bg-emerald-600 text-white">1</button>
                        <button class="nex-action-button min-w-0 border border-slate-300 bg-white text-slate-700">Next</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
