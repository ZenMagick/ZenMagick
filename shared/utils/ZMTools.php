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

use zenmagick\base\Runtime;
use zenmagick\apps\store\bundles\ZenCartBundle\mock\ZenCartCheckoutOrder;

/**
 * (System) Tools.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.utils
 */
class ZMTools {
    const RANDOM_DIGITS = 'digits';
    const RANDOM_CHARS = 'chars';
    const RANDOM_MIXED = 'mixed';
    const RANDOM_HEX = 'hex';

    private static $seedDone = false;
    private static $fileOwner = null;
    private static $fileGroup = null;

    // keep track of mock
    private static $mock = false;


}
