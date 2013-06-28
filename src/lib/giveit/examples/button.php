<?php

require_once 'init.php';

$product = new \GiveIt\SDK\Product;

// first, set the product details.
$product->setProductDetails('4506',1950, 'Accidents', 'http://s3.amazonaws.com/threadless-shop/products/4506/636x460shirt_guys_01.jpg');

// next, add an option. In this case the mandatory Delivery Option.
$delivery = $product->addBuyerOption('delivery', 'delivery', 'Delivery Option', null, true);
$product->addChoice($delivery, 'europe', 'Europe', 900);
$product->addChoice($delivery, 'usa', 'United States', 499);

// now, another option. As with the other examples, the gender
$gender = $product->addBuyerOption('gender', 'singly_choice', 'Shirt Type', null, true);
$product->addChoice($gender, 'guys', 'Guys');
$product->addChoice($gender, 'girly', 'Girly');

// let's add another option, this time for the description type
$delivery = $product->addBuyerOption('desc', 'description', 'Shirt Size', 'The shirt size will be picked by the recipient.');

// at last, for the recipient we also need them to select the shirt size
$size = $product->addRecipientOption('size', 'singly_choice', 'Shirt Size', null, true);
$product->addChoice($size, 's', 'Small');
$product->addChoice($size, 'm', 'Medium');
$product->addChoice($size, 'l', 'Large');
$product->addChoice($size, 'xl', 'Extra Large');

// validate the product before rendering

if (! $product->validate()) {
    echo "invalid product\n";
    exit;
}

// output the JS required to render the button

echo $giveIt->getButtonJS();

// output the button

echo $product->getButtonHTML();



