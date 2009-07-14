SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) FROM configuration_group WHERE configuration_group_title= 'CAPTCHA' LIMIT 1;
DELETE FROM configuration WHERE configuration_group_id = @configuration_group_id;
DELETE FROM configuration_group WHERE configuration_group_id = @configuration_group_id;

INSERT INTO configuration_group (configuration_group_id, configuration_group_title, configuration_group_description, sort_order, visible) VALUES (NULL, 'CAPTCHA', 'CAPTCHA Configuration', '1', '1');
SET @configuration_group_id=last_insert_id();
UPDATE configuration_group SET sort_order = @configuration_group_id WHERE configuration_group_id = @configuration_group_id;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES 
(NULL, 'Code Length', 'CAPTCHA_CODE_LENGTH', '6', 'Verification Code length', @configuration_group_id, 1, NOW(), NULL, NULL),
(NULL, 'Image Width', 'CAPTCHA_IMG_WIDTH', '240', 'CAPTCHA Image Width', @configuration_group_id, 2, NOW(), NULL, NULL),
(NULL, 'Image Height', 'CAPTCHA_IMG_HEIGHT', '50', 'CAPTCHA Image Height', @configuration_group_id, 3, NOW(), NULL, NULL),
(NULL, 'Chars minimum size', 'CAPTCHA_CHARS_MIN_SIZE', '0.6', 'Chars minimum size (1.0=Image Height)', @configuration_group_id, 4, NOW(), NULL, NULL),
(NULL, 'Chars maximum size', 'CAPTCHA_CHARS_MAX_SIZE', '0.8', 'Chars maximum size (1.0=Image Height)', @configuration_group_id, 5, NOW(), NULL, NULL),
(NULL, 'Corner of rotation', 'CAPTCHA_CHARS_ROTATION', '23', 'Chars Corner of rotation', @configuration_group_id, 6, NOW(), NULL, NULL),
(NULL, 'Shadow Chars', 'CAPTCHA_CHARS_SHADOW', 'true', 'Generate Shadows for Characters', @configuration_group_id, 7, NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),'),
(NULL, 'Image Type', 'CAPTCHA_IMG_TYPE', 'png', 'CAPTCHA Image Type', @configuration_group_id, 10, NOW(), NULL, 'zen_cfg_select_option(array(''png'', ''jpeg'', ''gif''),'),
(NULL, 'Create Account page', 'CAPTCHA_CREATE_ACCOUNT', 'true', 'Activate Validation on Create Account page', @configuration_group_id, 21, NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),'),
(NULL, 'Contact Us page', 'CAPTCHA_CONTACT_US', 'true', 'Activate Validation on Contact Us page', @configuration_group_id, 22, NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),'),
(NULL, 'Tell A Friend page', 'CAPTCHA_TELL_A_FRIEND', 'true', 'Activate Validation on Tell A Friend page', @configuration_group_id, 23, NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),'),
(NULL, 'Links Submit page', 'CAPTCHA_LINKS_SUBMIT', 'true', 'Activate Validation on Links Submit page', @configuration_group_id, 24, NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),'),
(NULL, 'Write Review page', 'CAPTCHA_REVIEWS_WRITE', 'true', 'Activate Validation on Write Review page', @configuration_group_id, 25, NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),');
