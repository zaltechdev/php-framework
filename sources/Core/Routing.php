<?php

namespace App\Core;

class Routing {
	
	private string $uri;
	private string $http_method;
	private string $base_url;
	private array $routes = [];

	private static function catchRouterError(string $message):void{
		Logging::record("error",$message,self::class);
	}
	
	public function __construct(){
		$this->uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH) ?? "/";
		$this->http_method = $_SERVER['REQUEST_METHOD'];
		$this->base_url = env("base_url");
	}

	
	private const VIEW_MAIN_PATH = __DIR__ . "/../views/main/";
	private const VIEW_ERRORS_PATH = __DIR__ . "/../views/errors/";
	
	public static function notFound():never{
		http_response_code(HTTP_NOT_FOUND);
		$notfound = self::VIEW_ERRORS_PATH . HTTP_NOT_FOUND . ".php";
		if(file_exists($notfound)){
			require_once $notfound;
			die();
		} 
		die("<center><h2>404 Not Found</h2></center>");
		
	}
	
	public static function methodNotAllowed():never{
		http_response_code(HTTP_METHOD_NOT_ALLOWED);
		$method_error = self::VIEW_ERRORS_PATH . HTTP_METHOD_NOT_ALLOWED . ".php";
		if(file_exists($method_error)){
			require_once $method_error;
			die();
		} 
		die("<center><h2>405 Method Not Allowed</h2></center>");
		
	}
	
	public static function internalError():never{
		http_response_code(HTTP_INTERNAL_ERROR);
		$internal_error = self::VIEW_ERRORS_PATH . HTTP_INTERNAL_ERROR . ".php";
		if(file_exists($internal_error)){
			require_once $internal_error;
			die();
		} 
		die("<center><h2>500 Internal Server Error</h2></center>");
		
	}
	
	public static function unavailable():never{
		http_response_code(HTTP_SERVICE_UNAVAILABLE);
		$unavailable = self::VIEW_ERRORS_PATH . HTTP_SERVICE_UNAVAILABLE . ".php";
		if(file_exists($unavailable)){
			require_once $unavailable;
			die();
		} 
		die("<center><h2>503 Service Unavailable</h2></center>");
	}
	
	public static function forbidden():never{
		http_response_code(HTTP_FORBIDDEN);
		$forbidden = self::VIEW_ERRORS_PATH . HTTP_FORBIDDEN . ".php";
		if(file_exists($forbidden)){
			require_once $forbidden;
			die();
		} 
		die("<center><h2>403 Forbidden</h2></center>");
	}
	
	public static function badRequest():never{
		http_response_code(HTTP_BAD_REQUEST);
		$bad_request = self::VIEW_ERRORS_PATH . HTTP_BAD_REQUEST . ".php";
		if(file_exists($bad_request)){
			require_once $bad_request;
			die();
		} 
		die("<center><h2>400 Bad Request</h2></center>");
	}
	
	public static function unauthorized():never{
		http_response_code(HTTP_UNAUTHORIZED);
		$unauthorized = self::VIEW_ERRORS_PATH . HTTP_UNAUTHORIZED . ".php";
		if(file_exists($unauthorized)){
			require_once $unauthorized;
			die();
		} 
		die("<center><h2>401 Unauthorized</h2></center>");
	}

	private function buildRoute(string $http_method, string $path, array | callable $controller, array | callable $middleware = []):void{
		if(hash_equals($http_method,$this->http_method)){
			$this->routes[] = [
				"path" => $path,
				"controller" => $controller,
				"middleware" => $middleware
			];
		}
	}

	public function get(string $path, array $controller):void{
		$this->buildRoute("GET",$path,$controller);
	}
	
	public function post(string $path, array $controller):void{
		$this->buildRoute("POST",$path,$controller);
	}

	private static function headers(){
		header_remove("X-Powered-By");
	}
	
	public function run():void{
		self::headers();

		foreach($this->routes as $route){
			if(hash_equals($route['path'],$this->uri)){

				[$controller_class,$controller_method] = [$route['controller'][0] ?? "",$route['controller'][1] ?? ""];
				if(!class_exists($controller_class) || !method_exists($controller_class,$controller_method)){
					self::catchRouterError("Class controller or method controller does not exist!");
					self::internalError();
				}					
				$controller = new $controller_class();
				$return = (object) $controller->$controller_method();

				if(isset($return->redirect)){
					header("location:" . url($return->redirect));
					exit;
				}
				else if(isset($return->view)){
					header("Content-Type:text/html");
					http_response_code($return->view['code'] ?? HTTP_OK);
					
					$view = self::VIEW_MAIN_PATH . $return->view['name'] . ".php";
					if(!file_exists($view)){
						self::catchRouterError("View $view does not exist!");
						self::internalError();
					}
					
					extract($return->view['data']);
					require_once $view; 
					exit;
				}
				else if(isset($return->file)){					
					$uploaded_file = Database::UPLOAD_DIR . $return->file;
					if(file_exists($uploaded_file)){

						$mime_type = mime_content_type($uploaded_file);
						if(!$mime_type){
							self::catchRouterError("Failed to get file mime type!");
							self::internalError();
						}

						header("Content-Type:$mime_type");
						if(!readfile($uploaded_file)){
							header("Content-Type:text/html");
							self::catchRouterError("Failed to read file!");
							self::internalError();
						}
					}
					exit;
				}
			}
		}
		
		self::notFound();
	}
}
