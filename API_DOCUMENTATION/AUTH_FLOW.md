# ANVNA Care – Authentication Flow

This document explains the complete authentication system of ANVNA Care in beginner-friendly language.

---

## 🔐 How Authentication Works in ANVNA Care

ANVNA Care does **NOT use JWT tokens** like many modern APIs.  
Instead, it uses **PHP Sessions** — a traditional, server-side authentication method.

Think of it like this:
- When you log in at a hotel, they give you a **room key card**.
- Every time you access your room, you use that key card.
- When you check out, the key card is deactivated.

In ANVNA Care:
- Login = Getting your room key (the `PHPSESSID` cookie)
- Making API requests = Using the key card at the door
- Logout = Deactivating the key card

---

## 1. 📝 Registration Flow

```
Step 1: User fills the registration form
        Fields: name, email, phone, password

Step 2: Client sends POST /api/register.php
        {
          "name": "John Doe",
          "email": "john@example.com",
          "phone": "9876543299",
          "password": "secret123"
        }

Step 3: Server validates:
        - Name ≥ 3 characters
        - Valid email format
        - Phone = exactly 10 digits
        - Password ≥ 6 characters

Step 4: Server checks if email already exists in `users` table
        → If exists: returns HTTP 409 Conflict

Step 5: Server hashes the password using PHP password_hash()
        (bcrypt algorithm — the password is NEVER stored in plain text)

Step 6: Server inserts the user into the `users` table with role = 'user'

Step 7: Server auto-logs in the user:
        $_SESSION['user_id']    = new user ID
        $_SESSION['user_name']  = name
        $_SESSION['user_email'] = email
        $_SESSION['user_role']  = 'user'

Step 8: Server returns:
        HTTP 200
        {
          "success": true,
          "message": "Registration successful.",
          "user": { "id": 16, "name": "John Doe", "email": "...", "role": "user" }
        }

Step 9: The browser/client receives the PHPSESSID cookie automatically
```

### Diagram
```
Client                        Server                        Database
  |                              |                              |
  |-- POST /api/register.php --->|                              |
  |   {name, email, phone, pwd}  |                              |
  |                              |-- Validate inputs ---------->|
  |                              |-- Check email exists ------->|
  |                              |<-- Not found ----------------|
  |                              |-- Hash password              |
  |                              |-- INSERT INTO users -------->|
  |                              |<-- New user ID --------------|
  |                              |-- Start Session              |
  |<-- 200 OK ------------------|                              |
  |   {success, user{id,role}}   |                              |
  |   Set-Cookie: PHPSESSID=xyz  |                              |
```

---

## 2. 🔑 Login Flow

```
Step 1: User enters email and password
        Optionally checks "Remember Me" checkbox

Step 2: Client sends POST /api/login.php
        {
          "email": "amit.kumar@anvnacare.com",
          "password": "password123",
          "remember": true
        }

Step 3: Server checks rate limiting:
        - Reads $_SESSION['login_attempts_<IP_hash>']
        - If 5+ failed attempts in last 10 minutes → Return HTTP 429 (Too Many Requests)

Step 4: Server validates:
        - Email and password are not empty

Step 5: Server queries:
        SELECT * FROM users WHERE email = ?

Step 6: If user found, server verifies password:
        password_verify($submitted_password, $stored_hash)

Step 7a: If PASSWORD CORRECT:
        - Clear rate limit counter
        - Set session variables:
            $_SESSION['user_id']    = user['id']
            $_SESSION['user_name']  = user['name']
            $_SESSION['user_email'] = user['email']
            $_SESSION['user_role']  = user['role']
        - Merge guest cart into DB cart (if guest had items in cart)
        - If remember=true: Set cookie 'remember_user' for 30 days
        - Return HTTP 200 with user object

Step 7b: If PASSWORD WRONG:
        - Increment failed attempts counter
        - Return HTTP 401 with remaining attempts count

Step 8: Client stores the PHPSESSID cookie (automatically done by browser/Postman)
```

### Diagram
```
Client                        Server                        Database
  |                              |                              |
  |-- POST /api/login.php ------>|                              |
  |   {email, password}          |                              |
  |                              |-- Check rate limit           |
  |                              |-- SELECT * FROM users ------>|
  |                              |<-- user row -----------------|
  |                              |-- password_verify()          |
  |                              |   (bcrypt comparison)        |
  |                              |-- Write to $_SESSION         |
  |<-- 200 OK ------------------|                              |
  |   {success, user{id,role}}   |                              |
  |   Set-Cookie: PHPSESSID=abc  |                              |
  |   (Set-Cookie: remember_user)|                              |
```

---

## 3. 🛡️ Session-Based Authentication for Protected Endpoints

After login, the `PHPSESSID` cookie is automatically sent with every subsequent request.

```
Step 1: Client sends GET /api/profile.php
        Cookie: PHPSESSID=abc123xyz

Step 2: Server receives the cookie
        PHP reads the session from the session store

Step 3: Server checks:
        if (!isset($_SESSION['user_id'])) {
            return HTTP 401 Unauthorized
        }

Step 4: If session is valid:
        $userId = $_SESSION['user_id'];
        // Process the request using this userId

Step 5: Response is returned to the client
```

### What is stored in the Session?

| Session Variable     | Description               |
|----------------------|---------------------------|
| `$_SESSION['user_id']`    | User's database ID      |
| `$_SESSION['user_name']`  | User's full name        |
| `$_SESSION['user_email']` | User's email address    |
| `$_SESSION['user_role']`  | 'user' or 'admin'       |
| `$_SESSION['csrf_token']` | CSRF protection token   |
| `$_SESSION['guest_cart']` | Guest cart items (pre-login) |

---

## 4. 🛡️ CSRF Protection (Cross-Site Request Forgery)

CSRF attacks happen when a malicious website tricks your browser into making a request to another website (like your bank) using your logged-in session.

ANVNA Care prevents this with **CSRF tokens**.

### How CSRF Works in ANVNA Care

```
Step 1: When a page loads, the server generates a unique CSRF token:
        bin2hex(random_bytes(32))
        Example: "a4f2e9c7b1d3..."

Step 2: The token is stored in:
        $_SESSION['csrf_token'] = "a4f2e9c7b1d3..."

Step 3: The token is embedded in every HTML page's meta tag:
        <meta name="csrf-token" content="a4f2e9c7b1d3...">

Step 4: When the user submits a form or JavaScript sends a POST request,
        the token is included in ONE of these ways:
        a) Form field:     csrf_token=a4f2e9c7b1d3...
        b) JSON body:      {"csrf_token": "a4f2e9c7b1d3..."}
        c) HTTP Header:    X-CSRF-Token: a4f2e9c7b1d3...

Step 5: The server validates the token:
        if ($requestToken !== $_SESSION['csrf_token']) {
            return HTTP 403 Forbidden
        }
```

### Which endpoints require CSRF?

All **state-changing** endpoints (POST, PUT, DELETE) require a CSRF token:
- Cart (add, update, remove)
- Wishlist (add, remove)
- Address (add, delete, set_default)
- Order (place order)
- Appointments (book, cancel)
- Profile (update_profile, change_password)

**Endpoints that do NOT require CSRF** (read-only or public):
- Register and Login
- Medicines, Products, Doctors (GET)
- Search (GET)
- Coupon (GET)
- Profile (GET)
- Address (GET)

### For API Testers:
To bypass CSRF when testing with Postman or Rest Assured:
1. First login using a **browser** or Postman
2. Get the CSRF token from the HTML page's `<meta name="csrf-token">` tag
3. Copy that token value
4. Include it as the `X-CSRF-Token` header in all POST/DELETE requests

---

## 5. 🍪 Remember Me (Persistent Login)

```
When "Remember Me" is checked during login:

Step 1: Server sets a cookie:
        setcookie('remember_user', $user['email'], time() + (30 * 24 * 60 * 60), "/");
        (Cookie expires in 30 days)

Step 2: On every page load, header.php checks:
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
            // Restore session from the cookie
            $email = $_COOKIE['remember_user'];
            // Look up user in database and set session
        }

Step 3: If the "Remember Me" cookie is present and valid,
        the user is automatically re-authenticated without needing to log in again
```

---

## 6. 🚪 Logout Flow

```
Step 1: User clicks "Logout" link
        Client sends: GET /logout.php

Step 2: Server clears all session variables:
        $_SESSION = [];

Step 3: Server destroys the session cookie (PHPSESSID):
        setcookie(session_name(), '', time() - 42000, ...);

Step 4: Server clears the "Remember Me" cookie:
        setcookie('remember_user', '', time() - 3600, "/");

Step 5: Server destroys the PHP session:
        session_destroy();

Step 6: Server redirects to the homepage:
        header("Location: index.php");

After logout:
- Any request to protected endpoints returns HTTP 401
- The PHPSESSID cookie is no longer valid
```

---

## 7. 🔢 Rate Limiting (Login Protection)

To prevent brute-force attacks (guessing passwords), ANVNA Care limits login attempts:

```
Rules:
- Maximum 5 failed login attempts per IP address
- Window: 10 minutes (600 seconds)
- After 5 failures: HTTP 429 "Too Many Requests"
- Counter resets after a successful login

How it works:
- Failed attempts are stored in the PHP session:
  $_SESSION['login_attempts_<md5(IP)>'] = {count: 3, last: timestamp}

- On each failed attempt, count is incremented
- If count >= 5 and last attempt was < 10 minutes ago: BLOCKED

- Error message includes "remaining attempts": 
  "Invalid email or password. 3 attempt(s) remaining before lockout."

- After lockout: 
  "Too many failed login attempts. Please wait 8 minute(s) and try again."
```

---

## 8. 🧪 Testing the Auth Flow with Postman

### Step-by-Step for Students:

1. **Open Postman** and import the `POSTMAN_COLLECTION.json` and `POSTMAN_ENVIRONMENT.json` files.

2. **Enable Cookie Jar** in Postman:
   - Go to Settings → General → Enable "Automatically follow redirects"
   - Make sure "Send cookies" is enabled

3. **Call the Login request** first:
   - Method: POST
   - URL: `{{baseUrl}}/api/login.php`
   - Body: `{"email": "amit.kumar@anvnacare.com", "password": "password123"}`
   - This sets the `PHPSESSID` cookie in Postman's Cookie Jar

4. **Get the CSRF token**:
   - Open a browser, go to `https://anvnacare.alwaysdata.net`
   - Login with the same credentials
   - Press F12 → Console → type: `document.querySelector('meta[name="csrf-token"]').content`
   - Copy that token value

5. **Set the CSRF token** in the Postman environment:
   - Click the eye icon next to Environment dropdown
   - Set `csrfToken` = the value you copied

6. **Now test protected endpoints**:
   - Add item to cart, book appointment, etc.
   - The PHPSESSID cookie and CSRF token will be sent automatically

---

## 9. 👤 User Roles

| Role  | Access Level                                      |
|-------|---------------------------------------------------|
| user  | Normal patient — can browse, buy, book appointments |
| admin | Admin panel access, full platform management       |

> **Role is set at registration** and cannot be changed through the public API.
> Only database administrators can promote a user to admin role.

### Test Accounts (from dummy_data.sql):

| Role  | Email                      | Password    |
|-------|----------------------------|-------------|
| admin | admin@anvnacare.com        | password123 |
| user  | amit.kumar@anvnacare.com   | password123 |
| user  | priya.sharma@anvnacare.com | password123 |
| user  | rohan.verma@anvnacare.com  | password123 |

---

*This documentation was generated using static analysis only. No existing project files were modified.*
