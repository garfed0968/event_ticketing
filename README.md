# Event Ticketing System (Ethiopia)

A premium, web-based event ticketing platform tailored for the Ethiopian market. Features a robust organizer dashboard, upcoming event browsing, manual payment verification system, and a full admin panel.

## 🚀 Features

### For Event Organizers
- **Dashboard**: Real-time overview of created events and sales.
- **Event Creation**: Easy-to-use form to publish events with details (Date, Location, Price, Theme).
- **Ticket Management**: Approve or Decline ticket requests.
- **Refunds**: View and confirm refund requests from users.

### For Attenders
- **Browse Events**: Filter by Location, Date, or Theme.
- **Save Events**: Bookmark interested events.
- **Ticket Purchase**:
  - Select Payment Method (CBE, Awash, Dashen, Abissinia, Telebirr).
  - Upload Payment Receipt/Proof.
- **Refunds**: Request refunds for approved tickets if unable to attend.
- **My Dashboard**: View tickets, status badges, and download tickets.
- **Notifications**: Automatic reminders 1 day before an event.

### For Admins
- **User Management**: View all registered users.
- **Account Control**: Delete users (and their associated data) if necessary.

### General
- **Profile Management**: Update Username, Email, and Password.
- **Forgot Password**: Simulated email reset flow.

## 🛠️ Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5 (Local files, no CDN required)
- **Styling**: Custom "Glassmorphism" UI (`assets/css/custom.css`)
- **Backend**: PHP 8+
- **Database**: MySQL

## 📦 Installation & Setup

1. **Prerequisites**
   - Install **XAMPP** (or any PHP/MySQL environment).
   - Ensure Apache and MySQL services are running.

2. **File Placement**
   - Copy the project folder `events` to your `htdocs` directory (e.g., `C:\xampp\htdocs\events`).

3. **Database Setup**
   - **Option A (Recommended)**: Import the SQL file.
     - Go to `http://localhost/phpmyadmin/`.
     - Create a database named `event_ticketing_db`.
     - Click **Import** and select `events/database/event_ticketing_db.sql`.
   - **Option B (Alternative)**: Run the setup script.
     - Open `http://localhost/events/setup_db.php` to create tables automatically.
     - Run `http://localhost/events/seed_admin.php` to create the admin account.

4. **Launch the Application**
   - Navigate to the landing page:
     ```
     http://localhost/events/index.php
     ```

## 📂 Project Structure
```
events/
├── assets/                   # CSS (custom.css) and Vendor files
├── database/                 # SQL Dump (event_ticketing_db.sql)
├── docs/                     # Documentation and Diagrams
├── uploads/                  # Stores user payment proofs
├── db_connect.php            # Database connection
├── setup_db.php              # One-time DB setup script
├── seed_admin.php            # Creates default admin account
├── index.php                 # Landing Page
├── login.php / register.php  # Authentication
├── forgot_password.php       # Password Reset Flow
├── edit_profile.php          # User Profile Management
├── organizer_dashboard.php   # Organizer Control Panel
├── admin_dashboard.php       # Admin Control Panel
├── create_event.php          # Event Creation Form
├── ticket_action.php         # Approve/Decline/Refund Logic
├── my_tickets.php            # User Dashboard
└── ...
```

## 🔑 Default Credentials
- **Admin**: `admin@event.com` / `admin123`
- **Users**: Register freely via `register.php`.
