<?php

ini_set('display_errors', true);
ini_set('error_level', E_ALL);

require_once 'init.php';

use GiveIt\SDK\Product;
use GiveIt\SDK\Option;
use GiveIt\SDK\Choice;

$product = new Product;

// first, set the product details.
$product->setProductDetails(array(
            'code' => '4506',
            'price' => 1950,
            'name' => 'Accidents',
            'image' => 'http://s3.amazonaws.com/threadless-shop/products/4506/636x460shirt_guys_01.jpg')
);

// next, add an option. In this case the mandatory Delivery Option.
$delivery = new Option(array(
                            'id'            => 'my_delivery_id',
                            'type'          => 'layered_delivery',
                            'name'          => 'Shipping',
                            'tax_delivery'  => true
                            ));

$nl = new Choice(array('id'=> 'nl', 'name' => 'Netherlands', 'price' => 495));
$be = new Choice(array('id'=> 'be', 'name' => 'Belgium',     'price' => 895));

$delivery->addChoices(array($nl, $be));


// now, another option. As with the other examples, the gender
$gender = new Option(array(
                        'id'        => 'gender',
                        'type'      => 'single_option',
                        'name'      => 'Shirt Type',
                        'mandatory' => true,
                    ));

$product->addRecipientOption($gender);

// and last, for the recipient we also need them to select the shirt size
$size = new Option(array(
                        'id'        => 'size',
                        'type'      => 'single_choice',
                        'name'      => 'Shirt Size',
                        'mandatory' => true,
                  ));

$size->addChoice(new Choice(array('id' => 's', 'name' => 'Small',    'price' => 123.345)));
$size->addChoice(new Choice(array('id' => 'm', 'name' => 'Medium')));
$size->addChoice(new Choice(array('id' => 'l', 'name' => 'Large')));

$product->addRecipientOption($size);

// validate the product before rendering

if (! $product->validate()) {
    echo "invalid product\n";
    exit;
}

// output the JS required to render the button

echo $giveIt->getButtonJS();

// output the button

echo $product->getButtonHTML();

echo "\n\n";

