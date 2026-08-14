# CampusGuardian – Technical Viva & Architectural Documentation

---

## SECTION 1: PROJECT OVERVIEW & ARCHITECTURE

### 1.1 Project Abstract
**CampusGuardian** is an enterprise-grade, multi-role web platform designed for educational institutions to automate student monitoring, late entry verification, multi-tier leave approvals, campus outpass permissions, automated attendance detection, and parent alert dispatches. Developed using Core PHP 8, MySQL, Bootstrap 5, AJAX, Chart.js, and PHPMailer, CampusGuardian replaces manual paper forms and unorganized WhatsApp messaging with a centralized, secure digital workflow.

### 1.2 Problem Statement
Traditional student attendance and leave management systems in colleges suffer from severe operational bottlenecks:
- Physical gate pass slips are frequently lost or falsified.
- Leave approval requires physical movement of paper applications between students, class tutors, and HOD offices.
- Parents are unaware of student delays or unauthorized absences until end-of-month reports.
- Manual attendance register compilation consumes significant faculty hours and leads to human data entry errors.

### 1.3 System Objectives
1. Eliminate manual paper forms for student late arrivals, leaves, and outpass permissions.
2. Implement a 2-tier authorization hierarchy (Faculty Staff Verification &rarr; HOD Final Approval).
3. Automatically flag un-checked students missing past 09:00 AM as `Not Informed` and trigger real-time parent notifications.
4. Provide interactive Chart.js analytics for department attendance rates and student risk levels.
5. Offer one-click CSV export and printable PDF summary reports.

---

## SECTION 2: SYSTEM DESIGN & DATA FLOW DIAGRAMS

### 2.1 Entity-Relationship (ER) Diagram Description
The `campusguardian` database comprises 13 normalized tables adhering to Third Normal Form (3NF):
- `users`: Core authentication entity (`id`, `email`, `password`, `role`, `is_active`).
- `departments`: Academic department records (`id`, `dept_code`, `dept_name`).
- `students`: Linked to `users` and `departments` via Foreign Keys.
- `staff` & `hod`: Faculty profiles mapped to respective department IDs.
- `attendance`: Date-wise student check-in records (`status` enum: Present, Late, Leave, Half Day, Absent, Not Informed).
- `late_entries`: Late arrival logs storing arrival time, delay duration, and 2-tier approval states.
- `leave_requests`: Leave applications supporting start/end dates, total days, medical attachments, and 2-tier approvals.
- `half_day_permissions`: Outpass requests storing exit time, return time, and status.
- `notifications`: User alert notifications table with read/unread markers.
- `email_logs`: Complete log of dispatched HTML parent emails.
- `settings`: System configuration parameters (e.g. 09:00 AM cutoff time, SMTP parameters).

### 2.2 Data Flow Diagram (DFD) Level 0 - Context Diagram
```text
[ Student ] ──► Submit Late / Leave / Outpass Request ──► [ CampusGuardian System ]
[ Staff ]   ──► Verify & Recommend Requests            ──► [ CampusGuardian System ]
[ HOD ]     ──► Final Approval / Rejection             ──► [ CampusGuardian System ]
[ System ]  ──► Dispatches Email Alert / Updates DB     ──► [ Parent / Student ]
```

### 2.3 Data Flow Diagram (DFD) Level 1 - Detailed Process Flow
1. **Process 1.0 (Authentication)**: User inputs credentials &rarr; Validated against `users` table via `password_verify()` &rarr; Role session initialized.
2. **Process 2.0 (Request Filing)**: Student files Late/Leave request &rarr; Stored in `late_entries` / `leave_requests` with status `pending`.
3. **Process 3.0 (Faculty Review)**: Staff reviews pending queue &rarr; Updates `staff_approval` state and adds remarks.
4. **Process 4.0 (HOD Authorization)**: HOD inspects escalated queue &rarr; Grants final status `approved` / `rejected` &rarr; Updates `attendance` table automatically.
5. **Process 5.0 (Notification & Alert)**: System creates entry in `notifications` table and dispatches HTML email alert via `CampusMailer` to Parent Email.
6. **Process 6.0 (Auto-Attendance Engine)**: Background cron script checks 09:00 AM cutoff &rarr; Flags missing students as `Not Informed` &rarr; Logs parent email alert.

---

## SECTION 3: ADVANTAGES, LIMITATIONS & FUTURE ENHANCEMENTS

### 3.1 Advantages
- **Security & Integrity**: Role-Based Access Control (RBAC), PDO prepared statements, and bcrypt password hashing.
- **Automated Communication**: Instant parent alerts eliminate communication gaps between college and home.
- **Zero Framework Footprint**: Pure PHP 8 code runs out-of-the-box on standard XAMPP Apache servers without complex `npm` or `composer` setup.
- **Responsive Dark/Light UI**: Modern user experience on desktop, tablet, and mobile browsers.

### 3.2 System Limitations
- Requires active XAMPP MySQL server running locally or hosted on an Apache server.
- Email dispatch in offline XAMPP environments relies on fallback logging to the `email_logs` table unless connected to live SMTP settings.

### 3.3 Future Enhancements
1. Integration with Biometric Fingerprint / RFID Smart Card hardware scanners.
2. Facial Recognition camera check-in at campus entry gates.
3. Native Android and iOS mobile application development using Flutter / React Native.
4. SMS Gateway integration for instant WhatsApp / SMS text messaging.

---

## SECTION 4: 50 MCA VIVA QUESTIONS AND DETAILED ANSWERS

### 1. What is the title of your project and what core problem does it solve?
**Answer**: Title is "CampusGuardian – Smart Student Monitoring, Approval & Alert System". It replaces paper-based gate passes and unorganized WhatsApp leave notes with a digital, 2-tier approval workflow and automated parent email alerts for student late arrivals and absences.

### 2. Why did you choose Core PHP 8 instead of a framework like Laravel or CodeIgniter?
**Answer**: Core PHP 8 provides maximum architectural clarity, zero external dependency bloat, high execution speed, direct control over session handling and PDO prepared statements, and seamless deployment on any standard XAMPP Apache server.

### 3. Explain the Database design of CampusGuardian.
**Answer**: The database `campusguardian` is normalized up to 3NF and consists of 13 tables (`users`, `students`, `staff`, `hod`, `departments`, `attendance`, `late_entries`, `leave_requests`, `half_day_permissions`, `notifications`, `email_logs`, `settings`, `reports`) connected via relational Foreign Keys with CASCADE rules and indexes.

### 4. How does CampusGuardian implement Role-Based Access Control (RBAC)?
**Answer**: RBAC is enforced via session middleware (`includes/auth_check.php`). Upon login, the user's role (`admin`, `hod`, `staff`, `student`) is saved in `$_SESSION['role']`. Every protected page calls `check_auth(['allowed_roles'])`, redirecting unauthorized users to their respective portals.

### 5. What security measures are taken to prevent SQL Injection attacks?
**Answer**: All database interactions utilize PDO Prepared Statements with parameterized placeholders (`?` or `:param`). Inputs are bound separately from SQL query execution, rendering SQL injection impossible.

### 6. How is user password security handled in the system?
**Answer**: Passwords are never stored in plain text. They are hashed using PHP 8's native `password_hash($password, PASSWORD_BCRYPT)` function and verified during login using `password_verify($password, $hash)`.

### 7. How does the automated attendance engine detect un-checked students at 09:00 AM?
**Answer**: The script `cron/auto_attendance_check.php` queries for all active students who do not have an attendance entry for today and have no approved leave application. Past 09:00 AM, it automatically marks their status as `Not Informed` and logs a parent email alert.

### 8. What design pattern is used for the database connection in CampusGuardian?
**Answer**: The **Singleton Pattern** is implemented in `config/database.php` via `Database::getConnection()`. This ensures only a single PDO connection instance is instantiated and reused across the entire script lifecycle, optimizing memory and DB pool resources.

### 9. What is AJAX and how is it used in CampusGuardian?
**Answer**: Asynchronous JavaScript and XML (AJAX) allows web pages to send and receive data asynchronously from the server without full page reloads. In CampusGuardian, AJAX is used for modal approval/rejection submissions (`ajax/handler.php`) and background auto-attendance triggers.

### 10. How does the system handle late entry delay calculation?
**Answer**: When a student submits a late entry with an arrival time (e.g. 09:18 AM), the system parses the official start time setting (09:00 AM) and calculates the difference in minutes: `round((strtotime($arrival_time) - strtotime($cutoff_time)) / 60)`.

### 11. What is the purpose of the `email_logs` table?
**Answer**: It acts as an audit trail for all outgoing emails. Every parent notification email (whether sent via live SMTP or logged locally) records recipient details, subject, HTML body, and dispatch status.

### 12. How does the system protect against Cross-Site Scripting (XSS)?
**Answer**: User input rendered in the browser is sanitized using `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')` through the global helper function `sanitize()`, converting special HTML characters into harmless HTML entities.

### 13. Explain the multi-tier approval workflow for student leave.
**Answer**: 
1. Student submits leave request &rarr; Status becomes `pending` at Staff level.
2. Faculty Staff reviews and approves &rarr; Status updates to `staff_approval = approved`.
3. HOD inspects escalated request and grants final approval &rarr; Status updates to `hod_approval = approved` and `status = approved`, and the `attendance` table is updated.

### 14. What frontend technologies are used in CampusGuardian?
**Answer**: HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, JavaScript (ES6), jQuery 3.7, and Chart.js 4.4.

### 15. How is Dark Mode implemented in the application?
**Answer**: Using CSS Custom Properties (Variables) toggled on the `<html>` element with `data-theme="dark"`. The active state is persisted across page reloads using browser `localStorage`.

### 16. What is PDO in PHP?
**Answer**: PHP Data Objects (PDO) is a database abstraction layer providing a uniform, object-oriented interface for interacting with relational databases securely using prepared statements.

### 17. How does session management work in PHP 8?
**Answer**: Sessions are initialized using `session_start()`. Security directives such as `session.cookie_httponly = 1` prevent JavaScript access to session cookies, protecting against session hijacking.

### 18. What is 3rd Normal Form (3NF)?
**Answer**: A database table is in 3NF if it is in 2NF and all non-key attributes are functional dependent ONLY on the primary key, eliminating transitive dependencies.

### 19. How are file uploads (profile photos, medical certificates) validated?
**Answer**: In `includes/functions.php`, `upload_file()` checks file extension whitelist (`jpg`, `jpeg`, `png`, `pdf`), enforces a 5MB size limit, generates a unique filename using `uniqid()`, and moves the file securely using `move_uploaded_file()`.

### 20. What is the function of `headers_sent()` or `header("Location: ...")` in PHP?
**Answer**: `header()` sends raw HTTP headers to the browser. It is used in `redirect()` for client-side redirection after authenticating or processing forms.

### 21. How are CSV reports generated and downloaded in `reports/export_excel.php`?
**Answer**: PHP outputs standard `text/csv` headers with `Content-Disposition: attachment`, fetching database records and writing formatted rows directly to the output stream using `fputcsv()`.

### 22. What is the difference between `GET` and `POST` HTTP methods?
**Answer**: `GET` appends parameters to the URL string (used for filtering reports and viewing pages). `POST` sends parameters in the HTTP request body (used for logins, form submissions, and sensitive data modifications).

### 23. What are Foreign Key Constraints and why are they used?
**Answer**: Foreign keys enforce referential integrity between tables (e.g., linking `students.department_id` to `departments.id`). With `ON DELETE CASCADE`, deleting a department automatically cleans up child records cleanly.

### 24. Explain Chart.js integration in CampusGuardian.
**Answer**: In `assets/js/chart-custom.js`, Chart.js instantiates dynamic HTML5 `<canvas>` doughnut and line charts using JSON stats passed directly from PHP backend queries.

### 25. What is the difference between `require_once` and `include_once` in PHP?
**Answer**: `require_once` produces a fatal error (`E_COMPILE_ERROR`) and halts script execution if the file is missing, whereas `include_once` emits a warning (`E_WARNING`) and continues execution.

### 26. How does the system handle session timeout or unauthorized page access?
**Answer**: `auth_check.php` verifies if `$_SESSION['user_id']` exists. If not, it sets a flash error message and redirects the visitor back to `index.php`.

### 27. What is CSRF and how can it be mitigated?
**Answer**: Cross-Site Request Forgery tricks an authenticated user into executing unwanted actions. Mitigation involves generating unique CSRF tokens per session and verifying them on form submission.

### 28. How does `password_verify()` work internally?
**Answer**: It extracts the salt and cost factor embedded within the bcrypt hash string, hashes the input plain password with the same parameters, and performs a time-constant string comparison to prevent timing attacks.

### 29. What is the purpose of `constants.php`?
**Answer**: Centralizes global application configuration values, status identifiers, role names, and file path definitions, preventing magic strings across the codebase.

### 30. Explain the role of `index.php` in your project.
**Answer**: Serves as the public landing portal featuring multi-role login tabs (Student, Staff, HOD, Admin) and one-click demo credentials for quick evaluation.

### 31. How are notifications delivered to users within the app?
**Answer**: When an approval action occurs, `add_notification()` inserts a row into `notifications`. The navbar fetches unread counts for the logged-in `user_id` and renders a red badge.

### 32. What database engine is used in MySQL and why?
**Answer**: **InnoDB**, because it supports ACID transactions, row-level locking, foreign key constraints, and crash recovery.

### 33. What is an Index in database design?
**Answer**: A data structure (B-Tree) that speeds up data retrieval operations on a table at the cost of additional write time and storage space. Indexes are placed on `email`, `register_number`, and `role` fields.

### 34. How is error handling structured in CampusGuardian?
**Answer**: Database operations are wrapped inside `try-catch (PDOException $e)` blocks. On failure, transactions roll back (`$db->rollBack()`), and clean error messages display via flash alerts.

### 35. How does `fputcsv()` work?
**Answer**: Formats an array as a CSV line and writes it to an open file stream or HTTP output stream.

### 36. Why is UTF-8 encoding (`utf8mb4`) specified for database connections?
**Answer**: `utf8mb4` provides complete Unicode support, including multi-lingual names, special symbols, and emojis.

### 37. What is XAMPP?
**Answer**: A free, cross-platform web server stack consisting of **X** (Cross-platform), **A**pache HTTP Server, **M**ariaDB/MySQL, **P**HP, and **P**erl.

### 38. How is print capability implemented in `export_pdf.php`?
**Answer**: Uses clean HTML/CSS print media queries (`@media print`) and triggers the browser's native print dialog automatically via JavaScript `window.print()`.

### 39. What is a Session Cookie?
**Answer**: A small text file stored in the user's browser containing the session ID (e.g. `PHPSESSID`), linking the client browser to server-side `$_SESSION` data.

### 40. Explain the difference between `==` and `===` in PHP.
**Answer**: `==` tests for equality after type coercion, while `===` tests for strict identity (both value and data type must match).

### 41. How are parent email alerts dispatched?
**Answer**: `CampusMailer::send()` generates responsive HTML content via `EmailTemplates`, dispatches it using mailer sockets, and records an entry in `email_logs`.

### 42. What is the role of `htdocs` in XAMPP?
**Answer**: `htdocs` is the root web directory served by the Apache web server. Any project folder placed inside is accessible via `http://localhost/folder_name`.

### 43. What is JSON and how is it used in the project?
**Answer**: JavaScript Object Notation (JSON) is a lightweight data-interchange format. Used in `ajax/handler.php` to return structured `{success: true, message: "..."}` responses to client scripts.

### 44. What is the function of `date_default_timezone_set()`?
**Answer**: Sets the default timezone used by all date/time functions in the PHP script (e.g. `Asia/Kolkata`), ensuring accurate timestamp logging.

### 45. What is HTTP status code 404 vs 500?
**Answer**: `404 Not Found` indicates the requested URL does not exist on the server. `500 Internal Server Error` indicates an unhandled server-side script crash.

### 46. What is a Modal dialog in Bootstrap 5?
**Answer**: A lightweight JavaScript popup overlaying the primary document window, used for quick approval forms and remarks without navigating away from the dashboard.

### 47. How does `uniqid()` work in PHP?
**Answer**: Generates a unique, time-based identifier string, preventing filename collisions during student photo or document uploads.

### 48. What is the purpose of `session_destroy()`?
**Answer**: Destroys all data registered to the current session on the server during logout, ensuring complete user session termination.

### 49. How can the system be deployed to a live domain?
**Answer**: Upload source code to a web host via FTP, import `campusguardian.sql` via cPanel phpMyAdmin, and update `config/database.php` with live MySQL credentials.

### 50. What were your primary learning outcomes while developing CampusGuardian?
**Answer**: Mastering modular Core PHP 8 software architecture, relational database normalization (3NF), securing web applications against SQLi and XSS, implementing role-based authentication middleware, building AJAX API handlers, and creating responsive user interface dashboards.
