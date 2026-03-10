#!/bin/bash

# Script pour exécuter toutes les vérifications Mago : lint, format, analyze et guard

echo "--- Running Mago Lint ---"
vendor/bin/mago lint || exit 1

echo -e "\n--- Running Mago Format ---"
vendor/bin/mago format || exit 1

echo -e "\n--- Running Mago Analyze ---"
vendor/bin/mago analyze || exit 1

echo -e "\n--- Running Mago Guard ---"
vendor/bin/mago guard || exit 1

echo -e "\n--- Mago checks completed successfully ---"
