<?php

class I18nOptions {
    private static function doListLocaleFiles($path) {
        $dir = dirname(__DIR__) . "/{$path}";
        if (!file_exists($dir)) {
            return array();
        }
        $it  = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $it->setMaxDepth(1);

        $langs = array();
        foreach ($it as $fileInfo) {
            if ($fileInfo->isFile()) {
                $filename = $fileInfo->getFilename();
                $filename = str_replace('.php', '', $filename);
                $langs[] = $filename;
            }
        }
        return $langs;
    }

    private static function doListLangs($path) {
        $dir = dirname(__DIR__) . "/{$path}";
        if (!file_exists($dir)) {
            return array();
        }
        $it  = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $it->setMaxDepth(1);

        $langs = array();

        foreach ($it as $fileInfo) {
            if ($fileInfo->isFile()) {
                $file = $fileInfo->getPathname();
                $filename = $fileInfo->getFilename();
                $filename = str_replace('.php', '', $filename);
                if (is_readable($file)) {
                    $className = str_replace('/', '_', $path) . '_' . $filename;
                    if (!class_exists($className)) {
                        require_once($file);
                    }
                    if (class_exists($className)) {
                        $lang = new $className();
                    } else {
                        $lang = null;
                    }

                    if (is_subclass_of($lang, "Lang")) {
                        if (method_exists($lang, "locale")) {
                            $locale = $lang->locale();
                            if (!empty($locale) && method_exists($lang, "name")) {
                                $name = $lang->name();
                                if (!empty($name)) {
                                    $langs[$locale] = $name;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $langs;
    }

    public static function listLangs() {
        $lang = array('auto'=>'Auto');
        $langs = array_merge($lang, self::doListLangs("lang"), self::doListLangs("usr/lang"));
        return $langs;
    }

    public static function listLocaleFiles($isSettingsPage = false) {
        $lang = array();
        if ($isSettingsPage) {
            $langs = array_merge($lang, self::doListLocaleFiles("lang/settings"));
        } else {
            $langs = array_merge($lang, self::doListLocaleFiles("lang"), self::doListLocaleFiles("usr/lang"));
        }
        return array_unique($langs);
    }
}