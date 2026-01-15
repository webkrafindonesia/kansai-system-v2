<?php

namespace App\Filament\Resources\Purchases;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Purchases\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Purchases\Pages\ListPurchases;
use App\Filament\Resources\Purchases\Pages\CreatePurchase;
use App\Filament\Resources\Purchases\Pages\ViewPurchase;
use App\Filament\Resources\Purchases\Pages\EditPurchase;
use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/sell-stock.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Logistik';

    protected static ?string $title = 'Pembelian';

    protected static ?string $label = 'Pembelian';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status')
                    ->description('Status Pembayaran')
                    ->aside()
                    ->schema([
                        Placeholder::make('payment_status')
                            ->label('Status Pembayaran')
                            ->content(fn($record)=>$record->payment_status),
                    ])
                    ->visible(fn($operation) => $operation != 'create'),
                Section::make('Pembelian')
                    ->description('Informasi Pembelian Barang')
                    ->aside()
                    ->schema([
                        Placeholder::make('purchase_no')
                            ->label('Kode')
                            ->content(fn($record)=>$record->purchase_no)
                            ->columnSpanFull()
                            ->visible(fn($operation) => $operation != 'create'),
                        // Forms\Components\TextInput::make('deliveryorder_no')
                        //     ->label('Nomor Surat Jalan')
                        //     ->maxLength(255)
                        //     ->default(null),
                        DatePicker::make('date')
                            ->label('Tanggal Penerimaan')
                            ->native(false)
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->required()
                            ->default(now()),
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make('Gudang')
                    ->description('Informasi Gudang')
                    ->aside()
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Gudang Penerimaan')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_no')
                    ->label('Kode')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('deliveryorder_no')
                //     ->label('Nomor Surat Jalan')
                //     ->searchable(),
                TextColumn::make('date')
                    ->label('Tanggal Terima')
                    ->date()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_count')
                    ->label('Jenis Item')
                    ->counts('items')
                    ->sortable()
                    ->suffix(' item'),
                TextColumn::make('items_sum_qty')
                    ->label('Qty Item')
                    ->sum('items','qty')
                    ->sortable()
                    ->suffix(' item'),
                TextColumn::make('items_sum_total_price')
                    ->label('Total Harga')
                    ->currency('IDR')
                    ->sum('items','total_price')
                    ->sortable(),
                IconColumn::make('is_accepted')
                    ->label('Terima Barang')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->alignCenter()
                    ->color(function($state){
                        return ($state == 'Belum Lunas') ? 'danger' : 'success';
                    })
                    ->sortable(),
            ])
            ->groups([
                'supplier.name'
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
                            $indicators['startDate'] = 'Pembelian dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Pembelian hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
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
            'index' => ListPurchases::route('/'),
            'create' => CreatePurchase::route('/create'),
            'view' => ViewPurchase::route('/{record}'),
            'edit' => EditPurchase::route('/{record}/edit'),
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
