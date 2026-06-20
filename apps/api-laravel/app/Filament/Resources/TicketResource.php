<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Support\AdminOptions;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationGroup = 'Tiket';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Tiket';

    protected static ?string $modelLabel = 'Tiket';

    protected static ?string $pluralModelLabel = 'Tiket';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\Select::make('customer_id')->label('Pelanggan')->options(fn () => AdminOptions::customers())->searchable(),
                Forms\Components\Select::make('service_id')->label('Layanan')->options(fn () => AdminOptions::services())->searchable(),
                Forms\Components\Select::make('router_id')->label('Router')->options(fn () => AdminOptions::routers())->searchable(),
                Forms\Components\TextInput::make('subject')->label('Subjek')->required()->maxLength(255),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(4)->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'new' => 'Baru',
                        'open' => 'Dibuka',
                        'in_progress' => 'Diproses',
                        'resolved' => 'Selesai',
                        'closed' => 'Ditutup',
                    ])
                    ->default('new')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')->label('Subjek')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Pelanggan')->searchable(),
                Tables\Columns\TextColumn::make('router.router_name')->label('Router')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'resolved', 'closed' => 'success',
                    'in_progress' => 'warning',
                    'new', 'open' => 'info',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'new' => 'Baru',
                    'open' => 'Dibuka',
                    'in_progress' => 'Diproses',
                    'resolved' => 'Selesai',
                    'closed' => 'Ditutup',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
