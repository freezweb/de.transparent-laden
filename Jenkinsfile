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

        // Version aus Build-Nummer
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
                bat '''
                    echo ========================================
                    echo Environment Setup
                    echo ========================================
                    echo JAVA_HOME: %JAVA_HOME%
                    echo ANDROID_HOME: %ANDROID_HOME%
                    echo FLUTTER_HOME: %FLUTTER_HOME%
                    echo VERSION_CODE: %VERSION_CODE%
                    echo.
                    java -version
                    echo.
                    flutter --version
                    echo ========================================
                '''
            }
        }

        stage('Accept Android Licenses') {
            steps {
                bat '''
                    @echo off
                    if not exist "%ANDROID_HOME%\\licenses" mkdir "%ANDROID_HOME%\\licenses"

                    set "SDKMANAGER_PATH="
                    if exist "%ANDROID_HOME%\\cmdline-tools\\latest\\bin\\sdkmanager.bat" (
                        set "SDKMANAGER_PATH=%ANDROID_HOME%\\cmdline-tools\\latest\\bin\\sdkmanager.bat"
                    )
                    if defined SDKMANAGER_PATH (
                        (echo y&echo y&echo y&echo y&echo y&echo y&echo y&echo y&echo y&echo y) | "%SDKMANAGER_PATH%" --licenses || echo Licenses done
                    ) else (
                        echo 24333f8a63b6825ea9c5514f83c2829b004d1fee > "%ANDROID_HOME%\\licenses\\android-sdk-license"
                        echo d56f5187479451eabf01fb78af6dfcb131a6481e > "%ANDROID_HOME%\\licenses\\android-sdk-preview-license"
                        echo 8933bad161af4178b1185d1a37fbf41ea5269c55 > "%ANDROID_HOME%\\licenses\\android-ndk-license"
                        echo Licenses created manually
                    )
                '''
            }
        }

        // ============================================================
        // BACKEND STAGES
        // ============================================================

        stage('Backend: Composer Install') {
            when {
                expression { return params.DEPLOY_BACKEND }
            }
            steps {
                dir('webserver') {
                    bat 'composer install --no-dev --optimize-autoloader --no-interaction'
                }
            }
        }

        stage('Backend: PHP Lint') {
            when {
                expression { return params.DEPLOY_BACKEND }
            }
            steps {
                dir('webserver') {
                    bat '''
                        @echo off
                        setlocal enabledelayedexpansion
                        set ERRORS=0
                        for /r app %%f in (*.php) do (
                            php -l "%%f" > nul 2>&1
                            if errorlevel 1 (
                                echo FEHLER: %%f
                                set /a ERRORS+=1
                            )
                        )
                        if !ERRORS! gtr 0 (
                            echo %ERRORS% PHP Dateien mit Fehlern gefunden!
                            exit /b 1
                        )
                        echo Alle PHP-Dateien fehlerfrei
                    '''
                }
            }
        }

        stage('Backend: Deploy to Server') {
            when {
                allOf {
                    expression { return params.DEPLOY_BACKEND }
                    branch 'main'
                }
            }
            steps {
                script {
                    echo 'Deploying backend to profipos.de...'
                    bat '''
                        echo Deploying backend...
                        "C:\\key\\plink.exe" -i "C:\\key\\key\\private.ppk" -batch root@profipos.de "cd /srv/www/git/de.einfach-laden && git pull origin main && cd webserver && composer install --no-dev --optimize-autoloader --no-interaction && php spark migrate --all && echo Deploy erfolgreich"
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
                expression { return params.BUILD_APK }
            }
            steps {
                dir('app') {
                    bat 'flutter pub get'
                }
            }
        }

        stage('Flutter: Analyze') {
            when {
                expression { return params.BUILD_APK }
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
                    expression { return params.BUILD_APK }
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
                    expression { return params.BUILD_APK }
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
                    expression { return params.BUILD_APK }
                    branch 'main'
                }
            }
            steps {
                dir('app') {
                    withCredentials([
                        string(credentialsId: 'einfach-laden-keystore-password', variable: 'KEYSTORE_PASSWORD'),
                        string(credentialsId: 'einfach-laden-key-alias', variable: 'KEY_ALIAS'),
                        string(credentialsId: 'einfach-laden-key-password', variable: 'KEY_PASSWORD')
                    ]) {
                        bat """
                            echo storePassword=%KEYSTORE_PASSWORD%> android\\key.properties
                            echo keyPassword=%KEY_PASSWORD%>> android\\key.properties
                            echo keyAlias=%KEY_ALIAS%>> android\\key.properties
                            echo storeFile=%WORKSPACE%\\einfach-laden-release.keystore>> android\\key.properties

                            flutter build apk --release --build-number=%VERSION_CODE% --build-name=1.0.%VERSION_CODE%
                        """
                    }
                }
            }
            post {
                success {
                    archiveArtifacts artifacts: 'app/build/app/outputs/flutter-apk/app-release.apk', fingerprint: true, allowEmptyArchive: true
                }
                always {
                    // key.properties nach Build entfernen
                    bat 'del /q app\\android\\key.properties 2>nul'
                }
            }
        }

        stage('Send APK to Telegram') {
            when {
                allOf {
                    expression { return params.BUILD_APK }
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
                    expression { return params.DEPLOY_BACKEND }
                    branch 'main'
                }
            }
            steps {
                bat '''
                    echo Setting up cron jobs on server...
                    "C:\\key\\plink.exe" -i "C:\\key\\key\\private.ppk" -batch root@profipos.de "cd /srv/www/git/de.einfach-laden/webserver && (crontab -l 2>/dev/null | grep -v 'spark ' ; echo '*/5 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark provider:sync >> /var/log/einfach-laden-cron.log 2>&1' ; echo '*/2 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark notifications:process >> /var/log/einfach-laden-cron.log 2>&1' ; echo '*/10 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark sessions:check-stale >> /var/log/einfach-laden-cron.log 2>&1' ; echo '*/15 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark sessions:recover-stuck >> /var/log/einfach-laden-cron.log 2>&1' ; echo '0 2 * * * cd /srv/www/git/de.einfach-laden/webserver && php spark billing:retry-pending >> /var/log/einfach-laden-cron.log 2>&1' ; echo '0 3 1 * * cd /srv/www/git/de.einfach-laden/webserver && php spark billing:subscription-invoices >> /var/log/einfach-laden-cron.log 2>&1' ; echo '0 4 * * 0 cd /srv/www/git/de.einfach-laden/webserver && php spark devices:cleanup >> /var/log/einfach-laden-cron.log 2>&1' ; echo '0 5 * * * cd /srv/www/git/de.einfach-laden/webserver && php spark notifications:cleanup >> /var/log/einfach-laden-cron.log 2>&1' ; echo '*/30 * * * * cd /srv/www/git/de.einfach-laden/webserver && php spark sessions:check-blocking >> /var/log/einfach-laden-cron.log 2>&1') | crontab - && echo Cron jobs eingerichtet"
                '''
            }
        }
    }

    post {
        success {
            script {
                def message = "Einfach Laden - Build Erfolgreich\n\nBuild: #${env.BUILD_NUMBER}\nVersion: 1.0.${env.BUILD_NUMBER}\nBranch: ${env.GIT_BRANCH_NAME}\nCommit: ${env.GIT_COMMIT_SHORT}\nDauer: ${currentBuild.durationString}"

                powershell """
                    \$body = @{
                        chat_id = '${env.TELEGRAM_CHAT_ID}'
                        text = '${message}'
                    }
                    Invoke-RestMethod -Uri 'https://api.telegram.org/bot${env.TELEGRAM_BOT_TOKEN}/sendMessage' -Method Post -Body \$body -ContentType 'application/x-www-form-urlencoded; charset=utf-8'
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
                def message = "Einfach Laden - Build FEHLGESCHLAGEN\n\nBuild: #${env.BUILD_NUMBER}\nBranch: ${env.GIT_BRANCH_NAME}\nCommit: ${env.GIT_COMMIT_SHORT}\nDauer: ${currentBuild.durationString}\n\nBitte Jenkins-Log pruefen"

                powershell """
                    \$body = @{
                        chat_id = '${env.TELEGRAM_CHAT_ID}'
                        text = '${message}'
                    }
                    Invoke-RestMethod -Uri 'https://api.telegram.org/bot${env.TELEGRAM_BOT_TOKEN}/sendMessage' -Method Post -Body \$body -ContentType 'application/x-www-form-urlencoded; charset=utf-8'
                """
            }
        }

        always {
            echo 'Pipeline abgeschlossen.'
        }
    }
}
