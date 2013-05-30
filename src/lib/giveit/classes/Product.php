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

class Product extends Base
{

    public      $renderErrors       = true;
    private     $data               = array();
    private     $requiredFields     = array(
                    'details:code'     => 'string:25',
                    'details:price'    => 'integer',
                    'details:name'     => 'string:50',
                    'details:image'    => 'string',
                 );

    public function __construct($data = Array())
    {
        if ($data){
            $this->data = $data;
        }
        $this->addGiveItData();

        return true;
    }

    /**
     * Set data for the product. This can be instead of the __construct function
     * to be able to set the product step by step
     */
    public function setProductDetails($code, $price, $name, $image)
    {
        $this->data['details']['code'] = $code;
        $this->data['details']['price'] = $price;
        $this->data['details']['name'] = $name;
        $this->data['details']['image'] = $image;
    }
    
    /**
     * Add a delivery option. Shortcut for buyer option so
     * the developer implementing this SDK cannot accidentally add it for the recipient
    */ 
    public function addDeliveryOption($id, $name){
        return $this->addOption('buyer', $id, 'delivery', $name, null, true);
    }
    
    /**
     * Add an option for the buyer
     * @return reference
     */
    public function addBuyerOption($id, $type, $name, $description = '', $mandatory = false){
        return $this->addOption('buyer', $id, $type, $name, $description, $mandatory);
    }
    
    /**
     * Add an option for the recipient
     * @return reference
     */
    public function addRecipientOption($id, $type, $name, $description = '', $mandatory = false){
        return $this->addOption('recipient', $id, $type, $name, $description, $mandatory);
    }
    
    // add a choice to an option, provide reference returned by an 'add option' function
    public function addChoice($ref, $id, $name, $price = 0){
    
        if (!$this->data['options'][$ref[0]][$ref[1]]){
            $this->errors[] = "Could not add choice '$name' because referenced option does not exist";
            return false;
        }
        
        $this->data['options'][$ref[0]][$ref[1]]['choices'][$id]['id'] = $id;
        $this->data['options'][$ref[0]][$ref[1]]['choices'][$id]['name'] = $name;
        
        // only set price if price is set, and the option is for the buyer
        if ($price && $ref[0] == 'buyer'){
            $this->data['options'][$ref[0]][$ref[1]]['choices'][$id]['price'] = $price;
        }
    }
    
    private function addOption($br, $id, $type, $name, $description, $mandatory){
        $this->data['options'][$br][$id]['id'] = $id;
        $this->data['options'][$br][$id]['type'] = $type;
        $this->data['options'][$br][$id]['name'] = $name;
        $this->data['options'][$br][$id]['description'] = $description;
        // convert the boolean into a string
        $this->data['options'][$br][$id]['mandatory'] = $mandatory ? "true" : "false";
        return array($br,$id);
    }
    
    /**
     * Validate that the product data is being given in a valid format
     * Flatten the array and check for the existence of required fields
     */
    public function validate()
    {
        $flat   = $this->flatten($this->data);
        $valid  = true;

        foreach ($this->requiredFields as $fieldName => $fieldType) {

            if (! isset($flat[$fieldName])) {
                $this->addError("missing field $fieldName");
                $valid = false;
                continue;
            }

            $result = $this->validateFieldType($fieldType, $flat[$fieldName]);

            if ($result !== true) {
                $this->addError("$fieldName - $result");
                $valid = false;
            }
        }

        return $valid;
    }

    private function validateFieldType($type, $value)
    {
        if (strpos($type, ':') !== false) {
            list($type, $param) = explode(':', $type);
        }

        switch ($type) {
            case 'integer':

                if (is_int($value)) {
                   return true;
                }

                return 'must be an integer';

            case 'string':

                if (! is_string($value)) {
                    return 'must be a string';
                }

                if (isset($param)) {

                    if (strlen($value) <= $param) {
                        return true;
                    }

                   return "must be no more than $param characters";

                }

                return true;
        }

        return false;
    }

    private function addGiveItData()
    {
        $this->data['give.it'] = array(
          'md5'          => md5(serialize($this->data)),
          'rendered_at'  => date('Y-m-d H:i:s') . ' ' . microtime(true),
          'sdk_version'  => 'PHP ' . \GiveIt\SDK::VERSION,
        );

        return true;
    }

   /**
    * Generate Button HTML
    *
    * This function generates the necessary HTML to render the button.
    *
    */
    public function getButtonHTML($buttonType = 'blue_rect_sm')
    {
        if (! $this->validate()) {
            $this->addError("product data is invalid");
            return $this->getErrorsHTML();
        }

        $parent     = \GiveIt\SDK::getInstance();
        $crypt      = \GiveIt\SDK\Crypt::getInstance();

        if ($parent == false) {
            $this->addError("GiveIt SDK must be instantiated to render buttons");
            return false;
        }

        $encrypted  = $crypt->encode(json_encode($this->data), $parent->dataKey);

        if ($encrypted == false) {
            if (! $this->renderErrors) {
                return false;
            }

            $this->addError($crypt->errors());

            return $this->getErrorsHTML();
        }

        // $encrypted  = urlencode($encrypted);

        $html = "<span class='giveit-button' data-giveit-buttontype='$buttonType' data-giveit-api-key='$parent->publicKey' data-giveit-data='$encrypted'></span>";

        return $html;
    }

    public function getErrorsHTML()
    {
        if (! $this->renderErrors) {
            return false;
        }

        $html = "\n<span class='giveit-button'>";

        foreach ($this->errors() as $error) {
            $html .= "\n\t<span class='giveit-error'>$error</span>";
        }

        $html .= "\n</span>\n";

        return $html;
    }

}
