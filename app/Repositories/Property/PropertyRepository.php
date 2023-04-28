<?php

namespace App\Repositories\Property;

use App\Models\Address;
use App\Models\House;
use App\Repositories\Currency\Currency;
use App\Repositories\Currency\Money;
use App\Repositories\Facility;
use App\Repositories\GeoCode\Location;
use App\Repositories\Media\Media;
use App\Repositories\Model;
use App\Repositories\OptionalFacility;
use App\Repositories\Repository;
use App\Repositories\Review\Review;
use App\Repositories\User\Host;
use App\Repositories\User\User as User;
use App\Traits\Tokens;
//use App\Traits\Uuids;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use stdClass;

class PropertyRepository extends Repository implements PropertyInterface
{
    use Tokens;

    private static $mainQuery = <<<QUERY


              WITH cteMedia (house_id, cMedia) AS (

                SELECT m.house_id AS category_id, JSON_ARRAYAGG(JSON_OBJECT(
                        'house id', m.house_id,
                        'id', m.id,
                        'name', m.name
                    )) FROM media m GROUP BY m.house_id


            ) , cteFacility (house_id,  cFacility ) AS (
                SELECT hf.house_id, JSON_ARRAYAGG(JSON_OBJECT(
                    'house id', hf.house_id,
                    'id', f.id,
                    'name', f.name
                )) AS facilities FROM facility f LEFT JOIN house_facilities hf ON  hf.facility_id = f.id GROUP BY hf.house_id
                ),
                cteFacilityRule (house_id,  cTermsRules ) AS (
                    SELECT hotr.house_id, JSON_ARRAYAGG(JSON_OBJECT(
                        'house id', hotr.house_id,
                        'id',  htr.id,
                        'name',  htr.name
                    )) AS termsRules FROM house_terms_rules htr LEFT JOIN house_optional_terms_rules hotr ON  htr.facility_id = htr.id GROUP BY hotr.house_id
                )
             SELECT
                    h.id,
                    pt.name AS type,
                    h.house_name AS title,
                    h.rent_duration AS duration,
                    h.house_status AS status,
                    h.description AS description,
                    h.area_sq_ft AS sq_ft,
                    h.capacity as capacity,
                    h.uuid AS token,
                    h.service_tag_id AS tag,
                    h.created_at AS created_at,
                    h.number_of_bath_room AS bath_room,
                    h.number_of_bed_room AS bed_room,

             JSON_OBJECT(

                   'symbol', h.currency ,
                   'amount',  CONVERT( h.house_price , DECIMAL(15,2))

                ) AS currency,
            cm.cMedia AS media,
              JSON_OBJECT(

                   'rate',   IFNULL(rev.average,0) ,
                   'total',  IFNULL(rev.total,0)

                ) AS review_rate,
               JSON_ARRAYAGG( JSON_OBJECT(

                   'address', d.address,
                   'name', d.feature_name,
                   'country', d.country,
                   'locality',d.locality,
                   'sub_locality', d.sub_locality,
                   'latitude', d.latitude,
                   'longitude', d.longitude

                )) AS location,
            hf.cFacility AS facility,
            u.name AS host,
            DATE_FORMAT(h.created_at, '%Y-%m-%dT%H:%i:%sZ') AS created_at,
            DATE_FORMAT(h.updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at
            FROM house h
             LEFT JOIN (
            SELECT star, id,house_id, AVG( star ) AS average, COUNT( id ) AS total
                 FROM reviews
                 GROUP BY house_id
                ) AS rev
                On rev.house_id = h.id
            JOIN users u ON u.id = h.user_id
            JOIN property_type pt ON pt.id = h.property_type
            LEFT JOIN cteMedia cm ON cm.house_id = h.id
            LEFT JOIN cteFacility hf ON hf.house_id = h.id
            JOIN (
                 SELECT x.house_id AS house_id,
                        x.address_line AS address,
                        x.id AS l_id,
                        x.feature_name AS feature_name,
                        x.country AS country,
                        x.longitude AS longitude,
                        x.latitude AS latitude,
                        x.locality AS locality,
                        x.sub_locality AS sub_locality
                        ,ST_Distance_Sphere(point(x.longitude, x.latitude), point(-122.084,37.4219983),6378.137) AS distance
                 FROM  geo_location x HAVING distance <= 18004) d ON d.house_id = h.id

               LEFT JOIN media m ON  m.house_id = h.id


    QUERY;

    private static $mainQueryOld = <<<QUERY


        WITH cteMedia (house_id, cMedia) AS (

                SELECT m.house_id AS category_id, JSON_ARRAYAGG(JSON_OBJECT(
                        'id', m.id,
                        'name', m.name
                    )) FROM media m GROUP BY m.house_id


            ) , cteFacility (house_id,  cFacility ) AS (
                SELECT hf.house_id, JSON_ARRAYAGG(JSON_OBJECT(
                    'id', f.id,
                    'name', f.name
                )) AS facilities FROM facility f LEFT JOIN house_facilities hf ON  hf.facility_id = f.id GROUP BY hf.house_id
            ),
             cteReview (house_id,  cReview ) AS (
                SELECT r.id, JSON_ARRAYAGG(JSON_OBJECT(
                    'id', r.id,
                    'name', r.name,
                    'description', r.review

                )) FROM reviews r GROUP BY r.house_id
            ),
            cteLocation (house_id,  cLocation ) AS (
                SELECT loc.id, JSON_ARRAYAGG(JSON_OBJECT(

                   'id', loc.id ,
                   'name', loc.feature_name,
                   'country', loc.country,
                   'address', loc.address_line,
                   'locality',loc.locality,
                   'sub_locality', loc.sub_locality ,
                   'latitude', loc.latitude,
                   'longitude', loc.longitude

                )) FROM geo_location loc GROUP BY loc.house_id
            )
            SELECT
            h.id,
            h.user_id AS h_user_id,
            pt.name AS type,
            h.house_name AS title,
            h.rent_duration AS duration,
            h.house_status AS status,
            h.description AS description,
            h.area_sq_ft AS sq_ft,
            h.capacity AS capacity,
            h.uuid AS token,
            h.service_tag_id AS tag,
            h.created_at AS created_at,
            h.number_of_bath_room AS bath_room,
            h.number_of_bed_room AS bed_room,

             JSON_OBJECT(

                   'symbol', h.currency ,
                   'amount',  CONVERT( h.house_price , DECIMAL(15, 2))

                ) AS currency,
            cm.cMedia AS media,
              JSON_OBJECT(

                   'rate',   IFNULL(rev.average, 0) ,
                   'total',  IFNULL(rev.total, 0)

                ) AS review_rate,
            cr.cReview AS review,
            cl.cLocation AS location,
            hf.cFacility AS facility,
            u.name AS host,
            DATE_FORMAT(h.created_at, '%Y-%m-%dT%H:%i:%sZ') AS created_at,
            DATE_FORMAT(h.updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at
            FROM house h
             LEFT JOIN (
            SELECT star, id,house_id, AVG( star ) AS average, COUNT( id ) AS total
                 FROM reviews
                 GROUP BY house_id
                ) AS rev
                On rev.house_id = h.id
            JOIN users u ON u.id = h.user_id
            JOIN property_type pt ON pt.id = h.property_type
            LEFT JOIN cteMedia cm ON cm.house_id = h.id
            LEFT JOIN cteReview cr ON cr.house_id = h.id
            LEFT JOIN cteLocation cl ON cl.house_id = h.id
            LEFT JOIN cteFacility hf ON hf.house_id = h.id

    QUERY;

    /**
     * token  Sub query string
     */

    private static $tokenSubQuery = <<<QUERY

    FROM users u
    JOIN house p ON u.id  = p.user_id

    QUERY;

    /**
     * @var
     */

    private $query;

    protected $connection;

    public function __construct(

        Query $query
    ) {
        $this->query = $query;
        $this->connection = DB::connection()->getPdo();

    }

    /**
     * Get all properties
     * @return mixed
     */
    public function fetch(int $limit = 50, int $offset = 0): array
    {

        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $mainQuery = $this->query->propertyMainQuery();

        $query = <<<QUERY

         $mainQuery

         GROUP BY  h.id  ORDER BY h.id DESC LIMIT $limit OFFSET $offset;

        QUERY;

        $stmt = $this->connection->prepare($query);

        // $stmt->bindValue(1, $email, PDO::PARAM_STR)
        //$stmt->bindValue(2, $email, PDO::PARAM_STR);

        if ($stmt->execute()) {

            return array_map(function ($p) {
                return $this->format($p);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }

        return [];

    }

    /**
     * Get all properties by location
     * @param Location $location
     * @return array
     */
    public function findPropertyByGeoLocation(Location $location, int $limit = 100, int $offset = 0): array
    {
        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $mainQuery = $this->query->geoSubQuery($location); //self::$mainQuery;

        $query = <<<QUERY

         $mainQuery
         GROUP BY  h.id  ORDER BY h.id DESC LIMIT $limit OFFSET $offset ;

        QUERY;

        $stmt = $this->connection->prepare($query);

        if ($stmt->execute()) {

            return array_map(function ($p) {

                return $this->format($p);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }

        return [];

    }

    /**
     * Fetch s single property  by token
     * @param String $token
     * @return Property
     */
    public function findPropertyByToken(String $token): ?Property//array
    {

        $property = new Property();
        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $mainQuery = $this->query->propertyMainQuery(); //self::$mainQuery;

        $query = <<<QUERY
        $mainQuery

         WHERE h.uuid = ? ORDER BY h.id DESC LIMIT 1 OFFSET 0

        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $token, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false !== $data) {

                return $this->format($data);
            }
            /*return array_map(function ($p) {
        return $this->format($p);
        }, $stmt->fetchAll(PDO::FETCH_OBJ));
         */

        }
        return $property;

        /* $tokenSubQuery = self::$tokenSubQuery;

    $query = <<<QUERY
    SELECT u.id AS user_id , p.id AS property_id $tokenSubQuery WHERE u.blocked =0 AND p.uuid = ? LIMIT 1
    QUERY;

    $user = new \App\Repositories\User\User();
    $property = new Property();
    $stmt = $this->connection->prepare($query);

    $stmt->bindValue(1, $token, PDO::PARAM_STR);

    if ($stmt->execute()) {
    $data = $stmt->fetch(PDO::FETCH_OBJ);

    dump(data)

    if (false !== $data) {

    $user->setId($data->user_id);
    $property->setId($data->property_id);
    $property->setUser($user);

    return $property;
    }

    return $property;

    }*/
    }

    public function findById(int $id): ?Property
    {

        $property = new Property();
        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $mainQuery = $this->query->propertyMainQuery(); //self::$mainQuery;

        $query = <<<QUERY
         $mainQuery

         WHERE h.id = ? ORDER BY h.id DESC LIMIT 1 OFFSET 0

        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $id, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false !== $data) {

                return $this->format($data);
            }
            /*return array_map(function ($p) {
        return $this->format($p);
        }, $stmt->fetchAll(PDO::FETCH_OBJ));
         */

        }
        return $property;
    }

    /**
     *  Find user property by id [House]
     * @param Request $request
     * @param int|null $userID
     * @return PropertyHouse
     */

    public function findUserByPropertyID(Property $property): ?User
    {

        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $mainQuery = $this->query->propertyMainQuery(); //self::$mainQuery;

        $query = <<<QUERY
         $mainQuery

         WHERE h.id = ? ORDER BY h.id DESC LIMIT 1 OFFSET 0

        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $property->getID(), PDO::PARAM_STR);

        $items = [];

        if ($stmt->execute()) {
            $items = array_map(function ($model) {

                $user = new User();
                if (isset($model->user)) {

                $decodeItem = json_decode($model->user);

                $user->setId($decodeItem->id ?? null);
                $user->setName($decodeItem->name ?? null);
                $user->setPhone($decodeItem->phone ?? null);
                $user->setEmail($decodeItem->email ?? null);
                }

                return $user;

            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return array_shift($items);
    }

    /**
     * Create user new property [House]
     * @param Request $request
     * @param int|null $userID
     * @return Property
     */

    public function createNewProperty(Property $property, int $userID = null): int
    {

        $time = (new DateTime())->format('Y-m-d H:i:s');

        try {

            $query = <<<QUERY

            INSERT INTO house (
                house_name,currency,house_price,house_status,area_sq_ft,age,
                number_of_bath_room, number_of_bed_room,property_type,uuid,
                description,user_id,created_at)

            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE updated_at = ?
            QUERY;

            $stmt = $this->connection->prepare($query);

            $property->setToken($this->token(64));

            $stmt->bindValue(1, $property->getName(), PDO::PARAM_STR);
            $stmt->bindValue(2, $property->getMoney()->getCurrency()->getSymbol(), PDO::PARAM_STR);
            $stmt->bindValue(3, $property->getMoney()->getAmount(), PDO::PARAM_STR);
            $stmt->bindValue(4, $property->getStatus(), PDO::PARAM_STR);
            $stmt->bindValue(5, $property->getSqtFt(), PDO::PARAM_STR);
            $stmt->bindValue(6, $property->getAge(), PDO::PARAM_STR);
            $stmt->bindValue(7, $property->getBathRoom(), PDO::PARAM_INT);
            $stmt->bindValue(8, $property->getBedRoom(), PDO::PARAM_INT);
            $stmt->bindValue(9, $property->getType(), PDO::PARAM_STR);
            $stmt->bindValue(10, $property->getToken(), PDO::PARAM_STR);
            $stmt->bindValue(11, $property->getDescription(), PDO::PARAM_STR);
            $stmt->bindValue(12, $property->getUser()->getId(), PDO::PARAM_STR);
            $stmt->bindValue(13, $time, PDO::PARAM_STR);
            $stmt->bindValue(14, $time, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $id = (int) $this->connection->lastInsertId();
            }

            return $id;
            //   }
            // }

        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return 0;
    }

    /**
     * @param Request $request
     * @param int $propertyId
     * @param int $userId
     * @return Review
     */
    public function createReview(Request $request, int $propertyId, int $userId): Review
    {
        $review = new Review();

        $request->validate([
            "name" => "required",
            "score" => "required",
        ]);

        return $review;
    }

    /**
     * @param Request $request
     * @param int $propertyId
     * @param int $userId
     * @return User
     */
    public function createBlackList(Request $request, int $propertyId, int $userId): User
    {
        $review = new User();

        $request->validate([
            "name" => "required",
            "score" => "required",
        ]);

        return $review;
    }

    /**
     * @param int categoryId
     * @return Property
     */

    public function findPropertyByCategoryId(int $categoryId): ?Property
    {

        $property = new Property();
        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $mainQuery = $this->query->propertyMainQuery();

        $query = <<<QUERY
           $mainQuery

           WHERE pt.id = ? ORDER BY h.id DESC LIMIT 1 OFFSET 0

          QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $categoryId, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false !== $data) {

                return $this->format($data);
            }

        }
        return $property;

    }

    /**
     * property format
     * {@inheritdoc}
     */
    public function format(?stdClass $model): ?Model
    {
        //dump($model->id, $model->reviews);
        if (empty($model)) {
            return null;
        }

        $property = new Property();

        $property->setID($model->id);
        $property->setName($model->title);
        $property->setType($model->type);
        $property->setDuration($model->duration);
        $property->setSqtFt($model->sq_ft);
        $property->setCapacity($model->capacity);
        $property->setStatus($model->status);
        $property->setBathRoom($model->bath_room);
        $property->setBedRoom($model->bed_room);
        $property->setLink(empty($model->token) ? null : route('property/house/list/', ['token' => $model->token]));
        $property->setToken($model->token);
        $property->setCreatedAt(empty($model->created_at) ? null : $this->createDateTime(empty($model->created_at) ? date('Y-m-d H:i:s') : (string) $model->created_at));
        $property->setHumanCreatedAt(empty($model->created_at) ? null : $this->createDateTime(empty(date('m/d/Y', strtotime($model->created_at))) ? null : date('m/d/Y', strtotime($model->created_at))));
        $property->setDescription($model->description);

        $host = new Host();
        $host->setUserToken($model->user_token);
        $host->setName(empty($model->host) ? null : $model->host);
        $host->setWelcomeMessage(empty($model->host) ? null : 'Welcome to ' . $model->host . '\'s ' . $model->title);
        $property->setHost($host);

        $money = new Money();
        $currency = new Currency();
        $optionalFacility = new OptionalFacility();

        if (isset($model->currency)) {
            $decodeMoney = json_decode($model->currency);
            $currency->setSymbol($decodeMoney->symbol);
            $money->setCurrency($currency);
            $money->setAmount(empty($decodeMoney->amount) ? null : $decodeMoney->amount);

            $property->setMoney($money);
        }

        if (isset($model->optional_facility)) {
            $decodeOptionalFacility = json_decode($model->optional_facility);

            $optionalFacility->setOptionalName(isset($decodeOptionalFacility->optional_rule) ? $decodeOptionalFacility->optional_rule : null);
            $optionalFacility->setOptionalRule(isset($decodeOptionalFacility->optinal_facility) ? $decodeOptionalFacility->optinal_facility : null);
            $property->setOptionalFacility($optionalFacility);

        }

        if (isset($model->location)) {

            $decodeItem = json_decode($model->location);

            if (is_array($decodeItem) && count($decodeItem)) {
                $itemArray = array_map(function ($locationModel) {
                    $mLocation = new Location();

                    // $mLocation->setId(isset($locationModel->id) ? (int)$locationModel->id : null);
                    $mLocation->setName(isset($locationModel->name) ? $locationModel->name : null);
                    $mLocation->setAddress(isset($locationModel->address) ? $locationModel->address : null);
                    $mLocation->setLocality(isset($locationModel->locality) ? $locationModel->locality : null);
                    $mLocation->setSubLocality(isset($locationModel->sub_locality) ? $locationModel->sub_locality : null);
                    $mLocation->setLatitude(isset($locationModel->latitude) ? $locationModel->latitude : null);
                    $mLocation->setLongitude(isset($locationModel->longitude) ? $locationModel->longitude : null);
                    $mLocation->setCountry(isset($locationModel->country) ? $locationModel->country : null);

                    return $mLocation;
                }, $decodeItem);

                $property->setLocation(...$itemArray);
            }

            /*$decodeItem = json_decode($model->location);

        $mLocation = new Location();

        // $mLocation->setId(isset($locationModel->id) ? (int)$locationModel->id : null);
        $mLocation->setName(isset($decodeItem->name) ? $decodeItem->name : null);
        $mLocation->setAddress(isset($decodeItem->address) ?  $decodeItem->address : null);
        $mLocation->setLocality(isset($decodeItem->locality) ?  $decodeItem->locality : null);
        $mLocation->setSubLocality(isset( $decodeItem->sub_locality) ?  $decodeItem->sub_locality : null);
        $mLocation->setLatitude(isset( $decodeItem->latitude) ?  $decodeItem->latitude : null);
        $mLocation->setLongitude(isset( $decodeItem->longitude) ?  $decodeItem->longitude : null);
        $mLocation->setCountry(isset( $decodeItem->country) ?  $decodeItem->country : null);
        $property->setLocation($mLocation);*/
        }

        $mReview = new Review();

        if (isset($model->review_rate)) {

            $decodeReviewRate = json_decode($model->review_rate);
            $mReview->setRateScore(empty($decodeReviewRate->rate) ? null : $decodeReviewRate->rate);
            $mReview->setRateCount(empty($decodeReviewRate->total) ? null : $decodeReviewRate->total);
            $property->setRateScore($mReview);
        }

        /**if (isset($model->reviews)) {
            $decodeItem = json_decode($model->reviews);

            if (is_array($decodeItem) && count($decodeItem)) {
                $itemArray = array_map(function ($reviewModel) {
                    $mReview = new Review();

                    $mReview->setId(isset($reviewModel->id) ? (int) $reviewModel->id : null);
                    $mReview->setName(isset($reviewModel->name) ? $reviewModel->name : null);
                    $mReview->setRateScore(isset($reviewModel->rate) ? $reviewModel->rate : null);
                    $mReview->setDescription(isset($reviewModel->review) ? $reviewModel->review : null);
                    $mReview->setCreatedAt(isset($reviewModel->created_at) ? $this->createDateTime($reviewModel->created_at) : null);

                    return $mReview;
                }, $decodeItem);

                $property->setReview(...$itemArray);
            }
        }*/

        if (isset($model->reviews)) {
            $decodeItem = json_decode($model->reviews);
    
            if (is_array($decodeItem) && count($decodeItem)) {
                $itemArray = array_map(function ($reviewModel) {
                    
                    //dump("REVIEW ",$reviewModel);

                    $mReview = new Review();

                    $mReview->setId(isset($reviewModel->id) ? (int) $reviewModel->id : null);
                    $mReview->setName(isset($reviewModel->name) ? $reviewModel->name : null);
                    $mReview->setComment(isset($reviewModel->review) ? $reviewModel->review: null);
                    $mReview->setScale(isset($reviewModel->scale) ? (int)$reviewModel->scale : 0);
                    $mReview->setCreatedAt(isset($reviewModel->created) ? $this->createDateTime($reviewModel->created) : null);
                    $mReview->setCreatedAt(isset($reviewModel->updated_at) ? $this->createDateTime($reviewModel->updated_at) : null);
    
                  
                    return $mReview;
                }, $decodeItem);
    
                $property->setReview(...$itemArray);
               // $review->setReview(...$itemArray);
            }
    
        
        }

        if (isset($model->media)) {
            $decodeMedia = json_decode($model->media);

            if (is_array($decodeMedia) && count($decodeMedia)) {
                $mediaArray = array_map(function ($mediaModel) {
                    $mMedia = new Media();

                    $mMedia->setSrc(isset($mediaModel->name) ? asset('/upload/user/properties/houses/media/images/' . $mediaModel->name) : null/*asset('../../../images/static/property/listing-item-01.jpg')*/); // asset('/upload/user/properties/houses/media/images/' . $this->name),. (string)$mediaModel->name : null);

                    return $mMedia;
                }, $decodeMedia);

                $property->setMedia(...$mediaArray);

            }
        }

        if (isset($model->facility)) {
            $decodeItem = json_decode($model->facility);

            if (is_array($decodeItem) && count($decodeItem)) {
                $itemArray = array_map(function ($facilityModel) {
                    $mFacility = new Facility();
                    //$mFacility->setId(isset($facilityModel->id) ? (int)$facilityModel->id : null);
                    $mFacility->setName(isset($facilityModel->name) ? (string) $facilityModel->name : null);

                    return $mFacility;
                }, $decodeItem);

                $property->setFacility(...$itemArray);

            }
        }

        if (isset($model->term_rule)) {
            $decodeItem = json_decode($model->term_rule);

            if (is_array($decodeItem) && count($decodeItem)) {
                $itemArray = array_map(function ($facilityRuleModel) {
                    $mFacilityRule = new Facility();

                    $mFacilityRule->setId(isset($facilityRuleModel->id) ? (int) $facilityRuleModel->id : null);
                    $mFacilityRule->setName(isset($facilityRuleModel->name) ? (string) $facilityRuleModel->name : null);
                    $mFacilityRule->setDescription(isset($facilityRuleModel->desc) ? (string) $facilityRuleModel->desc : null);

                    return $mFacilityRule;
                }, $decodeItem);

                $property->setFacilityRule(...$itemArray);

            }
        }

        return $property;

    }

}
