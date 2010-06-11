<?php
if (function_exists('bindtextdomain')) {
    define('LOCALE_DIR', dirname(__FILE__) .'/locale');
    define('DEFAULT_LOCALE', 'en_US');

    $supported_locales = array('en_US', 'sr_CS', 'de_CH');
    $encoding = 'UTF-8';

    $locale = (isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;

    // gettext setup
    setlocale(LC_MESSAGES, $locale);
    // Set the text domain as 'messages'
    $domain = 'messages';
    bindtextdomain($domain, LOCALE_DIR);
    // bind_textdomain_codeset is supported only in PHP 4.2.0+
    if (function_exists('bind_textdomain_codeset')) 
      bind_textdomain_codeset($domain, $encoding);
    textdomain($domain);


    echo LOCALE_DIR,"<BR>";
    echo $locale,"<BR>";
    print "<pre>";
    print _("This is how the story goes.\n\n");
    for ($number=6; $number>=0; $number--) {
      print sprintf(ngettext("%d pig went to the market\n", 
            "%d pigs went to the market\n", $number), 
         $number );
    }
    print "</pre>\n";
}


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
          // get translated string
          $ts = _zmn("%d pig went to the market\n", $number, "%d pigs went to the market\n");
          print sprintf($ts, $number);
        }
        print "</pre>\n";
  }

}

ZMEvents::instance()->attach(new FOMO());
