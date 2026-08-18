USE yazzea_db;

ALTER TABLE products
ADD COLUMN image VARCHAR(255) DEFAULT NULL;

ALTER TABLE orders
ADD COLUMN payment_method VARCHAR(40)
NOT NULL DEFAULT 'Cash on Delivery (COD)';


UPDATE products
SET image = 'lavender.jpg'
WHERE name = 'Lavender Mist';


UPDATE products
SET image = 'tote.jpg'
WHERE name = 'Purple Tote Bag';


UPDATE products
SET image = 'candle.jpg'
WHERE name = 'Yazzea Candle';


UPDATE products
SET image = 'lipgloss.jpg'
WHERE name = 'Purple Lip Gloss';


UPDATE products
SET image = 'bracelet.jpg'
WHERE name = 'Yazzea Bracelet';