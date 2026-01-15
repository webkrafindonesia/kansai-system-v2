<?php

namespace App\Filament\Resources\Sales\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\MaxWidth;
use App\Models\Customer;
use App\Filament\Resources\Customers\CustomerResource;

class CustomerRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->options(fn()=>Customer::pluck('name','id'))
                    ->required()
                    ->searchable(),
                // Forms\Components\TextInput::make('discount')
                //     ->suffix('%')
                //     ->numeric()
                //     ->default(0)
                //     ->minValue(0)
                //     ->maxValue(100)
                //     ->required()
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('discount_sales')
                    ->suffix('%'),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make()
                //     ->modalWidth(MaxWidth::Small),
            ])
            ->recordActions([
                // Tables\Actions\EditAction::make()
                //     ->modalWidth(MaxWidth::Small),
                // Tables\Actions\DeleteAction::make(),
                // // Tables\Actions\ForceDeleteAction::make(),
                // Tables\Actions\RestoreAction::make(),
                Action::make('Lihat Customer')
                    ->url(fn($record)=>CustomerResource::getUrl('view', ['record' => $record->id]))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->openUrlInNewTab()
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
