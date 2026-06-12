# 🦷 SmileCare Dental CRM

> A lightweight, portable Dental CRM built with PHP and flat-file JSON storage — no database required.


SmileCare is a self-hosted dental appointment management system designed for small clinics that need a simple, fast, and portable booking solution — without the overhead of MySQL or any external services.

---

## ✨ Features

- 📅 **Patient Booking Form** — Clean public-facing form to capture name, phone, email, date, time, and reason for visit
- 🔐 **Secure Admin Panel** — bcrypt-hashed password login with PHP session authentication
- 📊 **Dashboard** — Live stats: Today's appointments, Pending requests, Total count
- 📋 **Appointments Manager** — Full list with pagination, date/status filters, and detail view
- ⚡ **Status Workflow** — Inline status updates: Pending → Confirmed → Completed / Cancelled
- 🗑️ **Delete Appointments** — Full CRUD from the admin panel
- 🗂️ **Flat-File Storage** — All data stored in JSON files. Zero SQL setup needed
- 🔒 **Directory Protection** — Auto-generated `.htaccess` blocks direct access to data files

---

## 📸 Screenshots

<img width="1268" height="835" alt="Screenshot 2026-06-12 at 9 30 49 PM" src="https://github.com/user-attachments/assets/a8002363-7cfc-4a58-b3b3-ac4e1ae83757" />
<img width="1706" height="852" alt="Screenshot 2026-06-12 at 9 31 12 PM" src="https://github.com/user-attachments/assets/691a1f3b-ca81-451c-a23b-97d466b18a32" />
<img width="1709" height="855" alt="Screenshot 2026-06-12 at 9 31 27 PM" src="https://github.com/user-attachments/assets/029ba586-5468-4f34-a538-cc794113a3e4" />

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP (Server-Side Rendering) |
| Frontend | HTML5, Vanilla CSS, Google Fonts (Inter) |
| Storage | Flat-File JSON (`appointments.json`, `meta.json`) |
| Auth | PHP Native Sessions + bcrypt |
| Server | Apache / Any PHP 7.4+ host |

---

## 🚀 Getting Started

### Requirements

- PHP 7.4 or higher
- Apache or Nginx with PHP support
- No database required

### Installation

1. **Clone the repository**

```bash
git clone https://github.com/ahmed-ali-codes/smilecare-dental-crm.git
cd smilecare-dental-crm
```

2. **Set up a virtual host or drop into your server's web root**

```bash
# For XAMPP / WAMP — place in htdocs/
# For a live server — upload via FTP/SCP to public_html/
```

3. **Ensure the `data/` directory is writable**

```bash
chmod 755 data/
```

> The app will auto-initialize `appointments.json`, `meta.json`, and `.htaccess` on first run.

4. **Visit the app**

```
http://localhost/smilecare-dental-crm/          # Patient Booking Form
http://localhost/smilecare-dental-crm/admin/    # Admin Login
```

---

## 🔑 Admin Login

| Field | Value |
|---|---|
| Username | `admin` |
| Password | `admin123` |

> ⚠️ **Change the password hash before deploying to production.** See `admin/login.php` and replace the bcrypt hash.
>
> Generate a new hash:
> ```php
> echo password_hash('your_new_password', PASSWORD_BCRYPT);
> ```

---

## 📁 Project Structure

```
smilecare-dental-crm/
├── assets/
│   └── style.css              # Main stylesheet (CSS variables, responsive)
├── data/
│   ├── .htaccess              # Auto-generated — blocks direct web access
│   ├── appointments.json      # All appointment records
│   └── meta.json              # Auto-increment ID tracker
├── includes/
│   ├── auth.php               # Session + require_login() middleware
│   └── storage.php            # Core CRUD functions (JSON-based ORM)
├── admin/
│   ├── login.php              # Admin login page
│   ├── logout.php             # Session destroy
│   ├── dashboard.php          # Stats + recent appointments
│   └── appointments.php       # Full list, filters, detail view, actions
├── book.php                   # POST handler for booking form
└── index.php                  # Public booking landing page
```

---

## 🔒 Security

- All user input is sanitized with `htmlspecialchars()` before rendering
- Admin password is stored as a bcrypt hash — never plain text
- `data/` directory is protected from direct HTTP access via `.htaccess`
- Admin routes are protected by session middleware (`require_login()`)

---

## 🗺️ Roadmap

- [ ] Email confirmation to patient on booking (PHPMailer)
- [ ] Admin email alert on new appointment
- [ ] CSRF token protection on forms
- [ ] Time slot conflict detection
- [ ] Appointment reminder via cron job + email
- [ ] iCal / `.ics` export for Google Calendar
- [ ] Patient notes / internal admin notes per appointment
- [ ] Config file for clinic name, email, timezone, and available slots
- [ ] Print-friendly appointment detail view

---

## 🤝 Contributing

Contributions are welcome! Feel free to fork this repo, open issues, or submit pull requests.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/email-notifications`)
3. Commit your changes (`git commit -m 'Add email confirmation on booking'`)
4. Push to the branch (`git push origin feature/email-notifications`)
5. Open a Pull Request

---

## 🔗 Related Projects
| Project | Description |
|---|---|
| [MediTrack Clinic CRM](https://github.com/ahmed-ali-codes/meditrack-clinic-crm) | Same stack, purpose-built for general clinics |

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

## 👨💻 Author

**Ahmed Ali**
- GitHub: [@ahmed-ali-codes](https://github.com/ahmed-ali-codes)
- LinkedIn: [linkedin.com/in/ahmed-ali-jawad](https://linkedin.com/in/ahmed-ali-jawad)
- Agency: [Ecotrustia Solutions](https://ecotrustiasolutions.com)

---

> Built with ❤️ for small dental clinics who need a simple, reliable booking system without the overhead.
