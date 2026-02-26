# Kafila Student Service & Logistics Engine 🛡️

## 📌 Project Overview
This system was developed to solve a critical administrative bottleneck at **Kafila International Islamic School**. Previously, managing student phone lending, communication tracking, and expense logging for **350+ students** was handled manually. 

This platform transforms those manual workflows into a centralized, secure digital ecosystem, ensuring high data integrity and administrative efficiency.

## 🚀 Key Features
- **High-Volume Transaction Logic:** Custom-built engine to handle concurrent service requests for a large student body.
- **Automated Expense Tracking:** Integrated financial logging for service usage, replacing manual bookkeeping.
- **Secure Administrative Dashboard:** Real-time visibility for staff to monitor device status and student logs.
- **Privacy-Centric Architecture:** Designed specifically for internal network deployment to prevent unauthorized external access.

## 🛡️ Security & Strategic Deployment
To meet international security standards for educational institutions, this project utilizes a **Private Network Deployment strategy**:
1. **Anti-Manipulation:** By deploying on a controlled local environment, we eliminated the risk of tech-savvy students attempting to intercept or manipulate data via public domains.
2. **Zero-Latency Data Integrity:** Optimized for internal server performance to ensure real-time logging without external internet delays.
3. **Role-Based Access Control (RBAC):** Strict permission layers to ensure only authorized personnel can access sensitive student financial data.

## 🛠️ Tech Stack & Architecture
- **Language:** Native PHP 8.x
- **Database Driver:** PDO (PHP Data Objects) for secure, prepared SQL statements.
- **Frontend:** Tailwind CSS & Bootstrap 5 for responsive administrative UI.
- **Key Logic:**
  - **Dynamic Billing Engine:** Scripted in `history.php` to calculate real-time costs based on time blocks (e.g., Rp 1.000 for first 15 mins, subsequent 5-min increments).
  - **Data Management:** CRUD operations for managing device inventories and student interaction logs.
  - **State Management:** Utilizing PHP Sessions for flash messages and transaction status.
  
## 📂 Installation (For Internal Staff)
1. Clone the repository to the local server.
2. Run `composer install` & `npm install`.
3. Configure `.env` for local database connection.
4. Run `php artisan migrate --seed`.
5. Access via local institutional IP.

---
*Note: Some sensitive configuration files and student data directories are excluded from this repository to comply with privacy regulations.*
