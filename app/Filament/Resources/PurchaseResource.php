<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $modelLabel = 'عملية شراء';
    protected static ?string $pluralModelLabel = 'المشتريات';
    protected static ?string $navigationLabel = 'المشتريات';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('المستخدم')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'title')
                    ->label('الدورة')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'bank_transfer' => 'تحويل بنكي',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                    ])
                    ->required(),
                Forms\Components\Select::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\FileUpload::make('payment_proof')
                    ->label('إثبات الدفع')
                    ->image()
                    ->directory('purchases/proofs'),
                Forms\Components\TextInput::make('sender_name_or_phone')
                    ->label('اسم المرسل أو رقم الهاتف')
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                    ])
                    ->sortable(),
                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('إثبات الدفع')
                    ->circular(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الشراء')
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->label('المستخدم')
                    ->placeholder('الكل')
                    ->preload(),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->label('الدورة')
                    ->placeholder('الكل')
                    ->preload(),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'bank_transfer' => 'تحويل بنكي',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                    ])
                    ->placeholder('الكل'),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                    ])
                    ->placeholder('الكل'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
