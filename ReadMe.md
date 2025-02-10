# Senegal_Eventbrite

**Développement d’un Clone d’Eventbrite en PHP MVC & PostgreSQL**

**Author du Brief:** Iliass RAIHANI.

**Scrum Master:** Moustapha Ndiaye. 

**collaborateur :** Hamza Atig - Zakariae El Hassad - Ibrahim Nidam.

## Links

- **Presentation Link :** [View Presentation](https://www.canva.com/design/DAGesshlejM/C5g7wD3RY1OqmWGACjCEvA/view?utm_content=DAGesshlejM&utm_campaign=designshare&utm_medium=link2&utm_source=uniquelinks&utlId=h71b28e934e)
- **Backlog Link :** [View Backlog](https://github.com/orgs/Youcode-Classe-E-2024-2025/projects/123/views/1)
- **GitHub Repository :** [View on GitHub](https://github.com/Youcode-Classe-E-2024-2025/Senegal_Eventbrite.git)

### Créé : 10/02/25

Creation d'une plateforme de gestion et réservation des places d'événements avec PHP MVC


# Configuration et Exécution du Projet

### Prérequis
* **Node.js** et **npm** installés (téléchargez [Node.js](https://nodejs.org/)).
* **Laragon** installé (téléchargez [Laragon](https://laragon.org/download/)).
* **PHP** installé et ajouté au PATH (Environnement système).

### Étapes d’installation

1. **Cloner le projet** :
   - Ouvrir un terminal et exécuter :  
     `git clone https://github.com/Youcode-Classe-E-2024-2025/Senegal_Eventbrite.git`

2. **Placer le projet dans le dossier Laragon** :
   - Cliquez sur le bouton **Root** dans Laragon pour ouvrir le dossier `www` (par défaut, `C:\laragon\www`).
   - Le chemin de votre projet devrait être `C:\laragon\www\Senegal_Eventbrite`.

3. **Configurer la base de données** :
   - Faites un clic droit sur **Laragon**, puis allez dans **Tools** > **Quick Add** et téléchargez **phpMyAdmin** et **MySQL**.
   - Ouvrir **phpMyAdmin** via Laragon :
     - Dans Laragon, cliquez sur le bouton **Database** pour accéder à phpMyAdmin (username = `root` et mot de passe est vide).
     - Créez une base de données `eventbrite_db` et importez le fichier `script.sql` (disponible dans le dossier `/data/`).

4. **Installer les dépendances Node.js** :
   - Ouvrez un terminal dans le dossier du projet cloné.
   - Exécutez :  `npm install` ou `npm i`

5. **Installer Composer** :
   - Ouvrez un terminal dans le dossier du projet cloné et exécutez les commandes suivantes :

     ```cmd
     REM Télécharger l'installateur Composer
     php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

     REM Vérifier le hash SHA-384 de l'installateur
     php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') echo Installer verified && exit; echo Installer corrupt && del composer-setup.php && exit /b 1"

     REM Exécuter l'installateur
     php composer-setup.php

     REM Supprimer le script d'installation
     php -r "unlink('composer-setup.php');"

     REM Déplacer composer.phar dans un répertoire du PATH (optionnel pour un usage global)
     move composer.phar C:\bin\composer.phar
     ```

    - Ajoutez le dossier `C:\bin` à votre variable d'environnement PATH pour utiliser Composer globalement.

6. **Initialiser Composer dans le projet** :
   - Dans le dossier racine du projet, exécutez :

     ```cmd
     composer init
     ```
   - Suivez les instructions pour générer un fichier `composer.json` et accepter **psr-4**.

7. **Installer les dépendances PHP** :
   - Une fois le fichier `composer.json` généré, installez les dépendances en exécutant :

     ```cmd
     composer install
     ```

8. **Configurer Laragon pour le serveur local** :
   - Lancez **Laragon** et démarrez les services **Apache** et **MySQL** en cliquant sur **Start All**.

9. **Exécuter le projet** :
   - Une fois les services lancés dans Laragon, cliquez sur le bouton **Web** pour accéder à `http://localhost/Senegal_Eventbrite` dans votre navigateur.



# Contexte du projet:

Les plateformes de gestion d’événements comme Eventbrite permettent aux organisateurs de créer, gérer et promouvoir des événements en ligne ou en présentiel.

​

Ce projet vise à concevoir un clone avancé d’Eventbrite, en respectant les meilleures pratiques en PHP MVC avec PostgreSQL et en intégrant AJAX pour des interactions dynamiques.

​

L’objectif est de proposer un système où :

✅ Les organisateurs peuvent publier et gérer des événements,

✅ Les participants peuvent réserver des billets en ligne,

✅ Un back-office permet aux administrateurs de gérer les utilisateurs et événements,

✅ Des statistiques avancées permettent un suivi précis des événements et ventes.

​

Fonctionnalités requises

​

Gestion des utilisateurs

✔ Inscription et connexion sécurisée (email, mot de passe hashé avec bcrypt)

✔ Gestion des rôles : Organisateur, Participant, Admin

✔ Profil utilisateur (avatar, nom, historique des événements créés/participés)

✔ Système de notifications (email, alertes sur le site)

​

Gestion des événements

✔ Création et modification d’un événement (titre, description, date, lieu, prix, capacité)

✔ Gestion des catégories et tags (Conférence, Concert, Sport, etc.)

✔ Ajout d’images et de vidéos promotionnelles

✔ Validation des événements par un administrateur

✔ Système de mise en avant (événements sponsorisés)

​

Réservation et paiement

✔ Achat de billets avec différentes options (gratuit, payant, VIP, early bird)

✔ Paiement sécurisé via Stripe ou PayPal (sandbox mode)

✔ Génération de QR Code pour validation des billets à l’entrée

✔ Système de remboursement et annulation de billets

✔ Téléchargement de billets en PDF après achat

​

Tableau de bord organisateur

✔ Liste des événements créés avec état (actif, en attente, terminé)

✔ Statistiques des ventes et des réservations en temps réel

✔ Export des participants en CSV/PDF

✔ Gestion des promotions et réductions (codes promo, early bird)

​

Back-office Admin

✔ Gestion des utilisateurs (bannissement, suppression, modification)

✔ Gestion des événements (validation, suppression, modification)

✔ Statistiques globales (nombre d’utilisateurs, billets vendus, revenus)

✔ Système de modération des commentaires et signalements

​

Interactions dynamiques avec AJAX

✔ Chargement dynamique des événements (pagination sans rechargement)

✔ Recherche et filtres avancés (par catégorie, prix, date, lieu)

✔ Autocomplétion des recherches avec suggestions

✔ Validation de formulaire en temps réel (email déjà utilisé, mot de passe sécurisé)

​

Technologies utilisées

​

🔹 Backend (PHP MVC & PostgreSQL)

✅ PHP 8.x – Gestion du backend

✅ PostgreSQL – Base de données relationnelle optimisée

✅ PDO – Sécurisation des requêtes SQL (requêtes préparées)

✅ Twig – Moteur de templates pour structurer les vues

✅ Composer – Gestionnaire de dépendances PHP

​

🔹 Frontend (Ajax & UI/UX)

✅ HTML5, CSS3, JavaScript (ES6) – Interface utilisateur

✅ Bootstrap 5 – Design responsive ou TailwindCSS

✅ AJAX (Fetch API & jQuery) – Chargement dynamique

​

🔹 Sécurité et Outils

✅ .htaccess – Sécurisation et réécriture d’URL

✅ (Session Based Authentication) – Authentification sécurisée (optionnel)

✅ Classes Validator & Security – Protection XSS, CSRF, SQL Injection

✅ Gestion des sessions sécurisées (Class Session)

​

User Stories

​

👥 En tant qu'utilisateur (Participant), je veux :

✅ Créer un compte et me connecter avec mon email ou Google/Facebook.

✅ Parcourir la liste des événements et les filtrer par catégorie.

✅ Réserver un billet en ligne et recevoir un QR Code.

✅ Annuler ma réservation et demander un remboursement.

✅ Recevoir des notifications pour mes événements à venir.

​

👤 En tant qu'organisateur, je veux :

✅ Publier un événement et définir un prix pour les billets.

✅ Gérer mes ventes et voir les statistiques des inscriptions.

✅ Offrir des codes promo et gérer les remises.

✅ Exporter la liste des participants en CSV ou PDF.

​

🛡️ En tant qu'administrateur, je veux :

✅ Gérer les utilisateurs (bannissement, modification des rôles).

✅ Valider ou refuser les événements soumis.

✅ Suivre les statistiques globales et modérer les contenus.

​

Logique Métier (Business Logic)

​

📌 Gestion des rôles et permissions Un Participant ne peut réserver que des événements publics. Un Organisateur ne peut voir que ses propres événements. Un Admin a tous les droits (validation, modération).

​

📌 Système de réservation Vérification des places disponibles avant validation. Envoi d’un email avec le billet en pièce jointe après achat. Option d’annulation sous certaines conditions (remboursement partiel ou total).

​

📌 Sécurité avancée

Protection contre le CSRF et injections SQL. Hashage des mots de passe avec bcrypt. Gestion des sessions.

​

📌 Optimisation des performances Optimisation des requêtes PostgreSQL avec indexes et partitions. Chargement des événements par lazy loading avec AJAX.


## **Modalités pédagogiques**

Travail: Groupe

Durée de travail: 5 jours

Date de lancement du brief: 10/02/2025 à 10:00 am

Date limite de soumission: 14/02/2024 avant 05:00 pm



## **Modalités d'évaluation**

- Quiz collectif
- 15 minutes en one-to-one pour la partie démonstration et explication de code.

## **Livrables**
- La gestion des tâches sur un Scrum Board avec tous les User  stories.
- Lien de Repository Github du projet (team_eventbrite)
- Lien de la présentation

## **Critères de performance**

Optimisation des Requêtes SQL :
- Examiner et optimiser les requêtes SQL pour minimiser le temps d'exécution et éviter les goulots d'étranglement dans la base de données.

Nommer Convenablement les Classes et les Méthodes :
- Utiliser des noms de classes et de méthodes explicites et conformes aux conventions de nommage PSR-1 et PSR-12.

Encapsulation :
- Déclarer les propriétés comme privées ou protégées et fournir des méthodes d'accès publiques si nécessaire (getters et setters).

Héritage avec Précaution :
- Utiliser l'héritage avec modération et préférez la composition. Assurez-vous que l'héritage reflète une relation "est un" réelle.

Interfaces et Traits :
- Utiliser des interfaces pour définir des contrats et des traits pour réutiliser des fonctionnalités communes dans plusieurs classes.

Optimisation des Performances :
- Éviter l'utilisation excessive de la surcharge magique (__get, __set, etc.) qui peut entraîner une perte de performance. Privilégier des solutions plus explicites.

Documenter le Code :
- Fournir une documentation claire et concise pour chaque classe, méthode et propriété, en utilisant des commentaires de code.