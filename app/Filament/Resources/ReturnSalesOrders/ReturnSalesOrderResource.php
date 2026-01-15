<?php

namespace App\Filament\Resources\ReturnSalesOrders;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\ReturnSalesOrders\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\ReturnSalesOrders\Pages\ListReturnSalesOrders;
use App\Filament\Resources\ReturnSalesOrders\Pages\CreateReturnSalesOrder;
use App\Filament\Resources\ReturnSalesOrders\Pages\ViewReturnSalesOrder;
use App\Filament\Resources\ReturnSalesOrders\Pages\EditReturnSalesOrder;
use App\Filament\Resources\ReturnSalesOrderResource\Pages;
use App\Filament\Resources\ReturnSalesOrderResource\RelationManagers;
use App\Models\ReturnSalesOrder;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\SalesOrder;

class ReturnSalesOrderResource extends Resource
{
    protected static ?string $model = ReturnSalesOrder::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/return-purchase.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Penjualan';

    protected static ?string $label = 'Retur';

    protected static ?string $title = 'Retur';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Retur')
                    ->aside()
                    ->schema([
                        Select::make('sales_order_id')
                            ->label('No. Invoice') // pencarian berdasarkan invoice tapi yang disimpan tetep SO ID
                            ->required()
                            ->options(function(){
                                $salesOrder = SalesOrder::whereNotNull('invoice_no')
                                                    ->orderBy('invoice_no')
                                                    ->pluck('invoice_no','id');
                                return $salesOrder;
                            })
                            ->suffixAction(function ($state) {
                                if (! $state) return null;

                                return Action::make('view_sales_order')
                                    ->label('Lihat')
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->color('info')
                                    ->url(fn () => route('filament.admin.resources.sales-orders.view', $state))
                                    ->openUrlInNewTab();
                            })
                            ->searchable(),
                        DatePicker::make('return_date')
                            ->label('Tanggal Retur')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->maxDate(now()),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                        ])
                        ->columns(2),
                Section::make('Customer')
                    ->description('Informasi Customer')
                    ->aside()
                    ->schema([
                        Placeholder::make('customer_name')
                            ->label('Nama Customer')
                            ->content(fn($record)=>$record->customer->name),
                    ])
                    ->visible(fn($operation) => $operation != 'create')
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('salesOrder.invoice_no')
                    ->label('No. Invoice')
                    ->searchable(),
                TextColumn::make('salesOrder.salesorder_no')
                    ->label('No. Sales Order')
                    ->searchable(),
                TextColumn::make('return_date')
                    ->label('Tanggal Retur')
                    ->date()
                    ->sortable(),
                TextColumn::make('processed_at')
                    ->label('Diproses')
                    ->date()
                    ->formatStateUsing(fn($state)=>is_null($state)?'Pending':dateFormat($state))
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
                                $q->whereDate('return_date', '>=', $date)
                            )
                            ->when($data['endDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('return_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Retur dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Retur hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
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
            ItemsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReturnSalesOrders::route('/'),
            'create' => CreateReturnSalesOrder::route('/create'),
            'view' => ViewReturnSalesOrder::route('/{record}'),
            'edit' => EditReturnSalesOrder::route('/{record}/edit'),
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
