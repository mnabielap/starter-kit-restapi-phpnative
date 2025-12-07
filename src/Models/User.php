<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static $table = 'users';
    
    // Fields to hide in JSON response
    protected static $hidden = ['password'];

    /**
     * Check if email is taken
     */
    public static function isEmailTaken($email, $excludeUserId = null)
    {
        $sql = "SELECT count(*) as count FROM " . static::$table . " WHERE email = :email";
        $params = [':email' => $email];

        if ($excludeUserId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeUserId;
        }

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Override create to hash password automatically
     */
    public static function create($data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return parent::create($data);
    }

    /**
     * Override update to hash password if changed
     */
    public static function update($id, $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return parent::update($id, $data);
    }
}