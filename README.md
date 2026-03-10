# OhOui - Moteur de Recherche Simple

Projet basé sur Symfony 8.0, Solr, et l'architecture hexagonale.

## Documentation
- [Guide de Développement](instruction/development_guidelines.md)
- [Spécifications](instruction/specification.md)
- [Instructions de Sécurité](instruction/security.md)

## Installation & Lancement

1. **Cloner le projet**
2. **Installer les dépendances** : `composer install`
3. **Lancer l'infrastructure (Docker)** : `docker compose up -d`
4. **Vérifier le linter** : `vendor/bin/mago lint && vendor/bin/mago guard`
5. **Lancer les tests** : `vendor/bin/phpunit`

## Commandes utiles
- Indexer des données d'exemple : `php bin/console app:index-sample`
