# Freelance Connect - Upwork Style Homepage

A modern, responsive homepage inspired by **Upwork**, built using HTML, CSS, JavaScript for frontend, and PHP with MySQL for backend functionality. This is part of the _Freelance Connect_ platform, aimed at connecting clients and freelancers.

## 🌟 Features

- ✅ **Modern Homepage Layout** - Clean, professional design inspired by Upwork
- ✅ **Hero Banner with Search** - Prominent search functionality with call-to-action
- ✅ **Categories Section** - Popular freelance service categories with icons
- ✅ **Trusted By Section** - Social proof with company logos
- ✅ **Statistics Section** - Animated stats showcasing platform success
- ✅ **How It Works** - Step-by-step guide for users
- ✅ **Responsive Design** - Mobile-friendly across all devices
- ✅ **Interactive Elements** - Smooth animations and hover effects
- ✅ **Modular PHP Structure** - Reusable header and footer components
- ✅ **Database Ready** - MySQL connection and sample data included

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Icons**: Font Awesome 6.0
- **Fonts**: Inter (Google Fonts)
- **Local Server**: XAMPP / WAMP / MAMP

## 📁 Project Structure

```
freelance-connect/
├── index.php                 # Main homepage
├── assets/
│   ├── css/
│   │   └── style.css        # All custom styles
│   ├── js/
│   │   └── script.js        # Interactive features
│   └── images/              # Image assets
├── includes/
│   ├── header.php           # Common header/navigation
│   └── footer.php           # Common footer
├── config/
│   └── db.php              # Database configuration
├── freelance_connect.sql    # Database structure & sample data
└── README.md               # This file
```

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or local development environment

### Installation

1. **Clone or Download the Project**

   ```bash
   git clone https://github.com/your-username/freelance-connect.git
   cd freelance-connect
   ```

2. **Set Up Local Server**

   - Install XAMPP, WAMP, or MAMP
   - Start Apache and MySQL services
   - Place the project in your web server's document root

3. **Database Setup**

   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
   - Create a new database named `freelance_connect`
   - Import the `freelance_connect.sql` file

4. **Configure Database Connection**

   - Edit `config/db.php` if needed:

   ```php
   $host = "localhost";
   $user = "root";
   $password = "";
   $dbname = "freelance_connect";
   ```

5. **Access the Website**
   - Open your browser and navigate to:
   ```
   http://localhost/freelance-connect/
   ```

## 🎨 Design Features

### Color Scheme

- **Primary Green**: `#14a800` (Upwork-inspired)
- **Dark Green**: `#118f00` (Hover states)
- **Text**: `#333` (Dark gray)
- **Background**: `#f8f9fa` (Light gray)

### Typography

- **Font Family**: Inter (Google Fonts)
- **Weights**: 300, 400, 500, 600, 700

### Responsive Breakpoints

- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

## 🔧 Customization

### Adding New Categories

1. Edit `index.php` to add new category cards
2. Update the database with new categories in `freelance_connect.sql`
3. Add corresponding CSS styles in `style.css`

### Modifying Colors

Update the CSS custom properties in `style.css`:

```css
:root {
  --primary-color: #14a800;
  --secondary-color: #118f00;
  --text-color: #333;
  --bg-color: #f8f9fa;
}
```

### Adding New Sections

1. Create the HTML structure in `index.php`
2. Add corresponding CSS styles in `style.css`
3. Include any JavaScript functionality in `script.js`

## 📱 Mobile Features

- **Hamburger Menu** - Collapsible navigation for mobile devices
- **Touch-Friendly** - Large buttons and touch targets
- **Responsive Images** - Optimized for different screen sizes
- **Smooth Scrolling** - Native-like scrolling experience

## 🎯 Interactive Elements

- **Animated Statistics** - Numbers count up on scroll
- **Hover Effects** - Cards lift and scale on hover
- **Smooth Scrolling** - Anchor links scroll smoothly
- **Mobile Navigation** - Animated hamburger menu
- **Search Functionality** - Form validation and submission

## 🔮 Future Enhancements

- [ ] User authentication system
- [ ] Project listing and search
- [ ] User profiles and portfolios
- [ ] Messaging system
- [ ] Payment integration
- [ ] Review and rating system
- [ ] Advanced search filters
- [ ] Real-time notifications

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**

   - Verify MySQL is running
   - Check database credentials in `config/db.php`
   - Ensure database `freelance_connect` exists

2. **Page Not Loading**

   - Check if Apache is running
   - Verify file permissions
   - Check error logs in your web server

3. **Styles Not Loading**
   - Verify CSS file path
   - Check browser console for errors
   - Ensure Font Awesome CDN is accessible

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

If you have questions or need help:

- Create an issue on GitHub
- Check the troubleshooting section above
- Review the code comments for guidance

---

**Built with ❤️ for the freelance community**
