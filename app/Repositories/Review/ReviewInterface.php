<?php
declare(strict_types = 1);
namespace App\Repositories\Review;

use App\Repositories\Property\Property;
use App\Repositories\Review\Review;
use App\Repositories\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


interface ReviewInterface
{

    /**
     * Create property scale review 
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function fetchPropertyReviewScale();

     /**
     * Fetch property review by id
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function fetchPropertyReviewByID(int $reviewId):?Review;

     /**
     * Fetch property review by user id
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function fetchPropertyReviewByPropertyID(Property $property, User $user);

    /**
     * Create property scale review units
     * @param int $limit, 
     * @param int $offset
     * @param array $fields
     * @return array
     */
    
    public function fetchScaleUnit(int $limit, int $offset, $fields): array;


    /**
     * Fetch property review 
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function fetchUserPropertyReview(Property $property):?array;

    /**
     * Create property review 
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function createPropertyReview(Property $property , User $user, Review $review):?int;

    /**
     * Create property id review 
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function updatePropertyReviewByID(Property $property , User $user, Review $review, int $reviewId):?int;


    /**
     * Delete property review 
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function deletePropertyReview(Property $property , User $user, Review $review);



    /**
     * Delete property review id
     * @param Property $property
     * @param User $user
     * Property $property , User $user, int $reviewId
     * @return int
     */

    public function deletePropertyReviewByReviewID(Property $property , User $user, int $reviewId): int;



 


}
