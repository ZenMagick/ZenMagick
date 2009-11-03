<?php

class MetaTags extends ZMMetaTags {
    public function getDescription($echo=ZM_ECHO_DEFAULT) {
        if ('index' == $this->getRequest()->getRequestId()) {
            $desc = 'My custom description bla bla';
            if ($echo) echo $desc;
            return $desc;
        }
        return parent::getDescription($echo);
    }
}

?>
