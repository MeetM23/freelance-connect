# Register & Login Module – Freelance Connect

This module adds **user registration and login** functionality to the Freelance Connect platform. It supports both **Freelancers** and **Clients** using a single form.

---

## 🧾 Features

- 🔐 Register with Name, Email, Password, User Type (Freelancer/Client)
- 🔑 Secure login using email and hashed passwords
- 🔒 Password hashing with `password_hash()` and verification with `password_verify()`
- 🧪 Form validation and error handling
- 📂 Reusable layout with header and footer includes

---

## 📁 Folder Structure

```
freelance-connect/
├── register.php
├── login.php
├── assets/
│   └── css/
│       └── auth.css
├── includes/
│   ├── header.php
│   └── footer.php
├── config/
│   └── db.php
```

---

## 🧰 Tech Used

- **Frontend**: HTML, CSS (auth.css)
- **Backend**: PHP (with MySQLi)
- **Database**: MySQL

---

## 🗃️ Database Table: `users`

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  user_type ENUM('freelancer', 'client'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 📝 How It Works

### 🔹 Registration Flow (`register.php`)
1. User fills in name, email, password, user_type (Freelancer or Client)
2. PHP checks if email exists and validates password match
3. Password is hashed and stored in the database
4. User is redirected to login page upon successful registration

### 🔹 Login Flow (`login.php`) – *Coming Next*
- User enters email and password
- PHP validates credentials using `password_verify()`
- Starts session and redirects to the correct dashboard

---

## 🧪 Validation Checks

- All fields required
- Valid email format
- Passwords must match
- Email must be unique in the database

---

## 🎨 Styling

Custom styles are in `assets/css/auth.css`, with a clean and responsive layout.

---

## 🚀 Next Step

After registration, build `login.php` to authenticate users and redirect them to their respective dashboards based on role (`freelancer` or `client`).

---

## 🙋 Support

For questions or improvements, feel free to contact the developer or raise an issue.

---

## 📝 License

This module is open-source and free to use under the [MIT License](LICENSE).
