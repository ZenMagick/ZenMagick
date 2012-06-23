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
namespace zenmagick\apps\store\storefront\services;

use zenmagick\base\Runtime;
use zenmagick\http\messages\Messages as HttpMessageService;

/**
 * Messages to be displayed to the user.
 *
 * <p>Messages will be saved in the session if not delivered.</p>
 *
 * <p>All known zen cart query message types stored are supported.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MessageService extends HttpMessageService {

    /**
     * {@inheritDoc}
     */
    public function loadMessages($session) {
        // load our session messages
        parent::loadMessages($session);

        // also check for other messages in the request...
        $request = Runtime::getContainer()->get('request');
        // These messages come from various payment methods
        if (null != ($error = $request->query->get('error_message'))) {
            $this->error($error);
        }
        if (null != ($error = $request->query->get('credit_class_error'))) {
            $this->error($error);
        }
        if (null != ($info = $request->query->get('info_message'))) {
            $this->info($info);
        }
        if (null != ($perror = $request->query->get('payment_error'))) {
            $this->error($request->query->get('error', $perror));
        }
    }
}
