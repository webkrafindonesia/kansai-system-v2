<?php

namespace App\Filament\Resources\Mutations\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Support\Enums\Width;
use Filament\Actions\EditAction;
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
                    ->label('Produk')
                    ->required()
                    ->options(function ($get, $record, $livewire) {
                        // Ambil ownerRecord (record induk dari relation manager)
                        $ownerRecord = $livewire->ownerRecord;

                        if (! $ownerRecord) {
                            return [];
                        }

                        // Filter product berdasarkan warehouse_id di ownerRecord
                        $stocks = Stock::query()
                            ->where('warehouse_id', $ownerRecord->origin_warehouse->id)
                            ->with('product')
                            ->get();

                        $options = [];
                        foreach($stocks as $stock){
                            $options[$stock->product->id] = '['.$stock->product->code.'] '.$stock->product->name .' ('.$stock->qty.' '.$stock->uom.' tersedia)';
                        }
                        return $options;
                    })
                    ->live()
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function (?string $state, callable $set) {
                        $product = Product::find($state);
                        $set('uom', $product->uom);
                    }),
                TextInput::make('qty')
                    ->reactive()
                    ->numeric()
                    ->rule(function ($get, $record, $livewire) {
                        $productId = $get('product_id');
                        $warehouseId = $livewire->ownerRecord->origin_warehouse->id;

                        $max = Stock::where('product_id', $productId)
                            ->where('warehouse_id', $warehouseId)
                            ->value('qty');

                        return "max:$max";
                    })
                    ->required(),
                TextInput::make('uom')
                    ->label('Satuan')
                    ->required()
                    ->readonly(),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                TextColumn::make('product')
                    ->label('Produk')
                    ->formatStateUsing(fn($state) => '['.$state->code.'] '.$state->name)
                    ->searchable(),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->sortable(),
                TextColumn::make('uom')
                    ->label('Satuan')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Produk')
                    ->hidden(fn (RelationManager $livewire): bool => $livewire->ownerRecord->is_processed)
                    ->modalWidth(Width::Small),
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn (RelationManager $livewire): bool => $livewire->ownerRecord->is_processed)
                    ->modalWidth(Width::Small),
                DeleteAction::make()
                    ->hidden(fn (RelationManager $livewire): bool => $livewire->ownerRecord->is_processed),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
