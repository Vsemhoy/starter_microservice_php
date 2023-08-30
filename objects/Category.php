<?php
namespace objects;
require_once("ObjectInterface.php");

class Category implements ObjectInterface
{
      // define service prefix for ID (each device or application has specified id)
      const PREFIX = "s_";

      public string $id;
      public ?string $parent;
      public string $title;
      public ?string $group;
      public string $color;
      public ?string $content;
  
      public string $user;
      
      public int $locked;
      public int $event_counter;
      public int $status;
      public int $ordered;

      public int $level;
      
      public string $created_at;
      public string $updated_at;
      
  
      public function __construct(string $title = "",  string $user = '__NULL__', string $id = "")
      {
          if ($id == ""){
            $this->id = uniqid(self::PREFIX);
          } else {
              $this->id = $id;
          };
          if (strlen($title) > 100)
          {
              $this->title = mb_substr($title, 0, 100) . "...";
          } else {
              $this->title = $title;
          };
          $dt = date("Y-m-d H:i:s");
          if (mb_strlen($title) < 1){
              $this->title = $dt;
          }
  
          $this->color = "";
          $this->group = "";
          $this->content = "";
      
          $this->user = $user;
  
          $this->locked  = 0;
          $this->event_counter  = 0;
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
          $this->id = uniqid(self::PREFIX);
      }
  
      public static function createTableQueryText() : string 
      {
          $text = "
          CREATE TABLE IF NOT EXISTS `category` (
              `id` CHAR(15) NOT NULL,
              `title` VARCHAR(105) NOT NULL,
              `group` VARCHAR(100),
              `color` VARCHAR(8),
              `user` CHAR(8) NOT NULL,
              `content` VARCHAR(1000),
              `locked` TINYINT DEFAULT 0,
              `event_counter` BIGINT DEFAULT 0,
              `status` TINYINT DEFAULT 0,
              `ordered` INT DEFAULT 0,
              `parent` CHAR(26),
              `level` TINYINT DEFAULT 0,
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
              'title'    => 100,
              'group'    => 100,
              'color'    => 8,
              'content'  => 1000
          ];
          if (isset($lim[$key])){
              return $lim[$key];
          } 
          return 0;
      }
  
      public static function getSanitizeMap(){
        return Category::$sanitize_map;
    }

    public static array $sanitize_map = [
        'id'         => 'string',
        'title'      => 'title',
        'group'      => 'nstring',
        'color'      => 'string',
        'user'       => 'string',
        'content'    => 'nstring',
        'locked'     => 'int',
        'event_counter'  => 'int',
        'status'     => 'int',
        'ordered'    => 'int',
        'parent'     => 'nstring',
        'level'      => 'int',
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
