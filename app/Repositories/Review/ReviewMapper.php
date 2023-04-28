<?php
declare(strict_types = 1);
namespace App\Repositories\Review;

use App\Models\User as ModelsUser;
use App\Repositories\IDModel;
use App\Repositories\ModelFormatter;
use App\Repositories\User\Account;
use App\Repositories\User\User;
use stdClass;

use function PHPUnit\Framework\isEmpty;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
class ReviewMapper extends ModelFormatter
{

   /* private const HANDLING_QUERY = <<<QUERY
    (SELECT JSON_ARRAYAGG(JSON_OBJECT(
    	'id', h.id,
        'description', h.description,
        'icon', h.icon,
        'rate', CAST(h.rate AS CHAR),
    	'created_at', DATE_FORMAT(z.created_at, '%Y-%m-%dT%H:%i:%sZ'),
    	'updated_at', DATE_FORMAT(z.updated_at, '%Y-%m-%dT%H:%i:%sZ')
    )) AS handling FROM delivery_packages_handlings z JOIN package_handlings h ON z.package_handling_id = h.id WHERE z.package_id = p.id GROUP BY z.package_id)
    QUERY;

    private const MEDIA_QUERY = <<<QUERY
    (SELECT JSON_ARRAYAGG(JSON_OBJECT(
    	'id', m.id,
        'name', m.name,
        'original_name', m.original_name,
        'size', m.size,
        'mime_type', m.mime_type,
        'src', m.src,
        'path', m.path,
    	'created_at', DATE_FORMAT(n.created_at, '%Y-%m-%dT%H:%i:%sZ'),
    	'updated_at', DATE_FORMAT(n.updated_at, '%Y-%m-%dT%H:%i:%sZ')
    )) AS images FROM delivery_packages_media n JOIN media m ON n.media_id = m.id WHERE n.package_id = p.id GROUP BY n.package_id)
    QUERY;
    }*/

    /**
     *
     * {@inheritdoc}
     * @see App\Repositories\ModelFormatterInterface::format()
     */
    public function format(?stdClass $model)
    {

        if (empty($model)) {
            return null;
        }
        
        $review = new Review();
        $review->setId(isset($model->id) ? (int) $model->id : null);
        $review->setName(isset($model->name) ? $model->name: null);
        $review->setComment(isset($model->comment) ? $model->comment: null);
        $review->setScale(isset($model->scale)? (int)$model->scale : 0); //boolval($model->scale) : null);
        $review->setCreatedAt(! empty($model->CreatedAt) ? $this->createDateTime((string) $model->CreatedAt) : null);
        $review->setUpdatedAt(! empty($model->UpdatedAt) ? $this->createDateTime((string) $model->UpdatedAt) : null);

        $reviewClone[] = $review;

       //----
        $reviewing = array_map(function ($rw) {
        
            return $rw;
        },  $reviewClone, []);

        if (count($reviewing)) {
           // $review->setReview(...$reviewing);
        }

      //----
     
      if (isset($model->user)) {
        $decodeAccount =json_decode($model->user);
        $account = new Account();
        $account->setName($decodeAccount->name??null);
        $review->setUser($account);
      }
      //----
     
      if (isset($model->reviews)) {
        $decodeItem = json_decode($model->reviews);

        if (is_array($decodeItem) && count($decodeItem)) {
            $itemArray = array_map(function ($reviewModel) {
                $mReview = new Review();

                $mReview->setId(isset($reviewModel->id) ? (int) $reviewModel->id : null);
                $mReview->setName(isset($reviewModel->name) ? $reviewModel->name : null);
                $mReview->setScale(isset($reviewModel->scale) ? (int)$reviewModel->scale : 0);
                $mReview->setDescription(isset($reviewModel->review) ? $reviewModel->review : null);
                $mReview->setCreatedAt(isset($reviewModel->created_at) ? $this->createDateTime($reviewModel->created_at) : null);
                $mReview->setCreatedAt(isset($reviewModel->updated_at) ? $this->createDateTime($reviewModel->updated_at) : null);

                return $mReview;
            }, $decodeItem);

            $review->setReview(...$itemArray);
        }

    
    }
        return $review;
    }

}

