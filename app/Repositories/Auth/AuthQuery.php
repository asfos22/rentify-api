<?php
declare(strict_types=1);

namespace App\Repositories\Auth;

class AuthQuery{
   
    public static $query  = <<<QUERY
    WITH ctePermissions (role_id, permissions) AS (
    	SELECT rp.role_id, JSON_ARRAYAGG(JSON_OBJECT(
    		'id', p.id,
            'code', p.slug,
            'description', p.description,
            'created_at', DATE_FORMAT(p.created_at, '%Y-%m-%dT%H:%i:%sZ'),
            'updated_at', DATE_FORMAT(p.updated_at, '%Y-%m-%dT%H:%i:%sZ')
        )) AS permissions FROM roles_permissions rp JOIN permissions p ON rp.permission_id = p.id GROUP BY rp.role_id
    )
    SELECT
    a.id,
    a.user_id ,
    a.password,
    a.reset_code,
    a.confirmation_code,
    u.name AS name,
    u.email AS email,
    u.phone_number AS phone,
    u.country_code AS country,
    u.activated AS activated,
    u.blocked AS blocked,
    JSON_OBJECT(
         'name',u.name,
         'email',u.email,
         'phone',u.phone_number,
         'role',u.role_id,
         'country', u.country_code,
         'activated',  u.activated,
         'blocked',u.blocked,
         'id', u.id
    ) as user,
    JSON_OBJECT(
        'name',r.name,
        'id', r.id
    ) as role,
    DATE_FORMAT(a.created_at, '%Y-%m-%dT%H:%i:%sZ') AS created_at,
    DATE_FORMAT(a.updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at,
    p.permissions,
    JSON_OBJECT(
    	'id', t.id,
        'secret', t.token,
        'client', t.client,
        'ip', t.ip,
        'expires_at', DATE_FORMAT(t.expires_at, '%Y-%m-%dT%H:%i:%sZ'),
        'created_at', DATE_FORMAT(t.created_at, '%Y-%m-%dT%H:%i:%sZ'),
        'updated_at', DATE_FORMAT(t.updated_at, '%Y-%m-%dT%H:%i:%sZ')
    ) as authToken
    FROM auth a
    JOIN users u ON u.id = a.user_id
    JOIN roles r ON r.id = u.role_id
    JOIN ctePermissions p ON p.role_id = u.role_id
    LEFT JOIN auth_tokens t ON t.auth_id = a.id
    QUERY;
}