<?php

require_once 'init.php';

$giveIt->sales()->setLimit(5);

// get the first 5 sales

$sales = $giveIt->sales()->all();

print_r($sales);

// now the next 5

$sales =  $giveIt->sales()->nextPage();

print_r($sales);

exit;

// example below of how you set a specific sale to shipped

$sale  = $giveIt->sales()->get(1);
$sale->setShipped();



