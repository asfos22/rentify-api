<?php

namespace App\Repositories;


class Facility extends Model
{

    /**
     * @var id
     */
    protected $id;

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $desc;

    /**
     * Returns facility id
     *
     * @return int|NULL
     */
    public function getID(): ?int
    {
        return $this->id;
    }

    /**
     * Set facility  id
     * @param int|null $id
     */
    public function setID(?int $id)
    {
        $this->id = $id;
    }


    /**
     * Returns facility name
     *
     * @return string|NULL
     */
    public function getName(): ?String
    {
        return $this->name;
    }

    /**
     * Set facility name
     * @param string|null $name
     */
    public function setName(?String $name)
    {
        $this->name = $name;
    }

    /**
     * Returns facility description
     *
     * @return string|NULL
     */
    public function getDescription(): ?String
    {
        return $this->desc;
    }

    /**
     * Set facility description
     * @param string|null $name
     */
    public function setDescription(?String $desc)
    {
        $this->desc = $desc;
    }

    /**
     * @return array
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}