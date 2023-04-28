<?php
declare (strict_types = 1);
namespace App\Repositories\Message;

use App\Jobs\ProcessEmail;
use App\Repositories\Message\Contact\MessageContactRepositoryInterface;
use App\Repositories\Message\MessagingServiceInterface;
use App\Repositories\Message\Messaging\EmailMessenger;
use App\Repositories\Message\Messaging\PushMessenger;
use App\Repositories\Message\Push\PushMessage;
use App\Repositories\Message\Push\PushMessageRepositoryInterface;
use App\Repositories\User\Account;
use App\Repositories\User\User;
use App\Services\Messaging\MessageInterface;
use App\Services\Messaging\Messages\Push\FcmInterface;
//use App\Services\Messaging\MessageInterface as MessagingMessageInterface;
//use App\Services\Messaging\MessengerInterface as MessagingMessengerInterface;
use App\Services\Messaging\MessageUserInterface;
use App\Services\Messaging\MessengerInterface;
use App\Services\TaskRunner\TaskRunnerInterface;
//use App\Services\Messaging\MessageUserInterface as MessagingMessageUserInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class MessagingService implements MessagingServiceInterface
{
    /**
     *
     * @var TaskRunnerInterface
     */
    private $runner;

    /**
     *
     * @var MessageContactRepositoryInterface
     */
    private $contactRepository;

    protected $connection;

    /**
     * Messaging/channel handlers
     *
     * @var MessengerInterface[]
     */
    private $handlers = [];

    private $mappedContact = [];

    private $messengerInterface;

    private $emailMessenger;

    private $recipient;

    private $pushMessageRepositoryInterface;

    private $notificationSettingManagerInterface;

    private $fcmInterface;

    private $messenger;

    private $sender;


    // class EmailMessenger implements MessengerInterface

    public function __construct(
        // TaskRunnerInterface $runner,
        MessageContactRepositoryInterface $contactRepo,
        MessageUserInterface $recipient,
        PushMessageRepositoryInterface $pushMessageRepositoryInterface,
        NotificationSettingManagerInterface $notificationSettingManagerInterface,
        FcmInterface $fcmInterface,
        ///MessagingServiceInterface $messenger,
        MessengerInterface...$handlers,
        //MessengerInterface  $messengerInterface ,

    ) {
        $this->connection = DB::connection()->getPdo();
        // $this->runner = $runner;

        $this->contactRepository = $contactRepo;
        $this->recipient = $recipient;
        $this->pushMessageRepositoryInterface = $pushMessageRepositoryInterface;
        $this->notificationSettingManagerInterface = $notificationSettingManagerInterface;
        $this->fcmInterface = $fcmInterface;
        //$this->messenger = $messenger;
        $this->messengerInterface = $handlers;
        //$this->messengerInterface  = $messengerInterface ;
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getChannel()] = $handler;
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::broadcast()
     */
    public function broadcast(string $topic, MessageInterface $message): void
    {
        $contacts = $this->contactRepository->fetchByTopics($topic);

        if (!count($contacts)) {
            return;
        }

        $this->runner->run(function () use ($topic, $message, $contacts) {
            $mappings = $this->mapMessagesToHandlers($message);

            foreach ($mappings as $channel => $mappedMessages) {
                $handler = $this->handlers[$channel] ?? null;

                if (!$handler) {
                    $this->throwHandlerNotFoundException($channel);
                }

                foreach ($mappedMessages as $curMessage) {
                    $handler->broadcast($topic, $curMessage, ...$contacts);
                }
            }
        });
    }

    /**
     *
     * @param  MessageInterface...$messages
     * @see \App\Services\Messaging\MessagingServiceInterface::send()
     */
    public function send(MessageUserInterface $messageUserInterface,  MessageInterface ...$messages): void
    {

        $this->sender = clone $messageUserInterface;
       /* MessageUserInterface $messageUserInterface;
        sender = clone $messageUserInterface;
        $messagingMessage->setSender($sender);*/

       

        $recipientIds = array_filter(array_map(function (MessageInterface $message) {
            
           
            //dump("RECIPIENT MSG ID", $message->getRecipient() ? $message->getRecipient() : null);
            //exit();
            return $message->getRecipient() ? $message->getRecipient()->getIds() : null;
        }, $messages));

           if (! count($recipientIds)) {

            return;
            }

        // Some message handlers, currently only the push messager, need additional contact information like
        // So we load and provide them as additional contact information

        $recipientIds = array_map('intval', explode(',', (string) implode(",", ...$recipientIds)));

        $contacts = $this->contactRepository->fetchByIds(...$recipientIds); //fetchByIds(...$recipientIds);

        
        
        $messages = array_map(function (MessageInterface $message) use ($contacts) {
            $recipient = $message->getRecipient();
            

            $this->mappedContact = $contacts; //$contacts[$recipient->getId()] ?? null;

            foreach ($contacts as $contact) {

                //--
                $recipient = $this->recipient; ///new MessageUserInterface() ;
                $recipient->setName($contact->getName());
                $recipient->setPhone($contact->getPhone());
                $recipient->setEmail($contact->getEmail());
                $recipient->setSubject("ffdfdfff");
                $recipient->setContent("content content");
                $message->setRecipient($contact);
                $message->setRecipients(...$contacts);
                //---
               // dump("SENDING EMAIL 1 ", $message, "CONTACT " , $recipient);
               // exit();

            }
            if ($recipient) {

                $this->mappedContact = $contacts; 

                

                foreach ($contacts as $contact) {
                
                    //--
                    $notifications[] = $contact->getEmail();
                    $recipient = $this->recipient; ///new MessageUserInterface() ;
                    $recipient->setName($contact->getName());
                    $recipient->setPhone($contact->getPhone());
                    $recipient->setEmail($contact->getEmail());
                    $recipient->setEmail($contact->getEmail());

            

                    $contacts[$contact->setSubject($message->getSubject())] = $message->getSubject();
                    $contacts[$contact->setContent($message->getContent())] = $message->getContent();

                    if ($this->sender->getId() ===  $contact->getId() /*$message->getSender()->getId() === $contact->getId()*/) {
                        $contacts[$contact->setSubject($message->getSender()->getSubject())] = $message->getSender()->getSubject();
                        $contacts[$contact->setContent($message->getSender()->getContent())] = $message->getSender()->getContent();
                    }

                }
            }

            return $message;
        }, $messages);

        //$this->runner->run(function () use ($messages) {
        // $messagingMessage->setRecipient($recipient);

        $mappings = $this->mapMessagesToHandlers(...$messages);
        
        foreach ($mappings as $channel => $mappedMessages) {

            $this->handler = $channel ?? null;

            if (!$this->handler) {
                $this->throwHandlerNotFoundException($channel);
            }

            /*if ($this->handler === MessengerInterface::CHANNEL_SMS) {

                dump("SMS Service");
            }*/

            if ($this->handler === MessengerInterface::CHANNEL_PUSH) {

                $pushMessenger = new PushMessenger(
                    $this->pushMessageRepositoryInterface,
                    $this->notificationSettingManagerInterface,
                    $this->fcmInterface
                );
                
                $pushMessenger->send(...$mappedMessages);
            }

            if ($this->handler === MessengerInterface::CHANNEL_EMAIL) {  

                $emailMessenger = new EmailMessenger(
                    $this->recipient->getEmail(),
                    $this->recipient->getName(), 
                    $this->recipient
                );
              
                $emailMessenger->send(...$messages);

            }
        }
        

    }

    /**
     * Maps messages to their respective handlers
     *
     * @param MessageInterface ...$messages
     * @return MessageInterface[]
     */
    private function mapMessagesToHandlers(MessageInterface...$messages): array
    {
        $mappings = [];

        foreach ($messages as $message) {
            foreach ($message->getChannels() as $channel) {
                if (!array_key_exists($channel, $mappings)) {
                    $mappings[$channel] = [];
                }

                $mappings[$channel][] = $message;
            }
        }

        return $mappings;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::subscribe()
     */
    public function subscribe(User $user, string $channel, array $subscription): void
    {
        if (!array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

        $this->handlers[$channel]->subscribe($user, $subscription);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::unsubscribe()
     */
    public function unsubscribe(User $user, string $channel, array $unsubscription): void
    {
        if (!array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

        $this->handlers[$channel]->unsubscribe($user, $unsubscription);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::fetchForUser()
     */
    public function fetchForUser(string $channel, Account $user): array
    {
        if (!array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

        return $this->handlers[$channel]->fetchForUser($user);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::countForUser()
     */
    public function countForUser(string $channel, Account $user): int
    {
        if (!array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

        return $this->handlers[$channel]->countForUser($user);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::findForUser()
     */
    public function findForUser(string $channel, Account $user, int $message): ?PushMessage
    {
        if (!array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

        return $this->handlers[$channel]->findForUser($user, $message);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::markMessageAsRead()
     */
    public function markMessageAsRead(string $channel, PushMessage $message): void
    {
        if (!array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

        $this->handlers[$channel]->markMessageAsRead($message->getId());
    }

    protected function throwHandlerNotFoundException(string $channel, int $statusCode = 500): void
    {
        throw new Exception(sprintf('No handler defined for "%s" messaging channel.', $channel), $statusCode);
    }

}
