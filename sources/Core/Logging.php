<?php

namespace App\Core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Logging{

    private static string $timezone;
    private static bool $is_timezone_true = false;
	
    private static function turnOffErrorDisplay(){
        error_reporting(0);
        ini_set("display_errors",0);
    }

    
    private const string APP_MODE_PROD = "production";
    private const string APP_MODE_MAIN = "maintenance";

    private static function errorReportingHandler(){
		$mode = env("app_mode");
		if($mode === self::APP_MODE_PROD){
			self::turnOffErrorDisplay();
		}
		else if($mode === self::APP_MODE_MAIN){
			self::turnOffErrorDisplay();
			Routing::unavailable();
		}
    }

    private static function timezoneSetting(){
        self::$timezone = env("app_timezone");
        if(!in_array(self::$timezone,timezone_identifiers_list())){
            throw new \Exception("Invalid timezone setting value!");
        }
        date_default_timezone_set(self::$timezone);
        self::$is_timezone_true = true;
    }

    public function __construct(){
        try{
            self::errorReportingHandler();
            self::timezoneSetting();
        }
        catch(\Exception $error){
            self::record("error",$error,self::class);
            Routing::internalError();
        }
    }

    private static array $loggers = [];
    private const string DEFAULT_TIMEZONE = "UTC";
    private const string LOG_DIR = __DIR__ . "/../../storages/logs/";

    private static function getLogger(string $trace): Logger {
        $timezone_log = !self::$is_timezone_true ? self::DEFAULT_TIMEZONE : self::$timezone;
        $filename_with_datetime_format = (new \DateTime("now", new \DateTimeZone($timezone_log)))->format("d-m-Y");

        if (!isset(self::$loggers[$trace])) {
            $logger = new Logger($trace);
            $logger->setTimezone(new \DateTimeZone($timezone_log));
            $logger->pushHandler(new StreamHandler(self::LOG_DIR . "$filename_with_datetime_format.log"));
            self::$loggers[$trace] = $logger;
        }

        return self::$loggers[$trace];
    }

    public static function record(string $level, string|\Throwable $message, string $trace) {
        $logger = self::getLogger($trace);
        $msg = is_string($message) ? $message : $message->getMessage();

        if (method_exists($logger, $level)) {
            $logger->$level($msg);
        } 
        else {
            $logger->debug($msg);
        }
    }
}