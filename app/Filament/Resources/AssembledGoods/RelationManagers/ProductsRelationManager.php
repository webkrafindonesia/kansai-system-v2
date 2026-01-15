<?php

namespace App\Filament\Resources\AssembledGoods\RelationManagers;

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
use Filament\Forms\Components\Grid;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_breakdown_id')
                    ->columnSpanFull()
                    ->options(function(){
                        return Cache::rememberForever('raw_material_list', function () {
                            $products = Product::where('types','=','raw_material')
                                            ->active()
                                            ->get();
                            foreach($products as $product){
                                if($product->productCategory)
                                    $options[$product->id] = $product->productCategory->name.' - '.$product->name;
                                else
                                    $options[$product->id] = $product->name;
                            }
                            return $options;
                        });
                    })
                    ->live()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Bahan Baku / Artikel')
                    ->afterStateUpdated(function (?string $state, callable $set) {
                        if(!is_null($state)){
                            $article = Product::find($state);
                            $set('uom', $article->uom);
                        }
                        else{
                            $set('qty', 0);
                            $set('uom', '');
                        }
                    }),
                TextInput::make('qty')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                TextInput::make('uom')
                    ->required()
                    ->readonly(),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('product_reference')
                    ->label('Bahan Baku / Article')
                    ->formatStateUsing(function($state) {
                        if($state->productCategory)
                            return '['.$state->code.'] '.$state->productCategory->name.' - '.$state->name;
                        else
                            return '['.$state->code.'] '.$state->name;
                    }),
                TextColumn::make('qty')
                    ->label('Qty'),
                TextColumn::make('uom')
                    ->label('Satuan'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::Small),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Small),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    // {
    //     return $ownerRecord->types === "finish_good";
    // }
}
