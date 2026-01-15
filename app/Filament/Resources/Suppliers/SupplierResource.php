<?php

namespace App\Filament\Resources\Suppliers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Suppliers\RelationManagers\ContactsRelationManager;
use App\Filament\Resources\Suppliers\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Components\Split;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/supplier.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil')
                    ->description('Profil Supplier')
                    ->aside()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Perusahaan / Nama Usaha')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_alias')
                            ->label('Nama Alias')
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label('Alamat Utama')
                            ->helperText('Alamat Utama otomatis akan menjadi Alamat Pengiriman dengan nama "PUSAT".')
                            ->required(),
                        TextInput::make('city')
                            ->label('Kota')
                            ->maxLength(255)
                            ->default(null),
                        TextInput::make('category')
                            ->maxLength(255),
                        Textarea::make('notes'),
                    ])
                    ->columns(2),
                Section::make('Kontak')
                    ->description('Kontak Utama')
                    ->aside()
                    ->schema([
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->prefixIcon('heroicon-m-phone')
                            ->maxLength(20),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->prefixIcon('heroicon-m-at-symbol')
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->prefixIcon('heroicon-m-globe-alt')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Legalitas')
                    ->description('Dokumen Legalitas')
                    ->aside()
                    ->schema([
                        TextInput::make('ktp')
                            ->label('KTP')
                            ->helperText('Diisi jika supplier adalah perorangan.')
                            ->maxLength(16),
                        TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(21)
                            ->mask('99.999.999.9-999.999')
                            ->placeholder('00.000.000.0-000.000')
                            ->default(null),
                    ])
                    ->columns(2)
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_alias')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ContactsRelationManager::class,
            AddressesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'view' => ViewSupplier::route('/{record}'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
