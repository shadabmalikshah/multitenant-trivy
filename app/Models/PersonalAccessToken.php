<?php
namespace App\Models;

    // Removed Sanctum dependency; file can be deleted if not used elsewhere.

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $connection = 'tenant';

    public static function findToken($token)
    {
        // Always force tenant connection for token lookup
        $tenantConnection = 'tenant';
        $instance = (new static)->setConnection($tenantConnection);
        $token = $instance->getTokenFromString($token);
        return $instance->where('token', hash('sha256', $token))->first();
    }

    public function tokenable()
    {
        // Always force tenant connection for user lookup
        $relation = $this->morphTo();
        if (method_exists($relation, 'getQuery')) {
            $model = $relation->getQuery()->getModel();
            $model->setConnection('tenant');
        }
        return $relation;
    }
}
