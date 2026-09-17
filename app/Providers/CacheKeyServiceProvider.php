<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Menyimpan semua cache key sebagai constant agar mudah di-inspect dan
 * konsisten di seluruh aplikasi. Durasi cache wajib 120 detik (2 menit).
 */
class CacheKeyServiceProvider extends ServiceProvider
{
    /**
     * Default TTL untuk data yang jarang berubah — 120 detik (2 menit).
     */
    public const CACHE_TTL = 120;

    /*
    |---------------------------------------------------------------------
    | Cache keys
    |---------------------------------------------------------------------
    */

    public const LIST_ROLES = 'role:list:all';

    public const LIST_PERMISSIONS = 'permission:list:all';

    public const LIST_MENUS = 'menu:list';

    public const LIST_PARENT_MENUS = 'menu:parent:list';

    public const SIDEBAR_MENUS = 'menu:sidebar';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No runtime bindings required — only constants are exposed.
    }
}
