<?php
namespace objects;
require_once("ObjectInterface.php");

use DateTime;

class Event implements ObjectInterface
{
    // define service prefix for ID (each device or application has specified id)
    const PREFIX = "w_";

    public string $id;
    public string $parent;
    public string $title;
    public string $content;
    public string $section;
    public string $category;

    public int $user;
    
    public int $locked;
    public int $access;
    public int $status;
    public int $starred;
    public int $pinned;
    
    public string $created_at;
    public string $updated_at;
    

    public function __construct(string $title, int $user, string $id = "")
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
        $this->parent = "";
        $this->content = "";
        $this->section = "";
        $this->category = "";
    
        $this->user = $user;

        $this->locked  = 0;
        $this->access  = 1;
        $this->status  = 1;
        $this->starred = 0;
        $this->pinned  = 0;

        $dt = date("Y-m-d H:i:s");

        $this->created_at = $dt;
        $this->updated_at = $dt;
    }

    // Get new unique ID
    public function FreshId()
    {
        $this->id = uniqid(self::PREFIX, true);
    }

    public static function CreateTableQueryText() : string 
    {
        $text = "
        CREATE TABLE IF NOT EXISTS `event` (
            `id` CHAR(26) NOT NULL,
            `parent` CHAR(26),
            `title` VARCHAR(200) NOT NULL,
            `section` CHAR(26),
            `category` CHAR(26),
            `user` INT UNSIGNED,
            `content` TEXT,
            `locked` INT DEFAULT 0,
            `access` INT DEFAULT 1,
            `status` INT DEFAULT 0,
            `starred` INT DEFAULT 0,
            `pinned` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ";
            return $text;
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
