# Guide de l'Interface Utilisateur (UI/UX)

Ce document définit les standards et les bonnes pratiques pour le développement de l'interface utilisateur du projet Ohoui.

## 1. Technologies et Frameworks
- **Framework CSS :** Bootstrap 5.3 (via AssetMapper).
- **Moteur de Template :** Twig (Symfony).
- **Gestion des Assets :** Symfony AssetMapper (pas de Webpack/Encore ou Vite par défaut).
- **Réactivité :** Symfony UX (Stimulus & Turbo).

## 2. Organisation des Assets
Tous les assets se trouvent dans le dossier `assets/` :
- `assets/styles/app.css` : Styles globaux et personnalisés.
- `assets/app.js` : Point d'entrée JavaScript principal (importe le CSS et Bootstrap).
- `assets/controllers/` : Contrôleurs Stimulus pour les interactions dynamiques.

### Règles CSS
- Éviter le CSS "inline" dans les fichiers Twig.
- Préférer l'utilisation des classes utilitaires de Bootstrap quand c'est possible.
- Les styles personnalisés persistants (comme `.hl` pour le highlighting) doivent être ajoutés dans `assets/styles/app.css`.

## 3. Standards Twig
- Les templates se trouvent dans `templates/`.
- Utiliser l'héritage de template (`extends 'base.html.twig'`).
- Organiser les blocs (`title`, `stylesheets`, `javascripts`, `body`).
- **Accessibilité :** Utiliser des balises sémantiques (`main`, `nav`, `section`, `article`, `aria-label`).

## 4. Intégration des Assets dans Twig
- Utiliser `{{ importmap('app') }}` dans le bloc `stylesheets` et `javascripts` de `base.html.twig`.
- Ne pas ajouter manuellement des balises `<link>` ou `<script>` pour les assets gérés par l'Importmap, sauf exception justifiée.

## 5. Composants UI Spécifiques
### Mise en évidence (Highlighting)
- Les termes de recherche mis en évidence par Solr sont entourés de balises `<em class="hl">`.
- La classe `.hl` est définie dans `assets/styles/app.css`.
- Dans Twig, utiliser le filtre `|raw` pour afficher le contenu mis en évidence (car il contient des balises HTML sécurisées générées par l'infrastructure).
  ```twig
  {{ doc.getHighlight()|raw }}
  ```

## 6. Réactivité et UX
- Utiliser Turbo pour des transitions de page fluides sans rechargement complet.
- Pour tout comportement JavaScript spécifique à un élément (ex: auto-submit, modales, tooltips), créer un contrôleur Stimulus dans `assets/controllers/`.
