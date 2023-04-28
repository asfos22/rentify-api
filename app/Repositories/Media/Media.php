<?php

namespace App\Repositories\Media;

use App\Repositories\Model;


/**
 *
 * @author Foster Asante<asantefoster22@gmail.com>
 *
 */
final class Media extends Model
{
    /**
     * @var
     */
    protected $id;

    /**
     *
     * @var string
     */
    private $name;

    /**
     * @var
     */
    private $src;

    /**
     * Returns media id
     *
     * @return int|NULL
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets media id
     *
     * @param int $id
     */
    public function setId(?int $id)
    {
        $this->id = $id;
    }

    /**
     * Get media name
     * @return String|null
     */
    public function getName(): ?String
    {
        return $this->name;
    }


    /**
     * Sets media name
     *
     * @param string $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     * Get media src
     * @return String|null
     */
    public function getSrc(): ?String
    {
        return $this->src;
    }


    /**
     * Sets media src
     *
     * @param string $src
     */
    public function setSrc(?string $src)
    {
        $this->src = $src;
    }


    /**
     * @return array
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}

