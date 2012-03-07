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
?>
<?php
namespace zenmagick\base;

use Exception;
use zenmagick\base\Runtime;
use zenmagick\base\logging\Logging;

/**
 * Exception base class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMException extends Exception {

    /**
     * Create new instance.
     *
     * @param string message The message; default is <code>null</code>.
     * @param int code The exception code; default is <em>0</em>.
     * @param Exception previous The original exception (if any) for chaining; default is <code>null</code>.
     */
    public function __construct($message=null, $code=0, $previous=null) {
        parent::__construct((string)$message, (int)$code, $previous);
        Runtime::getLogging()->trace($message, Logging::TRACE);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() {
        $s =  '['.get_class($this);
        $s .= ' message='.$this->getMessage();
        $s .= ', file='.Runtime::getContainer()->get('filesystem')->makePathRelative($this->getFile(), Runtime::getInstallationPath());
        $s .= ', line='.$this->getLine();
        $s .= ', previous='.$this->getPrevious();
        $s .= ']';
        return $s;
    }

}
