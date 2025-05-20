# Utilise l'image officielle de PHP avec les outils nécessaires
FROM php:8.1-cli

# Installe les dépendances système et les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install zip gd xml mbstring dom curl

# Crée un dossier de travail
WORKDIR /app

# Copie tous les fichiers de ton projet dans /app
COPY . .

# Installe les dépendances PHP via Composer (si composer.json existe)
RUN curl -sS https://getcomposer.org/installer | php && \
    php composer.phar install || true

# Ouvre le port requis par Render (10000)
EXPOSE 10000

# Démarre le serveur PHP sur ce port
CMD ["php", "-S", "0.0.0.0:10000"]