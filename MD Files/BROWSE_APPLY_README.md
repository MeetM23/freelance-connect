# Browse & Apply for Projects – Freelance Connect

This module allows **freelancers** to browse active projects posted by clients and submit proposals. It connects freelancers to opportunities on the Freelance Connect platform.

---

## 🧾 Features

- 🔍 **Advanced Search & Filtering**: Search projects by title, description, skills, category, budget range, and project type
- 📃 **Project Browsing**: View detailed project cards with title, description, budget, deadline, and required skills
- 📤 **Proposal Submission**: Submit comprehensive proposals with message, budget, delivery time, and file attachments
- 📎 **File Upload**: Attach resumes, portfolios, or relevant documents (PDF, DOC, DOCX, JPG, PNG)
- 🧾 **Proposal Management**: Track submitted proposals and their status (pending, accepted, rejected)
- 📱 **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices

---

## 📁 Files Structure

```
freelance-connect/
├── browse-projects.php          # Main project browsing page
├── submit-proposal.php          # Proposal submission form
├── my-proposals.php             # Freelancer's proposal tracking
├── assets/css/
│   └── projects.css             # Styling for browse/proposal pages
├── uploads/                     # Directory for proposal attachments
├── config/
│   └── db.php                   # Database configuration
└── includes/
    ├── header.php               # Site header
    └── footer.php               # Site footer
```

---

## 🧰 Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome 6.0
- **Fonts**: Google Fonts (Inter)

---

## 🗃️ Database Integration

### Projects Table Query

```sql
SELECT p.*,
       c.name as category_name,
       u.first_name, u.last_name,
       COUNT(pr.id) as proposal_count
FROM projects p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN users u ON p.client_id = u.id
LEFT JOIN proposals pr ON p.id = pr.project_id
WHERE p.status = 'open'
GROUP BY p.id
ORDER BY p.created_at DESC
```

### Proposals Table

```sql
CREATE TABLE proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    cover_letter TEXT NOT NULL,
    bid_amount DECIMAL(10,2) NOT NULL,
    delivery_time INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (freelancer_id) REFERENCES users(id)
);
```

---

## 🔍 Search & Filter Features

### Available Filters:

- **Text Search**: Project title, description, and required skills
- **Category**: Filter by project category (Web Development, Design, etc.)
- **Project Type**: Fixed price or hourly rate projects
- **Budget Range**: Min and max budget filters
- **Auto-submit**: Filters apply automatically when changed

### Search Logic:

```php
$where_conditions = ["p.status = 'open'"];

if (!empty($search)) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.skills_required LIKE ?)";
}

if (!empty($category)) {
    $where_conditions[] = "p.category_id = ?";
}

if (!empty($budget_min)) {
    $where_conditions[] = "p.budget_max >= ?";
}

if (!empty($budget_max)) {
    $where_conditions[] = "p.budget_min <= ?";
}
```

---

## 📤 Proposal Submission Process

### Form Fields:

1. **Proposal Message** (required, min 50 characters)
2. **Proposed Budget** (required, numeric)
3. **Delivery Time** (required, days)
4. **File Attachment** (optional, max 5MB)

### Validation Rules:

- Message must be at least 50 characters
- Budget must be a positive number
- Delivery time must be a positive number
- File types: PDF, DOC, DOCX, JPG, JPEG, PNG
- File size limit: 5MB
- One proposal per freelancer per project

### File Upload Process:

```php
$filename = 'proposal_' . $project_id . '_' . $user_id . '_' . time() . '.' . $file_extension;
$upload_path = $upload_dir . $filename;

if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
    $attachment_path = $upload_path;
}
```

---

## 🔐 Access Control & Security

### User Authentication:

- Only logged-in freelancers can access browse and proposal pages
- Clients are redirected to login page
- Session-based authentication

### Data Protection:

- SQL injection prevention with prepared statements
- XSS prevention with `htmlspecialchars()`
- File upload validation and sanitization
- CSRF protection through session validation

### File Security:

- Restricted file types and sizes
- Unique filename generation
- Upload directory isolation

---

## 🎨 UI/UX Features

### Project Cards Display:

- Project title and description
- Budget range with visual indicators
- Required skills as tags
- Client information
- Proposal count
- Posted date
- Project type (fixed/hourly)

### Interactive Elements:

- Hover effects on project cards
- Loading states during form submission
- File upload preview
- Auto-focus on form fields
- Responsive design for all screen sizes

### Color-coded Status:

- **Pending**: Yellow (#fff3cd)
- **Accepted**: Green (#d4edda)
- **Rejected**: Red (#f8d7da)

---

## 📱 Responsive Design

### Breakpoints:

- **Desktop**: 1200px+ (full layout)
- **Tablet**: 768px-1199px (adjusted grid)
- **Mobile**: <768px (single column)

### Mobile Optimizations:

- Touch-friendly buttons
- Simplified navigation
- Optimized form layouts
- Reduced padding and margins

---

## 🚀 How to Use

### For Freelancers:

1. **Browse Projects**:

   - Visit `/browse-projects.php`
   - Use search and filters to find relevant projects
   - Click "View Details" for more information

2. **Submit Proposal**:

   - Click "Submit Proposal" on any project
   - Fill out the proposal form
   - Upload relevant files (optional)
   - Submit and wait for client response

3. **Track Proposals**:
   - Visit `/my-proposals.php`
   - View status of all submitted proposals
   - Edit pending proposals if needed

### For Developers:

1. **Setup Database**:

   ```sql
   -- Ensure proposals table exists
   -- Import sample data if needed
   ```

2. **Configure Upload Directory**:

   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

3. **Test Functionality**:
   - Create test projects as a client
   - Submit proposals as a freelancer
   - Verify file uploads work correctly

---

## 🔧 Customization

### Adding New Filters:

1. Add filter fields to the search form
2. Update the PHP query building logic
3. Add corresponding CSS styles

### Modifying File Upload:

1. Update allowed file types in `$allowed_types` array
2. Adjust file size limit in validation
3. Modify upload directory structure

### Styling Changes:

1. Edit `assets/css/projects.css`
2. Update color scheme in CSS variables
3. Modify responsive breakpoints as needed

---

## 🐛 Troubleshooting

### Common Issues:

1. **File Upload Fails**:

   - Check upload directory permissions
   - Verify PHP upload settings in php.ini
   - Ensure directory exists and is writable

2. **Search Not Working**:

   - Check database connection
   - Verify table structure matches queries
   - Test with simple search terms first

3. **Proposal Submission Errors**:
   - Check form validation rules
   - Verify database table structure
   - Review error logs for specific issues

### Debug Mode:

Add `?debug=1` to URLs to see:

- Session data
- Form submission data
- Database query results

---

## 📈 Future Enhancements

### Planned Features:

- **Advanced Search**: Full-text search with relevance scoring
- **Proposal Templates**: Pre-built proposal templates
- **Bulk Actions**: Apply to multiple projects at once
- **Notifications**: Email/SMS alerts for proposal status changes
- **Analytics**: Proposal success rate tracking
- **Rating System**: Client feedback on proposals

### Technical Improvements:

- **Caching**: Redis/Memcached for better performance
- **API Endpoints**: RESTful API for mobile apps
- **Real-time Updates**: WebSocket integration
- **Advanced Security**: Two-factor authentication

---

## 📞 Support

For technical support or feature requests:

- Check the troubleshooting section above
- Review error logs for specific issues
- Test with sample data to isolate problems
- Contact the development team for assistance

---

## 📝 License

This module is part of the Freelance Connect platform and is licensed under the MIT License. See the main LICENSE file for details.
