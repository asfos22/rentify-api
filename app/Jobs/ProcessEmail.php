<?php

namespace App\Jobs;

use App\Repositories\Message\Messaging\EmailMessenger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Swift_Mailer;

use App\Repositories\Message\Push\PushMessage;
use App\Repositories\User\Account;
use App\Repositories\User\User;
use App\Services\Messaging\MessageInterface;
use App\Services\Messaging\MessageUserInterface;
use App\Services\Messaging\MessengerInterface;
use Exception;
use Illuminate\Support\Facades\Config;
use Psr\Log\LoggerInterface;
use Swift_Message;
use Swift_SmtpTransport;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostBodyInterface;
use GuzzleHttp\Exception\ClientException;


class ProcessEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   
    /**
     *
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var string
     */
    private $mime;

    /**
     *
     * @var string
     */
    private $charset;

    /**
     *
     * @var string
     */
    private $sender_email;

    /**
     *
     * @var string
     */
    private $reply_email;

    /**
     *
     * @var string
     */
    private $email_name;

    private $messageUserInterface;

    /**
     *
     * @var string
     */
    private $sender_name;
    public function __construct(

        // Swift_Mailer $mailer, LoggerInterface $logger,
       // string $sender_email,
        //string $sender_name,
       // MessageUserInterface $messageUserInterface,
       // string $mime = 'text/html',
       // string $charset = 'utf8'
       MessageInterface...$messages

    ) {
        $config = Config::get('custom.email');

       
        $transport = (new Swift_SmtpTransport(
            $config['host'],
            $config['port'],
            $config['encryption']
        ))
            ->setAuthMode('PLAIN')
            ->setUsername($config['username'])
            ->setPassword($config['password']);
       // $this->mailer = new Swift_Mailer($transport);

        

    

        //--
       // $this->sender_email = $sender_email;
       // $this->sender_name = $sender_name;
        $this->reply_email = $config['from'];
        $this->email_name = $config['name'];
        $this->messageUserInterface = $messages;//$messageUserInterface;

        //$this->mailer = $mailer;
        // $this->logger = $logger;
        $this->mime = 'text/html';//$mime;
        $this->charset = 'utf8';//$charset;

         //dump( $this->messageUserInterface);


    }

    private function format(string $content): string
    {
        $content = nl2br($content);

        $message = <<<MESSAGE
        <html>
        <body>$content</body>
        </html>
        MESSAGE;

        return $message;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::send()
     */
    
    public function send(MessageInterface...$messages): bool
    {    

       // ProcessEmail::dispatch(...$messages)->onQueue('email');

        //exit();
        try {

            foreach ($this->composeMessage(...$messages) as $message) {
                
            $this->mailer->send($message);

             dump("DUMP MSG");
             dump($message);

             //ProcessEmail::dispatch(...$messages)->onQueue('email');

             //$job = (new  ProcessEmail($message))->onQueue('sms');

            // $this->dispatch($job);
           
             
              
            }
        } catch (Exception $e) {
           /* $this->logger->warning($e->getMessage(), array(
                'trace' => $e->getTraceAsString(),
            ));*/
            dump($e->getMessage());
            return false;
        }

        return true;
    }

    private function composeMessage(MessageInterface...$messages): array
    {
      
        $composedMessages = [];

        foreach ($messages as $message) {

        
            //$recipient = $this->messageUserInterface;//$message->getRecipient();
            $recipients = $message->getRecipients();

            foreach ($recipients as $recipient) {

                if (!$recipient || !$recipient->getEmail()) {
                    continue;
                }

                //$composedMessage = new Swift_Message($message->getSubject(), $this->format($message->getContent()), $this->mime, $this->charset);
                $composedMessage = new Swift_Message(
                    $recipient->getSubject(),
                 $this->format($recipient->getContent()),
                  $this->mime, $this->charset);

                $composedMessage->setFrom(array(
                   /* $this->sender_email*/"asantefoster22@gmail.com"  => "asantefoster22@gmail.com" //$this->email_name, /*$this->sender_name*/
                ));

                $composedMessage->setReplyTo(array(
                    /*$this->sender_email$this->reply_email*/ "asantefoster22@gmail.com" =>"asantefoster22@gmail.com"  //$this->email_name, //$this->sender_name
                ));

                $composedMessage->setTo(array(
                    $recipient->getEmail() => $recipient->getName(),
                ));

                $composedMessages[] = $composedMessage;
            }

            //exit();

            /*if (! $recipient || ! $recipient->getEmail()) {
        continue;
        }

        $composedMessage = new Swift_Message($message->getSubject(), $this->format($message->getContent()), $this->mime, $this->charset);

        $composedMessage->setFrom(array(
        $this->sender_email => $this->email_name/*$this->sender_name*
        ));

        $composedMessage->setReplyTo(array(
        /*$this->sender_email* $this->reply_email => $this->email_name//$this->sender_name
        ));

        $composedMessage->setTo(array(
        $recipient->getEmail() => $recipient->getName()
        ));

        $composedMessages[] = $composedMessage;*/
        }

        return $composedMessages;
    }

    private function composeBroadcastMessage(MessageInterface $message, MessageUserInterface...$recipients): array
    {
        $composedMessages = [];

        foreach ($recipients as $recipient) {

            if (!$recipient->getEmail()) {
                continue;
            }

            $composedMessage = new Swift_Message($message->getSubject(), $this->format($message->getContent()), $this->mime, $this->charset);

            $composedMessage->setFrom(array(
                $this->sender_email => $this->sender_name,
            ));

            $composedMessage->setReplyTo(array(
                $this->sender_email => $this->sender_name,
            ));

            $composedMessage->setTo(array(
                $recipient->getEmail() => $recipient->getName(),
            ));

            $composedMessages[] = $composedMessage;
        }

        return $composedMessages;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::broadcast()
     */
    public function broadcast(string $topic, MessageInterface $message, MessageUserInterface...$recipients): bool
    {
        try {
            foreach ($this->composeBroadcastMessage($message, ...$recipients) as $curMessage) {
                $this->mailer->send($curMessage);
            }
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage(), array(
                'trace' => $e->getTraceAsString(),
            ));

            return false;
        }

        return true;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::subscribe()
     */
    public function subscribe(User $user, array $subscription): void
    {}

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::unsubscribe()
     */
    public function unsubscribe(User $user, array $unsubscription): void
    {}

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::fetchForUser()
     */
    public function fetchForUser(Account $user, /* ParsedFilter $filter*/): array
    {
        return [];
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::countForUser()
     */
    public function countForUser(Account $user, /* FilterCollection $filters*/): int
    {
        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::findForUser()
     */
    public function findForUser(Account $user, int $message, /* FieldCollection $fields*/): ?PushMessage
    {
        return null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::markMessageAsRead()
     */
    public function markMessageAsRead(int $message): void
    {}


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        dump("TEST TEST TEST");
        dump("Send Email");
       // dump($this->swiftMailer);

        dump("Crons Send Email");
       // $this-> send($this->messages);
       // $this->mailer->send($this->message);
       //$this->emailMessenger->send(...$mappedMessages);

       try {

        foreach ($this->composeMessage(...$this->messageUserInterface) as $message) {
     
            
 // Create the SMTP Transport


 $config = Config::get('custom.email');

       
 $transport = (new Swift_SmtpTransport(
    $config['host'],
    $config['port'],
    $config['encryption']
))
    ->setAuthMode('PLAIN')
    ->setUsername($config['username'])
    ->setPassword($config['password']);

   

// Create the Mailer using your created Transport
      $mailer = new Swift_Mailer($transport);

      $mailer->send($message);

      //dump($transport);


     // exit();

         //ProcessEmail::dispatch(...$messages)->onQueue('email');

         //$job = (new  ProcessEmail($message))->onQueue('sms');

        // $this->dispatch($job);
       
         
          
        }
    } catch (Exception $e) {
       /* $this->logger->warning($e->getMessage(), array(
            'trace' => $e->getTraceAsString(),
        ));*/

        dump($e->getMessage());
        return false;
    }
    }
}
