<?php namespace JSArtisan\Shopkart\Providers;

use JSArtisan\Shopkart\Contracts\Provider as ProviderContract;

abstract class AbstractProvider implements ProviderContract {
    
    /**
     * The client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * Associate Tag.
     *
     * @var string
     */
    protected $tag;

    /**
     * Country
     *
     * @var string
     */
    protected $country;

    /**
     * API Search URL.
     *
     * @var string
     */

    protected $searchUrl;

    /**
     * Search Query String
     *
     * @var string
     */
    protected $query;

    /**
     * Result Count for Flipkart
     *
     * @var string
     */
    protected $count;

     /**
     * Category String for Amazon
     *
     * @var string
     */
    protected $category;


    /**
     * Array to contain Raw JSON.
     *
     * @var string
     */
    protected $rawProducts = array();

    /**
     * Array to contain Product.
     *
     * @var string
     */
    protected $products = array();


    /**
     * Create a new provider instance.
     *
     * @param  Request  $request
     * @param  string  $clientId
     * @param  string  $clientSecret
     * @return void
     */
    public function __construct($clientId, $clientSecret,$country,$tag)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->country = $country;
        $this->tag = $tag;
    }

    abstract protected function setHeaders();

    /**
     * Get Product Search URL
     *
     * @param  string  $state
     * @return string
     */
   abstract protected function buildSearchUrl();

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        return new  \Guzzle\Http\Client;
    }
    
    /**
     * Return the Query String
     *
     * @return \GuzzleHttp\Client
     */
    protected function getQuery()
    {
        return $this->query;
    }

    /**
     * Return Result Count
     *
     * @return \GuzzleHttp\Client
     */
    protected function getCount()
    {
        return $this->count;
    }

    /**
     * Abstract Products Method
     *
     * @return \GuzzleHttp\Client
     */
    abstract function products();

    /**
     * Map the products array to Shopkart Products Instance.
     *
     * @param  array  $user
     * @return \App\Acme\Shopkart\Providers\Product
     */
    abstract protected function mapProductsToObject( array $product);



}