# Post New Project – Freelance Connect

This module allows **clients** to post new freelance projects on the Freelance Connect platform. Projects are stored in the database and later visible to freelancers for browsing and applying.

---

## 🧾 Features

- 🎯 Client can post a project with title, description, budget, skills, and deadline
- 📥 Projects are saved in a `projects` table
- 📅 Includes date/time of posting
- 📌 Automatically links project to the logged-in client (via `client_id` session)

---

## 📁 Files Involved

```
freelance-connect/
├── client/
│   └── post-project.php
├── config/
│   └── db.php
├── includes/
│   ├── header.php
│   └── footer.php
```

---

## 🧰 Tech Used

- **Frontend**: HTML, CSS
- **Backend**: PHP (with MySQLi)
- **Database**: MySQL

---

## 🗃️ Database Table: `projects`

```sql
CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT,
  title VARCHAR(255),
  description TEXT,
  budget DECIMAL(10, 2),
  skills_required VARCHAR(255),
  deadline DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES users(id)
);
```

---

## 📋 Form Fields (`post-project.php`)

- Project Title (`title`)
- Project Description (`description`)
- Budget (`budget`)
- Skills Required (`skills_required`)
- Deadline (`deadline` - date input)

All fields are validated before submission.

---

## 🔐 Access Control

Only logged-in users with role `client` can access `post-project.php`. Others are redirected.

---

## ✅ Next Step

After project submission:
- Show success message and redirect to `my-projects.php`
- Freelancers will see these projects on the **Browse Projects** page

---

## 🙋 Support

For help or suggestions, please contact the developer or raise an issue.

---

## 📝 License

This module is open-source and free to use under the [MIT License](LICENSE).
