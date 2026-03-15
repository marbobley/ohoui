

# Instructions pour l'utilisation des données Owilix

Ce document regroupe les informations nécessaires pour utiliser les échantillons de données de l'Open Web Index (OWI) via l'outil `owilix` dans le cadre du projet OhOui.

## 1. Localisation des données
Les données téléchargées se trouvent dans le container Docker `sharp_feistel`.
Chemin interne des datasets : `/home/owi/.owi/public/cefal/`
Dataset actuel : `a742176a-e940-11f0-8645-02a47ca5d9fd`

## 2. Structure des données (Parquet)
Les fichiers sont au format Parquet. D'après les premières analyses, les colonnes disponibles incluent :
- `url` : URL de la page.
- `url_domain` : Domaine de l'URL (équivalent à `domain` dans notre schéma).
- `title` : Titre de la page.
- `main_content` : Contenu textuel principal.
- `language` : Langue détectée.

## 3. Extraction des données
Pour utiliser ces données dans OhOui (Solr), elles doivent être extraites du container. La méthode validée par l'utilisateur utilise la commande `owi` (alias ou version spécifique d'owilix) avec le format JSON.

### Commande d'extraction validée :
```bash
docker exec sharp_feistel owi --format json query less --local a742176a-e940-11f0-8645-02a47ca5d9fd
```

Note : L'utilisation de `query stream` avec `owilix` peut présenter des erreurs de compatibilité (ex: `TypeError` sur `explain`). La commande `owi ... query less` semble plus stable pour l'extraction immédiate.

## 4. Intégration dans OhOui
Une fois les données récupérées au format JSON, elles peuvent être injectées dans Solr via une commande Symfony dédiée.

### Schéma de correspondance (Mapping) :
Les données retournées par `owi` au format JSON utilisent les clés suivantes :
| Champ OWI | Champ Solr OhOui |
|-----------|------------------|
| `url`     | `url`            |
| `title`   | `title`          |
| `main_content` | `content`   |
| `language` | `language`      |
| `url_domain` | `domain`      |

## 5. Automatisation de l'import (Commande Symfony)
L'implémentation de la commande Symfony `app:index-owi` permettra de :
1. Exécuter la commande `docker exec` pour récupérer le flux JSON.
2. Décoder chaque objet JSON.
3. Créer un objet `App\Domain\Model\Document`.
4. Appeler `IndexUseCase` pour l'indexation.

Exemple de structure de commande :
```bash
php bin/console app:index-owi --limit 100
```
