# IvoirePay — application mobile Flutter

Application client et commerçant d'IvoirePay. Documentation complète : [README racine](../README.md).

```bash
flutter pub get
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api   # émulateur Android
flutter test
```

L'URL de l'API est définie dans [`lib/core/config/api_config.dart`](lib/core/config/api_config.dart) ; aucune URL de tunnel n'est codée en dur.
