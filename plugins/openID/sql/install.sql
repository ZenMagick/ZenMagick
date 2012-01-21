#
# OpenID SQL
#
# $Id$
#

CREATE TABLE `zm_openid_associations` (
  `server_url` varchar(64) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `secret` blob NOT NULL,
  `issued` int(10) unsigned NOT NULL,
  `lifetime` int(10) unsigned NOT NULL,
  `assoc_type` varchar(64) NOT NULL,
  PRIMARY KEY (`server_url`,`handle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='OpenID Server Associations';

CREATE TABLE `zm_openid_nonces` (
  `server_url` varchar(255) NOT NULL,
  `issued` int(10) unsigned NOT NULL,
  `salt` char(40) NOT NULL,
  PRIMARY KEY (`server_url`,`issued`,`salt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Nonce table for OpenID authentication';

ALTER TABLE `customers` ADD `openid` varchar(255);
