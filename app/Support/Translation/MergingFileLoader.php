<?php

namespace App\Support\Translation;

use Illuminate\Translation\FileLoader;

class MergingFileLoader extends FileLoader
{
    /**
     * Load the messages for the given locale, merging any subdirectory files
     * into the group so dot notation works transparently.
     *
     * Given a group "app", the flat file "app.php" is merged recursively with
     * the homonymous directory "app/": each .php file becomes a key and each
     * subdirectory becomes a nested level.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string|null  $namespace
     * @return array<string, mixed>
     */
    public function load($locale, $group, $namespace = null)
    {
        $lines = parent::load($locale, $group, $namespace);

        if ($namespace !== null && $namespace !== '*') {
            return $lines;
        }

        if ($group === '*') {
            return $lines;
        }

        foreach ($this->paths as $path) {
            $directory = "{$path}/{$locale}/{$group}";

            if ($this->files->isDirectory($directory)) {
                $lines = array_replace_recursive($lines, $this->loadDirectory($directory));
            }
        }

        return $lines;
    }

    /**
     * Recursively load every translation file within the given directory.
     *
     * @return array<string, mixed>
     */
    protected function loadDirectory(string $directory): array
    {
        $lines = [];

        foreach ($this->files->directories($directory) as $subdirectory) {
            $lines[basename($subdirectory)] = $this->loadDirectory($subdirectory);
        }

        foreach ($this->files->files($directory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $key = $file->getFilenameWithoutExtension();

            $contents = $this->files->getRequire($file->getPathname());

            if (is_array($contents)) {
                $lines[$key] = array_replace_recursive($lines[$key] ?? [], $contents);
            }
        }

        return $lines;
    }
}
