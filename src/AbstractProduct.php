<?php namespace JSArtisan\Shopkart;

abstract class AbstractProduct{

    /**
     * The unique identifier for the product.
     *
     * @var mixed
     */
    public $id;

    /**
     * The Title of the product.
     *
     * @var mixed
     */
    public $title;

    /**
     * Price of the price.
     *
     * @var mixed
     */
    public $price;

    /**
     * Product Thumbnail
     *
     * @var mixed
     */
    public $thumbnail;

    /**
     * Product URL.
     *
     * @var mixed
     */
    public $url;

    /**
     * Thumbnail of the product
     *
     * @var mixed
     */
    public $product;

    /**
     * Gets the ID  of the Product
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the Title of the Product
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Gets the Price of the Product
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     *
     * Gets the Thumbnail of the Product
     * @return mixed
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Gets the Url of the Product
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the raw user array from the provider.
     *
     * @param  array  $user
     * @return $this
     */
    public function setRaw(array $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Map the given array onto the user's properties.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function map(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }
}