<?php

namespace GetOpt;

class Translator
{
    /** @var string */
    protected $languageFile;

    /** @var array */
    protected $translations;

    public function __construct($language = 'en')
    {
        $this->setLanguage('en');
    }

    /**
     * Translate $key
     *
     * Returns the key if no translation is found
     *
     * @param string $key
     * @return string
     */
    public function translate($key)
    {
        if ($this->translations === null) {
            $this->loadTranslations();
        }

        return !isset($this->translations[$key]) ? $key : $this->translations[$key];
    }

    /**
     * Set the language to $language
     *
     * The language can either be a known language from resources/localization (feel free to contribute your language)
     * or a path to a file that returns an array like the files in resources/localization.
     *
     * @param string $language
     * @return bool Whether the language change was successful
     */
    public function setLanguage($language)
    {
        $languageFile = file_exists($language) ?
            $language : __DIR__ . '/../resources/localization/' . $language . '.php';
        if (!file_exists($languageFile)) {
            return false;
        }

        if ($this->languageFile != $languageFile) {
            $this->translations = null;
        }

        $this->languageFile = $languageFile;
        return true;
    }

    /**
     * Load the current languageFile
     */
    protected function loadTranslations()
    {
        $this->translations = include $this->languageFile;
    }
}
