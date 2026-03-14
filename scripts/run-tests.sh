#!/bin/bash

# Script pour exécuter PHPUnit
# Si le premier paramètre est "true", on utilise --testdox pour un résumé propre.

if [ "$1" == "false" ]; then
    vendor/bin/phpunit --testdox --do-not-cache 2>&1 | grep -v "PHPUnit" | grep -v "Runtime" | grep -v "Configuration" | grep -v "\.\." | grep -v "Time:" | grep -v "Memory:"
else
    vendor/bin/phpunit
fi
