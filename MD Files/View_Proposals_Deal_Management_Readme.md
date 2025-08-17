# View Proposals & Deal Management – Freelance Connect

This module allows **clients** to manage proposals submitted by freelancers and initiate deals for accepted proposals. It connects both users for collaboration after proposal acceptance.

---

## 🧾 Features

### 🔹 View Proposals (`view-proposals.php`)

- Clients can see all proposals submitted for each of their projects
- Displays freelancer name, proposal message, proposed budget, and attached files
- Clients can **Accept** or **Reject** proposals

### 🔹 Deal Page (`deal-page.php`)

- Chat interface between client and freelancer
- Supports real-time messaging via AJAX or polling
- File upload support (images, docs)
- Message "seen" status tracking
- Status updates (Ongoing / Completed)

---

## 📁 Files Involved

```
freelance-connect/
├── client/
│   └── view-proposals.php
├── shared/
│   └── deal-page.php
├── assets/
│   └── js/
│       └── chat.js
├── uploads/               (for shared files)
├── config/
│   └── db.php
```

---

## 🗃️ Database Tables Used

### `proposals` (extended)

```sql
ALTER TABLE proposals ADD status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending';
```

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

### `messages`

```sql
CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  deal_id INT,
  sender_id INT,
  message TEXT,
  file VARCHAR(255),
  seen BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (deal_id) REFERENCES deals(id)
);
```

---

## 🔐 Access Control

- Only `client` can access `view-proposals.php`
- Only `client` and the relevant `freelancer` can access `deal-page.php`

---

## 🔄 Flow Summary

1. Freelancer submits a proposal for a project
2. Client visits `view-proposals.php` to view proposals
3. On accepting a proposal:
   - A new deal is created in the `deals` table
   - Both users are redirected to `deal-page.php?id=DEAL_ID`
4. They can now exchange messages and files

---

## 🧪 Optional Enhancements

- Push notifications for new messages
- Seen status per message
- File type restriction and size limits

---

## 🙋 Support

For help or suggestions, please contact the developer or raise an issue.

---

## 📝 License

This module is open-source and free to use under the [MIT License](LICENSE).
