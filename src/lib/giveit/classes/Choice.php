<?php

namespace GiveIt\SDK;

class Choice extends Option {

    public function valid()
    {
        if ($this->id === null) {
            return false;
        }

        if ($this->name == null) {
            return false;
        }

        if (isset($this->price) and ! is_int($this->price)) {
            $this->addError("price for choice $this->id must be an integer");
            return false;
        }

        if (isset($this->tax_percent)) {
            if (! is_int($this->tax_percent)) {
                $this->addError("tax_percent for choice $this->id must be an integer");
                return false;
            }

            if ($this->tax_percent < 0 or $this->tax_percent > 100) {
                $this->addError("tax_percent should be in the range 0-100 (currently $this->tax_percent)");
                return false;
            }
        }

        return true;

    }
}
