<?php namespace JSArtisan\Shopkart;

use InvalidArgumentException;
use Illuminate\Support\Manager;

class Shopkart extends Manager implements Contracts\Factory{

    /**
     * Get a driver instance.
     *
     * @param string $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\Acme\Shopkart\Providers\AbstractProvider
     */
    protected function createFlipkartDriver()
    {
        $config = $this->app['config']['services.flipkart'];

        return $this->buildProvider(
            'JSArtisan\Shopkart\Providers\FlipkartProvider', $config
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\Acme\Shopkart\Providers\AbstractProvider
     */
    protected function createAmazonDriver()
    {
        $config = $this->app['config']['services.amazon'];


        return $this->buildProvider(
            'JSArtisan\Shopkart\Providers\AmazonProvider', $config
        );
    }

    /**
     * Build Provider Instance
     * @param $provider
     * @param $config
     * @return mixed
     */
    public function buildProvider($provider, $config)
    {
        return new $provider(
            $config['client_id'],
            $config['client_secret'],
            $config['country'],
            $config['tag']
        );
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException("No Socialite driver was specified.");
    }
}