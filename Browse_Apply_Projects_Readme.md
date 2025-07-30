# Browse & Apply for Projects – Freelance Connect

This module allows **freelancers** to browse active projects posted by clients and submit proposals. It connects freelancers to opportunities on the Freelance Connect platform.

---

## 🧾 Features

- 🔍 Freelancers can view a list of available projects
- 📃 Each project displays title, description, budget, deadline, and skills required
- 📤 Freelancers can submit proposals with message and proposed budget
- 📎 Option to attach a file (like resume or portfolio)
- 🧾 Submitted proposals are stored and can be managed by clients

---

## 📁 Files Involved

```
freelance-connect/
├── freelancer/
│   ├── browse-projects.php
│   └── submit-proposal.php
├── uploads/               (for proposal attachments)
├── config/
│   └── db.php
├── includes/
│   ├── header.php
│   └── footer.php
```

---

## 🧰 Tech Used

- **Frontend**: HTML, CSS, JS (optional for interactivity)
- **Backend**: PHP with MySQLi
- **Database**: MySQL

---

## 🗃️ Database Table: `proposals`

```sql
CREATE TABLE proposals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT,
  freelancer_id INT,
  message TEXT,
  proposed_budget DECIMAL(10,2),
  attachment VARCHAR(255),
  status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (freelancer_id) REFERENCES users(id)
);
```

---

## 📋 Form Fields (`submit-proposal.php`)

- Project ID (via `GET`)
- Message (textarea)
- Proposed Budget (input)
- File Attachment (PDF, DOCX, JPG, etc. – optional)

All fields are validated before submission.

---

## 🔐 Access Control

Only users logged in as `freelancer` can access these pages. Clients are redirected.

---

## ✅ Next Step

After a proposal is submitted:
- Client will see the proposal on the **View Proposals** page
- Client can accept or reject the proposal
- If accepted, user is redirected to a **Deal Page** (real-time messaging, file exchange, etc.)

---

## 🙋 Support

For help or suggestions, please contact the developer or raise an issue.

---

## 📝 License

This module is open-source and free to use under the [MIT License](LICENSE).
