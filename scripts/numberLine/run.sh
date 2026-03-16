#!/bin/bash

# Vérifier si l'image est construite (optionnel, on peut la construire à chaque fois ou laisser l'utilisateur le faire)
# docker build -t number-line scripts/numberLine > /dev/null

TARGET_LINES=$1
DIR_TO_ANALYZE=$2

if [ -z "$TARGET_LINES" ] || [ -z "$DIR_TO_ANALYZE" ]; then
    echo "Usage: $0 <target_lines> <directory>"
    exit 1
fi

# Obtenir le chemin absolu du dossier à analyser
ABS_DIR=$(realpath "$DIR_TO_ANALYZE")

# Exécuter le conteneur en montant le dossier cible dans /data
docker run --rm -v "$ABS_DIR":/data number-line /usr/src/app/numberLine "$TARGET_LINES" /data
