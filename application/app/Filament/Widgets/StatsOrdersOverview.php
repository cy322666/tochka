<?php

namespace App\Filament\Widgets;

use App\Models\Platform\Order;
use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOrdersOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $todayOrdersQuery = Order::query()->where('created_at', '>', date('Y-m-d').' 00:00:00');

        $todayOrders = $todayOrdersQuery->get();

        return [
            Stat::make('Заказов за сегодня', $todayOrders->count())
                ->description('На сумму '.$todayOrders->sum('cost_money')),
//                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before),
            Stat::make('Осталось выгрузить', $todayOrdersQuery->where('status', false)->count())
//                ->description()
//                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('success'),
            Stat::make('Какой то показатель', '3:12')
                ->description('3% increase')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
        ];
    }
}
