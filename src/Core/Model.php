<?php

namespace App\Core;

use App\Config\Database;
use PDO;

abstract class Model
{
    protected static $table;
    protected static $hidden = []; // Fields to hide in JSON
    protected static $searchable = []; // Fields to search in 'all' scope

    protected static function getDB()
    {
        return Database::connect();
    }

    // Filter output to hide sensitive fields
    public static function filterOutput($record)
    {
        if (!$record) return null;
        foreach (static::$hidden as $field) {
            if (isset($record[$field])) {
                unset($record[$field]);
            }
        }
        return $record;
    }

    public static function create($data)
    {
        $keys = array_keys($data);
        $fields = implode(", ", $keys);
        $placeholders = ":" . implode(", :", $keys);
        
        $sql = "INSERT INTO " . static::$table . " ($fields) VALUES ($placeholders)";
        $stmt = self::getDB()->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        $id = self::getDB()->lastInsertId();
        
        return self::findById($id);
    }

    public static function findOne($conditions)
    {
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = :$key";
        }
        $whereClause = implode(" AND ", $where);
        
        $sql = "SELECT * FROM " . static::$table . " WHERE $whereClause LIMIT 1";
        $stmt = self::getDB()->prepare($sql);
        
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result ? $result : null;
    }

    public static function findById($id)
    {
        return self::findOne(['id' => $id]);
    }

    public static function update($id, $data)
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $setClause = implode(", ", $set);
        
        $sql = "UPDATE " . static::$table . " SET $setClause WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        
        $stmt->bindValue(":id", $id);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        return self::findById($id);
    }

    public static function delete($id)
    {
        $sql = "DELETE FROM " . static::$table . " WHERE id = :id";
        $stmt = self::getDB()->prepare($sql);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
    }

    public static function paginate($filter, $options)
    {
        $page = isset($options['page']) ? (int)$options['page'] : 1;
        $limit = isset($options['limit']) ? (int)$options['limit'] : 10;
        $sortBy = isset($options['sortBy']) ? $options['sortBy'] : 'created_at:desc';
        
        $offset = ($page - 1) * $limit;

        // Build Where Clause
        $whereSegments = ["1=1"];
        $params = [];

        // 1. Handle Exact Filters (excluding special keys)
        foreach ($filter as $key => $value) {
            if ($key === 'search' || $key === 'scope') continue;
            
            if ($value !== null && $value !== '') {
                $whereSegments[] = "$key = :filter_$key";
                $params[":filter_$key"] = $value;
            }
        }

        // 2. Handle Search
        if (!empty($filter['search'])) {
            $term = $filter['search'];
            $scope = $filter['scope'] ?? 'all';
            $searchSegments = [];

            if ($scope === 'all') {
                // Search in defined searchable fields
                foreach (static::$searchable as $field) {
                    $searchSegments[] = "$field LIKE :search_wild";
                }
                // Optionally search ID if term looks numeric
                if (is_numeric($term)) {
                    $searchSegments[] = "id = :search_exact";
                    $params[':search_exact'] = $term;
                }
                $params[':search_wild'] = "%$term%";
            } elseif ($scope === 'id') {
                $searchSegments[] = "id = :search_exact";
                $params[':search_exact'] = $term;
            } else {
                // Search in specific column
                if (in_array($scope, static::$searchable)) {
                    $searchSegments[] = "$scope LIKE :search_wild";
                    $params[':search_wild'] = "%$term%";
                }
            }

            if (!empty($searchSegments)) {
                $whereSegments[] = "(" . implode(' OR ', $searchSegments) . ")";
            }
        }

        $whereClause = implode(" AND ", $whereSegments);

        // Build Sort
        $sortParts = explode(':', $sortBy);
        $sortField = $sortParts[0];
        $sortOrder = isset($sortParts[1]) && strtolower($sortParts[1]) === 'desc' ? 'DESC' : 'ASC';

        $orderByClause = "$sortField $sortOrder";

        // Count Query
        $countSql = "SELECT COUNT(*) as total FROM " . static::$table . " WHERE $whereClause";
        $stmt = self::getDB()->prepare($countSql);
        $stmt->execute($params);
        $totalResults = (int)$stmt->fetch()['total'];

        // Data Query
        $sql = "SELECT * FROM " . static::$table . " WHERE $whereClause ORDER BY $orderByClause LIMIT :limit OFFSET :offset";
        $stmt = self::getDB()->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $results = $stmt->fetchAll();

        // Filter hidden fields
        $results = array_map([static::class, 'filterOutput'], $results);

        $totalPages = $limit > 0 ? ceil($totalResults / $limit) : 0;

        return [
            'results' => $results,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
            'totalResults' => $totalResults
        ];
    }
}