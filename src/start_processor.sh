#!/bin/bash

if [ -z "$1" ]; then
    echo "Aufruf: $0 <ip>"
    exit 1
fi

ip="$1"
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Ports zentral aus config/config.json lesen
if ! port_list=$(php -r '
    require $argv[1] . "/../vendor/autoload.php";
    $config = new Ais\Request\Config($argv[1] . "/config/config.json");
    foreach (array_keys($config->getSources()) as $port) {
        echo $port, PHP_EOL;
    }
' "$SCRIPT_DIR"); then
    echo "Fehler beim Lesen der Ports aus $SCRIPT_DIR/config/config.json"
    exit 1
fi

mapfile -t ports <<< "$port_list"

if [ -z "${ports[0]}" ]; then
    echo "Keine Ports in $SCRIPT_DIR/config/config.json konfiguriert (\"sources\")."
    exit 1
fi

for port in "${ports[@]}"; do

    # Prozess im Hintergrund starten
    php "$SCRIPT_DIR"/processor.php --ip "$ip" --port "$port" &

done

echo "Alle Prozesse gestartet. Ergebnis nach festgelegter Zeit"
