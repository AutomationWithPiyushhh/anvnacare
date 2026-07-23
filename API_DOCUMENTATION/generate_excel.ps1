
# ANVNA Care - Generate Excel using OpenXML (no COM, no Excel needed)
# Creates a proper .xlsx file directly

$outputPath = "c:\Users\User\anvnacare\API_DOCUMENTATION\ANVNA_Care_API_Test_Data.xlsx"

Add-Type -AssemblyName "DocumentFormat.OpenXml" -ErrorAction SilentlyContinue

# Check if DocumentFormat.OpenXml is available
$oxml = [System.AppDomain]::CurrentDomain.GetAssemblies() | Where-Object { $_.GetName().Name -eq "DocumentFormat.OpenXml" }

if (-not $oxml) {
    Write-Host "OpenXml SDK not available, falling back to raw XML method..." -ForegroundColor Yellow

    # --- Pure XML / ZIP approach ---
    # .xlsx is a ZIP archive containing XML files

    Add-Type -AssemblyName "System.IO.Compression"
    Add-Type -AssemblyName "System.IO.Compression.FileSystem"

    # ── Shared strings table ────────────────────────────────────────────
    # We'll inline string values directly in the cells (type="inlineStr")
    # so we do NOT need a sharedStrings.xml

    # ── Helper: escape XML ──────────────────────────────────────────────
    function EscapeXml($s) {
        return [System.Security.SecurityElement]::Escape([string]$s)
    }

    # ── Helper: build a cell with an inline string ───────────────────────
    function Cell($col, $row, $value, $styleIdx = 0) {
        $v = EscapeXml $value
        return "<c r=`"$col$row`" t=`"inlineStr`" s=`"$styleIdx`"><is><t>$v</t></is></c>"
    }

    function NumCell($col, $row, $value, $styleIdx = 0) {
        return "<c r=`"$col$row`" s=`"$styleIdx`"><v>$value</v></c>"
    }

    # ── All endpoint data ────────────────────────────────────────────────
    $endpoints = @(
        @{ID=1;  Method="POST";   URL="https://anvnacare.alwaysdata.net/api/register.php";   Req="{ name: John Doe, email: john@example.com, phone: 9876543299, password: test1234 }";                                               Res="{ success: true, message: Registration successful., user: { id: 16, name: John Doe, email: john@example.com, role: user } }";  Code=200},
        @{ID=2;  Method="POST";   URL="https://anvnacare.alwaysdata.net/api/login.php";      Req="{ email: amit.kumar@anvnacare.com, password: password123, remember: false }";                                                        Res="{ success: true, message: Login successful., user: { id: 2, name: Amit Kumar, email: amit.kumar@anvnacare.com, role: user } }";  Code=200},
        @{ID=3;  Method="GET";    URL="https://anvnacare.alwaysdata.net/logout.php";         Req="NA | Cookie: PHPSESSID=<session_id>";                                                                                                 Res="HTTP 302 Redirect to index.php. Session destroyed and cookies cleared.";                                                          Code=302},
        @{ID=4;  Method="GET";    URL="https://anvnacare.alwaysdata.net/api/medicines.php";  Req="NA | Optional query params: category, search, sort, page, limit";                                                                   Res="{ success: true, count: 10, total: 20, page: 1, medicines: [{ id: 1, name: Paracetamol 650mg, mrp: 30.00, discount_price: 24.00, stock: 120, category: OTC }] }"; Code=200},
        @{ID=5;  Method="GET";    URL="https://anvnacare.alwaysdata.net/api/medicines.php?category=OTC&sort=price_asc"; Req="NA | category=OTC, sort=price_asc, page=1, limit=5";                                                    Res="{ success: true, count: 5, total: 8, medicines: [{ id: 10, name: Amlodipine 5mg, discount_price: 22.00, category: OTC }] }";  Code=200},
        @{ID=6;  Method="GET";    URL="https://anvnacare.alwaysdata.net/api/medicines.php?search=paracetamol"; Req="NA | search=paracetamol";                                                                                          Res="{ success: true, count: 1, medicines: [{ id: 1, name: Paracetamol 650mg (Crocin), discount_price: 24.00 }] }";               Code=200},
        @{ID=7;  Method="GET";    URL="https://anvnacare.alwaysdata.net/api/products.php";   Req="NA | Optional query params: category, search, sort, page, limit";                                                                   Res="{ success: true, count: 10, total: 20, products: [{ id: 1, name: Whey Protein Powder 1kg, mrp: 3200.00, discount_price: 2699.00, category: Supplements }] }"; Code=200},
        @{ID=8;  Method="GET";    URL="https://anvnacare.alwaysdata.net/api/products.php?category=Devices&sort=rating"; Req="NA | category=Devices, sort=rating";                                                                     Res="{ success: true, count: 8, products: [{ id: 20, name: Premium Digital BP Monitor, discount_price: 2999.00, rating: 4.80, category: Devices }] }";            Code=200},
        @{ID=9;  Method="GET";    URL="https://anvnacare.alwaysdata.net/api/doctors.php";    Req="NA | Optional query params: specialization, search, sort, page, limit";                                                             Res="{ success: true, count: 15, doctors: [{ id: 1, name: Dr. Arvind Sharma, specialization: Cardiologist, fee: 800.00, experience: 15 }] }";                      Code=200},
        @{ID=10; Method="GET";    URL="https://anvnacare.alwaysdata.net/api/doctors.php?specialization=Cardiologist"; Req="NA | specialization=Cardiologist";                                                                          Res="{ success: true, count: 1, doctors: [{ id: 1, name: Dr. Arvind Sharma, specialization: Cardiologist, fee: 800.00 }] }";      Code=200},
        @{ID=11; Method="GET";    URL="https://anvnacare.alwaysdata.net/api/doctors.php?sort=fee_asc"; Req="NA | sort=fee_asc (lowest fee first)";                                                                                     Res="{ success: true, count: 15, doctors: [{ id: 15, name: Dr. Ananya Sen, specialization: Dentist, fee: 400.00 }] }";             Code=200},
        @{ID=12; Method="GET";    URL="https://anvnacare.alwaysdata.net/api/search.php?q=para"; Req="NA | q=para";                                                                                                                     Res="{ success: true, results: [{ type: medicine, id: 1, name: Paracetamol 650mg, url: medicine-details.php?id=1 }] }";            Code=200},
        @{ID=13; Method="GET";    URL="https://anvnacare.alwaysdata.net/api/coupon.php?code=SAVE10&cart_value=500"; Req="NA | code=SAVE10, cart_value=500";                                                                           Res="{ success: true, coupon: { code: SAVE10, discount_type: percentage, discount_value: 10.0 } }";                                  Code=200},
        @{ID=14; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/cart.php";       Req="{ action: add, item_id: 1, item_type: medicine, quantity: 1, csrf_token: <token> }";                                               Res="{ success: true, message: Paracetamol 650mg added to cart., cart_count: 1 }";                                                   Code=200},
        @{ID=15; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/cart.php";       Req="{ action: add, item_id: 1, item_type: product, quantity: 2, csrf_token: <token> }";                                                Res="{ success: true, message: Whey Protein Powder 1kg added to cart., cart_count: 3 }";                                             Code=200},
        @{ID=16; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/cart.php";       Req="{ action: update, item_id: 1, item_type: medicine, quantity: 3, csrf_token: <token> }";                                            Res="{ success: true, message: Cart updated., cart_count: 3 }";                                                                      Code=200},
        @{ID=17; Method="DELETE"; URL="https://anvnacare.alwaysdata.net/api/cart.php";       Req="{ item_id: 1, item_type: medicine, csrf_token: <token> }";                                                                          Res="{ success: true, message: Item removed from cart., cart_count: 0 }";                                                            Code=200},
        @{ID=18; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/wishlist.php";   Req="{ action: add, item_id: 1, item_type: medicine, csrf_token: <token> }";                                                            Res="{ success: true, message: Paracetamol 650mg added to wishlist. }";                                                              Code=200},
        @{ID=19; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/wishlist.php";   Req="{ action: remove, item_id: 1, item_type: medicine, csrf_token: <token> }";                                                         Res="{ success: true, message: Item removed from wishlist. }";                                                                       Code=200},
        @{ID=20; Method="GET";    URL="https://anvnacare.alwaysdata.net/api/address.php";    Req="NA | Cookie: PHPSESSID=<session_id> (Login required)";                                                                              Res="{ success: true, addresses: [{ id: 1, name: Amit Kumar, phone: 9876543211, city: Bengaluru, state: Karnataka, pincode: 560103, is_default: 1 }] }"; Code=200},
        @{ID=21; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/address.php";    Req="{ name: John Doe, phone: 9876543200, address_line1: Flat 101 Test Tower, city: Pune, state: Maharashtra, pincode: 411001, is_default: 1, csrf_token: <token> }"; Res="{ success: true, message: Address saved successfully., address: { id: 15, name: John Doe, city: Pune, pincode: 411001 } }"; Code=200},
        @{ID=22; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/address.php";    Req="{ action: set_default, address_id: 2, csrf_token: <token> }";                                                                       Res="{ success: true, message: Default address updated. }";                                                                          Code=200},
        @{ID=23; Method="DELETE"; URL="https://anvnacare.alwaysdata.net/api/address.php";    Req="{ address_id: 1, csrf_token: <token> }";                                                                                             Res="{ success: true, message: Address deleted successfully. }";                                                                     Code=200},
        @{ID=24; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/order.php";      Req="{ address_id: 1, payment_method: Card, delivery_method: Standard, coupon_code: SAVE10, csrf_token: <token> }";                     Res="{ success: true, message: Order placed successfully!, order_id: 21, order_number: ORD-20260721-4582, total_amount: 240.00, discount_amount: 24.00, net_amount: 216.00 }"; Code=200},
        @{ID=25; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/appointments.php"; Req="{ action: book, doctor_id: 1, date: 2026-12-25, time: 10:00 AM, csrf_token: <token> }";                                         Res="{ success: true, message: Appointment with Dr. Arvind Sharma booked for 2026-12-25 at 10:00 AM., appointment_id: 21 }";        Code=200},
        @{ID=26; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/appointments.php"; Req="{ action: cancel, appointment_id: 21, csrf_token: <token> }";                                                                    Res="{ success: true, message: Appointment cancelled successfully. }";                                                               Code=200},
        @{ID=27; Method="GET";    URL="https://anvnacare.alwaysdata.net/api/profile.php";    Req="NA | Cookie: PHPSESSID=<session_id> (Login required)";                                                                              Res="{ success: true, user: { id: 2, name: Amit Kumar, email: amit.kumar@anvnacare.com, phone: 9876543211, role: user, created_at: 2026-07-01 }, addresses: [{ id: 1, city: Bengaluru, is_default: 1 }] }"; Code=200},
        @{ID=28; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/profile.php";    Req="{ action: update_profile, name: Amit Kumar Updated, phone: 9876543211, csrf_token: <token> }";                                     Res="{ success: true, message: Profile updated successfully. }";                                                                     Code=200},
        @{ID=29; Method="POST";   URL="https://anvnacare.alwaysdata.net/api/profile.php";    Req="{ action: change_password, current_password: password123, new_password: newpassword456, csrf_token: <token> }";                    Res="{ success: true, message: Password changed successfully. }";                                                                    Code=200}
    )

    $errorData = @(
        @{ID=1; Mod="Auth";         Mth="POST";   URI="/api/register.php";       Scenario="Name less than 3 characters";                                Res="success: false, message: Name must be at least 3 characters.";              Code=400},
        @{ID=2; Mod="Auth";         Mth="POST";   URI="/api/register.php";       Scenario="Invalid email format (missing @ symbol)";                    Res="success: false, message: A valid email address is required.";               Code=400},
        @{ID=3; Mod="Auth";         Mth="POST";   URI="/api/register.php";       Scenario="Phone number not exactly 10 digits";                         Res="success: false, message: Phone must be a valid 10-digit number.";           Code=400},
        @{ID=4; Mod="Auth";         Mth="POST";   URI="/api/register.php";       Scenario="Password less than 6 characters";                            Res="success: false, message: Password must be at least 6 characters.";         Code=400},
        @{ID=5; Mod="Auth";         Mth="POST";   URI="/api/register.php";       Scenario="Email already exists in database";                           Res="success: false, message: Email address is already registered.";            Code=409},
        @{ID=6; Mod="Auth";         Mth="POST";   URI="/api/login.php";          Scenario="Email or password field is empty";                           Res="success: false, message: Email and Password are required.";                Code=400},
        @{ID=7; Mod="Auth";         Mth="POST";   URI="/api/login.php";          Scenario="Correct email but wrong password";                           Res="success: false, message: Invalid email or password. 4 attempt(s) remaining before lockout."; Code=401},
        @{ID=8; Mod="Auth";         Mth="POST";   URI="/api/login.php";          Scenario="5 or more consecutive failed login attempts (Rate Limited)"; Res="success: false, message: Too many failed login attempts. Please wait 8 minute(s) and try again."; Code=429},
        @{ID=9; Mod="Cart";         Mth="POST";   URI="/api/cart.php";           Scenario="Invalid or missing CSRF token in request";                   Res="success: false, message: Invalid or missing CSRF token. Please refresh the page and try again."; Code=403},
        @{ID=10;Mod="Cart";         Mth="POST";   URI="/api/cart.php";           Scenario="item_id does not exist in the database";                     Res="success: false, message: Item not found.";                                  Code=404},
        @{ID=11;Mod="Cart";         Mth="POST";   URI="/api/cart.php";           Scenario="Quantity exceeds available stock";                           Res="success: false, message: Requested quantity is out of stock. Only 5 left.";Code=400},
        @{ID=12;Mod="Cart";         Mth="POST";   URI="/api/cart.php";           Scenario="item_type parameter missing from body";                      Res="success: false, message: Invalid parameters.";                              Code=400},
        @{ID=13;Mod="Cart";         Mth="POST";   URI="/api/cart.php";           Scenario="Invalid action value (e.g. action=delete)";                  Res="success: false, message: Invalid action.";                                  Code=400},
        @{ID=14;Mod="Wishlist";     Mth="POST";   URI="/api/wishlist.php";       Scenario="User not logged in (no valid session)";                      Res="success: false, message: Please login to manage your wishlist.";            Code=401},
        @{ID=15;Mod="Wishlist";     Mth="POST";   URI="/api/wishlist.php";       Scenario="item_type=test is not allowed in wishlist";                  Res="success: false, message: Invalid parameters.";                              Code=400},
        @{ID=16;Mod="Wishlist";     Mth="POST";   URI="/api/wishlist.php";       Scenario="item_id does not exist in the database";                     Res="success: false, message: Item not found.";                                  Code=404},
        @{ID=17;Mod="Coupon";       Mth="GET";    URI="/api/coupon.php";         Scenario="Coupon code query parameter is missing";                     Res="success: false, message: Coupon code is required.";                        Code=400},
        @{ID=18;Mod="Coupon";       Mth="GET";    URI="/api/coupon.php";         Scenario="Invalid or expired coupon code provided";                    Res="success: false, message: Invalid or expired coupon code.";                 Code=200},
        @{ID=19;Mod="Coupon";       Mth="GET";    URI="/api/coupon.php";         Scenario="Cart total is below coupon minimum (WELCOME needs Rs.500)";  Res="success: false, message: Minimum cart value required to apply this coupon is Rs.500.00"; Code=200},
        @{ID=20;Mod="Address";      Mth="GET";    URI="/api/address.php";        Scenario="User not logged in (no valid session)";                      Res="success: false, message: Please login to manage addresses.";               Code=401},
        @{ID=21;Mod="Address";      Mth="POST";   URI="/api/address.php";        Scenario="Required field missing (e.g. city not provided)";            Res="success: false, message: All address fields (except Line 2) are required.";Code=400},
        @{ID=22;Mod="Address";      Mth="POST";   URI="/api/address.php";        Scenario="Pincode is not exactly 6 digits";                            Res="success: false, message: Pincode must be a 6-digit number.";               Code=400},
        @{ID=23;Mod="Address";      Mth="DELETE"; URI="/api/address.php";        Scenario="Trying to delete another users address by ID";               Res="success: false, message: Address not found.";                              Code=404},
        @{ID=24;Mod="Order";        Mth="POST";   URI="/api/order.php";          Scenario="User not logged in (no session)";                            Res="success: false, message: Please login to complete your order.";            Code=401},
        @{ID=25;Mod="Order";        Mth="POST";   URI="/api/order.php";          Scenario="Cart is empty when placing order";                           Res="success: false, message: Your cart is empty.";                             Code=400},
        @{ID=26;Mod="Order";        Mth="POST";   URI="/api/order.php";          Scenario="address_id not provided in request body";                   Res="success: false, message: Please select a delivery address.";               Code=400},
        @{ID=27;Mod="Order";        Mth="POST";   URI="/api/order.php";          Scenario="Item out of stock at checkout time";                         Res="success: false, message: Item Paracetamol is out of stock. Max: 0";        Code=500},
        @{ID=28;Mod="Appointments"; Mth="POST";   URI="/api/appointments.php";   Scenario="User not logged in (no session)";                            Res="success: false, message: Please login to manage appointments.";           Code=401},
        @{ID=29;Mod="Appointments"; Mth="POST";   URI="/api/appointments.php";   Scenario="doctor_id=9999 does not exist in database";                  Res="success: false, message: Selected doctor not found.";                     Code=404},
        @{ID=30;Mod="Appointments"; Mth="POST";   URI="/api/appointments.php";   Scenario="Date in wrong format (DD-MM-YYYY instead of YYYY-MM-DD)";    Res="success: false, message: Invalid date format. Expected YYYY-MM-DD.";      Code=400},
        @{ID=31;Mod="Appointments"; Mth="POST";   URI="/api/appointments.php";   Scenario="Same doctor, date and time already booked by user";          Res="success: false, message: You already have an upcoming appointment with this doctor on 2026-12-25 at 10:00 AM."; Code=409},
        @{ID=32;Mod="Appointments"; Mth="POST";   URI="/api/appointments.php";   Scenario="Trying to cancel an already Cancelled appointment";          Res="success: false, message: Only upcoming appointments can be cancelled.";   Code=400},
        @{ID=33;Mod="Profile";      Mth="GET";    URI="/api/profile.php";        Scenario="User not logged in (no session)";                            Res="success: false, message: Unauthorized. Please login first.";              Code=401},
        @{ID=34;Mod="Profile";      Mth="POST";   URI="/api/profile.php";        Scenario="Updated name has fewer than 3 characters";                   Res="success: false, message: Name must be at least 3 characters.";            Code=400},
        @{ID=35;Mod="Profile";      Mth="POST";   URI="/api/profile.php";        Scenario="Wrong current password when changing password";              Res="success: false, message: Incorrect current password.";                    Code=400},
        @{ID=36;Mod="Profile";      Mth="POST";   URI="/api/profile.php";        Scenario="New password has fewer than 6 characters";                   Res="success: false, message: New password must be at least 6 characters.";    Code=400},
        @{ID=37;Mod="Search";       Mth="GET";    URI="/api/search.php";         Scenario="Search query is only 1 character (minimum is 2)";            Res="success: false, message: Query too short (min 2 chars). results: []";     Code=200}
    )

    # ── Build Sheet 1 XML ──────────────────────────────────────────────
    $cols = @("A","B","C","D","E","F")
    $hdrRow = "<row r=`"1`" ht=`"40`" customHeight=`"1`"><c r=`"A1`" t=`"inlineStr`" s=`"1`"><is><t>TCID</t></is></c><c r=`"B1`" t=`"inlineStr`" s=`"1`"><is><t>Request Type</t></is></c><c r=`"C1`" t=`"inlineStr`" s=`"1`"><is><t>URI (Full URL)</t></is></c><c r=`"D1`" t=`"inlineStr`" s=`"1`"><is><t>Request Payload</t></is></c><c r=`"E1`" t=`"inlineStr`" s=`"1`"><is><t>Response Payload (Success)</t></is></c><c r=`"F1`" t=`"inlineStr`" s=`"1`"><is><t>Status Code</t></is></c></row>"

    $dataRows = ""
    $rowNum = 2
    foreach ($ep in $endpoints) {
        $styleIdx = if ($ep.Method -eq "GET") { 2 } elseif ($ep.Method -eq "POST") { 3 } else { 4 }
        $dataRows += "<row r=`"$rowNum`" ht=`"100`" customHeight=`"1`">"
        $dataRows += "<c r=`"A$rowNum`" t=`"inlineStr`" s=`"$styleIdx`"><is><t>$(EscapeXml $ep.ID)</t></is></c>"
        $dataRows += "<c r=`"B$rowNum`" t=`"inlineStr`" s=`"$styleIdx`"><is><t>$(EscapeXml $ep.Method)</t></is></c>"
        $dataRows += "<c r=`"C$rowNum`" t=`"inlineStr`" s=`"$styleIdx`"><is><t>$(EscapeXml $ep.URL)</t></is></c>"
        $dataRows += "<c r=`"D$rowNum`" t=`"inlineStr`" s=`"$styleIdx`"><is><t>$(EscapeXml $ep.Req)</t></is></c>"
        $dataRows += "<c r=`"E$rowNum`" t=`"inlineStr`" s=`"$styleIdx`"><is><t>$(EscapeXml $ep.Res)</t></is></c>"
        $dataRows += "<c r=`"F$rowNum`" t=`"inlineStr`" s=`"$styleIdx`"><is><t>$(EscapeXml $ep.Code)</t></is></c>"
        $dataRows += "</row>"
        $rowNum++
    }

    $sheet1Xml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetFormatPr defaultRowHeight="15" customHeight="1"/>
  <cols>
    <col min="1" max="1" width="8"  customWidth="1"/>
    <col min="2" max="2" width="14" customWidth="1"/>
    <col min="3" max="3" width="55" customWidth="1"/>
    <col min="4" max="4" width="52" customWidth="1"/>
    <col min="5" max="5" width="62" customWidth="1"/>
    <col min="6" max="6" width="12" customWidth="1"/>
  </cols>
  <sheetData>
    $hdrRow
    $dataRows
  </sheetData>
</worksheet>
"@

    # ── Build Sheet 2 XML ──────────────────────────────────────────────
    $hdrRow2 = "<row r=`"1`" ht=`"40`" customHeight=`"1`"><c r=`"A1`" t=`"inlineStr`" s=`"5`"><is><t>TCID</t></is></c><c r=`"B1`" t=`"inlineStr`" s=`"5`"><is><t>Module</t></is></c><c r=`"C1`" t=`"inlineStr`" s=`"5`"><is><t>HTTP Method</t></is></c><c r=`"D1`" t=`"inlineStr`" s=`"5`"><is><t>URI</t></is></c><c r=`"E1`" t=`"inlineStr`" s=`"5`"><is><t>Error Scenario</t></is></c><c r=`"F1`" t=`"inlineStr`" s=`"5`"><is><t>Error Response Body</t></is></c><c r=`"G1`" t=`"inlineStr`" s=`"5`"><is><t>Status Code</t></is></c></row>"

    $errRows2 = ""
    $rn = 2
    foreach ($er in $errorData) {
        $errRows2 += "<row r=`"$rn`" ht=`"70`" customHeight=`"1`">"
        $errRows2 += "<c r=`"A$rn`" t=`"inlineStr`" s=`"6`"><is><t>$(EscapeXml $er.ID)</t></is></c>"
        $errRows2 += "<c r=`"B$rn`" t=`"inlineStr`" s=`"6`"><is><t>$(EscapeXml $er.Mod)</t></is></c>"
        $errRows2 += "<c r=`"C$rn`" t=`"inlineStr`" s=`"6`"><is><t>$(EscapeXml $er.Mth)</t></is></c>"
        $errRows2 += "<c r=`"D$rn`" t=`"inlineStr`" s=`"6`"><is><t>$(EscapeXml $er.URI)</t></is></c>"
        $errRows2 += "<c r=`"E$rn`" t=`"inlineStr`" s=`"6`"><is><t>$(EscapeXml $er.Scenario)</t></is></c>"
        $errRows2 += "<c r=`"F$rn`" t=`"inlineStr`" s=`"6`"><is><t>$(EscapeXml $er.Res)</t></is></c>"
        $errRows2 += "<c r=`"G$rn`" t=`"inlineStr`" s=`"6`"><is><t>$(EscapeXml $er.Code)</t></is></c>"
        $errRows2 += "</row>"
        $rn++
    }

    $sheet2Xml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetFormatPr defaultRowHeight="15" customHeight="1"/>
  <cols>
    <col min="1" max="1" width="6"  customWidth="1"/>
    <col min="2" max="2" width="14" customWidth="1"/>
    <col min="3" max="3" width="10" customWidth="1"/>
    <col min="4" max="4" width="35" customWidth="1"/>
    <col min="5" max="5" width="42" customWidth="1"/>
    <col min="6" max="6" width="55" customWidth="1"/>
    <col min="7" max="7" width="10" customWidth="1"/>
  </cols>
  <sheetData>
    $hdrRow2
    $errRows2
  </sheetData>
</worksheet>
"@

    # ── Sheet 3 - Legend ───────────────────────────────────────────────
    $sheet3Xml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetFormatPr defaultRowHeight="18"/>
  <cols>
    <col min="1" max="1" width="28" customWidth="1"/>
    <col min="2" max="2" width="55" customWidth="1"/>
    <col min="3" max="3" width="42" customWidth="1"/>
  </cols>
  <sheetData>
    <row r="1" ht="30" customHeight="1"><c r="A1" t="inlineStr" s="1"><is><t>Legend - Colour Key</t></is></c><c r="B1" t="inlineStr" s="1"><is><t>Meaning</t></is></c><c r="C1" t="inlineStr" s="1"><is><t>Description</t></is></c></row>
    <row r="2"><c r="A2" t="inlineStr"><is><t>GET Request (Green rows)</t></is></c><c r="B2" t="inlineStr"><is><t>GET</t></is></c><c r="C2" t="inlineStr"><is><t>Read-only endpoints - fetch or list data</t></is></c></row>
    <row r="3"><c r="A3" t="inlineStr"><is><t>POST Request (Blue rows)</t></is></c><c r="B3" t="inlineStr"><is><t>POST</t></is></c><c r="C3" t="inlineStr"><is><t>Create, submit, login, register actions</t></is></c></row>
    <row r="4"><c r="A4" t="inlineStr"><is><t>DELETE Request (Orange rows)</t></is></c><c r="B4" t="inlineStr"><is><t>DELETE</t></is></c><c r="C4" t="inlineStr"><is><t>Remove items from cart or address</t></is></c></row>
    <row r="5"><c r="A5" t="inlineStr"><is><t>200 OK</t></is></c><c r="B5" t="inlineStr"><is><t>Success</t></is></c><c r="C5" t="inlineStr"><is><t>Request succeeded</t></is></c></row>
    <row r="6"><c r="A6" t="inlineStr"><is><t>302 Found</t></is></c><c r="B6" t="inlineStr"><is><t>Redirect</t></is></c><c r="C6" t="inlineStr"><is><t>Redirect to another page (logout)</t></is></c></row>
    <row r="7"><c r="A7" t="inlineStr"><is><t>400 Bad Request</t></is></c><c r="B7" t="inlineStr"><is><t>Invalid input</t></is></c><c r="C7" t="inlineStr"><is><t>Missing or invalid required fields</t></is></c></row>
    <row r="8"><c r="A8" t="inlineStr"><is><t>401 Unauthorized</t></is></c><c r="B8" t="inlineStr"><is><t>Not logged in</t></is></c><c r="C8" t="inlineStr"><is><t>No valid session cookie (PHPSESSID)</t></is></c></row>
    <row r="9"><c r="A9" t="inlineStr"><is><t>403 Forbidden</t></is></c><c r="B9" t="inlineStr"><is><t>CSRF invalid</t></is></c><c r="C9" t="inlineStr"><is><t>CSRF token missing or does not match</t></is></c></row>
    <row r="10"><c r="A10" t="inlineStr"><is><t>404 Not Found</t></is></c><c r="B10" t="inlineStr"><is><t>Resource missing</t></is></c><c r="C10" t="inlineStr"><is><t>Item, doctor, or address not found in DB</t></is></c></row>
    <row r="11"><c r="A11" t="inlineStr"><is><t>409 Conflict</t></is></c><c r="B11" t="inlineStr"><is><t>Duplicate entry</t></is></c><c r="C11" t="inlineStr"><is><t>Duplicate email or appointment slot</t></is></c></row>
    <row r="12"><c r="A12" t="inlineStr"><is><t>429 Too Many Requests</t></is></c><c r="B12" t="inlineStr"><is><t>Rate limited</t></is></c><c r="C12" t="inlineStr"><is><t>5 failed login attempts in 10 minutes</t></is></c></row>
    <row r="13"><c r="A13" t="inlineStr"><is><t>500 Server Error</t></is></c><c r="B13" t="inlineStr"><is><t>Database/server crash</t></is></c><c r="C13" t="inlineStr"><is><t>Item out of stock at checkout</t></is></c></row>
    <row r="14"><c r="A14" t="inlineStr"><is><t> </t></is></c></row>
    <row r="15" ht="26" customHeight="1"><c r="A15" t="inlineStr" s="1"><is><t>SUMMARY STATISTICS</t></is></c><c r="B15" t="inlineStr" s="1"><is><t>Value</t></is></c></row>
    <row r="16"><c r="A16" t="inlineStr"><is><t>Total API Endpoints</t></is></c><c r="B16" t="inlineStr"><is><t>23</t></is></c></row>
    <row r="17"><c r="A17" t="inlineStr"><is><t>GET Endpoints</t></is></c><c r="B17" t="inlineStr"><is><t>8</t></is></c></row>
    <row r="18"><c r="A18" t="inlineStr"><is><t>POST Endpoints</t></is></c><c r="B18" t="inlineStr"><is><t>13</t></is></c></row>
    <row r="19"><c r="A19" t="inlineStr"><is><t>DELETE Endpoints</t></is></c><c r="B19" t="inlineStr"><is><t>2</t></is></c></row>
    <row r="20"><c r="A20" t="inlineStr"><is><t>PUT Endpoints</t></is></c><c r="B20" t="inlineStr"><is><t>0</t></is></c></row>
    <row r="21"><c r="A21" t="inlineStr"><is><t>Total Modules</t></is></c><c r="B21" t="inlineStr"><is><t>12</t></is></c></row>
    <row r="22"><c r="A22" t="inlineStr"><is><t>Total Database Tables</t></is></c><c r="B22" t="inlineStr"><is><t>12</t></is></c></row>
    <row r="23"><c r="A23" t="inlineStr"><is><t>Happy Path Test Cases (Sheet 1)</t></is></c><c r="B23" t="inlineStr"><is><t>29</t></is></c></row>
    <row r="24"><c r="A24" t="inlineStr"><is><t>Error Scenarios (Sheet 2)</t></is></c><c r="B24" t="inlineStr"><is><t>37</t></is></c></row>
    <row r="25"><c r="A25" t="inlineStr"><is><t>Authentication Method</t></is></c><c r="B25" t="inlineStr"><is><t>PHP Session (PHPSESSID Cookie)</t></is></c></row>
    <row r="26"><c r="A26" t="inlineStr"><is><t>CSRF Protection</t></is></c><c r="B26" t="inlineStr"><is><t>Yes - Required for all POST and DELETE</t></is></c></row>
    <row r="27"><c r="A27" t="inlineStr"><is><t>Data Format</t></is></c><c r="B27" t="inlineStr"><is><t>application/json</t></is></c></row>
    <row r="28"><c r="A28" t="inlineStr"><is><t>Base URL</t></is></c><c r="B28" t="inlineStr"><is><t>https://anvnacare.alwaysdata.net</t></is></c></row>
    <row r="29"><c r="A29" t="inlineStr"><is><t>Test User Email</t></is></c><c r="B29" t="inlineStr"><is><t>amit.kumar@anvnacare.com</t></is></c></row>
    <row r="30"><c r="A30" t="inlineStr"><is><t>Test User Password</t></is></c><c r="B30" t="inlineStr"><is><t>password123</t></is></c></row>
    <row r="31"><c r="A31" t="inlineStr"><is><t>Admin Email</t></is></c><c r="B31" t="inlineStr"><is><t>admin@anvnacare.com</t></is></c></row>
    <row r="32"><c r="A32" t="inlineStr"><is><t>Coupon Codes</t></is></c><c r="B32" t="inlineStr"><is><t>SAVE10, WELCOME, HEALTH20, DIAGNO50, FLAT200</t></is></c></row>
  </sheetData>
</worksheet>
"@

    # ── Styles XML (basic) ─────────────────────────────────────────────
    $stylesXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="3">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="12"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font>
    <font><b/><sz val="11"/><name val="Calibri"/></font>
  </fonts>
  <fills count="8">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1F7A4A"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFE2EFDA"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFDDEBF7"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFCE4D6"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFC00000"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFCE4D6"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border><left style="thin"><color auto="1"/></left><right style="thin"><color auto="1"/></right><top style="thin"><color auto="1"/></top><bottom style="thin"><color auto="1"/></bottom><diagonal/></border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="7">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment wrapText="1" vertical="top"/></xf>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1"><alignment wrapText="1" vertical="top"/></xf>
    <xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1"><alignment wrapText="1" vertical="top"/></xf>
    <xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFill="1"><alignment wrapText="1" vertical="top"/></xf>
    <xf numFmtId="0" fontId="1" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFill="1"><alignment wrapText="1" vertical="top"/></xf>
  </cellXfs>
</styleSheet>
"@

    # ── Workbook XML ───────────────────────────────────────────────────
    $workbookXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="All Endpoints"    sheetId="1" r:id="rId1"/>
    <sheet name="Error Responses"  sheetId="2" r:id="rId2"/>
    <sheet name="Legend Summary"   sheetId="3" r:id="rId3"/>
  </sheets>
</workbook>
"@

    $workbookRels = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>
  <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"    Target="styles.xml"/>
</Relationships>
"@

    $packageRels = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
"@

    $contentTypes = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"              ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml"     ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet2.xml"     ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet3.xml"     ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml"                ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
"@

    # ── Write the zip ──────────────────────────────────────────────────
    $enc = [System.Text.Encoding]::UTF8

    if (Test-Path $outputPath) { Remove-Item $outputPath -Force }

    $zip = [System.IO.Compression.ZipFile]::Open($outputPath, [System.IO.Compression.ZipArchiveMode]::Create)

    function AddEntry($zip, $name, $content) {
        $entry  = $zip.CreateEntry($name, [System.IO.Compression.CompressionLevel]::Optimal)
        $stream = $entry.Open()
        $bytes  = [System.Text.Encoding]::UTF8.GetBytes($content)
        $stream.Write($bytes, 0, $bytes.Length)
        $stream.Close()
    }

    AddEntry $zip "[Content_Types].xml"           $contentTypes
    AddEntry $zip "_rels/.rels"                   $packageRels
    AddEntry $zip "xl/workbook.xml"               $workbookXml
    AddEntry $zip "xl/_rels/workbook.xml.rels"    $workbookRels
    AddEntry $zip "xl/styles.xml"                 $stylesXml
    AddEntry $zip "xl/worksheets/sheet1.xml"      $sheet1Xml
    AddEntry $zip "xl/worksheets/sheet2.xml"      $sheet2Xml
    AddEntry $zip "xl/worksheets/sheet3.xml"      $sheet3Xml

    $zip.Dispose()

    if (Test-Path $outputPath) {
        $size = (Get-Item $outputPath).Length
        Write-Host ""
        Write-Host "SUCCESS: Excel file created!" -ForegroundColor Green
        Write-Host "Location : $outputPath" -ForegroundColor Yellow
        Write-Host "File size: $([math]::Round($size/1KB, 1)) KB" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "3 Sheets:" -ForegroundColor Cyan
        Write-Host "  1. All Endpoints   - 29 happy path rows (GET / POST / DELETE)"
        Write-Host "  2. Error Responses - 37 error scenarios (negative tests)"
        Write-Host "  3. Legend Summary  - colour key and API statistics"
    } else {
        Write-Host "ERROR: File was not saved." -ForegroundColor Red
    }
} else {
    Write-Host "DocumentFormat.OpenXml is available - using advanced mode."
}
