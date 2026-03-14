# Utilisation de l'image FrankenPHP officielle avec PHP 8.4
FROM dunglas/frankenphp:1-php8.4-alpine

# Installation des extensions PHP nécessaires
RUN install-php-extensions \
    pdo_pgsql \
    intl \
    zip \
    opcache

# Définition du répertoire de travail
WORKDIR /app

# Copie des fichiers du projet
COPY . /app

# On s'assure que le dossier public est bien utilisé comme racine du serveur
ENV SERVER_NAME=:80
ENV DOCUMENT_ROOT=/app/public

# FrankenPHP utilise par défaut le serveur Caddy intégré
