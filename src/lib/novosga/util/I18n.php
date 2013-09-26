<?php
namespace novosga\util;

/**
 * Internationalization
 *
 * @author rogeriolino
 */
class I18n {
    
    const LOCALE_DIR = 'locale';
    const DEFAULT_LANG = 'pt';
    const DEFAULT_LOCALE = 'pt_BR';
    
    private static $lang;
    private static $locale;
    private static $locales = array(
        'pt' => array('pt_BR'),
        'en' => array('en_US'),
        'es' => array('es_ES')
    );
    
    public static function lang() {
        if (!self::$lang) {
            self::load();
        }
        return self::$lang;
    }
    
    public static function locale() {
        if (!self::$locale) {
            self::load();
        }
        return self::$locale;
    }
    
    private static function load() {
        self::$lang = self::DEFAULT_LANG;
        self::$locale = self::DEFAULT_LOCALE;
        $langs = self::acceptLanguage();
        foreach ($langs as $lang => $q) {
            $lang = explode('-', $lang);
            // se o locale esta disponivel
            if (isset(self::$locales[$lang[0]])) {
                $locales = self::$locales[$lang[0]];
                $l = $lang[0] . '_' . strtoupper(sizeof($lang) > 1 ? $lang[1] : $lang[0]);
                // se nao existir o idioma da regiao, pega o primeiro
                self::$locale = (isset($locales[$l])) ? $locales[$l] : $locales[0];
                self::$lang = $lang[0];
                break;
            }
        }
    }
    
    private static function acceptLanguage() {
        $langs = array();
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
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
        }
        return $langs;
    }
    
    public static function bind() {
        $locale = self::locale() . ".utf8";
        if (defined('LC_MESSAGES')) {
            setlocale(LC_MESSAGES, $locale);
        }
        if (defined('LC_ALL')) {
            setlocale(LC_ALL, $locale);
        }
        putenv("LANG={$locale}");
        bindtextdomain("default", PUBLIC_HTML . DS . self::LOCALE_DIR);
        textdomain("default");
        bind_textdomain_codeset("default", "UTF-8");
    }
    
}
