<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OnlineSubscriptionResource\Pages;
use App\Models\RadiusUser;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OnlineSubscriptionResource extends Resource
{
    protected static ?string $model = RadiusUser::class;

    protected static ?string $navigationGroup = 'Langganan';

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationLabel = 'Langganan online';

    protected static ?string $modelLabel = 'Langganan Online';

    protected static ?string $pluralModelLabel = 'Langganan Online';

    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'active');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Langganan Online')
            ->columns([
                Tables\Columns\TextColumn::make('service.cid')->label('No Layanan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service.customer.name')->label('Pelanggan')->searchable(),
                Tables\Columns\TextColumn::make('username')->label('Username')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service.ip_address')->label('IP Address'),
                Tables\Columns\TextColumn::make('router.router_name')->label('Router')->searchable(),
                Tables\Columns\TextColumn::make('service.server_name')->label('Server')->searchable(),
                Tables\Columns\TextColumn::make('service.partner_name')->label('Partner')->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Last Update')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('sync')
                    ->label('Sinkronkan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(fn () => Notification::make()->title('Data online disegarkan dari database aplikasi')->success()->send()),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOnlineSubscriptions::route('/'),
        ];
    }
}
