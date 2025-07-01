<?php

function css(string $css_name){
	return "/assets/css/$css_name.css";
}

function js(string $js_name){
	return "/assets/js/$js_name.js";
}

function csrf_field(){
	App\Core\Security::csrfField();
}

function generate_6digit_token(){
	$pool = "1234567890";
	$token = "";
	for($i = 0; $i < 6; $i++){
		$token .= $pool[rand(0,strlen($pool) - 1)];
	}
	return $token;
}

function generate_uuid4() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function validate_submit_button(string $button_name, string $button_value){
    return hash_equals($button_value,post($button_name));
}

function global_assets(array $css, array $js){
	if(!empty($css)){
		foreach($css as $style){
			echo '<link rel="stylesheet" href="'.css($style).'"></a>';
		}		
	}
	
	if(!empty($js)){
		foreach($js as $script){
			echo '<script src="'.js($script).'"></script>';
		}		
	}
}

function get_app_name(){
	return App\Core\Environment::env("app_name");
}


function get_user_device_info(){

	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$device = "";

    $os_types = [
        "Android"       => "/android/i",
        "iOS"           => "/iphone|ipad|ipod/i",
        "Windows Phone" => "/windows phone/i",
        "Windows 11"    => "/windows nt 10.0/i",
        "Windows 10"    => "/windows nt 10/i",
        "Windows 8.1"   => "/windows nt 6.3/i",
        "Windows 8"     => "/windows nt 6.2/i",
        "Windows 7"     => "/windows nt 6.1/i",
        "Windows Vista" => "/windows nt 6.0/i",
        "Windows XP"    => "/windows nt 5.1|windows xp/i",
        "Mac OS X"      => "/macintosh|mac os x/i",
        "Linux"         => "/linux/i",
        "BlackBerry"    => "/blackberry/i",
        "Chrome OS"     => "/cros/i",
    ];

	$lower_case_user_agent = strtolower($user_agent);
	if (preg_match("/tablet|ipad/", $lower_case_user_agent)) {
		$device .= 'Tablet - ';
	} 
	else if (preg_match("/mobile|iphone|ipod|android|blackberry|phone|windows phone/", $lower_case_user_agent)) {
		$device .= "Mobile - ";
	}
	else {
		$device .= "Desktop - ";
	}

	$type_os = "Unknown OS";

	foreach($os_types as $type => $regex){
		if(preg_match($regex,$user_agent)){
			$type_os = $type;
		}
	}

	$device .= $type_os;
	
	return $device;
}

function url(string $path){
	return rtrim(App\Core\Environment::env("base_url"),"/") . "/" . ltrim($path,"/");
}