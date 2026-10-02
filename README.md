# CampusGuardian
### Smart Student Monitoring, Approval & Alert System

<p align="center">
  <strong>A role-based college campus management platform for student requests, attendance monitoring, multi-level approvals, notifications, parent visibility, and reporting.</strong>
</p>

<p align="center">
  <img src="docs/screenshots/login-page.png" alt="CampusGuardian Login" width="900">
</p>

<p align="center">
  <a href="#-overview">Overview</a> •
  <a href="#-key-features">Features</a> •
  <a href="#-system-workflow">Workflow</a> •
  <a href="#-screenshots">Screenshots</a> •
  <a href="#-technology-stack">Tech Stack</a> •
  <a href="#-installation--setup">Setup</a>
</p>

---

## 📌 Overview

**CampusGuardian** is a web-based student monitoring and request-management system designed for college environments.

The system digitizes common campus processes such as:

- Late-entry requests
- Leave applications
- Half-day / outpass permissions
- Attendance monitoring
- Staff verification
- HOD approval
- Parent monitoring and alerts
- Notifications
- Administrative management
- Attendance, late-entry, and leave reports
- CSV export and print/PDF-ready reports
- Automated attendance checking after the configured campus cutoff time

Instead of handling these activities through paper forms, manual registers, or scattered messages, CampusGuardian provides a centralized workflow with role-based dashboards.

---

## 🎯 Problem Statement

Traditional student permission and attendance processes can involve:

- Paper-based leave forms
- Manual late-entry records
- Delayed communication with parents
- Difficulty tracking request status
- Repeated data entry
- Limited visibility for staff and administrators
- Time-consuming report preparation

**CampusGuardian** addresses these problems by moving the request, verification, approval, notification, attendance, and reporting workflow into one web application.

---

## 💡 Solution

CampusGuardian connects five major user roles through a common system:

```text
                         ┌─────────────────────┐
                         │   CampusGuardian    │
                         │   Central System    │
                         └──────────┬──────────┘
                                    │
          ┌─────────────┬───────────┼───────────┬─────────────┐
          ▼             ▼           ▼           ▼             ▼
      Student         Staff        HOD        Parent        Admin
          │             │           │           │             │
          │             │           │           │             │
       Request       Verify       Approve     Monitor      Manage
       Submit        Request      / Reject    Ward         System
          │             │           │           │             │
          └─────────────┴───────────┼───────────┴─────────────┘
                                    ▼
                         Attendance + Notifications
                              + Reports
```

---

# 👥 User Roles

| Role | Main Responsibilities |
|---|---|
| 🎓 **Student** | Submit late-entry, leave, and half-day requests; track status; view notifications; manage profile |
| 👨‍🏫 **Staff** | Review student requests, verify details, approve/reject at faculty level, view history |
| 🧑‍💼 **HOD** | Review escalated requests, provide final approval/rejection, monitor department reports and performance |
| 👨‍👩‍👦 **Parent** | Monitor the student's leave activity, alerts, and academic/request status |
| 🛡️ **Admin** | Manage students, staff, HODs, parents, departments, settings, notifications, and system reports |

---

# ✨ Key Features

## 🎓 Student Portal

- Student dashboard with request statistics
- Submit **Late Entry** request
- Submit **Leave Request**
- Submit **Half-Day / Outpass Permission**
- Upload supporting attachments where applicable
- Track approval status
- View request history
- Receive system notifications
- Manage profile information

<p align="center">
  <img src="docs/screenshots/student-dashboard.png" alt="Student Dashboard" width="900">
</p>

---

## 👨‍🏫 Staff Portal

The staff portal provides a faculty-level verification queue.

### Functions

- View pending student requests
- Review request details
- Approve or reject requests
- Add verification remarks
- View request history
- View notifications
- Manage staff profile

<p align="center">
  <img src="docs/screenshots/staff-dashboard.png" alt="Staff Dashboard" width="900">
</p>

---

## 🧑‍💼 HOD Portal

The HOD portal provides department-level supervision and final authorization.

### Functions

- Department dashboard
- Final request approvals
- Performance/monitoring view
- Reports
- Notifications
- Profile management

The project implements a **two-level approval workflow** for applicable requests:

```text
Student submits request
        │
        ▼
Staff verification
        │
   ┌────┴────┐
   │         │
Reject     Approve
             │
             ▼
        HOD review
             │
       ┌─────┴─────┐
       │           │
    Reject       Approve
                   │
                   ▼
          Final request status
                   │
                   ▼
       Attendance / Notification
```

<p align="center">
  <img src="docs/screenshots/hod-reports.png" alt="HOD Reports" width="900">
</p>

---

## 🛡️ Admin Portal

The administrator manages the overall campus system.

### Management Modules

- Student management
- Staff management
- HOD management
- Parent management
- Department management
- System settings
- Notifications
- Reports
- User profiles

<p align="center">
  <img src="docs/screenshots/admin-dashboard.png" alt="Admin Dashboard" width="900">
</p>

---

## 👨‍👩‍👦 Parent Portal

The parent portal provides visibility into the student's campus activity.

### Functions

- View ward/student information
- Monitor leave activity
- View alerts and notifications
- View request-related information
- Check monitoring status
- Manage parent profile

<p align="center">
  <img src="docs/screenshots/parent-dashboard.png" alt="Parent Dashboard" width="900">
</p>

---

# ⏰ Automated Attendance Monitoring

CampusGuardian includes an automated attendance-checking mechanism.

The configured default campus cutoff is **09:00 AM**.

### Process

```text
09:00 AM cutoff
      │
      ▼
Check active students
      │
      ▼
Attendance exists?
   ┌──┴──┐
  YES    NO
   │      │
   ▼      ▼
Continue  Check approved leave
             │
          ┌──┴──┐
         YES    NO
          │      │
          ▼      ▼
       Exclude  Mark as
                "Not Informed"
                    │
                    ▼
             Create notification
                    │
                    ▼
              Log parent alert
```

The implementation is located in:

```text
cron/auto_attendance_check.php
```

The cutoff time and auto-attendance setting are read from the system configuration.

---

# 🔔 Notifications & Parent Alerts

The system maintains a notification mechanism for important events such as:

- Request updates
- Approval/rejection decisions
- Attendance flags
- Absence-related alerts

The project also contains an email notification layer with an `email_logs` table for maintaining an audit trail of notification attempts.

> **Demo note:** The included mailer currently logs notification attempts for demonstration. The code contains an SMTP integration point for connecting a real mail provider during deployment.

---

# 📊 Reports & Export

The reporting module provides filtered views for administrative/academic monitoring.

### Supported report categories

- Daily attendance
- Late-entry records
- Leave records

### Export options

- CSV export
- Print / Save as PDF

<p align="center">
  <img src="docs/screenshots/hod-reports.png" alt="Reports and Export Center" width="900">
</p>

---

# 🗃️ Database Design

The project uses **MySQL/MariaDB** with relational tables.

Core entities include:

```text
departments
     │
     ├── students
     │      ├── attendance
     │      ├── late_entries
     │      ├── leave_requests
     │      └── half_day_permissions
     │
     ├── staff
     │
     └── hod

users
  │
  ├── admin
  ├── student
  ├── staff
  ├── hod
  └── parent

notifications
email_logs
settings
```

### Main database tables

| Table | Purpose |
|---|---|
| `users` | Authentication and role information |
| `departments` | Department master data |
| `students` | Student profile and parent details |
| `staff` | Faculty/staff information |
| `hod` | HOD information and department mapping |
| `attendance` | Daily attendance records |
| `late_entries` | Late-entry requests and approval states |
| `leave_requests` | Leave applications and approval states |
| `half_day_permissions` | Half-day/outpass requests |
| `notifications` | User notifications |
| `email_logs` | Notification/email audit records |
| `settings` | System configuration values |

---

# 🔐 Security & Application Design

The project includes several standard web-application security practices:

### Role-Based Access Control

Protected pages check the authenticated session and allowed roles.

```text
Login
  │
  ▼
Session created
  │
  ▼
Role identified
  │
  ├── Admin  ──► Admin Portal
  ├── HOD    ──► HOD Portal
  ├── Staff  ──► Staff Portal
  ├── Student──► Student Portal
  └── Parent ──► Parent Portal
```

### Password Security

Passwords are handled using PHP password hashing and verification functions rather than storing plain-text passwords in application logic.

### SQL Injection Protection

Database operations use **PDO prepared statements** with parameterized values.

### Output Sanitization

The project provides a reusable sanitization helper based on `htmlspecialchars()` for safely rendering user-provided values.

### Session-Based Authorization

Protected modules use centralized authentication/authorization checks.

---

# 🎨 User Interface

The interface is designed as a modern dashboard-style web application with:

- Responsive layout
- Sidebar navigation
- Role-specific dashboards
- Dashboard cards
- Status badges
- Notification indicators
- Modal-based actions
- Charts and analytics
- Light/dark UI elements
- Mobile-friendly navigation behavior

The frontend uses Bootstrap components together with custom CSS and JavaScript.

---

# 🧰 Technology Stack

## Frontend

- HTML5
- CSS3
- Bootstrap 5.3
- Bootstrap Icons
- JavaScript ES6
- jQuery 3.7
- Chart.js 4.4

## Backend

- PHP 8+
- Core PHP architecture
- PDO
- Session-based authentication
- AJAX request handling

## Database

- MySQL / MariaDB
- InnoDB
- `utf8mb4`

## Development Environment

- XAMPP
- Apache
- phpMyAdmin
- VS Code

---

# 🏗️ Project Architecture

```text
CampusGuardian/
│
├── admin/                 # Administrator portal
├── ajax/                  # AJAX request handlers
├── assets/
│   ├── css/               # Application styles
│   ├── images/             # Application images
│   └── js/                 # JavaScript and charts
│
├── config/                # Database and application configuration
├── cron/                  # Automated attendance processing
├── database/              # SQL database schema and seed data
├── hod/                   # HOD portal
├── includes/              # Shared authentication/layout/helpers
├── mail/                  # Mail templates and notification service
├── parent/                # Parent portal
├── reports/               # CSV and printable report generation
├── staff/                 # Staff portal
├── student/               # Student portal
│
├── index.php              # Login / landing page
├── register.php           # Registration
├── logout.php             # Logout handler
│
├── README.md
└── VIVA_DOCUMENTATION.md
```

---

# 🔄 End-to-End Request Flow

A typical leave/permission request follows this lifecycle:

```text
                 ┌───────────────┐
                 │    Student    │
                 └───────┬───────┘
                         │
                         ▼
                Submit Application
                         │
                         ▼
              Store in MySQL Database
                         │
                         ▼
                 ┌───────────────┐
                 │     Staff     │
                 └───────┬───────┘
                         │
                 Verify / Recommend
                         │
                         ▼
                 ┌───────────────┐
                 │      HOD      │
                 └───────┬───────┘
                         │
                    Final Decision
                         │
             ┌───────────┴───────────┐
             ▼                       ▼
          Approved                Rejected
             │                       │
             ▼                       ▼
       Update status          Update status
             │
             ▼
      Attendance update
             │
             ▼
        Notification
             │
             ▼
           Parent
```

---

# 📸 Screenshots

## Login & Role Selection

<p align="center">
  <img src="docs/screenshots/login-page.png" alt="CampusGuardian Login and Role Selection" width="1000">
</p>

## Student Dashboard

<p align="center">
  <img src="docs/screenshots/student-dashboard.png" alt="Student Dashboard" width="1000">
</p>

## Staff Dashboard

<p align="center">
  <img src="docs/screenshots/staff-dashboard.png" alt="Faculty Staff Dashboard" width="1000">
</p>

## HOD Reports

<p align="center">
  <img src="docs/screenshots/hod-reports.png" alt="HOD Reports and Export Center" width="1000">
</p>

## Admin Dashboard

<p align="center">
  <img src="docs/screenshots/admin-dashboard.png" alt="Admin Dashboard" width="1000">
</p>

## Parent Dashboard

<p align="center">
  <img src="docs/screenshots/parent-dashboard.png" alt="Parent Dashboard" width="1000">
</p>

---

# 🚀 Installation & Setup

## 1. Install XAMPP

Install XAMPP with:

- Apache
- MySQL
- PHP
- phpMyAdmin

Start **Apache** and **MySQL** from the XAMPP Control Panel.

---

## 2. Place the Project

Copy the project into your XAMPP `htdocs` directory.

Example:

```text
C:\xampp\htdocs\CampusGuardian
```

---

## 3. Create the Database

Open:

```text
http://localhost/phpmyadmin
```

Create/import the database using:

```text
database/campusguardian.sql
```

The intended database name is:

```text
campusguardian
```

---

## 4. Check Configuration

Review:

```text
config/config.php
config/database.php
config/constants.php
```

Make sure the database host, username, password, and database name match your local XAMPP configuration.

---

## 5. Run the Application

Open:

```text
http://localhost/CampusGuardian/
```

The login page should appear.

---

## 6. Demo Accounts

The repository contains seeded local demo accounts for the different roles.

For the exact demo login details, see:

```text
login_credentials.txt
```

> For a public/production deployment, change all demo credentials and never commit real passwords, SMTP passwords, API keys, or other secrets.

---

# ⚙️ Automated Attendance Test

The attendance checker is available at:

```text
cron/auto_attendance_check.php
```

For a local demonstration, it can be triggered through the supported test mode after the database is configured.

Example CLI command:

```bash
php cron/auto_attendance_check.php force=1
```

The process checks students without an attendance record and considers approved leave before creating a `Not Informed` attendance entry.

---

# 🧪 Testing Checklist

After installation, test the system in this order:

### Authentication

- [ ] Student login
- [ ] Staff login
- [ ] HOD login
- [ ] Parent login
- [ ] Admin login
- [ ] Logout
- [ ] Unauthorized page access

### Student Workflow

- [ ] Submit late-entry request
- [ ] Submit leave request
- [ ] Submit half-day request
- [ ] Check request status
- [ ] View notifications

### Staff Workflow

- [ ] Open pending request queue
- [ ] Review student request
- [ ] Approve request
- [ ] Reject request
- [ ] Add remarks

### HOD Workflow

- [ ] View escalated requests
- [ ] Approve request
- [ ] Reject request
- [ ] View reports

### Parent Workflow

- [ ] View ward details
- [ ] View leave history
- [ ] View alerts
- [ ] Check monitoring status

### Admin Workflow

- [ ] Manage students
- [ ] Manage staff
- [ ] Manage HODs
- [ ] Manage parents
- [ ] Manage departments
- [ ] View reports
- [ ] Check settings

---

# 📈 Future Enhancements

Potential future extensions include:

1. RFID or biometric attendance integration
2. QR-based campus entry
3. Face-recognition-assisted check-in
4. Native Android/iOS application
5. SMS and WhatsApp notification integrations
6. Real SMTP email delivery
7. Advanced attendance analytics
8. Parent acknowledgment workflow
9. Department-level analytics and trend dashboards
10. Cloud deployment with centralized database hosting

---

# 📚 Project Documentation

Additional technical and viva documentation is available in:

```text
VIVA_DOCUMENTATION.md
```

It contains detailed explanations of:

- Project architecture
- Database design
- Data flow
- Security concepts
- Approval workflow
- Attendance automation
- Technical viva questions and answers

---

# 🧑‍💻 Project Information

**Project:** CampusGuardian – Smart Student Monitoring, Approval & Alert System

**Application Type:** College Campus Management Web Application

**Architecture:** Role-Based Multi-Portal Web Application

**Backend:** Core PHP 8+

**Database:** MySQL / MariaDB

**Frontend:** HTML5, CSS3, Bootstrap, JavaScript, jQuery

**Development Environment:** XAMPP / Apache / phpMyAdmin

---

# 👨‍💻 Author

**Mohamed Mohasir**

MCA Student | Software Development & Python Enthusiast

- GitHub: [@mdmohasir2407](https://github.com/mdmohasir2407)

---

# 📄 License

This project was developed for **academic, learning, demonstration, and portfolio purposes**.

If you reuse or extend the project, please provide appropriate attribution to the original author.

---

<p align="center">
  <strong>CampusGuardian</strong><br>
  Smart • Secure • Connected Campus Management
</p>
