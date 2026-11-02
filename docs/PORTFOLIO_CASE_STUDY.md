# Étude de cas — IvoirePay

> Paiement marchand par QR code : API Laravel, back-office web et application mobile Flutter réunis dans un seul produit.

## Contexte

En Côte d'Ivoire, le paiement du quotidien passe par l'argent mobile : Wave, Orange Money, MTN MoMo, Moov Money, Djamo. Pour un petit commerçant, accepter tous ces portefeuilles veut dire jongler entre plusieurs numéros, vérifier chaque transfert et rendre la monnaie.

IvoirePay répond avec un principe simple : **un QR code statique par commerçant**. Le client scanne, choisit son portefeuille, saisit le montant, confirme avec son PIN, et le commerçant est crédité.

## Rôle

Conception et développement de l'ensemble du produit :

- modèle de données et API REST (Laravel 12, Sanctum, MySQL) ;
- back-office d'exploitation (Blade, Tailwind, Alpine.js) et landing page ;
- application mobile Flutter double profil, client et commerçant (Riverpod, go_router, Dio) ;
- intégration d'un agrégateur de paiement (Yengapay) et d'une passerelle SMS (Africa's Talking).

## Parcours couverts

| Acteur | Parcours |
|---|---|
| Client | téléphone → OTP → PIN → scan du QR → choix du portefeuille → montant → PIN → succès → reçu PDF |
| Commerçant | inscription → dossier KYC (pièces) → attente de validation → PIN → tableau de bord, QR code, historique → retrait protégé par PIN |
| Administrateur | revue KYC (pièces via URL signée) → approbation (QR généré, SMS) ou rejet motivé ; suivi des transactions et exports ; traitement ou annulation des retraits ; notifications groupées |

## Décisions techniques

**Authentification sans mot de passe.** Le téléphone sert d'identifiant. L'OTP à 6 chiffres est limité en durée et en essais. Le PIN à 4 chiffres est haché avec bcrypt et le compte est suspendu après 5 échecs. Les jetons Sanctum sont révoqués à chaque connexion par PIN et à chaque changement de PIN.

**Un seul backend pour deux interfaces.** Les contrôleurs API (mobile) et Admin (web) s'appuient sur les mêmes modèles Eloquent et les mêmes services (`YengapayService`, `QrCodeService`, `NotificationService`). Les règles métier ne sont donc pas dupliquées.

**L'argent sous verrou.** Le crédit d'un paiement et la réservation d'un retrait se font dans une transaction SQL avec `lockForUpdate()`. Les changements de statut sont conditionnels (`UPDATE … WHERE status = 'pending'`), si bien qu'un webhook rejoué, un double clic ou deux workers concurrents ne créditent ni ne remboursent deux fois.

**Webhooks défensifs.** La signature HMAC-SHA256 est obligatoire et un secret absent bloque tout. Les statuts inconnus sont refusés, et un paiement réglé ne peut pas repasser en échec.

**Conformité KYC.** Les pièces d'identité vont sur un disque privé et ne sont visibles qu'à travers des URL signées valables 15 minutes depuis le back-office.

**Mode simulation explicite.** `YENGAPAY_SIMULATE` règle paiements et retraits localement, sans aucun appel au fournisseur. Ce mode ne s'active plus parce qu'un `APP_DEBUG` aurait été oublié en production.

## Audit et finalisation

Le projet existait en deux dépôts séparés (backend et mobile). La finalisation a consisté à :

1. **réunir** les deux applications dans un monorepo `backend/` + `mobile/`, sans sous-module, et à retirer les journaux et fichiers personnels ;
2. **écrire des tests** sur les parcours critiques (39 tests Laravel, 7 tests Flutter) avant de corriger quoi que ce soit ;
3. **corriger uniquement les défauts démontrés** par ces tests ou par l'exécution réelle, entre autres :
   - colonne `wallet_number` absente des migrations et portefeuilles Orange / MTN refusés par l'enum : toute installation neuve échouait au premier paiement ;
   - service de paiement en erreur fatale quand il n'était pas configuré, ce qui cassait tout l'espace commerçant ;
   - webhook falsifiable lorsque le secret n'était pas défini ; statut final écrasable ;
   - PIN de retrait demandé par l'application mais jamais vérifié par le serveur ;
   - double paiement possible par double confirmation, double remboursement possible entre annulation admin et job de retrait ;
   - codes OTP écrits en clair dans les journaux, logs HTTP mobiles (PIN, jeton) actifs en production ;
   - recherches admin en `ilike` (PostgreSQL) qui échouaient sur MySQL ; pièces KYC jamais affichées (URL non signées) ;
   - URL de tunnel codée en dur à 4 endroits de l'application mobile, remplacée par `--dart-define=API_BASE_URL`.

Le détail figure dans [AUDIT_REPORT.md](AUDIT_REPORT.md).

## Résultat

- Un dépôt unique, installable en quelques commandes, avec des données de démonstration entièrement fictives.
- Des parcours de paiement et de retrait couverts par des tests qui reproduisent les cas limites : rejeu, concurrence, mauvais PIN, absence de secret.
- Une landing et une application mobile vérifiées en conditions réelles (Playwright, appareil Android).

## Limites assumées

- Les paiements sont **simulés** : l'intégration Yengapay n'a pas été validée contre le fournisseur réel dans ce dépôt.
- Il n'y a ni utilisateurs ni transactions réels. Les chiffres affichés sur la landing (« 5 000+ transactions / jour »…) sont des textes marketing de démonstration.
- À prévoir avant une mise en production : limitation de débit sur l'OTP et la confirmation de paiement, gestion de la commission, envoi réel du formulaire de contact.
