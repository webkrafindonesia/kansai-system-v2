<?php

namespace App\Filament\Resources\Mutations;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Mutations\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Mutations\Pages\ListMutations;
use App\Filament\Resources\Mutations\Pages\CreateMutation;
use App\Filament\Resources\Mutations\Pages\ViewMutation;
use App\Filament\Resources\Mutations\Pages\EditMutation;
use App\Filament\Resources\MutationResource\Pages;
use App\Filament\Resources\MutationResource\RelationManagers;
use App\Models\Mutation;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Contracts\Support\Htmlable;

class MutationResource extends Resource
{
    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $model = Mutation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/back-sorting.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Logistik';

    protected static ?string $title = 'Mutasi Barang';

    protected static ?string $label = 'Mutasi Barang';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status')
                    ->description('Status Mutasi Barang')
                    ->aside()
                    ->schema([
                        Placeholder::make('is_processed')
                            ->label('Status')
                            ->content(fn (?Mutation $record): string => $record ? ($record->is_processed ? 'Sudah Diproses' : 'Belum Diproses') : 'Belum Diproses'),
                    ]),
                Section::make('Mutasi Barang')
                    ->description('Informasi Mutasi Barang')
                    ->aside()
                    ->schema([
                        Select::make('origin')
                            ->label('Gudang Asal')
                            ->options(fn()=>Warehouse::pluck('name','code'))
                            ->searchable()
                            ->required(),
                        Select::make('destination')
                            ->label('Gudang Tujuan')
                            ->options(fn()=>Warehouse::pluck('name','code'))
                            ->searchable()
                            ->required()
                            ->different('origin'),
                        DateTimePicker::make('date')
                            ->label('Tanggal Mutasi')
                            ->required()
                            ->seconds(false)
                            ->default(now()),
                        TextInput::make('reference')
                            ->maxLength(255),
                    ])->columns(2)
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('origin')
                    ->searchable(),
                TextColumn::make('destination')
                    ->searchable(),
                TextColumn::make('reference')
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
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMutations::route('/'),
            'create' => CreateMutation::route('/create'),
            'view' => ViewMutation::route('/{record}'),
            'edit' => EditMutation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchResultTitle($record): string | Htmlable
    {
        return 'Mutation from '.$record->origin.' to '.$record->destination;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'ID' => $record->id,
        ];
    }
}
