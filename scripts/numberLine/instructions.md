# Instructions pour l'application de comptage de lignes

## Objectif
Créer une application en C capable de parcourir un dossier, de compter le nombre de lignes de chaque fichier et 
de les classer selon leur proximité avec un nombre cible donné en paramètre.

## Utilisation
L'application doit être appelable via un script shell (ou directement via l'exécutable compilé) avec deux paramètres :
1. Le nombre de lignes cible (entier).
2. Le chemin du dossier à analyser.

Exemple d'appel :
`./numberLine 100 ./mon_dossier`

## Logique de traitement
Pour chaque fichier présent dans le dossier :
1. Parcourir le fichier et compter le nombre de lignes.
2. Comparer ce nombre avec la cible donnée.
3. Classer le fichier dans l'une des catégories suivantes :
   - **Taille approchante à 10% près** : Le nombre de lignes est compris entre -10% et +10% de la cible (excluant 0 à 10% au-dessus).
   - **Légèrement au-dessus (0 à 10%)** : Le nombre de lignes est entre la cible et la cible + 10%.
   - **Fortement au-dessus** : Le nombre de lignes est supérieur à la cible + 10%.

*Note : La description initiale mentionne "approchant la taille à 10% près", "ceux légèrement au-dessus 0 à 10%" 
et "enfin ceux fortement au-dessus". Il faudra clarifier si les 10% de proximité incluent aussi le "légèrement au-dessus" ou si ce sont des catégories distinctes.*
Réponse : ce sont les mêmes catégories

## Sortie attendue
À la fin du traitement, l'application doit afficher la liste des fichiers classés par catégories.
