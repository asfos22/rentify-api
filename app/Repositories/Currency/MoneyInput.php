<?php
declare(strict_types = 1);
namespace App\Repositories\Currency;

/**
 * Class for accepting money inputs
 * @author Asante Foster <asantefoster22@gmail.com>    
 */
class MoneyInput
{

    /**
     *
     * @var string
     */
    private $amount;

    /**
     *
     * @var string
     */
    private $currency;

    /**
     *
     * @return string
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     *
     * @return string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     *
     * @param string $amount
     */
    public function setAmount(?string $amount)
    {
        $this->amount = $amount;
    }

    /**
     *
     * @param string $currency
     */
    public function setCurrency(?string $currency)
    {
        $this->currency = $currency;
    }
}

