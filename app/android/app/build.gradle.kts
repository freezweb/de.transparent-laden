plugins {
    id("com.android.application")
    id("kotlin-android")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    namespace = "de.einfachladen.einfach_laden"
    compileSdk = 36
    ndkVersion = "25.1.8937393"  // Auf Jenkins installierte Version - verhindert Auto-Download

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = JavaVersion.VERSION_17.toString()
    }

    defaultConfig {
        applicationId = "de.einfachladen.einfach_laden"
        minSdk = 24
        targetSdk = 35
        versionCode = flutter.versionCode
        versionName = flutter.versionName

        multiDexEnabled = true
    }

    signingConfigs {
        create("release") {
            // Ausschließlich Jenkins withCredentials – kein lokaler Keystore
            val envKeystoreFile = System.getenv("KEYSTORE_FILE")
            if (envKeystoreFile != null && File(envKeystoreFile).exists()) {
                storeFile = File(envKeystoreFile)
                storePassword = System.getenv("KEYSTORE_PASSWORD")
                keyAlias = System.getenv("KEY_ALIAS")
                keyPassword = System.getenv("KEY_PASSWORD")
                println("Using Release Keystore: $envKeystoreFile")
            }
        }
    }

    buildTypes {
        release {
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")

            // Release-Signierung NUR wenn KEYSTORE_FILE gesetzt (= Jenkins)
            signingConfig = if (System.getenv("KEYSTORE_FILE") != null) {
                signingConfigs.getByName("release")
            } else {
                signingConfigs.getByName("debug")
            }
        }
    }
}

flutter {
    source = "../.."
}
