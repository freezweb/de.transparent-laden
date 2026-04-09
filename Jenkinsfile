pipeline {
    agent any

    environment {
        // Android SDK
        ANDROID_HOME = 'C:\\Program Files (x86)\\Android\\android-sdk'

        // Java 17
        JAVA_HOME = 'C:\\Program Files\\Eclipse Adoptium\\jdk-17.0.16.8-hotspot'

        // Flutter SDK
        FLUTTER_HOME = 'C:\\flutter'

        // PATH erweitern
        PATH = "${env.FLUTTER_HOME}\\bin;${env.ANDROID_HOME}\\platform-tools;${env.ANDROID_HOME}\\build-tools\\35.0.0;${env.JAVA_HOME}\\bin;${env.PATH}"

        // Telegram Bot
        TELEGRAM_BOT_TOKEN = '7922836994:AAEGKibf1tchleSwLI4Ij5L0BHXUYLTyxSc'
        TELEGRAM_CHAT_ID = '-1002345619813'

        // SSH-Tool Pfad (plink.exe)
        PLINK_EXE = 'C:\\key\\plink.exe'
        PLINK_KEY = 'C:\\key\\key\\private.ppk'
        VERSION_CODE = "${env.BUILD_NUMBER}"
    }

    parameters {
        booleanParam(name: 'SKIP_TESTS', defaultValue: true, description: 'Flutter-Tests ueberspringen?')
        booleanParam(name: 'SEND_TELEGRAM', defaultValue: true, description: 'APK per Telegram versenden?')
        booleanParam(name: 'DEPLOY_BACKEND', defaultValue: true, description: 'Backend auf Server deployen?')
        booleanParam(name: 'BUILD_APK', defaultValue: true, description: 'Android APK bauen?')
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
                script {
                    env.GIT_COMMIT_SHORT = bat(returnStdout: true, script: '@git rev-parse --short HEAD').trim()
                    env.GIT_BRANCH_NAME = bat(returnStdout: true, script: '@git rev-parse --abbrev-ref HEAD').trim()
                    echo "Building commit ${env.GIT_COMMIT_SHORT} on branch ${env.GIT_BRANCH_NAME}"
                }
            }
        }

        stage('Setup Environment') {
            steps {
                script {
                    env.HAS_FLUTTER = fileExists("${env.FLUTTER_HOME}\\bin\\flutter.bat") ? 'true' : 'false'
                    env.HAS_PLINK = fileExists(env.PLINK_EXE) ? 'true' : 'false'
                }
                bat '''
                    echo ========================================
                    echo Environment Setup
                    echo ========================================
                    echo JAVA_HOME: %JAVA_HOME%
                    echo ANDROID_HOME: %ANDROID_HOME%
                    echo FLUTTER_HOME: %FLUTTER_HOME%
                    echo VERSION_CODE: %VERSION_CODE%
                    echo.
                    echo Tool-Verfuegbarkeit:
                    echo   Flutter: %HAS_FLUTTER%
                    echo   Plink/SSH: %HAS_PLINK%
                    echo.
                    java -version
                    echo.
                    if exist "%FLUTTER_HOME%\\bin\\flutter.bat" (
                        flutter --version
                    ) else (
                        echo WARNUNG: Flutter nicht gefunden unter %FLUTTER_HOME%
                        echo   -> Flutter-Stages werden uebersprungen
                    )
                    if not exist "%PLINK_EXE%" (
                        echo WARNUNG: plink.exe nicht gefunden unter %PLINK_EXE%
                        echo   -> SSH/Deploy-Stages werden uebersprungen
                    )
                    echo ========================================
                '''
            }
        }

        stage('Accept Android Licenses') {
            steps {
                bat '''
                    @echo off
                    if not exist "%ANDROID_HOME%\\licenses" mkdir "%ANDROID_HOME%\\licenses"
                    echo 24333f8a63b6825ea9c5514f83c2829b004d1fee > "%ANDROID_HOME%\\licenses\\android-sdk-license"
                    echo d56f5187479451eabf01fb78af6dfcb131a6481e > "%ANDROID_HOME%\\licenses\\android-sdk-preview-license"
                    echo 8933bad161af4178b1185d1a37fbf41ea5269c55 > "%ANDROID_HOME%\\licenses\\android-ndk-license"
                    echo Android Licenses erstellt
                '''
            }
        }

        // ============================================================
        // BACKEND STAGES
        // ============================================================

        stage('Backend: Lint & Test (Remote)') {
            when {
                expression { return params.DEPLOY_BACKEND && fileExists(env.PLINK_EXE) }
            }
            steps {
                bat '''
                    echo PHP Lint auf Server ausfuehren...
                    "%PLINK_EXE%" -i "%PLINK_KEY%" -batch root@profipos.de "cd /srv/www/git/de.einfach-laden/webserver && find app -name '*.php' -exec php -l {} \\; 2>&1 | grep -i error && echo PHP LINT FEHLER GEFUNDEN && exit 1 || echo Alle PHP-Dateien fehlerfrei"
                '''
            }
        }

        stage('Backend: Deploy to Server') {
            when {
                allOf {
                    expression { return params.DEPLOY_BACKEND && fileExists(env.PLINK_EXE) }
                    branch 'main'
                }
            }
            steps {
                script {
                    echo 'Deploying backend to profipos.de...'
                    bat '''
                        echo Deploying backend...
                        "%PLINK_EXE%" -i "%PLINK_KEY%" -batch root@profipos.de "cd /srv/www/git/de.einfach-laden && git pull origin main && cd webserver && composer install --no-dev --optimize-autoloader --no-interaction && php spark migrate --all && echo Deploy erfolgreich"
                    '''
                }
            }
        }

        stage('Backend: Smoke Test') {
            when {
                allOf {
                    expression { return params.DEPLOY_BACKEND }
                    branch 'main'
                }
            }
            steps {
                bat '''
                    echo Smoke Test...
                    curl -s -o nul -w "HTTP Status: %%{http_code}" https://transparent-laden.de/api/v1/health
                    echo.
                '''
            }
        }

        // ============================================================
        // FLUTTER / ANDROID STAGES
        // ============================================================

        stage('Flutter: Pub Get') {
            when {
                expression { return params.BUILD_APK && fileExists("${env.FLUTTER_HOME}\\bin\\flutter.bat") }
            }
            steps {
                dir('app') {
                    bat 'flutter pub get'
                }
            }
        }

        stage('Flutter: Analyze') {
            when {
                expression { return params.BUILD_APK && fileExists("${env.FLUTTER_HOME}\\bin\\flutter.bat") }
            }
            steps {
                dir('app') {
                    bat 'flutter analyze --no-fatal-infos || echo Analyze done with warnings'
                }
            }
        }

        stage('Flutter: Test') {
            when {
                allOf {
                    expression { return params.BUILD_APK && fileExists("${env.FLUTTER_HOME}\\bin\\flutter.bat") }
                    expression { return !params.SKIP_TESTS }
                }
            }
            steps {
                dir('app') {
                    bat 'flutter test'
                }
            }
        }

        stage('Flutter: Build Debug APK') {
            when {
                allOf {
                    expression { return params.BUILD_APK && fileExists("${env.FLUTTER_HOME}\\bin\\flutter.bat") }
                    not { branch 'main' }
                }
            }
            steps {
                dir('app') {
                    bat "flutter build apk --debug --build-number=%VERSION_CODE% --build-name=1.0.%VERSION_CODE%"
                }
            }
            post {
                success {
                    archiveArtifacts artifacts: 'app/build/app/outputs/flutter-apk/app-debug.apk', fingerprint: true, allowEmptyArchive: true
                }
            }
        }

        stage('Flutter: Build Release APK') {
            when {
                allOf {
                    expression { return params.BUILD_APK && fileExists("${env.FLUTTER_HOME}\\bin\\flutter.bat") }
                    branch 'main'
                }
            }
            steps {
                dir('app') {
                    withCredentials([
                        string(credentialsId: 'keystore-password', variable: 'KEYSTORE_PASSWORD'),
                        string(credentialsId: 'key-alias', variable: 'KEY_ALIAS'),
                        string(credentialsId: 'key-password', variable: 'KEY_PASSWORD')
                    ]) {
                        bat """
                            @echo off
                            set KEYSTORE_FILE=%WORKSPACE%\\einfach-laden-release.keystore
                            echo ========================================
                            echo Release Build mit autorisiertem Keystore
                            echo KEYSTORE_FILE=%KEYSTORE_FILE%
                            echo ========================================

                            if not exist "%KEYSTORE_FILE%" (
                                echo FEHLER: Autorisierter Keystore nicht gefunden: %KEYSTORE_FILE%
                                echo Der Keystore muss manuell auf dem Jenkins-Agent platziert werden.
                                exit /b 1
                            )

                            flutter build apk --release --build-number=%VERSION_CODE% --build-name=1.0.%VERSION_CODE%
                        """
                    }
                }
            }
            post {
                success {
                    archiveArtifacts artifacts: 'app/build/app/outputs/flutter-apk/app-release.apk', fingerprint: true, allowEmptyArchive: true
                }
            }
        }

        stage('Send APK to Telegram') {
            when {
                allOf {
                    expression { return params.BUILD_APK && fileExists("${env.FLUTTER_HOME}\\bin\\flutter.bat") }
                    expression { return params.SEND_TELEGRAM }
                }
            }
            steps {
                script {
                    def apkType = env.GIT_BRANCH_NAME == 'main' ? 'release' : 'debug'
                    def apkPath = "app\\build\\app\\outputs\\flutter-apk\\app-${apkType}.apk"

                    bat """
                        @echo off
                        echo Sende APK per Telegram...

                        curl -s -X POST "https://api.telegram.org/bot%TELEGRAM_BOT_TOKEN%/sendMessage" ^
                            -d "chat_id=%TELEGRAM_CHAT_ID%" ^
                            -d "text=Einfach Laden Build #${env.BUILD_NUMBER}%%0A%%0AVersion: 1.0.${env.BUILD_NUMBER}%%0ABranch: ${env.GIT_BRANCH_NAME}%%0ACommit: ${env.GIT_COMMIT_SHORT}%%0ATyp: ${apkType}%%0A%%0AAPK bereit zum Testen!"

                        curl -s -X POST "https://api.telegram.org/bot%TELEGRAM_BOT_TOKEN%/sendDocument" ^
                            -F "chat_id=%TELEGRAM_CHAT_ID%" ^
                            -F "document=@${apkPath}" ^
                            -F "caption=Einfach Laden v1.0.${env.BUILD_NUMBER} (${apkType})"
                    """
                }
            }
        }

        // ============================================================
        // CRON JOBS SETUP
        // ============================================================

        stage('Setup Cron Jobs') {
            when {
                allOf {
                    expression { return params.DEPLOY_BACKEND && fileExists(env.PLINK_EXE) }
                    branch 'main'
                }
            }
            steps {
                bat '''
                    echo Setting up cron jobs on server...
                    "%PLINK_EXE%" -i "%PLINK_KEY%" -batch root@profipos.de "cd /srv/www/git/de.einfach-laden/webserver && (crontab -l 2>/dev/null | grep -v 'spark ' ; echo '*/5 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark provider:sync >> /var/log/einfach-laden-cron.log 2>&1' ; echo '*/2 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark notifications:process >> /var/log/einfach-laden-cron.log 2>&1' ; echo '*/10 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark sessions:check-stale >> /var/log/einfach-laden-cron.log 2>&1' ; echo '*/15 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark sessions:recover-stuck >> /var/log/einfach-laden-cron.log 2>&1' ; echo '0 2 * * * cd /srv/www/git/de.einfach-laden/webserver && php spark billing:retry-pending >> /var/log/einfach-laden-cron.log 2>&1' ; echo '0 3 1 * * cd /srv/www/git/de.einfach-laden/webserver && php spark billing:subscription-invoices >> /var/log/einfach-laden-cron.log 2>&1' ; echo '0 4 * * 0 cd /srv/www/git/de.einfach-laden/webserver && php spark devices:cleanup >> /var/log/einfach-laden-cron.log 2>&1' ; echo '0 5 * * * cd /srv/www/git/de.einfach-laden/webserver && php spark notifications:cleanup >> /var/log/einfach-laden-cron.log 2>&1' ; echo '*/30 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark sessions:check-blocking >> /var/log/einfach-laden-cron.log 2>&1') | crontab - && echo Cron jobs eingerichtet"
                '''
            }
        }
    }

    post {
        success {
            script {
                bat """
                    curl -s -X POST "https://api.telegram.org/bot%TELEGRAM_BOT_TOKEN%/sendMessage" ^
                        -d "chat_id=%TELEGRAM_CHAT_ID%" ^
                        -d "text=Einfach Laden - Build Erfolgreich%%0A%%0ABuild: #${env.BUILD_NUMBER}%%0AVersion: 1.0.${env.BUILD_NUMBER}%%0ABranch: ${env.GIT_BRANCH_NAME}%%0ACommit: ${env.GIT_COMMIT_SHORT}"
                """
            }

            echo """
                ========================================
                BUILD ERFOLGREICH
                ========================================
                Build: #${env.BUILD_NUMBER}
                Branch: ${env.GIT_BRANCH_NAME}
                Commit: ${env.GIT_COMMIT_SHORT}
                Duration: ${currentBuild.durationString}
                ========================================
            """
        }

        failure {
            script {
                bat """
                    curl -s -X POST "https://api.telegram.org/bot%TELEGRAM_BOT_TOKEN%/sendMessage" ^
                        -d "chat_id=%TELEGRAM_CHAT_ID%" ^
                        -d "text=Einfach Laden - Build FEHLGESCHLAGEN%%0A%%0ABuild: #${env.BUILD_NUMBER}%%0ABranch: ${env.GIT_BRANCH_NAME}%%0ACommit: ${env.GIT_COMMIT_SHORT}%%0A%%0ABitte Jenkins-Log pruefen"
                """
            }
        }

        always {
            echo 'Pipeline abgeschlossen.'
        }
    }
}
