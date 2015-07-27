<?php namespace JSArtisan\Shopkart\Contracts;

interface Factory
{

    /**
     * Get an Provider Implementation
     *
     * @param  string  $driver
     * @return \Jsartisan\Shopkart\Contracts\Provider
     */
    public function driver($driver = null);
}
