<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiusUserResource\Pages;
use App\Filament\Resources\RadiusUserResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\RadiusUser;
use App\Models\Service;
use App\Services\FreeRadiusService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RadiusUserResource extends Resource
{
    protected static ?string $model = RadiusUser::class;

    protected static ?string $navigationGroup = 'Radius';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna Radius';

    protected static ?string $pluralModelLabel = 'Pengguna Radius';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->options(fn () => AdminOptions::tenants())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set): void {
                        $set('customer_id', null);
                        $set('service_id', null);
                        $set('router_id', null);
                        $set('profile_id', null);
                    }),
                Forms\Components\Select::make('customer_id')
                    ->label('Pelanggan')
                    ->options(fn (Forms\Get $get) => AdminOptions::customers($get('tenant_id')))
                    ->getSearchResultsUsing(fn (string $search, Forms\Get $get): array => AdminOptions::customers($get('tenant_id'), $search))
                    ->getOptionLabelUsing(fn (?string $value): ?string => AdminOptions::customerOptionLabel($value))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('service_id')
                    ->label('Layanan')
                    ->options(fn (Forms\Get $get) => AdminOptions::services($get('tenant_id')))
                    ->searchable()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                        $service = $state
                            ? Service::query()->with(['product', 'primaryRouterMapping'])->find($state)
                            : null;

                        if (! $service) {
                            return;
                        }

                        $set('customer_id', $service->customer_id);
                        $set('router_id', $service->primaryRouterMapping?->router_id);
                        $set('username', $service->internet_username ?: $service->cid);
                        $set('secret', $service->internet_password);
                        $set('profile_id', $service->product
                            ? \App\Models\RadiusProfile::query()
                                ->where('tenant_id', $service->tenant_id)
                                ->where('name', $service->product->name)
                                ->value('id')
                            : null);
                    }),
                Forms\Components\Select::make('router_id')
                    ->label('Router')
                    ->options(fn (Forms\Get $get) => AdminOptions::routers($get('tenant_id')))
                    ->searchable(),
                Forms\Components\Select::make('profile_id')
                    ->label('Profil Langganan')
                    ->options(fn (Forms\Get $get) => AdminOptions::radiusProfiles($get('tenant_id')))
                    ->searchable(),
                Forms\Components\TextInput::make('username')->required()->maxLength(64),
                Forms\Components\TextInput::make('secret')->password()->revealable()->required()->maxLength(64),
                Forms\Components\Select::make('status')
                    ->options(['pending' => 'Pending', 'active' => 'Active', 'suspended' => 'Suspended'])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('username')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service.customer.name')->label('Pelanggan')->searchable(),
                Tables\Columns\TextColumn::make('service.cid')->label('Layanan')->searchable(),
                Tables\Columns\TextColumn::make('router.router_name')->label('Router')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('profile.name')->label('Profil Langganan')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'pending' => 'warning',
                    'suspended' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (RadiusUser $record): void {
                        $log = app(FreeRadiusService::class)->syncUser($record);

                        Notification::make()
                            ->title($log->status === 'synced' ? 'Radius user tersinkron' : 'Sync Radius belum berhasil')
                            ->body($log->message)
                            ->color($log->status === 'synced' ? 'success' : 'warning')
                            ->send();
                    }),
                Tables\Actions\Action::make('suspend')
                    ->label('Isolir')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (RadiusUser $record): bool => $record->status !== 'suspended')
                    ->action(function (RadiusUser $record): void {
                        $log = app(FreeRadiusService::class)->suspendUser($record);

                        Notification::make()
                            ->title('Radius user diisolir')
                            ->body($log->message)
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (RadiusUser $record): bool => $record->status !== 'active')
                    ->action(function (RadiusUser $record): void {
                        $log = app(FreeRadiusService::class)->activateUser($record);

                        Notification::make()
                            ->title('Radius user aktif')
                            ->body($log->message)
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRadiusUsers::route('/'),
            'create' => Pages\CreateRadiusUser::route('/create'),
            'edit' => Pages\EditRadiusUser::route('/{record}/edit'),
        ];
    }
}
