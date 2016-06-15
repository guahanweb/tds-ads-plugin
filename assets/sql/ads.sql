CREATE TABLE {{TABLE}} (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    advertiser_id INTEGER UNSIGNED NOT NULL,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated TIMESTAMP DEFAULT '0000-00-00 00:00:00',
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT '',
    content TEXT DEFAULT '',
    PRIMARY KEY  (id),
    UNIQUE KEY  (advertiser_id, name)
) {{CHARSET}};
