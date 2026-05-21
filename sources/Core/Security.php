<?php

namespace App\Core;

class Security{

    private const string CSRF_SESSION_NAME = "csrfses";
    private const string CSRF_INPUT_NAME = "csrfinp";

	private static function loadCsrf():void{
        if(empty(get_session(self::CSRF_SESSION_NAME))){
            set_session(self::CSRF_SESSION_NAME,bin2hex(random_bytes(32)));
        }
    }

    private static function getCsrf():string{
        return get_session(self::CSRF_SESSION_NAME);
    }

    public static function csrfField():void{
        self::loadCsrf();
        echo '<input type="hidden" 
            style="opacity:0;visibility:hidden;" id="csrf-field"
            name="'.self::CSRF_INPUT_NAME.'" 
            value="'.self::getCsrf().'">';
    }

    private static function validateCsrf():bool{
        // Try getting token from post input or custom header
        $input = input(self::CSRF_INPUT_NAME);
        if(empty($input)){
            $input = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? "";
        }

        $session = self::getCsrf();
        $is_valid = !empty($input) && !empty($session) && hash_equals($session,$input);
        
        // Only regenerate if it was a successful form submit, or keep it for better UX
        // For now, let's keep it strong by regenerating after use if it's not AJAX
        if($is_valid && empty($_SERVER['HTTP_X_CSRF_TOKEN'])){
             unset_session(self::CSRF_SESSION_NAME);
             self::loadCsrf();
        }
        
        return $is_valid;
    }

    public static function checkCsrf():void{
        $method = method();
        $mutating_methods = ["POST", "PUT", "PATCH", "DELETE"];

        if(in_array($method, $mutating_methods)){
            // Basic Origin/Referer check
            $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? "";
            $base_url = env("base_url");
            
            if(!empty($origin) && strpos($origin, $base_url) === false){
                Logging::record("error", "CSRF Check Failed: Origin mismatch. Origin: $origin", self::class);
                Routing::forbidden();
            }

            if(!self::validateCsrf()){
                Logging::record("error", "CSRF Check Failed: Token mismatch or missing.", self::class);
                Routing::forbidden();
            }
        }
    }
    
    public static function securityFormSubmit(string $btn_name, string $btn_value){
        return validate_submit_button($btn_name,$btn_value) && self::validateCsrf();
    }
}