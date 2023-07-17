<?php
namespace server;

class Response
{
    public const ACTION_SELECT    = 1;
    public const ACTION_WRITE     = 2;
    public const ACTION_UPDATE    = 3;
    public const ACTION_DELETE    = 4;
    public const ACTION_GETSTRUCT = 5;


    public int    $status;
    public string $message;
    public array  $objects;
    public int    $action;
    public string $type;
    public string $setKey;

    public function __construct(int $action, string $type, array $objects = [])
    {
        $this->action  = $action;
        $this->type    = $type;
        $this->objects = $objects;
        $this->status  = 0;
        $this->message = "";
    }
}