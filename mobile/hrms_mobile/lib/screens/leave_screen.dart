import 'package:flutter/material.dart';
import 'package:hrms_mobile/services/api_service.dart';

class LeaveScreen extends StatefulWidget {
  const LeaveScreen({super.key});

  @override
  State<LeaveScreen> createState() => _LeaveScreenState();
}

class _LeaveScreenState extends State<LeaveScreen> {
  List<dynamic> _leaves = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadLeaves();
  }

  void _loadLeaves() async {
    final list = await ApiService.getLeaves();
    setState(() {
      _leaves = list;
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Leave Management', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        backgroundColor: Theme.of(context).primaryColor,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _leaves.isEmpty
              ? const Center(child: Text('No leave applications found.'))
              : ListView.builder(
                  padding: const EdgeInsets.all(12),
                  itemCount: _leaves.length,
                  itemBuilder: (context, index) {
                    final item = _leaves[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      child: ListTile(
                        title: Text(
                          item['leave_type']?['name'] ?? 'Leave Request',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        subtitle: Text(
                          '${item['start_date']} to ${item['end_date']}\nDuration: ${item['days_requested']} days',
                          style: const TextStyle(fontSize: 12),
                        ),
                        trailing: Chip(
                          label: Text(
                            item['status'] ?? 'Pending',
                            style: TextStyle(
                              color: _getStatusColor(item['status']),
                              fontWeight: FontWeight.bold,
                              fontSize: 11,
                            ),
                          ),
                          backgroundColor: _getStatusBg(item['status']),
                        ),
                      ),
                    );
                  },
                ),
    );
  }

  Color _getStatusColor(String? status) {
    switch (status) {
      case 'Approved':
        return Colors.green.shade800;
      case 'Rejected':
        return Colors.red.shade800;
      default:
        return Colors.orange.shade800;
    }
  }

  Color _getStatusBg(String? status) {
    switch (status) {
      case 'Approved':
        return Colors.green.shade50;
      case 'Rejected':
        return Colors.red.shade50;
      default:
        return Colors.orange.shade50;
    }
  }
}
