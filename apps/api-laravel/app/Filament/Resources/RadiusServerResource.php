<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiusServerResource\Pages;
use App\Filament\Resources\RadiusServerResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\RadiusServer;
use App\Services\FreeRadiusService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RadiusServerResource extends Resource
{
    protected static ?string $model = RadiusServer::class;

    protected static ?string $navigationGroup = 'Radius';

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationLabel = 'Server';

    protected static ?string $modelLabel = 'Server Radius';

    protected static ?string $pluralModelLabel = 'Server Radius';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable(),
                Forms\Components\TextInput::make('name')->required()->maxLength(80),
                Forms\Components\TextInput::make('host')->required()->maxLength(120),
                Forms\Components\TextInput::make('auth_port')->numeric()->default(1812)->required(),
                Forms\Components\TextInput::make('acct_port')->numeric()->default(1813)->required(),
                Forms\Components\TextInput::make('shared_secret')->password()->revealable()->required()->maxLength(80),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                    ->default('active')
                    ->required(),
                Forms\Components\TextInput::make('last_test_status')->disabled(),
                Forms\Components\Textarea::make('last_test_message')->disabled()->columnSpanFull(),
                Forms\Components\DateTimePicker::make('last_tested_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('host')->searchable(),
                Tables\Columns\TextColumn::make('auth_port'),
                Tables\Columns\TextColumn::make('acct_port'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('last_test_status')->badge()->color(fn (?string $state): string => match ($state) {
                    'success' => 'success',
                    'warning' => 'warning',
                    'failed' => 'danger',
                    default => 'gray',
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('test')
                    ->label('Test')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (RadiusServer $record): void {
                        $result = app(FreeRadiusService::class)->testServerConnection($record);

                        Notification::make()
                            ->title('Test Radius selesai')
                            ->body($result['message'])
                            ->color(match ($result['status']) {
                                'success' => 'success',
                                'failed' => 'danger',
                                default => 'warning',
                            })
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
            'index' => Pages\ListRadiusServers::route('/'),
            'create' => Pages\CreateRadiusServer::route('/create'),
            'edit' => Pages\EditRadiusServer::route('/{record}/edit'),
        ];
    }
}
