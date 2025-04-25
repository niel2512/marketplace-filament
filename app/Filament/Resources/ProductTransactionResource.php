<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Shoe;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\PromoCode;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ProductTransaction;
use Filament\Forms\FormsComponent;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\View\TablesRenderHook;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductTransactionResource\Pages;
use App\Filament\Resources\ProductTransactionResource\RelationManagers;
use Filament\Notifications\Notification;

class ProductTransactionResource extends Resource
{
    protected static ?string $model = ProductTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //schema itu seperti div yaitu pembungkus
                Wizard::make([
                Wizard\Step::make('Product and Price')
                    ->schema([

                        Grid::make(2)
                        ->schema([
                        Forms\Components\Select::make('shoe_id')
                        ->relationship('shoe', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        //ini dijalankan apabila belum ada shoe_id
                        ->afterStateUpdated(function (callable $set, callable $get, $state) { //state adalah id shoe_id yang dipilih
                            //cek apakah ada id shoe_id di DB
                            $shoe = Shoe::find($state);
                            $price = $shoe ? $shoe->price : 0;
                            $quantity = $get('quantity') ?? 1;
                            $subTotalAmount = $price * $quantity;

                            $set('price', $price);
                            $set('sub_total_amount', $subTotalAmount);

                            $discount = $get('discount') ?? 0;
                            $grandTotalAmount = $subTotalAmount - $discount;
                            $set('grand_total_amount', $grandTotalAmount);

                            $sizes = $shoe ? $shoe->sizes->pluck('size','id')->toArray() : [];
                            $set('shoe_sizes', $sizes);
                        })
                        //ini yang dijalankan apabila sudah ada shoe_id
                        ->afterStateHydrated(function (callable $get, callable $set, $state) {
                            $shoeId = $state;
                            if ($shoeId){
                                $shoe = Shoe::find($shoeId);
                                $sizes = $shoe ? $shoe->sizes->pluck('size','id')->toArray() : [];
                                $set('shoe_sizes', $sizes);
                            }
                        }),

                        Forms\Components\Select::make('shoe_size')
                        ->label('Shoe Size')
                        ->Options(function (callable $get){
                            $sizes = $get ('shoe_sizes');
                            return is_array($sizes) ? $sizes : [];
                        })
                        ->required()
                        ->live(),

                        Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->required()
                        ->prefix('Qty')
                        ->live()
                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                            $price = $get('price');
                            $quantity = $state;
                            $subTotalAmount = $price * $quantity;

                            $set('sub_total_amount', $subTotalAmount);

                            $discount = $get('discount_amount') ?? 0;
                            $grandTotalAmount = $subTotalAmount - $discount;
                            $set('grand_total_amount', $grandTotalAmount);
                        }),

                        Forms\Components\Select::make('promo_code_id')
                        ->relationship('promoCode', 'code')
                        ->preload()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                            $subTotalAmount = $get('sub_total_amount');
                            $promoCode = PromoCode::find($state); //mengecek apakah ada id promo_code di DB
                            $discount = $promoCode ? $promoCode->discount_amount : 0; //kalo ada lgsg panggil kalo belum set 0

                            $set('discount_amount', $discount);
                                                    
                            $grandTotalAmount = $subTotalAmount - $discount;
                            $set('grand_total_amount', $grandTotalAmount);
                        }),
                        Forms\Components\TextInput::make('sub_total_amount')
                        ->required()
                        ->readOnly()
                        ->numeric()
                        ->prefix('Rp'),

                        Forms\Components\TextInput::make('grand_total_amount')
                        ->required()
                        ->readOnly()
                        ->numeric()
                        ->prefix('Rp'),

                        Forms\Components\TextInput::make('discount_amount')
                        ->required()
                        // ->readOnly()
                        ->numeric()
                        ->prefix('Rp'),
                    ]),
                ]),
                //ini step kedua
                Wizard\Step::make('Customer Information')
                ->schema([
                    Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                        ->required()
                        ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                        ->required()
                        ->email(),

                        Forms\Components\TextInput::make('address')
                        ->required()
                        ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                        ->required()
                        ->maxLength(255),

                        Forms\Components\TextInput::make('post_code')
                        ->required()
                        ->maxLength(255),
                    ]),
                ]),
                //ini step kedua
                Wizard\Step::make('Payment Information')
                ->schema([
                    Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('booking_tx_id')
                        ->required()
                        ->maxLength(255),

                        ToggleButtons::make('is_paid')
                        ->label('Payment Status')
                        ->boolean()
                        ->grouped()
                        ->icons([
                             true => 'heroicon-o-check',
                            false => 'heroicon-o-x-mark',
                        ])
                        ->required(),

                        Forms\Components\FileUpload::make('proof')
                        ->required()
                        ->image()
                    ]),
                ]),
            ])
            //agar ukuran kolomnya menyesuaikan dengan ukuran layar 
            ->columnSpan('full')
            ->columns(1)//ini untuk menampilkan 1 kolom
            ->skippable() //ini agar wizard bisa di skip
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\ImageColumn::make('shoe.thumbnail')
                ->label('Shoes'),

                Tables\Columns\TextColumn::make('name')
                ->label('Customer Name')
                ->searchable(),

                Tables\Columns\TextColumn::make('booking_tx_id')
                ->label('Transaction ID')
                ->searchable(),

                Tables\Columns\IconColumn::make('is_paid')
                ->label('Payment Status')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger')
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                //
                SelectFilter::make('shoe_id')
                ->label('Shoe')
                ->relationship('shoe', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(), //tombol edit
                Tables\Actions\ViewAction::make() ->label('Detail'), //tombol view

                Tables\Actions\Action::make('Approve') //membuat tombol approve
                ->action(function (ProductTransaction $record) {
                    $record->is_paid = true;
                    $record->save();

                    //trigger the custom notification
                    Notification::make()
                    ->title('Payment Approved')
                    ->icon('heroicon-o-check-circle')
                    ->success()
                    ->body('Pembayaran berhasil di approve')
                    ->send();
                })
                ->color('success')
                ->requiresConfirmation()
                ->icon('heroicon-o-check-circle')
                ->visible(fn (ProductTransaction $record): bool => !$record->is_paid) //tombol approve akan muncul kalau status belum bayar
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
            'index' => Pages\ListProductTransactions::route('/'),
            'create' => Pages\CreateProductTransaction::route('/create'),
            'edit' => Pages\EditProductTransaction::route('/{record}/edit'),
        ];
    }
}
