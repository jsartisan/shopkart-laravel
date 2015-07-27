<?php namespace JSArtisan\Shopkart;

use Illuminate\Support\ServiceProvider;

class ShopkartServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('Shopkart', function ($app) {
            return new Shopkart($app);
        });
    }

}
