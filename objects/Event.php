<?php
namespace objects;
require_once("ObjectInterface.php");

use DateTime;

class Event implements ObjectInterface
{
    public string $id;
    public string $parent;
    public string $title;
    public string $content;
    public string $category;

    public int $user;
    
    public int $locked;
    public int $access;
    public int $status;
    public int $starred;
    public int $pinned;
    
    public int $created_at;
    public int $updated_at;
    

    public function __construct(string $title, int $user, string $id = "")
    {
        if ($id == ""){
            $this->id = uniqid('', true);
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
        $this->category = "";
    
        $this->user = $user;

        $this->locked  = 0;
        $this->access  = 1;
        $this->status  = 1;
        $this->starred = 0;
        $this->pinned  = 0;

        $dt = new DateTime();

        $this->created_at = $dt->getTimeStamp();
        $this->updated_at = $dt->getTimeStamp();
    }


    public function CreateTableQueryText() : string 
    {
        $text = "
        CREATE TABLE IF NOT EXISTS `event` (
            `id` CHAR(30) NOT NULL,
            `parent` CHAR(30),
            `title` VARCHAR(200) NOT NULL,
            `category` CHAR(30),
            `user` INT UNSIGNED,
            `content` TEXT,
            `locked` INT DEFAULT 0,
            `access` INT DEFAULT 1,
            `status` INT DEFAULT 0,
            `starred` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)) 
            CHARACTER SET utf8 COLLATE utf8mb4_general_ci
            ";
            return $text;
    }
}