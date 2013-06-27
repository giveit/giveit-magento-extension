Give.it PHP SDK
=======

The Give.it PHP SDK makes it very easy to implement the Give.it button on your website. A simple example to set up a product with some options below. If you want to start implementing the SDK, please visit our [wiki](https://github.com/giveit/sdk-php/wiki)

The Wiki gives simple explaination on how to implement the button. Short example below, but look at the wiki for all options.

```php
include the SDK

    require_once 'sdk/sdk.php';

set keys

    define('GIVEIT_PUBLIC_KEY',  '[key]');
    define('GIVEIT_DATA_KEY',    '[key]');
    define('GIVEIT_PRIVATE_KEY', '[key]');


create an instance of the SDK

    $giveIt = new \GiveIt\SDK;
    $giveIt->debug = true;
    $giveIt->setEnvironment('sandbox');

create the product

    $product = new \GiveIt\SDK\Product();

// first, set the product details.
    $product->setProductDetails(array(
                'code' => '4506', 
                'price' => 1950, 
                'name' => 'Accidents', 
                'image' => 'http://s3.amazonaws.com/threadless-shop/products/4506/636x460shirt_guys_01.jpg')
    );

// add a delivery option
    $delivery = new \GiveIt\SDK\Option(Array(
                                'id' => 'my_id',
                                'type' => 'layered_delivery',
                                'name' => 'Shipping',
                                'tax_delivery' => true
                                ));

    // add 2 choices to the delivery option
    $nl = new \GiveIt\SDK\Choice(Array('id'=> 'nl', 'name' => 'Netherlands', 'price' => 495));
    $be = new \GiveIt\SDK\Choice(Array('id'=> 'be', 'name' => 'Belgium', 'price' => 895));
    
    $delivery->addChoices(array($nl,$be));
    
    // add the delivery option to the product
    $product->addBuyerOption($delivery);

    //We should validate this product
    $result = $product->validate();
    
    // is it valid, echo the button; if it is not, display the errors
    if ($result == true){
        $htmlFromProduct    = $product->getButtonHTML();
        echo $htmlFromProduct;
    }
    else {
        $errorsHTML = $product->getErrorsHTML();
        echo $errorsHTML;
    }
    
    // finally, now also include giveit.js so the button actually works
    $giveIt->outputButtonJS();
    
```
