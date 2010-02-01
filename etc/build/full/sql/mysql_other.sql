#
# ZenMagick full installer other SQL
#
# $Id$
#

## disable missing page check
UPDATE configuration set configuration_value = 'Off' WHERE configuration_key = 'MISSING_PAGE_CHECK';
## force cookie to avoid SID urls
UPDATE configuration set configuration_value = 'True' WHERE configuration_key = 'SESSION_FORCE_COOKIE_USE';
## disable downloads
UPDATE configuration set configuration_value = 'false' WHERE configuration_key = 'DOWNLOAD_ENABLED';
## set default theme to 'default'
UPDATE template_select set template_dir = 'default' WHERE template_dir = 'classic';


## remove zen cart banners - these should be demo only
DELETE FROM banners;

## hide some unused options
#  8 = Configuration - Product Listing
# 10 = Configuration - Logging
# 14 = Configuration - GZip Compression
# 18 = Configuration - Product Info
# 21 = Configuration - New Listing
# 22 = Configuration - Featured Listing
# 23 = Configuration - All Listing
# 24 = Configuration - Index Listing
# 25 = Configuration - Define Page Status
# 30 = Configuration - EZ-Pages Settings
UPDATE configuration_group SET visible = 0 WHERE configuration_group_id in (8, 10, 14, 18, 21, 22, 23, 24, 25, 30)

