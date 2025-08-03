# 🛠️ Admin Panel Setup Guide - Freelance Connect

This guide will help you set up and use the Admin Panel for the Freelance Connect platform.

---

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB database
- Web server (Apache/Nginx)
- Freelance Connect platform already installed

---

## 🚀 Installation Steps

### 1. Database Setup

First, run the admin database schema to create the necessary tables:

```sql
-- Run this SQL in your database
SOURCE admin_schema.sql;
```

Or manually execute the SQL commands from `admin_schema.sql` in your database.

### 2. Default Admin Account

The schema creates a default admin account:

- **Email**: admin@freelanceconnect.com
- **Password**: admin123
- **Username**: admin

⚠️ **Important**: Change the default password immediately after first login!

---

## 🔐 Accessing the Admin Panel

1. Navigate to: `http://your-domain.com/freelance-connect/admin-login.php`
2. Login with the default credentials above
3. You'll be redirected to the admin dashboard

---

## 📊 Admin Panel Features

### Dashboard (`admin-dashboard.php`)

- Platform overview statistics
- Recent user registrations
- Recent project posts
- Quick navigation to other admin sections

### User Management (`admin-users.php`)

- View all platform users (clients & freelancers)
- Filter by user type and status
- Search users by name, username, or email
- Actions: Suspend, Activate, Ban, Delete users

### Project Management (`admin-projects.php`)

- View all posted projects
- Filter by status and category
- Search projects by title, description, or client
- Actions: Disable, Activate, Delete projects

### Proposal Management (`admin-proposals.php`)

- Review all freelancer proposals
- Filter by status and project
- Search proposals by content
- Actions: Approve, Reject, Delete proposals

### Admin Settings (`admin-settings.php`)

- Update admin profile information
- Change admin password
- View login history
- System information

---

## 🔧 Configuration

### Database Connection

The admin panel uses the same database configuration as the main platform (`config/db.php`).

### Security Features

- Session-based authentication
- Password hashing using PHP's `password_hash()`
- Login history tracking
- IP address logging
- Secure logout functionality

---

## 🛡️ Security Best Practices

1. **Change Default Password**: Immediately change the default admin password
2. **Strong Passwords**: Use strong, unique passwords for admin accounts
3. **HTTPS**: Always use HTTPS in production
4. **Regular Updates**: Keep PHP and database software updated
5. **Access Control**: Limit admin panel access to authorized personnel only
6. **Backup**: Regularly backup your database

---

## 📱 Responsive Design

The admin panel is fully responsive and works on:

- Desktop computers
- Tablets
- Mobile devices

---

## 🎨 Customization

### Styling

All styles are included inline in each page. You can customize the appearance by modifying the CSS in each file.

### Branding

Update the following elements to match your brand:

- Color scheme (currently uses purple gradient)
- Logo/icon references
- Platform name references

---

## 🔍 Troubleshooting

### Common Issues

1. **Login Not Working**

   - Verify database connection
   - Check if admin table exists
   - Ensure password is correct

2. **Database Errors**

   - Verify all required tables exist
   - Check database permissions
   - Review error logs

3. **Session Issues**
   - Ensure PHP sessions are enabled
   - Check session storage permissions
   - Verify session configuration

### Error Logs

Check your web server error logs for detailed error messages.

---

## 📞 Support

For issues or questions:

1. Check the troubleshooting section above
2. Review the main Freelance Connect documentation
3. Check PHP and MySQL error logs

---

## 🔄 Updates

To update the admin panel:

1. Backup your current installation
2. Replace admin panel files with new versions
3. Run any new database migrations
4. Test functionality

---

## 📄 File Structure

```
freelance-connect/
├── admin-login.php          # Admin login page
├── admin-dashboard.php      # Main dashboard
├── admin-users.php          # User management
├── admin-projects.php       # Project management
├── admin-proposals.php      # Proposal management
├── admin-settings.php       # Admin settings
├── admin-logout.php         # Logout functionality
├── admin_schema.sql         # Database schema
└── ADMIN_PANEL_SETUP.md     # This file
```

---

## ✅ Quick Start Checklist

- [ ] Run `admin_schema.sql` in your database
- [ ] Access admin panel at `/admin-login.php`
- [ ] Login with default credentials
- [ ] Change default password
- [ ] Review and customize settings
- [ ] Test all admin functions
- [ ] Set up proper security measures

---

**🎉 Congratulations! Your Admin Panel is now ready to use.**
