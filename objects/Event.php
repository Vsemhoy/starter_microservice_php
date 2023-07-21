<?php
namespace objects;
require_once("ObjectInterface.php");

class Event implements ObjectInterface
{
    // define service prefix for ID (each device or application has specified id)
    const PREFIX = "w_";
    const FORMAT_TEXT = 0;
    const FORMAT_HTML = 1;

    public string $id;
    public string $parent;
    public string $title;
    public string $content;
    public int    $format;
    public string $section;
    public string $category;

    public int $user;
    public string $client;
    
    public int $locked;
    public int $access;
    public int $status;
    public int $starred;
    public int $pinned;
    public int $importance;
    public string $location;
    
    public string $setdate;
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
        $this->parent     = "";
        $this->content    = "";
        $this->format     = 0;
        $this->section    = "";
        $this->category   = "";
      
        $this->user       = $user;
        $this->client     = "";
  
        $this->locked     = 0;
        $this->access     = 1;
        $this->status     = 1;
        $this->starred    = 0;
        $this->pinned     = 0;
        $this->importance = 2;

        $this->setdate    = date("Y-m-d");
        $this->created_at = $dt;
        $this->updated_at = $dt;

        $this->location  = "";
    }

    // Get new unique ID
    public function FreshId()
    {
        $this->id = uniqid(self::PREFIX, true);
    }

    public static function createTableQueryText() : string 
    {
        $text = "
        CREATE TABLE IF NOT EXISTS `event` (
            `id` CHAR(26) NOT NULL,
            `parent` CHAR(26),
            `title` VARCHAR(200) NOT NULL,
            `section` CHAR(26),
            `category` CHAR(26),
            `user` INT UNSIGNED,
            `client` VARCHAR(120),
            `content` TEXT,
            `format` UNSIGNED TINYINT DEFAULT 0,
            `locked` TINYINT DEFAULT 0,
            `access` TINYINT DEFAULT 1,
            `status` TINYINT DEFAULT 0,
            `starred` TINYINT DEFAULT 0,
            `pinned` TINYINT DEFAULT 0,
            `importance` UNSIGNED TINYINT DEFAULT 2 CHECK (importance >= 0 AND importance <= 10),
            `location` VARCHAR(50),
            `setdate` DATE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ";
            return $text;
    }

    public static function getStringLimit($key)
    {
        $lim = [
            'title'    => 190,
            'client'   => 120,
            'content'  => 99000,
            'location' => 50
        ];
        if (isset($lim[$key])){
            return $lim[$key];
        } 
        return 0;
    }

    public static function getSanitizeMap(){
        return Event::$sanitize_map;
    }

    public static array $sanitize_map = [
            'id'         => 'string',
            'format'     => 'int', // Format can be always before content
            'parent'     => 'string',
            'title'      => 'title',
            'section'    => 'string',
            'category'   => 'string',
            'user'       => 'int',
            'client'     => 'string',
            'content'    => 'html',
            'locked'     => 'int',
            'access'     => 'int',
            'status'     => 'int',
            'starred'    => 'int',
            'pinned'     => 'int',
            'importance' => 'int',
            'location'   => 'json',
            'setdate'    => 'date', // Assuming date is passed as a string
            'created_at' => 'datetime', // Assuming date is passed as a string
            'updated_at' => 'datetime', // Assuming date is passed as a string
        ];



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
