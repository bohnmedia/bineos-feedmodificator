<?php

class Log {
		
	static private function write($text) {
		
		if (!is_dir(__DIR__ . '/../logs')) mkdir(__DIR__ . '/../logs');
		$log = date("Y-d-m H:i:s ") . $text . PHP_EOL;
		file_put_contents(__DIR__ . '/../logs/log_' . date("j.n.Y") . '.log', $log, FILE_APPEND);
		
	}

	static public function info($text) {
		
		self::write("INFO - " . $text);
		
	}

	static public function error($text) {
		
		self::write("ERROR - " . $text);
		
	}

}