#
# ZenMagick config SQL - undo
#

## get the config group id
SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'ZenMagick Configuration';

## remove config entries for group
DELETE FROM configuration WHERE configuration_group_id = @t4;

## remove group itself
DELETE FROM configuration_group WHERE configuration_group_id = @t4;


## get the plugins group id
SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'ZenMagick Plugins';

## remove config entries for group
DELETE FROM configuration WHERE configuration_group_id = @t4;

## remove group itself
DELETE FROM configuration_group WHERE configuration_group_id = @t4;
