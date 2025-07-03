<?php

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
