<?php
namespace core\util;

/**
 * Internationalization
 *
 * @author rogeriolino
 */
class I18n {
    
    const LOCALE_DIR = 'locale';
    const DEFAULT_LOCALE = 'pt_BR';
    
    private static $locale;
    
    public static function locale() {
        if (!self::$locale) {
            self::$locale = self::DEFAULT_LOCALE;
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $langs = array();
                preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
                if (sizeof($lang_parse[1])) {
                    $langs = array_combine($lang_parse[1], $lang_parse[4]);
                    foreach ($langs as $lang => $val) {
                        if ($val === '') {
                            $langs[$lang] = 1;
                        }
                    }
                    arsort($langs, SORT_NUMERIC);
                }
                if (sizeof($langs)) {
                    $lang = explode('-', current(array_keys($langs)));
                    $accept = $lang[0] . '_';
                    if (sizeof($lang) > 1) {
                        $accept .= $lang[1];
                    } else {
                        $accept .= strtoupper($lang[0]);
                    }
                    self::$locale = $accept;
                }
            }
            self::$locale .= ".utf8";
        }
        return self::$locale;
    }
    
    public static function bind() {
        $locale = self::locale();
        setlocale(LC_MESSAGES, $locale);
        putenv("LANG={$locale}");
        bindtextdomain("default", ROOT . DS . self::LOCALE_DIR);
        textdomain("default");
        bind_textdomain_codeset("default", "UTF-8");
    }
    
}
