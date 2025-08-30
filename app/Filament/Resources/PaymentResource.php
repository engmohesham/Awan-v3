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
use Illuminate\Support\Facades\Storage;

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
                Forms\Components\Section::make('معلومات الدفعة الأساسية')
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('إثبات الدفع والتفاصيل')
                    ->schema([
                        Forms\Components\FileUpload::make('proof_image')
                            ->label('إثبات الدفع')
                            ->image()
                            ->imageEditor()
                            ->directory('payment-proofs')
                            ->visibility('public')
                            ->helperText('يمكنك تحرير الصورة قبل الحفظ')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                            ->maxSize(5120),
                        
                        Forms\Components\TextInput::make('sender_name')
                            ->label('اسم المرسل')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('sender_phone')
                            ->label('هاتف المرسل')
                            ->maxLength(20),
                        
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('تاريخ الدفع'),
                        
                        Forms\Components\TextInput::make('failure_reason')
                            ->label('سبب الفشل')
                            ->maxLength(500),
                    ])
                    ->columns(2),

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

                Forms\Components\Section::make('ملاحظات إضافية')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('ملاحظات إضافية حول الدفعة')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
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
                Tables\Actions\Action::make('approve_payment')
                    ->label('موافقة على الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الموافقة على الدفع')
                    ->modalDescription('هل أنت متأكد من الموافقة على هذا الدفع؟ سيتم تغيير حالة الطلب إلى مدفوع.')
                    ->modalSubmitActionLabel('نعم، أوافق')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function (Payment $record) {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                        
                        // تحديث حالة الطلب
                        $record->order->update([
                            'payment_status' => 'paid',
                            'status' => 'paid',
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('تمت الموافقة على الدفع بنجاح')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Payment $record) => $record->status === 'pending'),
                
                Tables\Actions\Action::make('reject_payment')
                    ->label('رفض الدفع')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد رفض الدفع')
                    ->modalDescription('هل أنت متأكد من رفض هذا الدفع؟ سيتم تغيير حالة الدفع إلى فشل.')
                    ->modalSubmitActionLabel('نعم، أرفض')
                    ->modalCancelActionLabel('إلغاء')
                    ->form([
                        Forms\Components\Textarea::make('failure_reason')
                            ->label('سبب الرفض')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $record->update([
                            'status' => 'failed',
                            'failure_reason' => $data['failure_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('تم رفض الدفع بنجاح')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Payment $record) => $record->status === 'pending'),
                
                Tables\Actions\Action::make('view_proof')
                    ->label('عرض إثبات الدفع')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Payment $record) => $record->proof_image ? Storage::url($record->proof_image) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn (Payment $record) => $record->proof_image !== null),
                
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
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
