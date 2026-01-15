<?php

namespace App\Filament\Resources\Users;

use Filament\Schemas\Schema;
use BezhanSalleh\FilamentShield\FilamentShield;
use Filament\Forms\Components\Select;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 9;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/group-skin-type-7.png';

    public static function getNavigationLabel(): string
    {
        return trans('filament-users::user.resource.label');
    }

    public static function getPluralLabel(): string
    {
        return trans('filament-users::user.resource.label');
    }

    public static function getLabel(): string
    {
        return trans('filament-users::user.resource.single');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-users.group');
    }

    public function getTitle(): string
    {
        return trans('filament-users::user.resource.title.resource');
    }

    public static function form(Schema $schema): Schema
    {
        $rows = [
            TextInput::make('name')
                ->required()
                ->label(trans('filament-users::user.resource.name')),
            TextInput::make('email')
                ->email()
                ->required()
                ->label(trans('filament-users::user.resource.email')),
            TextInput::make('password')
                ->label(trans('filament-users::user.resource.password'))
                ->password()
                ->maxLength(255)
                ->dehydrateStateUsing(static function ($state, $record) use ($schema) {
                    return !empty($state)
                        ? Hash::make($state)
                        : $record->password;
                }),
        ];


        // if (config('filament-users.shield') && class_exists(FilamentShield::class)) {
        //     $rows[] = Select::make('roles')
        //         ->multiple()
        //         ->preload()
        //         ->relationship('roles', 'name')
        //         ->label(trans('filament-users::user.resource.roles'));
        // }

        $schema->components($rows);

        return $schema;
    }

    public static function table(Table $table): Table
    {
        if(class_exists( STS\FilamentImpersonate\Tables\Actions\Impersonate::class) && config('filament-users.impersonate')){
            $table->recordActions([Impersonate::make('impersonate')]);
        }
        $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label(trans('filament-users::user.resource.name')),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->label(trans('filament-users::user.resource.email')),
                // IconColumn::make('email_verified_at')
                //     ->boolean()
                //     ->sortable()
                //     ->searchable()
                //     ->label(trans('filament-users::user.resource.email_verified_at')),
                TextColumn::make('created_at')
                    ->label(trans('filament-users::user.resource.created_at'))
                    ->dateTime('M j, Y')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(trans('filament-users::user.resource.updated_at'))
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            // ->filters([
            //     Tables\Filters\Filter::make('verified')
            //         ->label(trans('filament-users::user.resource.verified'))
            //         ->query(fn(Builder $query): Builder => $query->whereNotNull('email_verified_at')),
            //     Tables\Filters\Filter::make('unverified')
            //         ->label(trans('filament-users::user.resource.unverified'))
            //         ->query(fn(Builder $query): Builder => $query->whereNull('email_verified_at')),
            // ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                ]),
            ]);
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
