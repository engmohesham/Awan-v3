<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل الطلب'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات الطلب')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('رقم الطلب'),
                        
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('المستخدم'),
                        
                        Infolists\Components\TextEntry::make('course.title')
                            ->label('الدورة'),
                        
                        Infolists\Components\TextEntry::make('amount')
                            ->label('المبلغ')
                            ->money('EGP'),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->label('حالة الطلب')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'success',
                                'cancelled' => 'danger',
                                'expired' => 'gray',
                                'paid' => 'success',
                                'failed' => 'danger',
                            }),
                        
                        Infolists\Components\TextEntry::make('payment_status')
                            ->label('حالة الدفع')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'paid' => 'success',
                                'failed' => 'danger',
                                'refunded' => 'info',
                            }),
                        
                        Infolists\Components\TextEntry::make('expires_at')
                            ->label('تاريخ انتهاء الصلاحية')
                            ->dateTime(),
                        
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('تفاصيل العميل')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer_name')
                            ->label('اسم العميل'),
                        
                        Infolists\Components\TextEntry::make('customer_email')
                            ->label('البريد الإلكتروني'),
                        
                        Infolists\Components\TextEntry::make('customer_phone')
                            ->label('رقم الهاتف'),
                    ])
                    ->columns(3),
                
                Infolists\Components\Section::make('الملاحظات')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->markdown(),
                    ])
                    ->collapsible(),
                
                Infolists\Components\Section::make('المدفوعات')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('payments')
                            ->schema([
                                Infolists\Components\TextEntry::make('id')
                                    ->label('رقم الدفعة'),
                                
                                Infolists\Components\TextEntry::make('amount')
                                    ->label('المبلغ')
                                    ->money('EGP'),
                                
                                Infolists\Components\TextEntry::make('payment_method')
                                    ->label('طريقة الدفع')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'vodafone_cash' => 'primary',
                                        'instapay' => 'success',
                                    }),
                                
                                Infolists\Components\TextEntry::make('status')
                                    ->label('حالة الدفع')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'cancelled' => 'gray',
                                    }),
                                
                                Infolists\Components\TextEntry::make('sender_name')
                                    ->label('اسم المرسل'),
                                
                                Infolists\Components\TextEntry::make('sender_phone')
                                    ->label('هاتف المرسل'),
                                
                                Infolists\Components\TextEntry::make('paid_at')
                                    ->label('تاريخ الدفع')
                                    ->dateTime(),
                                
                                Infolists\Components\ImageEntry::make('proof_image')
                                    ->label('إثبات الدفع')
                                    ->circular(),
                                
                                Infolists\Components\TextEntry::make('failure_reason')
                                    ->label('سبب الفشل')
                                    ->markdown(),
                            ])
                            ->columns(4),
                    ])
                    ->collapsible(),
            ]);
    }
}
