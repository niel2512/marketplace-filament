<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShoeResource\Pages;
use App\Filament\Resources\ShoeResource\RelationManagers;
use Filament\Forms\Components\Fieldset; //import komponen form fieldset
use Filament\Tables\Filters\SelectFilter; //import filter table select filter
use App\Models\Shoe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShoeResource extends Resource
{
    protected static ?string $model = Shoe::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
                Fieldset::make('Details') //fieldset untuk mengelompokkan field jadi ada kolom yang beda
                ->schema([

                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('price')
                ->numeric()
                ->required()
                ->prefix('IDR'),

                Forms\Components\FileUpload::make('thumbnail')
                ->image()
                ->required(),

                //repeater untuk menambah field yang sama bisa upload lebih dari satu foto
                Forms\Components\Repeater::make('photos')
                ->relationship('photos') //selalu cek nama relasi di model
                ->schema([
                    Forms\Components\FileUpload::make('photo') //photo adalah nama kolom di database pada table photos
                    ->required(),
                ]),

                Forms\Components\Repeater::make('sizes')
                ->relationship('sizes')
                ->schema([
                    Forms\Components\TextInput::make('size')
                    ->required(),
                ]),

            ]),
            
            Fieldset::make('Additional')
            ->schema([

                Forms\Components\Textarea::make('about')
                ->required()
                ->maxLength(255),

                Forms\Components\Select::make('is_popular')
                //select untuk menampilkan data boolean true/false
                //dari boolean diubah jadi string
                ->options([
                    true => 'Popular',
                    false => 'Not Popular',
                ])->required(),
                
                //preload untuk menampilkan data di select tanpa harus di klik dulu
                Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->preload()
                ->required(),

                Forms\Components\Select::make('brand_id')
                ->relationship('brand', 'name')
                ->preload()
                ->required(),

                Forms\Components\TextInput::make('stock')
                ->prefix('QTY')
                ->numeric()
                ->required(),

                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                ->searchable(),

                Tables\Columns\ImageColumn::make('thumbnail')
                ->label('Thumbnail'),

                Tables\Columns\IconColumn::make('is_popular')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger')
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->label('Popular'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                ->label('category')
                ->relationship('category', 'name'),

                SelectFilter::make('brand_id')
                ->label('brand')
                ->relationship('brand', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShoes::route('/'),
            'create' => Pages\CreateShoe::route('/create'),
            'edit' => Pages\EditShoe::route('/{record}/edit'),
        ];
    }
}
