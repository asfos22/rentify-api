<?php
declare(strict_types=1);

namespace App\Repositories\Location;

use App\Repositories\Model;
use Exception;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
class State extends Model
{

    /**
     *
     * @var string
     */
    private $code;

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var Country
     */
    private $country;

    /**
     * Returns code
     *
     * @return string|NULL
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Sets code
     *
     * @param string $code
     */
    public function setCode(?string $code)
    {
        $this->code = $code;
    }

    /**
     * Returns name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->iso_code;
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
     * Returns state country
     *
     * @return Country|NULL
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * Sets country
     *
     * @param Country $country
     */
    public function setCountry(?Country $country)
    {
        $this->country = $country;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Model::getId()
     */
    public function getId(): ?int
    {
        throw new Exception(sprintf('Call to undefined method %s.', __METHOD__));
        return null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Model::setId()
     */
    public function setId(?int $id)
    {
        throw new Exception(sprintf('Call to undefined method %s.', __METHOD__));
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Model::toJson()
     */
    public function toJson()
    {
        $content = get_class($this);
        unset($content['id']);

        return $content;
    }
}

