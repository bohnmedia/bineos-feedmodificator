# FeedModificator für Bineos

Dieses PHP-Script dient zur Modifikation von Feeds, bevor diese in Bineos importiert werden.

Für den Import muss eine Config-Datei angelegt werden.

```php
// config/config.php

// Import-Intervall in Sekunden
$config['interval'] = 60 * 30;

// Array für individuelle Import-Logik
$config['feed'] = [];

// Kunde 1
// Ein Eingangsfeed, ein Ausgangsfeed
$config['feed'][] = [
    'source' => ['url' => '[URL DES QUELLFEEDS]'],
    'target' => [
        ['class' => [INDIVIDUELLE KLASSE FÜR KUNDEN]::class, 'filename' => '[DATEINAME DER ZIELDATEI]']
    ]
];

// Kunde 2
// Ein Eingangsfeed, zwei Ausgangsfeeds
$config['feed'][] = [
    'source' => ['url' => '[URL DES QUELLFEEDS]'],
    'target' => [
        ['class' => [INDIVIDUELLE KLASSE FÜR KUNDEN]::class, 'filename' => '[DATEINAME DER ZIELDATEI]'],
        ['class' => [INDIVIDUELLE KLASSE FÜR KUNDEN]::class, 'filename' => '[DATEINAME DER ZIELDATEI]', 'options' => ['bineos_referrer' => 'reco']]
    ]
];

// ...
```

Für jeden Kunden wird eine eigene Klasse angelegt. Wird diese im Unterordner "classes" abgelegt, so wird diese automatisch geladen. Um den Feed zu Verarbeiten, muss die Klasse die Funktion "onload" beinhalten.

Diese nimmt zum einen den geparsten Feed, sowie das in der Config definierte Array "options" entgegen.

```php
// classes/Kunde.php

class Kunde {

    // Funktion zur Weiterverarbeitung des geparsten XML-Feeds
    public function onload($feedobj, $options) {

        // Modifiziere die Items
        foreach ($feedobj->channel->item as $item) {

            // Modifiziere den <item>-Tag

        }

    }

}
```