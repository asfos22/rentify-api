<?php
declare(strict_types = 1);
namespace App\Repositories\Currency;

use App\Repositories\Model;
use Exception;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *        
 */
class Currency extends Model
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
     * @var string
     */
    private $symbol;

    /**
     *
     * @var bool
     */
    private $supported;

    /**
     *
     * @return boolean
     */
    public function isSupported(): ?bool
    {
        return $this->supported;
    }

    /**
     *
     * @param boolean $supported
     */
    public function setSupported(?bool $supported)
    {
        $this->supported = $supported;
    }

    /**
     * Returns code
     *
     * @param string $default
     * @return string|NULL
     */
    public function getCode(?string $default = 'GHS'): ?string
    {
        return $this->code ?? $default;
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
     * Returns symbol
     *
     * @return string|NULL
     */
    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    /**
     * Sets symbol
     *
     * @param string $symbol
     */
    public function setSymbol(?string $symbol)
    {
        $this->symbol = $symbol;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Klloyds\Api\Repositories\Model::getId()
     */
    public function getId(): ?int
    {
        throw new Exception(sprintf('Call to undefined method %s.', __METHOD__));
        return null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Klloyds\Api\Repositories\Model::setId()
     */
    public function setId(?int $id)
    {
        throw new Exception(sprintf('Call to undefined method %s.', __METHOD__));
    }

    /**
     *
     * {@inheritdoc}
     * @see \Klloyds\Api\Repositories\Model::toJson()
     */
    public function toJson()
    {
        $content = get_object_vars($this);
        unset($content['id']);

        return $content;
    }
}

