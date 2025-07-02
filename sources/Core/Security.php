<?php

namespace App\Core;

class Security{

    private const string CSRF_SESSION_NAME = "csrfses";
    private const string CSRF_INPUT_NAME = "csrfinp";
	
	public function __construct(){		
        $this->loadCsrf();
	}

	private function loadCsrf():void{
        if(empty(get_session(self::CSRF_SESSION_NAME))){
            set_session(self::CSRF_SESSION_NAME,bin2hex(random_bytes(32)));
        }
    }

    private function getCsrf():string{
        return get_session(self::CSRF_SESSION_NAME);
    }

    public function csrfField():void{
        echo '<input type="hidden" 
            style="opacity:0;visibility:hidden;" id="csrf-field"
            name="'.self::CSRF_INPUT_NAME.'" 
            value="'.$this->getCsrf().'">';
    }

    public function validateCsrf():bool{
        $input = post(self::CSRF_INPUT_NAME);
        $session = $this->getCsrf();
        $is_valid = !empty($input) && !empty($session) && hash_equals($session,$input);
        unset_session(self::CSRF_SESSION_NAME);
        $this->loadCsrf();
        return $is_valid;
    }
}