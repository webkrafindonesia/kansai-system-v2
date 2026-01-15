<?php

namespace App\Filament\Resources\DeliveryOrders;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\DeliveryOrders\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\DeliveryOrders\Pages\ListDeliveryOrders;
use App\Filament\Resources\DeliveryOrders\Pages\CreateDeliveryOrder;
use App\Filament\Resources\DeliveryOrders\Pages\ViewDeliveryOrder;
use App\Filament\Resources\DeliveryOrders\Pages\EditDeliveryOrder;
use App\Filament\Resources\DeliveryOrderResource\Pages;
use App\Filament\Resources\DeliveryOrderResource\RelationManagers;
use App\Models\DeliveryOrder;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryOrderResource extends Resource
{
    protected static ?string $model = DeliveryOrder::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/loading-truck.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Logistik';

    protected static ?string $title = 'Pengiriman';

    protected static ?string $label = 'Pengiriman';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status')
                    ->description('Status Pengiriman')
                    ->aside()
                    ->schema([
                        Placeholder::make('processed_at')
                            ->label('Waktu proses')
                            ->content(fn ($record) => $record->processed_at ? $record->processed_at->format('d M Y H:i') : '-'),
                        Placeholder::make('processed_by')
                            ->label('Diproses oleh')
                            ->content(fn ($record) => $record->processed_by ?? '-'),
                    ])
                    ->columns(2)
                    ->visible(fn($record)=> $record && !is_null($record->processed_at)),
                Section::make('Delivery Order')
                    ->description('Informasi Delivery Order')
                    ->aside()
                    ->schema([
                        Select::make('sales_order_id')
                            ->label('Sales Order')
                            ->relationship('salesOrder', 'salesorder_no')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->suffixAction(function ($state) {
                                if (! $state) return null;

                                return Action::make('view_sales_order')
                                    ->label('Lihat')
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->color('info')
                                    ->url(fn () => route('filament.admin.resources.sales-orders.view', $state))
                                    ->openUrlInNewTab();
                            }),
                        DatePicker::make('delivery_date')
                            ->label('Tanggal Pengiriman')
                            ->native(false)
                            ->required(),
                    ]),
                Section::make('Gudang')
                    ->description('Informasi Gudang Pengiriman')
                    ->aside()
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Section::make('Pengiriman')
                    ->description('Informasi Pengiriman')
                    ->aside()
                    ->schema([
                        TextInput::make('courier_type')
                            ->label('Jenis Angkutan')
                            ->maxLength(255)
                            ->default(null),
                        TextInput::make('courier_name')
                            ->label('Nama Kurir')
                            ->maxLength(255)
                            ->default(null),
                        TextInput::make('vehicle_plate_no')
                            ->label('Nomor Polisi Kendaraan')
                            ->maxLength(255)
                            ->default(null),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('salesOrder.salesorder_no')
                    ->searchable(),
                TextColumn::make('delivery_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->searchable(),
                TextColumn::make('processed_at')
                    ->label('Status')
                    ->default('Draft')
                    ->formatStateUsing(fn ($state) => $state != 'Draft' ? 'Diproses pada<br/>'.$state : 'Draft')
                    ->color(fn ($state) => $state != 'Draft' ? 'success' : 'warning')
                    ->html()
                    ->searchable(),
                // Tables\Columns\TextColumn::make('courier_type')
                //     ->label('Jenis Angkutan')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('courier_name')
                //     ->label('Nama Kurir')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('vehicle_plate_no')
                //     ->label('Nomor Polisi Kendaraan')
                //     ->searchable(),
                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
                    ->sortable(),
            ])
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
                                $q->whereDate('delivery_date', '>=', $date)
                            )
                            ->when($data['endDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('delivery_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Pengiriman dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Pengiriman hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
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
            'index' => ListDeliveryOrders::route('/'),
            'create' => CreateDeliveryOrder::route('/create'),
            'view' => ViewDeliveryOrder::route('/{record}'),
            'edit' => EditDeliveryOrder::route('/{record}/edit'),
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
