# 🛠️ Admin Panel – Freelance Connect

This document outlines the structure and functionality of the **Admin Panel** for the Freelance Connect platform.

---

## 🎯 Purpose of the Admin Panel

The Admin Panel provides administrative oversight of the platform. It allows the admin to manage users, projects, proposals, and deals while monitoring platform activity and ensuring content integrity.

---

## 🧭 Admin Panel Page Structure

### 1. `admin-login.php` – Admin Login Page
- Secure login with email and password
- Verifies admin credentials from the `admins` table
- Starts an admin session

---

### 2. `admin-dashboard.php` – Admin Dashboard
Displays platform overview stats:

| Metric | Description |
|--------|-------------|
| Total Users | All clients + freelancers |
| Total Projects | All posted projects |
| Active Deals | Ongoing deals between clients and freelancers |
| Completed Deals | Successfully finished collaborations |
| Pending Proposals | Proposals awaiting response |
| Recent Activity | Latest logins, project posts, user signups |

---

### 3. `admin-users.php` – User Management
Manage all platform users:

| Feature | Description |
|---------|-------------|
| View All Users | Clients + Freelancers |
| Filter by Role | Client / Freelancer |
| View Profile | Basic user data and project history |
| Suspend/Ban | Temporarily block platform access |
| Delete User | Remove permanently (with caution) |

---

### 4. `admin-projects.php` – Project Management
Monitor and moderate project listings:

| Feature | Description |
|---------|-------------|
| View All Projects | All client-submitted projects |
| View Project Owner | Client information |
| Delete/Disable | Remove spam, duplicates, or inactive projects |

---

### 5. `admin-proposals.php` – Proposal Management
Review all proposals made by freelancers:

| Feature | Description |
|---------|-------------|
| View All Proposals | Project-wise listing |
| View Freelancer | Who submitted the proposal |
| View Proposal Content | Budget, timeline, notes |
| Delete Proposal | Spam or inappropriate submissions |

---

### 6. `admin-deals.php` – Deal Oversight
Track deal status and activity:

| Feature | Description |
|---------|-------------|
| View All Deals | Ongoing + completed |
| View Deal Details | Project title, client, freelancer |
| Force Complete / Cancel | Optional emergency actions |

---

### 7. `admin-settings.php` – Admin Profile & Settings

| Feature | Description |
|---------|-------------|
| Update Password | Change admin login password |
| Update Profile | Name, email, image |
| View Login History | Recent admin access logs |

---

## 🔐 Database Tables Needed

| Table | Purpose |
|-------|---------|
| `admins` | Stores admin credentials and profile |
| `users` | Clients and freelancers (view only) |
| `projects` | All projects posted by clients |
| `proposals` | All proposals submitted by freelancers |
| `deals` | All accepted collaborations |
| `deal_files` | Files exchanged during deals |

---

## ✅ Summary

The Admin Panel enables backend control of all platform activities and ensures safety, moderation, and platform stability.
