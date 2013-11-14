<?php
/**
 * @copyright    give.it 2013
 * @author       David Kelly
 *
 * required:
 *
 * PHP > 5.3.0 with modules:
 * - mcrypt
 * - curl
 * - json
 */

namespace GiveIt;

class SDK extends SDK\Base
{
    const   VERSION          = '1.1.6';

    public  $dataKey         = null;
    public  $publicKey       = null;
    public  $privateKey      = null;
    public  $debug           = false;

    private $environment     = 'live';
    private $jsOutput        = false;
    private $sales           = null;
    private $payments        = null;

    private $urls            = array(
        'live'      => array(
            'api'       => 'https://api.give.it',
            'widget'    => '//widget.give.it'
        ),
        'sandbox'   => array(
            'api'       => 'https://api.sandbox.give.it',
            'widget'    => '//widget.sandbox.give.it'
        )
    );

    protected static $instance = null;

    public function __construct($settings = array())
    {
        $this->registerInstance();

        if (is_array($settings)) {

            $allowed = array('publicKey', 'privateKey', 'dataKey', 'environment', 'debug');

            foreach ($allowed as $type) {
                if (isset($settings[$type])) {
                    $this->$type = $settings[$type];
                }
            }
        }

        // fall back to constants if nothing defined
        $this->getKeysFromConstants();

        return true;
    }

    private function getKeysFromConstants()
    {
        if ($this->publicKey == null && defined('GIVEIT_PUBLIC_KEY')) {
            $this->publicKey = GIVEIT_PUBLIC_KEY;
        }

        if ($this->privateKey == null && defined('GIVEIT_PRIVATE_KEY')) {
            $this->privateKey = GIVEIT_PRIVATE_KEY;
        }

        if ($this->dataKey == null && defined('GIVEIT_DATA_KEY')) {
            $this->dataKey = GIVEIT_DATA_KEY;
        }

        return true;
    }

   /**
    * Get the singleton
    *
    * @return object
    */
    static public function getInstance()
    {
        if (! isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

   /**
    * Register the singleton
    *
    * @return boolean
    */
    protected function registerInstance()
    {
        static::$instance = $this;

        return true;
    }

    private function setupRenderer()
    {
        $this->renderer = new \GiveIt\SDK\Renderer;
    }

    public function setEnvironment($environment)
    {
        if (! isset($this->urls[$environment])) {
            return false;
        }

        $this->environment = $environment;

        return true;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getURL($type)
    {
        return $this->urls[$this->environment][$type];
    }

    public function getButtonJS()
    {
        $text = file_get_contents(__DIR__ . '/../templates/widget.js');
        $text = str_replace('$widgetUrl', $this->urls[$this->environment]['widget'], $text);
        $text = str_replace('$public_api_key', $this->publicKey, $text);

        return $text;
    }

    /**
     * This function outputs the JS inclusion for the button
     * @return boolean
     */
    public function outputButtonJS()
    {
        if ($this->jsOutput) {
            return true;
        }

        echo $this->getButtonJS();

        $this->jsOutput = true;

        return true;
    }

    public function getCallbackType($postData)
    {
        return $postData['type'];
    }

    public function parseCallback($postData)
    {
        $type       = ucfirst($postData['type']);
        $class      = '\GiveIt\SDK\Callback\\' . $type;
        $callback   = new $class;
        $parsed     = $callback->parse($postData);

        return $parsed;
    }

    public function sales()
    {
        if (! $this->sales) {
            $this->sales = new SDK\Collection('Sale');
        }

        return $this->sales;
    }

    public function payments()
    {
        if (! $this->payments) {
            $this->payments = new SDK\Collection('Payment');
        }

        return $this->payments;
    }

    public function verifyKeys()
    {
        $client         = SDK\Client::getInstance();
        $authenticated  = $client->authenticate();

        if (! $authenticated) {
            $this->addError("unable to log in with private key");
            return false;
        }

        $result = $client->sendGET('/retailers/me');

       if (isset($result->errors)) {
            foreach ($result->errors as $error) {
                $this->addError($error);
            }
            return false;
        }

        // at this point we can assume that the private key is okay, verify the other two

        if ($result->public_api_key != $this->publicKey) {
            $this->addError("incorrect public key");
            return false;
        }

        if ($result->data_key != $this->dataKey) {
            $this->addError("incorrect data key");
            return false;
        }

        return true;

    }
}

