<?php
declare(strict_types=1);

namespace App\Repositories\User;

use App\Repositories\Document\Document;
use App\Repositories\Document\Type;
use App\Repositories\Location\Country;
use App\Repositories\Media\Media;
use App\Repositories\Payment\Invoice\InvoicePreference;
use App\Repositories\Message\NotificationSetting;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class User extends Account
{

    //protected $table = 'users';

    /**
     *
     * @var Media
     */
    private $image;

    /**
     *
     * @var Document[]
     */
    private $documents;

    /**
     *
     * @var Account
     */
    private $parent;

    /**
     *
     * @var Account
     */
    private $creator;

    /**
     *
     * @var Account
     */
    private $updator;

    /**
     *
     * @var Country
     */
    private $country;

    /**
     *
     * @var Revenue
     */
    private $revenue;

    /**
     *
     * @var InvoicePreference
     */
    private $invoicing;

    /**
     *
     * @var NotificationSetting
     */
    private $notification_setting;

    // private $ids;

    /**
     * Returns list of users
     *
     * @return Users[]
     */

    /*public function getIds(): array
    {
        return is_array($this->ids) ? $this->ids : [];
    }*/

    /**
     * Sets list of users
     * @param int ...$ids
     */
    /*public function setIds(int ...$ids)
    {
        if (count($ids)) {
            $this->ids = $ids;
        } else {
            $this->ids;
        }
     }*/

    /**
     * @return NotificationSetting
     */
    public function getNotificationSetting(): NotificationSetting
    {
        return $this->notification_setting ?? new NotificationSetting();
    }

    /**
     * @param NotificationSetting|null $notification_setting
     */
    public function setNotificationSetting(?NotificationSetting $notification_setting)
    {
        $this->notification_setting = $notification_setting;
    }

    /**
     *
     * @return Media|NULL
     */
    public function getImage(): ?Media
    {
        return $this->image;
    }

    /**
     *
     * @param Media $image
     */
    public function setImage(?Media $image)
    {
        $this->image = $image;
    }

    /**
     * Returns user documents
     *
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return is_array($this->documents) ? $this->documents : [];
    }

    /**
     * Checks if a user has a specified document
     *
     * @param string $code
     * @return bool
     */
    public function hasDocument(string $code): bool
    {
        return null !== $this->getDocumentByCode($code);
    }

    /**
     * Gets a single user document by code
     *
     * @param string $code
     * @return Document|NULL
     */
    public function getDocumentByCode(string $code): ?Document
    {
        foreach ($this->getDocuments() as $document) {
            $type = $document->getType();

            if (!($type instanceof Type)) {
                continue;
            }

            if (0 === strcasecmp($code, $type->getCode())) {
                return $document;
            }
        }

        return null;
    }

    /**
     * Sets user documents
     *
     * @param Document ...$documents
     */
    public function setDocuments(Document ...$documents)
    {
        $this->documents = $documents;
    }

    /**
     * Returns parent for this user
     *
     * @return Account|NULL
     */
    public function getParent(): ?Account
    {
        return $this->parent;
    }

    /**
     * @param String|null $parent
     * @return String|null
     */
    public function setParent(?Account $parent)
    {
        return $this->parent = $parent;
    }

    /**
     * @return String|null
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param int|null $creator
     * @return int|null
     */
    public function setCreator(?int $creator)
    {
        return $this->creator = $creator;
    }

    /**
     * Returns updator for this user
     *
     * @return Account|NULL
     */
    public function getUpdator(): ?Account
    {
        return $this->updator;
    }

    /**
     * @param Account|null $updator
     * @return Account|null
     */
    public function setUpdator(?Account $updator)
    {
        return $this->updator = $updator;
    }

    /**
     * Returns country
     *
     * @return Country|NULL
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * Sets country
     *
     * @param Country $country
     */
    public function setCountry(?Country $country)
    {
        $this->country = $country;
    }



    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Model::toJson()
     */
    public function toJson($options = 0)
    {
        // return array_merge(parent::toJson(), get_object_vars($this));
        $content = array_merge(parent::toJson(), get_object_vars($this));

        $hide = [
            'table',
            'connection',
            'primaryKey',
            'keyType',
            'incrementing',
            'with',
            'withCount',
            'perPage',
            'exists',
            'wasRecentlyCreated',
            'attributes',
            'original',
            'changes',
            'casts',
            'dates',
            'dateFormat',
            'appends',
            'dispatchesEvents',
            'observables',
            'relations',
            'touches',
            'timestamps',
            'hidden',
            'visible',
            'fillable',
            'guarded',
            'null'
        ];

        foreach ($hide as $key) {
            unset($content[$key]);
        }

        return $content;
    }
}
