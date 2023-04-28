<?php

namespace App\Http\Controllers\RateReview;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Http\Exception\AccessControlException;
use App\Http\Exception\ResourceNotFoundException;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Property\Property;
use App\Repositories\Property\PropertyInterface;
use App\Repositories\Review\Review;
use App\Repositories\Review\ReviewInterface;
use App\Repositories\User\User;
use App\Services\Messaging\Message;
use App\Services\Messaging\MessageComposeInterface;
use App\Services\Messaging\MessageUserInterface;
use App\Services\Messaging\MessengerInterface;
use Illuminate\Http\Request;

class RateReviewController extends Controller
{

    /**
     * @var AuthRepositoryInterface
     */
    private $authRepository;

    /**
     * @var ReviewInterface
     */
    private $reviewRepository;

    /**
     * @var  PropertyInterface
     */
    private $propertyInterface;

    /**
     * @var
     */
    private $property;

    /**
     * @var
     */
    private $review;

    /**
     * @var  MessageComposeInterface
     */
    private $messageComposeInterface;

    /**
     * @var   MessageUserInterface $messageUserInterface,
     */
    private $messageUserInterface;

    /**
     * @param AuthRepositoryInterface $authRepository
     * @param ReviewInterface  $reviewInterface
     */

    public function __construct(

        AuthRepositoryInterface $authRepository,
        ReviewInterface $reviewRepository,
        PropertyInterface $propertyInterface,
        MessageUserInterface $messageUserInterface,
        MessageComposeInterface $messageComposeInterface

    ) {
        $this->authRepository = $authRepository;
        $this->reviewRepository = $reviewRepository;
        $this->propertyInterface = $propertyInterface;
        $this->messageUserInterface = $messageUserInterface;
        $this->messageComposeInterface = $messageComposeInterface;

    }

    public function fetchPropertyReviewScale(Request $request)
    {
        $fetchPropertyReviewScale = $this->reviewRepository->fetchPropertyReviewScale();

        return response()->json(

            [
                "code" => 200,
                "message" => "OK",
                "payload" => [
                    "items" => $fetchPropertyReviewScale ?? [],
                ],
            ]

        );

    }

    public function createUserPropertyReview(Request $request)
    {

        $auth = $this->authRepository->enforce($request);

        /*if (!$auth->hasPermission(Permission::PERM_REVIEW_SEND)) {
        throw new InsufficientPrivilegeException();
        }*/

        if (!$request->header(Constants::RENTIFY_PROPERTY_TOKEN)) {

            throw new AccessControlException('Sorry resource not found.', 401);
        }

        if ($request->hasHeader(Constants::RENTIFY_PROPERTY_TOKEN) &&

            !empty($request->hasHeader(Constants::RENTIFY_PROPERTY_TOKEN))) {

            $propertyToken = $request->header(Constants::RENTIFY_PROPERTY_TOKEN);

            if (empty($propertyToken)) {

                throw new ResourceNotFoundException('Resource not found', 403);

            }
            $this->property = $this->propertyInterface->findPropertyByToken($propertyToken);

            if (empty($this->property->getID())) {

                throw new ResourceNotFoundException('Resource not found', 404);

            }

        }

        $review = new Review();

        $bag = $request;

        $comment = $bag->get("name");

        $review->setComment($comment);
        $reviewing = array_map(function ($rw) {
            $curReviewBag = $rw;
            $review = new Review();
            $review->setID($curReviewBag['id']);
            $review->setScale($curReviewBag['scale']);
            return $review;
        }, $bag->get('review', []));

        if (count($reviewing)) {
            $review->setReview(...$reviewing);
        }

        $fetchPropertyReviewByPropertyID = $this->reviewRepository->fetchPropertyReviewByPropertyID($this->property, $auth->getUser(), $review);

        if ($fetchPropertyReviewByPropertyID instanceof Review) {

            $review = $this->reviewRepository->updatePropertyReviewByID($this->property, $auth->getUser(), $review, (int) $fetchPropertyReviewByPropertyID->getID());

        }

        if (!$fetchPropertyReviewByPropertyID instanceof Review) {

            $review = $this->reviewRepository->createPropertyReview($this->property, $auth->getUser(), $review);
        }

        /**
         * Send review notification
         */

        $findUserByPropertyID = $this->propertyInterface->findUserByPropertyID($this->property);

        if (empty($findUserByPropertyID->getId())) {

            throw new ResourceNotFoundException('Resource not found', 403);
        }

        $subject = <<<MESSAGE
         has written a new review for your listed property on
        MESSAGE;

        $content = <<<MESSAGE
         wrote a review for your listed property  “ $comment ”
         \n\n Sincerely,\nThe RenSeek Team.
         MESSAGE;

        $messagingMessage = new Message();
        $recipient = $this->messageUserInterface;
        $recipient->setId($findUserByPropertyID->getId());
        $recipient->setName($findUserByPropertyID->getName());
        $recipient->setPhone($findUserByPropertyID->getPhone());
        $recipient->setEmail($findUserByPropertyID->getEmail());
        $recipient->setSubject(sprintf("%s%s %s", $auth->getUser()->getName(), $subject, date('F d,Y', strtotime($fetchPropertyReviewByPropertyID->getCreatedAt()))));
        $recipient->setContent(sprintf("%s %s,\n\n%s %s %s", 'Hi', $findUserByPropertyID->getName() /*$auth->getUser()->getName()*/, 'We wanted to let you know that ', $auth->getUser()->getName(), $content));

        $notifications[] = $findUserByPropertyID->getId();

        $messagingMessage->setRecipients($recipient);
        $messagingMessage->setSender($recipient);
        $messagingMessage->setRecipient($recipient);

        $messagingMessage->setSubject(sprintf('%s%s %s', $auth->getUser()->getName(), "'s", "Review"));
        $messagingMessage->setContent($subject);

        $this->messageUserInterface->setIds(...$notifications);

        $this->messageComposeInterface->composeMessage(
            $messagingMessage,
            $this->messageUserInterface,
            MessengerInterface::CHANNEL_SMS,
            MessengerInterface::CHANNEL_PUSH,
            MessengerInterface::CHANNEL_EMAIL
        );

        return response()->json(

            [
                "code" => 200,
                "message" => sprintf("%s %s,%s %s.", 'Hi', $auth->getUser()->getName() ?? '', 'thank you for reviewing', $findUserByPropertyID->getName() ?? ''),
                "payload" => [
                    //"items" => $this->reviewRepository->fetchPropertyReviewByID($review) ?? [],
                ],
            ]

        );
    }

    public function fetchUserPropertyReview(Request $request)
    {

        if (!$request->header(Constants::RENTIFY_PROPERTY_TOKEN)) {

            throw new AccessControlException('Sorry resource not found.', 401);
        }

        if ($request->hasHeader(Constants::RENTIFY_PROPERTY_TOKEN) &&

            !empty($request->hasHeader(Constants::RENTIFY_PROPERTY_TOKEN))) {

            $propertyToken = $request->header(Constants::RENTIFY_PROPERTY_TOKEN);

            if (empty($propertyToken)) {

                throw new ResourceNotFoundException('Resource not found', 403);

            }
            $this->property = $this->propertyInterface->findPropertyByToken($propertyToken);

            if (empty($this->property->getID())) {

                throw new ResourceNotFoundException('Resource not found', 404);

            }

        }

        return response()->json(

            [
                "code" => 200,
                "message" => "OK",
                "payload" => [
                    "items" => $this->reviewRepository->fetchUserPropertyReview($this->property) ?? [], //$this->reviewRepository->fetchPropertyReviewByID($review) ?? [],
                ],
            ]

        );

        exit();
        $fetchUserPropertyReview = $this->reviewRepository->fetchUserPropertyReview($this->property);
        dump("FETCH REVIEW ", $fetchUserPropertyReview);
    }

}
