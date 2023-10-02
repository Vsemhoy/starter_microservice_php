<?php
namespace server;
if (!defined('MICROSERVICE')){
    define('MICROSERVICE', 'teledox');
};
require_once($_SERVER['DOCUMENT_ROOT'] .'/config.php');
use PDO;
use Config;
use PDOException;

class DB
{
    public static function GetPdo(string $port = "", array $extra = [])
    {
        $config = new Config();

        $string = "mysql:host=" . $config->host . ";";
        if ($port != ""){
            $string .= "port=" . $port . ";";
        };
        $string .= "dbname=" . $config->dbname . "";

        if (count($extra) > 0){
           return new PDO($string, $config->dbuser, $config->dbpass);
        } else {
            return new PDO($string, $config->dbuser, $config->dbpass, $extra);
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

    public static function isColumnExists(string $tableName, string $columnName){
        try {
            $db = DB::GetPdo();
            $stmt = $db->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table_name AND COLUMN_NAME = :column_name");
            $stmt->bindParam(':table_name', $tableName, PDO::PARAM_STR);
            $stmt->bindParam(':column_name', $columnName, PDO::PARAM_STR);
            $stmt->execute();
        
            $result = $stmt->fetch();
        
            if ($result) {
                //echo "The '$columnName' column exists in the '$tableName' table.";
                return true;
            } else {
                //echo "The '$columnName' column does not exist in the '$tableName' table.";
                return false;
            }
        } catch (PDOException $e) {
            //echo "Error: " . $e->getMessage();
            return false;
        }
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
                if (isset($taskwhere->column) && $taskwhere->column == "user") {
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
                if (isset($taskwhere->column) && $taskwhere->column == "access") {
                    $hasAccess = true;
                }
            }
    
            if (!$userMatch && $hasAccess) {
                $modifiedAccessConditions = [];
                foreach ($where as $taskwhere) {
                    if (isset($taskwhere->column) && $taskwhere->column == "access") {
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
    
            if (!$userMatch && count($accessConditions) == 0 && !$hasAccess) {
                // Add default access condition if the user condition was not found
                $accessCondition = (object) [
                    'column' => 'access',
                    'value' => '4', // Modify this value as needed
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
                if (is_string($column))
                {
                    $operator = "=";
                    if (isset($condition->operator)){
                        $operator = $condition->operator;
                    }
                    $value = $condition->value;
                    if (strtoupper($operator) == "BETWEEN"){
                        $conditions[] = "`$column` $operator :$column AND :$column" . 2;
                    } else if (strtoupper($operator) == "LIKE") {
                        $conditions[] = "`$column` $operator :$column";
                    } else {
                        $conditions[] = "`$column` $operator :$column";
                    }
                } else if (is_array($column)) {
                    $resultCondition = "";
                    for ($i=0; $i < count($column); $i++) { 
                        $arcolumn = $column[$i];
                        $operator = "=";
                        $OR = "";
                        if ($i < count($column) - 1){
                            $OR = " OR ";
                        }
                        if (isset($condition->operator)){
                            $operator = $condition->operator;
                        }
                        $value = $condition->value;
                        if (strtoupper($operator) == "BETWEEN"){
                            $resultCondition .= "`$arcolumn` $operator :$arcolumn AND :$arcolumn" . 2 . $OR;
                        } else if (strtoupper($operator) == "LIKE") {
                            $resultCondition .= "`$arcolumn` $operator :$arcolumn" . $OR;
                        } else {
                            $resultCondition .= "`$arcolumn` $operator :$arcolumn" . $OR;
                        }
                    }
                    $conditions[] = $resultCondition;
                }
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

        $stmt = $db->prepare($query);

        $newCon = [];
        // Bind the parameters for the WHERE conditions
        foreach ($where as $condition) {
            $column = $condition->column;
            if (is_string($column))
            {
                $value = $condition->value;
                $operator = "=";
                if (isset($condition->operator)){
                    $operator = $condition->operator;
                }
                if (is_float($value)){
                    $value = (float)$value;
                } else
                if (is_numeric($value)){
                    $value = (int)$value;
                }
                $newCon[$column] = $value;
                if (strtoupper($operator) == "BETWEEN"){
                    $newCon[$column . "2"] = $condition->value2;
                };
                if (strtoupper($operator) == "LIKE"){
                    $newCon[$column] = '%' . $value . '%';
                }
            } else if (is_array($column)){
                for ($i=0; $i < count($column); $i++) { 
                    $arcolumn = $column[$i];
                    $value = $condition->value;
                    $operator = "=";
                    if (isset($condition->operator)){
                        $operator = $condition->operator;
                    }
                    if (is_float($value)){
                        $value = (float)$value;
                    } else
                    if (is_numeric($value)){
                        $value = (int)$value;
                    }
                    $newCon[$arcolumn] = $value;
                    if (strtoupper($operator) == "BETWEEN"){
                        $newCon[$arcolumn . "2"] = $condition->value2;
                    };
                    if (strtoupper($operator) == "LIKE"){
                        $newCon[$arcolumn] = '%' . $value . '%';
                    }
                }
            } 
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
        if (empty($object->id)){
            $object->FreshId();
        }

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
            if ($unsetStamps){
                $object->created_at = date("Y-m-d H:i:s");
            }
            return $object; // Success, return true
        } catch (PDOException $e) {
            // If there's an exception (error), catch it and return false
            return $e;
        }
    }


    public static function updateObject($object, $user, bool $unsetStamps = true) 
    {
        $created = "";
        if ($unsetStamps) {
            $created = $object->created_at;
            if (isset($object->created_at)) {
                unset($object->created_at);
            }
            if (isset($object->updated_at)) {
                unset($object->updated_at);
            }
        }
        
        $table = strtolower($object->Name());
        $setClause = '';
        $dataToUpdate = [];

        $item = self::GetSingleRow($table, $object->id);
        if ($item != false){
            if (isset($item['user']) && $item['user'] != $user){
                return "No rigths for delete.";
            }
            
            if (isset($item['locked']) && $item['locked'] == 1){
                return "The item is locked.";
            }
        }

        
        foreach ($object as $key => $value) {
            $setClause .= "`$key` = :$key, ";
            $dataToUpdate[$key] = $value;
        }
        
        $setClause = rtrim($setClause, ', ');
        
        $query = "UPDATE `$table` SET $setClause WHERE `id` = :id";

        try {
            $pdo = DB::GetPdo();
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':id', $object->id);

            // Bind the values from the object's properties to the placeholders
            foreach ($dataToUpdate as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            // Execute the UPDATE statement
            $stmt->execute();
            
            if ($unsetStamps) {
                $object->updated_at = date("Y-m-d H:i:s");
                $object->created_at = $created;
            }
            
            return $object; // Success
        } catch (PDOException $e) {
            // If there's an exception (error), catch it and return false
            return $e;
        }
    }


    public static function updateByParams($task, $user) {
        $results = [];
    try {
        
        $where = $task->where;
            $table = strtolower($task->objects[0]->Name());
            // Know if column is locked and locked is exists
            $paramLockedExists = DB::isColumnExists($table, 'locked');

            $whereClause = '';
            if (!empty($where)) {
                $whereClause = 'WHERE (';
                $conditions = [];
                foreach ($where as $condition) {
                    $column = $condition->column;
                    if (is_string($column))
                    {
                        if ($column == 'user'){ continue; };
                        if ($column == 'locked'){ continue; };
                        $operator = "=";
                        if (isset($condition->operator)){
                            $operator = $condition->operator;
                        }
                        $value = $condition->value;
                        if (strtoupper($operator) == "BETWEEN"){
                            $conditions[] = "`$column` $operator :$column AND :$column" . 2;
                        } else if (strtoupper($operator) == "LIKE") {
                            $conditions[] = "`$column` $operator :$column";
                        } else {
                            $conditions[] = "`$column` $operator :$column";
                        }
                    } else if (is_array($column)) {
                        $resultCondition = "";
                        for ($i=0; $i < count($column); $i++) { 
                            $arcolumn = $column[$i];
                            $operator = "=";
                            $OR = "";
                            if ($i < count($column) - 1){
                                $OR = " OR ";
                            }
                            if (isset($condition->operator)){
                                $operator = $condition->operator;
                            }
                            $value = $condition->value;
                            if (strtoupper($operator) == "BETWEEN"){
                                $resultCondition .= "`$arcolumn` $operator :$arcolumn AND :$arcolumn" . 2 . $OR;
                            } else if (strtoupper($operator) == "LIKE") {
                                $resultCondition .= "`$arcolumn` $operator :$arcolumn" . $OR;
                            } else {
                                $resultCondition .= "`$arcolumn` $operator :$arcolumn" . $OR;
                            }
                        }
                        $conditions[] = $resultCondition;
                    }
                }
                $whereClause .= implode(' AND ', $conditions);
            }
            if ($whereClause != ''){
                $whereClause = $whereClause . ") AND";
            } else {
                $whereClause = 'WHERE';
            }
    
            $lockedQueryChunk = '';
            if ($paramLockedExists){
                $lockedQueryChunk = " AND `locked` = :locked";
            }
            



            $dataToUpdate = [];
            $setClause = ''; 
            foreach ($task->object as $key => $value) {
                $setClause .= "`$key` = :" . $key . "up, ";
                $dataToUpdate[$key . "up"] = $value;
            }
            
            $setClause = rtrim($setClause, ', ');
            
            $query = "UPDATE `$table` SET $setClause $whereClause `user` = :user" . $lockedQueryChunk;
            $pdo = DB::GetPdo();
            $stmt = $pdo->prepare($query);
            // Bind the parameters for the WHERE conditions
            
            $newCon = [];
            foreach ($where as $condition) {
                $column = $condition->column;
                if (is_string($column))
                {
                    if ($column == 'user'){
                        continue;
                    }
                    if ($column == 'locked')
                    { 
                        continue;
                    };
                    $value = $condition->value;
                    $operator = "=";
                    if (isset($condition->operator)){
                        $operator = $condition->operator;
                    }
                    if (is_float($value)){
                        $value = (float)$value;
                    } else
                    if (is_numeric($value)){
                        $value = (int)$value;
                    }
                    $newCon[$column] = $value;
                    if (strtoupper($operator) == "BETWEEN"){
                        $newCon[$column . "2"] = $condition->value2;
                    };
                    if (strtoupper($operator) == "LIKE"){
                        $newCon[$column] = '%' . $value . '%';
                    }
                } else if (is_array($column)){
                    for ($i=0; $i < count($column); $i++) { 
                        $arcolumn = $column[$i];
                        $value = $condition->value;
                        $operator = "=";
                        if (isset($condition->operator)){
                            $operator = $condition->operator;
                        }
                        if (is_float($value)){
                            $value = (float)$value;
                        } else
                        if (is_numeric($value)){
                            $value = (int)$value;
                        }
                        $newCon[$arcolumn] = $value;
                        if (strtoupper($operator) == "BETWEEN"){
                            $newCon[$arcolumn . "2"] = $condition->value2;
                        };
                        if (strtoupper($operator) == "LIKE"){
                            $newCon[$arcolumn] = '%' . $value . '%';
                        }
                    }
                } 
            }

            $newCon['user'] = $user;
            if ($paramLockedExists){
                $newCon['locked'] = 0;
            }
            // $stmt->bindValue(':user', $user);
            
            // Execute the Update statement
            // Bind the values from the object's properties to the placeholders
            foreach ($dataToUpdate as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute($newCon);
            $results[] = []; // Success
            
        } catch (PDOException $e) {
            // If there's an exception (error), catch it and return false
            return $e;
        }
        return $results;
    }


    public static function updateObjectOrder($object, $user, bool $unsetStamps = true) 
    {
        if ($unsetStamps) {
            if (isset($object->updated_at)) {
                unset($object->updated_at);
            }
        }
        
        $table = strtolower($object->Name());
        $setClause = '';
        $dataToUpdate = [];

        $item = self::GetSingleRow($table, $object->id);
        if ($item != false){
            if ($item['user'] != $user){
                return "No rigths for update.";
            }
        }
        $setClause .= "`ordered` = :ordered, ";
        $dataToUpdate['ordered'] = $object->ordered;
        
        $setClause = rtrim($setClause, ', ');
        
        $query = "UPDATE `$table` SET $setClause WHERE `id` = :id";

        try {
            $pdo = DB::GetPdo();
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':id', $object->id);

            // Bind the values from the object's properties to the placeholders
            foreach ($dataToUpdate as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            // Execute the UPDATE statement
            $stmt->execute();
            
            if ($unsetStamps) {
                $object->updated_at = date("Y-m-d H:i:s");
            }
            
            return $object; // Success
        } catch (PDOException $e) {
            // If there's an exception (error), catch it and return false
            return $e;
        }
    }


    public static function deleteObject($object, $user) {
        $table = strtolower($object->Name());
        $id = trim($object->id);

        $item = self::GetSingleRow($table, $id);
        if ($item != false){
            if (isset($item['user']) && $item['user'] != $user){
                return "No rigths for delete.";
            }
            
            if (isset($item['locked']) && $item['locked'] == 1){
                return "The item is locked.";
            }
        }

        $query = "DELETE FROM `$table` WHERE `id` = :id";

        try {
            $pdo = DB::GetPdo();
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':id', $id);
           // $stmt->bindValue(':user', $user);


            // Execute the DELETE statement
            $stmt->execute();
            return $object; // Success
        } catch (PDOException $e) {
            // If there's an exception (error), catch it and return false
            return $e;
        }
    }


    public static function deleteByParams($task, $user) {
        $results = [];
    try {
        
        $where = $task->where;
            $table = strtolower($task->objects[0]->Name());
            // Know if column is locked and locked is exists
            $paramLockedExists = DB::isColumnExists($table, 'locked');

            $whereClause = '';
            if (!empty($where)) {
                $whereClause = 'WHERE (';
                $conditions = [];
                foreach ($where as $condition) {
                    $column = $condition->column;
                    if (is_string($column))
                    {
                        if ($column == 'user'){ continue; };
                        if ($column == 'locked'){ continue; };
                        $operator = "=";
                        if (isset($condition->operator)){
                            $operator = $condition->operator;
                        }
                        $value = $condition->value;
                        if (strtoupper($operator) == "BETWEEN"){
                            $conditions[] = "`$column` $operator :$column AND :$column" . 2;
                        } else if (strtoupper($operator) == "LIKE") {
                            $conditions[] = "`$column` $operator :$column";
                        } else {
                            $conditions[] = "`$column` $operator :$column";
                        }
                    } else if (is_array($column)) {
                        $resultCondition = "";
                        for ($i=0; $i < count($column); $i++) { 
                            $arcolumn = $column[$i];
                            $operator = "=";
                            $OR = "";
                            if ($i < count($column) - 1){
                                $OR = " OR ";
                            }
                            if (isset($condition->operator)){
                                $operator = $condition->operator;
                            }
                            $value = $condition->value;
                            if (strtoupper($operator) == "BETWEEN"){
                                $resultCondition .= "`$arcolumn` $operator :$arcolumn AND :$arcolumn" . 2 . $OR;
                            } else if (strtoupper($operator) == "LIKE") {
                                $resultCondition .= "`$arcolumn` $operator :$arcolumn" . $OR;
                            } else {
                                $resultCondition .= "`$arcolumn` $operator :$arcolumn" . $OR;
                            }
                        }
                        $conditions[] = $resultCondition;
                    }
                }
                $whereClause .= implode(' AND ', $conditions);
            }
            if ($whereClause != ''){
                $whereClause = $whereClause . ") AND";
            } else {
                $whereClause = 'WHERE';
            }
            $lockedQueryChunk = '';
            if ($paramLockedExists){
                $lockedQueryChunk = " AND `locked` = :locked";
            }
            
            $query = "DELETE FROM `$table` $whereClause `user` = :user" . $lockedQueryChunk;
            $pdo = DB::GetPdo();
            $stmt = $pdo->prepare($query);
            // Bind the parameters for the WHERE conditions
            
            $newCon = [];
            foreach ($where as $condition) {
                $column = $condition->column;
                if (is_string($column))
                {
                    if ($column == 'user'){
                        continue;
                    }
                    if ($column == 'locked')
                    { 
                        continue;
                    };
                    $value = $condition->value;
                    $operator = "=";
                    if (isset($condition->operator)){
                        $operator = $condition->operator;
                    }
                    if (is_float($value)){
                        $value = (float)$value;
                    } else
                    if (is_numeric($value)){
                        $value = (int)$value;
                    }
                    $newCon[$column] = $value;
                    if (strtoupper($operator) == "BETWEEN"){
                        $newCon[$column . "2"] = $condition->value2;
                    };
                    if (strtoupper($operator) == "LIKE"){
                        $newCon[$column] = '%' . $value . '%';
                    }
                } else if (is_array($column)){
                    for ($i=0; $i < count($column); $i++) { 
                        $arcolumn = $column[$i];
                        $value = $condition->value;
                        $operator = "=";
                        if (isset($condition->operator)){
                            $operator = $condition->operator;
                        }
                        if (is_float($value)){
                            $value = (float)$value;
                        } else
                        if (is_numeric($value)){
                            $value = (int)$value;
                        }
                        $newCon[$arcolumn] = $value;
                        if (strtoupper($operator) == "BETWEEN"){
                            $newCon[$arcolumn . "2"] = $condition->value2;
                        };
                        if (strtoupper($operator) == "LIKE"){
                            $newCon[$arcolumn] = '%' . $value . '%';
                        }
                    }
                } 
            }

            $newCon['user'] = $user;
            if ($paramLockedExists){
                $newCon['locked'] = 0;
            }
            // $stmt->bindValue(':user', $user);
            
            // Execute the DELETE statement
            $stmt->execute($newCon);
            $results[] = []; // Success
            
        } catch (PDOException $e) {
            // If there's an exception (error), catch it and return false
            return $e;
        }
        return $results;
    }

    public static function countRows($task)
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
                if (isset($taskwhere->column) && $taskwhere->column == "user") {
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
                if (isset($taskwhere->column) && $taskwhere->column == "access") {
                    $hasAccess = true;
                }
            }
    
            if (!$userMatch && $hasAccess) {
                $modifiedAccessConditions = [];
                foreach ($where as $taskwhere) {
                    if (isset($taskwhere->column) && $taskwhere->column == "access") {
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
    
            if (!$userMatch && count($accessConditions) == 0 && !$hasAccess) {
                // Add default access condition if the user condition was not found
                $accessCondition = (object) [
                    'column' => 'access',
                    'value' => '4', // Modify this value as needed
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
                if (is_string($column))
                {
                    $operator = "=";
                    if (isset($condition->operator)){
                        $operator = $condition->operator;
                    }
                    $value = $condition->value;
                    if (strtoupper($operator) == "BETWEEN"){
                        $conditions[] = "`$column` $operator :$column AND :$column" . 2;
                    } else if (strtoupper($operator) == "LIKE") {
                        $conditions[] = "`$column` $operator :$column";
                    } else {
                        $conditions[] = "`$column` $operator :$column";
                    }
                } else if (is_array($column)) {
                    $resultCondition = "";
                    for ($i=0; $i < count($column); $i++) { 
                        $arcolumn = $column[$i];
                        $operator = "=";
                        $OR = "";
                        if ($i < count($column) - 1){
                            $OR = " OR ";
                        }
                        if (isset($condition->operator)){
                            $operator = $condition->operator;
                        }
                        $value = $condition->value;
                        if (strtoupper($operator) == "BETWEEN"){
                            $resultCondition .= "`$arcolumn` $operator :$arcolumn AND :$arcolumn" . 2 . $OR;
                        } else if (strtoupper($operator) == "LIKE") {
                            $resultCondition .= "`$arcolumn` $operator :$arcolumn" . $OR;
                        } else {
                            $resultCondition .= "`$arcolumn` $operator :$arcolumn" . $OR;
                        }
                    }
                    $conditions[] = $resultCondition;
                }
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
        $query = "SELECT COUNT(*) FROM `$table` $whereClause $orderClause $limitClause";

        $stmt = $db->prepare($query);

        $newCon = [];
        // Bind the parameters for the WHERE conditions
        foreach ($where as $condition) {
            $column = $condition->column;
            if (is_string($column))
            {
                $value = $condition->value;
                $operator = "=";
                if (isset($condition->operator)){
                    $operator = $condition->operator;
                }
                if (is_float($value)){
                    $value = (float)$value;
                } else
                if (is_numeric($value)){
                    $value = (int)$value;
                }
                $newCon[$column] = $value;
                if (strtoupper($operator) == "BETWEEN"){
                    $newCon[$column . "2"] = $condition->value2;
                };
                if (strtoupper($operator) == "LIKE"){
                    $newCon[$column] = '%' . $value . '%';
                }
            } else if (is_array($column)){
                for ($i=0; $i < count($column); $i++) { 
                    $arcolumn = $column[$i];
                    $value = $condition->value;
                    $operator = "=";
                    if (isset($condition->operator)){
                        $operator = $condition->operator;
                    }
                    if (is_float($value)){
                        $value = (float)$value;
                    } else
                    if (is_numeric($value)){
                        $value = (int)$value;
                    }
                    $newCon[$arcolumn] = $value;
                    if (strtoupper($operator) == "BETWEEN"){
                        $newCon[$arcolumn . "2"] = $condition->value2;
                    };
                    if (strtoupper($operator) == "LIKE"){
                        $newCon[$arcolumn] = '%' . $value . '%';
                    }
                }
            } 
        }
        
        // Execute the query
        $stmt->execute($newCon);

        // Fetch the rows
        $count = $stmt->fetchColumn();

        if ($count) {
            return $count;
        }

        return 0;
    }
}