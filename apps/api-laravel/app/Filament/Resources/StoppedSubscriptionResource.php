<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoppedSubscriptionResource\Pages;
use App\Models\Service;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StoppedSubscriptionResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationGroup = 'Langganan';

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationLabel = 'Stop berlangganan';

    protected static ?string $modelLabel = 'Stop Berlangganan';

    protected static ?string $pluralModelLabel = 'Stop Berlangganan';

    protected static ?int $navigationSort = 15;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'terminated');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Berhenti Langganan')
            ->description('Fitur ini berfungsi untuk menonaktifkan layanan agar invoice tetap tersimpan dan data yang sudah masuk tidak bisa diaktifkan kembali.')
            ->columns([
                Tables\Columns\TextColumn::make('cid')->label('No Layanan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Pelanggan')->searchable(),
                Tables\Columns\TextColumn::make('billing_profile_name')->label('Profile')->searchable(),
                Tables\Columns\TextColumn::make('billing_type')->label('Jenis Tagihan')->badge(),
                Tables\Columns\TextColumn::make('billing_cycle')->label('Siklus Tagihan'),
                Tables\Columns\TextColumn::make('created_at')->label('Tgl Daftar')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('terminated_at')->label('Tgl Stop')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('partner_name')->label('Partner')->searchable(),
                Tables\Columns\TextColumn::make('notes')->label('Note')->limit(30),
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
            'index' => Pages\ListStoppedSubscriptions::route('/'),
        ];
    }
}
