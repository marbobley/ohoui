# Rapport d'Analyse des Incohérences et Vulnérabilités (2026-03-15)

Ce document répertorie les incohérences techniques, les vulnérabilités de sécurité et les écarts architecturaux identifiés lors de l'audit flash du projet Ohoui.

## 1. Sécurité : Injections Solr (Haute Criticité)
Le fichier `src/Infrastructure/Solr/SolrQueryBuilder.php` présente plusieurs failles permettant l'injection de paramètres ou de commandes Solr.

*   **Incohérence :** Le guide de sécurité (`security_checklist.md`, ligne 16) stipule l'utilisation systématique de `$helper->escapeTerm($query)`, mais l'implémentation l'ignore délibérément si certains caractères sont présents.
*   **Détails :**
    *   **Requête principale (`q`) :** Si la requête contient un joker (`*`, `?`, `~`) ou un deux-points (`:`), l'échappement est totalement désactivé. Un utilisateur malveillant pourrait injecter d'autres champs de recherche ou des clauses complexes.
    *   **Filtres (`filters`) :** La méthode `configureFilters` utilise un `sprintf('%s:%s', $field, $value)` sans aucun échappement. Un attaquant peut injecter `filters[domain]=*:*` ou d'autres filtres arbitraires.
*   **Recommandation :** Utiliser systématiquement `$helper->escapeTerm()` sur les valeurs des filtres.

## 2. Sécurité : Risques XSS (Moyenne Criticité)
L'usage du filtre `|raw` dans Twig pour le highlighting est un point de vigilance critique.

*   **Incohérence :** La `security_checklist.md` (ligne 10) est marquée comme vérifiée, mais le point parent (ligne 9) ne l'est pas. Le code de `SolrSearchEngine::sanitizeHighlight` est fragile face à des données malveillantes qui imiteraient les balises de Solr.
*   **Risque :** Un document indexé pourrait contenir des séquences HTML échappées (`&lt;em...`) qui seraient re-transformées en HTML réel par le `str_replace` après le `htmlspecialchars`.
*   **Recommandation :** Renforcer `sanitizeHighlight` avec une validation plus stricte des balises ré-autorisées.

## 3. Architecture : Entorses au DDD / Hexagonal (Moyenne Criticité)
Le flux de données entre le contrôleur et l'infrastructure manque de couches de validation.

*   **Incohérence :** Le `HomeController` passe des tableaux bruts issus de `$_GET` directement au `SearchUseCase`, puis au `SolrSearchEngine`.
*   **Problème :** Absence de Value Objects ou de DTO de domaine pour valider les critères de recherche. L'infrastructure reçoit des données non-validées.
*   **Recommandation :** Introduire un objet `SearchCriteria` dans le Domaine.

## 4. Documentation et Qualité (Basse Criticité)
*   **Checklists obsolètes :** Plusieurs points de la `security_checklist.md` et `verification_checklist.md` ne sont pas à jour.
*   **Scripts manquants :** Référence à `scripts/check-all.sh` dans la documentation alors que le fichier est absent de la racine.
*   **Directives PHP :** L'usage de `use function` n'est pas systématique sur toutes les fonctions globales.

---
*Date du rapport : 2026-03-15*
