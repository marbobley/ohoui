#!/bin/bash

# Script pour exécuter toutes les vérifications Mago : lint, format, analyze et guard
# Si le premier paramètre est "true", on réduit l'output.

REDUCE_OUTPUT=false
if [ "$1" == "true" ]; then
    REDUCE_OUTPUT=true
fi

function run_mago() {
    local cmd=$1
    local extra_args=""
    if [ "$REDUCE_OUTPUT" == "true" ]; then
        if [ "$cmd" != "format" ]; then
            extra_args="--reporting-format short"
        fi
    else
        echo -e "\n--- Running Mago ${cmd^} ---"
    fi
    vendor/bin/mago $cmd $extra_args 2>&1 | grep -v "INFO"
    local status=${PIPESTATUS[0]}
    if [ $status -ne 0 ]; then
        exit $status
    fi
}

run_mago "lint"
run_mago "format"
run_mago "analyze"
run_mago "guard"

if [ "$REDUCE_OUTPUT" != "true" ]; then
    echo -e "\n--- Mago checks completed successfully ---"
fi
