# Fonctionnalités — IvoirePay

Toutes les fonctionnalités listées ici sont implémentées dans le code et ont été exercées par les tests ou en exécution locale.

## Authentification (mobile)

- Inscription et connexion par numéro de téléphone ivoirien (`+225`).
- OTP à 6 chiffres : validité et nombre d'essais limités.
- PIN à 4 chiffres haché (bcrypt), changement de PIN avec révocation des sessions.
- Suspension automatique du compte après 5 PIN erronés.
- Connexion biométrique optionnelle.

## Parcours client

- Scan du QR code commerçant (caméra).
- Choix du portefeuille : Wave, Orange Money, MTN MoMo, Moov Money, Djamo.
- Montant minimum de 100 FCFA, confirmation par PIN.
- Écran de succès, historique filtrable, détail et **reçu PDF**.
- Profil et photo de profil.

## Parcours commerçant

- Assistant **KYC** : raison sociale, adresse, CNI, RCCM, pièces justificatives (JPG / PNG / PDF).
- Écrans « dossier en attente » et « dossier rejeté » (avec motif).
- **QR code statique** généré à l'approbation, affichage plein écran et partage.
- Tableau de bord : solde, chiffre du jour et du mois, graphique sur 7 jours.
- Historique filtré (jour, semaine, mois).
- **Demande de retrait** protégée par PIN, historique des retraits.
- Notifications in-app.

## Back-office d'administration (web)

- Connexion réservée au rôle administrateur.
- Tableau de bord : volume du jour, commerçants actifs, KYC en attente, graphique sur 7 jours, répartition par portefeuille.
- Revue **KYC** : pièces consultables via URL signée temporaire, approbation (QR + SMS) ou rejet motivé.
- Transactions : recherche, export **PDF** et **CSV**.
- Commerçants : fiche, activation / suspension, téléchargement du QR.
- **Retraits** : lancement du traitement, annulation avec remboursement.
- Utilisateurs : recherche, fiche, suspension, SMS, suppression.
- **Notifications groupées** (clients, commerçants, tous) en in-app et/ou SMS.
- Profil administrateur, paramètres (minimum de retrait, support…).

## Fiabilité et sécurité des paiements

- Webhook fournisseur signé HMAC-SHA256, refusé sans secret configuré.
- Idempotence : un webhook rejoué ou une double confirmation ne crédite pas deux fois.
- Verrous de ligne lors du crédit du solde et de la réservation d'un retrait.
- Retrait remboursé **au plus une fois** (échec fournisseur ou annulation admin).
- Mode simulation explicite, désactivable en production.
- OTP absent des journaux ; logs HTTP mobiles limités au mode debug.
