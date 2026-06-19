<?php

namespace App\Filament\Support;

use Filament\Pages\Page;

abstract class FinanceTablePage extends Page
{
    protected static string $view = 'filament.pages.finance-table';

    /** @return array<int, array{label: string, value: string, icon: string, color: string}> */
    public function stats(): array
    {
        return [];
    }

    /** @return array<int, array{label: string, color: string}> */
    public function toolbarActions(): array
    {
        return [];
    }

    /** @return array<int, string> */
    abstract public function columns(): array;

    /** @return array<int, array<int, string>> */
    abstract public function rows(): array;

    abstract public function tableTitle(): string;

    public function emptyText(): string
    {
        return 'No data available in table';
    }
}
