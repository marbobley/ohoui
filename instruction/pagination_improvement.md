# Amélioration : Pagination et Gestion des Résultats

Ce document détaille la prochaine étape d'amélioration pour le moteur de recherche **Ohoui**. 
Actuellement, le système retourne un nombre fixe de résultats (10 par défaut) sans possibilité de navigation pour l'utilisateur.

## Objectif
Mettre en œuvre une pagination robuste et afficher le nombre total de résultats trouvés pour améliorer l'expérience utilisateur et la performance.

---

## Plan d'action détaillé

### 1. Évolution des Interfaces et Modèles (Domaine)
Il est nécessaire de structurer la réponse de recherche pour inclure des métadonnées.

- **Création de `SearchResult`** : Un nouvel objet de domaine dans `src/Domain/Model/SearchResult.php`.
    ```php
    final readonly class SearchResult {
        public function __construct(
            /** @var Document[] */
            public array $documents,
            public int $totalCount,
            public int $limit,
            public int $offset
        ) {}
    }
    ```
- **Mise à jour de `SearchEngineInterface`** :
    ```php
    public function search(string $query, int $offset = 0, int $limit = 10): SearchResult;
    ```

### 2. Mise à jour de l'Infrastructure (Solr)
- **`SolrClientService`** : Utiliser les méthodes `setStart($offset)` et `setRows($limit)` de Solarium de manière dynamique.
- **`SolrSearchEngine`** : 
    - Extraire le `numFound` de la réponse `Solarium\QueryType\Select\Result\Result`.
    - Mapper les données vers le nouvel objet `SearchResult`.

### 3. Couche Application et Contrôleur
- **`SearchUseCase`** : Faire suivre les paramètres `offset` et `limit` à l'interface du moteur de recherche.
- **`HomeController`** :
    - Récupérer le paramètre `page` depuis la `Request`.
    - Calculer l'offset : `$offset = ($page - 1) * $limit`.
    - Passer le `SearchResult` complet à la vue Twig.

### 4. Interface Utilisateur (Twig)
- **Affichage du compteur** : Afficher "X résultats trouvés" en utilisant `results.totalCount`.
- **Bloc de pagination** : Ajouter un composant de navigation Bootstrap en bas de la liste pour passer d'une page à l'autre.

---

## Pourquoi cette amélioration ?
1. **Expérience Utilisateur (UX)** : Permet de parcourir l'intégralité de l'index.
2. **Performance** : Évite de charger des volumes de données inutiles en mémoire.
3. **Professionnalisme** : Aligne l'application sur les standards des moteurs de recherche modernes.
