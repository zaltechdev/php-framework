<?php

function method():string{
	return $_SERVER['REQUEST_METHOD'];
}

function input(string $key, $default = null){
	$input_data = null;

	if($input_data === null){
		$method = method();
		if($method === "GET"){
			$input_data = $_GET;
		}
		else if($method === "POST"){
			$input_data = $_POST;
		}
		else{
			// For PUT, PATCH, DELETE
			$raw_input = file_get_contents("php://input");
			
			// Try to parse JSON first
			$json_data = json_decode($raw_input, true);
			if(json_last_error() === JSON_ERROR_NONE){
				$input_data = $json_data;
			}
			else{
				// Fallback to query string format (x-www-form-urlencoded)
				parse_str($raw_input, $parsed_data);
				$input_data = $parsed_data;
			}
		}
	}

	return htmlspecialchars($input_data[$key] ?? $default);
}

function post(string $key):string{
	return htmlspecialchars($_POST[$key] ?? "");
}

function get(string $key):string{
	return htmlspecialchars($_GET[$key] ?? "");
}

function posts(array $keys):object{
	$data = [];
	foreach($keys as $key){
		$data[$key] = post($key);
	}
	
	return (object) $data;
}

function post_array(string $key){
	return (object) [$key => array_map("htmlspecialchars",$_POST[$key] ?? [])];
}

function gets(array $keys):object{
	$data = [];
	foreach($keys as $key){
		$data[$key] = get($key);
	}
	
	return (object) $data;
}

function set_session(string $key, mixed $value):void{
	$_SESSION[$key] = $value;
}

function unset_session(string $key):void{
	unset($_SESSION[$key]);
}

function get_session(string $key):mixed{
	return $_SESSION[$key] ?? "";
}

function set_cookie(string $key, mixed $value, int | array | bool $options = []):void{
	setcookie($key,$value,$options);
}

function unset_cookie(string $key):void{
	setcookie($key,"",0);
}

function get_cookie(string $key):mixed{
	return $_COOKIE[$key] ?? "";
}

