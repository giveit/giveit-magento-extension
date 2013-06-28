<?php

namespace GiveIt\SDK\Callback;

class Callback
{
    public function __construct()
    {
        $this->parent     = \GiveIt\SDK::getInstance();
        $this->crypt      = \GiveIt\SDK\Crypt::getInstance();
    }

    protected function decodeJson($json)
    {
        $json =  preg_replace("/[^a-zA-Z0-9\s\p{P}]/", "", $json);

        return json_decode($json);
    }
}


class Sale extends Callback
{
    public function parse($data)
    {
        print_r($data);

        $json   = $this->crypt->decode($data['data'], $this->parent->dataKey);
        $sale   = $this->decodeJson($json);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                break;

            case JSON_ERROR_DEPTH:
                echo ' - Maximum stack depth exceeded';
                break;

            case JSON_ERROR_STATE_MISMATCH:
                echo ' - Underflow or the modes mismatch';
                break;

            case JSON_ERROR_CTRL_CHAR:
                echo ' - Unexpected control character found';
                break;

            case JSON_ERROR_SYNTAX:
                echo ' - Syntax error, malformed JSON';
                break;

            case JSON_ERROR_UTF8:
                echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;

            default:
                echo ' - Unknown error';
                break;
        }

        return $sale;
    }
}
