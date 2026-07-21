-- Create one active delivery area for every city without an active area.
-- Default delivery cost: 40 DH.
-- This script is idempotent and does not overwrite existing areas or costs.

START TRANSACTION;

INSERT INTO `areas`
(
    `name`,
    `city_id`,
    `cost`,
    `status`,
    `created_at`,
    `updated_at`,
    `deleted_at`
)
SELECT
    c.`name`,
    c.`id`,
    40.00,
    1,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP,
    NULL
FROM `cities` AS c
WHERE c.`name` IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `areas` AS a
      WHERE a.`city_id` = c.`id`
        AND a.`deleted_at` IS NULL
  );

COMMIT;

-- Verification: cities still without an active area.
SELECT
    c.`id` AS `city_id`,
    c.`name` AS `city_name`
FROM `cities` AS c
LEFT JOIN `areas` AS a
    ON a.`city_id` = c.`id`
   AND a.`deleted_at` IS NULL
WHERE a.`id` IS NULL
ORDER BY c.`id`;
