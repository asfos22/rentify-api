<?php
declare(strict_types=1);

namespace App\Repositories\Role;


use App\Repositories\Auth\Auth;
use Illuminate\Support\Facades\DB;

use PDO;


/**
 *
 * @author Asante Foster <asantefoser22@gmail.com>
 *
 */
class RoleRepository implements RoleRepositoryInterface
{

    /**
     * @var
     */
    protected $connection;


    public function __construct()
    {

        $this->connection = DB::connection()->getPdo();

    }

    private $roleQuery = <<<QUERY
     id, name FROM roles
    QUERY;


    /**
     * @param String $name
     * @return Auth|null
     */
    public function findRoleByIDName(String $name): ?Role
    {

        $query = 'SELECT ' . $this->roleQuery . ' WHERE name  = ? LIMIT 1';

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $name, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            $role = new Role();

            if (false != $data) {
                //return $this->format($data);

                $role->setId($data->id);
                $role->setName($data->name);

                return $role;

            }

            // SET HOST AS DEFAULT
            if (false == $data) {

                $role->setName('Host');
                $role->setId(2);

                return $role;
            }

        }

        return null;
    }


}

