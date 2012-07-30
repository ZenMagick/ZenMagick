#
# ZenMagick config SQL
#

## create hidden group to store all general config stuff
INSERT INTO configuration_group (configuration_group_title, configuration_group_description, visible)
VALUES ('ZenMagick Configuration', 'ZenMagick Configuration', '0');

## get the new group id
SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4
FROM configuration_group
WHERE configuration_group_title= 'ZenMagick Configuration';

## create config entry for general group
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added)
VALUES ('ZenMagick Configuration Group Id', 'ZENMAGICK_CONFIG_GROUP_ID', @t4, '', @t4, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added)
VALUES ('ZenMagick Plugin Status', 'ZENMAGICK_PLUGIN_STATUS', '', '', @t4, now());


## create hidden group to store all plugin config settings
INSERT INTO configuration_group (configuration_group_title, configuration_group_description, visible)
VALUES ('ZenMagick Plugins', 'ZenMagick Plugins', '0');

## get the new group id
SET @t5=0;
SELECT (@t5:=configuration_group_id) as t5
FROM configuration_group
WHERE configuration_group_title= 'ZenMagick Plugins';

## create config entry for plugin group
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added)
VALUES ('ZenMagick Plugins Group Id', 'ZENMAGICK_PLUGIN_GROUP_ID', @t5, '', @t4, now());

## migrate existing plugin settings
UPDATE configuration SET configuration_group_id = @t5 WHERE configuration_key like 'PLUGIN_REQUEST_%';
