# CampusGuardian – Smart Student Monitoring, Approval & Alert System

**CampusGuardian** is an enterprise-level web platform built for modern colleges and universities to replace manual paper forms and WhatsApp communication for student attendance tracking, late arrival management, leave applications, and parent notifications.

---

## 🌟 Key System Features

1. **Multi-Role Governance**: Dedicated secure portals for Admin, HOD, Faculty Staff, and Students.
2. **Late Entry Tracking**: Log late arrivals, calculate delay minutes automatically from the 09:00 AM cutoff time, and upload gate entry slips.
3. **Multi-Tier Leave Approval Engine**: 2-tier approval workflow (Faculty Staff Verification &rarr; HOD Final Authorization).
4. **Half-Day Campus Outpass**: Submit temporary campus exit and expected return time requests with real-time status tracking.
5. **Automated Attendance Detection**: Cron background engine that automatically flags un-checked students missing past 09:00 AM as `Not Informed` and triggers urgent parent email alerts.
6. **Parent Email Alerts**: Responsive HTML email templates via PHPMailer / Mailer engine with complete database dispatch logs (`email_logs`).
7. **Analytics & Charts**: Dynamic Chart.js interactive dashboards displaying present percentages, late trends, and leave distributions.
8. **Export & PDF Printing**: One-click Excel (CSV) downloads and printable PDF report templates for daily, weekly, monthly, and department attendance.
9. **Modern Responsive UI**: Built with Bootstrap 5, glassmorphism cards, and local-storage persistent Dark/Light mode theme toggles.

---

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5.3, JavaScript (ES6+), jQuery 3.7, AJAX, Chart.js 4.4, Bootstrap Icons.
- **Backend**: Native Core PHP 8 (No Frameworks used - Pure PHP architecture).
- **Database**: MySQL (Fully normalized schema with PDO prepared statements, InnoDB engine, foreign keys, and indexes).
- **Security**: Password hashing (`password_hash` / `password_verify`), PDO SQL injection prevention, HTML sanitization, session security (`httponly`, `use_only_cookies`).
- **Server Environment**: XAMPP / WAMP Apache Server with PHP 8.x and MySQL.

---

## 🚀 Quick Setup & Installation Guide

### Step 1: Copy Project Folder to XAMPP htdocs
Copy the extracted `CampusGuardian` directory into your XAMPP `htdocs` folder:
```text
C:\xampp\htdocs\CampusGuardian\
```

### Step 2: Start Apache and MySQL in XAMPP
Open the **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.

### Step 3: Import Database Schema into phpMyAdmin
1. Open your browser and navigate to `http://localhost/phpmyadmin/`.
2. Click on the **Import** tab.
3. Click **Choose File** and select `CampusGuardian/database/campusguardian.sql`.
4. Click **Import** at the bottom. This creates the `campusguardian` database with 13 pre-seeded tables.

### Step 4: Database Connection Settings (Optional)
If your XAMPP MySQL has a root password, open `config/database.php` and update:
```php
private static $host = 'localhost';
private static $db_name = 'campusguardian';
private static $username = 'root';
private static $password = ''; // Put your MySQL password if any
```

### Step 5: Launch Application
Open your browser and navigate to:
```text
http://localhost/CampusGuardian/
```

---

## 🔑 Pre-Configured Demo Credentials

For quick evaluation, click the demo buttons on the login portal or use:

| Role | Email | Password |
| :--- | :--- | :--- |
| **System Admin** | `admin@campusguardian.edu` | `password123` |
| **MCA HOD** | `hod.mca@campusguardian.edu` | `password123` |
| **CSE HOD** | `hod.cse@campusguardian.edu` | `password123` |
| **Faculty Staff** | `staff.sarah@campusguardian.edu` | `password123` |
| **Student (MCA)** | `rahul.mca24@campusguardian.edu` | `password123` |
| **Student (CSE)** | `priya.student@campusguardian.edu` | `password123` |

---

## 📁 Directory & Folder Structure

```text
CampusGuardian/
│── admin/                  # System Admin Portal Pages
│   ├── dashboard.php
│   ├── students.php
│   ├── staff.php
│   ├── hod.php
│   ├── departments.php
│   ├── reports.php
│   ├── settings.php
│   └── profile.php
│── student/                # Student Portal Pages
│   ├── dashboard.php
│   ├── late_entry.php
│   ├── leave_request.php
│   ├── half_day.php
│   ├── status.php
│   ├── notifications.php
│   └── profile.php
│── staff/                  # Faculty Staff Portal Pages
│   ├── dashboard.php
│   ├── requests.php
│   ├── history.php
│   └── profile.php
│── hod/                    # HOD Portal Pages
│   ├── dashboard.php
│   ├── approvals.php
│   ├── reports.php
│   ├── performance.php
│   └── profile.php
│── ajax/                   # Async API Handlers
│   └── handler.php
│── assets/                 # Frontend Assets
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── main.js
│       └── chart-custom.js
│── config/                 # System Configurations
│   ├── config.php
│   ├── constants.php
│   └── database.php
│── cron/                   # Automated Background Engine
│   └── auto_attendance_check.php
│── database/               # Database SQL File
│   └── campusguardian.sql
│── includes/               # Common PHP Components
│   ├── auth_check.php
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   └── sidebar.php
│── mail/                   # Email Dispatch Engine
│   ├── email_templates.php
│   └── mailer.php
│── reports/                # Report Generation & Export Scripts
│   ├── export_excel.php
│   └── export_pdf.php
│── uploads/                # File Uploads Directory
│   ├── attachments/
│   └── profile_photos/
│── index.php               # Unified Login Portal
│── login.php               # Auth Processing
│── logout.php              # Session Destruction
│── README.md               # System Documentation
└── VIVA_DOCUMENTATION.md   # Viva Q&A & Technical Specs
```

---

## 🧪 Testing Procedure

1. **Submit Late Entry**: Log in as `rahul.mca24@campusguardian.edu` (Password: `password123`), navigate to **Late Entry Form**, fill in arrival time (e.g. 09:20 AM), reason, and click Submit.
2. **Faculty Verification**: Log in as `staff.sarah@campusguardian.edu`, view **Pending Requests**, click **Approve** on Rahul's late entry, and provide faculty remarks.
3. **HOD Approval**: Log in as `hod.mca@campusguardian.edu`, view **Final Approvals**, and grant final authorization.
4. **Parent Email Log**: Log in as `admin@campusguardian.edu`, go to **Settings**, or view the MySQL `email_logs` table to confirm that the HTML notification email log was created.
