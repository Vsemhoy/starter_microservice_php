<?php
namespace objects;
require_once("ObjectInterface.php");

class Event implements ObjectInterface
{
    // define service prefix for ID (each device or application has specified id)
    const PREFIX = "s_";
    const FORMAT_TEXT = 0;
    const FORMAT_HTML = 1;

    public string $id;
    public string $parent;
    public string $title;
    public string $content;
    public int    $format;
    public string $section;
    public string $category;

    public string $user;
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
    

    public function __construct(string $title = "", string $user = '__NULL__', string $id = "")
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
        return $this->id;
    }

    public static function createTableQueryText() : string 
    {
        $text = "
        CREATE TABLE IF NOT EXISTS `event` (
            `id` CHAR(25) NOT NULL,
            `parent` CHAR(26),
            `title` VARCHAR(200) NOT NULL,
            `section` CHAR(26),
            `category` CHAR(26),
            `user` CHAR(8) NOT NULL,
            `client` VARCHAR(120),
            `content` TEXT,
            `format` TINYINT DEFAULT 0,
            `locked` TINYINT DEFAULT 0,
            `access` TINYINT DEFAULT 1,
            `status` TINYINT DEFAULT 0,
            `starred` TINYINT DEFAULT 0,
            `pinned` TINYINT DEFAULT 0,
            `importance` TINYINT DEFAULT 0 CHECK (importance >= 0 AND importance <= 10),
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
        'user'       => 'string',
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

/* C#
// 25-char
public static string GenerateRandomString(int length)
{
    const string pool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    var random = new Random();
    var result = new StringBuilder();

    for (int i = 0; i < length; i++)
    {
        int index = random.Next(pool.Length);
        result.Append(pool[index]);
    }

    return result.ToString();
}


public static string GenerateUniqueId(string prefix = "w_")
{
    lock (lockObject)
    {
        // Get the current timestamp in ticks
        long ticks = DateTime.Now.Ticks;

        // Generate a 4-digit random string for additional uniqueness
        string randomNum = GenerateRandomString(4);

        // Combine the prefix, timestamp, and random number to create the unique ID
        string uniqueId = $"{prefix}{ticks}{randomNum}";

        return uniqueId;
    }
}

// 15-char
public static string GenerateShortId(string prefix = "w_")
{
    lock (lockObject)
    {
        // Get the current timestamp in ticks
        long ticks = DateTime.Now.Ticks;

       // Generate a 5-digit random string for additional uniqueness
        string randomNum = GenerateRandomString(5);

        string tick_c = ticks.ToString().Substring(0, 13);
        tick_c = tick_c.Remove(0,5);

        // Combine the prefix, timestamp, and random number to create the unique ID
        string uniqueId = $"{prefix}{tick_c}{randomNum}";

        return uniqueId;
    }
}

Working with Id's:

The client application (e.g., Android app) generates a temporary ID for the new entity (e.g., Event) before sending it to the server.

The client sends the new entity data along with the temporary ID to the server for synchronization.

On the server side, when the request is received, the server first checks if the temporary ID is unique in the database.

If the temporary ID is unique, the server saves the new entity to the database with the provided ID.

If the temporary ID is not unique (collision), the server generates a new unique ID and saves the new entity with the new ID.

The server then sends the updated entity data back to the client, including the final ID assigned by the server.

The client updates the entity on its side with the final ID received from the server.

By following this process, you can ensure that all IDs used in your system are unique, even if multiple clients attempt to create entities with the same temporary ID simultaneously.

Remember to implement error handling and appropriate response messages to inform the client application about the status of the synchronization process and any ID replacements that occurred on the server side.
*/
