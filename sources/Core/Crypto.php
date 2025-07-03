<?php

namespace App\Core;

use MiladRahimi\PhpCrypt\Exceptions\InvalidKeyException;
use MiladRahimi\PhpCrypt\Exceptions\MethodNotSupportedException;
use MiladRahimi\PhpCrypt\Symmetric;

class Crypto{
    
    private static $symmetric;
	
	private function catchCryptoError(string $message){
        Logging::record("error",$message,self::class);
		Routing::internalError();
	}

    public function __construct(){
        if(self::$symmetric === null){
            $algo = env("encrypt_algo_method");
            $key = env("encrypt_keyphrase");
    
            try{
                $symmetric = new Symmetric();
                $symmetric->setMethod($algo);
    
                if(openssl_cipher_key_length($algo) !== strlen($key)){
                    throw new InvalidKeyException("Invalid encryption keyphrase. Character must equal with algorithm method!");
                }
    
                $symmetric->setKey($key);
            }
            catch(InvalidKeyException $error){
                $this->catchCryptoError($error);
            }
            catch(MethodNotSupportedException $error){
                $this->catchCryptoError($error);
            }
        }
                
        self::$symmetric = $symmetric;
    }
    
    public static function encrypt(string $plain_string){
        return base64_encode(self::$symmetric->encrypt($plain_string));
    } 

    public static function decrypt(string $encrypted_string){
        return self::$symmetric->decrypt(base64_decode($encrypted_string));
    } 

    public static function hashing(string $plain_string){
        return base64_encode(password_hash($plain_string,PASSWORD_BCRYPT));
    }
    
    public static function verifying(string $plain_string, string $hashed_string){
        return password_verify($plain_string,base64_decode($hashed_string));
    }
}