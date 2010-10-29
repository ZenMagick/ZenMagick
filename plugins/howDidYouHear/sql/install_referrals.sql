DROP TABLE IF EXISTS sources;
CREATE TABLE sources (
  sources_id int NOT NULL auto_increment,
  sources_name varchar(64) NOT NULL,
  PRIMARY KEY (sources_id),
  KEY IDX_SOURCES_NAME (sources_name)
);

INSERT INTO sources VALUES (1, 'Google');
INSERT INTO sources VALUES (2, 'Yahoo!');
INSERT INTO sources VALUES (3, 'AOL');
INSERT INTO sources VALUES (4, 'MSN');
INSERT INTO sources VALUES (5, 'ZenMagick');
INSERT INTO sources VALUES (9999, 'Other');

DROP TABLE IF EXISTS sources_other;
CREATE TABLE sources_other (
  customers_id int NOT NULL default '0',
  sources_other_name varchar(64) NOT NULL,
  PRIMARY KEY (customers_id)
);

ALTER TABLE customers_info ADD customers_info_source_id int DEFAULT 9999 AFTER customers_info_date_account_last_modified;


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display "Other" Referral option', 'DISPLAY_REFERRAL_OTHER', 'true', 'Display "Other - please specify" with text box in referral source in account creation', '1', '22', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Require Referral', 'REFERRAL_REQUIRED', 'true', 'Require the Referral Source in account creation', '5', '6', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
