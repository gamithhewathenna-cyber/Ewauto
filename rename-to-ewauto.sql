-- One-off: rename "ZXTec" to "EW Auto" in text already stored in the live DB.
-- Safe to re-run. Run it once in phpMyAdmin against tcplckfa_ewautodb, then delete this file.
UPDATE content  SET content_value = REPLACE(content_value, 'ZXTec', 'EW Auto') WHERE content_value LIKE '%ZXTec%';
UPDATE settings SET setting_value = REPLACE(setting_value, 'ZXTec', 'EW Auto') WHERE setting_value LIKE '%ZXTec%';
UPDATE images   SET alt_text      = REPLACE(alt_text,      'ZXTec', 'EW Auto') WHERE alt_text      LIKE '%ZXTec%';
UPDATE slides   SET heading = REPLACE(heading, 'ZXTec', 'EW Auto'), subheading = REPLACE(subheading, 'ZXTec', 'EW Auto'), alt_text = REPLACE(alt_text, 'ZXTec', 'EW Auto');
UPDATE bikes    SET tagline = REPLACE(tagline, 'ZXTec', 'EW Auto'), description = REPLACE(description, 'ZXTec', 'EW Auto');
UPDATE dealers  SET name = REPLACE(name, 'ZXTec', 'EW Auto');

-- Replace long dashes (em dash / en dash) with a plain hyphen in stored text.
UPDATE content  SET content_value = REPLACE(REPLACE(content_value, '—', '-'), '–', '-');
UPDATE settings SET setting_value = REPLACE(REPLACE(setting_value, '—', '-'), '–', '-');
UPDATE images   SET alt_text = REPLACE(REPLACE(alt_text, '—', '-'), '–', '-');
UPDATE slides   SET heading = REPLACE(REPLACE(heading, '—', '-'), '–', '-'), subheading = REPLACE(REPLACE(subheading, '—', '-'), '–', '-');
UPDATE bikes    SET tagline = REPLACE(REPLACE(tagline, '—', '-'), '–', '-'), description = REPLACE(REPLACE(description, '—', '-'), '–', '-');
UPDATE dealers  SET name = REPLACE(REPLACE(name, '—', '-'), '–', '-'), address = REPLACE(REPLACE(address, '—', '-'), '–', '-');
