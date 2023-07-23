# 🚚 Delivery

Delivery &mdash;  это веб-приложение для управления и отслеживания доставки. Проект предоставляет удобный интерфейс для операторов, продовцов и администраторов.

Разработан на <p><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="100" alt="Laravel Logo"></a></p>

Основные функциональные возможности проекта включают:

* Создание заказов.
* Оформление заказов на доставку.
* Отслеживание статуса доставки заказа.
* Присвоение заказов курьерам.
* Отчетность по заказам.

-----

## 🛠️ Установка

### Предварительные требования

* [PHP ^8.1](https://www.php.net/manual/ru/install.php)
* [Composer (v2+)](https://getcomposer.org/doc/00-intro.md)
* [Node.js (v16+)](https://nodejs.org/en) & [NPM (9+)](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)
* SQLite for local, MySQL or PostgreSQL for production

### Локальная установка

1. Склонируйте репозиторий проекта
```sh
git clone https://github.com/AbdievMarat/delivery.git
```

2. Перейдите в директорию проекта
```sh
cd delivery
```

3. Скопируйте файл .env.example и переименуйте его в .env
```sh
cp .env.example .env
```

4. Укажите параметры подключения к БД в файле .env
```sh
DB_CONNECTION=sqlite
```

5. Установите зависимости, используя Composer
```sh
composer install
```

6. Установите зависимости, используя Npm
```sh
npm install
```

7. Запустите сборку фронтенд-ресурсов в режиме разработки
```sh
npm run dev
```

8. Сгенерируйте ключ приложения
```sh
php artisan key:generate
```

9. Примените миграции и заполнените данные сидерами
```sh
php artisan migrate--seed
```

10. Запустите локальный сервер разработки
```sh
php artisan serve --port=9876
```

-----

## 🖥️ Руководство пользователя

Для удобства тестирования и ознакомления с приложением, вы можете использовать следующие учетные данные для входа в систему:

* Логин: admin@admin.com
* Пароль: password