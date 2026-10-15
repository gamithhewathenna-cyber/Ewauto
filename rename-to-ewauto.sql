-- One-off cleanup of text already stored in the live DB (the site shows DB text
-- in place of the code defaults). Run once in phpMyAdmin against
-- tcplckfa_ewautodb, then delete this file. Safe to re-run.
--
-- 1) Rename every spelling of "ZXTec" to "EW Auto".
-- 2) Replace long dashes (em dash / en dash) with a plain hyphen.

-- Helper expression used below (MySQL REPLACE is case-sensitive, so each
-- spelling variant is listed):
--   REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(x,
--     'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto')

UPDATE content SET content_value =
    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(content_value,
    'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),
    '—','-'),'–','-');

UPDATE settings SET setting_value =
    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(setting_value,
    'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),
    '—','-'),'–','-');

UPDATE images SET alt_text =
    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(alt_text,
    'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),
    '—','-'),'–','-');

UPDATE slides SET
    heading = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(heading,
        'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),'—','-'),'–','-'),
    subheading = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(subheading,
        'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),'—','-'),'–','-'),
    alt_text = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(alt_text,
        'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),'—','-'),'–','-');

UPDATE bikes SET
    button_label = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(button_label,
        'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),'—','-'),'–','-'),
    tagline = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(tagline,
        'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),'—','-'),'–','-'),
    description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description,
        'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),'—','-'),'–','-');

UPDATE bike_colors SET alt_text =
    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(alt_text,
    'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),
    '—','-'),'–','-');

UPDATE dealers SET
    name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name,
        'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),'—','-'),'–','-'),
    address = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(address,
        'ZX Tec','EW Auto'),'ZXTEC','EW Auto'),'ZXTec','EW Auto'),'ZXtec','EW Auto'),'Zxtec','EW Auto'),'zxtec','EW Auto'),'—','-'),'–','-');

-- Check: this should return 0 rows once the cleanup has run.
SELECT 'content' AS tbl, content_key AS ref FROM content WHERE content_value LIKE '%zxtec%' OR content_value LIKE '%zx tec%'
UNION ALL SELECT 'settings', setting_key FROM settings WHERE setting_value LIKE '%zxtec%'
UNION ALL SELECT 'images', slot FROM images WHERE alt_text LIKE '%zxtec%'
UNION ALL SELECT 'slides', id FROM slides WHERE CONCAT_WS(' ', heading, subheading, alt_text) LIKE '%zxtec%'
UNION ALL SELECT 'bikes', id FROM bikes WHERE CONCAT_WS(' ', button_label, tagline, description) LIKE '%zxtec%'
UNION ALL SELECT 'dealers', id FROM dealers WHERE CONCAT_WS(' ', name, address) LIKE '%zxtec%';
