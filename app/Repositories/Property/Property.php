<?php

namespace App\Repositories\Property;

use App\Repositories\Auth\Token;
use App\Repositories\Currency\Money;
use App\Repositories\Facility;
use App\Repositories\GeoCode\Location;
use App\Repositories\Media\Media;
use App\Repositories\Model;
use App\Repositories\OptionalFacility;
use App\Repositories\Review\Review;
use App\Repositories\User\Host;
use App\Repositories\User\User;

/**
 *
 * @author  Foster Asante <asantefoster22@gmail.com>
 *
 */
final class Property extends Model
{

    /**
     *
     * @var string
     */
    protected $id;

    /**
     * @var
     */
    private $name;
    /**
     * @var
     */

    private $type;
    /**
     * @var
     */

    private $age;
    /**
     * @var
     */
    private $duration;
    /**
     * @var
     */
    private $status;
    /**
     * @var
     */
    private $bath_room;
    /**
     * @var
     */
    private $bed_room;
    /**
     * @var
     */
    private $sq_ft;

    /**
     * @var
     */
    private $capacity;
    /**/
    /**
     * @var
     */
    private $tag;
    /**
     * @var
     */
    private $score;
    /**
     * @var
     */
    private $description;
    /**
     * @var
     */
    private $money;

    /**
     * @var
     */
    private $facility;

    /**
     * @var
     */
    private $optional_facility;

    /**
     * @var
     */
    private $facility_rule;

    /**
     * @var
     */
    private $review;

    /**
     * @var
     */
    private $location;

    /**
     * @var
     */

    private $link;

    /**
     * @var host
     */
    private $host;

    //protected $created_at;

    //protected $update_at;

    /**
     *
     * @var User
     */
    private $user;

    /**
     *
     * @var Token
     */
    private $token;

    /**
     * @var media
     */
    private $media;

    /**
     * Returns property id
     *
     * @return int|NULL
     */
    public function getID(): ?int
    {
        return $this->id;
    }

    /**
     * Set property id
     * @param int|null $id
     */
    public function setID(?int $id)
    {
        $this->id = $id;
    }

    /**
     *  Sets property name
     * @param string null $name
     */
    public function setName(?String $name)
    {
        $this->name = $name;
    }

    /**
     * Returns name
     *
     * @return String|NULL
     */
    public function getName(): ?String
    {
        return $this->name;
    }

    /**
     * Returns type
     *
     * @return String|NULL
     */
    public function getType(): ?String
    {
        return $this->type;
    }

    /**
     * Returns type
     * @param String|null $type
     */
    public function setType(?String $type)
    {
        $this->type = $type;
    }

    /**
     * Returns duration
     *
     * @return String|NULL
     */
    public function getDuration(): ?String
    {
        return $this->duration;
    }

    /** Sets duration
     * @param String|null $duration
     */
    public function setDuration(?String $duration)
    {
        $this->duration = $duration;
    }

    /**
     * Returns status
     *
     * @return String|NULL
     */
    public function getStatus(): ?String
    {
        return $this->status;
    }

    /**
     *  Sets status
     * @param String|null $status
     */
    public function setStatus(?String $status)
    {
        $this->status = $status;
    }

    /**
     * Returns age
     *
     * @return String|NULL
     */
    public function getAge(): ?String
    {
        return $this->age;
    }

    /**
     * Sets age
     * @param String|null $age
     */
    public function setAge(?String $age)
    {
        $this->age = $age;
    }

    /**
     * Returns bathroom
     *
     * @return String|NULL
     */
    public function getBathRoom(): ?String
    {
        return $this->bath_room;
    }

    /**
     * Sets bath room
     * @param String|null $bathroom
     */
    public function setBathRoom(?String $bathroom)
    {
        $this->bath_room = $bathroom;
    }

    /**
     * Returns bedroom
     *
     * @return String|NULL
     */
    public function getBedRoom(): ?String
    {
        return $this->bed_room;
    }

    /**
     * Sets bedroom
     * @param String|null $bedroom
     */
    public function setBedRoom(?String $bedroom)
    {
        $this->bed_room = $bedroom;
    }

    /**
     * Returns sql ft
     *
     * @return String|NULL
     */
    public function getSqtFt(): ?String
    {
        return $this->sq_ft;
    }

    /**
     * Sets sql ft
     * @param String|null $sqlFt
     */
    public function setSqtFt(?String $sqlFt)
    {
        $this->sq_ft = $sqlFt;
    }

    /**
     * Returns capacity
     *
     * @return String|NULL
     */
    public function getCapacity(): ?String
    {
        return $this->sq_ft;
    }

    /**
     * Sets Capacity
     * @param String|null $capacity
     */
    public function setCapacity(?String $capacity)
    {
        $this->capacity = $capacity;
    }
    /**
     * Returns description
     *
     * @return String|NULL
     */
    public function getDescription(): ?String
    {
        return $this->description;
    }

    /**
     * Sets description
     * @param String|null $desc
     */
    public function setDescription(?String $desc)
    {
        $this->description = $desc;
    }

    /**
     * Returns tag
     *
     * @return String|NULL
     */
    public function getTag(): ?String
    {
        return $this->tag;
    }

    /**
     * Sets tag
     * @param String|null $tag
     */
    public function setTag(?String $tag)
    {
        $this->tag = $tag;
    }

    /**
     * Returns score
     * @return Review|null
     */
    public function getRateScore(): ?Review
    {
        return $this->score;
    }

    /**
     * Sets rate score
     * @param Review|null $review
     */
    public function setRateScore(?Review $review)
    {
        $this->score = $review;
    }

    /**
     * Returns user associated with this auth instance
     *
     * @return User|NULL
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sets user associated with this auth instances
     *
     * @param User $user
     */
    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    /**
     * Returns token associated with this auth instance
     *
     * @return Token|NULL
     */
    public function getToken(): ?String
    {
        return $this->token;
    }

    /**
     * Sets token associated with this auth instances
     * @param String|null $token
     */

    public function setToken(?String $token)
    {
        $this->token = $token;
    }

    /**
     * Returns money associated with this auth instance
     *
     * @return money|NULL
     */
    public function getMoney(): ?Money
    {
        return $this->money;
    }

    /**
     * Sets token associated with this auth instances
     * @param Money|null $money
     */

    public function setMoney(?Money $money)
    {
        $this->money = $money;
    }

    /**
     * Returns property associated with this media
     *
     * @return Price[]
     */
    public function getMedia(): array
    {
        return is_array($this->media) ? $this->media : [];
    }

    /**
     * Sets property for this media
     * @param Media ...$media
     */
    public function setMedia(Media...$media)
    {
        if (count($media)) {
            $this->media = $media;
        } else {
            $this->media = null;
        }
    }

    /**
     * Returns property associated with this facility
     *
     * @return Facility[]
     */
    public function getFacility(): array
    {
        return is_array($this->facility) ? $this->facility : [];
    }

    /**
     * Sets property for this facility
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
     * Returns optional facility
     *
     * @return string|NULL
     */
    public function getOptionalFacility(): ?OptionalFacility
    {
        return $this->optional_facility;
    }

    /**
     * Set optination facility
     * @param string|null $name
     */
    public function setOptionalFacility(?OptionalFacility $optionalFacility)
    {
        $this->optional_facility = $optionalFacility;
    }

    /**
     * Returns property associated with this facility rule
     *
     * @return Facility[]
     */
    public function getFacilityRule(): array
    {
        return is_array($this->facility_rule) ? $this->facility_rule : [];
    }

    /**
     * Sets property for this facility
     * @param Facility ...$facility_rule
     */
    public function setFacilityRule(Facility...$facility_rule)
    {
        if (count($facility_rule)) {
            $this->facility_rule = $facility_rule;
        } else {
            $this->facility_rule = null;
        }
    }

    /**
     * Returns property associated with this review
     *
     * @return Facility[]
     */
    public function getReview(): array
    {
        return is_array($this->review) ? $this->review : [];
    }

    /**
     * Sets property for this review
     * @param Review ...$review
     */
    public function setReview(Review...$review)
    {
        if (count($review)) {
            $this->review = $review;
        } else {
            $this->review = null;
        }
    }

    /**
     * Returns property associated with this location
     *
     * @return Facility[]
     */

    public function getLocation(): array
    {
        return is_array($this->location) ? $this->location : [];
    }

    /* public function getLocation()
    {
    return $this->location;
    }*/
    /**
     * Sets property for this Location
     * @param Location ...$location
     */
    public function setLocation(Location...$location)
    {
        if (count($location)) {
            $this->location = $location;
        } else {
            $this->location = null;
        }
    }
    /* public function setLocation(Location $location)
    {

    $this->location = $location;

    }*/

    /**
     * Set host host
     * @param Host|null $host
     */
    public function setHost(?Host $host)
    {
        $this->host = $host;
    }

    /**
     * @return Host|null
     */

    public function getHost(): ?Host
    {
        return $this->host;
    }

    /**
     * Returns link
     *
     * @return String|NULL
     */
    public function getLink(): ?String
    {
        return $this->link;
    }

    /**
     * Returns link
     * @param String|null $link
     */
    public function setLink(?String $link)
    {
        $this->link = $link;
    }

    /**
     * @return array
     */
    protected function toJson()
    {
        $content = get_object_vars($this);
       // unset($content['id'], $content['updated_at'], $content['null']);
        return $content;
    }
}
