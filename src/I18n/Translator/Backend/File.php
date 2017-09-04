<?php

namespace Nip\I18n\Translator\Backend;

use Nip_File_System as FileSystem;

/**
 * Nip Framework
 *
 * @category   Nip
 * @copyright  2009 Nip Framework
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version    SVN: $Id$
 */
class File extends AbstractBackend
{
    protected $variableName = 'lang';
    protected $dictionary;

    protected $baseDirectory;

    /**
     * @return mixed
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @param mixed $baseDirectory
     */
    public function setBaseDirectory($baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * @param array $languages
     */
    public function addLanguages($languages)
    {
        foreach ($languages as $language) {
            $this->addLanguage($language);
        }
    }

    public function addLanguage($language)
    {
        $directory = $this->compileLanguageDirectory($language);
        return $this->addLanguageFromPath($language, $directory);
    }

    protected function compileLanguageDirectory($lang)
    {
        return $this->getBaseDirectory() . DIRECTORY_SEPARATOR . $lang;
    }

    /**
     * Adds a language to the dictionary
     *
     * @param string $language
     * @param string $path Path to file containing translations
     * @return $this
     */
    public function addLanguageFromPath($language, $path)
    {
        $this->languages[] = $language;

        $resolvedIncludePath = stream_resolve_include_path($path);
        $fromIncludePath = ($resolvedIncludePath !== false) ? $resolvedIncludePath : $path;

        if (is_dir($fromIncludePath)) {
            $this->loadDirectory($language, $fromIncludePath);
        } elseif (is_file($fromIncludePath)) {
            $this->loadFile($language, $fromIncludePath);
        } else {
            trigger_error(
                "Language file [" . $language . "][" . $path . "][" . $fromIncludePath . "] does not exist",
                E_USER_ERROR
            );
        }

        return $this;
    }

    /**
     * @param $language
     * @param $path
     */
    public function loadDirectory($language, $path)
    {
        $files = FileSystem::instance()->scanDirectory($path, true, true);
        if (is_array($files)) {
            foreach ($files as $file) {
                if (FileSystem::instance()->getExtension($file) == 'php') {
                    $this->loadFile($language, $file);
                }
            }
        }
    }

    /**
     * @param $language
     * @param $path
     */
    protected function loadFile($language, $path)
    {
        if (file_exists($path)) {
            /** @noinspection PhpIncludeInspection */
            $messages = include $path;

            if (is_array($messages)) {
                $this->loadMessages($language, $messages);
            } else {
                trigger_error(
                    sprintf(
                        'Expected an array, but received %s [%s][%s]',
                        gettype($messages), $language, $messages
                    ),
                    E_USER_ERROR
                );
            }
        }
    }

    /**
     * @param $language
     * @param $messages
     */
    protected function loadMessages($language, $messages)
    {
        foreach ($messages as $slug => $translation) {
            if ($slug) {
                $this->dictionary[$language][$slug] = $translation;
            }
        }
    }

    /**
     * Returns dictionary entry for $slug in $language
     * @param string $slug
     * @param string|bool $language
     * @return string|bool
     */
    protected function doTranslation($slug, $language = false)
    {
        if (isset($this->dictionary[$language][$slug])) {
            return $this->dictionary[$language][$slug];
        }

        return false;
    }
}
