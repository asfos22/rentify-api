<?php

namespace App\Repositories\Property;

use App\Repositories\GeoCode\Location;

class Query
{

    /**JSON_ARRAYAGG(
    JSON_OBJECT(

    'address', d.address,
    'name', d.feature_name,
    'country', d.country,
    'locality',d.locality,
    'sub_locality', d.sub_locality,
    'latitude', d.latitude,
    'longitude', d.longitude

    )) AS location,  */

    /*JSON_OBJECT(

    'address', d.address,
    'name', d.feature_name,
    'country', d.country,
    'locality',d.locality,
    'sub_locality', d.sub_locality,
    'latitude', d.latitude,
    'longitude', d.longitude

    ) AS location, */
    public static $mainQuery = <<<QUERY


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
                            'name',  htr.name,
                            'desc', htr.desc
                        )) AS termsRules FROM house_terms_rules htr LEFT JOIN house_optional_terms_rules hotr ON  hotr.house_terms_rules_id = htr.id GROUP BY hotr.house_id
                        ),
                    cteReview  (house_id, cReview) AS (

                        SELECT r.house_id AS review_id, JSON_ARRAYAGG(JSON_OBJECT(
                                'name', r.id,
                                'rate', r.house_id,
                                'review', r.review,
                                'created_at', r.created_at,
                                'test', (SELECT JSON_ARRAYAGG(
                                    JSON_OBJECT(
                                            'srid', sr.review_id,
                                            'name', pr.name,
                                            'rate',sr.star,
                                            'created_at', sr.created_at
                                        ))
                              FROM sub_reviews sr
                              JOIN reviews r ON sr.review_id = r.id
                              JOIN property_review pr ON pr.id = sr.property_review_id
                              JOIN users u ON u.id = r.user_id
                              ORDER BY sr.id DESC
                                )

                            )) FROM reviews r

                            JOIN users u ON r.user_id = u.id
                            JOIN sub_reviews srv ON srv.review_id = r.id
                            WHERE r.house_id = house_id

                            GROUP BY r.house_id ORDER BY r.id DESC  LIMIT 3
                    )
                SELECT
                h.id,
                h.user_id AS user_token,
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
                       'amount',  CONVERT( h.house_price , DECIMAL(15,2))

                    ) AS currency,
                cm.cMedia AS media,
                  JSON_OBJECT(

                       'rate',   IFNULL(rev.average,0) ,
                       'total',  IFNULL(rev.total,0)

                    ) AS review_rate,

                    JSON_ARRAYAGG(
                        JSON_OBJECT(
                        'address', d.address,
                        'name', d.feature_name,
                        'country', d.country,
                        'locality',d.locality,
                        'sub_locality', d.sub_locality,
                        'latitude', d.latitude,
                        'longitude', d.longitude

                     )) AS location,
                hf.cFacility AS facility,
                fr.cTermsRules  AS term_rule,
                JSON_OBJECT(

                    'optinal_facility', hoftr.name,
                    'optional_rule', hoftr.term_rule

                 ) AS optional_facility,
                u.name AS host,
                JSON_OBJECT(

                    'id', u.id,
                    'name', u.name,
                    'email', u.email,
                    'phone', u.phone_number

                ) AS user,
                DATE_FORMAT(h.created_at, '%Y-%m-%dT%H:%i:%sZ') AS created_at,
                DATE_FORMAT(h.updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at
                FROM house h
                LEFT JOIN (
                SELECT sr.star, r.id,house_id, AVG( sr.star ) AS average, COUNT( r.id ) AS total
                     FROM reviews r
                     JOIN sub_reviews sr ON sr.review_id = r.id
                     JOIN property_review pr ON pr.id = sr.property_review_id
                     GROUP BY house_id
                    ) AS rev
                    On rev.house_id = h.id
                JOIN users u ON u.id = h.user_id
                JOIN property_type pt ON pt.id = h.property_type
                LEFT JOIN cteMedia cm ON cm.house_id = h.id
                LEFT JOIN cteFacility hf ON hf.house_id = h.id
                LEFT JOIN cteFacilityRule fr ON fr.house_id = h.id
                LEFT JOIN house_optional_facility_terms_rules hoftr ON hoftr.house_id = h.id
                LEFT JOIN media m ON  m.house_id = h.id


    QUERY;

    public function propertyMainQuery(): string
    {

        $mainQuery = self::$mainQuery;

        $query = <<<QUERY
         $mainQuery
                LEFT JOIN (
                     SELECT x.house_id AS house_id,
                            x.address_line AS address,
                            x.id AS l_id,
                            x.feature_name AS feature_name,
                             x.country AS country,
                            x.longitude AS longitude,
                            x.latitude AS latitude,
                             x.locality AS locality,
                            x.sub_locality AS sub_locality
                     FROM  geo_location x) d ON d.house_id = h.id
               -- GROUP BY  h.id  ORDER BY h.id DESC LIMIT 1000 OFFSET 0 ; -- OFFSET 100, LIMIT 10,10;. DESC WHERE location.house_id IS NOT NULL OR rate.house_id IS NOT NULL  GROUP BY h.id;

        QUERY;

        return $query;

    }

    /**
     *  Generates a distance subquery
     * @param Location $location
     * @return string
     */
    public function geoSubQuery(Location $location): string
    {
        $mainQuery = self::$mainQuery;

        $latitude = $location->getLatitude();
        $longitude = $location->getLongitude();
        $maxDistance = $location->getMaxDistance();

        //dump($location);

        $query = <<<QUERY
         $mainQuery

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
                        ,ST_Distance_Sphere(point(x.longitude, x.latitude), point($longitude, $latitude),6378.137) AS distance
                 FROM  geo_location x HAVING distance <=  $maxDistance) d ON d.house_id = h.id

        QUERY;

        return $query;

        /**
         *  GROUP BY  h.id  ORDER BY h.id DESC LIMIT 1000 OFFSET 0 ; -- OFFSET 100, LIMIT 10,10;. DESC WHERE location.house_id IS NOT NULL OR rate.house_id IS NOT NULL  GROUP BY h.id;

         */

        /*$rawQuery = <<<QUERY
    SELECT x.id AS gps_id,
    ST_Distance_Sphere(point(x.longitude, x.latitude), point(%s, %s), %s) AS distance
    FROM gps x HAVING distance <= %s
    QUERY;

    return sprintf($rawQuery, $address->getLongitude(), $address->getLatitude(), DistanceEstimator::EARTH_RADIUS, $maxDistance);*/
    }

    public static $mainQueryDev = <<<QUERY


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
                       'name',  htr.name,
                       'desc', htr.desc
                   )) AS termsRules FROM house_terms_rules htr LEFT JOIN house_optional_terms_rules hotr ON  hotr.house_terms_rules_id = htr.id GROUP BY hotr.house_id
                   ),
               cteReview  (house_id, cReview) AS (

                   SELECT r.house_id AS review_id, JSON_ARRAYAGG(JSON_OBJECT(
                           'name', r.id,
                           'rate', r.house_id,
                           'review', r.review,
                           'created_at', r.created_at,
                           'test', (SELECT JSON_ARRAYAGG(
                               JSON_OBJECT(
                                       'srid', sr.review_id,
                                       'name', pr.name,
                                       'rate',sr.star,
                                       'created_at', sr.created_at
                                   ))
                         FROM sub_reviews sr
                         JOIN reviews r ON sr.review_id = r.id
                         JOIN property_review pr ON pr.id = sr.property_review_id
                         JOIN users u ON u.id = r.user_id
                         ORDER BY sr.id DESC
                           )

                       )) FROM reviews r

                       JOIN users u ON r.user_id = u.id
                       JOIN sub_reviews srv ON srv.review_id = r.id
                       WHERE r.house_id = house_id

                       GROUP BY r.house_id ORDER BY r.id DESC  LIMIT 3
               )
           SELECT
           h.id,
           h.user_id AS user_token,
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
                  'amount',  CONVERT( h.house_price , DECIMAL(15,2))

               ) AS currency,
           cm.cMedia AS media,
           cv.cReview AS reviews,
             JSON_OBJECT(

                  'rate',   IFNULL(rev.average,0) ,
                  'total',  IFNULL(rev.total,0)

               ) AS review_rate,

               JSON_ARRAYAGG(
                   JSON_OBJECT(
                   'address', d.address,
                   'name', d.feature_name,
                   'country', d.country,
                   'locality',d.locality,
                   'sub_locality', d.sub_locality,
                   'latitude', d.latitude,
                   'longitude', d.longitude

                )) AS location,
           hf.cFacility AS facility,
           fr.cTermsRules  AS term_rule,
           JSON_OBJECT(

               'optinal_facility', hoftr.name,
               'optional_rule', hoftr.term_rule

            ) AS optional_facility,
           u.name AS host,
            JSON_OBJECT(

                'id', u.id,
                'name', u.name,
                'email', u.email,
                'phone', u.phone_number

            ) AS user,
           DATE_FORMAT(h.created_at, '%Y-%m-%dT%H:%i:%sZ') AS created_at,
           DATE_FORMAT(h.updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at
           FROM house h
           LEFT JOIN (
           SELECT sr.star, r.id,house_id, AVG( sr.star ) AS average, COUNT( r.id ) AS total
                FROM reviews r
                JOIN sub_reviews sr ON sr.review_id = r.id
                JOIN property_review pr ON pr.id = sr.property_review_id
                GROUP BY house_id
               ) AS rev
               On rev.house_id = h.id
           JOIN users u ON u.id = h.user_id
           JOIN property_type pt ON pt.id = h.property_type
           LEFT JOIN cteMedia cm ON cm.house_id = h.id
           LEFT JOIN cteFacility hf ON hf.house_id = h.id
           LEFT JOIN cteFacilityRule fr ON fr.house_id = h.id
           LEFT JOIN cteReview cv ON cv.house_id = h.id
           LEFT JOIN house_optional_facility_terms_rules hoftr ON hoftr.house_id = h.id
           LEFT JOIN media m ON  m.house_id = h.id


QUERY;

}
