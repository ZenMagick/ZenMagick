<?php

/*
 */

/**
 * SwiftMailer Logger adaper for ZenMagick.
 */
class ZMSwiftLogger implements Swift_Plugins_Logger {
  
    /**
     * {@inheritDoc}
     */
    public function add($entry) {
        ZMLogging::instance()->log($entry, ZMLogging::TRACE);
    }
  
    /**
     * {@inheritDoc}
     */
    public function clear() { }
  
    /**
     * {@inheritDoc}
     */
    public function dump() { }
  
}
