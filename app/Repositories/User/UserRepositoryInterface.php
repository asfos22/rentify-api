<?php
declare(strict_types=1);

namespace App\Repositories\User;


/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *
 */
interface UserRepositoryInterface
{

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @return array
     */
    public function fetch(): array;


    /**
     * Fetches a single user by id
     *
     * @param int $id
     * @return User | null
     */
    public function find(int $id): ?User;

    /**
     * Fetches a single user by email
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;


    /**
     * Fetches a single user by email or phone
     * @param string|null $email
     * @param string|null $phone
     * @return User|null
     */
    public function findByEmailOrPhone(string $email = null, string $phone = null): ?User;

    /**
     * Fetches a single user by phone
     *
     * @param string $phone
     *
     * @return User | null
     */
    public function findByPhone(string $phone): ?User;

    /**
     * return newly create user
     * @param User $user
     * @return  int Unique id of newly created user
     */
    public function create(User $user):  User;

    /**
     * @param int $id
     * @param User $user
     * @return int Number of affected records
     * @return int
     */
    public function update(int $id, User $user): int;


}

