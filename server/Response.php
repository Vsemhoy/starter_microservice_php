<?php
namespace server;

class Response
{
    public int    $status;
    public string $message;
    public array  $objects;
    public int    $user;
    public array  $results;

    public function __construct()
    {
        $this->objects = [];
        $this->status  = 0;
        $this->message = "";
        $this->results = [];
        $this->user = 0;
    }
}