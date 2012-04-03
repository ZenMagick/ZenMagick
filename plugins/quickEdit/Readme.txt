This is a Catalog Manager plugin to quickly edit all products of a selected category.

It is possible to configure your own set of fields by creating a setting
named 'plugins.quickEdit.fieldList' that contains a custom field map.

admin:
  settings:
    quickEdit:
      fieldList:
         - { name: name, widget: 'textFormWidget#title=Name&name=name&size=35' }
         - { name: status, widget: 'booleanFormWidget#style=checkbox&title=Enabled&name=status' }
         - { name: manufacturerId, widget: 'nanufacturerSelectFormWidget#title=Manufacturer&options=0= --- ' }
  

Each field is configured separately, and requires the following information:
- name: The property name of ZMProduct
- widget: The widget to handle the data

NOTE: For custom database fields you also have to configure ZMProducts in order to actually read/write those columns. This is done
by setting by adding the property to the database mapping.

Example:

   
    $info = array('column' => 'metatag_title_status', 'type' => 'integer');
    ZMRuntime::getDatabase()->getMapper()->addPropertyForTable('products', 'metatag_title_status', $info);
