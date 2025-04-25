<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoCodeResource\Pages;
use App\Filament\Resources\PromoCodeResource\RelationManagers;
use App\Models\PromoCode;
use FFI;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;
    // mengatur icon di filament menggunakan heroicons.com (cuma ganti nama iconnya)
    protected static ?string $navigationIcon = 'heroicon-o-ticket'; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('discount_amount')
                ->required()
                ->numeric()
                ->prefix('IDR'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('code')
                ->label('Kode Promo')
                ->searchable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'edit' => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }
}
