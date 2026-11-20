import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Update this URL to point to your backend API server (e.g. ngrok or local network IP)
  static const String baseUrl = 'http://192.168.31.54:8000/api/v1';

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  static Future<Map<String, String>> _getHeaders() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', data['token']);
      return {'success': true};
    }
    return {'success': false, 'message': data['message'] ?? 'Login failed'};
  }

  static Future<void> logout() async {
    final headers = await _getHeaders();
    await http.post(Uri.parse('$baseUrl/auth/logout'), headers: headers);
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  static Future<Map<String, dynamic>?> getProfile() async {
    final headers = await _getHeaders();
    final response = await http.get(Uri.parse('$baseUrl/my/profile'), headers: headers);
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    return null;
  }

  static Future<List<dynamic>> getLeaves() async {
    final headers = await _getHeaders();
    final response = await http.get(Uri.parse('$baseUrl/my/leave'), headers: headers);
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    return [];
  }

  static Future<bool> applyLeave(Map<String, dynamic> leaveData) async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/my/leave/apply'), // Point to a store API if implemented
      headers: headers,
      body: jsonEncode(leaveData),
    );
    return response.statusCode == 200 || response.statusCode == 201;
  }

  static Future<List<dynamic>> getAttendance() async {
    final headers = await _getHeaders();
    final response = await http.get(Uri.parse('$baseUrl/my/attendance'), headers: headers);
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    return [];
  }

  static Future<Map<String, dynamic>> punchAttendance(double lat, double lng, String deviceId, String method) async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/my/attendance/punch'),
      headers: headers,
      body: jsonEncode({
        'latitude': lat,
        'longitude': lng,
        'device_id': deviceId,
        'verification_method': method,
      }),
    );
    return jsonDecode(response.body);
  }

  static Future<List<dynamic>> getPayslips() async {
    final headers = await _getHeaders();
    final response = await http.get(Uri.parse('$baseUrl/my/payslips'), headers: headers);
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    return [];
  }
}
