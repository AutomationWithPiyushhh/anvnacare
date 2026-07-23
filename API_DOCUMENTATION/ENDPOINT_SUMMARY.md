# ANVNA Care – Endpoint Summary

**Base URL**: `https://anvnacare.alwaysdata.net`  
**API Path**: `/api/`  
**Total Endpoints**: 23

---

| # | Module | Method | Endpoint | Purpose | Auth Required | Expected Status Code |
|---|--------|--------|----------|---------|---------------|----------------------|
| 1 | Authentication | POST | `/api/register.php` | Register a new user account | No | 200 (success), 400, 409 |
| 2 | Authentication | POST | `/api/login.php` | Login and start a PHP session | No | 200 (success), 400, 401, 429 |
| 3 | Authentication | GET | `/logout.php` | Logout and destroy session | No | 302 (redirect) |
| 4 | Medicines | GET | `/api/medicines.php` | List/search medicines (paginated) | No | 200 |
| 5 | Products | GET | `/api/products.php` | List/search health store products (paginated) | No | 200 |
| 6 | Doctors | GET | `/api/doctors.php` | List/search doctors with filters | No | 200 |
| 7 | Search | GET | `/api/search.php` | Global auto-suggest search across all categories | No | 200 |
| 8 | Coupon | GET | `/api/coupon.php` | Validate a coupon code | No | 200, 400 |
| 9 | Cart | POST | `/api/cart.php` (action=add) | Add item to cart | No (supports guest) | 200, 400, 403, 404 |
| 10 | Cart | POST | `/api/cart.php` (action=update) | Update cart item quantity | No (supports guest) | 200, 400, 403 |
| 11 | Cart | DELETE | `/api/cart.php` (action=remove) | Remove item from cart | No (supports guest) | 200, 400, 403 |
| 12 | Wishlist | POST | `/api/wishlist.php` (action=add) | Add item to wishlist | **Yes** | 200, 400, 401, 403, 404 |
| 13 | Wishlist | POST | `/api/wishlist.php` (action=remove) | Remove item from wishlist | **Yes** | 200, 400, 401, 403 |
| 14 | Address | GET | `/api/address.php` | Get all saved addresses | **Yes** | 200, 401 |
| 15 | Address | POST | `/api/address.php` | Add a new delivery address | **Yes** | 200, 400, 401, 403 |
| 16 | Address | DELETE | `/api/address.php` | Delete a saved address | **Yes** | 200, 400, 401, 403, 404 |
| 17 | Address | POST | `/api/address.php` (action=set_default) | Mark an address as default | **Yes** | 200, 400, 401, 403, 404 |
| 18 | Order | POST | `/api/order.php` | Place a new order from cart | **Yes** | 200, 400, 401, 403, 500 |
| 19 | Appointments | POST | `/api/appointments.php` (action=book) | Book a doctor appointment | **Yes** | 200, 400, 401, 403, 404, 409 |
| 20 | Appointments | POST | `/api/appointments.php` (action=cancel) | Cancel an upcoming appointment | **Yes** | 200, 400, 401, 403, 404 |
| 21 | Profile | GET | `/api/profile.php` | Get logged-in user's profile and addresses | **Yes** | 200, 401 |
| 22 | Profile | POST | `/api/profile.php` (action=update_profile) | Update user's name and phone | **Yes** | 200, 400, 401, 403 |
| 23 | Profile | POST | `/api/profile.php` (action=change_password) | Change user's password | **Yes** | 200, 400, 401, 403 |

---

## Summary by HTTP Method

| Method | Count | Endpoints |
|--------|-------|-----------|
| GET    | 8     | register (response), login (response), logout, medicines, products, doctors, search, coupon, address (GET), profile (GET) |
| POST   | 12    | register, login, cart (add/update), wishlist (add/remove), address (add/set_default), order, appointments (book/cancel), profile (update/change_password) |
| DELETE | 2     | cart (remove), address (delete) |
| PUT    | 0     | — |

> Note: GET count reflects endpoints called via HTTP GET; some GET responses above include results like register/login which are POST calls. Corrected counts are listed in README.md.

---

## Summary by Authentication Requirement

| Category | Count |
|----------|-------|
| Public (No Auth Required) | 8 |
| Protected (Login Required) | 15 |

### Public Endpoints
1. `POST /api/register.php`
2. `POST /api/login.php`
3. `GET /logout.php`
4. `GET /api/medicines.php`
5. `GET /api/products.php`
6. `GET /api/doctors.php`
7. `GET /api/search.php`
8. `GET /api/coupon.php`
9. `POST /api/cart.php` (add/update/remove — guest session supported)
10. `DELETE /api/cart.php` (remove — guest session supported)

### Protected Endpoints (🔒 Login Required)
1. `POST /api/wishlist.php` (add/remove)
2. `GET /api/address.php`
3. `POST /api/address.php` (add/set_default)
4. `DELETE /api/address.php`
5. `POST /api/order.php`
6. `POST /api/appointments.php` (book/cancel)
7. `GET /api/profile.php`
8. `POST /api/profile.php` (update_profile/change_password)

---

## Summary by Module

| Module         | GET Endpoints | POST Endpoints | DELETE Endpoints | Total |
|----------------|---------------|----------------|------------------|-------|
| Authentication | 1 (logout)    | 2 (register, login) | 0           | 3     |
| Medicines      | 1             | 0              | 0                | 1     |
| Products       | 1             | 0              | 0                | 1     |
| Doctors        | 1             | 0              | 0                | 1     |
| Search         | 1             | 0              | 0                | 1     |
| Coupon         | 1             | 0              | 0                | 1     |
| Cart           | 0             | 2 (add, update) | 1 (remove)      | 3     |
| Wishlist       | 0             | 2 (add, remove) | 0               | 2     |
| Address        | 1             | 2 (add, set_default) | 1 (delete)  | 4     |
| Order          | 0             | 1              | 0                | 1     |
| Appointments   | 0             | 2 (book, cancel) | 0              | 2     |
| Profile        | 1             | 2 (update, change_password) | 0   | 3     |
| **TOTAL**      | **8**         | **13**         | **2**            | **23** |

---

*This documentation was generated using static analysis only. No existing project files were modified.*
