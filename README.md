# **LabLife: A Geek-Centric Dating Web Application Utilizing Interest-Based Matching for Socially Awkward Romantics.**

A modern real-time matching platform built with a **custom PHP MVC framework**, **Supabase**, **GetStream**, **Cloudflare Turnstile**, and a **Python-based face verification API**.

LabLife features secure authentication, profile customization, smart matching, real-time messaging, video calls, and a complete admin dashboard.

---

## 🖼️ Project Overview

LabLife helps users:

* Discover compatible matches
* Exchange real-time messages
* Perform secure video calls
* Verify identity using face recognition
* Report users or bugs
* Manage their profile preferences and interests

Admins can:

* Manage bug reports
* Manage user reports
* Monitor system activity via dashboard
* View notifications

---

## 🚀 Features

### 👤 **User System**

* Secure Login & Registration
* Cloudflare Turnstile CAPTCHA
* Session-based authentication
* Redis brute-force prevention
* CSRF protection
* Fully customizable user profile
* Hobby, interest, personality, and preference management
* Profile image upload
* Face verification via Python API

---

### 💘 **Matching System**

A structured multi-step matching flow:

1. **User enters Discover mode**
2. Added to `active_match_searches`
3. System computes compatibility:

   * Gender preference
   * Age preference
4. If mutually compatible → **Match Session**
5. Users choose ❤️ Like / ✖ Dislike
6. If both like → becomes an official match
7. If not → session auto-rejects

---

### 💬 **Real-Time Communication (GetStream.io)**

* Full real-time chat
* Secure Stream token generation (server-side only)
* Online/offline indicators
* File/image messaging
* Video calling
* Notifications
* Chat list & detailed chat UI

---

### 🛠️ **Admin Panel**

* Dashboard with stats
* Manage Bug Reports
* Manage User Reports
* Admin notifications
* Action modals (Resolve, Delete, etc.)

---

### 🔒 **Security Features**

* Input sanitization (PHP & JS)
* Prepared statements for all DB queries
* Redis-based login rate limiting
* CSRF token validation
* Turnstile CAPTCHA
* Server-side Stream token creation
* Supabase RLS rules
* Sanitized file uploads

---

## 🏗️ Architecture

This project uses a **custom lightweight MVC structure**:

```
├── Core/          # Framework core
├── Controllers/   # Logic layer
├── Models/        # Data layer
├── Services/      # Utility service classes
├── views/         # HTML templates
├── public/        # Public assets & entry point
├── config/        # App configuration
├── python/        # Face verification microservice
```

Built using:

* **PHP 8+**
* **Supabase PostgreSQL**
* **Redis**
* **JavaScript (vanilla)**
* **Python (dlib + face_recognition)**
* **GetStream chat/video**
* **HTML/CSS (custom UI)**

---

## 📁 Directory Overview

### **Core/**

Custom-built mini-framework:

* `App.php` — kernel
* `Router.php` — routing engine
* `Auth.php` — session auth
* `Database.php` — PDO + Supabase
* `Middleware.php` — auth & access control
* `Validator.php` — form validation

---

### **Controllers/**

Separated by feature:

#### 🔐 Auth

* Register
* Login
* Logout

#### 👤 User

* Profile
* Preferences
* Discover (matching)
* Messages
* Video Call
* Reports

#### 🛠️ Admin

* Dashboard
* Bug Reports
* User Reports
* Notifications

---

### **Services/**

* `SupabaseService.php` — Supabase API
* `StreamService.php` — GetStream tokens
* `TurnstileService.php` — CAPTCHA verification

---

### **public/**

* `index.php` — main entry
* Assets: CSS, JS, Images
* Face detection model files
* Uploads folder

---

### **python/**

Contains the isolated Python microservice:

```
python/face_verification/
└── face_verification_api.py
```

This handles:

* Face embeddings
* Liveness check
* Comparison with profile picture

---

## 🛡️ Technologies Used

| Technology           | Purpose                |
| -------------------- | ---------------------- |
| PHP                  | Backend logic          |
| Supabase             | Database, storage      |
| Redis                | Rate limiting          |
| Python               | Face verification      |
| GetStream            | Real-time chat & video |
| Cloudflare Turnstile | CAPTCHA                |
| JS (Vanilla)         | Frontend logic         |
| HTML/CSS             | UI styling             |

---

## 📜 License

This project is made for **academic purposes** by the LabLife Development Team.
