-- =============================================
-- INITIALISATION DE LA BASE DE DONNÉES
-- =============================================
CREATE DATABASE IF NOT EXISTS eidia_absences CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eidia_absences;

-- =============================================
-- 1. TABLE UTILISATEURS (Accès Back-office)
-- =============================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    nom                 VARCHAR(100) NOT NULL,
    prenom              VARCHAR(100) NOT NULL,
    email               VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe        VARCHAR(255) NOT NULL,
    role                ENUM('admin', 'operateur') DEFAULT 'operateur',
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion de l'Admin par défaut (Email: admin@eidia.edu / Mdp: admin123)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES 
('Admin', 'Principal', 'admin@eidia.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');


-- =============================================
-- 2. TABLE DES ÉTUDIANTS (Cible de l'import CSV)
-- =============================================
CREATE TABLE IF NOT EXISTS etudiants (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    cne                 VARCHAR(20) UNIQUE,        -- Identifiant unique étudiant
    nom                 VARCHAR(100) NOT NULL,
    prenom              VARCHAR(100) NOT NULL,
    email               VARCHAR(150),              -- Email personnel de l'étudiant
    telephone           VARCHAR(20),               -- Téléphone personnel
    
    -- Infos Académiques
    classe              VARCHAR(50),               -- Ex: 4IIR, 3IIR...
    
    -- Infos Parents (Pour les notifications d'absences)
    email_parent        VARCHAR(255),
    telephone_parent    VARCHAR(20),
    whatsapp_parent     VARCHAR(20),
    nom_parent          VARCHAR(200),
    
    adresse             TEXT,
    actif               BOOLEAN DEFAULT TRUE,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- 3. TABLE DES CONFIGURATIONS D'IMPORT (Sauvegarde des mappings)
-- =============================================
CREATE TABLE IF NOT EXISTS configurations_import (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    nom                 VARCHAR(100) NOT NULL, -- Nom donné par l'user (ex: "Import Etudiants 2025")
    mapping_json        JSON NOT NULL,         -- Stocke le tableau associatif [col_csv => col_bdd]
    delimiteur          CHAR(1) DEFAULT ';',
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_utilisation DATETIME
);

-- =============================================
-- 4. HISTORIQUE DES IMPORTS
-- =============================================
CREATE TABLE IF NOT EXISTS imports (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    nom_fichier         VARCHAR(255) NOT NULL,
    lignes_importees    INT DEFAULT 0,
    statut              ENUM('succes', 'erreur') DEFAULT 'succes',
    configuration_id    INT, -- Lien optionnel vers la config utilisée
    utilisateur_id      INT, -- Qui a fait l'import (peut être NULL si script auto)
    date_import         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (configuration_id) REFERENCES configurations_import(id) ON DELETE SET NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- =============================================
-- 5. TABLE DES TEMPLATES DE MESSAGES
-- =============================================
CREATE TABLE IF NOT EXISTS templates_messages (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    nom                 VARCHAR(100) NOT NULL,
    canal               ENUM('email', 'whatsapp', 'sms') NOT NULL,
    sujet               VARCHAR(255),
    contenu             TEXT NOT NULL,
    variables_disponibles JSON,
    actif               BOOLEAN DEFAULT TRUE,
    par_defaut          BOOLEAN DEFAULT FALSE,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 6. TABLE DES NOTIFICATIONS ENVOYÉES
-- =============================================
CREATE TABLE IF NOT EXISTS notifications (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id         INT NOT NULL,
    absence_ids         JSON, -- Peut stocker plusieurs IDs d'absences liées
    canal               ENUM('email', 'whatsapp', 'sms') NOT NULL,
    destinataire        VARCHAR(255) NOT NULL,
    template_id         INT,
    sujet               VARCHAR(255),
    contenu             TEXT NOT NULL,
    statut              ENUM('en_attente', 'envoye', 'delivre', 'lu', 'echec') DEFAULT 'en_attente',
    message_erreur      TEXT,
    id_externe          VARCHAR(255), -- ID renvoyé par l'API (Twilio/SMTP)
    envoye_par          INT,
    date_envoi          DATETIME,
    date_delivrance     DATETIME,
    date_lecture        DATETIME,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES templates_messages(id) ON DELETE SET NULL,
    FOREIGN KEY (envoye_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- =============================================
-- 7. TABLE DES ENVOIS PROGRAMMÉS
-- =============================================
CREATE TABLE IF NOT EXISTS envois_programmes (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    nom                 VARCHAR(100) NOT NULL,
    criteres_selection  JSON NOT NULL,
    template_id         INT NOT NULL,
    canal               ENUM('email', 'whatsapp', 'both') NOT NULL,
    date_programmee     DATETIME NOT NULL,
    recurrence          ENUM('unique', 'quotidien', 'hebdomadaire', 'mensuel'),
    statut              ENUM('programme', 'en_cours', 'termine', 'annule') DEFAULT 'programme',
    cree_par            INT,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES templates_messages(id),
    FOREIGN KEY (cree_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- =============================================
-- 8. TABLE DE CONFIGURATION SYSTÈME
-- =============================================
CREATE TABLE IF NOT EXISTS configuration (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    categorie           VARCHAR(50) NOT NULL,
    cle                 VARCHAR(100) NOT NULL,
    valeur              TEXT,
    description         VARCHAR(255),
    UNIQUE KEY unique_config (categorie, cle)
);

-- Données initiales de configuration
INSERT IGNORE INTO configuration (categorie, cle, valeur, description) VALUES
('smtp', 'host', 'smtp.example.com', 'Serveur SMTP'),
('smtp', 'port', '587', 'Port SMTP'),
('smtp', 'username', '', 'Utilisateur SMTP'),
('smtp', 'password', '', 'Mot de passe SMTP'),
('smtp', 'from_email', 'admin@eidia.edu', 'Email expéditeur'),
('whatsapp', 'provider', 'twilio', 'Fournisseur API'),
('systeme', 'limite_envoi_heure', '100', 'Limite envois par heure');

-- =============================================
-- 9. TABLE MODÈLE ABSENCES
-- =============================================
CREATE TABLE IF NOT EXISTS absences_modele (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id         INT NOT NULL,
    date_absence        DATE NOT NULL,
    heure_debut         TIME,
    heure_fin           TIME,
    matiere             VARCHAR(100),
    type_absence        ENUM('absence', 'retard') DEFAULT 'absence',
    justifie            BOOLEAN DEFAULT FALSE,
    motif_justification TEXT,
    document_justificatif VARCHAR(255),
    notifie             BOOLEAN DEFAULT FALSE,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_absence (etudiant_id, date_absence, heure_debut)
);