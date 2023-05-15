<?php

namespace CranachDigitalArchive\Importer;

use Iterator;
use InvalidArgumentException;
use CranachDigitalArchive\Importer\Language;

/**
 * Collection class to hold item instances for each supported language
 *
 * @template T
 */
abstract class AbstractItemLanguageCollection implements Iterator
{
    /** @var T[] */
    private $collection = [];

    /** @var string[] */
    private $langCodeKeys;

    private function __construct()
    {
        $this->langCodeKeys = Language::getSupportedLanguages();

        foreach ($this->langCodeKeys as $langCode) {
            $this->collection[$langCode] = $this->createItem();
        }
    }


    /**
     * Creator function
     *
     * @return     static  A new container instance
     */
    public static function create(): static
    {
        return new static();
    }


    /**
     * Create new item instance
     *
     * @return     T                     New item instance
     */
    abstract protected function createItem();


    /**
     * Set a painting for a supported language
     *
     * @param      string                $langCode  The language code
     * @param      T                     $item      The item
     *
     * @throws     InvalidArgumentException  Thrown on unsupported language
     */
    public function set(string $langCode, mixed $item): void
    {
        if (!Language::isSupportedLanguage($langCode)) {
            throw new InvalidArgumentException('Unsupported langCode \'' . $langCode . '\'');
        }

        $this->collection[$langCode] = $item;
    }

    /**
     * Gets the painting for the requested language
     *
     * @param      string                    $langCode  The language code
     *
     * @throws     InvalidArgumentException  Thrown on unsupported language
     *
     * @return     T                         Item instance for the requested language
     */
    public function get(string $langCode)
    {
        if (!Language::isSupportedLanguage($langCode)) {
            throw new InvalidArgumentException('Unsupported langCode \'' . $langCode . '\'');
        }

        return $this->collection[$langCode];
    }

    /**
     * Return the first item instance
     *
     * @return     T  First item instance
     */
    public function first()
    {
        return current($this->collection);
    }


    /**
     * Check if a given language code is supported
     *
     * @param      string  $langCode  The language code to be checked
     *
     * @return     bool    The language code is supported
     */
    public function supportsLanguageCode(string $langCode): bool
    {
        return in_array($langCode, $this->langCodeKeys, true);
    }

    /* Iterator methods */

    /**
     * @return     T  Current item instance
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->collection[current($this->langCodeKeys)];
    }

    public function key(): string
    {
        return current($this->langCodeKeys);
    }

    public function next(): void
    {
        next($this->langCodeKeys);
    }

    public function rewind(): void
    {
        reset($this->langCodeKeys);
    }

    public function valid(): bool
    {
        return isset($this->collection[current($this->langCodeKeys)]);
    }
}
