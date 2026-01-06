#!/bin/bash

# 1. Nettoyage du JS (interdit)
rm -rf public/assets/js

# 2. Création des dossiers
mkdir -p config
mkdir -p public/assets/css
mkdir -p public/assets/img
mkdir -p public/uploads
mkdir -p src/Controllers
mkdir -p src/Models
mkdir -p src/Services
mkdir -p src/Views/auth
mkdir -p src/Views/dashboard
mkdir -p src/Views/import
mkdir -p src/Views/layouts
mkdir -p src/Views/notifications
mkdir -p src/Views/students

# 3. Création des fichiers VIDES (touch)

# Racine
touch README.md
touch database.sql

# Config
touch config/config.php
touch config/db.php

# Public
touch public/index.php
touch public/documentation_projet.html
touch public/assets/css/style.css

# Src - Controllers
touch src/Controllers/DashboardController.php
touch src/Controllers/ImportController.php
touch src/Controllers/NotificationController.php
touch src/Controllers/AuthController.php
touch src/Controllers/StudentController.php

# Src - Models
touch src/Models/Etudiant.php
touch src/Models/Absence.php
touch src/Models/Notification.php
touch src/Models/ImportConfiguration.php
touch src/Models/User.php

# Src - Services
touch src/Services/CsvImportService.php
touch src/Services/NotificationService.php
touch src/Services/DatabaseService.php
touch src/Services/AuthService.php

# Src - Views (Layouts)
touch src/Views/layouts/main.php
touch src/Views/layouts/header.php
touch src/Views/layouts/footer.php

# Src - Views (Pages spécifiques Projet 2)
touch src/Views/dashboard/index.php
touch src/Views/import/upload.php
touch src/Views/import/mapping.php
touch src/Views/import/preview.php
touch src/Views/notifications/send.php
touch src/Views/notifications/history.php
touch src/Views/students/list.php
touch src/Views/students/details.php
touch src/Views/auth/login.php

# 4. Gestion de la migration (si anciens dossiers existent)
if [ -d "templates" ]; then
    # On déplace juste pour ne pas perdre l'existant, mais on ne touche pas au contenu
    cp -r templates/* src/Views/ 2>/dev/null
    rm -rf templates
fi

if [ -d "src/views" ] && [ "src/views" != "src/Views" ]; then
    cp -r src/views/* src/Views/ 2>/dev/null
    rm -rf src/views
fi

echo "Arborescence créée avec fichiers vides."
