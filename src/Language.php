<?php

namespace CranachDigitalArchive\Importer;

/**
 * Language helper and class for kind of enum language code encoding
 */
final class Language
{
    const DE = 'de';
    const EN = 'en';

    private $translations;

    private function __construct()
    {
    }

    public static function translate(string $text, $translations, $destLangCode)
    {
        $translationKeys = array_keys($translations);
        $translationKeysAsPatterns = array_map(
            function ($value) {
                return '/' . $value . '/i';
            },
            $translationKeys,
        );

        $result = preg_replace_callback(
            $translationKeysAsPatterns,
            function ($matches) use ($translations, $destLangCode) {
                $match = $matches[0];

                return isset($translations[$match][$destLangCode])
                    ? $translations[$match][$destLangCode]
                    : $match;
            },
            $text,
        );

        return is_null($result) ? $text : $result;
    }
}
