<?php
namespace objects;
require_once("ObjectInterface.php");

class Section implements ObjectInterface
{
    // define service prefix for ID (each device or application has specified id)
    const PREFIX = "w_";

    public string $id;
    public string $title;
    public string $color;
    public string $content;

    public int $user;
    
    public int $locked;
    public int $access;
    public int $status;
    public int $ordered;
    
    public string $created_at;
    public string $updated_at;
    

    public function __construct(string $title = "", int $user = 0, string $id = "")
    {
        if ($id == ""){
            $this->id = uniqid(self::PREFIX, true);
        } else {
            $this->id = $id;
        };
        if (strlen($title) > 190)
        {
            $this->title = mb_substr($title, 0, 190) . "...";
        } else {
            $this->title = $title;
        };
        $dt = date("Y-m-d H:i:s");
        if (mb_strlen($title) < 1){
            $this->title = $dt;
        }

        $this->color = "";
        $this->content = "";
    
        $this->user = $user;

        $this->locked  = 0;
        $this->access  = 1;
        $this->status  = 1;
        $this->ordered = 0;

        $this->created_at = $dt;
        $this->updated_at = $dt;
    }

    // Get new unique ID
    public function FreshId()
    {
        $this->id = uniqid(self::PREFIX, true);
    }

    public static function createTableQueryText() : string 
    {
        $text = "
        CREATE TABLE IF NOT EXISTS `section` (
            `id` CHAR(26) NOT NULL,
            `title` VARCHAR(200) NOT NULL,
            `color` VARCHAR(8),
            `user` INT UNSIGNED,
            `content` VARCHAR(1000),
            `locked` TINYINT DEFAULT 0,
            `access` TINYINT DEFAULT 1,
            `status` TINYINT DEFAULT 0,
            `ordered` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ";
            return $text;
    }

    public static function getSanitizeMap()
    {
        $map = [
            'title'   => 'title',
            'color'   => 'string',
            'content' => 'string',
            'user'    => 'int',
            'locked'  => 'int',
            'access'  => 'int',
            'status'  => 'int',
            'ordered' => 'int',
        ];
        return $map;
    }

    public static function getStringLimit($key)
    {
        $lim = [
            'title'   => 190,
            'color'   => 8,
            'content' => 1000
        ];
        if (isset($lim[$key])){
            return $lim[$key];
        } 
        return 0;
    }


    public function Name()
    {
        $name = get_class($this);
        if (str_contains($name, "\\")){
            $tab = explode("\\", $name);
            $name = $tab[count($tab) - 1];
        }
        return $name;
    }
}
