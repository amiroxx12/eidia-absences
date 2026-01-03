#!/bin/bash

echo "🚀 Démarrage du nettoyage et de l'initialisation de l'architecture..."

# 1. NETTOYAGE (On garde .git et ce script)
# Attention : ça supprime tout le reste dans le dossier courant !
echo "🧹 Suppression des anciens fichiers..."
find . -maxdepth 1 ! -name '.git' ! -name 'setup.sh' ! -name '.' ! -name '..' -exec rm -rf {} +

# 2. CRÉATION DES DOSSIERS
echo "📂 Création de l'arborescence MVC..."
mkdir -p config
mkdir -p public/assets/css
mkdir -p public/assets/js
mkdir -p public/assets/img
mkdir -p public/uploads
mkdir -p src/Controllers
mkdir -p src/Models
mkdir -p src/Services
mkdir -p src/views/layouts
mkdir -p src/views/dashboard
mkdir -p src/views/import
mkdir -p templates/inc
mkdir -p templates/pages

# 3. CRÉATION DES FICHIERS DE BASE (Vides ou avec contenu par défaut)
echo "📝 Création des fichiers..."

# Config
touch config/db.php
echo "<?php
// config.php - Clés API et Config (Ignoré par Git)
define('DB_HOST', 'localhost');
define('DB_USER', 'eidia_user');
define('DB_PASS', 'secret123');
define('DB_NAME', 'absences_db');
" > config/config.php

# Public
echo "<?php
// Point d'entrée unique (Routeur)
require_once '../config/db.php';
echo 'Bienvenue sur le projet EIDIA Absences';
" > public/index.php

# Fichier Documentation (Vide pour l'instant, tu colleras le HTML dedans)
touch public/documentation_projet.html

# CSS de base
echo "/* Style global */
body { font-family: sans-serif; }" > public/assets/css/style.css

# Base de données (Fichier SQL vide pour l'instant)
touch database.sql

# 4. CRÉATION DU GITIGNORE
echo "🛑 Génération du .gitignore..."
cat <<EOT >> .gitignore
.DS_Store
Thumbs.db
config/config.php
vendor/
public/uploads/*
!public/uploads/.gitkeep
.idea/
.vscode/
*.swp
EOT

# 5. GESTION DES DOSSIERS VIDES (Pour que Git les prenne)
echo "⚓ Ajout des .gitkeep..."
touch public/uploads/.gitkeep
touch src/Controllers/.gitkeep
touch src/Models/.gitkeep
touch src/Services/.gitkeep
touch templates/.gitkeep

echo "✅ Terminé ! L'architecture est propre et prête."
echo "👉 N'oublie pas de coller le code HTML dans public/documentation_projet.html"
