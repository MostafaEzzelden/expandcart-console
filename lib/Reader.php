<?php

class Reader
{
    protected $baseTemplateDir;

    protected $templateDir;

    protected $moduleName;

    public function __construct(string $baseTemplateDir)
    {
        $this->baseTemplateDir = $baseTemplateDir;
    }

    public function setModuleName(string $moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    public function setTemplateDir(string $templateDir)
    {
        $this->templateDir = $templateDir;

        return $this;
    }

    public function createResources()
    {
        $output = "";
        foreach ($this->getFilesInDir($this->templateDir) as $path) {
            $output .= $this->copyFile($path) . PHP_EOL;
        }

        return $output;
    }

    public function deleteResources()
    {
        $output = "";
        foreach ($this->getFilesInDir($this->templateDir) as $path) {
            $output .= $this->deleteFile($path) . PHP_EOL;
        }

        return $output;
    }

    private function getFilesInDir($dirname)
    {
        $scanDir = $this->baseTemplateDir . '/' . $dirname;

        $handle = opendir($scanDir);

        if (!$handle) return [];

        while (($fileItem = readdir($handle)) !== false) {
            // skip '.' and '..'
            if (($fileItem == '.') || ($fileItem == '..')) continue;

            $file = rtrim($scanDir, '/') . '/' . $fileItem;

            // if dir found call again recursively
            if (is_dir($file)) {
                foreach ($this->getFilesInDir($dirname . '/' . $fileItem) as $childFileItem) {
                    yield $childFileItem;
                }
            } else {
                yield $file;
            }
        }

        closedir($handle);
    }

    private function getFileContents($path)
    {
        return $this->formatFileContents(file_get_contents($path));
    }

    private function copyFile($path)
    {
        $fileContents = $this->getFileContents($path);

        $pathToSave = $this->resolvePathToStore($path);

        if (file_put_contents($pathToSave, $fileContents) === false) {
            return "There was a problem (permissions?) creating the file " . $pathToSave;
        }

        return "Creating file - $pathToSave" ;
    }

    private function deleteFile($path)
    {
        $pathToDelete = $this->resolvePathToStore($path);

        if (!is_file($pathToDelete) || unlink($pathToDelete) === false) {
            return "Unable to delete the file " . $pathToDelete;
        }

        return "Deleting file - $pathToDelete";
    }

    private function formatFileContents(string $fileContents)
    {
        // resolve class name
        $fileContents = str_replace('__class__', array_reduce(explode('_', $this->moduleName), function ($carry, $slug) {
            return $carry .= ucfirst($slug);
        }, ''), $fileContents);

        // resolve module name
        $fileContents = str_replace('__name__', $this->moduleName, $fileContents);

        return $fileContents;
    }

    private function resolvePathToStore(string $path)
    {
        $path =  str_replace($this->baseTemplateDir . '/' . $this->templateDir . '/', '', $path);

        $path = str_replace('__dir__', $this->moduleName, $path);
        $path = str_replace('__file__', $this->moduleName, $path);

        $parts = explode('/', $path);
        $filename = array_pop($parts);

        $dir = Helper::getEnv('STORE_DIR') . implode('/', $parts);

        if (!is_dir($dir))
            mkdir($dir, 0775, true);

        $path = $dir . '/' . $filename;

        return $path;
    }
}
