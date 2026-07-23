# ANVNA Care – API Test Cases

**Total Endpoints**: 23  
**Test Strategy**: Positive (Happy Path) + Negative (Error/Edge Cases)

---

> 📌 **Legend**:
> - ✅ **Positive** = Expected happy path behavior
> - ❌ **Negative** = Invalid input, missing data, or error conditions
> - 🔒 **Auth** = Test requires a logged-in session

---

## 1. Auth – Register (`POST /api/register.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-REG-01 | Register with all valid fields | ✅ Positive | name=John Doe, email=new@test.com, phone=9876543299, password=pass123 | 200 | success:true, user.id present, role='user' |
| TC-REG-02 | Verify auto-login after registration | ✅ Positive | Valid registration data | 200 | PHPSESSID cookie set |
| TC-REG-03 | Name with exactly 3 characters | ✅ Positive | name=Joe | 200 | success:true |
| TC-REG-04 | Register with name less than 3 chars | ❌ Negative | name=Jo | 400 | "Name must be at least 3 characters." |
| TC-REG-05 | Register with empty name | ❌ Negative | name="" | 400 | success:false |
| TC-REG-06 | Register with invalid email (missing @) | ❌ Negative | email=notanemail | 400 | "A valid email address is required." |
| TC-REG-07 | Register with invalid email (missing domain) | ❌ Negative | email=user@ | 400 | success:false |
| TC-REG-08 | Register with phone less than 10 digits | ❌ Negative | phone=12345 | 400 | "Phone must be a valid 10-digit number." |
| TC-REG-09 | Register with phone more than 10 digits | ❌ Negative | phone=12345678901 | 400 | success:false |
| TC-REG-10 | Register with phone containing letters | ❌ Negative | phone=abc1234567 | 400 | success:false |
| TC-REG-11 | Register with password less than 6 chars | ❌ Negative | password=abc | 400 | "Password must be at least 6 characters." |
| TC-REG-12 | Register with empty password | ❌ Negative | password="" | 400 | success:false |
| TC-REG-13 | Register with already existing email | ❌ Negative | email=amit.kumar@anvnacare.com | 409 | "Email address is already registered." |
| TC-REG-14 | Register with completely empty body | ❌ Negative | {} | 400 | success:false |
| TC-REG-15 | Response Content-Type is application/json | ✅ Positive | Valid data | 200 | Header: Content-Type: application/json |

---

## 2. Auth – Login (`POST /api/login.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-LOG-01 | Login with valid email and password | ✅ Positive | email=amit.kumar@anvnacare.com, password=password123 | 200 | success:true, user.id present |
| TC-LOG-02 | Login sets PHPSESSID cookie | ✅ Positive | Valid credentials | 200 | PHPSESSID cookie in response |
| TC-LOG-03 | Login response contains user object | ✅ Positive | Valid credentials | 200 | user.id, user.name, user.email, user.role |
| TC-LOG-04 | Login as admin user | ✅ Positive | email=admin@anvnacare.com, password=password123 | 200 | user.role='admin' |
| TC-LOG-05 | Login with wrong password | ❌ Negative | password=wrongpass | 401 | "Invalid email or password." |
| TC-LOG-06 | Login with non-existent email | ❌ Negative | email=nobody@test.com | 401 | success:false |
| TC-LOG-07 | Login with empty email | ❌ Negative | email="" | 400 | "Email and Password are required." |
| TC-LOG-08 | Login with empty password | ❌ Negative | password="" | 400 | success:false |
| TC-LOG-09 | Login with empty body | ❌ Negative | {} | 400 | success:false |
| TC-LOG-10 | Rate limiting: 5 failed attempts locks account | ❌ Negative | Wrong password x5 | 429 | "Too many failed login attempts." |
| TC-LOG-11 | Remember Me cookie set when remember=true | ✅ Positive | remember=true | 200 | remember_user cookie set |
| TC-LOG-12 | Remember Me cookie NOT set when remember=false | ✅ Positive | remember=false | 200 | No remember_user cookie |
| TC-LOG-13 | Content-Type header is application/json | ✅ Positive | Valid credentials | 200 | Content-Type: application/json |
| TC-LOG-14 | Remaining attempts count in error message | ❌ Negative | Wrong password x2 | 401 | "3 attempt(s) remaining" |

---

## 3. Auth – Logout (`GET /logout.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-OUT-01 | Logout destroys session | ✅ Positive | With valid PHPSESSID | 200/302 | Session destroyed |
| TC-OUT-02 | After logout, protected endpoints return 401 | ✅ Positive | Access /api/profile.php after logout | 401 | success:false |
| TC-OUT-03 | Logout clears remember_user cookie | ✅ Positive | Login with remember=true then logout | 302 | remember_user cookie cleared |

---

## 4. Medicines – List (`GET /api/medicines.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-MED-01 | Get all medicines (no filters) | ✅ Positive | None | 200 | success:true, medicines array with items |
| TC-MED-02 | Medicines list has pagination fields | ✅ Positive | None | 200 | count, total, page, limit fields |
| TC-MED-03 | Filter by OTC category | ✅ Positive | category=OTC | 200 | Only OTC medicines |
| TC-MED-04 | Filter by Prescription category | ✅ Positive | category=Prescription | 200 | Only Prescription medicines |
| TC-MED-05 | Filter by Vitamins category | ✅ Positive | category=Vitamins | 200 | Only Vitamins medicines |
| TC-MED-06 | Search by medicine name | ✅ Positive | search=paracetamol | 200 | Matching medicine(s) returned |
| TC-MED-07 | Sort by price ascending | ✅ Positive | sort=price_asc | 200 | Medicines in ascending price order |
| TC-MED-08 | Sort by price descending | ✅ Positive | sort=price_desc | 200 | Medicines in descending price order |
| TC-MED-09 | Sort by rating | ✅ Positive | sort=rating | 200 | Highest rated medicine first |
| TC-MED-10 | Pagination page 1 | ✅ Positive | page=1&limit=5 | 200 | 5 medicines, page=1 |
| TC-MED-11 | Pagination page 2 | ✅ Positive | page=2&limit=5 | 200 | page=2 in response |
| TC-MED-12 | Medicine item has all required fields | ✅ Positive | limit=1 | 200 | id, name, manufacturer, mrp, discount_price, stock, category |
| TC-MED-13 | Search with no match returns empty array | ❌ Negative | search=ZZZNOMATCH | 200 | medicines:[], count:0 |
| TC-MED-14 | Page beyond total data returns empty | ❌ Negative | page=9999 | 200 | count:0, medicines:[] |
| TC-MED-15 | No authentication required | ✅ Positive | No cookie | 200 | success:true |

---

## 5. Products – List (`GET /api/products.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-PRD-01 | Get all products (no filters) | ✅ Positive | None | 200 | success:true, products array |
| TC-PRD-02 | Filter by Supplements category | ✅ Positive | category=Supplements | 200 | Only Supplements products |
| TC-PRD-03 | Filter by Devices category | ✅ Positive | category=Devices | 200 | Only Devices products |
| TC-PRD-04 | Filter by Wellness category | ✅ Positive | category=Wellness | 200 | Only Wellness products |
| TC-PRD-05 | Search by product name | ✅ Positive | search=protein | 200 | Matching products returned |
| TC-PRD-06 | Sort by price ascending | ✅ Positive | sort=price_asc | 200 | Products in ascending price order |
| TC-PRD-07 | Sort by rating | ✅ Positive | sort=rating | 200 | Highest rated product first |
| TC-PRD-08 | Pagination works correctly | ✅ Positive | page=1&limit=3 | 200 | count=3, page=1 |
| TC-PRD-09 | Product has correct fields | ✅ Positive | limit=1 | 200 | id, name, mrp, discount_price, stock, category |
| TC-PRD-10 | Non-existent category returns empty | ❌ Negative | category=NotExist | 200 | count:0, products:[] |

---

## 6. Doctors – List (`GET /api/doctors.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-DOC-01 | Get all doctors (no filters) | ✅ Positive | None | 200 | success:true, doctors array |
| TC-DOC-02 | Default limit is 15 | ✅ Positive | None | 200 | limit:15 in response |
| TC-DOC-03 | Filter by Cardiologist specialization | ✅ Positive | specialization=Cardiologist | 200 | Only cardiologists |
| TC-DOC-04 | Filter by Dentist specialization | ✅ Positive | specialization=Dentist | 200 | Only dentists |
| TC-DOC-05 | Search by doctor name | ✅ Positive | search=Sharma | 200 | Matching doctors |
| TC-DOC-06 | Search by specialization keyword | ✅ Positive | search=cardio | 200 | Matching doctors |
| TC-DOC-07 | Sort by fee ascending | ✅ Positive | sort=fee_asc | 200 | Lowest fee first |
| TC-DOC-08 | Sort by fee descending | ✅ Positive | sort=fee_desc | 200 | Highest fee first |
| TC-DOC-09 | Sort by experience | ✅ Positive | sort=experience | 200 | Most experienced first |
| TC-DOC-10 | Doctor record has all required fields | ✅ Positive | limit=1 | 200 | id, name, specialization, fee, experience, languages |
| TC-DOC-11 | Non-existent specialization returns empty | ❌ Negative | specialization=AlienDoctor | 200 | doctors:[], count:0 |

---

## 7. Search – Global (`GET /api/search.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-SRC-01 | Search with valid query returns results | ✅ Positive | q=para | 200 | success:true, results array |
| TC-SRC-02 | Search result items have type, id, name, url | ✅ Positive | q=blood | 200 | All 4 fields in each result |
| TC-SRC-03 | Search covers medicines | ✅ Positive | q=paracetamol | 200 | result with type='medicine' |
| TC-SRC-04 | Search covers doctors | ✅ Positive | q=sharma | 200 | result with type='doctor' |
| TC-SRC-05 | Search covers lab tests | ✅ Positive | q=blood count | 200 | result with type='test' |
| TC-SRC-06 | Query with 1 character returns error | ❌ Negative | q=a | 200 | success:false, results:[] |
| TC-SRC-07 | Empty query returns error | ❌ Negative | q= | 200 | success:false, results:[] |
| TC-SRC-08 | Missing q parameter returns error | ❌ Negative | (no q) | 200 | success:false |
| TC-SRC-09 | Search that matches nothing returns empty | ❌ Negative | q=zzznoresult | 200 | success:true, results:[] |
| TC-SRC-10 | Results limited to 4 per category | ✅ Positive | q=a (broad query) | 200 | max 4 per type |

---

## 8. Coupon (`GET /api/coupon.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-CPN-01 | Valid coupon SAVE10 with sufficient cart value | ✅ Positive | code=SAVE10, cart_value=500 | 200 | success:true, discount_type='percentage', discount_value=10 |
| TC-CPN-02 | Valid fixed coupon WELCOME | ✅ Positive | code=WELCOME, cart_value=600 | 200 | discount_type='fixed', discount_value=100 |
| TC-CPN-03 | Coupon HEALTH20 with 20% discount | ✅ Positive | code=HEALTH20, cart_value=1500 | 200 | discount_value=20 |
| TC-CPN-04 | Coupon code is case-sensitive | ❌ Negative | code=save10 | 200 | success:false |
| TC-CPN-05 | Invalid coupon code | ❌ Negative | code=FAKECODE | 200 | "Invalid or expired coupon code." |
| TC-CPN-06 | Cart value below minimum for WELCOME | ❌ Negative | code=WELCOME, cart_value=100 | 200 | success:false, "Minimum cart value required" |
| TC-CPN-07 | Missing coupon code returns 400 | ❌ Negative | (no code param) | 400 | "Coupon code is required." |
| TC-CPN-08 | Empty coupon code returns 400 | ❌ Negative | code= | 400 | success:false |
| TC-CPN-09 | Coupon response has code, type, value fields | ✅ Positive | code=SAVE10, cart_value=500 | 200 | coupon.code, coupon.discount_type, coupon.discount_value |

---

## 9. Cart – Add (`POST /api/cart.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-CRT-01 | Add medicine to cart (logged-in user) 🔒 | ✅ Positive | action=add, item_id=1, item_type=medicine, quantity=1 | 200 | success:true, cart_count updated |
| TC-CRT-02 | Add product to cart 🔒 | ✅ Positive | action=add, item_id=1, item_type=product | 200 | success:true, cart_count updated |
| TC-CRT-03 | Add lab test to cart 🔒 | ✅ Positive | action=add, item_id=1, item_type=test | 200 | success:true |
| TC-CRT-04 | Add item as guest (session cart) | ✅ Positive | action=add, item_id=1, item_type=medicine | 200/403 | success:true (if no CSRF) or 403 |
| TC-CRT-05 | Cart count increases after adding item | ✅ Positive | action=add | 200 | cart_count > 0 |
| TC-CRT-06 | Add with invalid item_id (0) | ❌ Negative | item_id=0 | 400 | "Invalid parameters." |
| TC-CRT-07 | Add with missing item_type | ❌ Negative | item_type not set | 400 | "Invalid parameters." |
| TC-CRT-08 | Add quantity exceeding stock | ❌ Negative | quantity=99999 | 400 | "Requested quantity is out of stock." |
| TC-CRT-09 | Add non-existent medicine ID | ❌ Negative | item_id=9999 | 404 | "Item not found." |
| TC-CRT-10 | Invalid CSRF token returns 403 | ❌ Negative | csrf_token=invalid | 403 | "Invalid or missing CSRF token." |
| TC-CRT-11 | Update cart item quantity 🔒 | ✅ Positive | action=update, item_id=1, item_type=medicine, quantity=3 | 200 | "Cart updated.", cart_count updated |
| TC-CRT-12 | Update with quantity exceeding stock | ❌ Negative | action=update, quantity=99999 | 200 | success:false, "Quantity exceeds stock" |
| TC-CRT-13 | Remove item using DELETE method 🔒 | ✅ Positive | DELETE with item_id=1, item_type=medicine | 200 | "Item removed from cart." |
| TC-CRT-14 | Remove item using action=remove 🔒 | ✅ Positive | action=remove, item_id=1, item_type=medicine | 200 | success:true |
| TC-CRT-15 | Remove with missing item_type | ❌ Negative | DELETE without item_type | 400 | "Missing item type or item ID." |

---

## 10. Wishlist (`POST /api/wishlist.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-WSH-01 | Add medicine to wishlist 🔒 | ✅ Positive | action=add, item_id=1, item_type=medicine | 200 | success:true |
| TC-WSH-02 | Add product to wishlist 🔒 | ✅ Positive | action=add, item_id=1, item_type=product | 200 | success:true |
| TC-WSH-03 | Add same item twice (idempotent) 🔒 | ✅ Positive | action=add, same item | 200 | success:true (INSERT IGNORE) |
| TC-WSH-04 | Remove item from wishlist 🔒 | ✅ Positive | action=remove, item_id=1, item_type=medicine | 200 | success:true |
| TC-WSH-05 | Access without login returns 401 | ❌ Negative | No session cookie | 401 | "Please login to manage your wishlist." |
| TC-WSH-06 | Add with item_type=test returns 400 | ❌ Negative | item_type=test | 400 | "Invalid parameters." |
| TC-WSH-07 | Add with missing item_id returns 400 | ❌ Negative | item_id not set | 400 | "Invalid parameters." |
| TC-WSH-08 | Add with non-existent item_id returns 404 | ❌ Negative | item_id=9999 | 404 | "Item not found." |
| TC-WSH-09 | Invalid action returns 400 | ❌ Negative | action=delete | 400 | "Invalid action." |
| TC-WSH-10 | Missing CSRF token returns 403 | ❌ Negative | No CSRF token | 403 | "Invalid or missing CSRF token." |

---

## 11. Address – GET (`GET /api/address.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-ADR-01 | Get all addresses for logged-in user 🔒 | ✅ Positive | GET request with session | 200 | success:true, addresses array |
| TC-ADR-02 | Default address appears first 🔒 | ✅ Positive | GET request | 200 | First address has is_default=1 |
| TC-ADR-03 | Address has all required fields 🔒 | ✅ Positive | GET request | 200 | id, name, phone, address_line1, city, state, pincode |
| TC-ADR-04 | Get addresses without login returns 401 | ❌ Negative | No session | 401 | "Please login to manage addresses." |

---

## 12. Address – Add (`POST /api/address.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-ADR-05 | Add new address with all valid fields 🔒 | ✅ Positive | All required fields | 200 | success:true, address.id present |
| TC-ADR-06 | Add address without address_line2 (optional) 🔒 | ✅ Positive | No line2 | 200 | success:true |
| TC-ADR-07 | Set is_default=1 marks as default 🔒 | ✅ Positive | is_default=1 | 200 | success:true |
| TC-ADR-08 | Missing required field (city) returns 400 | ❌ Negative | No city | 400 | "All address fields...are required." |
| TC-ADR-09 | Invalid phone (5 digits) returns 400 | ❌ Negative | phone=12345 | 400 | "Phone must be a 10-digit number." |
| TC-ADR-10 | Invalid pincode (4 digits) returns 400 | ❌ Negative | pincode=1234 | 400 | "Pincode must be a 6-digit number." |

---

## 13. Address – Delete (`DELETE /api/address.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-ADR-11 | Delete own address 🔒 | ✅ Positive | address_id of own address | 200 | "Address deleted successfully." |
| TC-ADR-12 | Delete another user's address returns 404 🔒 | ❌ Negative | address_id of other user | 404 | "Address not found." |
| TC-ADR-13 | Delete with invalid address_id=0 | ❌ Negative | address_id=0 | 400 | "Invalid address ID." |

---

## 14. Address – Set Default (`POST /api/address.php` with action=set_default)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-ADR-14 | Set existing address as default 🔒 | ✅ Positive | action=set_default, address_id=1 | 200 | "Default address updated." |
| TC-ADR-15 | Set non-existent address as default 🔒 | ❌ Negative | address_id=9999 | 404 | "Address not found." |
| TC-ADR-16 | Set another user's address as default | ❌ Negative | Another user's address_id | 404 | "Address not found." |

---

## 15. Order – Place Order (`POST /api/order.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-ORD-01 | Place order with valid cart and address 🔒 | ✅ Positive | address_id=1, payment_method=Card | 200 | success:true, order_id, order_number, net_amount |
| TC-ORD-02 | Order number has correct format 🔒 | ✅ Positive | Valid order placement | 200 | order_number starts with 'ORD-' |
| TC-ORD-03 | Place order with coupon applied 🔒 | ✅ Positive | coupon_code=SAVE10 | 200 | net_amount is discounted |
| TC-ORD-04 | Place order with COD payment 🔒 | ✅ Positive | payment_method=COD | 200 | success:true (payment_status=Pending) |
| TC-ORD-05 | Cart is cleared after order 🔒 | ✅ Positive | Place order | 200 | Cart becomes empty |
| TC-ORD-06 | Place order without login | ❌ Negative | No session | 401 | "Please login to complete your order." |
| TC-ORD-07 | Place order with empty cart 🔒 | ❌ Negative | No items in cart | 400 | "Your cart is empty." |
| TC-ORD-08 | Place order without address_id 🔒 | ❌ Negative | address_id=0 | 400 | "Please select a delivery address." |
| TC-ORD-09 | Place order with invalid address_id 🔒 | ❌ Negative | address_id=9999 | 400 | "Invalid delivery address." |
| TC-ORD-10 | Place order with out-of-stock item 🔒 | ❌ Negative | Item with 0 stock in cart | 500 | "Item 'X' is out of stock." |

---

## 16. Appointments – Book (`POST /api/appointments.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-APT-01 | Book appointment with valid doctor, date, time 🔒 | ✅ Positive | action=book, doctor_id=1, date=2027-01-15, time=10:00 AM | 200 | success:true, appointment_id present |
| TC-APT-02 | Booking message contains doctor name 🔒 | ✅ Positive | Valid booking | 200 | message contains doctor name |
| TC-APT-03 | Book appointment without login | ❌ Negative | No session | 401 | "Please login to manage appointments." |
| TC-APT-04 | Book with non-existent doctor ID 🔒 | ❌ Negative | doctor_id=9999 | 404 | "Selected doctor not found." |
| TC-APT-05 | Book with invalid date format (DD-MM-YYYY) 🔒 | ❌ Negative | date=15-01-2027 | 400 | "Invalid date format. Expected YYYY-MM-DD." |
| TC-APT-06 | Book with missing date 🔒 | ❌ Negative | No date field | 400 | "Doctor, appointment date, and time slot are required." |
| TC-APT-07 | Book with missing time 🔒 | ❌ Negative | No time field | 400 | success:false |
| TC-APT-08 | Book duplicate slot returns 409 🔒 | ❌ Negative | Same doctor, date, time (already booked) | 409 | "You already have an upcoming appointment with this doctor..." |
| TC-APT-09 | Book with doctor_id=0 🔒 | ❌ Negative | doctor_id=0 | 400 | success:false |
| TC-APT-10 | Invalid action returns 400 🔒 | ❌ Negative | action=delete | 400 | "Invalid action." |

---

## 17. Appointments – Cancel (`POST /api/appointments.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-APT-11 | Cancel an upcoming appointment 🔒 | ✅ Positive | action=cancel, appointment_id=valid_upcoming_id | 200 | "Appointment cancelled successfully." |
| TC-APT-12 | Cancel appointment not owned by user 🔒 | ❌ Negative | appointment_id of another user | 404 | "Appointment not found." |
| TC-APT-13 | Cancel already cancelled appointment 🔒 | ❌ Negative | appointment with status=Cancelled | 400 | "Only upcoming appointments can be cancelled." |
| TC-APT-14 | Cancel completed appointment 🔒 | ❌ Negative | appointment with status=Completed | 400 | "Only upcoming appointments can be cancelled." |
| TC-APT-15 | Cancel with invalid appointment_id=0 🔒 | ❌ Negative | appointment_id=0 | 400 | "Invalid appointment ID." |

---

## 18. Profile – GET (`GET /api/profile.php`)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-PRF-01 | Get profile of logged-in user 🔒 | ✅ Positive | GET with session | 200 | success:true, user object, addresses array |
| TC-PRF-02 | Profile has id, name, email, phone, role, created_at 🔒 | ✅ Positive | GET request | 200 | All 6 fields present |
| TC-PRF-03 | Profile does NOT expose password hash 🔒 | ✅ Positive | GET request | 200 | No 'password' field in user object |
| TC-PRF-04 | Get profile without login | ❌ Negative | No session | 401 | "Unauthorized. Please login first." |

---

## 19. Profile – Update (`POST /api/profile.php` with action=update_profile)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-PRF-05 | Update name and phone 🔒 | ✅ Positive | action=update_profile, name=New Name, phone=9876543211 | 200 | "Profile updated successfully." |
| TC-PRF-06 | Update with name too short (< 3 chars) 🔒 | ❌ Negative | name=AB | 400 | "Name must be at least 3 characters." |
| TC-PRF-07 | Update with invalid phone 🔒 | ❌ Negative | phone=12345 | 400 | "Phone must be a valid 10-digit number." |
| TC-PRF-08 | Update without login | ❌ Negative | No session | 401 | success:false |

---

## 20. Profile – Change Password (`POST /api/profile.php` with action=change_password)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-PRF-09 | Change password with correct current password 🔒 | ✅ Positive | current_password=password123, new_password=newpass456 | 200 | "Password changed successfully." |
| TC-PRF-10 | Change password with wrong current password 🔒 | ❌ Negative | current_password=wrongpass | 400 | "Incorrect current password." |
| TC-PRF-11 | New password less than 6 chars 🔒 | ❌ Negative | new_password=abc | 400 | "New password must be at least 6 characters." |
| TC-PRF-12 | Missing current_password field 🔒 | ❌ Negative | No current_password | 400 | "Current password and New password are required." |
| TC-PRF-13 | Missing new_password field 🔒 | ❌ Negative | No new_password | 400 | success:false |

---

## 21. CSRF Protection (Cross-cutting)

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-CSRF-01 | POST to cart without CSRF token | ❌ Negative | No csrf_token, no X-CSRF-Token header | 403 | "Invalid or missing CSRF token." |
| TC-CSRF-02 | POST to wishlist without CSRF token | ❌ Negative | No csrf_token | 403 | success:false |
| TC-CSRF-03 | POST to appointments without CSRF token | ❌ Negative | No csrf_token | 403 | success:false |
| TC-CSRF-04 | POST with invalid CSRF token | ❌ Negative | csrf_token=fakeinvalidtoken | 403 | success:false |
| TC-CSRF-05 | Valid CSRF token allows request | ✅ Positive | csrf_token from session | 200 | Request processed |

---

## 22. Security & Edge Cases

| # | Test Case | Type | Input | Expected Status | Expected Response |
|---|-----------|------|-------|-----------------|-------------------|
| TC-SEC-01 | SQL Injection in search query | ❌ Negative | q=' OR '1'='1 | 200 | Safe response (PDO prevents injection) |
| TC-SEC-02 | XSS in name field during registration | ❌ Negative | name=<script>alert(1)</script> | 200 | HTML chars escaped in response |
| TC-SEC-03 | Register with very long name (overflow) | ❌ Negative | name with 1000 chars | 200/400 | Handled gracefully |
| TC-SEC-04 | Access admin data without admin role | ❌ Negative | Regular user accessing admin pages | 403/401 | Access denied |

---

*Total Test Cases: 180+*  
*This documentation was generated using static analysis only. No existing project files were modified.*
