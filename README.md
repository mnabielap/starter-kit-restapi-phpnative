# 🚀 Starter Kit REST API (PHP Native)

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://www.php.net/)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://www.docker.com/)

A production-ready **RESTful API Boilerplate** built with **Native PHP** (No heavy frameworks like Laravel or Symfony). It follows the MVC architecture.

Designed for performance, simplicity, and flexibility. Supports both **MySQL** and **SQLite**.

---

## ✨ Features

- **⚡ Native PHP**: Built on top of PHP 8.x with PSR-4 Autoloading.
- **🗄️ Database Agnostic**: Compatible with **MySQL** and **SQLite** using PDO.
- **🔐 Authentication**: Secure JWT Authentication (Access & Refresh Tokens).
- **🛡️ Security**: Password hashing (Bcrypt), Rate Limiting (placeholder), and Header Security.
- **📂 MVC Architecture**: Clean separation of Controllers, Models, Services, and Middlewares.
- **🧪 API Testing**: Integrated Python scripts for automated endpoint testing.
- **🐳 Docker Ready**: Full containerization support for App and Database.
- **📨 Email**: SMTP Email integration via PHPMailer.

---

## 🛠️ Requirements

- **PHP**: >= 8.0
- **Composer**: Dependency Manager
- **Database**: MySQL or SQLite
- **Python 3.x**: (Optional, for running API tests)

---

## 🚀 Getting Started (Local Development)

It is recommended to run the project locally first to understand the structure before moving to Docker.

### 1. Clone and Install
```bash
git clone https://github.com/mnabielap/starter-kit-restapi-phpnative.git
cd starter-kit-restapi-phpnative

# Install PHP dependencies
composer install
```

### 2. Environment Configuration
Copy the example environment file and configure it.
```bash
cp .env.example .env
```
Open `.env` and set your configuration:
*   **DB_CONNECTION**: `mysql` or `sqlite`
*   **DB_HOST/DB_DATABASE**: Configure your credentials.

### 3. Database Setup
You must create the database tables manually using the provided SQL schemas.

#### Option A: MySQL 🐬
1.  Create a database named `starter_kit_php` (or whatever matches your `.env`).
2.  Import the file `database/schema.mysql.sql` into that database.

#### Option B: SQLite Lite 🗃️
1.  Ensure `DB_CONNECTION=sqlite` in `.env`.
2.  Create an empty file: `database/database.sqlite`.
3.  Execute the SQL from `database/schema.sqlite.sql` into that file (using DB Browser for SQLite or CLI).

### 4. Run the Application
You can use the built-in PHP server:
```bash
composer start
```
The server will start at `http://localhost:3000`.

> **Note for XAMPP/Apache Users:**
> If you run this inside `htdocs`, access it via `http://localhost/starter-kit-restapi-phpnative/public/`. The router automatically adjusts to subdirectories.

---

## 🐳 Running with Docker

This project supports a full Docker environment. We use persistent volumes for data and a custom network for communication.

### 1. Prepare Environment
Create a specific environment file for Docker:
```bash
cp .env.example .env.docker
```
**Important:** Inside `.env.docker`, ensure you set the following:
```env
DB_CONNECTION=mysql
DB_HOST=restapi-phpnative-mysql  <-- Must match the container name below
PORT=5005
```

### 2. Create Network & Volumes
We create a dedicated network and volumes to persist Database data and Uploaded files.
```bash
# Create Network
docker network create restapi_phpnative_network

# Create Volumes
docker volume create restapi_phpnative_db_volume
docker volume create restapi_phpnative_media_volume
```

### 3. Start MySQL Container
Run the database container attached to the network and volume.
```bash
docker run -d \
  --name restapi-phpnative-mysql \
  --network restapi_phpnative_network \
  -v restapi_phpnative_db_volume:/var/lib/mysql \
  -e MYSQL_ROOT_PASSWORD=rootpassword \
  -e MYSQL_DATABASE=starter_kit_php \
  mysql:8.0
```

### 4. Build and Run App Container
Build the PHP image and run it, mapping port **5005**.
```bash
# Build Image
docker build -t restapi-phpnative-app .

# Run Container
docker run -d -p 5005:5005 \
  --network restapi_phpnative_network \
  --env-file .env.docker \
  -v restapi_phpnative_media_volume:/var/www/html/public/uploads \
  --name restapi-phpnative-container \
  restapi-phpnative-app
```
Your API is now accessible at `http://localhost:5005`.

### 5. First Time DB Setup (Docker)
Since the database starts empty, you need to import the schema once.
```bash
docker exec -i restapi-phpnative-container php -r "echo file_get_contents('database/schema.mysql.sql');" | docker exec -i restapi-phpnative-mysql mysql -u root -prootpassword starter_kit_php
```

---

## 📦 Docker Management Commands

Here are useful commands to manage your containers.

#### 📜 View Logs
See what's happening inside the application container.
```bash
docker logs -f restapi-phpnative-container
```

#### oct: Stop Container
Stops the application without deleting data.
```bash
docker stop restapi-phpnative-container
```

#### ▶️ Start Container
Starts the container again.
```bash
docker start restapi-phpnative-container
```

#### 🗑️ Remove Container
Removes the container instance (requires stopping first).
```bash
docker stop restapi-phpnative-container
docker rm restapi-phpnative-container
```

#### 📂 View Volumes
Check your persistent data volumes.
```bash
docker volume ls
```

#### ❌ Remove Volume (Danger Zone)
**WARNING:** This will permanently delete your database data and uploaded files!
```bash
docker volume rm restapi_phpnative_db_volume
docker volume rm restapi_phpnative_media_volume
```

---

## 🧪 API Testing (Automated)

Instead of manually importing collections into Postman, this project comes with **Automated Python Scripts** located in `api_tests/`.

These scripts automatically handle:
1.  **Token Management**: Login scripts save JWT tokens to `secrets.json`.
2.  **Auth Injection**: Subsequent scripts read the token automatically.

### How to use:
1.  **Configure URL**: Open `api_tests/utils.py`.
    *   For Local: `BASE_URL = "http://localhost:3000/v1"`
    *   For Docker: `BASE_URL = "http://localhost:5005/v1"`
    *   For XAMPP: `BASE_URL = "http://localhost/starter-kit-restapi-phpnative/public/v1"`

2.  **Run Scripts**:
    Simply run the python files. No arguments needed.

    ```bash
    # 1. Register a user
    python api_tests/A1.auth_register.py

    # 2. Login (Saves token locally)
    python api_tests/A2.auth_login.py

    # 3. Get User Profile (Uses saved token)
    python api_tests/B3.user_get_one.py
    ```

3.  **Check Results**:
    The response JSON is printed to the console and also saved to a `.json` file in the same folder for inspection.

---

## 📂 Project Structure

```
starter-kit-restapi-phpnative/
│
├── api_tests/          # Python scripts for API testing
├── database/           # SQL Schemas (MySQL/SQLite) and SQLite DB file
├── public/             # Web Root (Entry point)
│   ├── uploads/        # Uploaded files directory
│   ├── .htaccess       # Apache Config
│   └── index.php       # Front Controller
├── src/
│   ├── Config/         # Database & Environment Setup
│   ├── Controllers/    # Request Handlers
│   ├── Core/           # Framework Core (Router, Request, Response, Model)
│   ├── Middlewares/    # Auth & Rate Limiting
│   ├── Models/         # Data Access Layer
│   ├── Services/       # Business Logic
│   ├── Utils/          # Validators, Logger, ErrorHandler
│   └── routes.php      # API Route Definitions
├── .env.example        # Env template
├── composer.json       # Dependencies
├── Dockerfile          # Docker Image Config
└── entrypoint.sh       # Docker Entry Script
```

## 📄 License

[MIT](LICENSE)