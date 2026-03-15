# Checklist de Sécurité (Projet Ohoui)

Ce document répertorie les points de vigilance critiques pour maintenir la sécurité du projet Ohoui.

## 1. Prévention XSS (Cross-Site Scripting)

L'utilisation du filtre `|raw` dans Twig est **dangereuse**. Elle ne doit être utilisée que si la donnée a été préalablement nettoyée de manière rigoureuse dans la couche applicative.

- [x] **Highlighting (Recherche)** : Toute donnée issue de Solr avec mise en évidence doit passer par la méthode `SolrSearchEngine::sanitizeHighlight`.
  - [x] Vérifier que `sanitizeHighlight` échappe tout le HTML (`htmlspecialchars`) avant de ré-autoriser sélectivement les balises de mise en évidence (ex: `<em class="hl">`).
- [x] **Inputs Utilisateur** : Utilisation du DTO `SearchCriteria` pour valider les données de recherche. Twig échappe par défaut, `|raw` n'est utilisé que sur le highlighting déjà assaini.
- [ ] **Attributs HTML** : Ne jamais injecter de données non-nettoyées dans des attributs HTML sensibles (comme `href`, `onclick`, `src`).

## 2. Prévention des Injections

- [x] **Solr Queries** : Utiliser systématiquement le `Helper` de Solarium pour échapper les termes de recherche (`$helper->escapeTerm($query)`). Les jokers (`*`, `?`, `~`) sont autorisés mais doivent être gérés prudemment pour éviter les injections de champs.
- [x] **Solr Filters** : Les valeurs des filtres sont systématiquement échappées via `$helper->escapeTerm($value)` dans `SolrQueryBuilder::configureFilters`.
- [ ] **SQL Injections** : Si Doctrine est utilisé, privilégier le Query Builder ou le DQL avec des paramètres nommés. Ne jamais concaténer directement des variables dans une requête SQL.

## 3. Gestion des Données et Secrets

- [ ] **Fichiers .env** : Ne jamais commiter de secrets (clés d'API, mots de passe) dans le code source. Utiliser `.env.local` pour le développement local.
- [ ] **Logs** : S'assurer qu'aucune donnée sensible (mots de passe, jetons de session) n'est écrite dans les logs applicatifs.

## 4. Tests de Sécurité

- [x] **Tests de Régression XSS** : Pour chaque correction de vulnérabilité XSS, un test d'intégration ou fonctionnel doit être ajouté pour s'assurer que la vulnérabilité ne réapparaît pas (ex: indexer un document avec un script malveillant et vérifier qu'il est neutralisé). `SolrSearchEngineTest` et les tests d'intégration valident le nettoyage.
- [x] **Validation des Entrées (DDD)** : Utilisation de Value Objects/DTO (`SearchCriteria`) pour valider les paramètres de recherche avant qu'ils n'atteignent l'infrastructure.

---
*Dernière mise à jour : 2026-03-15 (Suite à la résolution de la vulnérabilité XSS sur le highlighting)*
