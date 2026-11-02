# IvoirePay — présentation du projet

**Catégorie** : application full-stack (web + mobile) · fintech · paiement mobile money
**Dépôt** : <https://github.com/cedrickemmanuel25/ivoirepay>
**Démonstration visuelle** : <https://cedrickemmanuel25.github.io/ivoirepay/>

## Description courte (≈ 50 mots)

IvoirePay est une solution de paiement marchand par QR code pour la Côte d'Ivoire. Une API REST Laravel alimente une application mobile Flutter (clients et commerçants) et un back-office web d'exploitation : inscription par téléphone et OTP, paiements confirmés par PIN, KYC des commerçants, soldes, retraits et notifications.

## Description longue

IvoirePay réunit dans un seul dépôt :

- un **backend Laravel 12** qui expose une **API REST** sécurisée par Laravel Sanctum, avec MySQL comme base ;
- une **interface web** : landing page publique et back-office d'administration (Blade, Tailwind, Alpine.js) ;
- une **application Flutter** pour deux profils :
  - le **client** scanne le QR code d'un commerçant, choisit son portefeuille (Wave, Orange Money, MTN MoMo, Moov Money, Djamo) et confirme avec son PIN ;
  - le **commerçant** passe une **vérification KYC**, obtient son QR code, suit son chiffre d'affaires et demande des retraits.

L'équipe d'exploitation **administre les opérations** depuis le web : validation des dossiers KYC, suivi et export des transactions, traitement des retraits, gestion des utilisateurs et notifications groupées.

Le cœur technique porte sur la fiabilité des mouvements d'argent : webhooks signés et idempotents, verrous de ligne lors des crédits et des retraits, remboursements effectués une seule fois, documents KYC privés.

## Statut

Projet de portfolio, fonctionnel en local avec des données de démonstration fictives. Paiements et SMS **simulés** : aucun fournisseur réel n'est appelé, il n'y a ni utilisateur ni transaction réels.

## Pour aller plus loin

[CASE_STUDY.md](CASE_STUDY.md) · [TECH_STACK.md](TECH_STACK.md) · [FEATURES.md](FEATURES.md) · [ARCHITECTURE.md](ARCHITECTURE.md) · [SCREENSHOTS.md](SCREENSHOTS.md)
