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

    public static function validateCsrf():bool{
        $input = post(self::CSRF_INPUT_NAME);
        $session = self::getCsrf();
        $is_valid = !empty($input) && !empty($session) && hash_equals($session,$input);
        unset_session(self::CSRF_SESSION_NAME);
        self::loadCsrf();
        return $is_valid;
    }
    
    public static function securityFormSubmit(string $btn_name, string $btn_value){
        return validate_submit_button($btn_name,$btn_value) && Security::validateCsrf();
    }
}