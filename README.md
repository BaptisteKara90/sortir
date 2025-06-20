# Sortir

Projet d'étude réalisé par Antoine Guillo, Baptiste Kara et Elisabeth Lombard.

## Description

Sortir est une application web développée dans le cadre d'un projet d'étude. Elle permet la gestion d'événements, de groupes privés, de lieux et d'utilisateurs. L'application est construite avec le framework Symfony (PHP) côté back-end et utilise JavaScript côté front-end.

## Fonctionnalités principales
- Gestion des utilisateurs (inscription, connexion, modification du profil)
- Création et gestion de sorties (événements)
- Gestion des groupes privés
- Gestion des lieux et des villes
- Système d'import/export de données
- Gestion des images de profil et des lieux

## Installation

1. **Cloner le dépôt**
   ```bash
   git clone <url-du-dépôt>
   cd sortir
   ```
2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```
3. **Configurer la base de données**
   - Modifier le fichier `.env` pour renseigner vos identifiants de base de données.
   - Créer la base de données :
     ```bash
     php bin/console doctrine:database:create
     ```
   - Exécuter les migrations :
     ```bash
     php bin/console doctrine:migrations:migrate
     ```
4. **Lancer le serveur de développement Symfony**
   ```bash
   symfony server:start
   ```

## Structure du projet
- `src/` : Code source PHP (contrôleurs, entités, formulaires, services, repositories)
- `assets/` : Fichiers JavaScript et ressources front-end
- `public/` : Fichiers accessibles publiquement (index.php, CSS, images)
- `templates/` : Templates Twig pour le rendu des pages
- `config/` : Fichiers de configuration Symfony

## Auteurs
- Antoine Guillo
- Baptiste Kara
- Elizabeth Lombard

## Licence
Ce projet est réalisé dans un cadre pédagogique et n'est pas destiné à un usage commercial.
