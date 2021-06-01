<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Inflators\JSON;

use Error;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Filters\Entities\CustomFilter;

/**
 * CustomFilters inflator used to inflate filter instances
 *    by traversing a given json
 */
class CustomFiltersInflator implements IInflator
{
    private function __construct()
    {
    }


    public static function inflate(
        array $item,
        CustomFilter $customFilter,
    ): void {
        $customFilter->setId($item['id']);

        foreach ($item['text'] as $langCode => $langText) {
            if (!Language::isSupportedLanguage($langCode)) {
                throw new Error('Unsupported lang code in custom filters: ' . $langCode);
            }

            $customFilter->setLangText($langCode, $langText);
        }

        if (isset($item['matchers'])) {
            foreach ($item['matchers'] as $matcher) {
                $customFilter->addFilter($matcher);
            }
        }

        if (isset($item['children'])) {
            foreach ($item['children'] as $child) {
                $childCustomFilter = new CustomFilter();
                self::inflate($child, $childCustomFilter);
                $customFilter->addChild($childCustomFilter);
            }
        }
    }
}
