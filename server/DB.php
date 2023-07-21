<?php
namespace server;
use PDO;

class DB
{
    private const dbname = "splmod";
    private const host = "localhost";
    private const dbuser = "root";
    private const dbpass = "";

    public static function GetPdo(string $port = "", array $extra = [])
    {
        $string = "mysql:host=" . self::host . ";";
        if ($port != ""){
            $string .= "port=" . $port . ";";
        };
        $string .= "dbname=" . self::dbname . "";

        if (count($extra) > 0){
           return new PDO($string, self::dbuser, self::dbpass);
        } else {
            return new PDO($string, self::dbuser, self::dbpass, $extra);
        }
        return null;
    }
    

    public static function SelectAll(string $table)
    {
        $pdo = DB::GetPdo();
        $tmt = $pdo->query("SELECT * FROM " . $table);
        $result = $tmt->fetch();
        return $result;
    }

    
    public static function GetTables()
    {
        $tableList = array();
        $pdo = DB::GetPdo();
        $tmt = $pdo->query("SHOW TABLES");
        while ($row = $tmt->fetch(PDO::FETCH_NUM)) {
            $tableList[] = $row[0];
        }
        return $tableList;
    }


    public static function GetHeaders(string $table)
    {
        $db = DB::GetPdo();
        $select = $db->query('SELECT * FROM ' . $table);

        $total_column = $select->columnCount();
        var_dump($total_column);

        for ($counter = 0; $counter < $total_column; $counter ++) {
            $meta = $select->getColumnMeta($counter);
            $column[] = $meta['name'];
        }
        return $column;
    }


    public static function GetTypes(string $table)
    {
        $db = DB::GetPdo();
        $select = $db->query('SELECT * FROM ' . $table);

        $total_column = $select->columnCount();
        var_dump($total_column);

        for ($counter = 0; $counter < $total_column; $counter ++) {
            $meta = $select->getColumnMeta($counter);
            $column[] = $meta;
        }
        return $column;
    }

    public static function Describe(string $table)
    {
        $db = DB::GetPdo();
        $result = $db->query("DESCRIBE " . $table)->fetchAll(PDO::FETCH_COLUMN);
        return $result;
    }


    public static function createTable($query)
    {
        $db = DB::GetPdo();
        $do = $db->query($query);
    }


    public static function CheckFreeId(string $table, string $id) : bool
    {
        // if object with this id is not found, return true
        $db = DB::GetPdo();
        $stmt = $db->prepare("SELECT `id` FROM $table WHERE `id` = :id");
        // Bind the parameter
        $stmt->bindParam(':id', $id);
        // Execute the query
        $stmt->execute();
        // Fetch the row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return false;
        }
            return true;
    }


    public static function writeObject($object, bool $unsetStamps = true) 
    {
        if ($unsetStamps)
        {
            if (isset($object->created_at)){
                unset($object->created_at);
            };
            if (isset($object->updated_at)){
                unset($object->updated_at);
            };
        };
        $table = strtolower($object->Name());

        $limit = 12;
        while(!DB::CheckFreeId($table, $object->id))
        {
            $limit--;
            if ($limit == 0){
                return false;
            }
            $object->FreshId();
        }

        $columns = implode('`, `', array_keys((array) $object));
        $placeholders = ':' . implode(', :', array_keys((array) $object));
        $query = "INSERT INTO `$table` (`$columns`) VALUES ($placeholders)";

        try {
            $pdo = DB::GetPdo();
            $stmt = $pdo->prepare($query);

            // Bind the values from the object's properties to the placeholders
            foreach ($object AS $key => $value)
            {
                $stmt->bindValue(':'.$key, $value);
            }

            // Execute the INSERT statement
            $stmt->execute();
            return true; // Success, return true
        } catch (PDOException $e) {
            // If there's an exception (error), catch it and return false
            return false;
        }
        return true;
    }


    public static function GetSingleRow(string $table, string $id)
    {
        $db = DB::GetPdo();
        $stmt = $db->prepare("SELECT * FROM $table WHERE `id` = :id");
        // Bind the parameter
        $stmt->bindParam(':id', $id);
        // Execute the query
        $stmt->execute();
        // Fetch the row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
            return false;
    }

    public static function GetRowsSimple(string $table, string $param, string $value)
    {
        $db = DB::GetPdo();
        $stmt = $db->prepare("SELECT * FROM $table WHERE `$param` = :value");
        // Bind the parameter
        $stmt->bindParam(':value', $value);
        // Execute the query
        $stmt->execute();
        // Fetch the row
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
            return false;
    }



    
    public static function GetRows($task)
    {
         $table   = strtolower($task->type);
         $where   = $task->where;
         $limit   = $task->limit;
         $offset  = $task->offset;
         $order   = $task->order;
        // Check if User can access the data only if Table contains "access" column
        if (isset($task->map['access'])) {
            $accessConditions = [];
            $hasAccess = false;
            $userMatch = false;
            foreach ($where as $taskwhere) {
                if (isset($taskwhere->column) && $taskwhere->column === "user") {
                    if ($taskwhere->value == $task->user) {
                        $userMatch = true;
                    } else {
                        // Add access condition only if the user does not match
                        $accessCondition = (object) [
                            'column' => 'access',
                            'value' => '3', // Modify this value as needed
                            'operator' => '=',
                        ];
                        $accessConditions[] = $accessCondition;
                    }
                }
                if (isset($taskwhere->column) && $taskwhere->column === "access") {
                    $hasAccess = true;
                }
            }
    
            if (!$userMatch && $hasAccess) {
                $modifiedAccessConditions = [];
                foreach ($where as $taskwhere) {
                    if (isset($taskwhere->column) && $taskwhere->column === "access") {
                        $modifiedAccessCondition = (object) [
                            'column' => 'access',
                            'value' => '3',
                            'operator' => '=',
                        ];
                        $modifiedAccessConditions[] = $modifiedAccessCondition;
                    } else {
                        $modifiedAccessConditions[] = $taskwhere;
                    }
                }
                $where = $modifiedAccessConditions;
            }
    
            if (!$userMatch && count($accessConditions) === 0 && !$hasAccess) {
                // Add default access condition if the user condition was not found
                $accessCondition = (object) [
                    'column' => 'access',
                    'value' => '3', // Modify this value as needed
                    'operator' => '=',
                ];
                $accessConditions[] = $accessCondition;
            }
    
            // Merge access conditions with the original where array
            $where = array_merge($where, $accessConditions);
        }

        $db = DB::GetPdo();

        $whereClause = '';
        if (!empty($where)) {
            $whereClause = 'WHERE (';
            $conditions = [];
            foreach ($where as $condition) {
                $column = $condition->column;
                $operator = $condition->operator;
                $value = $condition->value;
                $conditions[] = "`$column` $operator :$column";
            }
            $whereClause .= implode(' AND ', $conditions);
        }
        if ($whereClause != ''){
            $whereClause = $whereClause . ")";
        }

        $limitClause = '';
        if ($limit > 0) {
            $limitClause = "LIMIT $limit";
            if ($offset > 0) {
                $limitClause .= " OFFSET $offset";
            }
        }

        $orderClause = '';
        if (!empty($order)) {
            $orderClause = "ORDER BY $order";
        }
        $query = "SELECT * FROM `$table` $whereClause $orderClause $limitClause";
        echo $query;

        $stmt = $db->prepare($query);

        $newCon = [];
        // Bind the parameters for the WHERE conditions
        foreach ($where as $condition) {
            $column = $condition->column;
            $value = $condition->value;
            if (is_float($value)){
                $value = (float)$value;
            } else
            if (is_numeric($value)){
                $value = (int)$value;
            }
            $newCon[$column] = $value;
        }
        
        // Execute the query
        $stmt->execute($newCon);

        // Fetch the rows
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            return $rows;
        }

        return false;
    }


}