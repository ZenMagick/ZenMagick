<?php
namespace ZenMagick\themes\test\http\tools;

use ZenMagick\apps\storefront\Http\Tools\ToolboxMetaTags as ToolboxMetaTags;

class XToolboxMetaTags extends ToolboxMetaTags {
    public function getDescription() {
        if ('index' == $this->getRequest()->getRequestId()) {
            $desc = 'My custom description bla bla';
            return $desc;
        }
        return parent::getDescription();
    }
}

?>
