import 'package:flutter/material.dart';
import 'package:hrms_mobile/services/api_service.dart';
import 'package:hrms_mobile/screens/login_screen.dart';
import 'package:hrms_mobile/screens/leave_screen.dart';
import 'package:hrms_mobile/screens/attendance_screen.dart';
import 'package:hrms_mobile/screens/payslip_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? _profile;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  void _loadProfile() async {
    final profile = await ApiService.getProfile();
    setState(() {
      _profile = profile;
      _isLoading = false;
    });
  }

  void _handleLogout() async {
    await ApiService.logout();
    if (mounted) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Theme.of(context).primaryColor;
    final goldColor = Theme.of(context).colorScheme.secondary;

    return Scaffold(
      appBar: AppBar(
        title: const Text('MYS ESS Portal',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        backgroundColor: primaryColor,
        actions: [
          IconButton(
            icon: const Icon(Icons.logout, color: Colors.white),
            onPressed: _handleLogout,
            tooltip: 'Logout',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Profile Card
                  Card(
                    color: primaryColor,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                    child: Padding(
                      padding: const EdgeInsets.all(20.0),
                      child: Column(
                        children: [
                          CircleAvatar(
                            radius: 40,
                            backgroundImage: NetworkImage(
                                (_profile?['photo_url'] ?? '')
                                    .toString()
                                    .replaceAll(
                                        'http://localhost',
                                        ApiService.baseUrl
                                            .replaceAll('/api/v1', ''))),
                            backgroundColor: Colors.white,
                          ),
                          const SizedBox(height: 12),
                          Text(
                            '${_profile?['first_name'] ?? '—'} ${_profile?['last_name'] ?? '—'}',
                            style: const TextStyle(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.bold),
                          ),
                          Text(
                            _profile?['designation'] ?? '—',
                            style: TextStyle(
                                color: goldColor,
                                fontSize: 13,
                                fontWeight: FontWeight.w500),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'ID: ${_profile?['employee_no'] ?? '—'}',
                            style: TextStyle(
                                color: Colors.white.withValues(alpha: 0.8),
                                fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Menu Options
                  const Text('Quick Operations',
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 12),

                  GridView.count(
                    crossAxisCount: 2,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    children: [
                      _buildMenuCard(
                        context,
                        title: 'Leave Requests',
                        icon: Icons.calendar_today_outlined,
                        color: const Color(0xFFC9A84C),
                        onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) => const LeaveScreen())),
                      ),
                      _buildMenuCard(
                        context,
                        title: 'Attendance logs',
                        icon: Icons.access_time_outlined,
                        color: const Color(0xFF4E60FF),
                        onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) => const AttendanceScreen())),
                      ),
                      _buildMenuCard(
                        context,
                        title: 'My Payslips',
                        icon: Icons.receipt_long_outlined,
                        color: Colors.teal,
                        onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) => const PayslipScreen())),
                      ),
                    ],
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildMenuCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      elevation: 2,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 40, color: color),
              const SizedBox(height: 12),
              Text(
                title,
                textAlign: TextAlign.center,
                style:
                    const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
