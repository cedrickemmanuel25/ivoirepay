# IvoirePay — backend Laravel

API REST (`/api`), back-office (`/admin`) et landing page (`/`) d'IvoirePay.
Documentation complète : [README racine](../README.md).

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate && php artisan storage:link
php artisan db:seed --class=DemoSeeder
php artisan serve --host=0.0.0.0 --port=8000
php artisan test
```

`YENGAPAY_SIMULATE=true` (par défaut en `APP_ENV=local`) : paiements et retraits réglés localement, aucun appel au fournisseur.
