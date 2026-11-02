# Technologies — IvoirePay

Uniquement les technologies réellement utilisées par le code.

## Backend & web

| Domaine | Technologie | Usage dans IvoirePay |
|---|---|---|
| Langage / framework | PHP 8.2+, Laravel 12 | API REST, back-office, landing |
| Authentification API | Laravel Sanctum | jetons Bearer pour l'application mobile |
| Authentification web | guard de session `admin` | back-office réservé au rôle `admin` |
| ORM / base | Eloquent, MySQL / MariaDB | modèles, relations, transactions SQL, `lockForUpdate` |
| Asynchrone | Laravel Queues (driver `database`) | traitement des retraits, notifications groupées |
| Stockage | disque `public` / disque privé `local` | QR codes et avatars publics ; pièces KYC privées servies par URL signée |
| PDF / export | barryvdh/laravel-dompdf, maatwebsite/excel | reçus, export des transactions en PDF et CSV |
| QR code | simplesoftwareio/simple-qrcode | QR statique SVG par commerçant |
| Front web | Blade, Tailwind CSS (CDN), Alpine.js, AOS, Font Awesome | landing et back-office |
| Tests | PHPUnit 11, Mockery, `Http::fake`, `Queue::fake` | 39 tests de parcours critiques |

## Mobile

| Domaine | Technologie | Usage |
|---|---|---|
| Framework | Flutter, Dart 3.9 | application Android (également compilable pour iOS et le web) |
| État | flutter_riverpod 3 | authentification, PIN |
| Navigation | go_router | ~25 routes (auth, KYC, paiement, profil…) |
| Réseau | Dio | intercepteur Bearer, URL injectée par `--dart-define` |
| Sécurité | flutter_secure_storage, local_auth | jeton et téléphone chiffrés ; biométrie optionnelle |
| Fonctionnel | mobile_scanner, image_picker, fl_chart, pdf / printing, share_plus, flutter_svg, lottie, google_fonts | scan QR, upload KYC, graphiques, reçus, partage du QR |
| Tests | flutter_test | 7 tests (modèles, configuration API, widget PIN) |

## Intégrations externes

| Service | Rôle | État dans le dépôt |
|---|---|---|
| Yengapay | agrégateur de paiement mobile money (paiements, retraits, webhook HMAC) | **simulé** (`YENGAPAY_SIMULATE=true`) |
| Africa's Talking | SMS (OTP, notifications) | **simulé** (`AFRICAS_TALKING_REAL_SEND=false`) |

## Outillage

Git (monorepo), Composer, Flutter CLI, Playwright (audit et capture de la landing), GitHub Pages (démonstration statique).
