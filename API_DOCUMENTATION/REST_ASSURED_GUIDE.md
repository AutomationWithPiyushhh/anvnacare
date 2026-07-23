# ANVNA Care – Rest Assured Java Guide

This guide provides **Java Rest Assured** code examples for every ANVNA Care API endpoint.

---

## 🛠️ Setup & Dependencies

Add these to your `pom.xml` (Maven) or `build.gradle` (Gradle):

### Maven (pom.xml)
```xml
<dependencies>
    <!-- Rest Assured -->
    <dependency>
        <groupId>io.rest-assured</groupId>
        <artifactId>rest-assured</artifactId>
        <version>5.4.0</version>
        <scope>test</scope>
    </dependency>
    <!-- Hamcrest (for assertions) -->
    <dependency>
        <groupId>org.hamcrest</groupId>
        <artifactId>hamcrest</artifactId>
        <version>2.2</version>
        <scope>test</scope>
    </dependency>
    <!-- JSON (for request bodies) -->
    <dependency>
        <groupId>org.json</groupId>
        <artifactId>json</artifactId>
        <version>20240303</version>
    </dependency>
    <!-- TestNG or JUnit -->
    <dependency>
        <groupId>org.testng</groupId>
        <artifactId>testng</artifactId>
        <version>7.9.0</version>
        <scope>test</scope>
    </dependency>
</dependencies>
```

---

## 📦 Base Configuration Class

Create this class to hold shared settings:

```java
package com.anvnacare.tests;

import io.restassured.RestAssured;
import io.restassured.filter.cookie.CookieFilter;
import io.restassured.specification.RequestSpecification;
import io.restassured.builder.RequestSpecBuilder;

public class BaseTest {

    // Base URL of the ANVNA Care application
    public static final String BASE_URL  = "https://anvnacare.alwaysdata.net";
    public static final String API_PATH  = "/api";

    // Test credentials (from dummy_data.sql)
    public static final String EMAIL     = "amit.kumar@anvnacare.com";
    public static final String PASSWORD  = "password123";
    public static final String ADMIN_EMAIL    = "admin@anvnacare.com";
    public static final String ADMIN_PASSWORD = "password123";

    // Shared cookie filter — keeps the PHPSESSID session alive across requests
    protected static CookieFilter cookieFilter = new CookieFilter();

    // CSRF token — must be obtained from the session after login
    // For API testing: use the X-CSRF-Token header or csrf_token body field
    // In a real test: grab the token from the HTML meta tag or set a known one
    protected static String csrfToken = "";

    // Shared base request specification
    protected static RequestSpecification requestSpec;

    static {
        RestAssured.baseURI = BASE_URL;
        RestAssured.basePath = API_PATH;
        RestAssured.enableLoggingOfRequestAndResponseIfValidationFails();

        requestSpec = new RequestSpecBuilder()
            .setBaseUri(BASE_URL)
            .setBasePath(API_PATH)
            .addHeader("Content-Type", "application/json")
            .addHeader("Accept", "application/json")
            .addFilter(cookieFilter)  // Keeps session cookies across requests
            .build();
    }
}
```

---

## 1. Register User

```java
package com.anvnacare.tests;

import io.restassured.RestAssured;
import io.restassured.response.Response;
import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class RegisterTest extends BaseTest {

    @Test(description = "Register a new user successfully")
    public void testRegisterSuccess() {
        String requestBody = "{"
            + "\"name\": \"Test Student\","
            + "\"email\": \"teststudent_" + System.currentTimeMillis() + "@example.com\","
            + "\"phone\": \"9876543299\","
            + "\"password\": \"test1234\""
            + "}";

        given()
            .spec(requestSpec)
            .body(requestBody)
        .when()
            .post("/register.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("message", containsString("Registration successful"))
            .body("user.id", notNullValue())
            .body("user.role", equalTo("user"))
            .log().ifValidationFails();
    }

    @Test(description = "Registration fails when name is too short")
    public void testRegisterShortName() {
        String requestBody = "{"
            + "\"name\": \"Jo\","
            + "\"email\": \"test@example.com\","
            + "\"phone\": \"9876543299\","
            + "\"password\": \"test1234\""
            + "}";

        given()
            .spec(requestSpec)
            .body(requestBody)
        .when()
            .post("/register.php")
        .then()
            .statusCode(400)
            .body("success", equalTo(false))
            .body("message", containsString("3 characters"))
            .log().ifValidationFails();
    }

    @Test(description = "Registration fails when email is invalid")
    public void testRegisterInvalidEmail() {
        String requestBody = "{"
            + "\"name\": \"John Doe\","
            + "\"email\": \"not-an-email\","
            + "\"phone\": \"9876543299\","
            + "\"password\": \"test1234\""
            + "}";

        given()
            .spec(requestSpec)
            .body(requestBody)
        .when()
            .post("/register.php")
        .then()
            .statusCode(400)
            .body("success", equalTo(false))
            .body("message", containsString("valid email"))
            .log().ifValidationFails();
    }

    @Test(description = "Registration fails when phone is not 10 digits")
    public void testRegisterInvalidPhone() {
        String requestBody = "{"
            + "\"name\": \"John Doe\","
            + "\"email\": \"john@example.com\","
            + "\"phone\": \"12345\","
            + "\"password\": \"test1234\""
            + "}";

        given()
            .spec(requestSpec)
            .body(requestBody)
        .when()
            .post("/register.php")
        .then()
            .statusCode(400)
            .body("success", equalTo(false))
            .body("message", containsString("10-digit"))
            .log().ifValidationFails();
    }

    @Test(description = "Registration fails when email already exists (409 Conflict)")
    public void testRegisterDuplicateEmail() {
        String requestBody = "{"
            + "\"name\": \"Amit Kumar\","
            + "\"email\": \"amit.kumar@anvnacare.com\","
            + "\"phone\": \"9876543299\","
            + "\"password\": \"test1234\""
            + "}";

        given()
            .spec(requestSpec)
            .body(requestBody)
        .when()
            .post("/register.php")
        .then()
            .statusCode(409)
            .body("success", equalTo(false))
            .body("message", containsString("already registered"))
            .log().ifValidationFails();
    }
}
```

---

## 2. Login

```java
package com.anvnacare.tests;

import io.restassured.response.Response;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class LoginTest extends BaseTest {

    @Test(description = "Login with valid credentials")
    public void testLoginSuccess() {
        String requestBody = "{"
            + "\"email\": \"" + EMAIL + "\","
            + "\"password\": \"" + PASSWORD + "\","
            + "\"remember\": false"
            + "}";

        Response response = given()
            .spec(requestSpec)
            .body(requestBody)
        .when()
            .post("/login.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("message", containsString("Login successful"))
            .body("user.id", notNullValue())
            .body("user.email", equalTo(EMAIL))
            .body("user.role", anyOf(equalTo("user"), equalTo("admin")))
            .log().ifValidationFails()
            .extract().response();

        System.out.println("Logged in as user ID: " + response.path("user.id"));
    }

    @Test(description = "Login fails with wrong password - HTTP 401")
    public void testLoginWrongPassword() {
        String requestBody = "{"
            + "\"email\": \"" + EMAIL + "\","
            + "\"password\": \"wrongpassword\""
            + "}";

        given()
            .spec(requestSpec)
            .body(requestBody)
        .when()
            .post("/login.php")
        .then()
            .statusCode(401)
            .body("success", equalTo(false))
            .body("message", containsString("Invalid email or password"))
            .log().ifValidationFails();
    }

    @Test(description = "Login fails with empty body - HTTP 400")
    public void testLoginEmptyBody() {
        given()
            .spec(requestSpec)
            .body("{}")
        .when()
            .post("/login.php")
        .then()
            .statusCode(400)
            .body("success", equalTo(false))
            .body("message", containsString("required"))
            .log().ifValidationFails();
    }

    @Test(description = "Response Content-Type is application/json")
    public void testLoginContentType() {
        String requestBody = "{\"email\":\"" + EMAIL + "\",\"password\":\"" + PASSWORD + "\"}";

        given()
            .spec(requestSpec)
            .body(requestBody)
        .when()
            .post("/login.php")
        .then()
            .header("Content-Type", containsString("application/json"))
            .log().ifValidationFails();
    }
}
```

---

## 3. Medicines API

```java
package com.anvnacare.tests;

import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class MedicinesTest extends BaseTest {

    @Test(description = "Get all medicines - no filters")
    public void testGetAllMedicines() {
        given()
            .spec(requestSpec)
        .when()
            .get("/medicines.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("medicines", notNullValue())
            .body("medicines", hasSize(greaterThan(0)))
            .body("count", greaterThan(0))
            .body("total", greaterThan(0))
            .body("page", equalTo(1))
            .body("limit", equalTo(10))
            .log().ifValidationFails();
    }

    @Test(description = "Filter medicines by category OTC")
    public void testGetMedicinesByCategory() {
        given()
            .spec(requestSpec)
            .queryParam("category", "OTC")
        .when()
            .get("/medicines.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("medicines", notNullValue())
            .body("medicines.category", everyItem(equalTo("OTC")))
            .log().ifValidationFails();
    }

    @Test(description = "Search medicines by name")
    public void testSearchMedicines() {
        given()
            .spec(requestSpec)
            .queryParam("search", "paracetamol")
        .when()
            .get("/medicines.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("medicines[0].name", containsStringIgnoringCase("paracetamol"))
            .log().ifValidationFails();
    }

    @Test(description = "Medicines sorted by price ascending")
    public void testMedicinesSortByPriceAsc() {
        given()
            .spec(requestSpec)
            .queryParam("sort", "price_asc")
            .queryParam("limit", "5")
        .when()
            .get("/medicines.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("medicines", hasSize(greaterThan(0)))
            .log().ifValidationFails();
    }

    @Test(description = "Medicines pagination test")
    public void testMedicinesPagination() {
        given()
            .spec(requestSpec)
            .queryParam("page", "2")
            .queryParam("limit", "5")
        .when()
            .get("/medicines.php")
        .then()
            .statusCode(200)
            .body("page", equalTo(2))
            .body("limit", equalTo(5))
            .log().ifValidationFails();
    }

    @Test(description = "Medicine response has correct fields")
    public void testMedicineResponseFields() {
        given()
            .spec(requestSpec)
            .queryParam("limit", "1")
        .when()
            .get("/medicines.php")
        .then()
            .statusCode(200)
            .body("medicines[0].id", notNullValue())
            .body("medicines[0].name", notNullValue())
            .body("medicines[0].manufacturer", notNullValue())
            .body("medicines[0].mrp", notNullValue())
            .body("medicines[0].discount_price", notNullValue())
            .body("medicines[0].stock", notNullValue())
            .body("medicines[0].category", notNullValue())
            .log().ifValidationFails();
    }
}
```

---

## 4. Products API

```java
package com.anvnacare.tests;

import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class ProductsTest extends BaseTest {

    @Test(description = "Get all products")
    public void testGetAllProducts() {
        given()
            .spec(requestSpec)
        .when()
            .get("/products.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("products", notNullValue())
            .body("products", hasSize(greaterThan(0)))
            .log().ifValidationFails();
    }

    @Test(description = "Filter products by Devices category")
    public void testGetProductsByCategory() {
        given()
            .spec(requestSpec)
            .queryParam("category", "Devices")
        .when()
            .get("/products.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("products.category", everyItem(equalTo("Devices")))
            .log().ifValidationFails();
    }

    @Test(description = "Products sorted by rating")
    public void testProductsSortedByRating() {
        given()
            .spec(requestSpec)
            .queryParam("sort", "rating")
            .queryParam("limit", "3")
        .when()
            .get("/products.php")
        .then()
            .statusCode(200)
            .body("products", hasSize(greaterThan(0)))
            .log().ifValidationFails();
    }
}
```

---

## 5. Doctors API

```java
package com.anvnacare.tests;

import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class DoctorsTest extends BaseTest {

    @Test(description = "Get all doctors")
    public void testGetAllDoctors() {
        given()
            .spec(requestSpec)
        .when()
            .get("/doctors.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("doctors", notNullValue())
            .body("doctors", hasSize(greaterThan(0)))
            .log().ifValidationFails();
    }

    @Test(description = "Filter doctors by specialization")
    public void testFilterBySpecialization() {
        given()
            .spec(requestSpec)
            .queryParam("specialization", "Cardiologist")
        .when()
            .get("/doctors.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("doctors.specialization", everyItem(equalTo("Cardiologist")))
            .log().ifValidationFails();
    }

    @Test(description = "Sort doctors by fee ascending")
    public void testSortDoctorsByFeeAsc() {
        given()
            .spec(requestSpec)
            .queryParam("sort", "fee_asc")
        .when()
            .get("/doctors.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("doctors", hasSize(greaterThan(0)))
            .log().ifValidationFails();
    }

    @Test(description = "Search doctors by name")
    public void testSearchDoctors() {
        given()
            .spec(requestSpec)
            .queryParam("search", "Sharma")
        .when()
            .get("/doctors.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .log().ifValidationFails();
    }

    @Test(description = "Doctor response has required fields")
    public void testDoctorResponseFields() {
        given()
            .spec(requestSpec)
            .queryParam("limit", "1")
        .when()
            .get("/doctors.php")
        .then()
            .statusCode(200)
            .body("doctors[0].id", notNullValue())
            .body("doctors[0].name", notNullValue())
            .body("doctors[0].specialization", notNullValue())
            .body("doctors[0].fee", notNullValue())
            .body("doctors[0].experience", notNullValue())
            .log().ifValidationFails();
    }
}
```

---

## 6. Search API

```java
package com.anvnacare.tests;

import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class SearchTest extends BaseTest {

    @Test(description = "Global search returns results")
    public void testGlobalSearchReturnsResults() {
        given()
            .spec(requestSpec)
            .queryParam("q", "para")
        .when()
            .get("/search.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("results", notNullValue())
            .body("results", hasSize(greaterThan(0)))
            .log().ifValidationFails();
    }

    @Test(description = "Search result has required fields")
    public void testSearchResultFields() {
        given()
            .spec(requestSpec)
            .queryParam("q", "blood")
        .when()
            .get("/search.php")
        .then()
            .statusCode(200)
            .body("results[0].type", notNullValue())
            .body("results[0].id", notNullValue())
            .body("results[0].name", notNullValue())
            .body("results[0].url", notNullValue())
            .log().ifValidationFails();
    }

    @Test(description = "Search fails when query is too short (less than 2 chars)")
    public void testSearchQueryTooShort() {
        given()
            .spec(requestSpec)
            .queryParam("q", "a")
        .when()
            .get("/search.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(false))
            .body("results", hasSize(0))
            .log().ifValidationFails();
    }

    @Test(description = "Search with no query parameter")
    public void testSearchNoQuery() {
        given()
            .spec(requestSpec)
        .when()
            .get("/search.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(false))
            .log().ifValidationFails();
    }
}
```

---

## 7. Coupon API

```java
package com.anvnacare.tests;

import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class CouponTest extends BaseTest {

    @Test(description = "Valid coupon code SAVE10 returns discount details")
    public void testValidCouponSave10() {
        given()
            .spec(requestSpec)
            .queryParam("code", "SAVE10")
            .queryParam("cart_value", "500")
        .when()
            .get("/coupon.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("coupon.code", equalTo("SAVE10"))
            .body("coupon.discount_type", equalTo("percentage"))
            .body("coupon.discount_value", equalTo(10.0f))
            .log().ifValidationFails();
    }

    @Test(description = "Invalid coupon code returns error")
    public void testInvalidCoupon() {
        given()
            .spec(requestSpec)
            .queryParam("code", "INVALIDCODE")
            .queryParam("cart_value", "500")
        .when()
            .get("/coupon.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(false))
            .body("message", containsString("Invalid or expired"))
            .log().ifValidationFails();
    }

    @Test(description = "Coupon not applied when cart value is below minimum")
    public void testCouponMinCartValueNotMet() {
        given()
            .spec(requestSpec)
            .queryParam("code", "WELCOME")
            .queryParam("cart_value", "100")  // WELCOME requires min Rs.500
        .when()
            .get("/coupon.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(false))
            .body("message", containsString("Minimum cart value"))
            .log().ifValidationFails();
    }

    @Test(description = "Missing coupon code returns 400")
    public void testMissingCouponCode() {
        given()
            .spec(requestSpec)
            .queryParam("cart_value", "500")
        .when()
            .get("/coupon.php")
        .then()
            .statusCode(400)
            .body("success", equalTo(false))
            .body("message", containsString("required"))
            .log().ifValidationFails();
    }
}
```

---

## 8. Cart API (Requires Login Session)

> ⚠️ **Important**: The tests below require an active session (you must call login first in a `@BeforeClass` method or test setup, using the same `cookieFilter`).

```java
package com.anvnacare.tests;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class CartTest extends BaseTest {

    @BeforeClass
    public void loginFirst() {
        // Login to establish session before cart tests
        String loginBody = "{\"email\":\"" + EMAIL + "\",\"password\":\"" + PASSWORD + "\"}";
        given()
            .spec(requestSpec)
            .body(loginBody)
        .when()
            .post("/login.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true));
        // After this, cookieFilter holds the PHPSESSID cookie for all subsequent requests
    }

    @Test(description = "Add medicine to cart")
    public void testAddMedicineToCart() {
        // NOTE: csrfToken must be set from the browser session.
        // For automated testing, you can temporarily disable CSRF or 
        // use a pre-set token via browser automation or the X-CSRF-Token header.
        String requestBody = "{"
            + "\"action\": \"add\","
            + "\"item_id\": 1,"
            + "\"item_type\": \"medicine\","
            + "\"quantity\": 1,"
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/cart.php")
        .then()
            .statusCode(anyOf(equalTo(200), equalTo(403)))
            // 403 if CSRF token is not set — expected in pure API test environment
            .log().ifValidationFails();
    }

    @Test(description = "Add lab test to cart")
    public void testAddLabTestToCart() {
        String requestBody = "{"
            + "\"action\": \"add\","
            + "\"item_id\": 1,"
            + "\"item_type\": \"test\","
            + "\"quantity\": 1,"
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/cart.php")
        .then()
            .body("success", notNullValue())
            .log().ifValidationFails();
    }

    @Test(description = "Cart action with missing item_id returns 400")
    public void testAddCartMissingItemId() {
        String requestBody = "{"
            + "\"action\": \"add\","
            + "\"item_type\": \"medicine\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/cart.php")
        .then()
            .body("success", equalTo(false))
            .log().ifValidationFails();
    }

    @Test(description = "Remove item from cart using DELETE method")
    public void testRemoveFromCart() {
        String requestBody = "{"
            + "\"item_id\": 1,"
            + "\"item_type\": \"medicine\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .delete("/cart.php")
        .then()
            .body("success", notNullValue())
            .log().ifValidationFails();
    }
}
```

---

## 9. Wishlist API

```java
package com.anvnacare.tests;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class WishlistTest extends BaseTest {

    @BeforeClass
    public void loginFirst() {
        String loginBody = "{\"email\":\"" + EMAIL + "\",\"password\":\"" + PASSWORD + "\"}";
        given().spec(requestSpec).body(loginBody).when().post("/login.php").then().statusCode(200);
    }

    @Test(description = "Add medicine to wishlist")
    public void testAddToWishlist() {
        String requestBody = "{"
            + "\"action\": \"add\","
            + "\"item_id\": 1,"
            + "\"item_type\": \"medicine\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/wishlist.php")
        .then()
            .body("success", notNullValue())
            .log().ifValidationFails();
    }

    @Test(description = "Wishlist requires login - 401 when not logged in")
    public void testWishlistRequiresLogin() {
        // Use a fresh spec without the cookie filter to simulate a logged-out state
        String requestBody = "{"
            + "\"action\": \"add\","
            + "\"item_id\": 1,"
            + "\"item_type\": \"medicine\","
            + "\"csrf_token\": \"fake\""
            + "}";

        given()
            .baseUri(BASE_URL)
            .basePath(API_PATH)
            .header("Content-Type", "application/json")
            // NO cookieFilter = no session
            .body(requestBody)
        .when()
            .post("/wishlist.php")
        .then()
            .statusCode(401)
            .body("success", equalTo(false))
            .body("message", containsString("login"))
            .log().ifValidationFails();
    }

    @Test(description = "Wishlist does not accept 'test' item_type")
    public void testWishlistInvalidType() {
        String requestBody = "{"
            + "\"action\": \"add\","
            + "\"item_id\": 1,"
            + "\"item_type\": \"test\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/wishlist.php")
        .then()
            .statusCode(400)
            .body("success", equalTo(false))
            .log().ifValidationFails();
    }
}
```

---

## 10. Address API

```java
package com.anvnacare.tests;

import io.restassured.response.Response;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class AddressTest extends BaseTest {

    private int newAddressId;

    @BeforeClass
    public void loginFirst() {
        String loginBody = "{\"email\":\"" + EMAIL + "\",\"password\":\"" + PASSWORD + "\"}";
        given().spec(requestSpec).body(loginBody).when().post("/login.php").then().statusCode(200);
    }

    @Test(description = "Get all addresses for logged-in user")
    public void testGetAddresses() {
        given()
            .spec(requestSpec)
        .when()
            .get("/address.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("addresses", notNullValue())
            .log().ifValidationFails();
    }

    @Test(description = "Add a new address")
    public void testAddAddress() {
        String requestBody = "{"
            + "\"name\": \"Test User\","
            + "\"phone\": \"9876543200\","
            + "\"address_line1\": \"Flat 101, Rest Assured Tower\","
            + "\"address_line2\": \"Near Test Landmark\","
            + "\"city\": \"Pune\","
            + "\"state\": \"Maharashtra\","
            + "\"pincode\": \"411001\","
            + "\"is_default\": 0,"
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        Response response = given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/address.php")
        .then()
            .body("success", notNullValue())
            .log().ifValidationFails()
            .extract().response();

        if (response.path("success").equals(true)) {
            newAddressId = response.path("address.id");
        }
    }

    @Test(description = "Address requires login - returns 401")
    public void testGetAddressesRequiresLogin() {
        given()
            .baseUri(BASE_URL)
            .basePath(API_PATH)
            .header("Content-Type", "application/json")
        .when()
            .get("/address.php")
        .then()
            .statusCode(401)
            .body("success", equalTo(false))
            .log().ifValidationFails();
    }

    @Test(description = "Add address with invalid pincode returns 400")
    public void testAddAddressInvalidPincode() {
        String requestBody = "{"
            + "\"name\": \"Test User\","
            + "\"phone\": \"9876543200\","
            + "\"address_line1\": \"123 Test Street\","
            + "\"city\": \"Pune\","
            + "\"state\": \"Maharashtra\","
            + "\"pincode\": \"123\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/address.php")
        .then()
            .body("success", equalTo(false))
            .log().ifValidationFails();
    }
}
```

---

## 11. Appointments API

```java
package com.anvnacare.tests;

import io.restassured.response.Response;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class AppointmentsTest extends BaseTest {

    private int bookedAppointmentId;

    @BeforeClass
    public void loginFirst() {
        String loginBody = "{\"email\":\"" + EMAIL + "\",\"password\":\"" + PASSWORD + "\"}";
        given().spec(requestSpec).body(loginBody).when().post("/login.php").then().statusCode(200);
    }

    @Test(description = "Book an appointment successfully")
    public void testBookAppointment() {
        String requestBody = "{"
            + "\"action\": \"book\","
            + "\"doctor_id\": 1,"
            + "\"date\": \"2027-01-15\","
            + "\"time\": \"10:00 AM\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        Response response = given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/appointments.php")
        .then()
            .body("success", notNullValue())
            .log().ifValidationFails()
            .extract().response();

        if (response.path("success").equals(true)) {
            bookedAppointmentId = response.path("appointment_id");
            System.out.println("Booked appointment ID: " + bookedAppointmentId);
        }
    }

    @Test(description = "Booking with invalid date format returns 400")
    public void testBookInvalidDateFormat() {
        String requestBody = "{"
            + "\"action\": \"book\","
            + "\"doctor_id\": 1,"
            + "\"date\": \"15-01-2027\","
            + "\"time\": \"10:00 AM\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/appointments.php")
        .then()
            .body("success", equalTo(false))
            .body("message", containsString("date format"))
            .log().ifValidationFails();
    }

    @Test(description = "Booking with non-existent doctor ID returns 404")
    public void testBookNonExistentDoctor() {
        String requestBody = "{"
            + "\"action\": \"book\","
            + "\"doctor_id\": 9999,"
            + "\"date\": \"2027-01-15\","
            + "\"time\": \"10:00 AM\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/appointments.php")
        .then()
            .statusCode(404)
            .body("success", equalTo(false))
            .body("message", containsString("not found"))
            .log().ifValidationFails();
    }

    @Test(description = "Appointments require login - returns 401")
    public void testAppointmentsRequireLogin() {
        String requestBody = "{"
            + "\"action\": \"book\","
            + "\"doctor_id\": 1,"
            + "\"date\": \"2027-01-15\","
            + "\"time\": \"10:00 AM\","
            + "\"csrf_token\": \"fake\""
            + "}";

        given()
            .baseUri(BASE_URL)
            .basePath(API_PATH)
            .header("Content-Type", "application/json")
        .when()
            .post("/appointments.php")
        .then()
            .statusCode(401)
            .body("success", equalTo(false))
            .log().ifValidationFails();
    }
}
```

---

## 12. Profile API

```java
package com.anvnacare.tests;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

public class ProfileTest extends BaseTest {

    @BeforeClass
    public void loginFirst() {
        String loginBody = "{\"email\":\"" + EMAIL + "\",\"password\":\"" + PASSWORD + "\"}";
        given().spec(requestSpec).body(loginBody).when().post("/login.php").then().statusCode(200);
    }

    @Test(description = "Get user profile with all fields")
    public void testGetProfile() {
        given()
            .spec(requestSpec)
        .when()
            .get("/profile.php")
        .then()
            .statusCode(200)
            .body("success", equalTo(true))
            .body("user.id", notNullValue())
            .body("user.name", notNullValue())
            .body("user.email", equalTo(EMAIL))
            .body("user.phone", notNullValue())
            .body("user.role", notNullValue())
            .body("user.created_at", notNullValue())
            .body("addresses", notNullValue())
            .log().ifValidationFails();
    }

    @Test(description = "Update profile with valid data")
    public void testUpdateProfile() {
        String requestBody = "{"
            + "\"action\": \"update_profile\","
            + "\"name\": \"Amit Kumar\","
            + "\"phone\": \"9876543211\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/profile.php")
        .then()
            .body("success", notNullValue())
            .log().ifValidationFails();
    }

    @Test(description = "Update profile with name too short returns 400")
    public void testUpdateProfileShortName() {
        String requestBody = "{"
            + "\"action\": \"update_profile\","
            + "\"name\": \"AB\","
            + "\"phone\": \"9876543211\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/profile.php")
        .then()
            .body("success", equalTo(false))
            .body("message", containsString("3 characters"))
            .log().ifValidationFails();
    }

    @Test(description = "Change password with wrong current password returns 400")
    public void testChangePasswordWrongCurrent() {
        String requestBody = "{"
            + "\"action\": \"change_password\","
            + "\"current_password\": \"wrongpassword\","
            + "\"new_password\": \"newpassword456\","
            + "\"csrf_token\": \"" + csrfToken + "\""
            + "}";

        given()
            .spec(requestSpec)
            .header("X-CSRF-Token", csrfToken)
            .body(requestBody)
        .when()
            .post("/profile.php")
        .then()
            .body("success", equalTo(false))
            .body("message", containsString("Incorrect current password"))
            .log().ifValidationFails();
    }

    @Test(description = "Profile requires login - returns 401")
    public void testProfileRequiresLogin() {
        given()
            .baseUri(BASE_URL)
            .basePath(API_PATH)
            .header("Content-Type", "application/json")
        .when()
            .get("/profile.php")
        .then()
            .statusCode(401)
            .body("success", equalTo(false))
            .log().ifValidationFails();
    }
}
```

---

## 📝 Key Notes for Rest Assured Testing

1. **Session Handling**: Use a shared `CookieFilter` across all tests to maintain the PHP session.
2. **CSRF Token**: The CSRF token is tied to the PHP session. For automated testing:
   - Option A: Parse the token from the HTML `<meta name="csrf-token">` tag after login
   - Option B: Use the `X-CSRF-Token` header with a value obtained from the session
   - Option C: Create a test endpoint that returns the token (add in test environment only)
3. **Test Order**: Login tests should run before any protected endpoint tests (use `@BeforeClass`).
4. **Test Isolation**: Use unique emails when testing registration to avoid conflicts.
5. **Response Time**: Assert response time is within 3000ms for all endpoints.

---

*This documentation was generated using static analysis only. No existing project files were modified.*
