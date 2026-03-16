#!/bin/bash

# Script global de vérification (Mago + Tests)
# Si le premier paramètre est "true", les deux scripts passent en mode réduit.

MODE=$1
[ "$MODE" == "true" ] && REDUCE="true" || REDUCE="false"

if [ "$REDUCE" == "false" ]; then
    echo "========================================"
    echo "      MAGO CHECKS                      "
    echo "========================================"
fi

bash scripts/mago-check.sh $MODE
MAGO_STATUS=$?

if [ "$REDUCE" == "false" ]; then
    echo -e "\n========================================"
    echo "      PHPUNIT TESTS                    "
    echo "========================================"
fi

bash scripts/run-tests.sh $MODE
TESTS_STATUS=$?

if [ "$REDUCE" == "false" ]; then
    echo -e "\n========================================"
    echo "      NUMBER LINE CHECK                "
    echo "========================================"
fi

bash scripts/number-line-check.sh $MODE
NL_STATUS=$?

if [ $MAGO_STATUS -ne 0 ] || [ $TESTS_STATUS -ne 0 ] || [ $NL_STATUS -ne 0 ]; then
    [ "$REDUCE" == "false" ] && echo -e "\n[FAIL] Certains contrôles ont échoué."
    exit 1
fi

[ "$REDUCE" == "false" ] && echo -e "\n[SUCCESS] Tout est au vert !"
exit 0
