allprojects {
    repositories {
        google()
        mavenCentral()
    }
}

// Compatibility shim: Some plugins (e.g. geolocator_android) still reference
// flutter.compileSdkVersion / flutter.minSdkVersion which is not provided
// automatically by AGP 8.x for library subprojects.
open class FlutterCompat {
    var compileSdkVersion: Int = 36
    var minSdkVersion: Int = 24
    var targetSdkVersion: Int = 35
    var ndkVersion: String = "27.0.12077973"
    var versionCode: Int = 1
    var versionName: String = "1.0.0"
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)

    // Register flutter extension for plugin subprojects that need it
    if (project.name != "app") {
        project.extensions.create("flutter", FlutterCompat::class.java)
    }
}
subprojects {
    project.evaluationDependsOn(":app")
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
