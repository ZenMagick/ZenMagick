<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @version $Id$
 */
?>
<?php

    /**
     * Return details about settings.
     */
    function zm_get_settings_details() {
        return array(
            'zenmagick.core' => array(
                'cache' => array(
                    array(
                        'key' => 'provider.file.baseDir',
                        'type' => 'string',
                        'desc' => 'Base directory for file based caching.'
                    ),
                    array(
                        'key' => 'mapping.defaults',
                        'type' => 'array',
                        'desc' => 'Default provider for persistent and transient caching.'
                    ),
                ),
                'logging' => array(
                    array(
                        'key' => 'enabled',
                        'type' => 'boolean',
                        'desc' => 'Enable/disable logging.'
                    ),
                    array(
                        'key' => 'level',
                        'type' => 'int',
                        'desc' => 'Current log level.'
                    ),
                    array(
                        'key' => 'filename',
                        'type' => 'string',
                        'desc' => 'Filename for custom logging.'
                    ),
                    array(
                        'key' => 'handleErrors',
                        'type' => 'boolean',
                        'desc' => 'Enable error handling by the configured logging service.'
                    )
                ),
                'plugins' => array(
                    array(
                        'key' => 'baseDir',
                        'type' => 'string',
                        'desc' => 'Plugin base directory.'
                    ),
                    array(
                        'key' => 'enabled',
                        'type' => 'boolean',
                        'desc' => 'Enable/disable *all* plugins.'
                    ),
                    array(
                        'key' => 'context',
                        'type' => 'int',
                        'desc' => 'Context flag.'
                    ),
                    array(
                        'key' => 'groups',
                        'type' => 'array',
                        'desc' => 'List of plugin groups (subfolder in zenmagick.core.plugins.baseDir) to load.'
                    ),
                    array(
                        'key' => '@name@.enabled',
                        'type' => 'dynamic:name:boolean',
                        'desc' => 'Enable/disable setting for plugin with name "@name@".'
                    )
                ),
                'database' => array(
                    array(
                        'key' => 'tablePrefix',
                        'type' => 'string',
                        'desc' => 'Optional table prefix to be used for all table names.'
                    ),
                    array(
                        'key' => 'mappings.cache.enabled',
                        'type' => 'boolean',
                        'desc' => 'Enable/disable caching of database table mappings.'
                    ),
                    array(
                        'key' => 'mappings.file',
                        'type' => 'string',
                        'desc' => 'File containing initial table mappings.'
                    ),
                    array(
                        'key' => 'mappings.autoMap.enabled',
                        'type' => 'boolean',
                        'desc' => 'Enable/disable automatic generation of table mappings for unknown tables.'
                    ),
                    array(
                        'key' => 'sql.@table@.customFields',
                        'type' => 'dynamic:table:string',
                        'desc' => 'List of custom field mappings for table "@table@".'
                    ),
                    array(
                        'key' => 'model.keyName',
                        'type' => 'string',
                        'desc' => 'Name of primary key column for model handling.'
                    ),
                    array(
                        'key' => 'connections.default',
                        'type' => 'array',
                        'desc' => 'Default connection settings.'
                    ),
                    array(
                        'key' => 'connections.@name@',
                        'type' => 'dynamic:name:string',
                        'desc' => 'Connection settings for symbolic name "@name@".'
                    ),
                    array(
                        'key' => 'provider',
                        'type' => 'string',
                        'desc' => 'Default database provider to be used if none specified in the connection settings.'
                    )
                ),
                'fs' => array(
                    array(
                        'key' => 'permissions.fix',
                        'type' => 'boolean',
                        'desc' => 'Enable/disable permission fixing in ZMFileUtils methods.'
                    ),
                    array(
                        'key' => 'permissions.defaults.folder',
                        'type' => 'octal',
                        'desc' => 'Default permissions to be set for folders.'
                    ),
                    array(
                        'key' => 'permissions.defaults.file',
                        'type' => 'octal',
                        'desc' => 'Default permissions to be set for files.'
                    )
                ),
                'authentication' => array(
                    array(
                        'key' => 'minPasswordLength',
                        'type' => 'int',
                        'desc' => 'Minimum password length.'
                    )
                )
            ),
            'zenmagick.mvc' => array(
                'html' => array(
                    array(
                        'key' => 'xhtml',
                        'type' => 'boolean',
                        'desc' => 'Control generation of HTML/XHTML content.'
                    ),
                    array(
                        'key' => 'contentType',
                        'type' => 'string',
                        'desc' => 'Content type.'
                    ),
                    array(
                        'key' => 'charset',
                        'type' => 'string',
                        'desc' => 'Page charset.'
                    ),
                    array(
                        'key' => 'tokenSecuredForms',
                        'type' => 'string',
                        'desc' => 'List of forms (formId) to be secured with a dynamic session token.'
                    )
                ),
                'sacs' => array(
                    array(
                        'key' => 'handler',
                        'type' => 'string',
                        'desc' => 'List of handler classes to handle (S)imple (A)ccess (C)ontrol (S)ystem requests.'
                    )
                ),
                'templates' => array(
                    array(
                        'key' => 'ext',
                        'type' => 'string',
                        'desc' => 'Template filename extension.'
                    )
                ),
                'toolbox' => array(
                    array(
                        'key' => 'tools',
                        'type' => 'string',
                        'desc' => 'Custom tools (example: name:class,name:class)'
                    )
                ),
                'request' => array(
                    array(
                        'key' => 'missingPage',
                        'type' => 'string',
                        'desc' => 'Request id to be used in case of invalid views.'
                    ),
                    array(
                        'key' => 'invalidSession',
                        'type' => 'string',
                        'desc' => 'Request id to be used in case of invalid session.'
                    ),
                    array(
                        'key' => 'login',
                        'type' => 'string',
                        'desc' => 'Request id of login page.'
                    ),
                    array(
                        'key' => 'seoRewriter',
                        'type' => 'string',
                        'desc' => 'List of SEO rewriter classes to use for SEO url generation.'
                    )
                ),
                'transactions' => array(
                    array(
                        'key' => 'enabled',
                        'type' => 'boolean',
                        'desc' => 'Execute the controller in the context of a database transaction.'
                    )
                ),
                'ajax' => array(
                    array(
                        'key' => 'format',
                        'type' => 'string',
                        'desc' => 'Format suffix for methods if no default is found.'
                    )
                ),
                'json' => array(
                    array(
                        'key' => 'header',
                        'type' => 'boolean',
                        'desc' => 'Enable/disable to return JSON as JSON header "X-JSON".'
                    ),
                    array(
                        'key' => 'echo',
                        'type' => 'boolean',
                        'desc' => 'Enable/disable echoing a JSON respose.'
                    )
                ),
                'controller' => array(
                    array(
                        'key' => 'default',
                        'type' => 'string',
                        'desc' => 'Default controller definition.'
                    )
                ),
                'view' => array(
                    array(
                        'key' => 'default',
                        'type' => 'string',
                        'desc' => 'Default view definition.'
                    ),
                    array(
                        'key' => 'defaultLayout',
                        'type' => 'string',
                        'desc' => 'Default layout name.'
                    )
                ),
                'resultlist' => array(
                    array(
                        'key' => 'defaultPagination',
                        'type' => 'int',
                        'desc' => 'Default results per page.'
                    )
                )
            )
        );
    }

?>
