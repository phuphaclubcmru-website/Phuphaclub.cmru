// app_config.dart
// กำหนดค่าคอนฟิกพื้นฐานสำหรับการเชื่อมต่อ API และไฟล์ของระบบชมรมเพื่อนภูผา
class AppConfig {
  // ชื่อของแอพพลิเคชัน
  static const String appName = 'ชมรมเพื่อนภูผา';
  static const String universityName = 'มหาวิทยาลัยราชภัฏเชียงใหม่';

  // ที่อยู่เซิร์ฟเวอร์ Backend สำหรับการเชื่อมต่อ API บน XAMPP
  // 1. สำหรับ Android Emulator ใน Android Studio: ใช้ 'http://10.0.2.2/web_code' หรือ 'http://10.0.2.2/phupha-club/web_code'
  // 2. สำหรับมือถือจริงผ่าน Wi-Fi: ใช้ IP เครื่องคอมพิวเตอร์ เช่น 'http://10.0.0.155/web_code'
  // 3. สำหรับ Web / Chrome / Windows: ใช้ 'http://localhost/web_code'
  //กำหนด URL ตอน build เพื่อไม่ให้ APK ติดอยู่กับ tunnel ที่หมดอายุ
  static const String _serverUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue:
        'https://expensive-gym-stuart-lbs.trycloudflare.com/phupha-club/web_code',
  );
  static String get baseUrl => _serverUrl.trim().replaceFirst(RegExp(r'/+$'), '');

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
