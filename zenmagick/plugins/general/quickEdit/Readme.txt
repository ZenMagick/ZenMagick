This is a Catalog Manager plugin to quickly edit all products of a selected category.

It is possible to configure your own set of fields by creating a setting
named 'plugins.quickEdit.fieldList' that contains a custom field mpa.

<?php

  // custom fields
  ZMSettings::set('plugins.quickEdit.fieldList', array(
      array('name' => 'name', 'widget' => 'TextFormWidget#title=Name&name=name&size=35'),
      array('name' => 'status', 'widget' => 'BooleanFormWidget#style=checkbox&title=Enabled&name=status&size=35'),
      array('name' => 'manufacturerId', 'widget' => 'ManufacturerSelectFormWidget#title=Manufacturer&options=0= --- '),
  ));

?>

Each field is configured separately, and requires the following information:
- name: The property name of ZMProduct
- widget: The widget to handle the data

NOTE: For custom database fields you also have to configure ZMProducts in order to actually read/write those columns. This is done
by setting 'zenmagick.core.database.sql.products.customFields'.

Example:

    ZMSettings::append('zenmagick.core.database.sql.products.customFields', 'metatags_title_status;integer', ',');

