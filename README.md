# 💍 Wedding Invitation Web App (Undangan Pernikahan Digital)

[![Laravel Version](https://img.shields.io/badge/Laravel-v11.x%20%2F%20v13.x-red.svg?style=flat-square&logo=laravel)](https://laravel.com)
[![Livewire Version](https://img.shields.io/badge/Livewire-v4.x-4e56a6.svg?style=flat-square&logo=livewire)](https://livewire.laravel.com)
[![AlpineJS](https://img.shields.io/badge/AlpineJS-v3.x-8bc0d0.svg?style=flat-square&logo=alpine.js)](https://alpinejs.dev)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v3.x-38bdf8.svg?style=flat-square&logo=tailwind-css)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

A modern, highly customizable digital wedding invitation web application built with **Laravel**, **Livewire**, **Alpine.js**, and **Tailwind CSS**. It features a robust administration dashboard to manage invitation details and 4 beautifully crafted premium themes to wow your guests.

---

## ✨ Features

- **🎨 4 Premium Aesthetic Themes:**
  - **Elegant Emerald & Gold (`elegant`):** A luxurious, dark-themed emerald & gold layout for high-end ceremonies.
  - **Modern Rustic Forest (`genz`):** A clean light theme with dynamic pine needle particles for the modern Gen-Z aesthetic.
  - **Pastel Sakura (`pastel`):** A minimalist, soft-pink cherry blossom theme with falling blossom animations.
  - **Retro Vintage (`retro`):** A funky terracotta & orange design for couples loving vintage/hipster designs.
- **✉️ Dynamic Personalized Invitation Cover:**
  - Double-border envelope opening effect with dynamic "Wax Seal" trigger.
  - Custom guest greeting via URL parameter (e.g., `/?to=Nama+Tamu`).
- **⏳ Real-time Countdown Timer:**
  - Automatically calculates the remaining days, hours, minutes, and seconds until the main event starts.
- **🗺️ Interactive Map & Directions:**
  - Integration with **Leaflet.js** (OpenStreetMap) with a styled sepia custom overlay.
  - Fallback embedding for **Google Maps iframe**.
  - One-click "Buka Peta Lokasi" button.
- **💬 RSVP & Real-time Buku Tamu (Greetings):**
  - Instant guest validation, status option (Hadir / Tidak Hadir / Ragu), and custom greeting form.
  - Real-time updates without page reloading using Laravel Livewire.
- **🎵 Auto-playback & Audio Control:**
  - Supports standard `.mp3` audio files and direct YouTube URL background music.
  - Custom dynamic rotating play/pause floating control button.
- **🛠️ Fully Loaded Admin Control Center (`/admin`):**
  - Real-time form updates for Groom/Bride profiles, welcome message, event coordinates, background music, and active template switcher.

---

## 🛠️ Technical Stack

- **Backend:** Laravel 11/13 + PHP 8.3+
- **Frontend Engine:** Livewire v4 (Real-time reactivity)
- **Interactive Logic:** Alpine.js
- **Styling:** Tailwind CSS (Custom themes, font families, keyframe animations)
- **Map System:** Leaflet.js & OpenStreetMap API

---

## 🚀 Installation & Setup

Follow these simple steps to run this project locally:

### Prerequisites

Make sure you have installed:
- PHP >= 8.3
- Composer
- Node.js (with npm)
- SQLite (or another database server like MySQL)

### Step-by-Step Guide

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/yambuttt/wedding-inv.git
   cd wedding-inv
   ```

2. **Run Automagic Setup script:**
   This project comes pre-configured with a setup command that installs dependencies, copies `.env`, generates the app key, and builds assets:
   ```bash
   composer setup
   ```
   > [!TIP]
   > The setup script runs `composer install`, sets up your `.env` file, generates the security key, runs migration commands, installs Node.js packages, and builds frontend assets automatically.

3. **Configure Database & Credentials:**
   Open the `.env` file and verify your database connection. By default, it's set to use **SQLite**:
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

4. **Seed Default Data:**
   Populate the database with a default wedding invitation (Sari & Raju) and create the default admin account:
   ```bash
   php artisan db:seed
   ```

5. **Start Dev Server:**
   Run the concurrent developer task runner which starts the web server, queue listener, and Vite watcher simultaneously:
   ```bash
   composer dev
   ```
   Alternatively, run them separately:
   ```bash
   # Terminal 1: Serve PHP Application
   php artisan serve

   # Terminal 2: Run Vite compiler
   npm run dev
   ```

---

## 👤 Admin Access Credentials

After seeding the database, navigate to `http://localhost:8000/admin` to modify the invitation details. Use these credentials to log in (if authentication is requested or to customize):

| Field | Default Value |
| :--- | :--- |
| **Email** | `admin@wedding.com` |
| **Password** | `admin123` |

---

## 📁 Directory Structure

```text
├── app/
│   └── Models/               # Eloquent Models (Invitation, Greeting, Guest)
├── database/
│   ├── migrations/           # Database Schema Migrations
│   └── seeders/              # Database Seeder (Demo Data & User Seed)
├── resources/
│   ├── css/                  # Custom CSS Styles and Animations
│   ├── js/                   # Javascript Initialization (Leaflet map config)
│   └── views/
│       ├── admin.blade.php   # Admin Panel Layout
│       ├── welcome.blade.php # Invitation Page Layout
│       └── components/       # Livewire Components (⚡invitation-admin, ⚡wedding-invitation)
└── routes/
    └── web.php               # Web Routing definitions
```

---

## 📜 License

This project is open-source software licensed under the [MIT License](LICENSE). Feel free to customize and use it for your special day!
