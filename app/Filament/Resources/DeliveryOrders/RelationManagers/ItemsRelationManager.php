<?php

namespace App\Filament\Resources\DeliveryOrders\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('qty')
                    ->required()
                    ->maxLength(255),
                TextInput::make('uom')
                    ->label('Satuan')
                    ->disabled()
                    ->maxLength(255),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode'),
                TextColumn::make('product.name')
                    ->label('Produk'),
                TextColumn::make('qty'),
                TextColumn::make('stocks_sum_qty')
                    ->label('Stok Tersedia')
                    ->alignCenter()
                    ->sum('stocks','qty')
                    ->default(0)
                    ->color(function($state, $record){
                        $stockQty = $state ?? 0;
                        if($stockQty <= 0){
                            return 'danger';
                        }
                        elseif($stockQty < $record->qty){
                            return 'warning';
                        }
                        else{
                            return 'success';
                        }
                    })
                    ->visible(fn($record)=> is_null($this->ownerRecord->processed_at)),
                TextColumn::make('qty_to_replenish')
                    ->label('Qty untuk Ditambah')
                    ->alignCenter()
                    ->default(function($record){
                        $stockQty = $record->stocks()->sum('qty') ?? 0;
                        $qtyToReplenish = $record->qty - $stockQty;
                        return ($qtyToReplenish > 0) ? $qtyToReplenish : '';
                    })
                    ->color(function($state){
                        if(is_numeric($state)){
                            return 'warning';
                        }
                    })
                    ->visible(fn($record)=> is_null($this->ownerRecord->processed_at)),
                TextColumn::make('uom'),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    // Tables\Actions\ViewAction::make(),
                    EditAction::make(),
                    // Tables\Actions\DeleteAction::make(),
                    // // Tables\Actions\ForceDeleteAction::make(),
                    // Tables\Actions\RestoreAction::make(),
                ])
            ])
            ->toolbarActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                //     // Tables\Actions\ForceDeleteBulkAction::make(),
                //     Tables\Actions\RestoreBulkAction::make(),
                // ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
