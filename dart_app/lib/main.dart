import 'package:flutter/material.dart';
import 'constants/app_colors.dart';
import 'constants/app_config.dart';
import 'screens/splash_screen.dart';

// main.dart
// จุดเริ่มต้นการทำงานของแอพพลิเคชันชมรมเพื่อนภูผา (Phupha Club App)
// มหาวิทยาลัยราชภัฏเชียงใหม่

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // โหลดแคชเดิมและซิงค์ลิงก์เซิร์ฟเวอร์อัตโนมัติจาก GitHub
  await AppConfig.init();
  runApp(const PhuphaClubApp());
}

class PhuphaClubApp extends StatelessWidget {
  const PhuphaClubApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'ชมรมเพื่อนภูผา',
      debugShowCheckedModeBanner: false,

      // กำหนดธีมสีของแอพพลิเคชันให้สอดคล้องกับระบบเว็บเดิม
      theme: ThemeData(
        useMaterial3: true,
        primaryColor: AppColors.primary,
        scaffoldBackgroundColor: AppColors.bgMain,
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.primary,
          primary: AppColors.primary,
          secondary: AppColors.secondary,
          surface: AppColors.surface,
        ),

        // กำหนดรูปแบบ AppBar เริ่มต้น ให้เป็นพื้นหลังสีขาว ตัวอักษรสีเขียวเข้มตามระบบเว็บ
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.white,
          foregroundColor: AppColors.primaryDark,
          elevation: 0,
          scrolledUnderElevation: 0,
          centerTitle: true,
          titleTextStyle: TextStyle(
            fontFamily: 'Prompt',
            fontSize: 17,
            fontWeight: FontWeight.bold,
            color: AppColors.primaryDark,
          ),
        ),

        // กำหนดรูปแบบการ์ด
        cardTheme: CardThemeData(
          color: Colors.white,
          elevation: 1,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),

        // กำหนดรูปแบบฟอนต์เริ่มต้น
        fontFamily: 'Prompt',
      ),

      // หน้าจอเริ่มต้นที่จะแสดงผล
      home: const SplashScreen(),
    );
  }
}
