# Deal Page (Non-Chat Version) – Freelance Connect

This module provides a **complete deal page** where both **clients and freelancers** can view deal details, submit files, and track the progress of their collaboration.

---

## 🧾 Features

### 🔹 For Both (Client & Freelancer)
- View deal summary: project title, budget, deadline
- View counterpart’s profile (name, email, image)
- Track deal status (Ongoing / Completed / Cancelled)
- View and download uploaded files

### 🔹 For Freelancer
- Upload deliverables (images, docs, zip, etc.)

### 🔹 For Client
- Mark deal as "Completed" once satisfied

---

## 📁 Files Involved

```
freelance-connect/
├── shared/
│   └── deal-page.php
├── uploads/deals/            # For storing submitted work files
├── assets/
│   └── css/
│       └── deal-page.css
├── config/
│   └── db.php
```

---

## 🗃️ Database Tables

### `deals`
```sql
CREATE TABLE deals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proposal_id INT,
  client_id INT,
  freelancer_id INT,
  status ENUM('ongoing', 'completed', 'cancelled') DEFAULT 'ongoing',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proposal_id) REFERENCES proposals(id)
);
```

### `deal_files`
```sql
CREATE TABLE deal_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  deal_id INT,
  uploaded_by INT,
  file_name VARCHAR(255),
  file_path VARCHAR(255),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (deal_id) REFERENCES deals(id)
);
```

---

## 🔐 Access Control

- Only the client or freelancer involved in the deal can access `deal-page.php?id=DEAL_ID`

---

## 🧭 Page Flow

1. Client accepts a proposal → Deal is created
2. Both users redirected to `deal-page.php?id=DEAL_ID`
3. Freelancer uploads files
4. Client reviews and marks deal as completed
5. Status updates on page in real-time (via form submission)

---

## 📌 Status Change Handling

- Freelancer can only upload if status is `ongoing`
- Client can only complete deal if status is `ongoing`
- Completed deals become **read-only**

---

## 📎 File Upload Handling

- Allowed file types: `.zip`, `.rar`, `.docx`, `.pdf`, `.png`, `.jpg`
- Max size (recommended): 10MB per file
- Files stored in `/uploads/deals/`

---

## 🙋 Support

For support, contact the development team or open a ticket.

---

## 📝 License

Open-source under the [MIT License](LICENSE).
