<?php
namespace server;

class Task
{
    public const ACTION_NONE      = 0;
    public const ACTION_SELECT    = 1;
    public const ACTION_WRITE     = 2;
    public const ACTION_UPDATE    = 3;
    public const ACTION_DELETE    = 4;
    public const ACTION_GETSTRUCT = 5;

    public const ORDER_NONE       = 0;
    public const ORDER_ASCEND     = 1;
    public const ORDER_DESCEND    = 2; 

    public int    $user;
    public int    $action;      // write / update / delete / structure / 
    public string $type;        // e.g. table name Like "event, material, category, etc"
    public array  $objects;     // array of rows to handle
    public array  $where;       // array of objects where key + value Like [{"name" : "id", "value" : "33", "operator": "="}, {...}];
    public int    $order;
    public int    $limit;
    public int    $offset;
    public string $setKey;      // if result should be an associative array, set array key
    public array  $postActions; // array of customer specified actions to do after main handle 
    public array  $results;
    public array  $map;
    public array  $lim;
    
    public function __construct(
        int    $user,
        int    $action, 
        string $type,
        array  $objects      = [],
        array  $where       = [],
        int    $order       = 0,
        string $setKey      = "",
        int    $limit       = 0,
        int    $offset      = 0,
        array  $postActions = [],
        array  $results     = [],
        array  $map         = [],
        array  $lim         = [],
        )
    {
        $this->user        = $user;
        $this->action      = $action;
        $this->type        = $type;
        $this->objects     = $objects;
        $this->where       = $where;
        $this->order       = $order;
        $this->limit       = $limit;
        $this->setKey      = $setKey;
        $this->offset      = $offset;
        $this->postActions = $postActions;
        $this->results     = $results;
        $this->map         = $map;
        $this->lim         = $lim;

    }

    public static function Where()
    {
        $obj = (object) array();
        $obj->column   = "id";
        $obj->value    = "1";
        $obj->operator = "=";
        return $obj;
    }

    public static function taskFromObject($obj, $user = 0)
    {
        if (!isset($obj->action)){ return "There is no action detected!"; };
        if (!isset($obj->type)){   return "There is no type definition detected!"; };
        $task = new Task($user, (int)$obj->action, $obj->type);
        $task->user = $user;
        if (isset($obj->objects)){ 
            $task->objects = $obj->objects;
        };
        if (isset($obj->where)) { 
            $task->where = $obj->where;
        };
        if (isset($obj->order)) { 
            $task->order = $obj->order;
        };
        if (isset($obj->limit)) { 
            $task->limit = $obj->limit;
        };
        if (isset($obj->setKey)) { 
            $task->setKey = $obj->setKey;
        };
        if (isset($obj->offset)) { 
            $task->offset = $obj->offset;
        };
        if (isset($obj->postActions)) { 
            $task->postActions = $obj->postActions;
        };
        return $task;
    }

    public function simplify()
    {
        unset($this->objects);     
        unset($this->where);      
        unset($this->order);
        unset($this->limit);
        unset($this->offset);
        unset($this->setKey);      
        unset($this->postActions);
        // unset($this->map);
        // unset($this->lim);
        return $this;
    }
}