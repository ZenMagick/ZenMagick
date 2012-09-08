<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\Http\Forms;

use ZenMagick\Base\Beans;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Request;

/**
 * Basic form container.
 *
 * <p>The default implementation of <code>populate($request)</code> will just populate the form instance
 * with all request data. Custom implementations are free to override/extend <code>populate($request)</code> to hook
 * up their own population code.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class FormData extends ZMObject implements Form {

    /**
     * {@inheritDoc}
     */
    public function populate(Request $request) {
        Beans::setAll($this, $request->getParameterMap(), null);
    }

}
