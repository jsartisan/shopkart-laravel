<?php
 
use JSArtisan\Shopkart\Shopkart;
 
class ShopkartTest extends PHPUnit_Framework_TestCase {
 
	  public function testShopkartSayHello()
	  {
	    $shopkart = new Shopkart;
	    
	    $this->assertEquals('hello',$shopkart->with('flipkart')->index());
	  }
 
}