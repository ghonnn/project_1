<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouterScriptTemplateResource\Pages;
use App\Filament\Support\AdminOptions;
use App\Models\RouterScriptTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RouterScriptTemplateResource extends Resource
{
    protected static ?string $model = RouterScriptTemplate::class;

    protected static ?string $navigationGroup = 'Jaringan';

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket-square';

    protected static ?string $navigationLabel = 'Script Templates';

    protected static ?string $modelLabel = 'Router Script Template';

    protected static ?string $pluralModelLabel = 'Router Script Templates';

    protected static ?int $navigationSort = 55;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('Tenant')
                    ->helperText('Kosongkan untuk global template semua tenant.')
                    ->options(fn () => AdminOptions::tenants())
                    ->searchable(),
                Forms\Components\Select::make('vendor')
                    ->options(['mikrotik' => 'MikroTik'])
                    ->default('mikrotik')
                    ->required(),
                Forms\Components\Select::make('os_version')
                    ->options(['ROS6' => 'RouterOS v6', 'ROS7' => 'RouterOS v7'])
                    ->required(),
                Forms\Components\Select::make('script_type')
                    ->options(['PPPoE' => 'PPPoE', 'Hotspot' => 'Hotspot'])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state): void {
                        if (blank($get('template_body')) && $state) {
                            $set('template_body', RouterScriptTemplate::defaultTemplate($state));
                        }
                    }),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\Textarea::make('template_body')
                    ->label('Template Body')
                    ->helperText('Variables: {{radius_service}}, {{radius_server_ip}}, {{radius_secret}}, {{auth_port}}, {{acct_port}}, {{router_hostname}}, {{interim_update}}, {{service_profile}}')
                    ->rows(14)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('variables_schema')
                    ->label('Variables Schema')
                    ->keyLabel('Variable')
                    ->valueLabel('Description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->default('Global')->searchable(),
                Tables\Columns\TextColumn::make('vendor')->badge(),
                Tables\Columns\TextColumn::make('os_version')->badge(),
                Tables\Columns\TextColumn::make('script_type')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRouterScriptTemplates::route('/'),
            'create' => Pages\CreateRouterScriptTemplate::route('/create'),
            'edit' => Pages\EditRouterScriptTemplate::route('/{record}/edit'),
        ];
    }
}
