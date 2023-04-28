<?php
declare(strict_types=1);

namespace App\Repositories;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
abstract class Model implements \JsonSerializable
{

    /**
     *
     * @var int
     */
    protected $id;

    /**
     *
     * @var DateTime
     */
    protected $created_at;

    /**
     *
     * @var human created DateTime
     */
    protected $human_date;

    /**
     *
     * @var DateTime
     */
    protected $updated_at;

    /**
     * Returns model unique id
     *
     * @return int|NULL
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets model unique Id
     *
     * @param int $id
     */
    public function setId(?int $id)
    {
        $this->id = $id;
    }

    /**
     * Returns datetime when instance was created
     *
     * @return DateTime|NULL
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->created_at;
    }

    /**
     * Sets datetime on which instances was created
     *
     * @param DateTime $datetime
     */
    public function setCreatedAt(?DateTime  $datetime)
    {
        $this->created_at = $datetime;
    }

    /**
     * Returns datetime when instance was updated
     *
     * @return DateTime|NULL
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated_at;
    }

    /**
     * Sets datetime on which instances was updated
     *
     * @param DateTime $datetime
     */
    public function setUpdatedAt(?DateTime $datetime)
    {
        $this->updated_at = $datetime;
    }


    /**
     * @param $carbonObject
     * @return mixed
     */
    public function getTimeAgo($carbonObject)
    {
        return str_ireplace(
            [' seconds', ' second', ' minutes', ' minute', ' hours', ' hour', ' days', ' day', ' weeks', ' week'],
            ['s', 's', 'm', 'm', 'h', 'h', 'd', 'd', 'w', 'w'],
            $carbonObject->diffForHumans()
        );
    }


    /**
     * Returns datetime when instance was created
     *
     * @return DateTime|NULL
     */
    public function getHumanCreatedAt(): ?DateTime
    {
        return $this->created_at;
    }

    /**
     * Sets human readable datetime on which instances was created
     *
     * @param DateTime $datetime
     */
    public function setHumanCreatedAt(?DateTime $datetime)
    {
        isset($datetime) ? $this->human_date = \Carbon\Carbon::parse($datetime)->diffForHumans() : null ;
        //  $this->human_date = \Carbon\Carbon::parse($datetime)->diffForHumans();//;
    }


    /**
     *
     * {@inheritdoc}
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        $json = $this->toJson();

        if (is_null($json) || is_scalar($json) || !is_iterable($json)) {
            return $json;
        }

        $model = $this->removeNulls($json);

        return empty($model) ? null : $model;
    }

    /**
     * Tells if a model is considered empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        $json = $this->toJson();

        $jsonValue = is_array($json) ? $this->removeNulls($json) : $json;

        return empty($jsonValue);
    }

    /**
     *
     * @return array
     */
    abstract protected function toJson();

    /**
     * Recursively removes all null fields
     *
     * @param array $haystack
     * @return array
     */
    final public function removeNulls(array $haystack): array
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = $this->removeNulls($haystack[$key]);
            }

            if (is_null($value) || (($value instanceof Model) && $value->isEmpty())) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }
}

