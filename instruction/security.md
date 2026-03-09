# Instructions de Sécurité et de Performance (PHP)

Ce document répertorie les règles de sécurité et les meilleures pratiques à suivre pour le développement PHP au sein du projet.

## 1. Appels de Fonctions Globales PHP

Pour optimiser les performances et prévenir les attaques par masquage de fonctions (Function Shadowing), toutes les fonctions globales natives de PHP **doivent** être précédées d'un anti-slash (`\`).

### Pourquoi ?
- **Sécurité :** Empêche un attaquant de définir une fonction portant le même nom dans le namespace courant (ex: `App\Service\str_contains`), détournant ainsi l'appel de la fonction native.
- **Performance :** PHP n'a pas besoin de chercher si la fonction existe dans le namespace courant avant de basculer sur la fonction globale. Cela permet à l'OPcache d'optimiser l'appel de fonction.

### Exemple
**Incorrect :**
```php
if (str_contains($query, ':')) {
    $formatted = sprintf('title:%s', $query);
}
```

**Correct :**
```php
if (\str_contains($query, ':')) {
    $formatted = \sprintf('title:%s', $query);
}
```

### Outil de vérification
L'outil `mago` est utilisé pour détecter ces appels ambigus. Assurez-vous de lancer le linter avant chaque commit :
```bash
vendor/bin/mago lint
```
