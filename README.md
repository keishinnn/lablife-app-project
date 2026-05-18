# LabLife: A Geek-Centric Real-Time Matchmaking Platform

LabLife is a full-stack real-time matchmaking web application built with a **custom PHP MVC framework**, **Supabase**, **Redis**, **GetStream**, and a **Python face verification microservice**.

The platform focuses on secure identity verification, structured compatibility matching, and real-time communication for users with shared interests and personalities.

---

# Overview

LabLife combines social matching, secure communication, and layered security into a single platform.

Users can:

- Create and customize profiles
- Discover compatible matches
- Participate in structured match sessions
- Exchange real-time messages
- Join video calls
- Verify identity using face recognition
- Report users or platform issues

Administrators can:

- Manage user and bug reports
- Monitor activity through analytics dashboards
- Handle moderation workflows
- Receive system notifications

---

# Core Features

## Authentication & User System

- Secure registration and login system
- Session-based authentication
- Cloudflare Turnstile CAPTCHA integration
- Redis brute-force prevention
- CSRF token validation
- Profile customization system
- Interest, hobby, and personality preferences
- Profile image uploads
- Face verification using Python + dlib

---

## 💘 Structured Matching System

LabLife uses a multi-step matchmaking workflow instead of random swiping.

### Matching Flow

1. User enters Discover mode
2. User is added to active matching queue
3. Compatibility scoring is computed based on:
   - Gender preference
   - Age preference
   - Shared interests
4. Compatible users enter a match session
5. Users choose Like or Dislike
6. Mutual likes create an official match
7. Non-mutual sessions are automatically rejected

---

## 💬 Real-Time Messaging & Video Calls

Powered by GetStream.io.

Features include:

- Real-time private messaging
- Secure server-side Stream token generation
- Video calling
- Online/offline presence
- File and image sharing
- Chat notifications
- Dedicated chat list and conversation UI

---

## 🤖 Face Verification Microservice

A separate Python-based microservice handles facial verification and identity checks.

### Verification Features

- Face embedding generation
- Face comparison
- Identity validation
- Liveness verification workflow
- Webcam image processing

Built using:

- Python
- Flask
- dlib
- face_recognition
- OpenCV

---

## 🛠️ Admin Dashboard

The admin panel provides moderation and monitoring tools.

### Admin Features

- Dashboard metrics
- User report management
- Bug report management
- Admin notifications
- Resolve/delete moderation actions
- Activity monitoring

---

# 🔒 Security Architecture

Security is implemented throughout the entire stack using a layered defense approach.

## Security Features

- Prepared statements for all database queries
- Input sanitization (PHP & JavaScript)
- CSRF token validation
- Redis-based rate limiting
- Cloudflare Turnstile CAPTCHA
- Supabase Row Level Security (RLS)
- Secure file upload handling
- Server-side Stream token generation
- Origin validation
- Session-based access control

---

# 🏗️ System Architecture

LabLife uses a custom lightweight MVC architecture.

```text
├── Core/              # Framework core
├── Controllers/       # Application controllers
├── Models/            # Database models
├── Services/          # Utility & integration services
├── views/             # UI templates
├── public/            # Public entry point & assets
├── config/            # App configuration
├── python/            # Face verification microservice
```

---

# ⚙️ Technology Stack

| Technology | Purpose |
|---|---|
| PHP 8+ | Backend application |
| Supabase PostgreSQL | Database & authentication |
| Redis | Rate limiting & caching |
| GetStream | Real-time chat & video |
| Python + dlib | Face verification |
| Flask | Python API server |
| Cloudflare Turnstile | CAPTCHA |
| JavaScript | Frontend interactivity |
| HTML/CSS | User interface |
| Docker | Containerization |
| GitHub Actions | CI/CD |

---

# 📁 Project Structure

## Core/

Custom-built framework components.

| File | Purpose |
|---|---|
| App.php | Application kernel |
| Router.php | Routing engine |
| Auth.php | Session authentication |
| Database.php | PDO database layer |
| Middleware.php | Access control |
| Validator.php | Validation utilities |

---

## Controllers/

Feature-separated application controllers.

### Authentication

- Register
- Login
- Logout

### User Features

- Profile management
- Discover/matching system
- Messaging
- Video calls
- Preferences
- Reports

### Admin Features

- Dashboard
- Notifications
- Bug reports
- User reports

---

## Services/

| Service | Purpose |
|---|---|
| SupabaseService.php | Supabase integration |
| StreamService.php | Stream token management |
| TurnstileService.php | CAPTCHA validation |

---

## Python Face Verification Service

```text
python/face_verification/
└── face_verification_api.py
```

Handles:

- Face embeddings
- Face comparison
- Verification requests
- Liveness workflow

---

# ⚡ Installation Guide

## 1. System Requirements

Before installation, ensure the following are installed:

- Windows 10 or newer
- XAMPP
- Composer
- Python 3.10+
- CMake
- Git (optional)

---

## 2. Clone or Extract Project

Move the project folder into:

```text
C:/xampp/htdocs/
```

Expected path:

```text
C:/xampp/htdocs/lablife-app-project
```

---

## 3. Configure Apache

### Update Apache Document Root

Open:

```text
XAMPP → Apache → Config → httpd.conf
```

Replace:

```apache
DocumentRoot "C:/xampp/htdocs/"
<Directory "C:/xampp/htdocs/">
```

With:

```apache
DocumentRoot "C:/xampp/htdocs/lablife-app-project/public"
<Directory "C:/xampp/htdocs/lablife-app-project/public">
```

Restart Apache afterward.

---

## 4. Enable PostgreSQL PDO Extension

Inside `php.ini`, enable:

```ini
extension=pdo_pgsql
extension=pgsql
```

Restart Apache.

---

## 5. Install PHP Dependencies

```bash
composer install
```

Required packages:

```json
{
  "require": {
    "predis/predis": "^3.2",
    "vlucas/phpdotenv": "^5.6",
    "get-stream/stream-chat": "^3.13"
  }
}
```

---

## 6. Configure Environment Variables

Copy:

```text
.env.example → .env
```

Add:

```env
SUPABASE_URL=
SUPABASE_KEY=
STREAM_API_KEY=
STREAM_API_SECRET=
REDIS_HOST=
REDIS_PORT=
```

---

## 7. Configure Redis

Install Redis or Memurai for Windows.

Verify connection:

```bash
redis-cli
ping
```

Expected response:

```text
PONG
```

---

## 8. Configure Python Face Verification Service

### Create Virtual Environment

```bash
cd python/face_verification

python -m venv venv
venv\Scripts\activate
```

### Install Dependencies

```bash
pip install -r requirements.txt
```

---

## 9. Run Face Verification API

```bash
python face_verification_api.py
```

Expected output:

```text
Running on http://127.0.0.1:5000
```

Keep the terminal open.

---

## 10. Run the Application

Start Apache from XAMPP.

Visit:

```text
http://localhost/
```

---

# ✅ Final Checklist

Before deployment or presentation:

- Apache running
- Redis running
- Python API running
- Composer dependencies installed
- `.env` configured
- Apache DocumentRoot configured
- Supabase credentials valid

---

# 🧪 Troubleshooting

## Predis Class Not Found

```bash
composer install
composer dump-autoload
```

---

## dlib Installation Failure

Install:

- Visual Studio Build Tools
- Desktop C++ Development tools

---

## 500 Internal Server Error

Check:

```text
C:\xampp\apache\logs\error.log
```

Verify `.env` variables are complete.

---

## Face Recognition Errors

Ensure:

- Webcam is accessible
- OpenCV installed correctly
- Python API server is running

---

# 📜 Academic Notice

This project was developed for academic purposes by the LabLife Development Team.
