# 🤝 Deal Page Functional Overview – Freelance Connect

This document explains the **core features, responsibilities, and logic** of the Deal Page after a client accepts a freelancer's proposal.

---

## 🔄 What Happens When a Proposal is Accepted?
- A new record is created in the `deals` table.
- Both **client** and **freelancer** are linked via a `deal_id`.
- They are redirected to: `deal-page.php?id=DEAL_ID`

---

## 👨‍💼 CLIENT SIDE – Responsibilities & Actions

| Function              | Description |
|-----------------------|-------------|
| ✅ View Project Details | Title, description, deadline, budget |
| 👤 View Freelancer Info | Name, email, profile image |
| 📂 View Submitted Files | Files uploaded by freelancer |
| 📥 Download Files      | Click to download deliverables |
| 🟢 Mark Deal as Completed | Closes the deal officially |
| ❌ Cancel Deal *(Optional)* | Feature to be added if needed |

- ✅ Only the **client** can mark the deal as **completed**.
- ❗Cannot upload files or modify submissions.

---

## 👨‍💻 FREELANCER SIDE – Responsibilities & Actions

| Function              | Description |
|-----------------------|-------------|
| ✅ View Project Summary | Title, budget, status, deadline |
| 👤 View Client Info     | Name, email, profile image |
| 📤 Upload Deliverables | Submit `.zip`, `.pdf`, `.jpg`, `.docx`, etc. |
| 📁 View Previous Uploads | File history per deal |
| 🚫 Cannot mark as completed | Only client has this permission |
| 🚫 Cannot cancel deal     | Optional future admin feature |

- ✅ Only the **freelancer** can **upload files**.
- ❗Cannot end or cancel deal.

---

## 📁 File Management

- **Folder**: `/uploads/deals/`
- **Table**: `deal_files`
- **Allowed Types**: `.zip`, `.pdf`, `.docx`, `.png`, `.jpg`
- **Max Size**: ~10MB (adjustable)
- Each upload is linked with `deal_id` and `uploaded_by`

---

## 🔄 Deal Status Flow

| Status        | Who Can Change It | Trigger Event |
|---------------|-------------------|---------------|
| `ongoing`     | System (default)  | Proposal accepted |
| `completed`   | Client only       | Client confirms work |
| `cancelled`   | *(Optional)*      | Future feature |

---

## 🔐 Access Control

- `deal-page.php?id=DEAL_ID` is accessible **only to**:
  - The **freelancer** involved
  - The **client** involved
- Others attempting access will be **redirected**

---

## 🧭 Visual Layout Summary

### CLIENT VIEW:
- Header: *Deal with Freelancer: John Doe*
- Project Info Section
- Freelancer Info Card
- Submitted Files List (with download buttons)
- **"Mark as Completed"** button

### FREELANCER VIEW:
- Header: *Deal with Client: Jane Smith*
- Project Info Section
- Client Info Card
- File Upload Form (if deal is ongoing)
- Upload History List

---

## ✅ Summary

The deal page is the **final stage** of collaboration, focusing on:
- Transparency between both parties
- Smooth file exchange
- A clear workflow to mark work as done

