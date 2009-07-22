<?php

class MetaTags extends ZMMetaTags {
    public function getDescription($echo=ZM_ECHO_DEFAULT) {
        if ('index' == ZMRequest::instance()->getRequestId()) {
            $desc = 'My custom description bla bla';
            if ($echo) echo $desc;
            return $desc;
        }
        return parent::getDescription($echo);
    }
}

?>
