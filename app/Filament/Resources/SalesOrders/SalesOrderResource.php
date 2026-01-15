<?php

namespace App\Filament\Resources\SalesOrders;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\SalesOrders\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\SalesOrders\Pages\ListSalesOrders;
use App\Filament\Resources\SalesOrders\Pages\CreateSalesOrder;
use App\Filament\Resources\SalesOrders\Pages\ViewSalesOrder;
use App\Filament\Resources\SalesOrders\Pages\EditSalesOrder;
use App\Filament\Resources\SalesOrderResource\Pages;
use App\Filament\Resources\SalesOrderResource\RelationManagers;
use App\Models\SalesOrder;
use App\Models\Customer;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/add-shopping-cart--v1.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Penjualan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section::make('Purchase Order')
                //     ->description('Informasi Purchase Order Customer')
                //     ->aside()
                //     ->schema([
                //         Forms\Components\TextInput::make('purchaseorder_no')
                //             ->required()
                //             ->maxLength(255)
                //             ->unique(),
                //     ])
                //     ->visible(fn($record)=> $record && !is_null($record->purchaseorder_no)),
                Section::make('Sales Order')
                    ->description('Informasi Sales Order')
                    ->aside()
                    ->schema([
                        Placeholder::make('salesorder_no')
                            ->label('Nomor SO')
                            ->content(fn($record) => ($record)?$record->salesorder_no:'-')
                            ->columnSpanFull()
                            ->visible(fn($operation) => $operation != 'create'),
                        DatePicker::make('date')
                            ->label('Tanggal SO')
                            ->native(false)
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->required()
                            ->default(now()),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function(callable $set, $state){
                                if(!is_null($state)){
                                    $customer = Customer::find($state);
                                    $set('sales_id',$customer->sales_id);
                                    $set('discount_sales',$customer->discount_sales);
                                    $set('discount_company',$customer->discount_company);
                                }
                            }),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Sales dan Diskon')
                    ->description('Informasi Sales dan Diskon')
                    ->aside()
                    ->schema([
                        Select::make('sales_id')
                            ->label('Sales')
                            ->relationship('sales', 'name')
                            ->searchable()
                            ->live()
                            ->preload(),
                        TextInput::make('discount_sales')
                            ->label('Diskon Sales')
                            ->required()
                            ->suffix('%')
                            ->numeric()
                            ->live()
                            ->default(0),
                        TextInput::make('discount_company')
                            ->label('Diskon Perusahaan')
                            ->required()
                            ->suffix('%')
                            ->numeric()
                            ->live()
                            ->default(0),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('salesorder_no')
                    ->label('Sales Order No.')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('purchaseorder_no')
                //     ->label('PO No.')
                //     ->searchable(),
                TextColumn::make('date')
                    ->label('Tgl. SO')
                    ->date()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('sales.name')
                    ->searchable(),
                IconColumn::make('delivery_order_id')
                    ->label('Status')
                    ->default(0)
                    ->icon(function($state){
                        if($state == 0)
                            return 'heroicon-m-lock-open';
                        else
                            return 'heroicon-m-lock-closed';
                    })
                    ->color(function($state){
                        if($state == 0)
                            return 'warning';
                        else
                            return 'success';
                    })
                    ->alignCenter(),
                TextColumn::make('invoice_status')
                    ->label('Pembayaran')
                    ->alignCenter()
                    ->color(function($state){
                        return ($state == 'Belum Lunas') ? 'danger' : 'success';
                    })
                    ->sortable(),
            ])
            ->groups([
                'customer.name',
                'sales.name',
            ])
            ->defaultSort('date','desc')
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
                Filter::make('invoice_date_filter')
                    ->schema([
                        DatePicker::make('startDate')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->label('Tanggal Awal'),
                        DatePicker::make('endDate')
                            ->default(now())
                            ->native(false)
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(function ( $query, $data){
                        return $query
                            ->when($data['startDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('date', '>=', $date)
                            )
                            ->when($data['endDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Sales Order dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Sales Order hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ])
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
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesOrders::route('/'),
            'create' => CreateSalesOrder::route('/create'),
            'view' => ViewSalesOrder::route('/{record}'),
            'edit' => EditSalesOrder::route('/{record}/edit'),
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
