// Unit / widget tests for the IvoirePay mobile app.
// They run offline: no backend call, no secure storage plugin.

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:ivoirepay_mobile/core/config/api_config.dart';
import 'package:ivoirepay_mobile/core/models/transaction_model.dart';
import 'package:ivoirepay_mobile/core/models/user_model.dart';
import 'package:ivoirepay_mobile/features/auth/widgets/pin_dots_row.dart';

void main() {
  group('ApiConfig', () {
    test('defaults to the Laravel dev server seen from the Android emulator', () {
      expect(ApiConfig.baseUrl, 'http://10.0.2.2:8000/api');
    });

    test('derives the public storage URL from the API URL', () {
      expect(ApiConfig.storageUrl, 'http://10.0.2.2:8000/storage');
    });
  });

  group('User.fromJson', () {
    test('parses the /api/user payload returned by Laravel', () {
      final user = User.fromJson({
        'id': 2,
        'name': 'Client Démo',
        'phone': '+2250000000001',
        'role': 'client',
        'avatar': null,
        'kyc_status': null,
        'has_pin': true,
      });

      expect(user.role, 'client');
      expect(user.hasPin, isTrue);
      expect(user.kycStatus, isNull);
    });

    test('treats a missing has_pin flag as false (merchant before PIN setup)', () {
      final user = User.fromJson({'id': 3, 'name': 'Commerçant', 'phone': '+2250000000002', 'role': 'merchant', 'kyc_status': 'approved'});
      expect(user.hasPin, isFalse);
      expect(user.kycStatus, 'approved');
    });
  });

  group('Transaction.fromJson', () {
    test('parses a paginated /api/transactions item (decimal strings from MySQL)', () {
      final tx = Transaction.fromJson({
        'id': 9,
        'reference': '5a9a5534-fd78-473c-8915-19518de5a86d',
        'merchant_name': 'Boutique Démo Plateau',
        'amount': '12000.00',
        'commission_amount': '0.00',
        'status': 'success',
        'wallet_type': 'orange',
        'created_at': '2026-11-02T12:30:00.000000Z',
      });

      expect(tx.id, '9');
      expect(tx.amount, 12000);
      expect(tx.merchantName, 'Boutique Démo Plateau');
      expect(tx.walletType, 'orange');
      expect(tx.createdAt.year, 2026);
    });

    test('falls back safely when optional fields are missing', () {
      final tx = Transaction.fromJson({'id': 1});
      expect(tx.status, 'pending');
      expect(tx.amount, 0);
      expect(tx.merchantName, 'Commerçant Inconnu');
    });
  });

  testWidgets('PinDotsRow renders one dot per PIN digit', (tester) async {
    await tester.pumpWidget(const MaterialApp(home: Scaffold(body: PinDotsRow(enteredDigits: 2))));
    // 4-digit PIN, matching the backend `size:4` rule on payment confirmation
    expect(find.byType(AnimatedContainer), findsNWidgets(4));
  });
}
