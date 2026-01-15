<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Kontak';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('position')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->tel()
                    ->prefixIcon('heroicon-m-phone')
                    ->maxLength(20),
                TextInput::make('email')
                    ->email()
                    ->prefixIcon('heroicon-m-at-symbol')
                    ->maxLength(255),
                Textarea::make('notes')
                    ->maxLength(1000),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('position'),
                TextColumn::make('phone'),
                TextColumn::make('email'),
                TextColumn::make('notes')
                    ->html()
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Kontak baru')
                    ->modalWidth(Width::Small)
                    ->modalHeading('Kontak Baru'),
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
}
