# AHRIMPN Abuja Renewal Conference 2025 – Registration & Certificate Management System

This web application was developed for the **Association of Health Records and Information Management Practitioners of Nigeria (AHRIMPN)** in collaboration with the **Health Records Officers Registration Board of Nigeria (HRORBN)** to support the **44th Annual General Meeting & National Scientific Conference** themed:

> **Strengthening Nigeria’s Healthcare System through Innovation and Technology in Health Information Management.**

The system provides online event registration, participant data management, conference tour booking, and automated certificate generation for successful attendees.

---

## 🌍 Features

### **Public Site**
- **Conference Information Pages**
  - About the event
  - Abuja Renewal theme and objectives
  - Important dates & schedules
- **Registration Forms**
  - Professional registration
  - Student registration
  - Committee/Volunteer registration
- **Tour Registration**
  - Optional guided tour sign-up
  - Status tracking (Pending / Approved / Rejected)

### **Admin Panel**
- Secure login with role-based access (`superadmin`, `approval`, staff roles)
- Manage all attendees & committee members
- Approve / reject registrations & tour requests
- **Automated Certificate Generation**
  - Committee Certificates
  - Professional Certificates
  - Student Certificates
  - PDF files generated and stored per user
- Edit user records, regenerate certificates, or remove invalid entries

---

## 🗂️ Project Structure (Simplified)
/admin
- Login & dashboard
- Manage registrations
- Certificate generation modules
/public
- Main website pages (About, Register, Conference Info)
- Registration & Tour forms
/includes
- Database configuration
- Email configuration
- Session manager
- admin js (table.js)
/lib
- PDF generation library (FPDF)
/assets
- CSS
- JS
- images
- front-end styling

---

## 🧰 Tech Stack

| Layer     | Technology |
|--------------|------------|
| Frontend     | HTML5, CSS3, Bootstrap, JavaScript |
| Backend      | PHP (Native / Procedural) |
| Database     | MySQL |
| PDF Engine   | FPDF |

---

## ⚙️ Key Functional Highlights

- REST-style AJAX interactions for status updates
- Clean Bootstrap UI with responsive layout
- Dynamic modals for record editing and approvals
- Permanent certificate file storage linked to user accounts
- Tour approval workflow without page reloads

---

## 💡 Setup Instructions

1. Clone the repository:
   ```bash
   git clone https://github.com/mewakaki/ahrimpn-conference-portal.git

2. Import the included SQL database into MySQL.

3. Update your database credentials in:

- /includes/config.php

4. Run the project on a PHP-enabled server (XAMPP, WAMP, Laravel Valet, etc.).

📄 License

This project is developed specifically for AHRIMPN & HRORBN and may not be used or distributed commercially without permission.

✨ Acknowledgement

Developed to support the advancement of Health Information Management and continuous professional development across Nigeria’s healthcare system.


