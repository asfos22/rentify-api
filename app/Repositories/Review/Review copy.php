<?php

namespace App\Repositories\Review;

use App\Repositories\Model;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class ReviewOld extends Model
{

    protected $id;

    /**
     * @var string
     */
    private $reviewID;

    /**
     * @var string
     */
    private $service;

    /**
     * @var string
     */
    private $money_value;

    /**
     * @var string
     */

    private $location;

    /**
     * @var string
     */
    private $cleanliness;


    /**
     * @var string
     */
    private $rate;

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
    private $score;


    /**
     * @var
     */
    private $total;

    /**
     * @var
     */
    private $scale;

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
     * Returns service rate
     *
     * @return string|NULL
     */
    public function getService(): ?string
    {
        return $this->service;
    }

    /**
     * Sets service rate
     *
     * @param string $service
     */
    public function setService(?string $service)
    {
        $this->service = $service;
    }


    /**
     * Returns location rate
     *
     * @return string|NULL
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Sets location rate
     *
     * @param string $location
     */
    public function setLocation(?string $location)
    {
        $this->location = $location;
    }


    /**
     * Returns money rate
     *
     * @return string|NULL
     */
    public function getMoneyValue(): ?string
    {
        return $this->money_value;
    }

    /**
     * Sets money value rate
     *
     * @param string $moneyValue
     */
    public function setMoneyValue(?string $moneyValue)
    {
        $this->money_value = $moneyValue;
    }


    /**
     * Returns cleanliness rate
     *
     * @return string|NULL
     */
    public function getCleanliness(): ?string
    {
        return $this->cleanliness;
    }

    /**
     * Sets cleanliness rate
     *
     * @param string $cleanliness
     */
    public function setCleanliness(?string $cleanliness)
    {
        $this->cleanliness = $cleanliness;
    }


    /**
     * Returns cleanliness rate
     *
     * @return string|NULL
     */
    public function getRate(): ?Array
    {
        return $this->score;
    }

    /**
     *  Sets star rate
     * @param array|null $rate
     */
    public function setRate(?Array $rate)
    {
        $this->score = $rate;
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
     * Returns rate description
     *
     * @return string|NULL
     */
    public function getDescription(): ?String
    {
        return $this->description;
    }

    /**
     *  Sets rate description
     * @param string|null $description
     */
    public function setDescription(?String $description)
    {
        $this->description = $description;
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

