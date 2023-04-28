<?php
declare(strict_types = 1);
namespace App\Repositories\Currency;

use App\Repositories\User\User;


/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>

 *        
 */
interface CurrencyRepositoryInterface
{

    /**
     * Returns number of currencies given count
     *
     * @return int
     */
    public function count(): int;

    /**
     * Fetches a number of currencies
     *
     * @param int $limit
     * @param int $offset
     * @param FieldCollection $fields
     * @param FilterCollection $filters
     * @param OrderCollection $ordering
     * @return Currency[]
     */
    public function fetch(int $limit, int $offset): array;

    /**
     * Finds a single currency by Id
     *
     * @param string $code
     * @param FieldCollection $fields
     * @return Currency|NULL
     */
    public function findByCode(string $code): ?Currency;

    /**
     * Creates a single currency
     *
     * @param Currency $currency
     * @return int
     */
    public function create(Currency $currency): int;

    /**
     * Updates a single existing currency
     *
     * @param int $id
     * @param Currency $currency
     * @param User $updator
     * @return int
     */
    public function update(int $id, Currency $currency, User $updator): int;

    /**
     * Deletes an existing payment method
     *
     * @param int $id
     * @param User $updator
     * @return int
     */
    public function delete(int $id, User $updator): int;
}

