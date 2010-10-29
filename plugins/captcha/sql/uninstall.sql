SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) FROM configuration_group WHERE configuration_group_title= 'CAPTCHA' LIMIT 1;
DELETE FROM configuration WHERE configuration_group_id = @configuration_group_id;
DELETE FROM configuration_group WHERE configuration_group_id = @configuration_group_id;