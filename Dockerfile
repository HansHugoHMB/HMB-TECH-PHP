# Utilise l'image officielle PHP CLI 8.1
FROM php:8.1-cli

# Installe les dépendances pour zip
RUN apt-get update && apt-get install -y libzip-dev zip unzip && \
    docker-php-ext-install zip

# Définit le dossier de travail
WORKDIR /app

# Copie tous les fichiers dans le conteneur
COPY . .

# Expose le port 10000 (Render)
EXPOSE 10000

# Démarre le serveur PHP natif sur le port 10000
CMD ["php", "-S", "0.0.0.0:10000"]