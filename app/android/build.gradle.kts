allprojects {
    repositories {
        google()
        mavenCentral()
    }
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)
}
subprojects {
    project.evaluationDependsOn(":app")
}

// ndkVersion fuer ALLE Android-Subprojekte auf die installierte Version setzen
// Verhindert Auto-Download von NDK 28 (Lizenz-Problem auf CI)
// gradle.projectsEvaluated laeuft NACH allen evaluationDependsOn
gradle.projectsEvaluated {
    rootProject.subprojects.forEach { sub ->
        sub.extensions.findByType(com.android.build.gradle.BaseExtension::class.java)?.apply {
            ndkVersion = "25.1.8937393"
        }
    }
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
