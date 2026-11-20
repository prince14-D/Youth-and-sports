import 'package:flutter_test/flutter_test.dart';
import 'package:hrms_mobile/main.dart';

void main() {
  testWidgets('App loads and shows welcome screen when logged out',
      (WidgetTester tester) async {
    await tester.pumpWidget(const HRMSApp(isLoggedIn: false));

    expect(find.text('MINISTRY OF YOUTH & SPORTS'), findsOneWidget);
    expect(find.text('Login'), findsOneWidget);
  });
}
