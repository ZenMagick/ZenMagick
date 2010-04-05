<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
?>
<?php


/**
 * ZenMagick MVC constants.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc
 * @version $Id$
 */
interface ZMMVCConstants {
    /** Fired before a redirect. */
    const INSUFFICIENT_CREDENTIALS = 'insufficient_credentials';
    const EVENT_REDIRECT = 'redirect';
    const DISPATCH_START = 'dispatch_start';
    const DISPATCH_DONE = 'dispatch_done';
    const VIEW_START = 'view_start';
    const VIEW_DONE = 'view_done';
    const CONTROLLER_PROCESS_START = 'controller_process_start';
    const CONTROLLER_PROCESS_END = 'controller_process_end';
    const ALL_DONE = 'all_done';
    const FINALISE_CONTENTS = 'finalise_contents';

}
