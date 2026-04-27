# 🏆 ERP Événement Sportif

[![deploy to alwaysdata](https://img.shields.io/badge/deploy-alwaysdata-blue?style=flat-square)](https://admin.alwaysdata.com/)
[![quality-check](https://img.shields.io/badge/quality-PHPStan%20%7C%20PHPCS-green?style=flat-square)]()

ERP modulaire conçu pour centraliser la gestion, la planification et le suivi d'événements sportifs. Le projet repose sur une architecture MVC (Modèle-Vue-Contrôleur) robuste, garantissant une séparation claire des responsabilités et une maintenance facilitée.

## 👥 Équipe
- **CLOT-GODARD Kenji**
- **CELESTINE Samuel**

## Installation & Développement

### Prérequis

- [`Docker`](https://docs.docker.com/engine/install/)
- [`Docker compose`](https://docs.docker.com/compose/install/)

### Démarrage rapide

1. Cloner le repo :
git clone https://github.com/votre-compte/erp_evenement_sportif.git
cd erp_evenement_sportif

2. Installer les dépendances :
docker compose run --rm composer install

3. Lancer l'environnement de développement :
docker compose up -d --build

### Accès aux services

- Site web : [localhost:8080](http://localhost:8080)
- Service PHP : erp_php (PHP 8.4)
- Serveur Web : erp_nginx

## Qualité du code et Tests

Nous utilisons un environnement Dockerisé pour garantir la cohérence des analyses entre les développeurs.

| Outil  | Commande | Description |
|:-------- |:---------| :--------|
| PHPCS     | docker compose run --rm composer lint   | Vérification du style de code (PSR-12)    |
| phpstan     | docker compose run --rm composer analyse   | Analyse statique de niveau 9    |
| phpunit     | docker compose run --rm composer test   | Tests unitaires et fonctionnels    |

## Accès Distant

L'application et la base de données sont hébergées sur la plateforme Alwaysdata.

### SSH

Utilisez la commande ci-dessous :
ssh erp-evenement-sportif_[prenom]@ssh-erp-evenement-sportif.alwaysdata.net

*Merci de remplacer [prenom] par votre prénom.*

### HTTPS

L'application est accessible en ligne :
https://erp-evenement-sportif-[prenom].alwaysdata.net

---
*Projet développé dans le cadre d'un stage ERP événementiel - 2026*
