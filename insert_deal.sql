INSERT INTO flash_deals (title, slug, start_date, end_date, status, featured, background_color, text_color, banner, created_at, updated_at) 
VALUES ('Test Modern Deal', 'test-modern-deal', UNIX_TIMESTAMP() - 3600, UNIX_TIMESTAMP() + 86400, 1, 1, '#ffffff', '#000000', 1, NOW(), NOW());

SET @deal_id = LAST_INSERT_ID();

INSERT INTO flash_deal_products (flash_deal_id, product_id, discount, discount_type, created_at, updated_at)
SELECT @deal_id, id, 10, 'percent', NOW(), NOW() FROM products WHERE published = 1 LIMIT 1;
