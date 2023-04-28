<?php

namespace App\Repositories;


class OptionalFacility extends Model
{

    /**
     * @var 
     */
    private  $optional_name;

    /**
     * @var 
     */
    private  $optional_rule;

    /**
     * Returns facility optinal name
     *
     * @return string|NULL
     */
    public function getOptionalName(): ?String
    {
        return $this->optional_name;
    }

    /**
     * Set facility optinal name
     * @param string|null $name
     */
    public function setOptionalName(?String $optionalName)
    {
        $this->optional_name = $optionalName;
    }

    /**
     * Returns facility optinal rule
     *
     * @return string|NULL
     */
    public function getOptionalRule(): ?String
    {
        return $this->optional_rule;
    }

    /**
     * Set facility optinal rule
     * @param string|null $name
     */
    public function setOptionalRule(?String $optionalRule)
    {
        $this->optional_rule = $optionalRule;
    }

    /**
     * @return array
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}