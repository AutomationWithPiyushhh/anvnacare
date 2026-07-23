# ANVNA Care – Complete API Collection

**Base URL:** `https://anvnacare.alwaysdata.net`  
**API Base Path:** `/api/`  
**Response Format:** `application/json` (all endpoints)

> 📌 **Important for Testers**: This API uses **PHP Session-based authentication**  
> (not JWT tokens). You must log in first via `/api/login.php` and keep the  
> `PHPSESSID` cookie active in Postman or your HTTP client.

---

## 📋 Table of Contents

1. [Auth – Register](#1-auth--register)
2. [Auth – Login](#2-auth--login)
3. [Auth – Logout](#3-auth--logout)
4. [Medicines – List/Search](#4-medicines--listsearch)
5. [Products – List/Search](#5-products--listsearch)
6. [Doctors – List/Search](#6-doctors--listsearch)
7. [Search – Global Auto-suggest](#7-search--global-auto-suggest)
8. [Cart – Add Item](#8-cart--add-item)
9. [Cart – Update Item Quantity](#9-cart--update-item-quantity)
10. [Cart – Remove Item](#10-cart--remove-item)
11. [Wishlist – Add Item](#11-wishlist--add-item)
12. [Wishlist – Remove Item](#12-wishlist--remove-item)
13. [Coupon – Validate Coupon](#13-coupon--validate-coupon)
14. [Address – Get All Addresses](#14-address--get-all-addresses)
15. [Address – Add New Address](#15-address--add-new-address)
16. [Address – Delete Address](#16-address--delete-address)
17. [Address – Set Default Address](#17-address--set-default-address)
18. [Order – Place Order](#18-order--place-order)
19. [Appointments – Book Appointment](#19-appointments--book-appointment)
20. [Appointments – Cancel Appointment](#20-appointments--cancel-appointment)
21. [Profile – Get Profile](#21-profile--get-profile)
22. [Profile – Update Profile](#22-profile--update-profile)
23. [Profile – Change Password](#23-profile--change-password)

---

---

## 1. Auth – Register

| Field              | Value                                            |
|--------------------|--------------------------------------------------|
| **Module Name**    | Authentication                                   |
| **Endpoint Name**  | Register User                                    |
| **HTTP Method**    | POST                                             |
| **URL**            | `https://anvnacare.alwaysdata.net/api/register.php` |
| **Host**           | `anvnacare.alwaysdata.net`                       |
| **Base URL**       | `https://anvnacare.alwaysdata.net`               |
| **URI**            | `/api/register.php`                              |
| **File Name**      | `api/register.php`                               |
| **Purpose**        | Register a new user account. Auto-logs in on success. |
| **Auth Required**  | No                                               |

### Headers
```
Content-Type: application/json
```

### Query Parameters
None

### Path Parameters
None

### Request Body Type
`application/json` OR `application/x-www-form-urlencoded`

### Request Body

| Field    | Type   | Required | Validation                         |
|----------|--------|----------|------------------------------------|
| name     | string | Yes      | Minimum 3 characters               |
| email    | string | Yes      | Must be a valid email address      |
| phone    | string | Yes      | Must be exactly 10 digits          |
| password | string | Yes      | Minimum 6 characters               |

### Sample Request (JSON)
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "phone": "9876543299",
  "password": "secret123"
}
```

### Sample cURL
```bash
curl -X POST https://anvnacare.alwaysdata.net/api/register.php \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john.doe@example.com","phone":"9876543299","password":"secret123"}'
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Registration successful.",
  "user": {
    "id": 16,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "role": "user"
  }
}
```

### Expected Error Responses

| Scenario               | HTTP Code | Response                                                      |
|------------------------|-----------|---------------------------------------------------------------|
| Name too short         | 400       | `{"success":false,"message":"Name must be at least 3 characters."}` |
| Invalid email          | 400       | `{"success":false,"message":"A valid email address is required."}` |
| Invalid phone          | 400       | `{"success":false,"message":"Phone must be a valid 10-digit number."}` |
| Password too short     | 400       | `{"success":false,"message":"Password must be at least 6 characters."}` |
| Email already exists   | 409       | `{"success":false,"message":"Email address is already registered."}` |
| Database error         | 500       | `{"success":false,"message":"Server error: ..."}` |

### Database Tables Used
- `users` (INSERT, SELECT)

### Notes for API Testing
- After successful registration, a PHP session is started (PHPSESSID cookie set)
- The user role is always `'user'` on registration (cannot self-register as admin)
- The phone must be exactly 10 numeric digits (no spaces, dashes, or country codes)
- Try registering with the same email twice to test the 409 Conflict scenario

---

## 2. Auth – Login

| Field              | Value                                              |
|--------------------|----------------------------------------------------|
| **Module Name**    | Authentication                                     |
| **Endpoint Name**  | User Login                                         |
| **HTTP Method**    | POST                                               |
| **URL**            | `https://anvnacare.alwaysdata.net/api/login.php`   |
| **Host**           | `anvnacare.alwaysdata.net`                         |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                 |
| **URI**            | `/api/login.php`                                   |
| **File Name**      | `api/login.php`                                    |
| **Purpose**        | Authenticate a user and start a PHP session. Supports Remember Me cookie. |
| **Auth Required**  | No                                                 |

### Headers
```
Content-Type: application/json
```

### Request Body Type
`application/json` OR `application/x-www-form-urlencoded`

### Request Body

| Field    | Type    | Required | Description                            |
|----------|---------|----------|----------------------------------------|
| email    | string  | Yes      | Registered user's email address        |
| password | string  | Yes      | User's password (plaintext)            |
| remember | boolean | No       | If true, sets a 30-day remember cookie |

### Sample Request (JSON)
```json
{
  "email": "amit.kumar@anvnacare.com",
  "password": "password123",
  "remember": false
}
```

### Sample cURL
```bash
curl -X POST https://anvnacare.alwaysdata.net/api/login.php \
  -H "Content-Type: application/json" \
  -c cookies.txt \
  -d '{"email":"amit.kumar@anvnacare.com","password":"password123"}'
```

> `-c cookies.txt` saves the session cookie for subsequent requests.

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Login successful.",
  "user": {
    "id": 2,
    "name": "Amit Kumar",
    "email": "amit.kumar@anvnacare.com",
    "role": "user"
  }
}
```

### Expected Error Responses

| Scenario                  | HTTP Code | Response                                                          |
|---------------------------|-----------|-------------------------------------------------------------------|
| Empty email or password   | 400       | `{"success":false,"message":"Email and Password are required."}` |
| Wrong credentials         | 401       | `{"success":false,"message":"Invalid email or password. 4 attempt(s) remaining before lockout."}` |
| Too many failed attempts  | 429       | `{"success":false,"message":"Too many failed login attempts. Please wait X minute(s) and try again."}` |
| Database error            | 500       | `{"success":false,"message":"Server error: ..."}` |

### Rate Limiting
- Maximum **5 failed login attempts** per IP address within **10 minutes**
- After 5 failed attempts, the account is **locked for 10 minutes**
- Counter resets after a successful login

### Database Tables Used
- `users` (SELECT)
- `cart` (SELECT, INSERT, UPDATE) — for merging guest cart on login

### Notes for API Testing
- After a successful login, the server sets a `PHPSESSID` cookie — keep this cookie for all subsequent protected API calls
- Test rate limiting by sending 6 wrong password attempts in a row
- Guest cart items are automatically merged into the user's DB cart on login

---

## 3. Auth – Logout

| Field              | Value                                              |
|--------------------|----------------------------------------------------|
| **Module Name**    | Authentication                                     |
| **Endpoint Name**  | User Logout                                        |
| **HTTP Method**    | GET                                                |
| **URL**            | `https://anvnacare.alwaysdata.net/logout.php`      |
| **Host**           | `anvnacare.alwaysdata.net`                         |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                 |
| **URI**            | `/logout.php`                                      |
| **File Name**      | `logout.php`                                       |
| **Purpose**        | Destroy the user session and clear all cookies. Redirects to homepage. |
| **Auth Required**  | No (but only meaningful when logged in)            |

### Headers
```
Cookie: PHPSESSID=<your_session_id>
```

### Sample cURL
```bash
curl -X GET https://anvnacare.alwaysdata.net/logout.php \
  -b cookies.txt \
  -L
```

### Expected Response
- HTTP 302 Redirect to `index.php`
- Session destroyed; PHPSESSID cookie cleared
- `remember_user` cookie cleared

### Notes for API Testing
- This is a page redirect, not a JSON endpoint
- After calling logout, the session cookie becomes invalid
- Subsequent requests to protected endpoints will return HTTP 401

---

## 4. Medicines – List/Search

| Field              | Value                                                    |
|--------------------|----------------------------------------------------------|
| **Module Name**    | Medicines / Pharmacy                                     |
| **Endpoint Name**  | Get Medicines List                                       |
| **HTTP Method**    | GET                                                      |
| **URL**            | `https://anvnacare.alwaysdata.net/api/medicines.php`     |
| **Host**           | `anvnacare.alwaysdata.net`                               |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                       |
| **URI**            | `/api/medicines.php`                                     |
| **File Name**      | `api/medicines.php`                                      |
| **Purpose**        | Fetch paginated list of medicines with optional filters  |
| **Auth Required**  | No                                                       |

### Query Parameters

| Parameter    | Type   | Required | Default | Description                                        |
|--------------|--------|----------|---------|----------------------------------------------------|
| category     | string | No       | (all)   | Filter by category: `OTC`, `Prescription`, `Vitamins` |
| search       | string | No       | (none)  | Search by medicine name (partial match)            |
| sort         | string | No       | `id ASC`| Sort options: `price_asc`, `price_desc`, `rating`  |
| page         | int    | No       | 1       | Page number for pagination                         |
| limit        | int    | No       | 10      | Number of results per page                         |

### Sample Requests

```
GET /api/medicines.php
GET /api/medicines.php?category=OTC
GET /api/medicines.php?search=paracetamol
GET /api/medicines.php?sort=price_asc&page=1&limit=5
GET /api/medicines.php?category=Vitamins&sort=rating
```

### Sample cURL
```bash
curl -X GET "https://anvnacare.alwaysdata.net/api/medicines.php?category=OTC&sort=price_asc&page=1&limit=5"
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "count": 5,
  "total": 8,
  "page": 1,
  "limit": 5,
  "medicines": [
    {
      "id": 1,
      "name": "Paracetamol 650mg (Crocin)",
      "manufacturer": "GlaxoSmithKline",
      "mrp": "30.00",
      "discount_price": "24.00",
      "rating": "4.50",
      "stock": 120,
      "image": "assets/images/medicines/crocin.png",
      "category": "OTC",
      "description": "Effective painkiller and fever reducer...",
      "created_at": "2026-07-01 10:00:00"
    }
  ]
}
```

### Expected Error Response
```json
{"success": false, "message": "Database error: ..."}
```

### Database Tables Used
- `medicines` (SELECT with optional filters)

### Notes for API Testing
- No authentication is required — this is a public endpoint
- `count` = number of records returned in this page
- `total` = total matching records in the database (for pagination UI)
- Category values: `OTC`, `Prescription`, `Vitamins`
- Sort by `rating` sorts descending (highest rated first)

---

## 5. Products – List/Search

| Field              | Value                                                    |
|--------------------|----------------------------------------------------------|
| **Module Name**    | Health Store / Products                                  |
| **Endpoint Name**  | Get Products List                                        |
| **HTTP Method**    | GET                                                      |
| **URL**            | `https://anvnacare.alwaysdata.net/api/products.php`      |
| **Host**           | `anvnacare.alwaysdata.net`                               |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                       |
| **URI**            | `/api/products.php`                                      |
| **File Name**      | `api/products.php`                                       |
| **Purpose**        | Fetch paginated health store products with optional filters |
| **Auth Required**  | No                                                       |

### Query Parameters

| Parameter    | Type   | Required | Default | Description                                             |
|--------------|--------|----------|---------|---------------------------------------------------------|
| category     | string | No       | (all)   | Filter by category: `Supplements`, `Devices`, `Wellness` |
| search       | string | No       | (none)  | Search by product name (partial match)                  |
| sort         | string | No       | `id ASC`| Sort: `price_asc`, `price_desc`, `rating`               |
| page         | int    | No       | 1       | Page number                                             |
| limit        | int    | No       | 10      | Results per page                                        |

### Sample cURL
```bash
curl -X GET "https://anvnacare.alwaysdata.net/api/products.php?category=Devices&sort=rating&limit=5"
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "count": 5,
  "total": 8,
  "page": 1,
  "limit": 5,
  "products": [
    {
      "id": 3,
      "name": "Blood Pressure Monitor (Omron)",
      "mrp": "2800.00",
      "discount_price": "2249.00",
      "rating": "4.70",
      "stock": 60,
      "image": "assets/images/products/bp_monitor.png",
      "category": "Devices",
      "description": "Fully automatic upper-arm blood pressure monitor...",
      "created_at": "2026-07-01 10:00:00"
    }
  ]
}
```

### Database Tables Used
- `products` (SELECT with optional filters)

---

## 6. Doctors – List/Search

| Field              | Value                                                    |
|--------------------|----------------------------------------------------------|
| **Module Name**    | Doctors / Consultations                                  |
| **Endpoint Name**  | Get Doctors List                                         |
| **HTTP Method**    | GET                                                      |
| **URL**            | `https://anvnacare.alwaysdata.net/api/doctors.php`       |
| **Host**           | `anvnacare.alwaysdata.net`                               |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                       |
| **URI**            | `/api/doctors.php`                                       |
| **File Name**      | `api/doctors.php`                                        |
| **Purpose**        | Fetch paginated list of doctors with filtering and sorting |
| **Auth Required**  | No                                                       |

### Query Parameters

| Parameter      | Type   | Required | Default  | Description                                              |
|----------------|--------|----------|----------|----------------------------------------------------------|
| specialization | string | No       | (all)    | Filter by specialization (exact match), e.g. `Cardiologist` |
| search         | string | No       | (none)   | Search by name or specialization (partial match)         |
| sort           | string | No       | `id ASC` | Sort: `fee_asc`, `fee_desc`, `experience`                |
| page           | int    | No       | 1        | Page number                                              |
| limit          | int    | No       | 15       | Results per page                                         |

### Sample cURL
```bash
curl -X GET "https://anvnacare.alwaysdata.net/api/doctors.php?specialization=Cardiologist&sort=fee_asc"
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "count": 1,
  "total": 1,
  "page": 1,
  "limit": 15,
  "doctors": [
    {
      "id": 1,
      "name": "Dr. Arvind Sharma",
      "specialization": "Cardiologist",
      "experience": 15,
      "languages": "English, Hindi",
      "fee": "800.00",
      "availability": "{\"days\":[\"Mon\",\"Wed\",\"Fri\"],\"time\":\"09:00 AM - 01:00 PM\"}",
      "image": "assets/images/doctors/doc1.png",
      "bio": "Senior Consultant Cardiologist...",
      "created_at": "2026-07-01 10:00:00"
    }
  ]
}
```

### Database Tables Used
- `doctors` (SELECT with optional filters)

### Notes for API Testing
- Specializations available: `Cardiologist`, `Pediatrician`, `General Physician`, `Dermatologist`, `Orthopedician`, `Gynecologist`, `Neurologist`, `Endocrinologist`, `Oncologist`, `ENT Specialist`, `Ophthalmologist`, `Psychiatrist`, `Urologist`, `Gastroenterologist`, `Dentist`
- Sort by `experience` shows most experienced doctors first (DESC)

---

## 7. Search – Global Auto-suggest

| Field              | Value                                                    |
|--------------------|----------------------------------------------------------|
| **Module Name**    | Search                                                   |
| **Endpoint Name**  | Global Auto-Suggestion Search                            |
| **HTTP Method**    | GET                                                      |
| **URL**            | `https://anvnacare.alwaysdata.net/api/search.php`        |
| **Host**           | `anvnacare.alwaysdata.net`                               |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                       |
| **URI**            | `/api/search.php`                                        |
| **File Name**      | `api/search.php`                                         |
| **Purpose**        | Search across medicines, products, doctors, and lab tests simultaneously |
| **Auth Required**  | No                                                       |

### Query Parameters

| Parameter | Type   | Required | Description                                        |
|-----------|--------|----------|----------------------------------------------------|
| q         | string | Yes      | Search query (minimum 2 characters)                |

### Sample cURL
```bash
curl -X GET "https://anvnacare.alwaysdata.net/api/search.php?q=para"
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "results": [
    {
      "type": "medicine",
      "id": 1,
      "name": "Paracetamol 650mg (Crocin)",
      "url": "medicine-details.php?id=1"
    },
    {
      "type": "doctor",
      "id": 3,
      "name": "Dr. Rajesh Patel (General Physician)",
      "url": "doctor-details.php?id=3"
    }
  ]
}
```

### Expected Error Response (query too short)
```json
{
  "success": false,
  "message": "Query too short",
  "results": []
}
```

### Database Tables Used
- `medicines` (SELECT, limit 4)
- `products` (SELECT, limit 4)
- `doctors` (SELECT, limit 4)
- `tests` (SELECT, limit 4)

### Notes for API Testing
- Returns up to **4 results per category** (max 16 results total)
- Query must be at least **2 characters** long
- Results include a `url` field for navigation

---

## 8. Cart – Add Item

| Field              | Value                                              |
|--------------------|----------------------------------------------------|
| **Module Name**    | Cart                                               |
| **Endpoint Name**  | Add Item to Cart                                   |
| **HTTP Method**    | POST                                               |
| **URL**            | `https://anvnacare.alwaysdata.net/api/cart.php`    |
| **Host**           | `anvnacare.alwaysdata.net`                         |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                 |
| **URI**            | `/api/cart.php`                                    |
| **File Name**      | `api/cart.php`                                     |
| **Purpose**        | Add a medicine, product, or lab test to the cart   |
| **Auth Required**  | No (supports guest cart via session)               |

### Headers
```
Content-Type: application/json
Cookie: PHPSESSID=<your_session_id>
X-CSRF-Token: <your_csrf_token>
```

### Request Body Type
`application/json`

### Request Body

| Field     | Type   | Required | Description                                           |
|-----------|--------|----------|-------------------------------------------------------|
| action    | string | Yes      | Must be `"add"`                                       |
| item_id   | int    | Yes      | ID of the item (medicine, product, or test)           |
| item_type | string | Yes      | One of: `"medicine"`, `"product"`, `"test"`           |
| quantity  | int    | No       | Quantity to add (default: 1)                          |
| csrf_token| string | Yes*     | CSRF token from session                               |

### Sample Request (JSON)
```json
{
  "action": "add",
  "item_id": 1,
  "item_type": "medicine",
  "quantity": 2,
  "csrf_token": "abc123..."
}
```

### Sample cURL
```bash
curl -X POST https://anvnacare.alwaysdata.net/api/cart.php \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: <token>" \
  -b cookies.txt \
  -d '{"action":"add","item_id":1,"item_type":"medicine","quantity":2,"csrf_token":"<token>"}'
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Paracetamol 650mg (Crocin) added to cart.",
  "cart_count": 3
}
```

### Expected Error Responses

| Scenario              | HTTP Code | Response                                               |
|-----------------------|-----------|--------------------------------------------------------|
| Missing params        | 400       | `{"success":false,"message":"Invalid parameters."}` |
| Item not found        | 404       | `{"success":false,"message":"Item not found."}` |
| Out of stock          | 400       | `{"success":false,"message":"Requested quantity is out of stock. Only X left."}` |
| Invalid CSRF          | 403       | `{"success":false,"message":"Invalid or missing CSRF token..."}` |

### Database Tables Used
- `cart` (SELECT, INSERT, UPDATE)
- `medicines` / `products` / `tests` (SELECT for stock check)

### Notes for API Testing
- Works for both **logged-in users** (DB cart) and **guests** (session cart)
- Lab tests (`item_type: "test"`) have unlimited stock (no stock check)
- `cart_count` in the response is the new total item count in the cart

---

## 9. Cart – Update Item Quantity

| Field              | Value                                              |
|--------------------|----------------------------------------------------|
| **Module Name**    | Cart                                               |
| **Endpoint Name**  | Update Cart Item Quantity                          |
| **HTTP Method**    | POST (with `action: "update"`)                     |
| **URL**            | `https://anvnacare.alwaysdata.net/api/cart.php`    |
| **URI**            | `/api/cart.php`                                    |
| **File Name**      | `api/cart.php`                                     |
| **Purpose**        | Set the quantity of an existing cart item          |
| **Auth Required**  | No (supports guest cart)                           |

### Request Body

```json
{
  "action": "update",
  "item_id": 1,
  "item_type": "medicine",
  "quantity": 3,
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Cart updated.",
  "cart_count": 5
}
```

### Expected Error Responses

| Scenario              | HTTP Code | Response                                                |
|-----------------------|-----------|----------------------------------------------------------|
| Missing params        | 400       | `{"success":false,"message":"Invalid parameters."}`    |
| Quantity exceeds stock| N/A (200) | `{"success":false,"message":"Quantity exceeds available stock. Max: X"}` |

---

## 10. Cart – Remove Item

| Field              | Value                                              |
|--------------------|----------------------------------------------------|
| **Module Name**    | Cart                                               |
| **Endpoint Name**  | Remove Item from Cart                              |
| **HTTP Method**    | DELETE (or POST with `action: "remove"`)           |
| **URL**            | `https://anvnacare.alwaysdata.net/api/cart.php`    |
| **URI**            | `/api/cart.php`                                    |
| **File Name**      | `api/cart.php`                                     |
| **Purpose**        | Remove a specific item from the cart               |
| **Auth Required**  | No (supports guest cart)                           |

### Headers
```
Content-Type: application/json
X-CSRF-Token: <your_csrf_token>
Cookie: PHPSESSID=<session_id>
```

### Request Body (JSON for DELETE method)
```json
{
  "item_id": 1,
  "item_type": "medicine",
  "csrf_token": "<token>"
}
```

### Sample cURL (DELETE method)
```bash
curl -X DELETE https://anvnacare.alwaysdata.net/api/cart.php \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: <token>" \
  -b cookies.txt \
  -d '{"item_id":1,"item_type":"medicine","csrf_token":"<token>"}'
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Item removed from cart.",
  "cart_count": 2
}
```

### Expected Error Response

| Scenario        | HTTP Code | Response                                              |
|-----------------|-----------|-------------------------------------------------------|
| Missing params  | 400       | `{"success":false,"message":"Missing item type or item ID."}` |

---

## 11. Wishlist – Add Item

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | Wishlist                                            |
| **Endpoint Name**  | Add Item to Wishlist                                |
| **HTTP Method**    | POST                                                |
| **URL**            | `https://anvnacare.alwaysdata.net/api/wishlist.php` |
| **Host**           | `anvnacare.alwaysdata.net`                          |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                  |
| **URI**            | `/api/wishlist.php`                                 |
| **File Name**      | `api/wishlist.php`                                  |
| **Purpose**        | Add a medicine or product to the user's wishlist    |
| **Auth Required**  | **Yes** (login required)                            |

### Headers
```
Content-Type: application/json
Cookie: PHPSESSID=<session_id>
X-CSRF-Token: <csrf_token>
```

### Request Body

| Field     | Type   | Required | Description                              |
|-----------|--------|----------|------------------------------------------|
| action    | string | Yes      | Must be `"add"`                          |
| item_id   | int    | Yes      | ID of the medicine or product            |
| item_type | string | Yes      | One of: `"medicine"`, `"product"`        |
| csrf_token| string | Yes      | CSRF token                               |

### Sample Request
```json
{
  "action": "add",
  "item_id": 3,
  "item_type": "medicine",
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Amoxicillin 500mg (Novamox) added to wishlist."
}
```

### Expected Error Responses

| Scenario         | HTTP Code | Response                                               |
|------------------|-----------|--------------------------------------------------------|
| Not logged in    | 401       | `{"success":false,"message":"Please login to manage your wishlist.","redirect":"login.php"}` |
| Invalid params   | 400       | `{"success":false,"message":"Invalid parameters."}` |
| Item not found   | 404       | `{"success":false,"message":"Item not found."}` |

### Database Tables Used
- `wishlist` (INSERT IGNORE)
- `medicines` / `products` (SELECT for verification)

### Notes for API Testing
- Duplicate adds are silently ignored (`INSERT IGNORE`) — no error on re-adding
- Only `medicine` and `product` types are supported (NOT `test`)

---

## 12. Wishlist – Remove Item

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | Wishlist                                            |
| **Endpoint Name**  | Remove Item from Wishlist                           |
| **HTTP Method**    | POST                                                |
| **URL**            | `https://anvnacare.alwaysdata.net/api/wishlist.php` |
| **URI**            | `/api/wishlist.php`                                 |
| **File Name**      | `api/wishlist.php`                                  |
| **Purpose**        | Remove a medicine or product from the user's wishlist |
| **Auth Required**  | **Yes**                                             |

### Request Body
```json
{
  "action": "remove",
  "item_id": 3,
  "item_type": "medicine",
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Item removed from wishlist."
}
```

---

## 13. Coupon – Validate Coupon

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | Coupon                                              |
| **Endpoint Name**  | Validate Coupon Code                                |
| **HTTP Method**    | GET                                                 |
| **URL**            | `https://anvnacare.alwaysdata.net/api/coupon.php`   |
| **Host**           | `anvnacare.alwaysdata.net`                          |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                  |
| **URI**            | `/api/coupon.php`                                   |
| **File Name**      | `api/coupon.php`                                    |
| **Purpose**        | Check if a coupon code is valid and get its discount details |
| **Auth Required**  | No                                                  |

### Query Parameters

| Parameter  | Type   | Required | Description                                  |
|------------|--------|----------|----------------------------------------------|
| code       | string | Yes      | The coupon code string (e.g., `SAVE10`)      |
| cart_value | float  | No       | Current cart total to check minimum cart value |

### Sample cURL
```bash
curl -X GET "https://anvnacare.alwaysdata.net/api/coupon.php?code=SAVE10&cart_value=500"
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Coupon code applied successfully.",
  "coupon": {
    "code": "SAVE10",
    "discount_type": "percentage",
    "discount_value": 10.0
  }
}
```

### Expected Error Responses

| Scenario                  | HTTP Code | Response                                                    |
|---------------------------|-----------|-------------------------------------------------------------|
| No code provided          | 400       | `{"success":false,"message":"Coupon code is required."}` |
| Invalid/expired coupon    | 200       | `{"success":false,"message":"Invalid or expired coupon code."}` |
| Minimum cart value not met| 200       | `{"success":false,"message":"Minimum cart value required to apply this coupon is ₹100.00"}` |

### Available Test Coupons

| Code     | Type       | Discount | Min Cart Value | Expiry     |
|----------|------------|----------|----------------|------------|
| SAVE10   | percentage | 10%      | ₹100           | 2030-12-31 |
| WELCOME  | fixed      | ₹100     | ₹500           | 2030-12-31 |
| HEALTH20 | percentage | 20%      | ₹1000          | 2030-12-31 |
| DIAGNO50 | percentage | 50%      | ₹500           | 2030-12-31 |
| FLAT200  | fixed      | ₹200     | ₹1500          | 2030-12-31 |

### Database Tables Used
- `coupons` (SELECT)

---

## 14. Address – Get All Addresses

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | Address Management                                  |
| **Endpoint Name**  | Get User Addresses                                  |
| **HTTP Method**    | GET                                                 |
| **URL**            | `https://anvnacare.alwaysdata.net/api/address.php`  |
| **Host**           | `anvnacare.alwaysdata.net`                          |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                  |
| **URI**            | `/api/address.php`                                  |
| **File Name**      | `api/address.php`                                   |
| **Purpose**        | Retrieve all saved delivery addresses for the logged-in user |
| **Auth Required**  | **Yes**                                             |

### Headers
```
Cookie: PHPSESSID=<session_id>
```

### Sample cURL
```bash
curl -X GET https://anvnacare.alwaysdata.net/api/address.php \
  -b cookies.txt
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "addresses": [
    {
      "id": 1,
      "user_id": 2,
      "name": "Amit Kumar",
      "phone": "9876543211",
      "address_line1": "Flat No. 402, Green Glen Layout",
      "address_line2": "Bellandur",
      "city": "Bengaluru",
      "state": "Karnataka",
      "pincode": "560103",
      "is_default": 1,
      "created_at": "2026-07-01 10:00:00"
    }
  ]
}
```

### Expected Error Response
| Scenario       | HTTP Code | Response                                                        |
|----------------|-----------|-----------------------------------------------------------------|
| Not logged in  | 401       | `{"success":false,"message":"Please login to manage addresses."}` |

### Database Tables Used
- `addresses` (SELECT)

---

## 15. Address – Add New Address

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | Address Management                                  |
| **Endpoint Name**  | Add New Delivery Address                            |
| **HTTP Method**    | POST                                                |
| **URL**            | `https://anvnacare.alwaysdata.net/api/address.php`  |
| **URI**            | `/api/address.php`                                  |
| **File Name**      | `api/address.php`                                   |
| **Purpose**        | Save a new delivery address for the logged-in user  |
| **Auth Required**  | **Yes**                                             |

### Request Body

| Field          | Type    | Required | Validation                         |
|----------------|---------|----------|------------------------------------|
| name           | string  | Yes      | Full name of recipient             |
| phone          | string  | Yes      | Exactly 10 digits                  |
| address_line1  | string  | Yes      | Street / flat number               |
| address_line2  | string  | No       | Landmark / area (optional)         |
| city           | string  | Yes      | City name                          |
| state          | string  | Yes      | State name                         |
| pincode        | string  | Yes      | Exactly 6 digits                   |
| is_default     | bool    | No       | `1` to set as default address      |
| csrf_token     | string  | Yes      | CSRF token                         |

### Sample Request (JSON)
```json
{
  "name": "John Doe",
  "phone": "9876543200",
  "address_line1": "Flat 101, Sunrise Towers",
  "address_line2": "Near City Mall",
  "city": "Pune",
  "state": "Maharashtra",
  "pincode": "411001",
  "is_default": 1,
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Address saved successfully.",
  "address": {
    "id": 15,
    "name": "John Doe",
    "phone": "9876543200",
    "address_line1": "Flat 101, Sunrise Towers",
    "address_line2": "Near City Mall",
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411001",
    "is_default": 1
  }
}
```

### Expected Error Responses

| Scenario              | HTTP Code | Response                                                            |
|-----------------------|-----------|---------------------------------------------------------------------|
| Missing required field| 400       | `{"success":false,"message":"All address fields (except Line 2) are required."}` |
| Invalid phone         | 400       | `{"success":false,"message":"Phone must be a 10-digit number."}` |
| Invalid pincode       | 400       | `{"success":false,"message":"Pincode must be a 6-digit number."}` |

### Database Tables Used
- `addresses` (SELECT, INSERT, UPDATE)

---

## 16. Address – Delete Address

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | Address Management                                  |
| **Endpoint Name**  | Delete Saved Address                                |
| **HTTP Method**    | DELETE (or POST with `_method: "DELETE"`)           |
| **URL**            | `https://anvnacare.alwaysdata.net/api/address.php`  |
| **URI**            | `/api/address.php`                                  |
| **File Name**      | `api/address.php`                                   |
| **Purpose**        | Delete a specific saved address                     |
| **Auth Required**  | **Yes**                                             |

### Request Body
```json
{
  "address_id": 1,
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Address deleted successfully."
}
```

### Expected Error Responses

| Scenario           | HTTP Code | Response                                              |
|--------------------|-----------|-------------------------------------------------------|
| Invalid address ID | 400       | `{"success":false,"message":"Invalid address ID."}` |
| Address not owned  | 404       | `{"success":false,"message":"Address not found."}` |

### Database Tables Used
- `addresses` (SELECT, DELETE)

---

## 17. Address – Set Default Address

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | Address Management                                  |
| **Endpoint Name**  | Set Default Address                                 |
| **HTTP Method**    | POST                                                |
| **URL**            | `https://anvnacare.alwaysdata.net/api/address.php`  |
| **URI**            | `/api/address.php`                                  |
| **File Name**      | `api/address.php`                                   |
| **Purpose**        | Mark an existing address as the default             |
| **Auth Required**  | **Yes**                                             |

### Request Body
```json
{
  "action": "set_default",
  "address_id": 2,
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Default address updated."
}
```

### Database Tables Used
- `addresses` (SELECT, UPDATE)

---

## 18. Order – Place Order

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | Orders                                              |
| **Endpoint Name**  | Place Order                                         |
| **HTTP Method**    | POST                                                |
| **URL**            | `https://anvnacare.alwaysdata.net/api/order.php`    |
| **Host**           | `anvnacare.alwaysdata.net`                          |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                  |
| **URI**            | `/api/order.php`                                    |
| **File Name**      | `api/order.php`                                     |
| **Purpose**        | Place a new order from the logged-in user's cart    |
| **Auth Required**  | **Yes**                                             |

### Headers
```
Content-Type: application/json
Cookie: PHPSESSID=<session_id>
X-CSRF-Token: <csrf_token>
```

### Request Body

| Field           | Type   | Required | Description                                    |
|-----------------|--------|----------|------------------------------------------------|
| address_id      | int    | Yes      | ID of the saved delivery address               |
| payment_method  | string | No       | `"Card"` (default) or `"COD"`                  |
| delivery_method | string | No       | `"Standard"` (default) or `"Express"`          |
| coupon_code     | string | No       | Coupon code to apply                           |
| csrf_token      | string | Yes      | CSRF token                                     |

### Sample Request (JSON)
```json
{
  "address_id": 1,
  "payment_method": "Card",
  "delivery_method": "Standard",
  "coupon_code": "SAVE10",
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Order placed successfully!",
  "order_id": 21,
  "order_number": "ORD-20260721-4582",
  "net_amount": 216.0
}
```

### Expected Error Responses

| Scenario               | HTTP Code | Response                                                        |
|------------------------|-----------|-----------------------------------------------------------------|
| Not logged in          | 401       | `{"success":false,"message":"Please login to complete your order."}` |
| No address selected    | 400       | `{"success":false,"message":"Please select a delivery address."}` |
| Cart is empty          | 400       | `{"success":false,"message":"Your cart is empty."}` |
| Invalid address        | 400       | `{"success":false,"message":"Invalid delivery address."}` |
| Item out of stock      | 500       | `{"success":false,"message":"Item 'X' is out of stock. Max: Y"}` |

### Database Tables Used
- `cart` (SELECT, DELETE)
- `addresses` (SELECT)
- `orders` (INSERT)
- `order_items` (INSERT)
- `medicines` / `products` / `tests` (SELECT, UPDATE for stock)
- `coupons` (SELECT, if coupon_code provided)

### Notes for API Testing
- The entire order placement is wrapped in a **database transaction** (BEGIN/COMMIT/ROLLBACK)
- Payment method `"Card"` sets `payment_status = "Paid"` automatically
- Payment method `"COD"` sets `payment_status = "Pending"`
- Order number format: `ORD-YYYYMMDD-XXXX` (e.g., `ORD-20260721-4582`)
- Cart is **cleared** after successful order placement

---

## 19. Appointments – Book Appointment

| Field              | Value                                                      |
|--------------------|------------------------------------------------------------|
| **Module Name**    | Appointments                                               |
| **Endpoint Name**  | Book Doctor Appointment                                    |
| **HTTP Method**    | POST                                                       |
| **URL**            | `https://anvnacare.alwaysdata.net/api/appointments.php`    |
| **Host**           | `anvnacare.alwaysdata.net`                                 |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                         |
| **URI**            | `/api/appointments.php`                                    |
| **File Name**      | `api/appointments.php`                                     |
| **Purpose**        | Book a new appointment with a doctor                       |
| **Auth Required**  | **Yes**                                                    |

### Headers
```
Content-Type: application/json
Cookie: PHPSESSID=<session_id>
X-CSRF-Token: <csrf_token>
```

### Request Body

| Field         | Type   | Required | Description                                          |
|---------------|--------|----------|------------------------------------------------------|
| action        | string | Yes      | Must be `"book"`                                     |
| doctor_id     | int    | Yes      | ID of the doctor                                     |
| date          | string | Yes      | Appointment date in `YYYY-MM-DD` format              |
| time          | string | Yes      | Time slot (e.g., `"10:00 AM"`)                       |
| csrf_token    | string | Yes      | CSRF token                                           |

### Sample Request (JSON)
```json
{
  "action": "book",
  "doctor_id": 1,
  "date": "2026-08-15",
  "time": "10:00 AM",
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Appointment with Dr. Arvind Sharma booked successfully for 2026-08-15 at 10:00 AM.",
  "appointment_id": 21
}
```

### Expected Error Responses

| Scenario                    | HTTP Code | Response                                                   |
|-----------------------------|-----------|-------------------------------------------------------------|
| Not logged in               | 401       | `{"success":false,"message":"Please login to manage appointments."}` |
| Missing required fields     | 400       | `{"success":false,"message":"Doctor, appointment date, and time slot are required."}` |
| Doctor not found            | 404       | `{"success":false,"message":"Selected doctor not found."}` |
| Invalid date format         | 400       | `{"success":false,"message":"Invalid date format. Expected YYYY-MM-DD."}` |
| Duplicate appointment       | 409       | `{"success":false,"message":"You already have an upcoming appointment with this doctor on ... at ...."}` |

### Database Tables Used
- `appointments` (SELECT for duplicate check, INSERT)
- `doctors` (SELECT for doctor verification)

### Notes for API Testing
- Duplicate booking prevention: same user + same doctor + same date + same time = 409 Conflict
- Date must be in `YYYY-MM-DD` format exactly
- Time slot format is free text (e.g., `"10:00 AM"`, `"2:30 PM"`) — no strict validation

---

## 20. Appointments – Cancel Appointment

| Field              | Value                                                      |
|--------------------|------------------------------------------------------------|
| **Module Name**    | Appointments                                               |
| **Endpoint Name**  | Cancel Appointment                                         |
| **HTTP Method**    | POST                                                       |
| **URL**            | `https://anvnacare.alwaysdata.net/api/appointments.php`    |
| **URI**            | `/api/appointments.php`                                    |
| **File Name**      | `api/appointments.php`                                     |
| **Purpose**        | Cancel an upcoming appointment                             |
| **Auth Required**  | **Yes**                                                    |

### Request Body

| Field          | Type   | Required | Description                   |
|----------------|--------|----------|-------------------------------|
| action         | string | Yes      | Must be `"cancel"`            |
| appointment_id | int    | Yes      | ID of the appointment         |
| csrf_token     | string | Yes      | CSRF token                    |

### Sample Request (JSON)
```json
{
  "action": "cancel",
  "appointment_id": 1,
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Appointment cancelled successfully."
}
```

### Expected Error Responses

| Scenario                   | HTTP Code | Response                                                         |
|----------------------------|-----------|------------------------------------------------------------------|
| Invalid appointment ID     | 400       | `{"success":false,"message":"Invalid appointment ID."}` |
| Appointment not found      | 404       | `{"success":false,"message":"Appointment not found."}` |
| Not upcoming status        | 400       | `{"success":false,"message":"Only upcoming appointments can be cancelled."}` |

### Database Tables Used
- `appointments` (SELECT for ownership/status check, UPDATE)

### Notes for API Testing
- Only appointments with `status = 'Upcoming'` can be cancelled
- Users can only cancel their own appointments (ownership check)

---

## 21. Profile – Get Profile

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | User Profile                                        |
| **Endpoint Name**  | Get User Profile                                    |
| **HTTP Method**    | GET                                                 |
| **URL**            | `https://anvnacare.alwaysdata.net/api/profile.php`  |
| **Host**           | `anvnacare.alwaysdata.net`                          |
| **Base URL**       | `https://anvnacare.alwaysdata.net`                  |
| **URI**            | `/api/profile.php`                                  |
| **File Name**      | `api/profile.php`                                   |
| **Purpose**        | Retrieve the profile details and addresses of the logged-in user |
| **Auth Required**  | **Yes**                                             |

### Headers
```
Cookie: PHPSESSID=<session_id>
```

### Sample cURL
```bash
curl -X GET https://anvnacare.alwaysdata.net/api/profile.php \
  -b cookies.txt
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "user": {
    "id": 2,
    "name": "Amit Kumar",
    "email": "amit.kumar@anvnacare.com",
    "phone": "9876543211",
    "role": "user",
    "created_at": "2026-07-01 10:00:00"
  },
  "addresses": [
    {
      "id": 1,
      "user_id": 2,
      "name": "Amit Kumar",
      "phone": "9876543211",
      "address_line1": "Flat No. 402, Green Glen Layout",
      "address_line2": "Bellandur",
      "city": "Bengaluru",
      "state": "Karnataka",
      "pincode": "560103",
      "is_default": 1
    }
  ]
}
```

### Database Tables Used
- `users` (SELECT)
- `addresses` (SELECT)

---

## 22. Profile – Update Profile

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | User Profile                                        |
| **Endpoint Name**  | Update User Profile (Name & Phone)                  |
| **HTTP Method**    | POST                                                |
| **URL**            | `https://anvnacare.alwaysdata.net/api/profile.php`  |
| **URI**            | `/api/profile.php`                                  |
| **File Name**      | `api/profile.php`                                   |
| **Purpose**        | Update the logged-in user's name and phone number   |
| **Auth Required**  | **Yes**                                             |

### Request Body

| Field      | Type   | Required | Validation                  |
|------------|--------|----------|-----------------------------|
| action     | string | Yes      | Must be `"update_profile"`  |
| name       | string | Yes      | Minimum 3 characters        |
| phone      | string | Yes      | Exactly 10 digits           |
| csrf_token | string | Yes      | CSRF token                  |

### Sample Request (JSON)
```json
{
  "action": "update_profile",
  "name": "Amit Kumar Updated",
  "phone": "9876543299",
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Profile updated successfully."
}
```

### Database Tables Used
- `users` (UPDATE)

---

## 23. Profile – Change Password

| Field              | Value                                               |
|--------------------|-----------------------------------------------------|
| **Module Name**    | User Profile                                        |
| **Endpoint Name**  | Change Password                                     |
| **HTTP Method**    | POST                                                |
| **URL**            | `https://anvnacare.alwaysdata.net/api/profile.php`  |
| **URI**            | `/api/profile.php`                                  |
| **File Name**      | `api/profile.php`                                   |
| **Purpose**        | Change the logged-in user's password                |
| **Auth Required**  | **Yes**                                             |

### Request Body

| Field            | Type   | Required | Validation                    |
|------------------|--------|----------|-------------------------------|
| action           | string | Yes      | Must be `"change_password"`   |
| current_password | string | Yes      | The user's current password   |
| new_password     | string | Yes      | Minimum 6 characters          |
| csrf_token       | string | Yes      | CSRF token                    |

### Sample Request (JSON)
```json
{
  "action": "change_password",
  "current_password": "password123",
  "new_password": "newpassword456",
  "csrf_token": "<token>"
}
```

### Expected Success Response (HTTP 200)
```json
{
  "success": true,
  "message": "Password changed successfully."
}
```

### Expected Error Responses

| Scenario                     | HTTP Code | Response                                                        |
|------------------------------|-----------|-----------------------------------------------------------------|
| Missing passwords            | 400       | `{"success":false,"message":"Current password and New password are required."}` |
| New password too short       | 400       | `{"success":false,"message":"New password must be at least 6 characters."}` |
| Current password incorrect   | 400       | `{"success":false,"message":"Incorrect current password."}` |

### Database Tables Used
- `users` (SELECT for hash, UPDATE)

---

*This documentation was generated using static analysis only. No existing project files were modified.*
