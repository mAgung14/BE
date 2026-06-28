<br />
<div align="center">
  <h1 align="center">KuisKita Backend API 🚀</h1>

  <p align="center">
    A robust, real-time backend API for an interactive quiz platform (Kahoot/Quizizz alternative).
    <br />
    <br />
    <a href="API_DOCUMENTATION.md"><strong>Explore the API Docs »</strong></a>
    <br />
    <br />
    <a href="#">Report Bug</a>
    ·
    <a href="#">Request Feature</a>
  </p>
</div>

<!-- ABOUT THE PROJECT -->
## 📚 About The Project

**KuisKita** is an interactive, real-time quiz platform designed to make learning fun and engaging. This repository contains the **Backend API** built with Laravel, serving as the core engine for user authentication, quiz management, real-time broadcasting, and result processing.

### ✨ Key Features
- **Teacher Dashboard**: Secure JWT-based authentication for teachers to create and manage quizzes.
- **Excel Integration**: Import questions and export participant results seamlessly using Excel/CSV.
- **Real-time Engine**: Powered by Pusher WebSockets to handle live participant lobbies, instant quiz starts, and live result submissions.
- **Flexible Access**: Support for both Public (discoverable) and Private (PIN-based) quizzes.
- **Stat Tracking**: Detailed insights, fastest completion times, and podium rankings for every quiz session.

### 🛠️ Built With

* [![Laravel][Laravel.com]][Laravel-url]
* [![PHP][PHP.net]][PHP-url]
* [![MySQL][MySQL.com]][MySQL-url]
* [![Pusher][Pusher.com]][Pusher-url]
* **JWT Auth** (php-open-source-saver/jwt-auth)
* **Maatwebsite Excel**

---

<!-- GETTING STARTED -->
## 🚀 Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites

Make sure you have the following installed on your machine:
* PHP >= 8.3
* Composer
* MySQL / MariaDB
* Node.js & NPM (optional, for frontend assets if needed)
* Pusher Account (for WebSocket credentials)

### Installation

1. **Clone the repository**
   ```sh
   git clone https://github.com/your_username/kuiskita-backend.git
   cd kuiskita-backend
   ```

2. **Install PHP dependencies**
   ```sh
   composer install
   ```

3. **Environment Setup**
   ```sh
   cp .env.example .env
   ```
   Generate the application key:
   ```sh
   php artisan key:generate
   ```
   Generate the JWT secret key:
   ```sh
   php artisan jwt:secret
   ```

4. **Configure Database & Pusher**
   Open `.env` and configure your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=aplikasi-kuis
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   Set up your Pusher credentials for real-time features:
   ```env
   BROADCAST_CONNECTION=pusher
   PUSHER_APP_ID=your_pusher_app_id
   PUSHER_APP_KEY=your_pusher_key
   PUSHER_APP_SECRET=your_pusher_secret
   PUSHER_APP_CLUSTER=your_pusher_cluster
   ```

5. **Run Migrations**
   ```sh
   php artisan migrate
   ```

6. **Serve the Application**
   ```sh
   php artisan serve
   ```
   The API will be available at `http://127.0.0.1:8000`.

---

<!-- USAGE EXAMPLES -->
## 📖 Usage & Documentation

For a complete list of all available endpoints, request bodies, and authentication methods, please refer to the comprehensive API Documentation.

* 📄 **[API Documentation](API_DOCUMENTATION.md)** - Full REST API endpoints.
* 📄 **[Excel Import Guide](EXCEL_IMPORT_GUIDE.md)** - How to structure Excel files for bulk question imports.

---

<!-- ROADMAP -->
## 🗺️ Roadmap

- [x] JWT Authentication & Verification
- [x] CRUD Quizzes & Questions
- [x] Real-time Lobby & Quiz Broadcasting
- [x] Excel Import/Export
- [ ] Gamification & Badges
- [ ] Question Banks / Templates Sharing

---

<!-- LICENSE -->
## 📝 License

Distributed under the MIT License. See `LICENSE` for more information.


<!-- MARKDOWN LINKS & IMAGES -->
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[PHP.net]: https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white
[PHP-url]: https://php.net
[MySQL.com]: https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white
[MySQL-url]: https://www.mysql.com/
[Pusher.com]: https://img.shields.io/badge/Pusher-300D4F?style=for-the-badge&logo=pusher&logoColor=white
[Pusher-url]: https://pusher.com
