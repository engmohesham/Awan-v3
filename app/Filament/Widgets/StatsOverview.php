<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي المستخدمين', User::count())
                ->description('عدد المستخدمين المسجلين')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('success'),

            Stat::make('إجمالي الدورات', Course::count())
                ->description('عدد الدورات المتاحة')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart([3, 5, 7, 4, 8, 3, 6, 4])
                ->color('warning'),

            Stat::make('إجمالي المبيعات', Purchase::where('payment_status', 'approved')->sum('amount') . ' $')
                ->description('إجمالي المبيعات المكتملة')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart([4, 8, 3, 7, 5, 6, 3, 5])
                ->color('success'),

            Stat::make('المبيعات قيد المراجعة', Purchase::where('payment_status', 'pending')->count())
                ->description('عدد المبيعات قيد المراجعة')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([3, 5, 4, 6, 3, 7, 4, 5])
                ->color('danger'),

            Stat::make('إجمالي الطلبات', Order::count())
                ->description('عدد الطلبات المقدمة')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart([2, 4, 6, 8, 5, 7, 3, 6])
                ->color('info'),

            Stat::make('المدفوعات المعلقة', Payment::where('status', 'pending')->count())
                ->description('عدد المدفوعات قيد المراجعة')
                ->descriptionIcon('heroicon-m-credit-card')
                ->chart([1, 3, 2, 4, 3, 5, 2, 4])
                ->color('warning'),
        ];
    }
} 