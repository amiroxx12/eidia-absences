# 📱 Notification des Parents sur les Absences

> **Projet EIDIA 2026** - Système intelligent d'import de CSV et de notifications multi-canaux (WhatsApp & Email).

🫵🏻 **Ce Markdown est écrit pour les développeurs** 🫵🏻 

🔗 **Site de Production :** [http://eidia-absences.duckdns.org](http://eidia-absences.duckdns.org)  
📖 **Documentation Technique :** [Voir la Documentation](http://eidia-absences.duckdns.org/documentation_projet.html)

---

## 📋 Présentation du Projet

Ce projet permet :
- D’importer facilement des fichiers CSV/Excel d’absences.
- De détecter automatiquement les informations importantes (nom, date, cours…).
- De notifier les parents en temps réel par WhatsApp et Email.
- De suivre les absences grâce à un tableau de bord simple avec statistiques.

### 🎯 Points Clés & Fonctionnalités
- **Import CSV** – Le système comprend automatiquement les colonnes importantes.
- **Base de données** – Les absences sont organisées par mois.
- **Notifications multi-canaux** – WhatsApp et Email.
- **Dashboard** – Visualisation simple des absences et statistiques.

---

## 🛠️ Architecture Technique (MVC)

Le projet respecte une architecture **MVC (Modèle-Vue-Contrôleur)** (séparation du code pour plus de clarté).



[Image of MVC architecture diagram]


```text
/notification-parents
├── config/                  # Configuration BDD & API (Ignoré par Git)
├── public/                  # RACINE WEB (Seul dossier accessible via navigateur)
│   ├── assets/              # CSS, JS, Images
│   ├── uploads/             # Stockage temporaire sécurisé
│   └── index.php            # Routeur unique (Front Controller)
├── src/                     # CŒUR DU SYSTÈME (Inaccessible web)
│   ├── Controllers/         # Orchestration (Import, Auth, Dashboard)
│   ├── Models/              # Accès Base de Données
│   └── Services/            # Logique métier (CsvDetector, WhatsAppService)
└── templates/               # VUES (Fichiers d'affichage HTML/PHP)
```

---

## 🗄️ Gestion de la Base de Données (Accès & Sécurité)

Vous vous demandez peut-être pourquoi l'accès à la BDD semble "compliqué". Voici pourquoi :

1.  **Pourquoi pas de `/phpmyadmin` classique ?** : C'est la cible n°1 des bots et hackers. En changeant l'URL pour une **URL secrète** (obfuscation), on élimine 99% des tentatives d'intrusion automatisées.
2.  **Pourquoi le dossier `config/` est ignoré ?** : On ne push **jamais** de mots de passe sur GitHub. Chaque développeur a son propre `config.php` en local, et le serveur a le sien. C'est la règle d'or de la sécurité.
3.  **Accès Production** : L'accès à la base de données en ligne est réservé aux tests finaux. Utilisez l'URL secrète fournie sur le groupe WhatsApp pour vos vérifications.

---

## 🚀 Installation & Démarrage (Local)

Pour les membres de l'équipe, suivez ces étapes pour coder en local :

### 1. Cloner le dépôt
```bash
git clone [https://github.com/amiroxx12/eidia-absences.git](https://github.com/amiroxx12/eidia-absences.git)
cd eidia-absences
```

### 2. Configurer l'environnement
Créez le fichier `config/config.php` (qui est ignoré par Git) et ajoutez vos accès locaux :

```php
<?php
// config/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'absences_db');
define('DB_USER', 'root'); // Votre user local
define('DB_PASS', '');     // Votre mdp local


define('TWILIO_SID', 'ACxxxxx...');
define('TWILIO_TOKEN', 'xxxxx...');
```

### 3. Base de Données
Importez le fichier `database.sql` (à la racine) dans votre PhpMyAdmin local pour créer la structure.

### 4. Lancer le serveur
⚠️ **Important :** Configurez votre serveur pour que la racine pointe vers le dossier **`public/`**. Si vous voyez les dossiers `src` ou `config` dans votre navigateur, c'est que votre configuration est **dangereuse**.

---

## 🔄 CI/CD & Déploiement

Le déploiement est **entièrement automatisé** via GitHub Actions :
**Push sur main** → **Mise à jour automatique** sur le serveur de production.

> **Règle d'or :** Toujours faire un `git pull --rebase` avant de commencer à travailler pour éviter de casser le pipeline de déploiement.

---

## 🛡️ Sécurité & Infrastructure

L'infrastructure de production a été durcie ("Hardened") :

* 🔒 **DocumentRoot Isolation :** Code source physiquement inaccessible du web.
* 🕵️ **Obfuscation :** URL PhpMyAdmin cachée.
* ⛔ **Fail2Ban :** Bannissement automatique des IPs suspectes sur le SSH.
* 🤫 **Server Hardening :** Masquage des versions serveur et erreurs PHP désactivées.
* 🔐 **HTTPS :** Chiffrement SSL Let's Encrypt.
