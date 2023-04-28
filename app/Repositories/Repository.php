<?php
//declare(strict_types = 1);
namespace App\Repositories;


use PDO;

/**
 * Base repository class
 *
 * @author asante foster <asantefoster22@gmail.com>
 */
abstract class Repository
{

    /**
     * Insert datetime format
     */
    protected const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Default date time
     */
    protected  const  DEFAULT_DATETIME_FORMAT = 'm/d/Y';



    /**
     * Create datetime from a string
     *
     * @param string $datetime
     * @return DateTime|NULL
     */
    protected function createDateTime(?string $datetime): ?DateTime
    {
        if (empty($datetime)) {
            return null;
        }

        try {
            return new  DateTime($datetime);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Returns current time as string
     * @param string $format
     * @return string
     * @throws \Exception
     */
    protected function getCurrentTimeAsString(string $format = 'Y-m-d H:i:s'): string
    {
        return (new Datetime())->format($format);
    }

    /**
     * Returns binding type for a given property
     *
     * @param mixed $param
     * @return int|NULL
     */
    protected function paramType($param): ?int
    {
        return PDO::PARAM_STR;
    }


}
