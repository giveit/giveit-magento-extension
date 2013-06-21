<?php

namespace GiveIt\SDK;

class Choice extends Option {

    public function valid()
    {
        if ($this->id == null) {
            return false;
        }

        if ($this->name == null) {
            return false;
        }

        if (isset($this->price) and ! is_int($this->price)) {
            return false;
        }

        if (isset($this->tax_percent)) {
            if (! is_int($this->tax_percent)) {
                return false;
            }

            if ($this->tax_percent < 0 or $this->tax_percent > 100) {
                return false;
            }
        }

        return true;

    }
}
