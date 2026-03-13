# Spécification du Moteur de Recherche Ohoui

Ce document définit les spécifications pour la mise en œuvre d'un moteur de recherche basé sur l'infrastructure actuelle du projet (Symfony + Solr).

## 1. Objectifs
- Fournir une interface de recherche web simple, réactive et performante.
- Permettre l'indexation de documents contenant des titres, des URLs, du contenu textuel, la langue et le domaine.
- Offrir une recherche plein texte avec pondération (boosting) et filtrage.

## 2. Architecture Technique
- **Approche :** Domain-Driven Design (DDD), Test-Driven Development (TDD), Architecture Hexagonale.
- **Principes :** Clean Code (DRY, KISS, SOLID).
- **Framework :** Symfony 8.0
- **Moteur de Recherche :** Apache Solr (via la bibliothèque Solarium)
- **Langage :** PHP 8.4+
- **Moteur de Template :** Twig (avec Symfony UX Turbo/Stimulus pour la réactivité)

## 3. Modèle de Données (Document Solr)
Chaque document indexé dans Solr comporte les champs suivants :
- `id` : Identifiant unique (string).
- `title` : Titre du document (string/text).
- `url` : URL source (string).
- `content` : Contenu textuel indexable (text).
- `language` : Code langue (ISO 639-1) (string).
- `domain` : Nom de domaine source (string).

## 4. Fonctionnalités Implémentées

### 4.1 Indexation
- Cas d'utilisation (`IndexUseCase`) pour orchestrer l'indexation via `SearchEngineInterface`.
- Commande console (`app:index-sample`) pour indexer un échantillon de données.

### 4.2 Recherche et Pertinence
- Recherche par mots-clés via paramètre `q`.
- **Boosting :** Les mots-clés trouvés dans le titre ont un poids supérieur (x2.0) à ceux du contenu.
- **Pagination :** Gestion de l'offset et de la limite pour parcourir les résultats.
- **Filtrage :** Support du filtrage par langue et domaine (via paramètres d'URL).

### 4.3 Interface Utilisateur
- Page d'accueil (`/`) avec champ de recherche.
- Affichage des résultats :
    - Titre cliquable (URL source).
    - Extrait du contenu.
    - Métadonnées (domaine, langue).
    - Compteur de résultats.
    - Navigation par pagination.

## 5. Perspectives d'Amélioration
- Mise en évidence des termes de recherche (Highlighting).
- Suggestion de recherche (Auto-complete).
- Boost sur certains domaines spécifiques (gouvernementaux, éducatifs).
