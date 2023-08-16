<?php
// here will be placed all configs
if (!defined('MICROSERVICE'))
{ die('Cannot load config file.'); };

class Config {
    public $apiKey = 'your_api_key';
    public $baseUrl = 'https://api.example.com';

    public string $dbname = "microservicer";
    public string $host = "localhost";
    public string $dbuser = "root";
    public string $dbpass = "";

    public bool $check_token = true;
    public $remote_hosts = [
        "localhost" => "127.0.0.1",
        "android" => "188.242.226.185"
    ];

    public $remote_host_tokens = [
        "localhost" =>  "kd5sjjkqrjke365jqrkj65kjejJJKD356JDjg89k3fjgf",
        "android" =>  "kd5sjjkqrjke365jqrkj65kjejJJKD356JDjg89k3fjgf"
    ];
};




?>