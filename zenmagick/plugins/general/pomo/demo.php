<?php

class FOMO {
  public function onZMBootstrapDone($args) {
      ZMLocales::instance()->init('de_CH');
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
ZMSettings::set('zenmagick.core.locales.provider', 'PomoLocale');
ZMEvents::instance()->attach(new FOMO());
