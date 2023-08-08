<?php
namespace server;
use server\DB;

class Host
{
    public const CHECKTOKEN = true;

    private const REMOTE_HOSTS = [
        "localhost" => "127.0.0.1",
        "android" => "188.242.226.185"
    ];

    private const REMOTE_HOST_TOKENS = [
        "localhost" =>  "kd5sjjkqrjke365jqrkj65kjejJJKD356JDjg89k3fjgf",
        "android" =>  "kd5sjjkqrjke365jqrkj65kjejJJKD356JDjg89k3fjgf"
    ];

    

    public static function Allowed(bool $checkToken = false) : bool 
    {
        /*
        // Check for banned session on every request
        session_start();
        $sessionId = session_id();

        if (isSessionBanned($sessionId)) {
            // Session is banned, take appropriate action
            // For example, you can deny access or redirect to a banned page
            header('HTTP/1.1 403 Forbidden');
            echo 'You are banned from accessing this page.';
            exit;
        }
        */

        $headers = getallheaders();
        $hostKey = null;

        foreach (self::REMOTE_HOSTS AS $key => $addr){
            if ($_SERVER['REMOTE_ADDR'] == trim($addr)){

                if (!$checkToken){
                    return true;
                }
                $hostKey = $key;
            }
        };
        if (!$checkToken)
        {
            return false;
        };

        if ($checkToken)
        {
            $token = null;
            $tokenToCheck = null;
            if (!isset($headers['Authorization'])){
                return false;
            }

            if (strpos($headers['Authorization'], 'TelesePost ') === 0) {
                // Token is present in the format 'Bearer <token>'
                $token = substr($headers['Authorization'], 11); // Extract the token portion
                // Now you can validate $token against your expected token
                // For example, compare it with the stored token or use a JWT library to verify its authenticity
            }

            if (isset($_GET['remoteservice'])){

                if ($hostKey === $_GET['remoteservice']){
                    if ($token === self::REMOTE_HOST_TOKENS[$hostKey]){

                        return true;
                    }
                }   
            }   
        }
        return false;
    }


    private static function isSessionBanned($sessionId) {
        // Establish database connection (replace with your database credentials)
        $host     = 'your_db_host';
        $username = 'your_db_username';
        $password = 'your_db_password';
        $dbname   = 'your_db_name';
    
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // Prepare and execute the database query
            $stmt = $conn->prepare('SELECT COUNT(*) FROM banned_sessions WHERE session_id = :sessionId');
            $stmt->bindParam(':sessionId', $sessionId);
            $stmt->execute();
            $count = $stmt->fetchColumn();
    
            // Close the database connection
            $conn = null;
    
            return ($count > 0);
        } catch (PDOException $e) {
            // Handle database connection error
            return false;
        }
    }
}