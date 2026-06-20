<?php

namespace App\Filament\Support;

use Filament\Pages\Page;

abstract class ComingSoonPage extends Page
{
    protected static string $view = 'filament.pages.coming-soon';

    protected string $description = '';

    /** @var array<int, string> */
    protected array $plannedFeatures = [];

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'title' => static::getNavigationLabel(),
            'description' => $this->description,
            'plannedFeatures' => $this->plannedFeatures,
        ];
    }
}
