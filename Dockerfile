# Utilise l'image officielle de PHP
FROM php:8.1-cli

# Crée un dossier de travail
WORKDIR /app

# Copie tous les fichiers de ton projet dans /app
COPY . .

# Ouvre le port requis par Render (10000)
EXPOSE 10000

# Démarre le serveur PHP sur ce port
CMD ["php", "-S", "0.0.0.0:10000"]