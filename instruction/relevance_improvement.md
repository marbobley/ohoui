# Amélioration de la Pertinence : Boosting des Titres

Cette étape a consisté à améliorer le classement des résultats de recherche en donnant plus de poids aux mots-clés trouvés dans les titres des documents.

## Changements apportés

### 1. Infrastructure (`SolrClientService`)
- Modification de la construction de la requête Solr par défaut.
- Ajout d'une recherche pondérée : `title:"keyword"^2.0 OR content:"keyword"`.
- Cela signifie qu'une correspondance dans le titre compte deux fois plus qu'une correspondance dans le contenu.

### 2. Tests
- **Test Unitaire (`SolrClientServiceTest`)** : Mise à jour pour vérifier que la chaîne de requête envoyée à Solr contient bien les opérateurs de boost.
- **Test d'Intégration (`SearchUseCaseIntegrationTest`)** : Ajout de `testSearchRankingBoostsTitle` qui :
    1. Indexe un document avec le mot-clé dans le contenu.
    2. Indexe un document avec le même mot-clé dans le titre.
    3. Vérifie que le document avec le titre correspondant arrive en première position des résultats.

## Pourquoi cette amélioration ?
Dans un moteur de recherche, le titre d'une page est généralement l'indicateur le plus précis de son sujet. En boostant les titres, nous augmentons considérablement la pertinence des premiers résultats affichés à l'utilisateur.

## Prochaines étapes suggérées
- Ajouter un boost sur certains domaines (ex: domaines gouvernementaux ou éducatifs).
- Passer à l'implémentation de **Facettes** (filtres par langue ou domaine) pour permettre une navigation plus précise.
