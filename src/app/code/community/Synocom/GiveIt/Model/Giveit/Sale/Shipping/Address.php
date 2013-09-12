<?php
/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
class Synocom_GiveIt_Model_Giveit_Sale_Shipping_Address extends Mage_Core_Model_Abstract {

    protected $_firstName;
    protected $_lastName;

    public function setData($key, $value = null) {
        parent::setData($key, $value);
        $this->_initData();

        return $this;
    }

    public function getStreet() {
        $street = array();

        foreach (array(1,2,3,4) as $lineNumber) {
            $propertyName = 'getLine'.$lineNumber;
            if ($this->{$propertyName}()) {
                array_push($street, $this->getLine{$lineNumber}());
            }
        }

        return join(',', $street);
    }

    public function getFirstName() {
        return $this->_firstName;
    }

    public function getLastName() {
        return $this->_lastName;
    }

    protected function _initData() {
        $firstLastName = explode(' ', $this->getName(), 1);

        $this->_firstName = array_pop($firstLastName);
        $this->_lastName = array_pop($firstLastName);
    }

}