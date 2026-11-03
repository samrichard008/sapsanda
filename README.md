# SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
### International Standard Professional Organization Website & Management System
**Official Registration Number:** `G A 00282904` (Sri Lanka)  
**Headquarters:** Jayabodhi Viharaya, Nalluruwa, Panadura, Western Province, Sri Lanka  
**Contact:** `070 7261978` | `072 253 2493` | `0777 26 19 78`  
**Official Email:** `sapsadaunit@gmail.com`  
**Official Slogan:** "Together We Care. Together We Protect Life."

---

## 🌟 Overview

This is an international-standard, production-ready, trilingual digital portal and management platform built specifically for **SAP SADA Social Welfare & Life Protection Unit**. 

The system features complete public-facing informational and engagement portals, alongside a fully secured administrative management console (`/admin/`), powered by **PHP 8.2+**, **MySQL / MariaDB**, **Bootstrap 5.3**, and **Vanilla JavaScript** (with zero heavy framework dependencies).

### Trilingual Language Architecture:
- 🇬🇧 **English (Default)**: Complete humanitarian portfolio, news, and registration.
- 🇱🇰 **Sinhala (සිංහල)**: සප් සඳ සමාජ සුභ සාධන හා ජීවිතාරක්ෂණ ඒකකය
- 🇱🇰 **Tamil (தமிழ்)**: சப் சத சமூக நலன்புரி மற்றும் உயிர்ப் பாதுகாப்புப் பிரிவு

---

## 🚀 Key Features

### 1. Public Portal (Citizen & Donor Engagement)
- **Hero Banner Slider**: Dynamic banners with official branding, slogans, and responsive CTAs.
- **Organization Identity**: Vision, Mission, Core Values, Legal Registration (`G A 00282904`), and Board of Directors.
- **12 Core Activity Categories**:
  1. Beach & Coastal Life Protection (Panadura coastal lifeguard patrols)
  2. Inland Water Rescue & Flood Response (Bolgoda & inland waterways)
  3. School & Youth Swimming Training
  4. Environmental & Wetland Conservation
  5. Heavy Machinery Dredging & Canal Cleaning (Authentic excavator flood mitigation)
  6. Women's Empowerment & Handicraft Skills (Self-employment programs)
  7. Healthcare & Medical Camps
  8. Elder & Orphan Welfare Support
  9. Low-Income Family Housing Support
  10. Drug & Substance Abuse Prevention
  11. Human Rights & Community Justice (National recognition & legal aid)
  12. Disaster Relief & Emergency Preparedness
- **Featured Projects**: Planned, Ongoing, Completed, and Archived projects with progress metrics.
- **Interactive Volunteer Portal**: Multi-step registration covering all **25 Sri Lankan Districts**, skill selections, availability, emergency contacts, generating official reference numbers (e.g. `SAP-VOL-2026-XXXX`).
- **Donation & Transparency Hub**: Campaign-specific fundraising progress bars, certified bank transfer details, and proof-of-payment slip upload notification.
- **Events & Community Calendar**: Date, venue, countdown, and community RSVP forms.
- **Action Photo Gallery**: Categorized photo gallery with responsive lightbox viewer.
- **Multilingual Contact Hub**: Real-time validated contact form saving inquiries to database, direct WhatsApp floating hotline, and interactive Google Map.

### 2. Administrator Management Console (`/admin/`)
- **Brute-Force Protected Login**: 5-attempt rate-limiting lockout, bcrypt password hashing, and CSRF token guards.
- **Interactive Executive Dashboard**: Real-time database metrics (Volunteers, Donations, Inquiries, Activities) and Sri Lanka district volunteer distribution analytics.
- **Banners Manager**: Slide order, text, and banner image uploader.
- **Pages & CMS Editor**: Trilingual editor for About Us, Mission, Vision, and Legal Registration.
- **Translations Manager**: Web editor for all dictionary strings across English, Sinhala, and Tamil.
- **Activities & Projects CRUD**: Comprehensive management with multilingual tabs and image upload.
- **Volunteers Management**: Search, district filter, status workflows (Pending / Approved / Contacted / Rejected), applicant notes, and **1-Click UTF-8 CSV Export**.
- **Donation Campaigns & Slip Verification**: Track donations and verify uploaded bank deposit slips.
- **News, Events & Gallery Managers**: Trilingual news publishing, RSVP event schedules, and photo album uploads.
- **Leadership Profiles**: Manage trustees, advisors, and executive board members.
- **Contact Inquiries**: Message reader, replied flags, and direct WhatsApp / Email shortcuts.
- **System Settings**: Hotlines, registration number, slogans, bank details, impact counters, and SEO tags.
- **Admin Users**: Role-based access control (`Super Admin`, `Editor`, `Moderator`).
- **Audit Logs**: Immutable log history tracking administrative logins, actions, and IP addresses.
- **1-Click Database Backup**: Instant `.sql` dump generator and download.

---

## 💻 Local Setup (XAMPP / WAMP)

1. **Clone or Copy Code into XAMPP**:
   Place the project folder inside your web root:
   ```text
   C:\xampp\htdocs\sapsanda
   ```

2. **Start Apache & MySQL**:
   Open XAMPP Control Panel and start **Apache** and **MySQL**.

3. **Verify / Run the Web Installer**:
   Open your browser and navigate to:
   ```text
   http://localhost/sapsanda/setup.php
   ```
   The installer automatically checks all PHP extensions, verify folder permissions, and tests database connectivity.
   - If tables need initialization, click **"Initialize Database Now"** (or import `database.sql` directly into MySQL / phpMyAdmin).

4. **Default Administrator Credentials**:
   - **URL**: `http://localhost/sapsanda/admin/login.php`
   - **Username**: `admin`
   - **Password**: `Admin@SapSada2026!`
   *(Note: Remember to change your password immediately after logging in via Admin Console > Admin Users).*

---

## 🐙 GitHub Deployment Guide (தமிழில் மற்றும் ஆங்கிலத்தில்)

பயனர் கேட்டவாறு இந்த திட்டத்தை GitHub-இல் deploy செய்து வைப்பதற்கான எளிய வழிமுறைகள்:

### Step 1: Open Terminal in Project Directory
Open PowerShell or Command Prompt in the project folder:
```powershell
cd C:\Users\ASUS\Desktop\xampp\htdocs\sapsanda
```

### Step 2: Initialize Git Repository
```bash
git init
```

### Step 3: Add All Files to Git
```bash
git add .
```

### Step 4: Commit Your Project
```bash
git commit -m "feat: complete SAP SADA social welfare and life protection unit platform"
```

### Step 5: Set Default Branch to Main
```bash
git branch -M main
```

### Step 6: Link Your GitHub Repository
Create a new empty repository on [GitHub](https://github.com/new) named `sapsanda` (or `sapsada-welfare`). Then run:
```bash
git remote add origin https://github.com/<YOUR-USERNAME>/<YOUR-REPO-NAME>.git
```

### Step 7: Push to GitHub
```bash
git push -u origin main
```

*(Git will securely push the entire repository while respecting `.gitignore`, preserving the clean directory structure and all assets).*

---

## 🌐 Live Server / cPanel Deployment Guide

When deploying to a live hosting server (e.g. Hostinger, Namecheap, cPanel, or VPS):

### Method A: Deploying via cPanel Git Versioning
1. Log into your **cPanel**.
2. Click **Git Versioning** > **Create Repository**.
3. Clone your GitHub repository URL into `public_html/` (or a subfolder/subdomain).
4. Go to **cPanel MySQL Databases**:
   - Create a new database (e.g. `cpaneluser_sapsada`).
   - Create a database user and assign all privileges.
5. Go to **phpMyAdmin** > Select your database > Click **Import** > Select `database.sql` > Click **Go**.
6. Update your database credentials in `config/config.php` (or set environment variables `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
7. Open `https://yourdomain.com/setup.php` to verify your server environment, then click **"Lock Installer"**.

### Method B: Deploying via File Manager / FTP
1. Zip the project contents and upload to your server's `public_html/`.
2. Extract the archive.
3. Import `database.sql` via phpMyAdmin.
4. Update `config/config.php` with live MySQL credentials.
5. Ensure `uploads/` folder permissions are set to `755` (or writable).
6. Enable SSL (HTTPS) via Let's Encrypt / AutoSSL in cPanel.

---

## 🔒 Security Best Practices Implemented

- **Prepared SQL Statements Everywhere**: PDO prepared statements prevent SQL Injection attacks.
- **CSRF Token Validation**: Every form submission is verified against randomized session tokens.
- **XSS Sanitization**: Input data sanitized with `htmlspecialchars` and strict UTF-8 filters.
- **Brute-Force Defense**: Automatic account lockout after 5 consecutive failed login attempts.
- **Bcrypt Password Hashes**: Secure password storage using PHP `PASSWORD_BCRYPT`.
- **Restricted Direct File Access**: Sensitive files (`.sql`, `.log`, `.env`, `.git`) are blocked in `.htaccess`.
- **MIME-Type Upload Validation**: Uploaded bank slips and photos are strictly verified for valid JPEG, PNG, or WebP MIME types with safe generated filenames.
- **Session Protection**: Strict session cookies with `httponly`, `samesite=Lax`, and optional SSL flags.

---

## 📞 Organization Contacts

- **Organization**: SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
- **Registration No**: G A 00282904
- **Address**: Jayabodhi Viharaya, Nalluruwa, Panadura, Sri Lanka
- **Telephones**: 070 7261978 / 072 253 2493 / 0777 26 19 78
- **Official Email**: sapsadaunit@gmail.com
- **Website**: Designed & Developed for International Humanitarian & Life Protection Excellence.
