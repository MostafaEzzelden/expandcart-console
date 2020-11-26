<?php

class FileManager extends Service
{
    /**
     * @return \Generator|string[]
     */
    public function files($dirname)
    {
        $scanDir = ROOT_DIR . '/' . $dirname;

        $handle = opendir($scanDir);

        if (!$handle) return [];

        while (($fileItem = readdir($handle)) !== false) {
            // skip '.' and '..'
            if (($fileItem == '.') || ($fileItem == '..')) continue;

            $file = rtrim($scanDir, '/') . '/' . $fileItem;

            // if dir found call again recursively
            if (is_dir($file)) {
                foreach ($this->files($dirname . '/' . $fileItem) as $file) {
                    yield trim($file);
                }
            } else {
                yield trim($file);
            }
        }

        closedir($handle);
    }

    public function read($path)
    {
        return file_get_contents($path);
    }

    public function create($path, $contents)
    {
        return file_put_contents($path, $contents);
    }

    public function delete($path)
    {
        return is_file($path) && unlink($path) !== false;
    }
}
