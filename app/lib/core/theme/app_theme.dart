import 'package:flutter/material.dart';

class AppTheme {
  static const _primaryColor = Color(0xFF2E7D32);
  static const _secondaryColor = Color(0xFF66BB6A);
  static const _errorColor = Color(0xFFD32F2F);

  // Partner-Aufteilung Farben (konsistent mit Website)
  static const Color operatorColor = Color(0xFF1B5E20);     // Betreiber/Infrastruktur – primary-800
  static const Color roamingColor = Color(0xFF388E3C);       // Roaming/Betrieb – primary-500
  static const Color paymentColor = Color(0xFFF57C00);       // Zahlungsabwicklung – accent/orange
  static const Color marginColor = Color(0xFF4CAF50);        // Unsere Marge – green-500

  static ThemeData get lightTheme => ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: _primaryColor,
          secondary: _secondaryColor,
          error: _errorColor,
          brightness: Brightness.light,
        ),
        fontFamily: 'Poppins',
        appBarTheme: const AppBarTheme(
          centerTitle: true,
          elevation: 0,
        ),
        cardTheme: CardThemeData(
          elevation: 2,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        ),
      );

  static ThemeData get darkTheme => ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: _primaryColor,
          secondary: _secondaryColor,
          error: _errorColor,
          brightness: Brightness.dark,
        ),
        fontFamily: 'Poppins',
        appBarTheme: const AppBarTheme(
          centerTitle: true,
          elevation: 0,
        ),
        cardTheme: CardThemeData(
          elevation: 2,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        ),
      );
}
