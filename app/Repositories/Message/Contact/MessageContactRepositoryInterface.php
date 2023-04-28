<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Contact;

interface MessageContactRepositoryInterface
{

    /**
     * Returns all contacts whose Id is in the provided list of Ids.
     *
     * The returned array of contacts must have the Ids of the contact as keys and the contact object as values
     *
     * @param int ...$ids
     * @return MessageContact[]
     */
    public function fetchByIds(int ...$ids): array;

    /**
     * Returns all contact who have subscribed to given topics.
     * The returned array of contacts must have the Ids of the contact as keys and the contact object as values
     *
     * @param string $topics
     * @return MessageContact[]
     */
    public function fetchByTopics(string ...$topics): array;
}

