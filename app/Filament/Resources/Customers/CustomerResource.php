<?php

namespace App\Filament\Resources\Customers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Customers\RelationManagers\ContactsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\Split;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Sales;
use App\Models\SalesCustomer;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/budget.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil')
                    ->description('Profil Customer')
                    ->aside()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Perusahaan / Nama Usaha')
                            ->maxLength(255)
                            ->required()
                            ->default(null)
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->label('Alamat Utama')
                            ->helperText('Alamat Utama otomatis akan menjadi Alamat Pengiriman dengan nama "PUSAT".')
                            ->required()
                            ->default(null),
                        TextInput::make('city')
                            ->label('Kota')
                            ->maxLength(255)
                            ->default(null),
                        TextInput::make('category')
                            ->label('Kategori')
                            ->maxLength(255)
                            ->default(null),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->default(null),
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
                            ->maxLength(20)
                            ->default(null),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->prefixIcon('heroicon-m-at-symbol')
                            ->maxLength(255)
                            ->default(null),
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->prefixIcon('heroicon-m-globe-alt')
                            ->maxLength(255)
                            ->default(null),
                    ])
                    ->columns(2),
                Section::make('Sales')
                    ->description('Sales')
                    ->aside()
                    ->schema([
                        Select::make('sales_id')
                            ->label('Sales')
                            ->options(fn()=>Sales::pluck('name','id'))
                            ->searchable()
                            ->preload()
                    ])
                    ->columns(2),
                Section::make('Diskon')
                    ->description('Diskon Sales dan Perusahaan.')
                    ->aside()
                    ->schema([
                        TextInput::make('discount_sales')
                            ->label('Diskon Sales')
                            ->numeric()
                            ->suffix('%')
                            ->dehydrated(true)
                            ->default(0),
                        TextInput::make('discount_company')
                            ->label('Diskon Perusahaan')
                            ->helperText('0 artinya NETTO')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->default(0),
                    ])
                    ->columns(2),
                Section::make('Legalitas')
                    ->description('Dokumen Legalitas')
                    ->aside()
                    ->schema([
                        TextInput::make('ktp')
                            ->label('KTP')
                            ->helperText('Diisi jika customer adalah perorangan.')
                            ->maxLength(16)
                            ->default(null),
                        TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(21)
                            ->mask('99.999.999.9-999.999')
                            ->placeholder('00.000.000.0-000.000')
                            ->default(null),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->html()
                    ->wrap()
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('sales.name')
                    ->label('Sales')
                    ->searchable(),
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
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
