# ANVNA Care – Project Overview

---

## 📌 Project Purpose

**ANVNA Care** is a full-featured **online healthcare platform** built with PHP and MySQL. It allows patients (users) to:

- Browse and purchase **medicines** from an online pharmacy
- Buy **health & wellness products** from a health store
- **Book appointments** with specialist doctors
- Book **lab tests / diagnostic packages**
- Manage a **shopping cart**, **wishlist**, and **delivery addresses**
- **Track orders** placed on the platform
- **Apply coupons** for discounts at checkout
- Manage their **user profile** and change passwords

The platform also has an **Admin Panel** (`/admin/index.php`) for administrative tasks.

The project is designed and deployed on **alwaysData** cloud hosting at:
```
https://anvnacare.alwaysdata.net
```

---

## 📁 Folder Structure

```
anvnacare/                          ← Project Root
│
├── api/                            ← All REST API endpoint files
│   ├── address.php                 ← Manage delivery addresses (GET / POST / DELETE)
│   ├── appointments.php            ← Book / Cancel appointments (POST)
│   ├── cart.php                    ← Add / Update / Remove cart items (POST / DELETE)
│   ├── coupon.php                  ← Validate coupon codes (GET)
│   ├── doctors.php                 ← Fetch doctors list with filters (GET)
│   ├── login.php                   ← User login with rate limiting (POST)
│   ├── medicines.php               ← Fetch medicines with filters (GET)
│   ├── order.php                   ← Place an order (POST)
│   ├── products.php                ← Fetch health store products (GET)
│   ├── profile.php                 ← Get / Update user profile (GET / POST)
│   ├── register.php                ← User registration (POST)
│   ├── search.php                  ← Global auto-suggestion search (GET)
│   └── wishlist.php                ← Add / Remove wishlist items (POST)
│
├── config/
│   └── database.php                ← PDO database connection configuration
│
├── database/
│   ├── schema.sql                  ← Database table definitions (DDL)
│   ├── dummy_data.sql              ← Seed data (15 users, 20 medicines, etc.)
│   └── migrate.php                 ← Migration helper script
│
├── includes/
│   ├── csrf.php                    ← CSRF token generation & validation helpers
│   ├── header.php                  ← Site-wide HTML header, navigation, cart counter
│   └── footer.php                  ← Site-wide HTML footer
│
├── assets/                         ← Static files (CSS, JS, images)
│   ├── css/style.css
│   └── images/
│       ├── medicines/
│       ├── products/
│       └── doctors/
│
├── admin/
│   └── index.php                   ← Admin dashboard (role-protected)
│
├── index.php                       ← Homepage
├── login.php                       ← Login page (HTML frontend)
├── register.php                    ← Registration page (HTML frontend)
├── logout.php                      ← Logout handler (clears session + cookie)
├── dashboard.php                   ← User dashboard
├── profile.php                     ← Profile management page
├── medicines.php                   ← Pharmacy browsing page
├── medicine-details.php            ← Single medicine detail page
├── doctors.php                     ← Doctor listing page
├── doctor-details.php              ← Single doctor detail page
├── cart.php                        ← Shopping cart page
├── checkout.php                    ← Checkout page
├── orders.php                      ← Order history page
├── wishlist.php                    ← Wishlist page
├── appointments.php                ← Appointments page
├── health-store.php                ← Health store page
├── lab-tests.php                   ← Lab tests page
├── about.php                       ← About us page
├── contact.php                     ← Contact page
├── faq.php                         ← FAQ page
├── forgot-password.php             ← Forgot password page
├── privacy.php                     ← Privacy policy page
├── terms.php                       ← Terms & conditions
├── 404.php                         ← Custom 404 error page
├── 500.php                         ← Custom 500 error page
├── qa-playground.php               ← QA testing playground page
└── anvnaaa.txt                     ← Deployment guide (alwaysData)
```

---

## 🛠️ Technologies Used

| Layer            | Technology                        |
|------------------|-----------------------------------|
| Backend Language | PHP 8.x                           |
| Database Driver  | PDO (PHP Data Objects)            |
| Frontend         | HTML5, CSS3, JavaScript (ES6+)    |
| CSS Framework    | Bootstrap 5.3                     |
| Icons            | Bootstrap Icons 1.10.5            |
| Database         | MySQL / MariaDB                   |
| Hosting          | alwaysData (Cloud Hosting)        |
| Web Server       | Apache / Nginx (PHP 8.2)          |
| Session Handling | PHP Native Sessions               |
| Security         | CSRF Tokens, bcrypt password hash |
| Data Format      | JSON (all API responses)          |

---

## 🗄️ Database Used

- **Database Type**: MySQL / MariaDB
- **Database Name**: `anvnacare_db`
- **Host**: `mysql-anvnacare.alwaysdata.net`
- **Port**: `3306`
- **Username**: `anvnacare_usr`
- **Charset**: `utf8mb4`

### Database Tables

| Table         | Description                                  |
|---------------|----------------------------------------------|
| `users`       | Registered users (patients and admin)        |
| `doctors`     | Doctor profiles with specialization and fees |
| `medicines`   | Medicine catalog with stock and pricing      |
| `products`    | Health store products                        |
| `tests`       | Lab test catalog                             |
| `cart`        | Shopping cart (per-user, persistent)         |
| `wishlist`    | User's wishlist (medicines & products)       |
| `addresses`   | Saved delivery addresses per user            |
| `orders`      | Placed orders                                |
| `order_items` | Individual items inside each order           |
| `coupons`     | Discount coupon codes                        |
| `appointments`| Doctor appointments booked by users          |

---

## 🔐 Authentication Flow

ANVNA Care uses **PHP Session-based authentication**. There is NO JWT token system.

```
Step 1: User sends POST request to /api/login.php
        Body: { email, password, remember (optional) }

Step 2: Server queries the `users` table, verifies password using password_verify()

Step 3: On success, server writes to PHP Session:
        $_SESSION['user_id']    = user's ID
        $_SESSION['user_name']  = user's name
        $_SESSION['user_email'] = user's email
        $_SESSION['user_role']  = 'user' or 'admin'

Step 4: A session cookie (PHPSESSID) is sent to the client automatically

Step 5: Subsequent API requests must include the same session cookie
        so the server can identify the logged-in user

Step 6: Optionally, a "Remember Me" cookie (remember_user=email) is set
        for 30 days so users stay logged in across browser restarts

Step 7: Logout: GET /logout.php destroys the session and clears cookies
```

> ⚠️ **For Postman Testing**: You MUST first call the login endpoint and enable
> "Cookie Jar" in Postman so that the PHPSESSID session cookie is automatically
> sent with subsequent requests.

---

## 🛡️ CSRF Protection

All **state-changing API endpoints** (POST, PUT, DELETE) require a valid **CSRF token**.

- The token is generated per session and stored in `$_SESSION['csrf_token']`
- The token can be sent via:
  1. Form field: `csrf_token` in the POST body
  2. JSON body field: `csrf_token` in JSON payload
  3. HTTP Header: `X-CSRF-Token: <token>`

> ⚠️ **For API Testing with Postman/Rest Assured**: You need to first visit
> any page (or call the login endpoint from a browser) to get a CSRF token,
> then include it in subsequent state-changing calls.
> Use the `X-CSRF-Token` header for convenience in API testing.

---

## 🌊 Request Flow

```
Client (Browser/Postman)
        │
        │  HTTP Request (GET/POST/DELETE)
        ▼
Apache/Nginx Web Server
        │
        ▼
PHP Script (e.g., api/cart.php)
        │
        ├─ Reads PHPSESSID Cookie → Identifies user
        ├─ Validates CSRF Token (for write operations)
        ├─ Parses JSON body or POST form data
        ├─ Validates input fields
        │
        ▼
config/database.php  ──→  MySQL Database (anvnacare_db)
        │                       │
        │   PDO Prepared         │
        │   Statements          │
        └───────────────────────┘
        │
        ▼
JSON Response returned to Client
```

---

## 📤 Response Flow

All API endpoints return **JSON responses** with the following structure:

### ✅ Success Response
```json
{
  "success": true,
  "message": "Human-readable success message",
  "data_key": { ... }   // optional, varies by endpoint
}
```

### ❌ Error Response
```json
{
  "success": false,
  "message": "Human-readable error message"
}
```

### Common HTTP Status Codes Used

| Code | Meaning                                         |
|------|-------------------------------------------------|
| 200  | OK – Request succeeded                          |
| 400  | Bad Request – Missing or invalid input          |
| 401  | Unauthorized – User not logged in               |
| 403  | Forbidden – CSRF token invalid                  |
| 404  | Not Found – Resource doesn't exist              |
| 409  | Conflict – Duplicate entry                      |
| 429  | Too Many Requests – Rate limit exceeded (login) |
| 500  | Internal Server Error – Database or code error  |

---

## 🌐 Base URL

```
https://anvnacare.alwaysdata.net
```

All API endpoints are located under:
```
https://anvnacare.alwaysdata.net/api/
```

### Example Endpoints:
```
GET  https://anvnacare.alwaysdata.net/api/medicines.php
POST https://anvnacare.alwaysdata.net/api/login.php
POST https://anvnacare.alwaysdata.net/api/cart.php
GET  https://anvnacare.alwaysdata.net/api/coupon.php?code=SAVE10&cart_value=500
```

---

## 🧪 Test Credentials (from dummy_data.sql)

| Role   | Email                        | Password     |
|--------|------------------------------|--------------|
| Admin  | admin@anvnacare.com          | password123  |
| User   | amit.kumar@anvnacare.com     | password123  |
| User   | priya.sharma@anvnacare.com   | password123  |
| User   | rohan.verma@anvnacare.com    | password123  |

---

*This documentation was generated using static analysis only. No existing project files were modified.*
