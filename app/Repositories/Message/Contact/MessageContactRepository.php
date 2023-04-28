<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Contact;

use PDO;
use App\Repositories\DateTime;
use Illuminate\Support\Facades\DB;

/**
 *
 * @author Asante Foster
 *        
 */
class MessageContactRepository implements MessageContactRepositoryInterface
{

    /**
     *
     * @var PDO
     */
    private $connection;


    public function __construct( )
    {
       $this->connection = DB::connection()->getPdo();
   }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Contact\MessageContactRepositoryInterface::fetchByIds()
     */
    public function fetchByIds(int ...$ids): array
    {
       
        if (! count($ids)) {
            return [];
        }
        
        $time = (new DateTime())->toMysqlDateTime();
        $idStr = implode(',', $ids);

        // TODO: OPTIMIZE QUERY FOR PERFORMANCE push_token

        /**
         *    $query = <<<QUERY
        *SELECT u.id, u.name, u.phone_number, u.email, (
         *   SELECT GROUP_CONCAT(nt.token) AS pushTokenStr FROM auth a
         *   JOIN auth_tokens t ON a.id = t.auth_id
         *   JOIN notification_token nt ON u.id = nt.user_id
         *   WHERE a.user_id = u.id AND t.expires_at IS NOT NULL AND t.expires_at > '$time' GROUP BY a.user_id
        *) AS pushTokens FROM users u WHERE u.id IN($idStr)
        *QUERY;
         */
        $query = <<<QUERY
        
        SELECT u.id, u.name, u.phone_number as phone, u.email, ( SELECT  GROUP_CONCAT(nt.token) 
        AS pushTokenStr FROM auth a 
        LEFT JOIN auth_tokens t ON a.id = t.auth_id
        JOIN notification_token nt ON u.id = nt.user_id WHERE a.user_id = u.id 
        GROUP BY a.user_id ) AS pushTokens 
        FROM users u WHERE u.id IN($idStr);

        QUERY;


        return $this->formatContacts($this->connection->query($query)
            ->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Contact\MessageContactRepositoryInterface::fetchByTopics()
     */
    public function fetchByTopics(string ...$topics): array
    {
       

        $topics = array_filter($topics);

        if (! count($topics)) {
            return [];
        }

        $time = (new DateTime())->toMysqlDateTime();

        $placeholoder = implode(',', str_split(str_repeat('?', count($topics))));

        // TODO: OPTIMIZE QUERY FOR PERFORMANCE
        $query = <<<QUERY
        SELECT u.id, u.name, u.email, (
            SELECT GROUP_CONCAT(t.push_token) AS pushTokenStr FROM auth a
            JOIN auth_tokens t ON a.id = t.auth_id
            WHERE a.user_id = u.id AND t.expires_at IS NOT NULL AND t.expires_at > ? GROUP BY a.user_id
        ) AS pushTokens FROM users u WHERE u.role_id IN(
            SELECT rt.role_id FROM roles_push_notification_topics rt 
            WHERE rt.topic_id IN(SELECT t.id FROM push_notification_topics t WHERE t.topic IN($placeholoder))
        )
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $time, PDO::PARAM_STR);

        foreach ($topics as $index => $topic) {
            $stmt->bindValue($index + 2, $topic, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $this->formatContacts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Formats an array of arrays as a list of contacts
     *
     * @param array $entries
     * @return array
     */
    private function formatContacts(?array $entries): array
    {
        if (empty($entries)) {
            return [];
        }

        $contacts = [];
    
        foreach ($entries as $entry) {
            $contact = new MessageContact();
            $contact->setId($entry['id'] ?? null);
            $contact->setName($entry['name'] ?? null);
            $contact->setPhone($entry['phone'] ?? null);
            $contact->setEmail($entry['email'] ?? null);
            $tokens = explode(',', $entry['pushTokens'] ?? '');
            $contact->setPushTokens(...array_filter($tokens));

            if ($contact->getId()) {
                $contacts[$contact->getId()] = $contact;
            }
        }

        return $contacts;
    }
}

