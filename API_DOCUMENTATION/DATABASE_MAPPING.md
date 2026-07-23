# ANVNA Care – Database Mapping

This document shows which API endpoints use which database tables.

---

## 📊 Database Tables Overview

| Table Name    | Description                                  | Rows (Seed Data) |
|---------------|----------------------------------------------|------------------|
| `users`       | Registered users (patients and admin)        | 15               |
| `doctors`     | Doctor profiles                              | 15               |
| `medicines`   | Medicine catalog                             | 20               |
| `products`    | Health store products                        | 20               |
| `tests`       | Lab test catalog                             | 20               |
| `cart`        | Shopping cart items (per user)               | Dynamic          |
| `wishlist`    | Wishlist items (per user)                    | Dynamic          |
| `addresses`   | Saved delivery addresses                     | 14 (seed)        |
| `orders`      | Placed orders                                | 20 (seed)        |
| `order_items` | Individual items within orders               | 29 (seed)        |
| `coupons`     | Discount coupon codes                        | 5                |
| `appointments`| Doctor appointments                          | 20 (seed)        |

---

## 🗺️ Endpoint → Table Mapping

### 1. Authentication Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Register User | `api/register.php` | POST | `users` | SELECT (email check), INSERT |
| Login | `api/login.php` | POST | `users`, `cart` | SELECT (user lookup), INSERT/UPDATE (cart merge) |
| Logout | `logout.php` | GET | None (session only) | — |

**Register**: Checks if email exists, then inserts new user row.  
**Login**: Fetches user by email, then (if guest cart exists) merges guest cart items into the `cart` table.

---

### 2. Medicines Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Get Medicines List | `api/medicines.php` | GET | `medicines` | SELECT with dynamic WHERE/ORDER BY/LIMIT |

**Query Example**:
```sql
SELECT * FROM medicines WHERE 1=1 [AND category = ?] [AND name LIKE ?]
ORDER BY discount_price ASC
LIMIT 10 OFFSET 0;
```

---

### 3. Products Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Get Products List | `api/products.php` | GET | `products` | SELECT with dynamic WHERE/ORDER BY/LIMIT |

**Query Example**:
```sql
SELECT * FROM products WHERE 1=1 [AND category = ?] [AND name LIKE ?]
ORDER BY rating DESC
LIMIT 10 OFFSET 0;
```

---

### 4. Doctors Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Get Doctors List | `api/doctors.php` | GET | `doctors` | SELECT with dynamic WHERE/ORDER BY/LIMIT |

**Query Example**:
```sql
SELECT * FROM doctors WHERE 1=1 [AND specialization = ?] [AND (name LIKE ? OR specialization LIKE ?)]
ORDER BY fee ASC
LIMIT 15 OFFSET 0;
```

---

### 5. Search Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Global Search | `api/search.php` | GET | `medicines`, `products`, `doctors`, `tests` | SELECT (4 parallel queries, max 4 results each) |

**Queries**:
```sql
SELECT id, name FROM medicines WHERE name LIKE ? LIMIT 4;
SELECT id, name FROM products WHERE name LIKE ? LIMIT 4;
SELECT id, name, specialization FROM doctors WHERE name LIKE ? OR specialization LIKE ? LIMIT 4;
SELECT id, name FROM tests WHERE name LIKE ? LIMIT 4;
```

---

### 6. Coupon Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Validate Coupon | `api/coupon.php` | GET | `coupons` | SELECT (lookup by code, check expiry) |

**Query**:
```sql
SELECT * FROM coupons WHERE code = ? AND expiry_date >= CURRENT_DATE();
```

---

### 7. Cart Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Add to Cart | `api/cart.php` | POST | `cart`, `medicines`/`products`/`tests` | SELECT (stock check), SELECT (existing cart entry), INSERT or UPDATE |
| Update Cart Item | `api/cart.php` | POST | `cart`, `medicines`/`products`/`tests` | SELECT (stock check), UPDATE |
| Remove from Cart | `api/cart.php` | DELETE | `cart` | DELETE |

**Key Queries**:
```sql
-- Check item exists and get stock
SELECT name, stock FROM medicines WHERE id = ?;     -- For medicine
SELECT name, stock FROM products WHERE id = ?;      -- For product
SELECT name, 999 as stock FROM tests WHERE id = ?;  -- For test (unlimited stock)

-- Check if item already in cart
SELECT id, quantity FROM cart WHERE user_id = ? AND item_type = ? AND item_id = ?;

-- Add new cart entry
INSERT INTO cart (user_id, item_type, item_id, quantity) VALUES (?, ?, ?, ?);

-- Update existing cart entry
UPDATE cart SET quantity = ? WHERE id = ?;

-- Remove from cart
DELETE FROM cart WHERE user_id = ? AND item_type = ? AND item_id = ?;
```

---

### 8. Wishlist Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Add to Wishlist | `api/wishlist.php` | POST | `wishlist`, `medicines`/`products` | SELECT (verify item), INSERT IGNORE |
| Remove from Wishlist | `api/wishlist.php` | POST | `wishlist` | DELETE |

**Key Queries**:
```sql
-- Verify item exists
SELECT name FROM medicines WHERE id = ?;    -- or products

-- Add to wishlist (silently ignores duplicates)
INSERT IGNORE INTO wishlist (user_id, item_type, item_id) VALUES (?, ?, ?);

-- Remove from wishlist
DELETE FROM wishlist WHERE user_id = ? AND item_type = ? AND item_id = ?;
```

---

### 9. Address Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Get All Addresses | `api/address.php` | GET | `addresses` | SELECT (all for user, ordered by is_default) |
| Add New Address | `api/address.php` | POST | `addresses` | UPDATE (clear old default), INSERT |
| Delete Address | `api/address.php` | DELETE | `addresses` | SELECT (ownership check), DELETE |
| Set Default Address | `api/address.php` | POST | `addresses` | SELECT (ownership), UPDATE (all to 0), UPDATE (one to 1) |

**Key Queries**:
```sql
-- Get all user's addresses
SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC;

-- Clear existing default before setting new one
UPDATE addresses SET is_default = 0 WHERE user_id = ?;

-- Insert new address
INSERT INTO addresses (user_id, name, phone, address_line1, address_line2, city, state, pincode, is_default)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);

-- Verify ownership
SELECT id FROM addresses WHERE id = ? AND user_id = ?;

-- Delete address
DELETE FROM addresses WHERE id = ? AND user_id = ?;

-- Set as default
UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?;
```

---

### 10. Order Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Place Order | `api/order.php` | POST | `cart`, `addresses`, `orders`, `order_items`, `medicines`, `products`, `tests`, `coupons` | Multiple — all wrapped in a transaction |

**Transaction Steps**:
```sql
BEGIN;

-- 1. Fetch all cart items
SELECT * FROM cart WHERE user_id = ?;

-- 2. Verify address
SELECT id FROM addresses WHERE id = ? AND user_id = ?;

-- 3. For each cart item — fetch price and stock
SELECT id, name, discount_price, stock FROM medicines WHERE id = ?;
SELECT id, name, discount_price, stock FROM products WHERE id = ?;
SELECT id, name, discount_price FROM tests WHERE id = ?;

-- 4. Check coupon (optional)
SELECT * FROM coupons WHERE code = ? AND expiry_date >= CURRENT_DATE();

-- 5. Insert order
INSERT INTO orders (user_id, order_number, total_amount, discount_amount, net_amount,
                    address_id, payment_method, payment_status, order_status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);

-- 6. For each item — insert order_item and reduce stock
INSERT INTO order_items (order_id, item_type, item_id, price, quantity) VALUES (?, ?, ?, ?, ?);
UPDATE medicines SET stock = stock - ? WHERE id = ?;  -- or products
UPDATE products SET stock = stock - ? WHERE id = ?;

-- 7. Clear user's cart
DELETE FROM cart WHERE user_id = ?;

COMMIT;  -- or ROLLBACK on any error
```

---

### 11. Appointments Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Book Appointment | `api/appointments.php` | POST | `appointments`, `doctors` | SELECT (doctor exists), SELECT (duplicate check), INSERT |
| Cancel Appointment | `api/appointments.php` | POST | `appointments` | SELECT (ownership + status), UPDATE |

**Key Queries**:
```sql
-- Verify doctor exists
SELECT name FROM doctors WHERE id = ?;

-- Check for duplicate booking (same user, doctor, date, time)
SELECT id FROM appointments
WHERE user_id = ? AND doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status = 'Upcoming';

-- Book appointment
INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status)
VALUES (?, ?, ?, ?, 'Upcoming');

-- Verify appointment ownership and status
SELECT id, status FROM appointments WHERE id = ? AND user_id = ?;

-- Cancel appointment
UPDATE appointments SET status = 'Cancelled' WHERE id = ?;
```

---

### 12. Profile Module

| Endpoint | File | Method | Tables Used | Operations |
|----------|------|--------|-------------|------------|
| Get Profile | `api/profile.php` | GET | `users`, `addresses` | SELECT user, SELECT all addresses |
| Update Profile | `api/profile.php` | POST | `users` | UPDATE name and phone |
| Change Password | `api/profile.php` | POST | `users` | SELECT (get hash), UPDATE (set new hash) |

**Key Queries**:
```sql
-- Get profile data
SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?;

-- Get addresses for profile page
SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC;

-- Update profile
UPDATE users SET name = ?, phone = ? WHERE id = ?;

-- Get current password hash for verification
SELECT password FROM users WHERE id = ?;

-- Update to new password hash
UPDATE users SET password = ? WHERE id = ?;
```

---

## 📌 Table Usage Frequency Summary

| Table | Used By (Endpoints) | Read | Write |
|-------|---------------------|------|-------|
| `users` | Register, Login, Profile (GET/Update/ChangePassword) | Yes | Yes |
| `medicines` | Medicines list, Cart (add/update), Order, Search | Yes | Yes (stock reduction) |
| `products` | Products list, Cart (add/update), Wishlist, Order, Search | Yes | Yes (stock reduction) |
| `doctors` | Doctors list, Appointments (book), Search | Yes | No |
| `tests` | Cart (add), Order, Search | Yes | No |
| `cart` | Cart (add/update/remove), Order (clear), Login (merge) | Yes | Yes |
| `wishlist` | Wishlist (add/remove) | Yes | Yes |
| `addresses` | Address (all), Order (verify), Profile (GET) | Yes | Yes |
| `orders` | Order (place) | No | Yes |
| `order_items` | Order (place) | No | Yes |
| `coupons` | Coupon (validate), Order (apply) | Yes | No |
| `appointments` | Appointments (book/cancel) | Yes | Yes |

---

## 🔑 Foreign Key Relationships

```
users (id)
├── cart.user_id          → Deletes cascade
├── wishlist.user_id      → Deletes cascade
├── addresses.user_id     → Deletes cascade
├── orders.user_id        → Deletes cascade
└── appointments.user_id  → Deletes cascade

doctors (id)
└── appointments.doctor_id → Deletes cascade

addresses (id)
└── orders.address_id      → Sets NULL on delete

orders (id)
└── order_items.order_id   → Deletes cascade
```

---

*This documentation was generated using static analysis only. No existing project files were modified.*
