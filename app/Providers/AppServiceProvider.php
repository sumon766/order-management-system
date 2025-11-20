<?php

namespace App\Providers;

use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantRepository;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, function ($app) {
            return new ProductRepository();
        });

        $this->app->bind(ProductVariantRepository::class, function ($app) {
            return new ProductVariantRepository();
        });

        $this->app->bind(ProductService::class, function ($app) {
            return new ProductService(
                $app->make(ProductRepository::class),
                $app->make(ProductVariantRepository::class)
            );
        });

        $this->app->bind(OrderRepository::class, function ($app) {
            return new OrderRepository();
        });

        $this->app->bind(OrderItemRepository::class, function ($app) {
            return new OrderItemRepository();
        });

        $this->app->bind(InventoryService::class, function ($app) {
            return new InventoryService();
        });

        $this->app->bind(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(OrderRepository::class),
                $app->make(OrderItemRepository::class),
                $app->make(InventoryService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
