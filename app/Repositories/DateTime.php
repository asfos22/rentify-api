<?php
declare(strict_types=1);

namespace App\Repositories;

/**
 *
 *
 */
class DateTime extends \DateTime implements \JsonSerializable
{

    /**
     *
     * {@inheritdoc}
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->format(static::ATOM);
    }

    public function __toString()
    {
        return $this->format(static::ATOM);
    }

    /**
     *
     * @return string
     */
    public function toMysqlDateTime(): string
    {
        return $this->format('Y-m-d H:i:s');
    }
}

