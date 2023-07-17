<?php
namespace server;

class Task
{
    public const ACTION_SELECT    = 1;
    public const ACTION_WRITE     = 2;
    public const ACTION_UPDATE    = 3;
    public const ACTION_DELETE    = 4;
    public const ACTION_GETSTRUCT = 5;

    public int    $action;      // write / update / delete / structure / 
    public string $type;        // e.g. table name Like "event, material, category, etc"
    public array  $objects;     // array of rows to handle
    public array  $postActions; // array of customer specified actions to do after main handle 
    public array  $where;       // array of objects where key + value Like [{"name" : "id", "value" : "33", "operator": "="}, {...}];
    public string $setKey;      // if result should be an associative array, set array key
    
    public function __construct(
        int    $action, 
        string $type,
        array  $objcts,
        array  $where       = [],
        string $setKey      = "",
        array  $postActions = []
        )
    {
        $this->action      = $action;
        $this->type        = $type;
        $this->objects     = $objcts;
        $this->where       = $where;
        $this->setKey      = $setKey;
        $this->postActions = $postActions;
    }

    public static function NewFROM()
    {
        $obj = (object) array();
        $obj->name     = "id";
        $obj->value    = "1";
        $obj->operator = "=";
        return $obj;
    }


}