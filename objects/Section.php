<?php
namespace objects;
require_once("ObjectInterface.php");

class Section implements ObjectInterface
{
    // define service prefix for ID (each device or application has specified id)
    const PREFIX = "s_";

    public string $id;
    public string $title;
    public string $color;
    public ?string $content;
    public string $categories;

    public string $user;
    
    public int $locked;
    public int $access;
    public int $status;
    public int $ordered;

    public int $pinned; // e.g. placed in menu
    public string $pinstyle;
    
    public string $created_at;
    public string $updated_at;
    

    public function __construct(string $title = "", string $user = '__NULL__', string $id = "")
    {
        if ($id == ""){
            $this->id = uniqid(self::PREFIX);
        } else {
            $this->id = $id;
        };
        if (strlen($title) > 100)
        {
            $this->title = mb_substr($title, 0, 60) . "...";
        } else {
            $this->title = $title;
        };
        $dt = date("Y-m-d H:i:s");
        if (strlen($title) < 1){
            $this->title = $dt;
        }
        $this->categories = "";
        $this->color = "";
        $this->content = "";
    
        $this->user = $user;

        $this->locked  = 0;
        $this->access  = 1;
        $this->status  = 1;
        $this->ordered = 0;
        $this->pinned = 0;
        $this->pinstyle = "";

        $this->created_at = $dt;
        $this->updated_at = $dt;
    }

    // Get new unique ID
    public function FreshId()
    {
        $this->id = uniqid(self::PREFIX);
    }

    public static function createTableQueryText() : string 
    {
        $text = "
        CREATE TABLE IF NOT EXISTS `section` (
            `id` CHAR(15) NOT NULL,
            `title` VARCHAR(105) NOT NULL,
            `color` VARCHAR(8),
            `user` CHAR(8) NOT NULL,
            `content` VARCHAR(1000),
            `categories` VARCHAR(1000) NOT NULL,
            `locked` TINYINT DEFAULT 0,
            `access` TINYINT DEFAULT 1,
            `status` TINYINT DEFAULT 0,
            `ordered` INT DEFAULT 0,
            `pinned` INT DEFAULT 0,
            `pinstyle` VARCHAR(1000) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ";
            return $text;
    }

    public static function getSanitizeMap(){
        return Section::$sanitize_map;
    }

    public static array $sanitize_map = [
        'id'      => 'string',
        'title'   => 'title',
        'color'   => 'string',
        'content' => 'nstring',
        'categories' => 'string',
        'user'    => 'string',
        'pinstyle' => 'string',
        'locked'  => 'int',
        'access'  => 'int',
        'status'  => 'int',
        'ordered' => 'int',
        'pinned'  => 'int',
    ];


    public static function getStringLimit($key)
    {
        $lim = [
            'title'    => 60,
            'color'    => 8,
            'content'  => 1000,
            'pinstyle' => 1000,
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
