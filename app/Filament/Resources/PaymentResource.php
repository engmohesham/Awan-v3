<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $modelLabel = 'دفعة';
    protected static ?string $pluralModelLabel = 'المدفوعات';
    protected static ?string $navigationLabel = 'المدفوعات';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationGroup = 'إدارة المبيعات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->label('الطلب')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('المستخدم')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                
                Forms\Components\TextInput::make('currency')
                    ->label('العملة')
                    ->default('EGP')
                    ->required()
                    ->maxLength(3),
                
                Forms\Components\Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'vodafone_cash' => 'فودافون كاش',
                        'instapay' => 'إنستا باي',
                    ])
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'معلق',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'cancelled' => 'ملغي',
                    ])
                    ->default('pending')
                    ->required(),
                
                Forms\Components\FileUpload::make('proof_image')
                    ->label('إثبات الدفع')
                    ->image()
                    ->directory('payment-proofs')
                    ->visibility('public'),
                
                Forms\Components\TextInput::make('sender_name')
                    ->label('اسم المرسل')
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('sender_phone')
                    ->label('هاتف المرسل')
                    ->maxLength(20),
                
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('تاريخ الدفع'),
                
                Forms\Components\TextInput::make('failure_reason')
                    ->label('سبب الفشل')
                    ->maxLength(500),
                
                // Customer details
                Forms\Components\Section::make('تفاصيل العميل')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('اسم العميل')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('customer_email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('رقم الهاتف')
                            ->maxLength(20),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vodafone_cash' => 'primary',
                        'instapay' => 'success',
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('sender_name')
                    ->label('اسم المرسل')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'معلق',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'cancelled' => 'ملغي',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'vodafone_cash' => 'فودافون كاش',
                        'instapay' => 'إنستا باي',
                    ]),
                
                Tables\Filters\SelectFilter::make('order')
                    ->relationship('order', 'order_number')
                    ->label('الطلب')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
