<?php

namespace GiveIt\SDK;

class Sale extends Object
{
    public function setShipped()
    {
        $client = \GiveIt\SDK\Client::getInstance();

        $data = array('status' => 'retailer_shipped');

        $result = $client->sendPUT("/sales/$this->id", $data);
    }
}
