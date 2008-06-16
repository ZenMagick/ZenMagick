#
# OpenID SQL
#
# $Id$
#

DROP TABLE `zm_openid_associations`;
DROP TABLE `zm_openid_nonces`;
ALTER TABLE `customers` DROP `openid`;
