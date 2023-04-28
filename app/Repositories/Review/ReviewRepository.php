<?php
declare (strict_types = 1);
namespace App\Repositories\Review;

use App\Repositories\Model;
use App\Repositories\Property\Property;
use App\Repositories\Review\Review;
use App\Repositories\User\User;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use PDO;
use stdClass;

class ReviewRepository implements ReviewInterface
{

    private static $mainQuery = <<<QUERY

            WITH cteReviews (review_id, reviews) AS (
                SELECT sr.review_id, JSON_ARRAYAGG(JSON_OBJECT(
                    'id', sr.id,
                    'name',pr.name, 
                    'scale', sr.star,
                    'created_at', DATE_FORMAT(sr.created_at, '%Y-%m-%dT%H:%i:%sZ'),
                    'updated_at', DATE_FORMAT(sr.updated_at, '%Y-%m-%dT%H:%i:%sZ')
                )) AS reviews FROM sub_reviews sr
                LEFT JOIN reviews r ON r.id = sr.review_id
                LEFT JOIN property_review pr ON pr.id = sr.property_review_id
                GROUP BY sr.review_id
            )
            SELECT
            r.id AS id,
            r.name AS name,
            r.review AS comment,
            r.star AS scale,
            r.created_at AS CreatedAt,
            r.updated_at AS UpdatedAt,
            cr.reviews,
            JSON_OBJECT(
                'id', u.id,
                'name', u.name,
                'updated_at', DATE_FORMAT(u.updated_at, '%Y-%m-%dT%H:%i:%sZ')
            ) as user
            FROM reviews r
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN cteReviews cr ON cr.review_id = r.id
           

    QUERY;

    const REVIEW_SCALE_MAIN_QUERY = <<<SCALE_MAIN_QUERY

            pr.id AS id, pr.name AS name, pr.code AS code,rs.scale AS scale,pr.created_at AS CreatedAt, pr.updated_at AS UpdatedAt
            FROM property_review pr
            JOIN reviews_statues rs ON rs.id = pr.reviews_statues_id

         SCALE_MAIN_QUERY;

    const SUB_COUNT_QUERY = <<<COUNT_QUERY

            SELECT COUNT(*) AS numCodes FROM delivery_codes c
            JOIN deliveries d ON d.id = c.delivery_id
            JOIN properties v ON v.id = c.vehicle_id
            LEFT JOIN users s ON s.id = c.starter_id
            LEFT JOIN users z ON z.id = c.completer_id

    COUNT_QUERY;

    protected $connection;

    private $units;

    public function __construct( /*Utils $utils*/)
    {
        $this->connection = DB::connection()->getPdo();

        // $this->util = $utils;
    }

    /**
     * Create property scale review
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function fetchPropertyReviewScale()
    {

        $reviewScaleQuery = self::REVIEW_SCALE_MAIN_QUERY;

        $query = <<<QUERY

         SELECT
         $reviewScaleQuery
        QUERY;

        $stmt = $this->connection->prepare($query);

        if ($stmt->execute()) {

            return array_map(function ($p) {

                $reviewMapper = new ReviewMapper();
                return $reviewMapper->format($p);
                //return $this->format($p);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }

        return [];

    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::count()
     */
    public function countPropertyReview(): int
    {

        $query = "SELECT COUNT(*) AS numReview FROM  property_review pr";

        $stmt = $this->connection->prepare($query);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return isset($result->numTermsRules) ? (int) $result->numTermsRules : 0;
        }

        return 0;
    }

    /**
     * Fetch property review id
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function fetchPropertyReviewByID(int $reviewId): ?Review
    {

        /* $query = <<<MAINQUERY
        SELECT
        r.id AS id, r.name AS name, r.review AS comment, sr.star AS scale, r.created_at AS CreatedAt, r.updated_at AS UpdatedAt
        FROM reviews r
        JOIN house h ON  h.id = r.house_id
        JOIN users u ON u.id = r.user_id
        JOIN sub_reviews sr ON sr.review_id = r.id
        WHERE r.id = ? ORDER BY r.id DESC LIMIT 1

        MAINQUERY;*/

        $mainQuery = self::$mainQuery;

        $query = <<<MAINQUERY
           $mainQuery
           WHERE r.id = ? ORDER BY r.id DESC LIMIT 1
           MAINQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $reviewId, PDO::PARAM_STR);
        //$stmt->bindValue(2, $property->getID(), PDO::PARAM_STR);

        $item = [];

        if ($stmt->execute()) {

            $item = array_map(function ($model) {
                $reviewMapper = new ReviewMapper();
                return $reviewMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return array_shift($item);
    }

    /**
     * Fetch property review by user id
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function fetchPropertyReviewByPropertyID(Property $property, User $user)
    {

        $query = <<<MAINQUERY

        SELECT
        r.id AS id, r.name AS name,r.created_at AS CreatedAt, r.updated_at AS UpdatedAt
        FROM reviews r
        JOIN house h ON  h.id = r.house_id
        JOIN users u ON u.id = r.user_id
        WHERE r.user_id = ? AND r.house_id = ? ORDER BY r.id DESC LIMIT 1

        MAINQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $user->getId(), PDO::PARAM_STR);
        $stmt->bindValue(2, $property->getID(), PDO::PARAM_STR);

        $item = [];

        if ($stmt->execute()) {

            $item = array_map(function ($model) {
                $reviewMapper = new ReviewMapper();
                return $reviewMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return array_shift($item);
    }

    /**
     * Create property scale review units
     * @param int $limit,
     * @param int $offset
     * @param array $fields
     * @return array
     */
    public function fetchScaleUnit(int $limit, int $offset, $fields): array
    {

        $selection = self::REVIEW_SCALE_MAIN_QUERY;

        $query = <<<MAINQUERY
        SELECT
        pr.id AS id, pr.name AS name, pr.code AS code,rs.scale AS scale,pr.created_at AS CreatedAt, pr.updated_at AS UpdatedAt
        FROM property_review pr
        JOIN reviews_statues rs ON rs.id = pr.reviews_statues_id
        $fields
        LIMIT $limit OFFSET $offset

        MAINQUERY;

        $stmt = $this->connection->prepare($query);

        $items = [];

        if ($stmt->execute()) {

            $items = array_map(function ($model) {

                $reviewMapper = new ReviewMapper();
                return $reviewMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return $items;
    }

    /**
     * Fetch property review
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function fetchUserPropertyReview(Property $property):?array
    {

        $mainQuery = self::$mainQuery;

        $query = <<<MAINQUERY
           $mainQuery
           WHERE r.house_id = ? ORDER BY r.id DESC LIMIT 10000
           MAINQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $property->getID(), PDO::PARAM_STR);
        //$stmt->bindValue(2, $property->getID(), PDO::PARAM_STR);
        if ($stmt->execute()) {

            return array_map(function ($p) {
                $reviewMapper = new ReviewMapper();
                return $reviewMapper->format($p);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }

        return [];

        /*$item = [];

        if ($stmt->execute()) {

            $item = array_map(function ($model) {
            
                $reviewMapper = new ReviewMapper();
                return $reviewMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }
        
        return array_shift($item);*/
        //dump("PROPERTY REVIEW ");
        //exit();
    }

    /**
     * Create property review
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function createPropertyReview(Property $property, User $user, Review $review): ?int
    {
        $currReview = $review->getReview();

        $time = (new DateTime())->format('Y-m-d H:i:s');

        $this->connection->beginTransaction();

        try {
            $userQuery = <<<USERQUERY

            INSERT INTO reviews (
                name,
                review,
                house_id,
                star,
                user_id,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE updated_at = ?
            USERQUERY;

            $stmt = $this->connection->prepare($userQuery);

            $stmt->bindValue(1, $user->getName(), PDO::PARAM_STR);
            $stmt->bindValue(2, $review->getComment(), PDO::PARAM_STR);
            $stmt->bindValue(3, $property->getID(), PDO::PARAM_STR);
            $stmt->bindValue(4, 1, PDO::PARAM_STR);
            $stmt->bindValue(5, $user->getID(), PDO::PARAM_STR);
            $stmt->bindValue(6, $time, PDO::PARAM_STR);
            $stmt->bindValue(7, $time, PDO::PARAM_STR);

            $stmt->execute();

            $reviewLastId = (int) $this->connection->lastInsertId();

            $lengthReviewId = [];

            if ($review->getReview()) {
                foreach ($review->getReview() as $review) {

                    $lengthReviewId[] = $review->getid();
                    sort($lengthReviewId);

                    $reviewId = implode(',', array_map('intval', $lengthReviewId)); //$review->getId();
                    $query = " WHERE pr.id IN ($reviewId)";

                    $this->units = $this->fetchScaleUnit(count($lengthReviewId), 0, $query);

                }

            }

            $query = <<<QUERY
            INSERT INTO sub_reviews (
                star,
                review_id,
                property_review_id,
                created_at
                )
                VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE updated_at = ?
            QUERY;

            $stmt = $this->connection->prepare($query);

            $mergedReview = array_column(array_merge($currReview, $review->getReview()), null, 'id');
            ksort($mergedReview);

            foreach ($mergedReview as $rw) {

                foreach ($this->units as $unit) {

                    if ($rw->getScale() <= $unit->getScale() && $rw->getid() == $unit->getid()) {
                        $stmt->bindValue(1, $rw->getScale(), PDO::PARAM_INT);
                    }

                    $stmt->bindValue(2, $reviewLastId, PDO::PARAM_STR);
                    $stmt->bindValue(3, $unit->getid(), PDO::PARAM_INT);
                    $stmt->bindValue(4, $time, PDO::PARAM_STR);
                    $stmt->bindValue(5, $time, PDO::PARAM_STR);

                }
                $stmt->execute();

            }

            $this->connection->commit();
            return $reviewId;
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

    }

    /**
     * Create property update review
     * @param Property $property
     * @param User $user
     * @return Review
     */

    public function updatePropertyReviewByID(Property $property, User $user, Review $review, int $reviewId): ?int
    {

        $currReview = $review->getReview();

        $time = (new DateTime())->format('Y-m-d H:i:s');

        $this->connection->beginTransaction();

        try {
            $userQuery = <<<USERQUERY

            UPDATE reviews r INNER JOIN users u ON r.user_id = u.id
            SET r.name = ?,
                r.review = ?,
                r.house_id = ?,
                r.star = ?,
                r.user_id = ?,
                r.updated_at = ?
            WHERE r.id = ? AND r.user_id = ?

            USERQUERY;

            $stmt = $this->connection->prepare($userQuery);

            $stmt->bindValue(1, $user->getName(), PDO::PARAM_STR);
            $stmt->bindValue(2, $review->getComment(), PDO::PARAM_STR);
            $stmt->bindValue(3, $property->getID(), PDO::PARAM_STR);
            $stmt->bindValue(4, 1, PDO::PARAM_STR);
            $stmt->bindValue(5, $user->getID(), PDO::PARAM_STR);
            $stmt->bindValue(6, $time, PDO::PARAM_STR);
            $stmt->bindValue(7, $reviewId, PDO::PARAM_STR);
            $stmt->bindValue(8, $user->getID(), PDO::PARAM_STR);

            $stmt->execute();

            //$reviewId = (int) $this->fetchPropertyReviewByPropertyID($property, $user)->getID(); //(int) $this->connection->lastInsertId();
            $this->deletePropertyReviewByReviewID($property, $user, $reviewId);

            $lengthReviewId = [];

            if ($review->getReview()) {
                foreach ($review->getReview() as $review) {

                    $lengthReviewId[] = $review->getid();
                    sort($lengthReviewId);

                    $implodeReviewId = implode(',', array_map('intval', $lengthReviewId)); //$review->getId();
                    $query = " WHERE pr.id IN ($implodeReviewId)";

                    $this->units = $this->fetchScaleUnit(count($lengthReviewId), 0, $query);

                }

                $query = <<<QUERY
                INSERT INTO sub_reviews (
                    star,
                    review_id,
                    property_review_id,
                    created_at
                    )
                    VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE updated_at = ?
              QUERY;

                $stmt = $this->connection->prepare($query);

                $mergedReview = array_column(array_merge($currReview, $review->getReview()), null, 'id');
                ksort($mergedReview);
                
               /* foreach ($currReview as $key => $val) {

                    foreach ($this->units as $unit) {
                    
                    if($unit->getId()=== $val->getId() &&
                     $val->getScale() <= $unit->getScale()){
                        $packItem[] = $val;
                    }
                    }
                }
                foreach ($packItem as $unit) {

                    $stmt->bindValue(1, $unit->getScale(), PDO::PARAM_INT);
                    $stmt->bindValue(2, $reviewId, PDO::PARAM_STR);
                    $stmt->bindValue(3, $unit->getid(), PDO::PARAM_INT);
                    $stmt->bindValue(4, $time, PDO::PARAM_STR);
                    $stmt->bindValue(5, $time, PDO::PARAM_STR);
                   // $stmt->execute();
                }*/

                foreach ($mergedReview as $rw) {

                    foreach ($this->units as $unit) {

                        if ($rw->getScale() <= $unit->getScale() && 
                            $rw->getid() == $unit->getid()) {                       
                        
                        $stmt->bindValue(1, $rw->getScale(), PDO::PARAM_INT);
                        $stmt->bindValue(2, $reviewId, PDO::PARAM_STR);
                        $stmt->bindValue(3, $unit->getid(), PDO::PARAM_INT);
                        $stmt->bindValue(4, $time, PDO::PARAM_STR);
                        $stmt->bindValue(5, $time, PDO::PARAM_STR);
                        $stmt->execute();
                    }
                    }
               }
            }
            $this->connection->commit();

            return $reviewId;
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

    }

    /**
     * Create property delete review id
     * @param Property $property
     * @param User $user
     * @return Review
     */
    public function deletePropertyReview(Property $property, User $user, Review $review): int
    {
        $query = <<<QUERY

        DELETE sr FROM sub_reviews AS sr
            INNER JOIN reviews AS r
                ON r.id = sr.review_id
            WHERE sr.id = ?
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $review->getID(), PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Create property delete review id
     * @param Property $property
     * @param User $user
     * @return Review
     */
    public function deletePropertyReviewByReviewID(Property $property, User $user, int $reviewId): int
    {
        $query = <<<QUERY

        DELETE sr FROM sub_reviews AS sr
            INNER JOIN reviews AS r
                ON r.id = sr.review_id
            WHERE sr.review_id = ?
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $reviewId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }


}
