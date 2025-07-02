<?php

namespace App\Core;

class Session{

    private const string DEFAULT_SESSION_ID_NAME = "sessid";
    private static $status;
    
    public function __construct(){
        try{
            self::$status = session_status();
            if(self::$status === PHP_SESSION_DISABLED){
                throw new \Exception("PHP session currently disabled!");
            }
            else if(self::$status === PHP_SESSION_NONE){
                if(!session_set_save_handler(new SessionDriver(),true)){
                    throw new \Exception("Failed to set session handler!");
                }
                
                if(!session_name(self::DEFAULT_SESSION_ID_NAME)){
                    throw new \Exception("Failed to set PHP session id name!");                
                }
                
                if(!session_start()){
                    throw new \Exception("Failed to start session!");
                }
            }
        }
        catch(\Exception $error){
            Logging::record("error",$error,self::class);
            Routing::internalError();
        }
    }

    public static function regenerateSessionId(){
        try{
            if(self::$status === PHP_SESSION_ACTIVE){
                if(!session_regenerate_id(true)){
                    throw new \Exception("Failed to regenerate session id!");
                }
            }
        }
        catch(\Exception $error){
            Logging::record("error",$error,self::class);
        }
    }
}