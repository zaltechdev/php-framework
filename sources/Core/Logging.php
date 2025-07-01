<?php

namespace App\Core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Logging{
	
    private function turnOffErrorDisplay(){
        error_reporting(0);
        ini_set("display_errors",0);
    }

    private function errorReportingHandler(){
		$mode = Environment::env("app_mode");
		if($mode === APP_MODE_PROD){
			$this->turnOffErrorDisplay();
		}
		else if($mode === APP_MODE_MAIN){
			$this->turnOffErrorDisplay();
			Routing::unavailable();
		}
    }

    private function timezoneSetting(){
        $timezone = Environment::env("app_timezone");
        if(!in_array($timezone,timezone_identifiers_list())){
            throw new \Exception("Invalid timezone setting value!");
        }
        date_default_timezone_set($timezone);
    }

    public function __construct(){
        try{
            $this->errorReportingHandler();
            $this->timezoneSetting();
        }
        catch(\Exception $error){
            self::record("error",$error,self::class);
            Routing::internalError();
        }
    }

    private static array $loggers = [];

    private static function getLogger(string $trace): Logger {
        $filename_with_datetime_format = (new \DateTime("now", new \DateTimeZone(DEFAULT_TIMEZONE)))->format("d-m-Y");

        if (!isset(self::$loggers[$trace])) {
            $logger = new Logger($trace);
            $logger->setTimezone(new \DateTimeZone(DEFAULT_TIMEZONE));
            $logger->pushHandler(new StreamHandler(LOG_DIR . "$filename_with_datetime_format.log"));
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