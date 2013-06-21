<?php


namespace GiveIt\SDK;

class Option extends Object {

    private $types      = array('single_choice', 'multiple_choice', 'delivery', 'layered', 'layered_delivery');

    public function valid()
    {
       if (! in_array($this->type, $this->types)) {
            return false;
       }

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

    public function addChoice(Choice $choice)
    {
        if (! $choice->valid()) {
            return false;
        }

        echo "$choice->id\n";

        $this->choices[$choice->id] = $choice;

        return $this;
    }

    public function addChoices($choices)
    {
        if (empty($choices)) {
            return $this;
        }

        foreach ($choices as $choice) {
            $this->addChoice($choice);
        }

        return $this;
    }

}
