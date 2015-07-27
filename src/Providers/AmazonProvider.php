<?php namespace JSArtisan\Shopkart\Providers;

use JSArtisan\Shopkart\AbstractProduct;
use JSArtisan\Shopkart\Contracts\Provider;

use SoapClient;
use SoapHeader;
use Exception;

class AmazonProvider extends AbstractProvider implements Provider
{   

	const RETURN_TYPE_ARRAY  = 1;
	const RETURN_TYPE_OBJECT = 2;

	/**
	 * Base configurationstorage
	 *
	 * @var array
	 */
	private $requestConfig = array(
		'requestDelay' => false
	);


	/**
	 * Configuration for Response
	 *
	 * @var array
	 */
	private $responseConfig = array(
		'returnType'          => self::RETURN_TYPE_ARRAY,
		'responseGroup'       => 'Images,OfferSummary,Small',
		'optionalParameters'  => array()
	);

	/**
	 * All possible locations
	 *
	 * @var array
	 */
	private $possibleLocations = array('de', 'com', 'co.uk', 'ca', 'fr', 'co.jp', 'it', 'cn', 'es','in');

	/**
	 * The WSDL File
	 *
	 * @var string
	 */
	protected $webserviceWsdl = 'http://webservices.amazon.com/AWSECommerceService/AWSECommerceService.wsdl';

	/**
	 * The SOAP Endpoint
	 *
	 * @var string
	 */
	protected $webserviceEndpoint = 'https://webservices.amazon.%%COUNTRY%%/onca/soap?Service=AWSECommerceService';


	/**
	 * Execute Search
	 *
	 * @param string $pattern
	 *
	 * @return array|object return type depends on setting
	 *
	 * @see returnType()
	 */
	public function search($query,$category)
	{   
		$this->query = $query;

		$this->category = $category;

		$this->getProducts();

		return $this->products();

	}

	/**
	 * Make a Soap Request and return Raw Products
	 * 
	 * @return mixed
	 */
	public function getProducts()
	{
		$params = $this->buildRequestParams('ItemSearch',
			array(
				'Keywords' => $this->query,
				'SearchIndex' => $this->category
			)
		);

		$this->rawProducts =  $this->returnData(
			$this->performSoapRequest("ItemSearch", $params)
		);

		return $this->rawProducts;
	}

	/**
	 * Maps raw products to JSArtisan/Shopkart/Product Instance
	 * 
	 * @return string
	 */
	public function products()
	{

		foreach($this->rawProducts['Items']['Item'] as $product)
		{
			$this->products[] = $this->mapProductsToObject($product);
		}

		return $this->products;
	}



	/**
	 * Builds the request parameters
	 *
	 * @param string $function
	 * @param array  $params
	 *
	 * @return array
	 */
	protected function buildRequestParams($function, array $params)
	{
		$associateTag = array();

		if(false === empty($this->tag))
		{
			$associateTag = array('AssociateTag' => $this->tag);
		}

		return array_merge(
			$associateTag,
			array(
				'AWSAccessKeyId' => $this->clientId,
				'Request' => array_merge(
					array('Operation' => $function),
					$params,
					$this->responseConfig['optionalParameters'],
					array('ResponseGroup' => $this->prepareResponseGroup())
				)));
	}

	/**
	 * Prepares the responsegroups and returns them as array
	 *
	 * @return array|prepared responsegroups
	 */
	protected function prepareResponseGroup()
	{
		if (false === strstr($this->responseConfig['responseGroup'], ','))
			return $this->responseConfig['responseGroup'];

		return explode(',', $this->responseConfig['responseGroup']);
	}

	/**
	 * @param string $function Name of the function which should be called
	 * @param array $params Requestparameters 'ParameterName' => 'ParameterValue'
	 *
	 * @return array The response as an array with stdClass objects
	 */
	protected function performSoapRequest($function, $params)
	{
		if (true ===  $this->requestConfig['requestDelay']) {
			sleep(1);
		}

		$soapClient = new SoapClient(
			$this->webserviceWsdl,
			array('exceptions' => 1)
		);

		$soapClient->__setLocation(str_replace(
			'%%COUNTRY%%',
			$this->country,
			$this->webserviceEndpoint
		));

		$soapClient->__setSoapHeaders($this->setHeaders());

		return $soapClient->__soapCall($function, array($params));
	}

	/**
	 * Provides some necessary soap headers
	 *
	 * @param string $function
	 *
	 * @return array Each element is a concrete SoapHeader object
	 */
	protected function setHeaders()
	{
		$timeStamp = $this->getTimestamp();
		$signature = $this->buildSignature("ItemSearch" . $timeStamp);

		return array(
			new SoapHeader(
				'http://security.amazonaws.com/doc/2007-01-01/',
				'AWSAccessKeyId',
				$this->clientId
			),
			new SoapHeader(
				'http://security.amazonaws.com/doc/2007-01-01/',
				'Timestamp',
				$timeStamp
			),
			new SoapHeader(
				'http://security.amazonaws.com/doc/2007-01-01/',
				'Signature',
				$signature
			)
		);
	}

	/**
	 * provides current gm date
	 *
	 * primary needed for the signature
	 *
	 * @return string
	 */
	final protected function getTimestamp()
	{
		return gmdate("Y-m-d\TH:i:s\Z");
	}

	/**
	 * provides the signature
	 *
	 * @return string
	 */
	final protected function buildSignature($request)
	{
		return base64_encode(hash_hmac("sha256", $request, $this->clientSecret, true));
	}

	/**
	 * Basic validation of the nodeId
	 *
	 * @param integer $nodeId
	 *
	 * @return boolean
	 */
	final protected function validateNodeId($nodeId)
	{
		if (false === is_numeric($nodeId) || $nodeId <= 0)
		{
			throw new InvalidArgumentException(sprintf('Node has to be a positive Integer.'));
		}

		return true;
	}

	/**
	 * Returns the response either as Array or Array/Object
	 *
	 * @param object $object
	 *
	 * @return mixed
	 */
	protected function returnData($object)
	{
		switch ($this->responseConfig['returnType'])
		{
			case self::RETURN_TYPE_OBJECT:
				return $object;
				break;

			case self::RETURN_TYPE_ARRAY:
				return $this->objectToArray($object);
				break;

			default:
				throw new InvalidArgumentException(sprintf(
					"Unknwon return type %s", $this->responseConfig['returnType']
				));
				break;
		}
	}

	/**
	 * Transforms the responseobject to an array
	 *
	 * @param object $object
	 *
	 * @return array An arrayrepresentation of the given object
	 */
	protected function objectToArray($object)
	{
		$out = array();
		foreach ($object as $key => $value)
		{
			switch (true)
			{
				case is_object($value):
					$out[$key] = $this->objectToArray($value);
					break;

				case is_array($value):
					$out[$key] = $this->objectToArray($value);
					break;

				default:
					$out[$key] = $value;
					break;
			}
		}

		return $out;
	}

	/**
	 * set or get optional parameters
	 *
	 * if the argument params is null it will reutrn the current parameters,
	 * otherwise it will set the params and return itself.
	 *
	 * @param array $params the optional parameters
	 *
	 * @return array|AmazonECS depends on params argument
	 */
	public function optionalParameters($params = null)
	{
		if (null === $params)
		{
			return $this->responseConfig['optionalParameters'];
		}

		if (false === is_array($params))
		{
			throw new InvalidArgumentException(sprintf(
				"%s is no valid parameter: Use an array with Key => Value Pairs", $params
			));
		}

		$this->responseConfig['optionalParameters'] = $params;

		return $this;
	}

	/**
	 * Setting/Getting the responsegroup
	 *
	 * @param string $responseGroup Comma separated groups
	 *
	 * @return string|AmazonECS depends on responseGroup argument
	 */
	public function responseGroup($responseGroup = null)
	{
		if (null === $responseGroup)
		{
			return $this->responseConfig['responseGroup'];
		}

		$this->responseConfig['responseGroup'] = $responseGroup;

		return $this;
	}

	/**
	 * Setting/Getting the returntype
	 * It can be an object or an array
	 *
	 * @param integer $type Use the constants RETURN_TYPE_ARRAY or RETURN_TYPE_OBJECT
	 *
	 * @return integer|AmazonECS depends on type argument
	 */
	public function returnType($type = null)
	{
		if (null === $type)
		{
			return $this->responseConfig['returnType'];
		}

		$this->responseConfig['returnType'] = $type;

		return $this;
	}


	/**
	 * @deprecated use returnType() instead
	 */
	public function setReturnType($type)
	{
		return $this->returnType($type);
	}


	/**
	 * Enables or disables the request delay.
	 * If it is enabled (true) every request is delayed one second to get rid of the api request limit.
	 *
	 * Reasons for this you can read on this site:
	 * https://affiliate-program.amazon.com/gp/advertising/api/detail/faq.html
	 *
	 * By default the requestdelay is disabled
	 *
	 * @param boolean $enable true = enabled, false = disabled
	 *
	 * @return boolean|AmazonECS depends on enable argument
	 */
	public function requestDelay($enable = null)
	{
		if (false === is_null($enable) && true === is_bool($enable))
		{
			$this->requestConfig['requestDelay'] = $enable;

			return $this;
		}

		return $this->requestConfig['requestDelay'];
	}


	/**
	 * Get Product Search URL
	 *
	 * @param  string $state
	 * @return string
	 */
	protected function buildSearchUrl()
	{
		// TODO: Implement buildSearchUrl() method.
	}

	/**
	 * Map the products array to Shopkart Products Instance.
	 *
	 * @param  array $user
	 * @return \Jsartisan\Shopkart\Providers\Product
	 */
	protected function mapProductsToObject(array $product)
	{

		return (new Product)->setRaw($product)->map([
			'id'            => $product['ASIN'],
			'title'         => $product['ItemAttributes']['Title'],
			'thumbnail'     => $product['MediumImage']['URL'],
			'price'         => $product['OfferSummary']['LowestNewPrice']['Amount'],
			'url'           => $product['DetailPageURL'],
			'vendor'        => 'amazon'
		]);
	}
}