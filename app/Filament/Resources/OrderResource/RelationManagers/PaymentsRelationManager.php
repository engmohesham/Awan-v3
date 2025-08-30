<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الدفعة')
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
                
                Tables\Columns\TextColumn::make('sender_phone')
                    ->label('هاتف المرسل')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة دفعة جديدة'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\Action::make('approve_payment')
                    ->label('موافقة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الموافقة على الدفع')
                    ->modalDescription('هل أنت متأكد من الموافقة على هذا الدفع؟')
                    ->modalSubmitActionLabel('نعم، أوافق')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function ($record) {
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
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                Tables\Actions\Action::make('reject_payment')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد رفض الدفع')
                    ->modalDescription('هل أنت متأكد من رفض هذا الدفع؟')
                    ->modalSubmitActionLabel('نعم، أرفض')
                    ->modalCancelActionLabel('إلغاء')
                    ->form([
                        Forms\Components\Textarea::make('failure_reason')
                            ->label('سبب الرفض')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'failed',
                            'failure_reason' => $data['failure_reason'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('تم رفض الدفع بنجاح')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                Tables\Actions\Action::make('view_proof')
                    ->label('عرض إثبات الدفع')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => $record->proof_image ? \Illuminate\Support\Facades\Storage::url($record->proof_image) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->proof_image !== null),
                
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
}
