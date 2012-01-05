This is a Catalog Manager plugin to quickly edit all products of a selected category.

It is possible to configure your own set of fields by creating a setting
named 'plugins.quickEdit.fieldList' that contains a custom field map.

<?php

  // custom fields
  ZMSettings::set('plugins.quickEdit.fieldList', array(
      array('name' => 'name', 'widget' => 'ZMTextFormWidget#title=Name&name=name&size=35'),
      array('name' => 'status', 'widget' => 'ZMBooleanFormWidget#style=checkbox&title=Enabled&name=status&size=35'),
      array('name' => 'manufacturerId', 'widget' => 'nanufacturerSelectFormWidget#title=Manufacturer&options=0= --- '),
  ));

?>

Each field is configured separately, and requires the following information:
- name: The property name of ZMProduct
- widget: The widget to handle the data

NOTE: For custom database fields you also have to configure ZMProducts in order to actually read/write those columns. This is done
by setting 'zenmagick.core.database.sql.products.customFields'.

Example:

    ZMSettings::append('zenmagick.core.database.sql.products.customFields', 'metatags_title_status;integer', ',');

