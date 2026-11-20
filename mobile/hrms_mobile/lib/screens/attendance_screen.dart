import 'package:flutter/material.dart';
import 'package:hrms_mobile/services/api_service.dart';
import 'package:hrms_mobile/services/location_service.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  List<dynamic> _attendance = [];
  bool _isLoading = true;
  bool _isPunching = false;

  @override
  void initState() {
    super.initState();
    _loadAttendance();
  }

  void _loadAttendance() async {
    final list = await ApiService.getAttendance();
    setState(() {
      _attendance = list;
      _isLoading = false;
    });
  }

  void _handlePunch(bool withinFence) async {
    setState(() {
      _isPunching = true;
    });

    final mockLoc = LocationService.getMockLocation(withinFence: withinFence);
    final lat = mockLoc['latitude']!;
    final lng = mockLoc['longitude']!;

    final res = await ApiService.punchAttendance(
      lat,
      lng,
      'device_uuid_testing_123',
      'gps',
    );

    setState(() {
      _isPunching = false;
    });

    if (mounted) {
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: Text(res['success'] == true ? 'Punch Logged' : 'Punch Blocked',
              style: TextStyle(
                  color: res['success'] == true ? Colors.green : Colors.red)),
          content: Text(res['message'] ?? 'Unable to log check-in.'),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(context);
                _loadAttendance();
              },
              child: const Text('OK'),
            ),
          ],
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Theme.of(context).primaryColor;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Attendance Logs',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        backgroundColor: primaryColor,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Geofence Punch card
                Card(
                  margin: const EdgeInsets.all(12),
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        const Text(
                          'GPS Check-In / Check-Out',
                          style: TextStyle(
                              fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'You must be within 100 meters of the Ministry compound to punch attendance.',
                          style: TextStyle(fontSize: 12, color: Colors.grey),
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: ElevatedButton.icon(
                                onPressed: _isPunching
                                    ? null
                                    : () => _handlePunch(true),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.green,
                                  foregroundColor: Colors.white,
                                ),
                                icon: const Icon(Icons.location_on),
                                label: const Text('Inside Comp.'),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: ElevatedButton.icon(
                                onPressed: _isPunching
                                    ? null
                                    : () => _handlePunch(false),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.red,
                                  foregroundColor: Colors.white,
                                ),
                                icon: const Icon(Icons.location_off),
                                label: const Text('Outside Comp.'),
                              ),
                            ),
                          ],
                        )
                      ],
                    ),
                  ),
                ),

                const Divider(),

                // Logs
                Expanded(
                  child: _attendance.isEmpty
                      ? const Center(child: Text('No attendance logs found.'))
                      : ListView.builder(
                          padding: const EdgeInsets.all(12),
                          itemCount: _attendance.length,
                          itemBuilder: (context, index) {
                            final item = _attendance[index];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 8),
                              child: ListTile(
                                leading: CircleAvatar(
                                  backgroundColor:
                                      _getStatusColor(item['status'])
                                          .withValues(alpha: 0.1),
                                  child: Icon(
                                    _getStatusIcon(item['status']),
                                    color: _getStatusColor(item['status']),
                                  ),
                                ),
                                title: Text(
                                  item['attendance_date'] ?? '—',
                                  style: const TextStyle(
                                      fontWeight: FontWeight.bold),
                                ),
                                subtitle: Text(
                                  'In: ${item['check_in'] ?? '—'}  |  Out: ${item['check_out'] ?? '—'}',
                                  style: const TextStyle(fontSize: 12),
                                ),
                                trailing: Text(
                                  item['status'] ?? 'Present',
                                  style: TextStyle(
                                    color: _getStatusColor(item['status']),
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
    );
  }

  IconData _getStatusIcon(String? status) {
    switch (status) {
      case 'Late':
        return Icons.warning_amber_rounded;
      case 'Absent':
        return Icons.cancel_outlined;
      default:
        return Icons.check_circle_outline_rounded;
    }
  }

  Color _getStatusColor(String? status) {
    switch (status) {
      case 'Late':
        return Colors.orange;
      case 'Absent':
        return Colors.red;
      default:
        return Colors.green;
    }
  }
}
