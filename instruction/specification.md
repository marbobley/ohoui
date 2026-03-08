# Spécification du Moteur de Recherche Simple

Ce document définit les spécifications pour la mise en œuvre d'un moteur de recherche simple basé sur l'infrastructure actuelle du projet (Symfony + Solr).

## 1. Objectifs
- Fournir une interface de recherche web simple et réactive.
- Permettre l'indexation de documents contenant des titres, des URLs, du contenu textuel, la langue et le domaine.
- Offrir une recherche plein texte performante.

## 2. Architecture Technique
- **Approche :** Domain-Driven Design (DDD), Test-Driven Development (TDD), Architecture Hexagonale.
- **Principes :** Clean Code (DRY, KISS, SOLID).
- **Framework :** Symfony 8.0
- **Moteur de Recherche :** Apache Solr (via la bibliothèque Solarium)
- **Langage :** PHP 8.4+
- **Moteur de Template :** Twig (avec Symfony UX Turbo/Stimulus pour la réactivité)

## 3. Modèle de Données (Document Solr)
Chaque document indexé dans Solr devra comporter au minimum les champs suivants :
- `id` : Identifiant unique (string).
- `title` : Titre du document (string/text).
- `url` : URL source (string).
- `content` : Contenu textuel indexable (text).
- `language` : Code langue (ISO 639-1) (string).
- `domain` : Nom de domaine source (string).

## 4. Fonctionnalités

### 4.1 Indexation
- Fournir un service (`SolrClientService`) pour envoyer des documents à Solr.
- Fournir une commande console (`app:index-sample`) pour indexer un échantillon de données à des fins de test.
- Gérer l'ajout (`addDocument`) et la validation (`addCommit`) des documents.

### 4.2 Recherche
- Permettre la recherche par mots-clés via un paramètre de requête `q`.
- Par défaut, si aucune recherche n'est saisie, ne pas afficher de résultats.
- Support de la recherche plein texte via le service `SolrClientService::search`.

### 4.3 Interface Utilisateur
- Une page d'accueil simple (`/`) avec un champ de recherche.
- Affichage des résultats incluant :
    - Titre du document (avec lien cliquable vers l'URL).
    - Extrait du contenu (si possible avec mise en évidence).
    - Métadonnées (domaine, langue).
- Utilisation de Twig pour le rendu.

## 5. Perspectives d'Amélioration
- Pagination des résultats.
- Facettage par langue ou domaine.
- Mise en évidence des termes de recherche (Highlighting).
- Suggestion de recherche (Auto-complete).
