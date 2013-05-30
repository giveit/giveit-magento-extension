<?php

namespace GiveIt\SDK;

class Client extends Base
{
    private     $cookieCache;
    private     $curl;
    private     $sdk;
    private     $authenticated = false;

    protected static $instance = null;

    public function __construct()
    {
        $this->sdk          = \GiveIt\SDK::getInstance();
        $this->cookieCache  = fopen('php://memory', "w+");

        $this->setupCurl();
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

    public function setupCurl()
    {
        $this->curl     = curl_init();

        $options = array(
            CURLOPT_RETURNTRANSFER      => true,
            CURLOPT_COOKIEJAR           => $this->cookieCache,
            CURLOPT_COOKIEFILE          => $this->cookieCache,
        );

        curl_setopt_array($this->curl, $options);
    }

    public function authenticate()
    {
        if (! $this->sdk->privateKey) {
            return false;
        }

        $data = array('key' => $this->sdk->privateKey);

        $result = $this->sendPOST("/auth/retailer", $data);

        if (! $result) {
            $this->addError("could not authenticate");

            return false;
        }

        if ($result->result == 'ok') {
            $this->authenticated = true;
            return true;
        }

        return false;
    }

    public function getSales(){

        if (!$this->authenticated){
            $this->authenticate();
        }

        $result = $this->sendGET('/sales');

        return $result;

    }

    public function sendGET($url, $data = false)
    {
        if (! $this->authenticated && ! $this->authenticate()) {
            return false;
        }

        $this->setupCurl();

        if (is_array($data) && ! empty($data)){

            if (strpos($url, '?') === false) {
                $url .= '&';
            } else {
                $url .= '?';
            }

            $url .= http_build_query($data);
        }

        curl_setopt($this->curl, CURLOPT_URL, $this->sdk->getURL('api') . $url);

        $response = curl_exec($this->curl);

        // TODO: what if we don't get back JSON? need error handling
        // TODO: also check status code
        return json_decode($response);

    }

    public function sendPOST($url, $data = false)
    {
        $this->setupCurl();

        curl_setopt($this->curl, CURLOPT_URL,        $this->sdk->getURL('api') . $url);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($this->curl);

        if ($response === false) {
            $this->addError(curl_error($this->curl));
            return false;
        }

        return json_decode($response);
    }

    public function sendPUT($url, $data = false)
    {
        $this->setupCurl();

        curl_setopt_array($this->curl, array(
            CURLOPT_URL             => $this->sdk->getURL('api') . $url,
            CURLOPT_CUSTOMREQUEST   => 'PUT',
            CURLOPT_POSTFIELDS      => $data,
        ));

        $response = curl_exec($this->curl);

        return json_decode($response);
    }
}
