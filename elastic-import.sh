#!/bin/bash

# Parameter einlesen
while getopts u:d:s: flag
do
    case "${flag}" in
        u) USERNAME=${OPTARG};;
        d) DIRECTORY=${OPTARG};;
        s) SERVER=${OPTARG};;
    esac
done

# Prüfen, ob alle Parameter gesetzt sind
if [ -z "$USERNAME" ] || [ -z "$DIRECTORY" ] || [ -z "$SERVER" ]; then
    echo "Fehlende Parameter! Nutzung: $0 -d <source_directory> -u <username_target_server> -s <target_server>"
    exit 1
fi

# Prüfen, ob das angegebene Verzeichnis existiert
if [ ! -d "$DIRECTORY" ]; then
    echo "Verzeichnis $DIRECTORY existiert nicht! Abbruch."
    exit 1
fi

# Zielverzeichnis auf dem Server definieren
TARGET_PATH="/var/lucascranach/cranach-docker/importer/files/"

# Passwort-Eingabe vom Benutzer
echo -n "Passwort für $USERNAME@$SERVER: "
stty -echo
read PASSWORD
stty echo
echo ""  # Um den Cursor auf die nächste Zeile zu verschieben

# Lösche alle Dateien im Zielverzeichnis auf dem Remote-Server
echo "Lösche Dateien im Verzeichnis $TARGET_PATH auf $SERVER"
sshpass -p "$PASSWORD" ssh "$USERNAME@$SERVER" "rm -rf $TARGET_PATH/*"

# Dateien via SCP kopieren
echo "Kopiere Dateien von $DIRECTORY nach $SERVER:$TARGET_PATH"
sshpass -p "$PASSWORD" scp -r "$DIRECTORY"/* "$USERNAME@$SERVER:$TARGET_PATH"

# Nach dem Kopieren den Befehl auf dem Zielserver ausführen
echo "Führe Import-Befehl auf $SERVER aus"
sshpass -p "$PASSWORD" ssh "$USERNAME@$SERVER" "cd /var/lucascranach/cranach-docker/importer && make importesindices"

echo "Fertig!"
