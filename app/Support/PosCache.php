<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class PosCache
{
    public const PRODUCTS_INDEX = 'products.index';
    public const PRODUCTS_DASHBOARD = 'products.dashboard';

    public static function forgetProductReads(): void
    {
        Cache::forget(self::PRODUCTS_INDEX);
        Cache::forget(self::PRODUCTS_DASHBOARD);
    }
}
