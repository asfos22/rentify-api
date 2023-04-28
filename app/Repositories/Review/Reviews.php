<?php

namespace App\Repositories\Review;

use App\Repositories\Model;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class Reviews extends Model
{

    protected $id;

    /**
     * @var
     */
    private $name;

    /***
     * @var
     */
    private $description;

    /**
     * @var
     */
    private $scale;

    /**
    * @var
    */
    private $comment;

    /**
     * Returns rate id
     *
     * @return id|NULL
     */
    public function getID(): ?int
    {
        return $this->id;
    }

    /**
     *  Sets rate id
     * @param int |null $id
     */
    public function setID(?int $id)
    {
        $this->id = $id;
    }

    /**
     * Returns review ID
     *
     * @return string|NULL
     */
    public function getReviewID(): ?int
    {
        return $this->reviewID;
    }

    /**
     * Sets service rate
     *
     * @param string|null $reviewID
     *
     */
    public function setReviewID(?int $reviewID)
    {
        $this->reviewID = $reviewID;
    }

    /**
     * Returns  rate name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *  Sets rate name
     * @param string|null $name
     */
    public function setName(?String $name)
    {
        $this->name = $name;
    }

    /**
     * Returns description
     *
     * @return string|NULL
     */
    public function getDescription(): ?String
    {
        return $this->description;
    }

    /**
     *  Sets description
     * @param string|null $description
     */
    public function setDescription(?String $description)
    {
        $this->description = $description;
    }


     /**
     * Returns comment
     *
     * @return string|NULL
     */
    public function getComment(): ?String
    {
        return $this->comment;
    }

    /**
     *  Sets comment
     * @param string|null $comment
     */
    public function setComment(?String $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Returns  rate count
     *
     * @return int|NULL
     */
    public function getRateCount(): ?int
    {
        return $this->total;
    }

    /**
     * Sets rate count
     * @param int|0 $total
     */
    public function setRateCount(?int $total = 0)
    {
        $this->total = $total;
    }

    /**
     * Returns  rate rate
     *
     * @return string|NULL
     */
    public function getRateScore(): ?string
    {
        return $this->rate;
    }

    /**
     * Sets rate score
     * @param string|null $rate
     */
    public function setRateScore(?string $rate)
    {
        $this->rate = $rate;
    }

    /**
     * Returns scale
     *
     * @return string|NULL
     */
    public function getScale(): ?int
    {
        return $this->scale;
    }

    /**
     *  Sets scale
     * @param array|null $rate
     */
    public function setScale(int $scale = 0)
    {
        $this->scale = $scale;
    }

    /**
     * @return array
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}
