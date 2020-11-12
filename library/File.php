<?php

require_once __DIR__ . '/Log.php';

class File {
	
	// Erhalte einen Zielpfad anhand einer URL
	static public function path($url) {

		// Prüfe die URL
		if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
			Log::error("URL-Validierung für '" . $url . "' fehlgeschlagen");
			die($url . ' ist keine valide URL');
		}

		// Gib den Pfad zurück
		return __DIR__ . "/../xml/downloads/" . explode("//", $url)[1];

	}
	
	// Prüfe, wann zuletzt eine Datei von einer URL geladen wurde
	static public function mtime($url) {
		
		// Pfad zur Original-XML
		$origFilePath = self::path($url);
		
		// Gib mtime zurück, wenn die Datei existiert, andernfalls FALSE
		return is_file($origFilePath) ? filemtime($origFilePath) : -1;
		
	}
	
	// Speichert eine Datei lokal
	static public function load($url, $backup=true) {

		// Pfad zur Original-XML
		$origFilePath = self::path($url);
		
		// Erzeuge Ordner für das XML-File
		if (!is_dir(dirname($origFilePath))) mkdir(dirname($origFilePath), 0777, true);

		// Öffne eine Datei zum Schreiben in die Original-XML
		$fp = fopen($origFilePath, 'wb');
		
		// Lade die Datei in den Originalpfad
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		$cr = curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		
		// Ist das Laden der Datei fehlgeschlagen, lösche diese und gib FALSE zurück
		if (!$cr) {
			Log::error("Laden des Feeds '" . $url . "' fehlgeschlagen");
			if (is_file($origFilePath)) unlink($origFilePath);
			return false;
		} else {
			Log::info("Laden des Feeds '" . $url . "' in Datei '" . realpath($origFilePath) . "' erfolgreich");
		}
		
		// Erzeuge ein Backup der Datei
		if ($backup) {
			$pathparts = pathinfo($origFilePath);
			$backupFilePath = $pathparts['dirname'] . '/' . $pathparts['filename'] . date("_Y-m-d_H-i-s.") . $pathparts['extension'];
			if (copy($origFilePath,$backupFilePath)) {
				Log::info("Backup in Datei '" . realpath($backupFilePath) . "' erfolgreich");
			} else {
				Log::error("Backup in Datei '" . $backupFilePath . "' fehlgeschlagen");
			}
		}
		
		// Gib die Datei zurück
		return true;

	}
	
	// Säubere die Ordner
	static public function clean($path='', $startfolder=true) {
				
		// Wähle die zu bereinigenden Ordner
		if (!$path) {
			self::clean(__DIR__ . '/../xml/downloads');
			self::clean(__DIR__ . '/../xml/backups');
			self::clean(__DIR__ . '/../logs');
			Log::info("Ordner für Downloads, Backups und Logs wurden aufgeräumt");
			return;
		}

		// Das Maximalalter einer Datei beträgt 14 Tage
		$minmtime = time() - 60 * 60 * 24 * 14;

		// Brich ab, wenn der Ordner nicht existiert
		if (!is_dir($path)) return;

		// Gehe rekursiv durch die Ordner
		foreach ($dir = new DirectoryIterator($path) as $fileinfo) {
			
			// Falls es sich um einen Ordner handelt, rufe die clean-Funktion auf
			if ($fileinfo->isDir() && !$fileinfo->isDot()) {
				self::clean($fileinfo->getPathname(), false);
			}
			
			// Wenn es sich um eine Datei handelt, die älter als 14 Tage ist, lösche diese
			if ($fileinfo->isFile() && $fileinfo->getMTime() < $minmtime) {
				unlink($fileinfo->getPathname());
			}

		}
		
		// Brich das Script an, wenn es sich um den Hauptordner handelt
		if ($startfolder) return;
		
		// Brich das Script ab, wenn der Ordner nicht leer ist
		foreach ($dir = new DirectoryIterator($path) as $fileinfo) {
			if (!$fileinfo->isDot()) return;
		}
		
		// Lösche den Ordner
		rmdir($path);
		
	}
	
}