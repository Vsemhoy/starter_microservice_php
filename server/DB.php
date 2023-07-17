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

}