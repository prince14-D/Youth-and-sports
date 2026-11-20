import 'package:flutter/material.dart';
import 'package:hrms_mobile/screens/dashboard_screen.dart';
import 'package:hrms_mobile/screens/welcome_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Check auth state
  final prefs = await SharedPreferences.getInstance();
  final token = prefs.getString('auth_token');

  runApp(HRMSApp(isLoggedIn: token != null));
}

class HRMSApp extends StatelessWidget {
  final bool isLoggedIn;

  const HRMSApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'MYS ESS Portal',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primaryColor: const Color(0xFF000A60), // Ministry Navy
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF000A60),
          primary: const Color(0xFF000A60),
          secondary: const Color(0xFFC9A84C), // Gold
        ),
        useMaterial3: true,
        fontFamily: 'Roboto',
      ),
      home: isLoggedIn ? const DashboardScreen() : const WelcomeScreen(),
    );
  }
}
