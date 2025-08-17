# Register & Login Module – Freelance Connect

This module adds **user registration and login** functionality to the Freelance Connect platform. It supports both **Freelancers** and **Clients** using a single form.

## 🧾 Features

- 🔐 **User Registration** - Register with Name, Email, Password, User Type (Freelancer/Client)
- 🔑 **Secure Login** - Login using email and hashed passwords
- 🔒 **Password Security** - Password hashing with `password_hash()` and verification with `password_verify()`
- 🧪 **Form Validation** - Comprehensive validation and error handling
- 📂 **Session Management** - Secure session handling and user authentication
- 🎨 **Modern UI** - Clean, responsive design with smooth animations
- 📱 **Mobile Friendly** - Optimized for all device sizes

## 📁 Files Created

```
freelance-connect/
├── register.php              # User registration page
├── login.php                 # User login page
├── logout.php                # Logout functionality
├── dashboard.php             # Dashboard redirector
├── freelancer-dashboard.php  # Freelancer dashboard
├── client-dashboard.php      # Client dashboard
├── assets/
│   └── css/
│       └── auth.css          # Authentication page styles
└── AUTH_README.md           # This file
```

## 🗃️ Database Integration

The module uses the existing `users` table structure:

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  user_type ENUM('freelancer', 'client') NOT NULL,
  profile_image VARCHAR(255),
  bio TEXT,
  skills TEXT,
  hourly_rate DECIMAL(10,2),
  location VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 📝 How It Works

### 🔹 Registration Flow (`register.php`)

1. User fills in name, email, password, user_type (Freelancer or Client)
2. PHP validates all fields and checks for existing email
3. Password is hashed using `password_hash()` with `PASSWORD_DEFAULT`
4. User data is stored in the database
5. Success message is shown and user is redirected to login

### 🔹 Login Flow (`login.php`)

1. User enters email and password
2. PHP validates credentials using `password_verify()`
3. Session is created with user data
4. User is redirected to appropriate dashboard based on user type

### 🔹 Session Management

- Sessions are started on all pages
- User data is stored in `$_SESSION`
- Automatic redirects for authenticated users
- Secure logout functionality

## 🧪 Validation Features

### Registration Validation

- ✅ All fields required
- ✅ Valid email format
- ✅ Email uniqueness check
- ✅ Password minimum length (6 characters)
- ✅ Password confirmation match
- ✅ User type selection required

### Login Validation

- ✅ Email and password required
- ✅ Credential verification
- ✅ Session creation on success

## 🎨 UI/UX Features

### Design Elements

- **Modern Card Layout** - Clean white cards on gradient background
- **Interactive Forms** - Real-time validation and error display
- **Password Toggle** - Show/hide password functionality
- **Loading States** - Button loading animations
- **Responsive Design** - Works on all screen sizes

### User Experience

- **Auto-focus** - Email field focused on login page
- **Form Persistence** - Form data retained on validation errors
- **Clear Error Messages** - Specific error messages for each field
- **Success Feedback** - Clear success messages
- **Smooth Transitions** - Hover effects and animations

## 🔧 Technical Implementation

### Security Features

- **Password Hashing** - Uses PHP's built-in `password_hash()`
- **SQL Injection Prevention** - Prepared statements with PDO
- **XSS Prevention** - `htmlspecialchars()` for output
- **Session Security** - Proper session handling
- **Input Validation** - Server-side validation

### Code Structure

- **Modular Design** - Reusable components
- **Error Handling** - Comprehensive error management
- **Database Abstraction** - PDO for database operations
- **Clean Code** - Well-organized and commented

## 🚀 Usage

### For Users

1. **Register** - Visit `/register.php` to create an account
2. **Login** - Visit `/login.php` to sign in
3. **Dashboard** - Access your personalized dashboard
4. **Logout** - Click logout to end your session

### For Developers

1. **Database Setup** - Ensure the `users` table exists
2. **Configuration** - Check `config/db.php` for database settings
3. **Testing** - Test registration and login flows
4. **Customization** - Modify styles in `assets/css/auth.css`

## 🔮 Future Enhancements

- [ ] Email verification system
- [ ] Password reset functionality
- [ ] Social media login integration
- [ ] Two-factor authentication
- [ ] Remember me functionality
- [ ] Account settings page
- [ ] Profile completion wizard

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**

   - Verify database credentials in `config/db.php`
   - Ensure MySQL service is running

2. **Session Issues**

   - Check PHP session configuration
   - Verify session storage permissions

3. **Password Hashing Issues**

   - Ensure PHP version supports `password_hash()`
   - Check for proper password verification

4. **Form Validation Errors**
   - Verify all required fields are filled
   - Check email format and uniqueness

## 📞 Support

For questions or improvements:

- Check the main project README
- Review the code comments
- Test with different user scenarios

---

**Built with security and user experience in mind** 🔐✨
