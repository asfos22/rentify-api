<?php
declare(strict_types = 1);
namespace App\Repositories;

use App\Repositories\User\Account;
use App\Repositories\Unit\Unit;
use App\Repositories\Currency\Currency;
use App\Repositories\Delivery\Address;
use App\Repositories\Media\Media;
use App\Repositories\Payment\PaymentMethod;
use App\Repositories\Payment\Status as PaymentStatus;
use App\Repositories\System\Event\SystemEvent;
use App\Repositories\Message\MessageState;
use App\Repositories\Service\Status;
use App\Repositories\Service\Service;
use App\Repositories\Gps\Gps;
use App\Repositories\Property\Amenity\Amenity;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
abstract class ModelFormatter  implements ModelFormatterInterface
{

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
            return new DateTime($datetime);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Converts an int to bool
     *
     * @param int $value
     * @return bool|NULL
     */
    protected function toBool(?int $value): ?bool
    {
        if (null === $value) {
            return null;
        }

        return boolval($value);
    }

    /**
     * Formats a given object into an account instance
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return NULL|NULL|\App\Repositories\User\Account
     */
    protected function formatAccount(?\stdClass $model, string $prefix = '')
    {
        if (empty($model)) {
            return null;
        }

        $user = new Account();

        $user->setId(isset($model->{$prefix . 'Id'}) ? (int) $model->{$prefix . 'Id'} : null);
        $user->setName(isset($model->{$prefix . 'Name'}) ? (string) $model->{$prefix . 'Name'} : null);
        $user->setPhone(isset($model->{$prefix . 'Phone'}) ? (string) $model->{$prefix . 'Phone'} : null);
        $user->setEmail(isset($model->{$prefix . 'Email'}) ? (string) $model->{$prefix . 'Email'} : null);
        $user->setBlocked(isset($model->{$prefix . 'Blocked'}) ? $this->toBool((int) $model->{$prefix . 'Blocked'}) : null);
        $user->setActivated(isset($model->{$prefix . 'Activated'}) ? $this->toBool((int) $model->{$prefix . 'Activated'}) : null);
        $user->setCreatedAt(isset($model->{$prefix . 'CreatedAt'}) ? $this->createDateTime((string) $model->{$prefix . 'CreatedAt'}) : null);
        $user->setUpdatedAt(isset($model->{$prefix . 'UpdatedAt'}) ? $this->createDateTime((string) $model->{$prefix . 'UpdatedAt'}) : null);

        return $user->isEmpty() ? null : $user;
    }

    /**
     * Formats a given object into an account instance
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return Gps
     */
    protected function formatGps(?\stdClass $model, string $prefix = '')
    {
        if (empty($model)) {
            return null;
        }

        $instance = new Gps();

        $instance->setId(isset($model->{$prefix . 'Id'}) ? (int) $model->{$prefix . 'Id'} : null);
        $instance->setLatitude(isset($model->{$prefix . 'Latitude'}) ? (string) $model->{$prefix . 'Latitude'} : null);
        $instance->setLongitude(isset($model->{$prefix . 'Longitude'}) ? (string) $model->{$prefix . 'Longitude'} : null);
        $instance->setSpeed(isset($model->{$prefix . 'Speed'}) ? (string) $model->{$prefix . 'Speed'} : null);
        $instance->setStatus(isset($model->{$prefix . 'Status'}) ? (string) $model->{$prefix . 'Status'} : null);
        $instance->setLocation(isset($model->{$prefix . 'Location'}) ? (string) $model->{$prefix . 'Location'} : null);
        $instance->setPhone(isset($model->{$prefix . 'Phone'}) ? (string) $model->{$prefix . 'Phone'} : null);
        $instance->setSerial(isset($model->{$prefix . 'Serial'}) ? (string) $model->{$prefix . 'Serial'} : null);
        $instance->setCreatedAt(isset($model->{$prefix . 'CreatedAt'}) ? $this->createDateTime((string) $model->{$prefix . 'CreatedAt'}) : null);
        $instance->setUpdatedAt(isset($model->{$prefix . 'UpdatedAt'}) ? $this->createDateTime((string) $model->{$prefix . 'UpdatedAt'}) : null);

        return $instance->isEmpty() ? null : $instance;
    }

    /**
     * Formats a given object into a unit instance
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return Unit|NULL
     */
    protected function formatUnint(?\stdClass $model, string $prefix = ''): ?Unit
    {
        if (empty($model)) {
            return null;
        }

        $uint = new Unit();
        $uint->setId(empty($model->{$prefix . 'Id'}) ? null : (int) $model->{$prefix . 'Id'});
        $uint->setName(empty($model->{$prefix . 'Name'}) ? null : (string) $model->{$prefix . 'Name'});
        $uint->setSymbol(empty($model->{$prefix . 'Symbol'}) ? null : (string) $model->{$prefix . 'Symbol'});
        $uint->setBase(isset($model->{$prefix . 'Base'}) ? (string) $model->{$prefix . 'Base'} : null);

        return $uint;
    }

    /**
     * Formats a given object into a currency instance
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return Currency|NULL
     */
    protected function formatCurrency(?\stdClass $model, string $prefix = ''): ?Currency
    {
        if (empty($model)) {
            return null;
        }

        $currency = new Currency();
        $currency->setCode(empty($model->{$prefix . 'Code'}) ? null : (string) $model->{$prefix . 'Code'});
        $currency->setName(empty($model->{$prefix . 'Name'}) ? null : (string) $model->{$prefix . 'Name'});
        $currency->setSymbol(empty($model->{$prefix . 'Symbol'}) ? null : (string) $model->{$prefix . 'Symbol'});

        return $currency->isEmpty() ? null : $currency;
    }

    /**
     * Formats a given object as payment method
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return PaymentMethod|NULL
     */
    protected function formatPaymentMethod(?\stdClass $model, string $prefix = ''): ?PaymentMethod
    {
        if (empty($model)) {
            return null;
        }

        $instance = new PaymentMethod();
        $instance->setCode(empty($model->{$prefix . 'Code'}) ? null : (string) $model->{$prefix . 'Code'});
        $instance->setName(empty($model->{$prefix . 'Name'}) ? null : (string) $model->{$prefix . 'Name'});

        return $instance->isEmpty() ? null : $instance;
    }

    /**
     * Formats a given object as payment status
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return PaymentStatus|NULL
     */
    protected function formatPaymentStatus(?\stdClass $model, string $prefix = ''): ?PaymentStatus
    {
        if (empty($model)) {
            return null;
        }

        $instance = new PaymentStatus();
        $instance->setCode(empty($model->{$prefix . 'Code'}) ? null : (string) $model->{$prefix . 'Code'});
        $instance->setName(empty($model->{$prefix . 'Name'}) ? null : (string) $model->{$prefix . 'Name'});

        return $instance->isEmpty() ? null : $instance;
    }

    /**
     * Formats a given object as service status
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return Status|NULL
     */
    protected function formatServiceStatus(?\stdClass $model, string $prefix = ''): ?Status
    {
        if (empty($model)) {
            return null;
        }

        $instance = new Status();
        $instance->setCode(empty($model->{$prefix . 'Code'}) ? null : (string) $model->{$prefix . 'Code'});
        $instance->setName(empty($model->{$prefix . 'Name'}) ? null : (string) $model->{$prefix . 'Name'});

        return $instance->isEmpty() ? null : $instance;
    }

    /**
     * Formats a given object as service
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return Service|NULL
     */
    protected function formatService(?\stdClass $model, string $prefix = ''): ?Service
    {
        if (empty($model)) {
            return null;
        }

        $instance = new Service();
        $instance->setCode(empty($model->{$prefix . 'Code'}) ? null : (string) $model->{$prefix . 'Code'});
        $instance->setName(empty($model->{$prefix . 'Name'}) ? null : (string) $model->{$prefix . 'Name'});
        $instance->setVisibility(empty($model->{$prefix . 'Visible'}) ? null : $this->toBool($model->{$prefix . 'Visible'}));

        return $instance->isEmpty() ? null : $instance;
    }

    /**
     * Formats a given object into an address instance
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return Address|NULL
     */
    protected function formatAddress(?\stdClass $model, string $prefix = ''): ?Address
    {
        if (empty($model)) {
            return null;
        }

        $address = new Address();
        $address->setId(empty($model->{$prefix . 'Id'}) ? null : (int) $model->{$prefix . 'Id'});
        $address->setName(empty($model->{$prefix . 'Name'}) ? null : (string) $model->{$prefix . 'Name'});
        $address->setContactName(empty($model->{$prefix . 'ContactName'}) ? null : (string) $model->{$prefix . 'ContactName'});
        $address->setLatitude(isset($model->{$prefix . 'Latitude'}) ? (string) $model->{$prefix . 'Latitude'} : null);
        $address->setLongitude(empty($model->{$prefix . 'Longitude'}) ? null : (string) $model->{$prefix . 'Longitude'});
        $address->setContactPhone(empty($model->{$prefix . 'ContactPhone'}) ? null : (string) $model->{$prefix . 'ContactPhone'});
        $address->setCreatedAt($this->createDateTime(empty($model->{$prefix . 'CreatedAt'}) ? null : (string) $model->{$prefix . 'CreatedAt'}));
        $address->setUpdatedAt($this->createDateTime(empty($model->{$prefix . 'UpdatedAt'}) ? null : (string) $model->{$prefix . 'UpdatedAt'}));

        return $address->isEmpty() ? null : $address;
    }

    protected function formatString($value): ?string
    {
        return is_null($value) ? null : sprintf('%s', $value);
    }

    /**
     * Formats a given object as a media
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return Media|NULL
     */
    protected function formatMedia(?\stdClass $model, string $prefix = ''): ?Media
    {
        if (empty($model)) {
            return null;
        }

        $media = new Media();
        $media->setId(empty($model->{$prefix . 'ImageId'}) ? null : (int) $model->{$prefix . 'ImageId'});
        $media->setName(empty($model->{$prefix . 'ImageName'}) ? null : (string) $model->{$prefix . 'ImageName'});
        $media->setOriginalName(empty($model->{$prefix . 'ImageOriginalName'}) ? null : (string) $model->{$prefix . 'ImageOriginalName'});
        $media->setSize(isset($model->{$prefix . 'ImageSize'}) ? (int) $model->{$prefix . 'ImageSize'} : null);
        $media->setSrc(empty($model->{$prefix . 'ImageSrc'}) ? null : (string) $model->{$prefix . 'ImageSrc'});
        $media->setPath(empty($model->{$prefix . 'ImagePath'}) ? null : (string) $model->{$prefix . 'ImagePath'});
        $media->setMimeType(empty($model->{$prefix . 'ImageMimeType'}) ? null : (string) $model->{$prefix . 'ImageMimeType'});
        $media->setCreatedAt($this->createDateTime(empty($model->{$prefix . 'ImageCreatedAt'}) ? null : (string) $model->{$prefix . 'ImageCreatedAt'}));
        $media->setUpdatedAt($this->createDateTime(empty($model->{$prefix . 'ImageUpdatedAt'}) ? null : (string) $model->{$prefix . 'ImageUpdatedAt'}));

        return $media;
    }

    /**
     * Formats a given object as a media
     *
     * @param \stdClass $model
     * @return Media|NULL
     */
    protected function formatMediaFromJson(?\stdClass $model): ?Media
    {
        if (empty($model)) {
            return null;
        }

        $media = new Media();
        $media->setId($model->id ?? null);
        $media->setName($model->name ?? null);
        $media->setOriginalName($model->original_name ?? null);
        $media->setSize($model->size ?? null);
        $media->setSrc($model->src ?? null);
        $media->setPath($model->path ?? null);
        $media->setMimeType($model->mime_type ?? null);
        $media->setCreatedAt($this->createDateTime($model->created_at ?? null));
        $media->setUpdatedAt($this->createDateTime($model->updated_at ?? null));

        return $media;
    }

    /**
     * Formats a given object as a system event
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return SystemEvent|NULL
     */
    protected function formatSystemEvent(?\stdClass $model, string $prefix = ''): ?SystemEvent
    {
        if (empty($model)) {
            return null;
        }

        $instance = new SystemEvent();
        $instance->setId(empty($model->{$prefix . 'Id'}) ? null : (int) $model->{$prefix . 'Id'});
        $instance->setCode(empty($model->{$prefix . 'Code'}) ? null : (string) $model->{$prefix . 'Code'});
        $instance->setDescription(empty($model->{$prefix . 'Description'}) ? null : (string) $model->{$prefix . 'Description'});

        return $instance->isEmpty() ? null : $instance;
    }

    /**
     * Formats a given object as a message state
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return MessageState|NULL
     */
    protected function formatMessageState(?\stdClass $model, string $prefix = ''): ?MessageState
    {
        if (empty($model)) {
            return null;
        }

        $instance = new MessageState();
        $instance->setId(empty($model->{$prefix . 'Id'}) ? null : (int) $model->{$prefix . 'Id'});
        $instance->setCode(empty($model->{$prefix . 'Code'}) ? null : (string) $model->{$prefix . 'Code'});
        $instance->setName(empty($model->{$prefix . 'Name'}) ? null : (string) $model->{$prefix . 'Name'});

        return $instance->isEmpty() ? null : $instance;
    }


    /**
     * Formats a given object as amenities service
     *
     * @param \stdClass $model
     * @param string $prefix
     * @return Service|NULL
     */
    protected function formatAmenities(?\stdClass $model, string $prefix = ''): ?Amenity
    {
        if (empty($model)) {
            return null;
        }

        $instance = new Amenity();
        $instance->setId(empty($model->{$prefix . 'Code'}) ? null : (string) $model->{$prefix . 'Code'});
        $instance->setName(empty($model->{$prefix . 'Name'}) ? null : (string) $model->{$prefix . 'Name'});
        $instance->setDecription(empty($model->{$prefix . 'Description'}) ? null : $this->toBool($model->{$prefix . 'Visible'}));

        return $instance->isEmpty() ? null : $instance;
    }
}

