<?php

require __DIR__ . '/library/Feedmodificator.php';
	
// Lade alle Klassen im classes-Ordner
foreach (glob( __DIR__ . '/classes/*.php') as $filename) {
	require $filename;
}

// Lade die Config in den Feedmodificator
$fm = new Feedmodificator(__DIR__ . '/config/config.php');

// Führe den Feedmodificator aus
$fm->execute();