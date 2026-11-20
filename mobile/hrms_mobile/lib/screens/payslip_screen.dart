import 'package:flutter/material.dart';
import 'package:hrms_mobile/services/api_service.dart';

class PayslipScreen extends StatefulWidget {
  const PayslipScreen({super.key});

  @override
  State<PayslipScreen> createState() => _PayslipScreenState();
}

class _PayslipScreenState extends State<PayslipScreen> {
  List<dynamic> _payslips = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadPayslips();
  }

  void _loadPayslips() async {
    final list = await ApiService.getPayslips();
    setState(() {
      _payslips = list;
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Payslips', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        backgroundColor: Theme.of(context).primaryColor,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _payslips.isEmpty
              ? const Center(child: Text('No payslips issued yet.'))
              : ListView.builder(
                  padding: const EdgeInsets.all(12),
                  itemCount: _payslips.length,
                  itemBuilder: (context, index) {
                    final item = _payslips[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Text(
                              item['period']?['name'] ?? 'Salary Cycle',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                            ),
                            const Divider(height: 20),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text('Net Pay (LRD):', style: TextStyle(color: Colors.grey, fontSize: 13)),
                                Text(
                                  'LRD ${item['net_pay_lrd']}',
                                  style: const TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text('Net Pay (USD):', style: TextStyle(color: Colors.grey, fontSize: 13)),
                                Text(
                                  'USD ${item['net_pay_usd']}',
                                  style: const TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.bold, color: Colors.amber),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
    );
  }
}
