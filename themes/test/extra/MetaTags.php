<?php

class MetaTags extends ZMToolboxMetaTags {
    public function getDescription() {
        if ('index' == $this->getRequest()->getRequestId()) {
            $desc = 'My custom description bla bla';
            return $desc;
        }
        return parent::getDescription();
    }
}

?>
