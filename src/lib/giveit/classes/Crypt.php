<?php
/**
 * @copyright    give.it 2013
 * @author       David Kelly
 *
 * required:
 * - PHP > 5.3.0
 * - libmcrypt >= 2.4.x
 */


namespace GiveIt\SDK;

class Crypt extends Base
{
    private $cipher          = false;
    private $ciphers         = array('rijndael-128', 'blowfish');

    public  $debug           = false;

    protected static $instance = null;

    public function __construct()
    {
        $this->registerInstance();

        if (! function_exists('mcrypt_encrypt')) {
            $this->addError("mcrypt functions not available");
            return false;
        }

        if (! $this->getCipher()) {
            return false;
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

   /*
    * Returns the mcrypt constant identifying the first available cipher from $this->ciphers,
    * or false if none are in the mcrypt algorithms list.
    */
    private function getCipher()
    {
        if ($this->cipher !== false) {
            return $this->cipher;
        }

        $available = mcrypt_list_algorithms();

        foreach ($this->ciphers as $cipher) {
            if (in_array($cipher, $available)) {
                $this->cipher = $cipher;
                return constant('MCRYPT_' . strtoupper(str_replace('-', '_', $cipher)));
            }
        }

        $this->addError("no available cipher");

        return false;
    }

  /**
    * Encode data into a single string
    *
    * @param    string  $data
    * @return   string
    */
    public function encode($plainText, $key = null)
    {
        $cipher     = $this->getCipher();

        if ($cipher == false) {
            return false;
        }

        if ($key == null) {
            $sdk = \GiveIt\SDK::getInstance();
            $key = $sdk->dataKey;
        }

        if ($key == null) {
            $this->addError("missing key for encryption");
            return false;
        }

        $iv           = mcrypt_create_iv(mcrypt_get_iv_size($cipher, MCRYPT_MODE_CBC), MCRYPT_RAND);
        $td           = mcrypt_module_open($cipher, '', MCRYPT_MODE_CBC, '');

        mcrypt_generic_init($td, $key, $iv);

        $text         = $this->pkcs5_pad($plainText, mcrypt_enc_get_block_size($td));      // manually pad the data since mcrypt doesn't do this properly
        $encrypted    = mcrypt_generic($td, $text);
        $crypt        = base64_encode($iv . $encrypted);
        $crypt        = urlencode($crypt);

        if ($this->debug) {
            echo "\n---- cipher ---\n" . $this->cipher;
            echo "\n---- text  ----\n" . $text;
            echo "\n---- crypt ----\n" . $crypt;
            echo "\n---------------\n";
        }

        return $crypt;
    }

 /**
    * Encode data into a single string
    *
    * @param    string  $data
    * @return   string
    */
    public function decode($text, $key = null)
    {
        $cipher       = $this->getCipher();

        if ($cipher == false) {
            return false;
        }

        if ($key == null) {
            $this->addError("missing key for encryption");
            return false;
        }

        $crypt        = base64_decode(urldecode($text));
        $ivSize       = mcrypt_get_iv_size($cipher, MCRYPT_MODE_CBC);
        $iv           = substr($crypt, 0, $ivSize);
        $text         = substr($crypt, $ivSize);
        $td           = mcrypt_module_open($cipher, '', MCRYPT_MODE_CBC, '');
        $plain        = mcrypt_decrypt($cipher, $key, $text, MCRYPT_MODE_CBC, $iv);

        return $plain;
    }

    function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }


}

