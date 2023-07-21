<?php
namespace objects;
require_once("ObjectInterface.php");

class Category implements ObjectInterface
{
      // define service prefix for ID (each device or application has specified id)
      const PREFIX = "w_";

      public string $id;
      public string $parent;
      public string $title;
      public string $color;
      public string $content;
  
      public int $user;
      
      public int $locked;
      public int $events;
      public int $status;
      public int $ordered;

      public int $level;
      
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
          $this->events  = 0;
          $this->status  = 1;
          $this->ordered = 0;

          $this->parent = "";
          $this->level = 0;
  
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
          CREATE TABLE IF NOT EXISTS `category` (
              `id` CHAR(26) NOT NULL,
              `title` VARCHAR(200) NOT NULL,
              `color` VARCHAR(8),
              `user` INT UNSIGNED,
              `content` VARCHAR(1000),
              `locked` TINYINT DEFAULT 0,
              `events` TINYINT DEFAULT 0,
              `status` TINYINT DEFAULT 0,
              `ordered` INT DEFAULT 0,
              `parent` CHAR(26),
              `level` UNSIGNED TINYINT DEFAULT 0,  
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
              'color'    => 8,
              'content'  => 1000
          ];
          if (isset($lim[$key])){
              return $lim[$key];
          } 
          return 0;
      }
  
      public static function getSanitizeMap()
      {
          $map = [
            'id'         => 'string',
            'title'      => 'title',
            'color'      => 'string',
            'user'       => 'int',
            'content'    => 'string',
            'locked'     => 'int',
            'events'     => 'int',
            'status'     => 'int',
            'ordered'    => 'int',
            'parent'     => 'string',
            'level'      => 'int',
            'created_at' => 'datetime', // Assuming date is passed as a string
            'updated_at' => 'datetime', // Assuming date is passed as a string
          ];
          return $map;
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
