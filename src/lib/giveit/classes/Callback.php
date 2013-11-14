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

    protected function getJsonErrorMessage()
    {
       switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return null;

            case JSON_ERROR_DEPTH:
                return  'maximum stack depth exceeded';

            case JSON_ERROR_STATE_MISMATCH:
                return 'underflow or the modes mismatch';

            case JSON_ERROR_CTRL_CHAR:
                return 'unexpected control character found';

            case JSON_ERROR_SYNTAX:
                return 'syntax error, malformed JSON';

            case JSON_ERROR_UTF8:
                return 'malformed UTF-8 characters, possibly incorrectly encoded';

            default:
                return 'unknown error';
        }
    }
}

/**
 * Sale callback class
 */
class Sale extends Callback
{
    public function parse($data)
    {
        $json      = $this->crypt->decode($data['data'], $this->parent->dataKey);
        $sale_data = $this->decodeJson($json);
        $result    = $this->getJsonErrorMessage();

        if ($result === null) {
            return new \GiveIt\SDK\Sale($sale_data);
        }

        return $result;
    }
}
