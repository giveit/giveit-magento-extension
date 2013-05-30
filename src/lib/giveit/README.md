Give.it PHP SDK
=======

The Give.it PHP SDK makes it very easy to implement the Give.it button on your website. A simple example to set up a product with some options below. If you want to start implementing the SDK, please visit our [wiki](https://github.com/giveit/sdk-php/wiki)

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
    
first, set the product details.

    $product->setProductDetails('4506',1950,'My Awesome Shirt','http://example.com/images/shirt4506.jpg');
    
next, add an option. In this case the mandatory Delivery Option.

    $delivery = $product->addDeliveryOption('delivery','Delivery Option');
    $product->addChoice($delivery,'europe','Europe',900);
    $product->addChoice($delivery,'usa','United States',499);
    
now, another option. As with the other examples, the gender

    $gender = $product->addBuyerOption('gender','single_choice','Shirt Type',null,true);
    $product->addChoice($gender,'guys','Guys');
    $product->addChoice($gender,'girly','Girly');
    
let's add another option, this time for the description type

    $delivery = $product->addBuyerOption('desc','description','Shirt Size','The shirt size will be picked by the recipient.');
    
at last, for the recipient we also need them to select the shirt size

    $size = $product->addRecipientOption('size','single_choice','Shirt Size',null,true);
    $product->addChoice($size,'s','Small');
    $product->addChoice($size,'m','Medium');
    $product->addChoice($size,'l','Large');
    $product->addChoice($size,'xl','Extra Large');

We should validate this product

    $result = $product->validate();
    
is it valid, echo the button; if it is not, display the errors

    if ($result == true){
        $htmlFromProduct    = $product->getButtonHTML();
        echo $htmlFromProduct;
    }
    else {
        $errorsHTML = $product->getErrorsHTML();
        echo $errorsHTML;
    }
    
finally, now also include giveit.js so the button actually works

    $giveIt->outputButtonJS();

