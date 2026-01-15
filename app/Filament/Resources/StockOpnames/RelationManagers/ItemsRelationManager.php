<?php

namespace App\Filament\Resources\StockOpnames\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\CreateAction;
use Filament\Support\Enums\Width;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use App\Models\Stock;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->options(function(){
                        $options = [];
                        Product::select('code','name','id')
                                    ->orderBy('types','asc')
                                    ->chunk(100, function ($products) use (&$options) {
                                        foreach ($products as $product) {
                                            $options[$product->id] = $product->code_and_name;
                                        }
                                    });
                        return $options;
                    })
                    ->searchable()
                    ->required(),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.code')
            ->heading('Produk Stock Opname')
            ->description('HPP : Harga Pokok Per Unit (bisa rata-rata). Jika dikosongkan atau 0, maka tidak akan diperhitungkan saat penghitungan Omset.')
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->sortable()
                    ->wrap()
                    ->searchable(),
                TextColumn::make('system_qty')
                    ->label('Stok Sistem')
                    ->sortable(),
                TextInputColumn::make('actual_qty')
                    ->label('Stok Aktual')
                    ->disabled(fn()=> $this->ownerRecord->status != 'On Check')
                    ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                    ->afterStateUpdated(function ($record, $state) {
                        $record->discrepancy_qty = $state - $record->system_qty;
                        $record->save();
                    }),
                TextColumn::make('discrepancy_qty')
                    ->label('Selisih')
                    ->sortable()
                    ->toggleable()
                    ->color(fn ($state) => $state == 0 ? 'success' : 'danger'),
                TextInputColumn::make('hpp')
                    ->label('HPP')
                    ->disabled(fn()=> $this->ownerRecord->status != 'On Check')
                    ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0),
                TextInputColumn::make('notes')
                    ->label('Catatan')
                    ->disabled(fn()=> $this->ownerRecord->status != 'On Check')
                    ->toggleable(),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::Small)
                    ->label('Tambah Produk')
                    ->mutateDataUsing(function (array $data): array {
                        $stock = Stock::where('product_id', $data['product_id'])
                                    ->where('warehouse_id', $this->ownerRecord->warehouse_id)
                                    ->first();
                        $data['system_qty'] = $stock ? $stock->qty : 0;;
                        $data['actual_qty'] = 0;
                        $data['discrepancy_qty'] = $stock ? $stock->qty * -1 : 0;

                        return $data;
                    })
                    ->visible(fn()=> $this->ownerRecord->status == 'On Check')
            ])
            ->recordActions([
                ActionGroup::make([
                    // Tables\Actions\ViewAction::make(),
                    // Tables\Actions\EditAction::make(),
                    DeleteAction::make()
                        ->visible(fn()=> $this->ownerRecord->status == 'On Check'),
                    // Tables\Actions\ForceDeleteAction::make(),
                    // Tables\Actions\RestoreAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                //     Tables\Actions\ForceDeleteBulkAction::make(),
                //     Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
