<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class PosCache
{
    private const PRODUCT_READ_VERSION = 'products.read.version';

    public static function productsIndexKey(int $page): string
    {
        return 'products.index.v'.self::productReadVersion().'.page.'.$page;
    }

    public static function productsDashboardKey(): string
    {
        return 'products.dashboard.v'.self::productReadVersion();
    }

    public static function forgetProductReads(): void
    {
        Cache::forever(self::PRODUCT_READ_VERSION, self::productReadVersion() + 1);
    }

    private static function productReadVersion(): int
    {
        $version = Cache::get(self::PRODUCT_READ_VERSION);

        if ($version === null) {
            Cache::forever(self::PRODUCT_READ_VERSION, 1);

            return 1;
        }

        return (int) $version;
    }
}
