/// Backend endpoint, injected at build time — no tunnel URL hard-coded in the app.
///
///   flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api
///
/// Default targets the Laravel dev server (`php artisan serve`) seen from the
/// Android emulator (10.0.2.2 = host machine). For a physical device use
/// `adb reverse tcp:8000 tcp:8000` with http://127.0.0.1:8000/api, or the host LAN IP.
class ApiConfig {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );

  /// Public files served by Laravel (`php artisan storage:link`), e.g. avatars.
  static String get storageUrl =>
      '${baseUrl.replaceFirst(RegExp(r'/api/?$'), '')}/storage';
}
