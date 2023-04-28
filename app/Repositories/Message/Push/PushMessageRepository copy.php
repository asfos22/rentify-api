<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Push;

use App\Repositories\DateTime;
use App\Repositories\Repository;
use App\Repositories\Auth\Token;
use PDO;
use Exception;
use App\Repositories\IDModel;
use App\Repositories\Message\MessageInterface as MessageMessageInterface;
use App\Repositories\Messaging\MessageInterface;
use App\Repositories\Messaging\MessageInterface as MessagingMessageInterface;
use App\Services\Messaging\MessageInterface as ServicesMessagingMessageInterface;


class PushMessageRepository extends Repository implements PushMessageRepositoryInterface
{

    private static $baseQuery = <<<QUERY
    FROM push_notifications m 
    JOIN users u ON u.id = m.user_id 
    JOIN system_events e ON e.id = m.event_id 
    JOIN message_states s ON s.id = m.state_id 
    QUERY;

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::find()
     */
    public function find(int $id, /*FieldCollection $fields*/): ?PushMessage
    {
        $filters = [
            new Filter('id', '=', [
                $id
            ])
        ];

        $properties = $this->fetch(1, 0, $fields, new FilterCollection(...$filters), new OrderCollection());

        return array_shift($properties);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::fetch()
     */
    public function fetch(int $limit, int $offset,/* FieldCollection $fields, FilterCollection $filters, OrderCollection $ordering*/): array
    {
        $selectables = ! $fields->isEmpty() ? $fields->getItems() : $this->filterer->getFieldMapper()->getSelectableFields();

        $selection = $this->filterer->getSelectionBuilder()->build(...$selectables);
        $filtering = $this->filterer->getFilterCompilter()->compile(...$filters);
        $orderingQuery = $this->filterer->getOrderBuilder()->build(...$ordering);

        $filterQuery = $filtering->getQuery();

        if (! empty($filterQuery)) {
            $filterQuery = 'WHERE ' . $filterQuery;
        }

        if (! empty($orderingQuery)) {
            $orderingQuery = 'ORDER BY ' . $orderingQuery;
        }

        $baseQuery = self::$baseQuery;

        $query = <<<MAINQUERY
        SELECT $selection $baseQuery $filterQuery $orderingQuery LIMIT $limit OFFSET $offset
        MAINQUERY;

        $stmt = $this->connection->prepare($query);

        foreach ($filtering->getParameters() as $index => $value) {
            $stmt->bindValue(1 + $index, $value);
        }

        $messagges = [];

        if ($stmt->execute()) {
            $messagges = array_map(function ($model) {
                return $this->filterer->getFieldMapper()->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return $messagges;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::count()
     */
    public function count(/*FilterCollection $filters*/): int
    {
        $filtering = $this->filterer->getFilterCompilter()->compile(...$filters);

        $filterQuery = $filtering->getQuery();

        if (! empty($filterQuery)) {
            $filterQuery = 'WHERE ' . $filterQuery;
        }

        $baseQuery = self::$baseQuery;

        $query = <<<COUNT_QUERY
        SELECT COUNT(*) AS numMessages $baseQuery $filterQuery
        COUNT_QUERY;

        $stmt = $this->connection->prepare($query);

        foreach ($filtering->getParameters() as $index => $value) {
            $stmt->bindValue(1 + $index, $value);
        }

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            return isset($result->numMessages) ? (int) $result->numMessages : 0;
        }

        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::create()
     */
    public function create(MessageInterface $message): int
    {
        $ids = $this->createMany($message);

        return array_shift($ids);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::createMany()
     */
    public function createMany(MessageInterface ...$messages): array
    {
        $time = (new DateTime())->toMysqlDateTime();

        $query = <<<CREATEQUERY
        INSERT INTO push_notifications (user_id, subject, content, event_id, state_id, payload, created_at)
        VALUES (
            (
                SELECT id FROM users WHERE phone = ? LIMIT 1)
                , ?, ?,
                (SELECT id FROM system_events WHERE code = ? LIMIT 1),
                (SELECT id FROM message_states WHERE code = ? LIMIT 1)
                , ?, ?
            )
        CREATEQUERY;

        $stmt = $this->connection->prepare($query);

        $ids = [];

        $this->connection->beginTransaction();

        try {
            foreach ($messages as $message) {
                // We use phone number to match recipients, so ensure all repicients has phone numbers
                if (! $message->getRecipient() || ! $message->getRecipient()->getPhone()) {
                    continue;
                }

                $stmt->bindValue(1, $message->getRecipient()
                    ->getPhone(), PDO::PARAM_STR);
                $stmt->bindValue(2, $message->getSubject(), PDO::PARAM_STR);
                $stmt->bindValue(3, $message->getContent(), PDO::PARAM_STR);
                $stmt->bindValue(4, $message->getEvent(), PDO::PARAM_STR);
                $stmt->bindValue(5, $message->getState(), PDO::PARAM_STR);
                $stmt->bindValue(6, json_encode($message->getPayload()), PDO::PARAM_STR);
                $stmt->bindValue(7, $time, PDO::PARAM_STR);

                $stmt->execute();

                $ids[] = (int) $this->connection->lastInsertId();
            }

            $this->connection->commit();

            return $ids;
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::findForUser()
     */
    public function findForUser(int $user, int $id, FieldCollection $fields): ?PushMessage
    {
        $filters = array(
            new Filter('id', '=', [
                $id
            ])
        );

        $messages = $this->fetchForUser($user, 1, 0, $fields, new FilterCollection(...$filters), new OrderCollection());

        return array_shift($messages);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::fetchForUser()
     */
    public function fetchForUser(int $user, int $limit, int $offset, FieldCollection $fields, FilterCollection $filters, OrderCollection $ordering): array
    {
        $innerFilters = array(
            new Filter('user.id', '=', [
                $user
            ])
        );

        if (count($filters->getItems())) {
            $innerFilters[] = new Filter(null, null, [], null, $filters->getItems());
        }

        return array_map(function ($message) {
            if ($message instanceof PushMessage) {
                $message->setUser(null);
            }
            return $message;
        }, $this->fetch($limit, $offset, $fields, new FilterCollection(...$innerFilters), $ordering));
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::countForUser()
     */
    public function countForUser(int $user, FilterCollection $filters): int
    {
        $innerFilters = array(
            new Filter('user.id', '=', [
                $user
            ])
        );

        if (count($filters->getItems())) {
            $innerFilters[] = new Filter(null, null, [], null, $filters->getItems());
        }

        return $this->count(new FilterCollection(...$innerFilters));
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::subscribe()
     */
    public function subscribe(Token $auth_token, string $push_registration_token): void
    {
        $this->setPushToken($auth_token, $push_registration_token);
    }

    /**
     * Removes all occurrences of a given push token
     *
     * @param string $push_token
     */
    private function removePushToken(string $push_token): void
    {
        $time = (new DateTime())->toMysqlDateTime();

        $query = <<<QUERY
        UPDATE auth_tokens SET push_token = NULL, updated_at = ? WHERE push_token = ?
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindParam(1, $time, PDO::PARAM_STR);
        $stmt->bindParam(2, $push_token, PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Sets a push token for a given auth token/session
     *
     * @param Token $auth_token
     * @param string $push_token
     */
    private function setPushToken(Token $auth_token, string $push_token): void
    {
        $time = (new DateTime())->toMysqlDateTime();

        $this->connection->beginTransaction();

        try {
            $this->removePushToken($push_token);

            $query = <<<QUERY
            UPDATE auth_tokens SET push_token = ?, updated_at = ? WHERE id = ? LIMIT 1
            QUERY;

            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(1, $push_token, PDO::PARAM_STR);
            $stmt->bindValue(2, $time, PDO::PARAM_STR);
            $stmt->bindValue(3, $auth_token->getId(), PDO::PARAM_INT);

            $stmt->execute();

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::unsubscribe()
     */
    public function unsubscribe(Token $auth_token): void
    {
        $time = (new DateTime())->toMysqlDateTime();

        $query = <<<QUERY
        UPDATE auth_tokens SET push_token = NULL, updated_at = ? WHERE id = ? LIMIT 1
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $time, PDO::PARAM_STR);
        $stmt->bindValue(2, $auth_token->getId(), PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::markAsRead()
     */
    public function markAsRead(int $message): int
    {
        $time = (new DateTime())->toMysqlDateTime();

        $query = <<<QUERY
        UPDATE push_notifications SET is_read = 1, read_at = ?, updated_at = ? WHERE id = ? LIMIT 1
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $time, PDO::PARAM_STR);
        $stmt->bindValue(2, $time, PDO::PARAM_STR);
        $stmt->bindValue(3, $message, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::loadUsersTokensByUserIds()
     */
    public function loadUsersTokensByUserIds(int ...$ids): array
    {
        if (! count($ids)) {
            return [];
        }

        $time = (new DateTime())->toMysqlDateTime();
        $idStr = implode(',', $ids);

        $query = <<<QUERY
        SELECT u.id, (
            SELECT GROUP_CONCAT(t.push_token) AS pushTokenStr FROM auth a
            JOIN auth_tokens t ON a.id = t.auth_id
            WHERE a.user_id = u.id AND t.expires_at IS NOT NULL AND t.expires_at > '$time' GROUP BY a.user_id
        ) AS pushTokens, (
            SELECT GROUP_CONCAT(t.topic) AS groupTopicStr FROM roles_push_notification_topics x  
            JOIN push_notification_topics t ON t.id = x.topic_id  
            WHERE u.role_id = x.role_id GROUP BY x.role_id 
        ) AS groupTopics FROM users u WHERE u.id IN($idStr)
        QUERY;

        $mappedTokens = [];

        foreach ($this->connection->query($query)->fetchAll(PDO::FETCH_ASSOC) as $entry) {
            $mappedToken = new MappedPushToken();
            $mappedToken->setOwner(new IDModel());
            $mappedToken->getOwner()->setId($entry['id'] ?? null);
            $tokenStr = $entry['pushTokens'] ?? '';
            $mappedToken->setTokens(...explode(',', $tokenStr));

            $topicStr = $entry['groupTopics'] ?? '';

            $mappedToken->setTopics(...explode(',', $topicStr));

            if ($mappedToken->getOwner()->getId()) {
                $mappedTokens[$mappedToken->getOwner()->getId()] = $mappedToken;
            }
        }

        return $mappedTokens;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::loadUserTokenByUserId()
     */
    public function loadUserTokenByUserId(int $id): ?MappedPushToken
    {
        $tokens = $this->loadUsersTokensByUserIds($id);

        return array_shift($tokens);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::loadUserIdsByBroadcastTopic()
     */
    public function loadUserIdsByBroadcastTopic(string $topic): array
    {
        $query = <<<QUERY
        SELECT u.id FROM users u WHERE u.role_id IN(
            SELECT rt.role_id FROM roles_push_notification_topics rt WHERE rt.topic_id = (SELECT t.id FROM push_notification_topics t WHERE t.topic = ? LIMIT 1)
        )
        QUERY;

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $topic, PDO::PARAM_STR);

        $stmt->execute();

        $ids = array_map(function (array $row) {
            return $row['id'] ?? 0;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));

        return array_unique($ids);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::invalidateTokens()
     */
    public function invalidateTokens(string ...$tokens): void
    {
        if (! count($tokens)) {
            return;
        }

        $time = (new DateTime())->toMysqlDateTime();
        $limit = count($tokens);

        $placeholders = implode(',', array_map(function () {
            return '?';
        }, $tokens));

        $query = <<<QUERY
        UPDATE auth_tokens SET push_token = NULL, updated_at = ? WHERE push_token IN ($placeholders) LIMIT $limit
        QUERY;

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $time, PDO::PARAM_STR);

        foreach ($tokens as $index => $token) {
            $stmt->bindValue($index + 2, $token, PDO::PARAM_STR);
        }

        $stmt->execute();
    }
}

