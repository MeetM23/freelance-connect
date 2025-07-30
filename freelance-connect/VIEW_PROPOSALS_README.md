# View Proposals & Deal Management – Freelance Connect

This module allows **clients** to manage proposals submitted by freelancers and collaborate through a real-time chat interface. It provides a complete workflow from proposal review to project completion.

---

## 🧾 Features

### 🔹 View Proposals (`view-proposals.php`)

- **Proposal Management**: View all proposals submitted for client's projects
- **Advanced Filtering**: Filter proposals by project, status, and other criteria
- **Proposal Actions**: Accept or reject proposals with confirmation
- **Detailed Information**: View freelancer details, skills, hourly rates, and proposal content
- **Status Tracking**: Color-coded status indicators (pending, accepted, rejected)
- **Expandable Content**: Read more/less functionality for long proposals

### 🔹 Deal Page (`deal-page.php`)

- **Real-time Chat**: AJAX-powered messaging system with auto-refresh
- **File Sharing**: Upload and share documents, images, and files
- **Message Status**: Seen/unseen indicators for message tracking
- **Deal Management**: Update deal status (ongoing, completed, cancelled)
- **Project Information**: Display deal details, budget, and timeline
- **Responsive Design**: Works seamlessly on all devices

---

## 📁 Files Structure

```
freelance-connect/
├── view-proposals.php          # Main proposals viewing page
├── deal-page.php               # Deal chat interface
├── send-message.php            # AJAX message sending handler
├── get-messages.php            # AJAX message retrieval handler
├── database_updates.sql        # Database schema updates
├── assets/css/
│   └── proposals.css           # Styling for proposals and deal pages
├── uploads/                    # Directory for shared files
├── config/
│   └── db.php                  # Database configuration
└── includes/
    ├── header.php              # Site header
    └── footer.php              # Site footer
```

---

## 🧰 Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+
- **AJAX**: Fetch API for real-time messaging
- **File Upload**: Secure file handling with validation
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome 6.0
- **Fonts**: Google Fonts (Inter)

---

## 🗃️ Database Schema

### Updated `proposals` Table

```sql
ALTER TABLE proposals
ADD COLUMN status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending';
```

### New `deals` Table

```sql
CREATE TABLE deals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proposal_id INT NOT NULL,
    client_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    status ENUM('ongoing', 'completed', 'cancelled') DEFAULT 'ongoing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### New `messages` Table

```sql
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deal_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    file VARCHAR(255),
    seen BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🔄 Workflow Process

### 1. Proposal Review

1. Client logs in and visits `/view-proposals.php`
2. Views all proposals with filtering options
3. Reviews proposal details, freelancer information, and skills
4. Accepts or rejects proposals with confirmation

### 2. Deal Creation

1. When proposal is accepted:
   - New deal is automatically created
   - Client is redirected to deal page
   - Both parties can start messaging

### 3. Project Collaboration

1. Real-time chat interface for communication
2. File sharing capabilities
3. Deal status management
4. Message seen/unseen tracking

---

## 🔐 Access Control & Security

### User Authentication:

- Only logged-in clients can access proposal management
- Deal access restricted to involved parties only
- Session-based authentication throughout

### Data Protection:

- SQL injection prevention with prepared statements
- XSS prevention with `htmlspecialchars()`
- File upload validation and sanitization
- CSRF protection through session validation

### File Security:

- Restricted file types (PDF, DOC, DOCX, JPG, PNG, GIF)
- File size limits (10MB for messages)
- Unique filename generation
- Upload directory isolation

---

## 🎨 UI/UX Features

### Proposal Cards:

- **Status Indicators**: Color-coded badges (pending, accepted, rejected)
- **Expandable Content**: Read more/less for long proposals
- **Freelancer Details**: Skills, hourly rates, contact information
- **Action Buttons**: Accept/reject with confirmation dialogs
- **Project Information**: Budget, timeline, category

### Chat Interface:

- **Message Bubbles**: Different styles for sent/received messages
- **File Attachments**: Visual indicators for shared files
- **Seen Status**: Checkmarks for read messages
- **Auto-scroll**: Automatic scrolling to latest messages
- **Real-time Updates**: 5-second auto-refresh interval

### Responsive Design:

- **Desktop**: Full-featured interface
- **Tablet**: Optimized layout
- **Mobile**: Touch-friendly controls

---

## 📤 AJAX Implementation

### Message Sending (`send-message.php`):

```javascript
const response = await fetch("send-message.php", {
  method: "POST",
  body: formData,
});
```

### Message Retrieval (`get-messages.php`):

```javascript
const response = await fetch(`get-messages.php?deal_id=${dealId}`);
const result = await response.json();
```

### Auto-refresh:

```javascript
setInterval(loadMessages, 5000); // Refresh every 5 seconds
```

---

## 🔧 Configuration

### Database Setup:

1. Run `database_updates.sql` to create required tables
2. Ensure proper indexes for performance
3. Verify foreign key constraints

### File Upload:

1. Create `uploads/` directory with proper permissions
2. Configure PHP upload settings in `php.ini`
3. Set appropriate file size limits

### Security Settings:

1. Validate file types and sizes
2. Implement proper access controls
3. Use HTTPS for production deployment

---

## 🚀 How to Use

### For Clients:

1. **View Proposals**:

   - Visit `/view-proposals.php`
   - Use filters to find specific proposals
   - Review proposal details and freelancer information
   - Accept or reject proposals

2. **Manage Deals**:

   - Access deal page after accepting proposal
   - Use chat interface for communication
   - Share files and documents
   - Update deal status as needed

3. **Project Completion**:
   - Mark deals as completed when finished
   - Cancel deals if necessary
   - Review project outcomes

### For Developers:

1. **Setup Database**:

   ```sql
   -- Run database_updates.sql
   source database_updates.sql;
   ```

2. **Test Functionality**:

   - Create test proposals as freelancers
   - Accept proposals as clients
   - Test chat functionality
   - Verify file uploads

3. **Customization**:
   - Modify chat refresh interval
   - Adjust file upload limits
   - Customize UI styling
   - Add notification features

---

## 🐛 Troubleshooting

### Common Issues:

1. **Messages Not Sending**:

   - Check database connection
   - Verify file permissions
   - Review AJAX error logs
   - Test with simple messages first

2. **File Upload Fails**:

   - Check upload directory permissions
   - Verify PHP upload settings
   - Review file size limits
   - Test with different file types

3. **Chat Not Updating**:
   - Check JavaScript console for errors
   - Verify AJAX endpoints
   - Test network connectivity
   - Review server response codes

### Debug Mode:

Add error logging to troubleshoot issues:

```php
error_log("Message sending error: " . $e->getMessage());
```

---

## 📈 Future Enhancements

### Planned Features:

- **Push Notifications**: Real-time browser notifications
- **Message Search**: Search through chat history
- **Voice Messages**: Audio message support
- **Screen Sharing**: Built-in screen sharing
- **Payment Integration**: In-chat payment processing
- **Milestone Tracking**: Project milestone management

### Technical Improvements:

- **WebSocket Integration**: True real-time messaging
- **Message Encryption**: End-to-end encryption
- **File Compression**: Automatic file optimization
- **Message Templates**: Pre-built message templates
- **Advanced Analytics**: Chat and project analytics

---

## 🔧 Customization

### Styling Changes:

1. Edit `assets/css/proposals.css`
2. Modify color scheme and layout
3. Adjust responsive breakpoints
4. Customize chat bubble styles

### Functionality Modifications:

1. Update file upload limits in `send-message.php`
2. Modify chat refresh interval in `deal-page.php`
3. Add new message types or features
4. Implement additional security measures

### Database Extensions:

1. Add new fields to existing tables
2. Create additional related tables
3. Implement message threading
4. Add message reactions or emojis

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

---

## 🔄 Integration

This module integrates seamlessly with:

- **Browse & Apply Projects**: Proposal submission workflow
- **Post New Project**: Project creation and management
- **Register & Login**: User authentication system
- **Dashboard**: Navigation and user management

The complete workflow provides a full freelance platform experience from project posting to completion.
