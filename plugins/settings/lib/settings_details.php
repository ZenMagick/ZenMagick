<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */

    /**
     * Return details about settings.
     */
    function zm_get_settings_details() {
        return array(
            'zenmagick.base' => array(
                'logging' => array(
                    array(
                        'key' => 'handler',
                        'type' => 'string',
                        'desc' => 'List of configured logging handler.'
                    ),
                ),
                'authentication' => array(
                    array(
                        'key' => 'minPasswordLength',
                        'type' => 'int',
                        'desc' => 'Minimum password length.'
                    )
                ),
            ),
            'zenmagick.core' => array(
                'cache' => array(
                    array(
                        'key' => 'providers',
                        'type' => 'string',
                        'desc' => 'List of known cache providers.'
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
                        'key' => '@name@.enabled',
                        'type' => 'dynamic:name:boolean',
                        'desc' => 'Enable/disable setting for plugin with name "@name@".'
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
                'events' => array(
                    array(
                        'key' => 'listeners',
                        'type' => 'string',
                        'desc' => 'List of comma separated class names of default event listeners.'
                    )
                ),
                'beans' => array(
                    array(
                        'key' => 'definitions.@name@',
                        'type' => 'string',
                        'desc' => 'Bean/singleton definition mapping for a bean with name "@name@".'
                    ),
                    array(
                        'key' => 'locale',
                        'type' => 'string',
                        'desc' => 'Active locale.'
                    )
                ),
            ),
            'zenmagick.mvc' => array(
                'html' => array(
                    array(
                        'key' => 'xhtml',
                        'type' => 'boolean',
                        'desc' => 'Control generation of HTML/XHTML content.'
                    ),
                    array(
                        'key' => 'dir',
                        'type' => 'string',
                        'desc' => 'The default base dir; either <em>ltr</em> or <em>rtl</em>.'
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
                    )
                ),
                'sacs' => array(
                    array(
                        'key' => 'handler',
                        'type' => 'array',
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
                        'type' => 'array',
                        'desc' => 'Custom tools (example: name:class,name:class)'
                    )
                ),
                'request' => array(
                    array(
                        'key' => 'idName',
                        'type' => 'string',
                        'desc' => 'Name of the query arg controlling the request id.'
                    ),
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
                        'key' => 'secure',
                        'type' => 'boolean',
                        'desc' => 'Are secure requests enabled.'
                    ),
                    array(
                        'key' => 'enforceSecure',
                        'type' => 'boolean',
                        'desc' => 'Are secure requests to be enforced. This will create redirects from http:// to https:// if a secure page is loaded via http://'
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
                    )
                ),
                'resultlist' => array(
                    array(
                        'key' => 'defaultPagination',
                        'type' => 'int',
                        'desc' => 'Default results per page.'
                    )
                )
            ),
            'zenmagick.http' => array(
                'request' => array(
                    array(
                        'key' => 'handler',
                        'type' => 'string',
                        'desc' => 'Name of the request handler (ie index.php).'
                    ),
                    array(
                        'key' => 'urlRewriter',
                        'type' => 'array',
                        'desc' => 'List of rewriter classes to use for url generation.'
                    ),
                    array(
                        'key' => 'allSecure',
                        'type' => 'boolean',
                        'desc' => 'Enforce all urls to be secure.'
                    ),
                    array(
                        'key' => 'urlType',
                        'type' => 'string',
                        'desc' => 'Type of url format; either "default", "path" or "realpath".'
                    )
                ),
                'view' => array(
                    'key' => 'defaultLayout',
                    'type' => 'string',
                    'desc' => 'Default layout name.'
                )
            ),
            'apps.store' => array(
                'update' => array(
                    array(
                        'key' => 'channel',
                        'type' => 'string',
                        'desc' => 'Set version check channel; valid are "stable" (default if empty) and "dev".'
                    )
                ),
                'search' => array(
                    array(
                        'key' => 'fulltext',
                        'type' => 'boolean',
                        'desc' => 'Enable MySQL fulltext search in product search.'
                    )
                ),
                'admin' => array(
                    array(
                        'key' => 'defaultEditor',
                        'type' => 'string',
                        'desc' => 'Name of the default text editor widget.'
                    )
                ),
                array(
                    'key' => 'database.default',
                    'type' => 'array',
                    'desc' => 'Default connection settings.'
                ),
                array(
                    'key' => 'database.@name@',
                    'type' => 'dynamic:name:string',
                    'desc' => 'Connection settings for symbolic name "@name@".'
                )
            ),
        );
    }

?>
