-- ANVNA Care Seed Data
-- Designed for MySQL/MariaDB

-- USE anvna_care;

-- Clean existing data
DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM appointments;
DELETE FROM cart;
DELETE FROM wishlist;
DELETE FROM addresses;
DELETE FROM coupons;
DELETE FROM users;
DELETE FROM medicines;
DELETE FROM products;
DELETE FROM doctors;
DELETE FROM tests;

-- 1. Insert Users (Password for all is 'password123' hashed with bcrypt)
-- Hash: $2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle
INSERT INTO users (id, name, email, phone, password, role) VALUES
(1, 'System Admin', 'admin@anvnacare.com', '9876543210', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'admin'),
(2, 'Amit Kumar', 'amit.kumar@anvnacare.com', '9876543211', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(3, 'Priya Sharma', 'priya.sharma@anvnacare.com', '9876543212', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(4, 'Rohan Verma', 'rohan.verma@anvnacare.com', '9876543213', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(5, 'Sneha Patel', 'sneha.patel@anvnacare.com', '9876543214', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(6, 'Vikram Singh', 'vikram.singh@anvnacare.com', '9876543215', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(7, 'Ananya Sen', 'ananya.sen@anvnacare.com', '9876543216', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(8, 'Deepak Rao', 'deepak.rao@anvnacare.com', '9876543217', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(9, 'Kiran Joshi', 'kiran.joshi@anvnacare.com', '9876543218', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(10, 'Neha Gupta', 'neha.gupta@anvnacare.com', '9876543219', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(11, 'Sanjay Nair', 'sanjay.nair@anvnacare.com', '9876543220', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(12, 'Meera Deshmukh', 'meera.d@anvnacare.com', '9876543221', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(13, 'Rajesh Varma', 'rajesh.v@anvnacare.com', '9876543222', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(14, 'Shweta Mishra', 'shweta.m@anvnacare.com', '9876543223', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user'),
(15, 'Rahul Bose', 'rahul.bose@anvnacare.com', '9876543224', '$2y$10$hER4zMrSLBwi979c9lBYg.6BTVvXG6aiPB7wBxX3gkfaodnGxgvle', 'user');

-- 2. Insert Medicines (20 items)
INSERT INTO medicines (id, name, manufacturer, mrp, discount_price, rating, stock, image, category, description) VALUES
(1, 'Paracetamol 650mg (Crocin)', 'GlaxoSmithKline', 30.00, 24.00, 4.5, 120, 'assets/images/medicines/crocin.png', 'OTC', 'Effective painkiller and fever reducer. Take 1 tablet every 6 hours as needed.'),
(2, 'Ibuprofen 400mg (Brufen)', 'Abbott Healthcare', 45.00, 36.00, 4.3, 85, 'assets/images/medicines/brufen.png', 'OTC', 'Non-steroidal anti-inflammatory drug (NSAID) used to relieve pain, swelling, and fever.'),
(3, 'Amoxicillin 500mg (Novamox)', 'Cipla Ltd', 110.00, 90.00, 4.6, 60, 'assets/images/medicines/novamox.png', 'Prescription', 'Broad-spectrum penicillin antibiotic used to treat various bacterial infections.'),
(4, 'Cetirizine 10mg (Alerid)', 'Cipla Ltd', 35.00, 28.00, 4.2, 150, 'assets/images/medicines/alerid.png', 'OTC', 'Antihistamine that treats symptoms of allergies such as runny nose, watery eyes, and sneezing.'),
(5, 'Metformin 500mg (Glycomet)', 'USV Private Ltd', 60.00, 52.00, 4.7, 200, 'assets/images/medicines/glycomet.png', 'Prescription', 'Oral diabetes medicine that helps control blood sugar levels for type 2 diabetes patients.'),
(6, 'Atorvastatin 10mg (Lipvas)', 'Cipla Ltd', 120.00, 98.00, 4.5, 90, 'assets/images/medicines/lipvas.png', 'Prescription', 'Statin medication used to prevent cardiovascular disease and lower cholesterol lipids.'),
(7, 'Pantoprazole 40mg (Pan-40)', 'Alkem Laboratories', 150.00, 125.00, 4.6, 110, 'assets/images/medicines/pan40.png', 'Prescription', 'Proton pump inhibitor (PPI) that decreases the amount of acid produced in the stomach.'),
(8, 'Azithromycin 500mg (Azee)', 'Cipla Ltd', 119.00, 99.00, 4.4, 75, 'assets/images/medicines/azee.png', 'Prescription', 'Macrolide-type antibiotic used for treating ear, throat, and skin bacterial infections.'),
(9, 'Montelukast 10mg (Montair)', 'Cipla Ltd', 180.00, 149.00, 4.5, 80, 'assets/images/medicines/montair.png', 'Prescription', 'Leukotriene receptor antagonist used for maintenance treatment of asthma and allergies.'),
(10, 'Amlodipine 5mg (Amlokind)', 'Mankind Pharma', 28.00, 22.00, 4.3, 140, 'assets/images/medicines/amlokind.png', 'Prescription', 'Calcium channel blocker used to treat high blood pressure and chest pain (angina).'),
(11, 'Losartan 50mg (Covance)', 'Sun Pharma', 85.00, 72.00, 4.4, 95, 'assets/images/medicines/covance.png', 'Prescription', 'Angiotensin II receptor antagonist used to treat hypertension (high blood pressure).'),
(12, 'Omeprazole 20mg (Omez)', 'Dr. Reddys Laboratories', 55.00, 44.00, 4.5, 130, 'assets/images/medicines/omez.png', 'OTC', 'Helps relieve symptoms of acid reflux, heartburn, and gastroesophageal reflux disease (GERD).'),
(13, 'Vitamin D3 60K UI (Calcirol)', 'Cadila Pharmaceuticals', 140.00, 115.00, 4.8, 100, 'assets/images/medicines/calcirol.png', 'Vitamins', 'High-potency Vitamin D supplement for bone health, muscle function, and immunity.'),
(14, 'Vitamin C 500mg (Limcee)', 'Abbott Healthcare', 40.00, 32.00, 4.7, 300, 'assets/images/medicines/limcee.png', 'Vitamins', 'Orange-flavored chewable Vitamin C tablet for cell protection and boosting overall immunity.'),
(15, 'Zincovit Tablets', 'Apex Laboratories', 110.00, 92.00, 4.6, 250, 'assets/images/medicines/zincovit.png', 'Vitamins', 'Advanced multivitamin and multimineral tablet enriched with zinc for nutritional support.'),
(16, 'B-Complex (Becosules)', 'Pfizer India', 50.00, 41.00, 4.5, 180, 'assets/images/medicines/becosules.png', 'Vitamins', 'B-complex vitamin capsule with Vitamin C to treat vitamin deficiencies and mouth ulcers.'),
(17, 'Cough Syrup (Benadryl)', 'Johnson & Johnson', 115.00, 99.00, 4.2, 85, 'assets/images/medicines/benadryl.png', 'OTC', 'Soothes throat irritation and relieves cough, runny nose, and sneezing due to colds.'),
(18, 'Antiseptic Liquid (Dettol)', 'Reckitt Benckiser', 84.00, 75.00, 4.8, 160, 'assets/images/medicines/dettol.png', 'OTC', 'First aid antiseptic liquid for cleaning wounds, cuts, and sanitizing surfaces.'),
(19, 'Aspirin 75mg (Ecosprin)', 'USV Private Ltd', 15.00, 12.00, 4.4, 220, 'assets/images/medicines/ecosprin.png', 'Prescription', 'Antiplatelet blood thinner used to prevent heart attacks, strokes, and blood clots.'),
(20, 'Ranitidine 150mg (Rantac)', 'J.B. Chemicals', 42.00, 34.00, 4.3, 170, 'assets/images/medicines/rantac.png', 'OTC', 'Reduces stomach acid production to treat acid indigestion and prevent ulcers.');

-- 3. Insert Products (Health Store - 20 items)
INSERT INTO products (id, name, mrp, discount_price, rating, stock, image, category, description) VALUES
(1, 'Whey Protein Powder 1kg', 3200.00, 2699.00, 4.6, 45, 'assets/images/products/whey.png', 'Supplements', 'Premium ultra-filtered whey protein concentrate for muscle recovery and fitness.'),
(2, 'Digital Thermometer', 250.00, 180.00, 4.4, 150, 'assets/images/products/thermometer.png', 'Devices', 'High precision digital thermometer for oral, underarm, and rectal temperature measurement.'),
(3, 'Blood Pressure Monitor (Omron)', 2800.00, 2249.00, 4.7, 60, 'assets/images/products/bp_monitor.png', 'Devices', 'Fully automatic upper-arm blood pressure monitor with Intellisense technology.'),
(4, 'Pulse Oximeter', 999.00, 699.00, 4.5, 90, 'assets/images/products/oximeter.png', 'Devices', 'Portable fingertip pulse oximeter for measuring blood oxygen saturation levels and pulse rate.'),
(5, 'N95 Face Mask (Pack of 5)', 450.00, 299.00, 4.3, 200, 'assets/images/products/mask.png', 'Wellness', '5-layered filtration mask providing advanced particulate respiratory protection.'),
(6, 'Hand Sanitizer 500ml', 250.00, 199.00, 4.6, 180, 'assets/images/products/sanitizer.png', 'Wellness', 'Alcohol-based hand sanitizer gel that kills 99.9% of disease-causing germs instantly.'),
(7, 'Vitamin C + Zinc Chewable Tablets', 120.00, 99.00, 4.5, 220, 'assets/images/products/vit_c_zinc.png', 'Supplements', 'Daily dietary supplement to maintain dynamic health and support immunity defense.'),
(8, 'OneTouch Glucometer Kit', 1500.00, 1199.00, 4.6, 75, 'assets/images/products/glucometer.png', 'Devices', 'Includes glucometer machine, lancing device, and 10 blood glucose test strips.'),
(9, 'Digital Weighing Scale', 1200.00, 899.00, 4.4, 80, 'assets/images/products/scale.png', 'Devices', 'Tempered glass body weighing scale with high-precision sensors and LCD display.'),
(10, 'Multi-vitamin Gummies (Kids)', 499.00, 399.00, 4.7, 110, 'assets/images/products/gummies.png', 'Supplements', 'Delicious fruit-flavored chewable gummies supplying key daily vitamins to growing kids.'),
(11, 'Orthopedic Heating Pad', 999.00, 799.00, 4.5, 65, 'assets/images/products/heating_pad.png', 'Devices', 'Electric heating belt with 3-tier temperature controller for quick back and joint pain relief.'),
(12, 'Knee Support Cap (Pair)', 600.00, 479.00, 4.2, 100, 'assets/images/products/knee_support.png', 'Wellness', 'Flexible compression sleeve designed to stabilize the knee joint and reduce strain.'),
(13, 'Vaporizer/Inhaler Machine', 450.00, 320.00, 4.3, 115, 'assets/images/products/vaporizer.png', 'Devices', '3-in-1 steam inhaler and facial sauna for clearing nasal congestion and skin treatment.'),
(14, 'Aloe Vera Gel 200g', 180.00, 144.00, 4.5, 140, 'assets/images/products/aloe_vera.png', 'Wellness', 'Pure cooling gel extracted from organic aloe vera to soothe skin irritation and sunburn.'),
(15, 'Medicated Anti-Dandruff Shampoo', 350.00, 299.00, 4.4, 95, 'assets/images/products/shampoo.png', 'Wellness', 'Contains ketoconazole to treat flaky scalp, itchiness, and severe dandruff issues.'),
(16, 'First Aid Kit Box', 500.00, 399.00, 4.6, 85, 'assets/images/products/first_aid.png', 'Wellness', 'Compact box loaded with bandages, medical tape, sterile gauze, ointment, and scissors.'),
(17, 'Elastic Crepe Bandage', 120.00, 95.00, 4.3, 160, 'assets/images/products/bandage.png', 'Wellness', 'Provides elastic support and compression for muscular sprains, strains, and minor swelling.'),
(18, 'Epsom Salt 1kg', 300.00, 240.00, 4.5, 105, 'assets/images/products/epsom_salt.png', 'Wellness', 'Pure magnesium sulfate crystals to dissolve in warm baths for body muscle relaxation.'),
(19, 'Blood Glucose Test Strips (50)', 999.00, 849.00, 4.7, 130, 'assets/images/products/strips.png', 'Devices', 'Pack of 50 sterile capillary blood test strips compatible with OneTouch glucometers.'),
(20, 'Premium Digital BP Monitor', 3500.00, 2999.00, 4.8, 40, 'assets/images/products/bp_premium.png', 'Devices', 'Smart Bluetooth enabled blood pressure monitor with companion app integration.');

-- 4. Insert Doctors (15 items)
INSERT INTO doctors (id, name, specialization, experience, languages, fee, availability, image, bio) VALUES
(1, 'Dr. Arvind Sharma', 'Cardiologist', 15, 'English, Hindi', 800.00, '{"days":["Mon","Wed","Fri"],"time":"09:00 AM - 01:00 PM"}', 'assets/images/doctors/doc1.png', 'Senior Consultant Cardiologist with 15+ years of experience in managing critical heart failure, angioplasty, and hypertension disorders.'),
(2, 'Dr. Priya Nair', 'Pediatrician', 10, 'English, Malayalam, Tamil', 600.00, '{"days":["Mon","Tue","Thu","Fri"],"time":"10:00 AM - 02:00 PM"}', 'assets/images/doctors/doc2.png', 'Dedicated Child Health specialist treating infant nutritional needs, asthma, childhood immunizations, and general health checkups.'),
(3, 'Dr. Rajesh Patel', 'General Physician', 12, 'English, Gujarati, Hindi', 500.00, '{"days":["Mon","Tue","Wed","Thu","Fri"],"time":"09:00 AM - 05:00 PM"}', 'assets/images/doctors/doc3.png', 'Experienced Family Physician focusing on lifestyle diseases, metabolic issues, respiratory colds, viral fever, and primary healthcare management.'),
(4, 'Dr. Sarah Gomez', 'Dermatologist', 8, 'English, Spanish', 700.00, '{"days":["Tue","Thu","Sat"],"time":"03:00 PM - 07:00 PM"}', 'assets/images/doctors/doc4.png', 'Expert Board-Certified Dermatologist dealing with acne scars, clinical skin allergies, chemical peels, hair fall treatments, and eczema.'),
(5, 'Dr. Amit Verma', 'Orthopedician', 14, 'English, Hindi, Punjabi', 750.00, '{"days":["Mon","Wed","Thu"],"time":"11:00 AM - 03:00 PM"}', 'assets/images/doctors/doc5.png', 'Specialized Joint Replacement and Bone Surgeon treating sports injuries, spinal problems, knee pain, and fracture cases.'),
(6, 'Dr. Neha Kapoor', 'Gynecologist', 11, 'English, Hindi', 650.00, '{"days":["Tue","Wed","Fri"],"time":"02:00 PM - 06:00 PM"}', 'assets/images/doctors/doc6.png', 'Expert in maternal healthcare, prenatal counselling, high-risk pregnancies, PCOS therapy, and women wellness wellness checks.'),
(7, 'Dr. Vikram Rao', 'Neurologist', 18, 'English, Telugu, Kannada', 1000.00, '{"days":["Mon","Fri"],"time":"10:00 AM - 02:00 PM"}', 'assets/images/doctors/doc7.png', 'Renowned Brain & Nerve specialist with advanced expertise in treating migraine headaches, epilepsy, stroke, and Parkinson\'s disease.'),
(8, 'Dr. Meera Deshmukh', 'Endocrinologist', 9, 'English, Marathi', 800.00, '{"days":["Wed","Thu","Sat"],"time":"09:30 AM - 01:30 PM"}', 'assets/images/doctors/doc8.png', 'Hormone health specialist dealing with severe diabetes management, thyroid nodules, obesity controls, and pituitary disorders.'),
(9, 'Dr. Sanjay Gupta', 'Oncologist', 20, 'English, Hindi, Bengali', 1200.00, '{"days":["Mon","Tue","Thu"],"time":"12:00 PM - 04:00 PM"}', 'assets/images/doctors/doc9.png', 'Highly respected Cancer Specialist providing clinical consultation on chemotherapy, early tumor detection, and immunotherapy treatments.'),
(10, 'Dr. Alok Mishra', 'ENT Specialist', 7, 'English, Hindi', 500.00, '{"days":["Mon","Wed","Fri"],"time":"04:00 PM - 08:00 PM"}', 'assets/images/doctors/doc10.png', 'Treats ear infections, nasal septum deviations, tonsillitis, throat voice disorders, and clinical hearing difficulties.'),
(11, 'Dr. Ritu Saxena', 'Ophthalmologist', 13, 'English, Hindi, Urdu', 600.00, '{"days":["Tue","Thu","Fri"],"time":"11:30 AM - 03:30 PM"}', 'assets/images/doctors/doc11.png', 'Specialist in refractive cataract surgery, glaucoma evaluation, diabetic retinopathy screening, and pediatric eye testing.'),
(12, 'Dr. John Carter', 'Psychiatrist', 16, 'English, German', 900.00, '{"days":["Mon","Wed","Sat"],"time":"01:00 PM - 05:00 PM"}', 'assets/images/doctors/doc12.png', 'Dedicated mental health counselor treating anxiety attacks, clinical depression, mood swings, and stress management.'),
(13, 'Dr. Sunita Williams', 'Urologist', 10, 'English, Hindi', 850.00, '{"days":["Tue","Fri"],"time":"03:00 PM - 06:00 PM"}', 'assets/images/doctors/doc13.png', 'Treats kidney stones, urinary tract infections, prostate gland enlargements, and male reproductive health issues.'),
(14, 'Dr. Devendra Joshi', 'Gastroenterologist', 14, 'English, Marathi, Hindi', 800.00, '{"days":["Wed","Thu","Fri"],"time":"10:00 AM - 02:00 PM"}', 'assets/images/doctors/doc14.png', 'Liver and Digestive tract specialist addressing acidity, liver cirrhosis, ulcerative colitis, IBS, and endoscopy exams.'),
(15, 'Dr. Ananya Sen', 'Dentist', 6, 'English, Bengali', 400.00, '{"days":["Mon","Tue","Wed","Thu","Fri","Sat"],"time":"09:00 AM - 01:00 PM"}', 'assets/images/doctors/doc15.png', 'Providing root canal therapy, teeth whitening, crowns/bridges, dental fillings, and scaling checkups.');

-- 5. Insert Lab Tests (20 items)
INSERT INTO tests (id, name, mrp, discount_price, rating, category, description) VALUES
(1, 'Complete Blood Count (CBC)', 350.00, 249.00, 4.6, 'General', 'Measures red blood cells, white blood cells, platelets, and hemoglobin. Essential indicator of overall health.'),
(2, 'HbA1c / Diabetes Profile', 500.00, 349.00, 4.7, 'Diabetes', 'Estimates average blood glucose levels over the past 3 months. Essential for diabetic monitoring.'),
(3, 'Vitamin D (25-Hydroxy) Test', 1200.00, 699.00, 4.5, 'Vitamins', 'Evaluates vitamin D levels in the blood, critical for bone structure and immune system health.'),
(4, 'Vitamin B12 Test', 900.00, 549.00, 4.4, 'Vitamins', 'Measures active B12 levels in blood, crucial for red blood cell formation and nervous system health.'),
(5, 'Liver Function Test (LFT)', 800.00, 499.00, 4.5, 'Organ Health', 'Set of clinical blood tests checking enzymes, bilirubin, and proteins to evaluate liver health.'),
(6, 'Thyroid Profile (T3, T4, TSH)', 700.00, 399.00, 4.6, 'Hormones', 'Assesses thyroid gland activity. Helps detect hyperthyroidism or hypothyroidism.'),
(7, 'Kidney Function Test (KFT)', 900.00, 549.00, 4.4, 'Organ Health', 'Measures urea, creatinine, and minerals in the blood to screen renal function.'),
(8, 'Lipid Profile (Cholesterol)', 650.00, 399.00, 4.5, 'Cardiac', 'Measures HDL, LDL, VLDL, and total cholesterol to evaluate cardiovascular risk status.'),
(9, 'Urine Routine & Microscopy', 200.00, 129.00, 4.3, 'General', 'Physical, chemical, and microscopic examination of urine to detect infections or kidney issues.'),
(10, 'Iron Profile / Anemia Test', 1000.00, 599.00, 4.5, 'General', 'Checks iron levels, ferritin, and total iron-binding capacity to detect iron-deficiency anemia.'),
(11, 'Double Marker Pregnancy Test', 2500.00, 1999.00, 4.6, 'Women Health', 'Maternal blood test done during the first trimester to screen for chromosomal abnormalities.'),
(12, 'Dengue NS1 Antigen Test', 800.00, 649.00, 4.4, 'Infections', 'Rapid blood test to diagnose acute dengue infection during the first few days of fever.'),
(13, 'COVID-19 RT-PCR Test', 1200.00, 799.00, 4.8, 'Infections', 'Gold-standard molecular test to diagnose active SARS-CoV-2 infection from nasal swabs.'),
(14, 'Cardiac Risk Profile (Premium)', 3000.00, 1999.00, 4.7, 'Cardiac', 'Comprehensive check including Lipid profile, Apolipoproteins, hs-CRP, and Homocysteine levels.'),
(15, 'Full Body Health Package (Basic)', 2500.00, 1249.00, 4.6, 'Packages', 'Comprehensive screening package covering CBC, LFT, KFT, Lipid Profile, and Blood Sugar (Fast).'),
(16, 'Executive Full Body Health (Premium)', 6000.00, 2999.00, 4.8, 'Packages', 'Premium package. Includes Basic tests + Vitamin D, B12, Thyroid profile, HbA1c, and Urine test.'),
(17, 'Women Wellness Package (Advanced)', 4000.00, 1999.00, 4.7, 'Packages', 'Tailored checkup for women: covers thyroid, iron levels, calcium, blood sugar, and CBC.'),
(18, 'Senior Citizen Checkup (Male)', 4500.00, 2249.00, 4.6, 'Packages', 'Covers bone health, cardiac risk factors, PSA (prostate cancer screen), kidney, and liver panels.'),
(19, 'Senior Citizen Checkup (Female)', 4500.00, 2249.00, 4.6, 'Packages', 'Covers bone mineral density markers, arthritis screen, thyroid, cardiac, liver, and kidney markers.'),
(20, 'Electrolyte Panel', 450.00, 299.00, 4.4, 'Organ Health', 'Measures concentrations of sodium, potassium, and chloride in blood to monitor fluid balance.');

-- 6. Insert Coupons
INSERT INTO coupons (id, code, discount_type, discount_value, min_cart_value, expiry_date) VALUES
(1, 'SAVE10', 'percentage', 10.00, 100.00, '2030-12-31'),
(2, 'WELCOME', 'fixed', 100.00, 500.00, '2030-12-31'),
(3, 'HEALTH20', 'percentage', 20.00, 1000.00, '2030-12-31'),
(4, 'DIAGNO50', 'percentage', 50.00, 500.00, '2030-12-31'), -- 50% off for tests
(5, 'FLAT200', 'fixed', 200.00, 1500.00, '2030-12-31');

-- 7. Insert Addresses for Dummy Users
INSERT INTO addresses (id, user_id, name, phone, address_line1, address_line2, city, state, pincode, is_default) VALUES
(1, 2, 'Amit Kumar', '9876543211', 'Flat No. 402, Green Glen Layout', 'Bellandur', 'Bengaluru', 'Karnataka', '560103', 1),
(2, 3, 'Priya Sharma', '9876543212', 'House 12, Sector 15', 'Noida Extension', 'Noida', 'Uttar Pradesh', '201301', 1),
(3, 4, 'Rohan Verma', '9876543213', 'B-34, Malviya Nagar', 'Near Market', 'New Delhi', 'Delhi', '110017', 1),
(4, 5, 'Sneha Patel', '9876543214', 'Plot 104, Navrangpura', 'Behind High School', 'Ahmedabad', 'Gujarat', '380009', 1),
(5, 6, 'Vikram Singh', '9876543215', 'Flat 12B, Royal Palm Society', 'Goregaon East', 'Mumbai', 'Maharashtra', '400063', 1),
(6, 7, 'Ananya Sen', '9876543216', '14/A, Ballygunge Circular Road', 'Opposite Post Office', 'Kolkata', 'West Bengal', '700019', 1),
(7, 8, 'Deepak Rao', '9876543217', '5-9-102, Himayatnagar', 'Lane No. 3', 'Hyderabad', 'Telangana', '500029', 1),
(8, 9, 'Kiran Joshi', '9876543218', 'Apt 205, FC Road', 'Shivajinagar', 'Pune', 'Maharashtra', '411005', 1),
(9, 10, 'Neha Gupta', '9876543219', 'C-12, Sector C, Indiranagar', 'Near Bhootnath Market', 'Lucknow', 'Uttar Pradesh', '226016', 1),
(10, 11, 'Sanjay Nair', '9876543220', '32, MG Road', 'Near Metro Station', 'Kochi', 'Kerala', '682011', 1),
(11, 12, 'Meera Deshmukh', '9876543221', 'Flat 502, Shivaji Colony', 'Deccan Gymkhana', 'Pune', 'Maharashtra', '411004', 1),
(12, 13, 'Rajesh Varma', '9876543222', 'House 43, Sector 4', 'Vashi', 'Navi Mumbai', 'Maharashtra', '400703', 1),
(13, 14, 'Shweta Mishra', '9876543223', 'A-89, Rajajipuram', 'Block F', 'Lucknow', 'Uttar Pradesh', '226017', 1),
(14, 15, 'Rahul Bose', '9876543224', '12, Salt Lake Sector 1', 'Near City Centre', 'Kolkata', 'West Bengal', '700064', 1);

-- 8. Insert Dummy Appointments (20 records)
INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status) VALUES
(2, 1, DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), '10:00 AM', 'Upcoming'),
(3, 2, DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), '11:30 AM', 'Upcoming'),
(4, 3, DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY), '02:00 PM', 'Completed'),
(5, 4, DATE_ADD(CURRENT_DATE, INTERVAL 4 DAY), '04:00 PM', 'Upcoming'),
(6, 5, DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY), '12:00 PM', 'Completed'),
(7, 6, DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), '03:30 PM', 'Upcoming'),
(8, 7, DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY), '11:00 AM', 'Upcoming'),
(9, 8, DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY), '10:30 AM', 'Cancelled'),
(10, 9, DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), '01:00 PM', 'Upcoming'),
(11, 10, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY), '05:00 PM', 'Completed'),
(12, 11, DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), '12:30 PM', 'Upcoming'),
(13, 12, DATE_ADD(CURRENT_DATE, INTERVAL 6 DAY), '02:00 PM', 'Upcoming'),
(14, 13, DATE_SUB(CURRENT_DATE, INTERVAL 4 DAY), '04:30 PM', 'Completed'),
(15, 14, DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), '11:00 AM', 'Upcoming'),
(2, 15, DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), '09:30 AM', 'Upcoming'),
(3, 3, DATE_SUB(CURRENT_DATE, INTERVAL 8 DAY), '10:00 AM', 'Completed'),
(4, 5, DATE_SUB(CURRENT_DATE, INTERVAL 10 DAY), '03:00 PM', 'Completed'),
(5, 7, DATE_ADD(CURRENT_DATE, INTERVAL 8 DAY), '11:00 AM', 'Upcoming'),
(6, 2, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY), '12:30 PM', 'Cancelled'),
(7, 4, DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY), '05:00 PM', 'Upcoming');

-- 9. Insert Dummy Orders (20 records)
INSERT INTO orders (id, user_id, order_number, total_amount, discount_amount, net_amount, address_id, payment_method, payment_status, order_status, created_at) VALUES
(1, 2, 'ORD-2026-1001', 350.00, 35.00, 315.00, 1, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 DAY)),
(2, 3, 'ORD-2026-1002', 2800.00, 280.00, 2520.00, 2, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 14 DAY)),
(3, 4, 'ORD-2026-1003', 119.00, 0.00, 119.00, 3, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 12 DAY)),
(4, 5, 'ORD-2026-1004', 1200.00, 100.00, 1100.00, 4, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 10 DAY)),
(5, 6, 'ORD-2026-1005', 450.00, 0.00, 450.00, 5, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 9 DAY)),
(6, 7, 'ORD-2026-1006', 1500.00, 150.00, 1350.00, 6, 'Card', 'Paid', 'Shipped', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY)),
(7, 8, 'ORD-2026-1007', 999.00, 0.00, 999.00, 7, 'Card', 'Paid', 'Shipped', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)),
(8, 9, 'ORD-2026-1008', 300.00, 30.00, 270.00, 8, 'Card', 'Paid', 'Processing', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 DAY)),
(9, 10, 'ORD-2026-1009', 450.00, 0.00, 450.00, 9, 'Card', 'Paid', 'Placed', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY)),
(10, 11, 'ORD-2026-1010', 99.00, 0.00, 99.00, 10, 'Card', 'Paid', 'Placed', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)),
(11, 12, 'ORD-2026-1011', 120.00, 12.00, 108.00, 11, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 20 DAY)),
(12, 13, 'ORD-2026-1012', 3200.00, 200.00, 3000.00, 12, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 19 DAY)),
(13, 14, 'ORD-2026-1013', 250.00, 0.00, 250.00, 13, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 18 DAY)),
(14, 15, 'ORD-2026-1014', 999.00, 99.90, 899.10, 14, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 17 DAY)),
(15, 2, 'ORD-2026-1015', 500.00, 50.00, 450.00, 1, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 16 DAY)),
(16, 3, 'ORD-2026-1016', 350.00, 0.00, 350.00, 2, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 DAY)),
(17, 4, 'ORD-2026-1017', 150.00, 15.00, 135.00, 3, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 14 DAY)),
(18, 5, 'ORD-2026-1018', 119.00, 0.00, 119.00, 4, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 12 DAY)),
(19, 6, 'ORD-2026-1019', 180.00, 18.00, 162.00, 5, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 10 DAY)),
(20, 7, 'ORD-2026-1020', 28.00, 0.00, 28.00, 6, 'Card', 'Paid', 'Delivered', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 8 DAY));

-- 10. Insert Order Items (20 records matching above orders)
INSERT INTO order_items (order_id, item_type, item_id, price, quantity) VALUES
(1, 'test', 1, 249.00, 1),
(1, 'medicine', 15, 92.00, 1),
(2, 'product', 3, 2249.00, 1),
(2, 'product', 13, 320.00, 1),
(3, 'medicine', 8, 99.00, 1),
(4, 'test', 3, 699.00, 1),
(4, 'medicine', 13, 115.00, 1),
(5, 'product', 13, 320.00, 1),
(5, 'medicine', 16, 41.00, 1),
(6, 'product', 8, 1199.00, 1),
(6, 'medicine', 14, 32.00, 2),
(7, 'product', 4, 699.00, 1),
(7, 'product', 5, 299.00, 1),
(8, 'product', 18, 240.00, 1),
(8, 'medicine', 4, 28.00, 1),
(9, 'product', 16, 399.00, 1),
(9, 'medicine', 16, 41.00, 1),
(10, 'product', 7, 99.00, 1),
(11, 'medicine', 6, 98.00, 1),
(12, 'product', 1, 2699.00, 1),
(13, 'product', 2, 180.00, 1),
(14, 'product', 11, 799.00, 1),
(15, 'test', 5, 499.00, 1),
(16, 'test', 1, 249.00, 1),
(17, 'medicine', 7, 125.00, 1),
(18, 'medicine', 8, 99.00, 1),
(19, 'medicine', 9, 149.00, 1),
(20, 'medicine', 10, 22.00, 1);
