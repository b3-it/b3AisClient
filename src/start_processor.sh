#!/bin/bash


ip="172.30.11.225"
ports=(31935 31936 31937)
count=3
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

for ((i=0; i<$count; i++)); do

    port=${ports[$i]}

    # Prozess im Hintergrund starten
    php $SCRIPT_DIR/processor.php --ip $ip --port $port &

done

echo "Alle Prozesse gestartet. Ergebnis nach festgelegter Zeit"
