import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

// app_config.dart
// กำหนดค่าคอนฟิกพื้นฐานสำหรับการเชื่อมต่อ API และไฟล์ของระบบชมรมเพื่อนภูผา
// รองรับระบบ Auto-Sync ดึง URL ล่าสุดจาก GitHub เพื่อให้ใช้งานได้ตลอดไปโดยไม่ต้อง Build แอพใหม่
class AppConfig {
  // ชื่อของแอพพลิเคชัน
  static const String appName = 'ชมรมเพื่อนภูผา';
  static const String universityName = 'มหาวิทยาลัยราชภัฏเชียงใหม่';

  // ค่า URL เริ่มต้นสำรอง
  static const String _defaultUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue:
        'https://caribbean-later-senior-vary.trycloudflare.com/phupha-club/web_code',
  );

  // ตัวแปรเก็บ URL ปัจจุบันที่ใช้งานจริงในหน่วยความจำ
  static String _runtimeUrl = _defaultUrl;

  // คีย์สำหรับบันทึกลงแคชของเครื่อง
  static const String _prefKey = 'phupha_cached_server_url';

  // GitHub API endpoint สำหรับอ่านลิงก์ล่าสุดแบบ Real-time (0 cache delay)
  static const String _syncEndpoint =
      'https://api.github.com/repos/phuphaclubcmru-website/Phuphaclub.cmru/contents/current_tunnel.txt';

  static String get baseUrl => _runtimeUrl.trim().replaceFirst(RegExp(r'/+$'), '');

  static bool get isConfigured {
    final uri = Uri.tryParse(baseUrl);
    return uri != null &&
        uri.hasAuthority &&
        (uri.scheme == 'https' || uri.scheme == 'http');
  }

  // URL สำหรับเรียกใช้งานไฟล์ API หลัก
  static String get apiUrl => '$baseUrl/api.php';

  // URL สำหรับแสดงผลรูปภาพที่อัปโหลดไว้บนเซิร์ฟเวอร์
  static String get uploadsUrl => '$baseUrl/uploads';

  // ฟังก์ชันเริ่มต้น: โหลดแคชเดิมและดึงลิงก์ล่าสุดจากคลาวด์อัตโนมัติ
  static Future<void> init() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cached = prefs.getString(_prefKey);
      if (cached != null && cached.trim().isNotEmpty) {
        _runtimeUrl = cached.trim();
      }
    } catch (_) {}

    // ซิงค์ลิงก์ล่าสุดในพื้นหลัง
    await syncLatestUrl();
  }

  // ดึงลิงก์ล่าสุดจาก GitHub API ทันที
  static Future<String> syncLatestUrl() async {
    try {
      final response = await http.get(
        Uri.parse(_syncEndpoint),
        headers: {
          'Accept': 'application/vnd.github.v3+json',
          'User-Agent': 'PhuphaClub-App',
        },
      ).timeout(const Duration(seconds: 4));

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = jsonDecode(response.body);
        final String rawContent = (data['content'] ?? '').replaceAll('\n', '').replaceAll('\r', '').trim();
        if (rawContent.isNotEmpty) {
          final decoded = utf8.decode(base64.decode(rawContent)).trim();
          if (decoded.startsWith('http://') || decoded.startsWith('https://')) {
            _runtimeUrl = decoded;
            final prefs = await SharedPreferences.getInstance();
            await prefs.setString(_prefKey, decoded);
            return decoded;
          }
        }
      }
    } catch (_) {}
    return baseUrl;
  }

  // ฟังก์ชันช่วยสร้าง URL เต็มสำหรับรูปภาพ
  static String get FullImageUrl => '$uploadsUrl/';
  static String getFullImageUrl(String? relativePath) {
    if (relativePath == null || relativePath.isEmpty) {
      return '';
    }
    if (relativePath.startsWith('http://') ||
        relativePath.startsWith('https://')) {
      return relativePath;
    }
    // หากขึ้นต้นด้วย uploads/ ให้ต่อกับ baseUrl ได้โดยตรง
    if (relativePath.startsWith('uploads/')) {
      return '$baseUrl/$relativePath';
    }
    return '$uploadsUrl/$relativePath';
  }
}
