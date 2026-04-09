# ProGuard Rules for Einfach Laden App
-keepattributes *Annotation*

# Flutter
-keep class io.flutter.** { *; }
-keep class io.flutter.plugins.** { *; }

# Google Maps
-keep class com.google.android.gms.maps.** { *; }

# Firebase
-keep class com.google.firebase.** { *; }

# Gson / JSON
-keepattributes Signature
-keepattributes *Annotation*
-keep class sun.misc.Unsafe { *; }

# Play Core (deferred components)
-dontwarn com.google.android.play.core.**
