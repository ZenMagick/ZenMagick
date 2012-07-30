<?php
namespace zenmagick\themes\test\http\tools;

use zenmagick\apps\storefront\http\tools\ToolboxMetaTags as ToolboxMetaTags;

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
