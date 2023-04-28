<?php
declare(strict_types=1);

namespace App\Repositories\Currency;

use App\Repositories\Model;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *
 */
class Money extends Model
{

    /**
     *
     * @var string
     */
    private $amount;

    /**
     *
     * @var Currency
     */
    private $currency;

    /**
     * Returns amount
     *
     * @param string $default
     * @return string|NULL
     */
    public function getAmount(?string $default = '0.00'): ?string
    {
        return $this->amount ?? $default;
    }

    /**
     * Sets amount
     *
     * @param string $amount
     */
    public function setAmount(?string $amount)
    {
        $this->amount = $amount;
    }

    /**
     * Returns currency
     *
     * @return Currency|NULL
     */
    public function getCurrency(): ?Currency
    {
        return $this->currency ?? new Currency();
    }

    /**
     * Sets currency
     *
     * @param Currency $currency
     */
    public function setCurrency(?Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * CHecks if the amount is larger thant the given value
     *
     * @param string $value
     * @return bool
     */
    public function isMoreThan(string $value): bool
    {
        return bccomp($this->getAmount(), $value, 4) === 1;
    }

    /**
     * CHecks if the amount is less thant the given value
     *
     * @param string $value
     * @return bool
     */
    public function isLessThan(string $value): bool
    {
        return bccomp($this->getAmount(), $value, 4) === -1;
    }

    /**
     * CHecks if the amount is equal to the given value
     *
     * @param string $value
     * @return bool
     */
    public function isEqualTo(string $value): bool
    {
        return bccomp($this->getAmount(), $value, 4) === 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Klloyds\Api\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }

    public function __toString()
    {
        return sprintf('%s %.2f', $this->getCurrency()->getCode(), $this->getAmount());
    }
}

