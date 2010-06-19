<?php

class FOMO {
  public function onZMBootstrapDone($args) {
      $locale = ZMLocales::instance()->getLocale();
      if (null != $locale && $locale instanceof ZMPomoLocale) {
          echo 'register MO...';
          $locale->registerMOForLocale(dirname(__FILE__), 'de_CH', 'messages.mo');
      }
  }

  public function onZMInitDone($args) {
        print "<pre>";
        print _zm("This is how the story goes.\n\n");
        for ($number=6; $number>=0; $number--) {
          // get translated string
          $ts = _zmn("%d pig went to the market\n", $number, "%d pigs went to the market\n");
          print sprintf($ts, $number);
        }
        print "</pre>\n";
  }

}

// do this ASAP
ZMSettings::set('zenmagick.core.locale.provider', 'PomoLocale');
ZMEvents::instance()->attach(new FOMO());
