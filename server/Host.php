<?php
namespace server;

class Host
{
    public const CHECKTOKEN = true;

    private const REMOTE_HOSTS = [
        "127.0.0.1",
        "188.242.226.185"
    ];

    private const REMOTE_HOST_TOKENS = [
        "kd5sjjkqrjke365jqrkj65kjejJJKD356JDjg89k3fjgf"
    ];

    public static function Allowed(bool $checkToken = false) : bool 
    {
        // echo $_SERVER['REMOTE_ADDR']; - remote
        // echo $_SERVER['HTTP_HOST']; - this
        if ($checkToken)
        {
            $checked = false;
            if (isset($_GET['token'])){
                foreach (self::REMOTE_HOST_TOKENS AS $tok){
                    if ($tok == $_GET['token']){
                        $checked = true;
                        break;
                    }
                }
            }
            if (!$checked){
                return false;
            }
        }

        foreach (self::REMOTE_HOSTS AS $addr){
            if ($_SERVER['REMOTE_ADDR'] == trim($addr)){
                return true;
            }
        }
        return false;
    }
}