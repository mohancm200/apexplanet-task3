# ApexPlanet Internship – Task 3
## PHP and MySQL User Management System

### Project Overview
This project is developed as part of the ApexPlanet Software Pvt. Ltd. Full Stack Web Development Internship. It is a PHP and MySQL-based User Management System that supports user authentication, profile management, and administrator-controlled user management.

### Features
- User registration with input validation
- Secure password hashing using PHP `password_hash()`
- Login authentication using `password_verify()`
- Session-based authentication and logout
- Protected user dashboard
- Role-based access for Admin and User
- Admin CRUD operations to view, edit, and delete users
- Profile editing for name and email
- Profile picture upload with file type and size validation
- MySQL database connectivity using PHP
- Prepared statements to help prevent SQL injection

### Technologies Used
- HTML
- CSS
- PHP
- MySQL
- XAMPP
- phpMyAdmin
- Git and GitHub

### Project Structure
```text
apex-task3/
├── config/
│   └── db.php
├── includes/
├── uploads/
├── index.php
├── register.php
├── login.php
├── dashboard.php
├── users.php
├── profile.php
├── logout.php
├── README.md
└── .gitignore
```

### Setup Instructions
1. Install XAMPP.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Create a database named `apex_task3`.
5. Create the required `roles` and `users` tables and configure the role records.
6. Place the project folder inside `C:\xampp\htdocs\apex-task3`.
7. Verify the database settings in `config/db.php`.
8. Open `http://localhost/apex-task3/` in your browser.

### Testing
The project was tested locally for registration, login, dashboard access, administrator user management, profile editing, and profile picture upload.

### Internship
**Organization:** ApexPlanet Software Pvt. Ltd.  
**Task:** Task 3 – User Management System using PHP and MySQL

### Author
Developed as part of the ApexPlanet Full Stack Web Development Internship.
