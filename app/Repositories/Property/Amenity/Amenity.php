<?php
declare (strict_types = 1);

namespace App\Repositories\Property\Amenity;

use App\Repositories\Model;

/**
 *
 * @author  Foster Asante <asantefoster22@gmail.com>
 *
 */
final class Amenity extends Model
{

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $desc;

    /**
     * @var string
     */
    private $rate;

    /**
     *
     * @var ids[]
     */
    //private $ids = [];

    /**
     * Returns id
     *
     * @return string|NULL
     */
    /* public function getid(): ?int
    {
    return $this->id;
    }*/

    /**
     * Sets id
     *
     * @param string $id
     */
    /* public function setId(?int $id)
    {
    $this->id = $id;
    }*/

    /**
     *
     * @var Amenities[]
     */
    private $amenities;

    /**
     *
     * @var Rules[]
     */
    private $rules;

    /**
     * @var facility[]
     */
    private $facility;

    /**
     * @var string optional_rule
     */
    private $optional_rule;

    /**
     * Returns name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets name
     *
     * @param string $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns description
     *
     * @return string|NULL
     */
    public function getDecription(): ?string
    {
        return $this->desc;
    }

    /**
     * Sets description
     *
     * @param string $description
     */
    public function setDecription(?string $description)
    {
        $this->desc = $description;
    }

    /**
     * Returns ids associated with this amenties
     *
     * @return ids[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * Sets id
     *
     * @param Array $ids
     */
    public function setIds(array $ids)
    {
        $this->ids = $ids;
    }

    /**
     *
     * @return \App\Repositories\Amenities ...$amenities[]
     */
    public function getAmenities(): array
    {
        return $this->amenities ?? [];
    }

    /**
     *
     * @param \App\Repositories\Amenities ...$amenities
     */
    /* public function setAmenitie(Amenity ...$amenity)
    {
    $this->amenities = count($amenity) ? $amenity : null;
    }*/

    /**
     *
     * @param \App\Repositories\Amenities ...$amenities
     */
    public function setAmenities(int...$amenity)
    {
        $this->amenities = count($amenity) ? $amenity : null;
    }

    /**
     *
     * @return \App\Repositories\Rules ...$rules[]
     */
    public function getRulesTerms(): array
    {
        return $this->rules ?? [];
    }

    /**
     *
     * @param \App\Repositories\int ...$rules
     */
    public function setRulesTerms(int...$rules)
    {
        $this->rules = count($rules) ? $rules : null;
    }

    /**
     * Returns category pricing
     *
     * @return [] Facility
     */
    public function getFacility(): array
    {
        return is_array($this->facility) ? $this->facility : [];
    }

    /**
     * Sets facilities for property
     *
     * @param Facility ...$facility
     */
    public function setFacility(Facility...$facility)
    {
        if (count($facility)) {
            $this->facility = $facility;
        } else {
            $this->facility = null;
        }
    }

    /**
     * Returns rate
     *
     * @param string $default [1 month]
     * @return string|NULL
     */
    public function getRate(?string $default = '30.4167'): ?string
    {
        return $this->rate ?? $default;
    }

    /**
     * Sets rate
     *
     * @param string $rate
     */
    public function setRate(?string $rate)
    {
        $this->rate = $rate;
    }

    /**
     * Returns optional rule
     *
     * @return string|NULL
     */
    public function getOptionalRule(): ?string
    {
        return $this->optional_rule;
    }

    /**
     * Sets optional_rule
     *
     * @param string $optional_rule
     */
    public function setOptionalRule(?string $optional_rule)
    {
        $this->optional_rule = $optional_rule;
    }

    protected function toJson()
    {
        $content = get_object_vars($this);
        //unset($content['id']);
        return $content;
    }
}
