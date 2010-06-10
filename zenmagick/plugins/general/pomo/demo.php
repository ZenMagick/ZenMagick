<?php

class FOMO {
  public function onZMBootstrapDone($args) {
      ZMSettings::set('zenmagick.core.locale.provider', 'PomoLocale');
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
          print sprintf(_zmn("%d pig went to the market\n", 
                $number, "%d pigs went to the market\n"), 
             $number );
        }
        print "</pre>\n";
  }

}

ZMEvents::instance()->attach(new FOMO());
