<?php namespace JSArtisan\Shopkart\Contracts;

interface Provider{

    /**
     * @return mixed
     */
    public function search($query,$count);

}