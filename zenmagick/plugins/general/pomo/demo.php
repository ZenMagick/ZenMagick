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

/**
 * Locale using <em>pomo</em>.
 *
 * <p>The domain and .mo filename (without the trailing .mo) are not synonymous. This allows to
 * load (and merge) multiple files for a single domain and locale.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.misc
 * @version $Id$
 */
class ZMLocales extends ZMObject {
    const DEFAULT_DOMAIN = 'defaults';
    // loaded translations per domain and for the current locale
    private $translations_;
    private static $EMPTY_ = null;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Locales');
    }


    /**
     * Get translations for the given domain.
     *
     * @param string domain The domain name.
     * @return Translations A <code>Translations</code> instance.
     */
    protected function getTranslationsForDomain($domain) {
        if (array_key_exists($domain, $this->translations_)) {
            return $this->translations_[$domain];
        } else {
            if (null == self::$EMPTY_) {
                self::$EMPTY_ = new Translations;
            }
            return self::$EMPTY_;
        }
    }

    /**
     * Register a .mo file for a specific locale.
     *
     * @param string basedir The base directory where the .mo files are located.
     * @param string locale The locale.
     * @param string filename The actual filename without any path; default is <code>null</code> to match the domain.
     * @param string domain The translation domain; default is <code>self::DEFAULT_DOMAIN</code>.
     * @return boolean <code>true</code> on success.
     */
    public function registerMOForLocale($basedir, $locale, $filename=null, $domain=self::DEFAULT_DOMAIN) {
        $filename = null == $filename ? $domain.'.mo' : $filename;
        $path = DIRECTORY_SEPARATOR. 'locale' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $filename;
        $this->registerMO($basedir.$path, $domain);
    }

    /**
     * Register a .mo file.
     *
     * @param string filename The .mo filename.
     * @param string domain The translation domain; default is <code>self::DEFAULT_DOMAIN</code>.
     * @return boolean <code>true</code> on success.
     */
    public function registerMO($filename, $domain=self::DEFAULT_DOMAIN) {
        $mo = new MO();
        if (!$mo->import_from_file($filename)) {
            return false;
        }

        if (array_key_exists($domain, $this->translations_)) {
            $mo->merge_with($this->translations_[$domain]);
        }

        $this->translations_[$domain] = $mo;
        return true;
    }

    /**
     * Translate the given text.
     *
     * @param string text The text to translate.
     * @param mixed context Optional translation context; default is <code>null</code>.
     * @param string domain The translation domain; default is <code>self::DEFAULT_DOMAIN</code>.
     * @return string The translated text or, if no translation found, the original text.
     */
    public function translate($text, $context=null, $domain=self::DEFAULT_DOMAIN) {
      $translations = $this->getTranslationsForDomain($domain);
      return $translations->translate($text, $context);
    }

    /**
     * Translate the given text with plural option.
     *
     * @param string single The text to translate for single case.
     * @param int number The number.
     * @param string plural The text to translate for plural case; default is <code>null</code> to default to the single case.
     * @param mixed context Optional translation context; default is <code>null</code>.
     * @param string domain The translation domain; default is <code>self::DEFAULT_DOMAIN</code>.
     * @return string The translated text or, if no translation found, the original text.
     */
    public function translatePlural($single, $number, $plural=null, $context=null, $domain=self::DEFAULT_DOMAIN) {
      $plural = null == $plural ? $single : $plural;
      $translations = $this->getTranslationsForDomain($domain);
      return $translations->translate_plural($single, $plural, $number, $context);
    }

}


/**
 * Translate the given text.
 *
 * @param string text The text to translate.
 * @param mixed context Optional translation context; default is <code>null</code>.
 * @param string domain The translation domain; default is <code>ZMLocales::DEFAULT_DOMAIN</code>.
 * @return string The translated text or, if no translation found, the original text.
 */
function _zm($text, $context=null, $domain=ZMLocales::DEFAULT_DOMAIN) {
  return ZMLocales::instance()->translate($text, $context, $domain);
}


/**
 * Translate the given text with plural option.
 *
 * @param string single The text to translate for single case.
 * @param int number The number.
 * @param string plural The text to translate for plural case; default is <code>null</code> to default to the single case.
 * @param mixed context Optional translation context; default is <code>null</code>.
 * @param string domain The translation domain; default is <code>ZMLocales::DEFAULT_DOMAIN</code>.
 * @return string The translated text or, if no translation found, the original text.
 */
function _zmn($single, $number, $plural=null, $context=null, $domain=ZMLocales::DEFAULT_DOMAIN) {
  return ZMLocales::instance()->translatePlural($single, $number, $plural, $context, $domain);
}

class FOMO {
  public function onZMBootstrapDone($args) {
      $mofile = dirname(__FILE__).'/locale/de_CH/LC_MESSAGES/messages.mo';
      //ZMLocales::instance()->registerMO($mofile);
      ZMLocales::instance()->registerMOForLocale(dirname(__FILE__), 'de_CH', 'messages.mo');
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

?>
