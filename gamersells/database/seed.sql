USE gamersells;
INSERT INTO categories(name,slug) VALUES ('Keyboards','keyboards'),('Mice','mice'),('Headsets','headsets'),('Gaming Chairs','gaming-chairs'),('Monitors','monitors'),('Accessories','accessories') ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO products(category_id,name,slug,description,price,stock,image,featured) VALUES
((SELECT id FROM categories WHERE slug='keyboards'),'RGB Mechanical Keyboard','rgb-mechanical-keyboard','Mechanical gaming keyboard with RGB lighting.',249.90,15,'keyboard.jpg',TRUE),
((SELECT id FROM categories WHERE slug='mice'),'Gamer Pro Mouse','gamer-pro-mouse','Ergonomic gaming mouse with adjustable DPI.',129.90,30,'mouse.jpg',TRUE),
((SELECT id FROM categories WHERE slug='headsets'),'Gamer X1 Headset','gamer-x1-headset','Surround sound headset with microphone.',299.90,12,'headset.jpg',TRUE),
((SELECT id FROM categories WHERE slug='gaming-chairs'),'Cyber Gaming Chair','cyber-gaming-chair','Adjustable ergonomic chair for long sessions.',1199.90,8,'chair.jpg',TRUE)
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),price=VALUES(price),stock=VALUES(stock),image=VALUES(image),featured=VALUES(featured);
