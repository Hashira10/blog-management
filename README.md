
# Blog Manager

## Развертывание

### Локальная установка и запуск

1. **Клонируйте репозиторий**:
    ```bash
    git clone https://github.com/Hashira10/blog-management.git
    cd blog-management
    ```

2. **Установите зависимости**:
    ```bash
    composer install
    npm install
    ```

3. **Настройте окружение**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Отредактируйте файл `.env` и укажите параметры подключения к базе данных.

5. **Выполните миграции и сидеры**:
    ```bash
    php artisan migrate --seed
    ```

6. **Запустите сервер**:
    ```bash
    php artisan serve
    ```

7. Откройте браузер и перейдите по адресу документации:  
    http://127.0.0.1:8000/api/documentation
   Для добавления или изменения данных выполните логин с данными админа и выполните авторизацию с токеном выданным после логина:
   'email' = 'admin@example.com',
   'password' = 'password123'

6. **Тестирование**:
    ```bash
    php artisan test
    ```

---

### Развертывание с использованием Docker

Для запуска в Docker:
Используйте DB_HOST=db в .env, так как база данных запускается в контейнере и доступна по имени сервиса Docker Compose. Для корректной работы приложения и подключения к базе данных убедитесь, что параметры в файле .env совпадают с настройками в docker-compose.yml.

1. **Клонируйте репозиторий**:
    ```bash
    git clone https://github.com/Hashira10/blog-management.git
    cd blog-management
    ```

2. **Соберите и запустите контейнеры**:
    ```bash
    docker-compose up --build
    ```

4. **Сборка фронтенда**:
    ```bash
    docker-compose exec node npm run build
    ```

6. Откройте браузер и перейдите по адресу:  
    [http://localhost:8000](http://localhost:8000)
