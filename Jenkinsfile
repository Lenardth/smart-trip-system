pipeline {
    agent any

    tools {
        nodejs 'node18'
    }

    environment {
        APP_ENV = 'testing'
        DB_CONNECTION = 'sqlite'
        DB_DATABASE = "${WORKSPACE}/database/database.sqlite"
        CACHE_STORE = 'array'
        SESSION_DRIVER = 'array'
        QUEUE_CONNECTION = 'sync'
        MAIL_MAILER = 'array'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Install PHP dependencies') {
            steps {
                sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'
            }
        }

        stage('Install Node dependencies') {
            steps {
                sh 'npm ci --prefer-offline'
            }
        }

        stage('Prepare app') {
            steps {
                sh '''
                    cp .env.example .env || true
                    mkdir -p database
                    touch database/database.sqlite
                    php artisan key:generate
                    php artisan config:clear
                    php artisan cache:clear
                    chmod -R 777 storage bootstrap/cache
                '''
            }
        }

        stage('Run tests') {
            steps {
                sh 'php artisan migrate --force'
                sh 'php artisan test'
            }
        }

        stage('Build frontend') {
            steps {
                sh 'npm run build'
            }
        }
    }

    post {
        always {
            archiveArtifacts artifacts: 'storage/logs/*.log', allowEmptyArchive: true
        }
    }
}
