#
# ZenMagick product tags plugin test data SQL
#
# $Id$
#

INSERT INTO tags (tag_id, name) VALUES (1, 'foo');
INSERT INTO tags (tag_id, name) VALUES (2, 'bar');
INSERT INTO tags (tag_id, name) VALUES (3, 'doh');

INSERT INTO product_tags (product_id, tag_id) VALUES (11, 1);
INSERT INTO product_tags (product_id, tag_id) VALUES (12, 1);
INSERT INTO product_tags (product_id, tag_id) VALUES (13, 1);
INSERT INTO product_tags (product_id, tag_id) VALUES (12, 2);
INSERT INTO product_tags (product_id, tag_id) VALUES (13, 2);
INSERT INTO product_tags (product_id, tag_id) VALUES (13, 3);

