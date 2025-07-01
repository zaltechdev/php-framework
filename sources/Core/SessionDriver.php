<?php

namespace App\Core;

use SessionHandlerInterface;

class SessionDriver implements SessionHandlerInterface{

	private static ?\PDO $connection = null;
	private const string DEFAULT_SESSION_DB_FILENAME = "session.db";

	private const string SESSION_CREATE_TABLE_QUERY =
		"CREATE TABLE IF NOT EXISTS session_store (
			id TEXT PRIMARY KEY NOT NULL,
			access INTEGER NOT NULL,
			data TEXT,
			device TEXT
		)";

	private const string SESSION_READ_QUERY     = "SELECT data FROM session_store WHERE id = ?";
	private const string SESSION_WRITE_QUERY    = "INSERT OR REPLACE INTO session_store (id, access, data, device) VALUES (?, ?, ?, ?)";
	private const string SESSION_DESTROY_QUERY  = "DELETE FROM session_store WHERE id = ?";
	private const string SESSION_GC_QUERY       = "DELETE FROM session_store WHERE access < ?";

	public function __construct() {
		try {
			$session_db_file = SESSION_DB_DIR . self::DEFAULT_SESSION_DB_FILENAME;

			if (!file_exists($session_db_file)) {
				if (!file_put_contents($session_db_file, "")) {
					throw new \Exception("Cannot create session DB at: $session_db_file");
				}
			}

			$pdo = self::getConnection($session_db_file);
			$pdo->exec(statement: self::SESSION_CREATE_TABLE_QUERY);
		}
		catch (\Throwable $error) {
			Logging::record("error", "Failed to initialize session DB: $error", self::class);
			Routing::internalError();
		}
	}
	
	private static function getConnection(string $dbPath): \PDO {
		if (self::$connection === null) {
			self::$connection = new \PDO("sqlite:$dbPath");
			self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			self::$connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		}
		return self::$connection;
	}

	public function open(string $savePath, $sessionName):bool{
		return true;
	}

	public function close():bool{
		return true;
	}

	public function read(string $id):string{
		try{
			$stmt = self::$connection->prepare(self::SESSION_READ_QUERY);
			$stmt->execute([$id]);
			$result = $stmt->fetchAll();
			return $result[0]['data'] ?? "";
		}
		catch(\PDOException $error){
			Logging::record("error","Failed to read session! Reason : $error",self::class);
			return "";
		}
	}

	public function write(string $id, string $data):bool{
		try{
			$stmt = self::$connection->prepare(self::SESSION_WRITE_QUERY);
			$stmt->execute([$id,time(),$data,get_user_device_info()]);
			return true;
		}
		catch(\PDOException $error){
			Logging::record("error","Failed to write a session! Reason : $error",self::class);
			return false;
		}		
	}

	public function destroy(string $id):bool{
		try{
			$stmt = self::$connection->prepare(self::SESSION_DESTROY_QUERY);
			$stmt->execute([$id]);
			return true;
		}
		catch(\PDOException $error){
			Logging::record("error","Failed to destroy a session! Reason : $error",self::class);
			return false;
		}				
	}

	public function gc(int $maxlifetime = 1440):int|false{
		try{
			$stmt = self::$connection->prepare(self::SESSION_GC_QUERY);
			$stmt->execute([time() - $maxlifetime]);
			return $stmt->rowCount();
		}
		catch(\PDOException $error){
			Logging::record("error","Failed to exec session garbage collector! Reason : $error",self::class);
			return false;
		}
	}
}