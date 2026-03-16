#!/bin/bash

# Script pour vérifier la longueur des fichiers (limite 150 lignes)
# Utilise l'outil numberLine via Docker

TARGET_LINES=150
REDUCE_OUTPUT=true
if [ "$1" == "false" ]; then
    REDUCE_OUTPUT=false
fi

function run_number_line() {
    local dir=$1
    if [ "$REDUCE_OUTPUT" == "false" ]; then
        echo -e "\n--- Analyse de $dir (Cible: $TARGET_LINES lignes) ---"
    fi

    # Appel au script run.sh de numberLine
    # On capture la sortie pour vérifier s'il y a des fichiers au-dessus de la limite
    output=$(bash scripts/numberLine/run.sh "$TARGET_LINES" "$dir")

    if [ "$REDUCE_OUTPUT" == "false" ]; then
        echo "$output"
    fi

    # Vérification si des fichiers sont signalés dans les catégories "au-dessus"
    # L'outil affiche "- /data/..." pour les fichiers trouvés.
    # On cherche si la section "légèrement au-dessus" ou "fortement au-dessus" contient des fichiers (lignes commençant par "  -")
    # On extrait les parties après les titres des sections 2 et 3.

    above=$(echo "$output" | sed -n '/2. Fichiers légèrement au-dessus/,/3. Fichiers fortement au-dessus/p' | grep "  -")
    strongly_above=$(echo "$output" | sed -n '/3. Fichiers fortement au-dessus/,$p' | grep "  -")

    if [ -n "$above" ] || [ -n "$strongly_above" ]; then
        if [ "$REDUCE_OUTPUT" == "true" ]; then
             echo "Erreur : Certains fichiers dans $dir dépassent la limite de $TARGET_LINES lignes."
             [ -n "$above" ] && echo "Légèrement au-dessus :" && echo "$above"
             [ -n "$strongly_above" ] && echo "Fortement au-dessus :" && echo "$strongly_above"
        fi
        return 1
    fi

    return 0
}

# Analyse de src/ uniquement
run_number_line "src"
SRC_STATUS=$?

if [ $SRC_STATUS -ne 0 ]; then
    exit 1
fi

if [ "$REDUCE_OUTPUT" == "false" ]; then
    echo -e "\n--- Vérification du nombre de lignes terminée avec succès ---"
fi

exit 0
