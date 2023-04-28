<?php
declare(strict_types = 1);
namespace App\Repositories\Location;

use App\Repositories\Model;
use Exception;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
class Country extends Model
{

    /**
     *
     * @var string
     */
    private $iso_code;

    /**
     *
     * @var string
     */
    private $iso_code_3;

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $phone_code;

    /**
     *
     * @var bool
     */
    private $supported;

    /**
     * Returns country name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets country name
     *
     * @param string $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns country phone code
     *
     * @return string|NULL
     */
    public function getPhoneCode(): ?string
    {
        return $this->phone_code;
    }

    /**
     * Sets country phone code
     *
     * @param string $code
     */
    public function setPhoneCode(?string $code)
    {
        $this->phone_code = $code;
    }

    /**
     * Returns country iso code
     *
     * @return string|NULL
     */
    public function getIsoCode(): ?string
    {
        return ! empty($this->iso_code) ? strtoupper($this->iso_code) : null;
    }

    /**
     * Sets country iso code
     *
     * @param string $code
     */
    public function setIsoCode(?string $code)
    {
        $this->iso_code = $code;
    }

    /**
     * Returns country (3 letter) iso code
     *
     * @return string|NULL
     */
    public function getIsoCode3(): ?string
    {
        return ! empty($this->iso_code_3) ? strtoupper($this->iso_code_3) : null;
    }

    /**
     * Sets country iso (3 letter) code
     *
     * @param string $code
     */
    public function setIsoCode3(?string $code)
    {
        $this->iso_code_3 = $code;
    }

    /**
     * Tells if a country is supported
     *
     * @return bool
     */
    public function isSupported(): bool
    {
        return true === $this->supported;
    }

    /**
     * Sets whether a country is supported
     *
     * @param bool $state
     */
    public function setSupported(?bool $state)
    {
        $this->supported = $state;
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
        return get_object_vars($this);
    }
}

