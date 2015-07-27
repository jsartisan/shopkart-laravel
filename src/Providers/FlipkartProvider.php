<?php namespace JSArtisan\Shopkart\Providers;

use JSArtisan\Shopkart\Contracts\Provider;

class FlipkartProvider extends AbstractProvider implements Provider{


	protected $baseUrl = 'https://affiliate-api.flipkart.net/affiliate/search/json?';

	/**
	 * Makes request to Flipkart API and returns the mapped array of Product Instance.
	 * 
	 * @return string
	 */
	public function search($query,$count)
	{
		$this->query = $query;

		$this->count = $count;

		$this->getProducts();

		return $this->products();
	}

	/**
	 * Get Products from Flipkart API
	 * 
	 * @return mixed
	 */
	public function getProducts()
	{
		$request = $this->getHttpClient()->get($this->buildSearchUrl(),$this->setHeaders());

		$response = $request->send();

		$this->rawProducts = $response->json();

	}

	/**
	 * Build API Search URL according to the vendor
	 * 
	 * @return string
	 */
	protected function buildSearchUrl()
	{
		$this->searchUrl =  $this->baseUrl . 'query=' . $this->getQuery() . '&resultCount=' . $this->getCount();

		return $this->searchUrl;
	}

	/**
	 * Maps raw products to Shopkart Product Instance
	 * 
	 * @return string
	 */
	public function products()
	{
		foreach($this->rawProducts['productInfoList'] as $product)
		{
			$this->products[] = $this->mapProductsToObject($product);
		}

		return $this->products;
	}

	/**
	 * Map the products array to Shopkart Products Instance.
	 *
	 * @param  array $user
	 * 
	 * @return \App\Acme\Shopkart\Providers\Product
	 */
	protected function mapProductsToObject(array $product)
	{
		return (new Product)->setRaw($product)->map([
			'id'            => $product['productBaseInfo']['productIdentifier']['productId'],
			'title'         => $product['productBaseInfo']['productAttributes']['title'],
			'thumbnail'     => $product['productBaseInfo']['productAttributes']['imageUrls']['275x275'] ?: $product['productBaseInfo']['productAttributes']['imageUrls']['200x200'],
			'price'         => $product['productBaseInfo']['productAttributes']['sellingPrice']['amount'],
			'url'           => $product['productBaseInfo']['productAttributes']['productUrl'],
			'vendor'        => 'flipkart'
		]);
	}

	/**
	 * Set Headers for the Flipkart API
	 * 
	 * @return array
	 */
	protected function setHeaders()
	{
		return [
		'Fk-Affiliate-Id'       =>  $this->clientId,
		'Fk-Affiliate-Token'    =>  $this->clientSecret
		];
	}
}