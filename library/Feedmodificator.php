<?php

require_once __DIR__ . '/File.php';
require_once __DIR__ . '/Log.php';

class Feedmodificator {

	private $config;

	// Lade die Config aus einer Datei
	private function config($filename) {

		include($filename);
		$this->config = $config;

	}

	// Konstruktor
	public function __construct($configfile) {

		$this->config($configfile);

	}

	// Speichere den Feed
	private function save($xmlstring, $target, $feed) {

		// Verwende den Dateinamen aus der Config
		if (!is_dir(__DIR__ . '/../xml')) mkdir(__DIR__ . '/../xml');
		$filename = __DIR__ . '/../xml/' . $target['filename'];
		if (file_put_contents($filename, iconv("UTF-8", "UTF-8//IGNORE", $xmlstring))) {
			Log::info("Der modifizierte Feed wurde in die Datei '" . realpath($filename) . "' geschrieben");
		}

		// Lege ein Backup an
		if (!is_dir(__DIR__ . '/../xml/backups')) mkdir(__DIR__ . '/../xml/backups');
		$pathparts = pathinfo($filename);
		$backupFilename = __DIR__ . '/../xml/backups/' . $pathparts['filename'] . date("_Y-m-d_H-i-s.") . $pathparts['extension'];
		if (copy($filename, $backupFilename)) {
			Log::info("Ein Backup des modifizierten Feeds wurde in die Datei '" . realpath($backupFilename) . "' geschrieben");
		}

	}

	// Modifiziere den Feed
	private function modify($filename, $target, $feed) {

		// Initialisiere die Klasse für Modifikationen am XML
		$targetClass = isset($target['class']) ? new $target['class'] : new stdClass;

		$feedobj = simplexml_load_file($filename);
		if ($feedobj) {
			Log::info("Der Feed '" . realpath($filename) . "' wurde erfolgreich geparst");
		} else {
			return Log::error("Der Feed '" . realpath($filename) . "' konnte nicht geparst werden");
		}

		// Optionen für das Target
		$options = isset($target["options"]) ? $target["options"] : [];

		// Rufe onload callback auf, falls dieser vorhanden ist
		if (method_exists($targetClass, "onload")) {
			call_user_func_array([$targetClass, "onload"], [$feedobj, $options]);
			Log::info("Der Feed '" . realpath($filename) . "' wurde per onload-Funktion in Klasse '" . get_class($targetClass) . "' modifiziert");
		}

		// Speichere den Feed
		$this->save($feedobj->asXML(), $target, $feed);

	}

	// Importiere einen Feed
	private function import($feed) {

		// Prüfe, ob der Feed angesichts des Import-Intervalls neu verarbeitet werden muss
		if (File::mtime($feed['source']['url']) > time() - $this->config["interval"]) return;

		// Überspringe den Schritt, wenn das Laden des XML-Files fehlgeschlagen ist
		if (!File::load($feed['source']['url'])) return;

		// Modifiziere den Feed
		foreach ($feed['target'] as $target) {
			$this->modify(File::path($feed['source']['url']), $target, $feed);
		}

	}

	// Führe den Feedmodifikator aus
	public function execute() {
		
		Log::info("Starte import");

		// Importiere die Feeds
		array_map([$this, 'import'], $this->config['feed']);
		
		// Säubere die Ordner
		File::clean();

		Log::info("Import abgeschlossen");

	}
}