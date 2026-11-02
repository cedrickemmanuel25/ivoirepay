# Rapport d'audit et de finalisation — IvoirePay

Date : 2 novembre 2026 · Branche : `finalisation/monorepo`

Méthode : chaque correction ci-dessous a d'abord été **démontrée** par un test automatisé en échec ou par une exécution réelle (MariaDB, serveur local, navigateur), puis corrigée, puis re-testée. Les modules qui fonctionnaient n'ont pas été modifiés.

## Origine des sources

| Dossier | Dépôt d'origine | Commit importé |
|---|---|---|
| `backend/` | `github.com/cedrickemmanuel25/ivoirepay`, branche `master` (5 correctifs cPanel par-dessus `main`) | `aa8d9be` |
| `mobile/` | `github.com/cedrickemmanuel25/web-mobile`, branche `main` | `e2b616c` |

- `backend/` conserve tout son historique Git (déplacement par `git mv`, commit `Restructure: move Laravel application into backend/`).
- `mobile/` est importé comme un commit normal, **sans** l'historique de `web-mobile`. L'unique commit d'origine contenait des photos personnelles (`assets/images/wa_*.jpg`), une sortie `ipconfig` et des scripts pointant vers des dossiers personnels : les importer avec leur historique les aurait publiés de façon permanente dans ce dépôt.
- Pas de sous-module, pas de dépôt imbriqué. Le dépôt `web-mobile` n'a pas été modifié.

### Fichiers non repris

| Fichier(s) | Raison |
|---|---|
| `mobile/assets/images/wa_0..4.jpg` | photos personnelles, non utilisées par le code |
| `mobile/assets/images/l1.png`, `l2.png` | logos « CRM » d'un autre projet, non utilisés |
| `mobile/flutter_01.png` | ancienne capture d'écran |
| `mobile/ip_output.txt` | configuration réseau locale |
| `mobile/*analy*.txt`, `run_*.txt`, `build_*.txt`, `android/build_diag*.txt` | journaux d'outils |
| `mobile/copy_wa.py`, `list_downloads.py` | scripts personnels (chemins `C:\Users\…`) |
| `backend/composer_error.txt`, `error.html` | journaux |
| `backend/tunnel_keeper.ps1` | tunnel LocalTunnel, remplacé par `API_BASE_URL` |
| `backend/fix_pending_withdrawals.php` | script ponctuel qui marquait **tous** les retraits en attente comme « complétés » |
| `backend/fix_qrcodes.php`, `generate_all_qrs.php` | scripts ponctuels de maintenance (toujours disponibles dans l'historique) |

## Corrections — backend

| # | Module | Défaut démontré | Correction |
|---|---|---|---|
| 1 | Paiement / BDD | Colonne `transactions.wallet_number` absente des migrations alors que l'API l'écrit : **toute initiation de paiement échoue** sur une base neuve. | Migration `2026_11_02_120000_fix_wallet_columns_on_transactions_table` (ajout protégé par `hasColumn`). |
| 2 | Paiement / BDD | `wallet_type` = enum `wave, djamo, moov` alors que l'API accepte `orange` et `mtn` : `Data truncated for column 'wallet_type'` sur MySQL. | Enum étendu aux 5 portefeuilles (même migration). |
| 3 | Service Yengapay | `TypeError` dans le constructeur si la configuration est vide : **tout l'espace commerçant** (dashboard, profil, QR, retraits) répond 500. | Valeurs de configuration converties en chaînes. |
| 4 | Webhook | Sans `YENGAPAY_WEBHOOK_SECRET`, un HMAC calculé avec une clé vide est accepté : webhook falsifiable. | Refus (401) si le secret ou la signature est vide. |
| 5 | Webhook | Un webhook `failed` tardif écrase un paiement déjà `success` (déjà crédité). Statut arbitraire écrit en base. | Échec appliqué uniquement à un paiement `pending` (sous verrou) ; statut inconnu → 422. |
| 6 | Crédit commerçant | Contrôle « déjà traité » fait sur un modèle en mémoire, sans verrou : deux webhooks concurrents peuvent créditer deux fois. | `lockForUpdate()` sur la transaction dans `DB::transaction`. |
| 7 | Confirmation | Simulation activée dès que `APP_DEBUG=true` : un serveur de production mal configuré valide de faux paiements. | Option explicite `YENGAPAY_SIMULATE` (par défaut uniquement si `APP_ENV=local`). |
| 8 | Confirmation | En mode simulation, appel réel au fournisseur malgré tout ; double confirmation = deux demandes de paiement envoyées au portefeuille du client. | En simulation, aucun appel au fournisseur ; verrou de 60 s + refus (409) si un paiement a déjà été initié. |
| 9 | Retrait | L'application demande le PIN mais ne l'envoie pas, et le serveur ne le vérifie pas. | `pin` obligatoire et vérifié (403 si incorrect) ; l'application l'envoie. |
| 10 | Retrait | Vérification du solde puis débit sans verrou : deux demandes simultanées peuvent dépasser le solde. | Réservation dans une transaction avec `lockForUpdate()`. |
| 11 | Job de retrait | Un retrait `processing` est retraité par un second job, d'où un double versement possible. | Prise en charge atomique `pending → processing` ; un job concurrent s'arrête. |
| 12 | Remboursement | Annulation admin et échec du job remboursent sans condition : double remboursement possible. | `UPDATE … WHERE status IN (…)`, puis remboursement seulement si la ligne a changé. |
| 13 | Auth | Codes OTP écrits en clair dans `laravel.log` (et corps des requêtes). | Codes retirés des journaux. |
| 14 | Auth | PIN haché avec `rounds => 12` codé en dur alors que le cast `hashed` vérifie `BCRYPT_ROUNDS` : 500 dès que la valeur diffère. | `Hash::make()` utilise la configuration. |
| 15 | Back-office | Connexion admin ouverte à **tout** compte possédant un e-mail. | `attempt()` limité à `role = admin` et `is_active = true`. |
| 16 | Back-office | Recherches utilisateurs et retraits en `ilike` (PostgreSQL) → erreur SQL sur MySQL ; filtre retraits sur la mauvaise table. | `like` et filtre via `merchant` / `merchant.user`. |
| 17 | KYC | Pièces liées par `Storage::url()` sur le disque privé : 403, documents jamais affichés à l'admin. | `Storage::temporaryUrl()` (15 min) ; les fichiers restent privés. |
| 18 | Statistiques | `thisMonth()` filtrait le mois sans l'année. | Ajout de `whereYear`. |
| 19 | Seeders | `AdminSeeder` échouait à la seconde exécution (contrainte unique). | `firstOrCreate`. |
| 20 | TLS | Vérification TLS désactivée (`withoutVerifying`) vers le fournisseur de paiement. | Vérification active par défaut (`YENGAPAY_VERIFY_SSL`). |

## Corrections — landing page

| Défaut | Correction |
|---|---|
| Restes de l'ancien nom « QR Pay » : titre, meta, textes, pied de page, e-mail `contact@qrpay.ci` | Remplacés par IvoirePay / `contact@ivoirepay.ci` ; meta complétée avec les 5 portefeuilles. |
| Débordement horizontal de 84 px (320–768 px) causé par l'animation `fade-left` de la carte contact | `overflow-x-clip` sur la section ; design inchangé. |

Vérifié avec Playwright : aucune erreur JavaScript ni requête en échec, ancres de navigation fonctionnelles, menu mobile, formulaire de contact (message de succès), toutes les images chargées, aucun débordement de 320 à 1440 px.

## Corrections — application Flutter

| Défaut | Correction |
|---|---|
| URL LocalTunnel codée en dur à 4 endroits | `lib/core/config/api_config.dart` + `--dart-define=API_BASE_URL` |
| `downloadReceipt` : `'\${dir.path}/…\$reference'` échappé, d'où un chemin littéral invalide | Interpolation corrigée |
| PIN de retrait non transmis | Envoyé à l'API ; message serveur affiché (« PIN incorrect. », « Solde insuffisant. ») |
| `LogInterceptor` actif en release : PIN, OTP et jeton dans logcat | Activé uniquement en `kDebugMode` |
| Test par défaut « Counter increments » sans rapport avec l'application | Remplacé par 7 tests réels |

Modifications produites par l'outillage Flutter 3.47 (conservées, nécessaires au build avec ce SDK) : `pubspec.lock`, drapeaux du migrateur dans `android/gradle.properties`, exclusions dans `analysis_options.yaml`, registres de plugins générés.

**Chaîne de build Android** : le build échouait avec Flutter 3.47, dont la validation exige des versions minimales. Mises à jour minimales, chacune démontrée par l'erreur du plugin Flutter :

| Composant | Avant | Après | Minimum Flutter 3.47 |
|---|---|---|---|
| Gradle wrapper | 8.12 | 8.14.3 | 8.14 |
| Android Gradle Plugin | 8.9.1 | 8.11.1 | 8.11.1 |
| Kotlin Gradle Plugin | 2.1.0 | 2.2.20 | 2.2.20 |

L'APK debug a ensuite été compilé (`BUILD SUCCESSFUL`), installé sur un Samsung Galaxy A04 (Android 14) et testé : splash, onboarding, saisie du numéro, OTP, PIN (pavé mélangé), puis accueil avec les transactions chargées depuis l'API.

## Tests exécutés

| Suite | Résultat | Commande |
|---|---|---|
| Laravel (PHPUnit, SQLite mémoire) | **39 réussis**, 0 échec | `cd backend && php artisan test` |
| Flutter | **7 réussis**, 0 échec | `cd mobile && flutter test` |
| `flutter analyze` | 0 erreur ; 22 avertissements et 63 infos préexistants | `cd mobile && flutter analyze` |
| Migrations MySQL (MariaDB 10.4) | toutes appliquées sur `ivoirepay_portfolio` | `php artisan migrate` |
| Smoke test API réel | login PIN, `/api/user`, historique, info marchand, dashboard, QR (SVG servi), paiement Orange simulé, re-confirmation refusée, webhook non signé → 401, retrait mauvais PIN → 403, reçu PDF | curl sur `php artisan serve` |
| Landing (Playwright) | aucune erreur, voir ci-dessus | script Playwright |
| Application Android | parcours connexion → accueil OK sur appareil réel | `gradlew assembleDebug` + `adb install` |

Couverture des tests Laravel ajoutés : `PaymentFlowTest` (12), `WithdrawalFlowTest` (7), `AuthAndKycFlowTest` (10), `AdminPanelTest` (8), plus les 2 exemples existants.

**Non exécutable dans cet environnement** : tests de concurrence réelle (deux requêtes simultanées sur MySQL). Les verrous sont en place et les scénarios séquentiels équivalents (rejeu, double job, double annulation) sont testés. L'intégration réelle Yengapay et Africa's Talking n'a pas été testée : clés non fournies, et volontairement mockée.

## Données de démonstration

`php artisan db:seed --class=DemoSeeder` : 1 admin, 1 client, 3 commerçants (2 approuvés avec QR, 1 en attente), 8 paiements sur 7 jours, 2 retraits, notifications. Numéros `+225 00 00 xx xx xx` (aucun préfixe d'opérateur), pièces d'identité fictives (`DEMO-CNI-…`), aucun document KYC réel. Soldes calculés à partir des transactions et retraits.
