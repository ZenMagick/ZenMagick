<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class File extends Constraint
{
    public $maxSize = null;
    public $mimeTypes = array();
    public $notFoundMessage = 'The file could not be found';
    public $notReadableMessage = 'The file is not readable';
    public $maxSizeMessage = 'The file is too large ({{ size }}). Allowed maximum size is {{ limit }}';
    public $mimeTypesMessage = 'The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}';
}
