<?php

class TemplateManager extends Service
{
    const ROOT_TEMPLATE_DIR = ROOT_DIR . '/tpl';

    protected $templateDir;

    protected $moduleName;


    public function setTemplateDir(string $templateDir)
    {
        if (!is_dir($dir = self::ROOT_TEMPLATE_DIR . '/' . $templateDir)) {
            echo "Template not found $dir!";
            exit;
        }

        $this->templateDir = $templateDir;

        return $this;
    }

    public function setModuleName(string $moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }


    public function createResources()
    {
        $output = "";
        foreach ($this->files($this->templateDir) as $path) {
            $output .= $this->copyFile($path) . PHP_EOL;
        }

        return $output;
    }

    public function deleteResources()
    {
        $output = "";
        foreach ($this->files($this->templateDir) as $path) {
            $output .= $this->deleteFile($path) . PHP_EOL;
        }

        return $output;
    }

    /**
     * @return \Generator|string[]
     */
    private function files($dirname)
    {
        $scanDir = self::ROOT_TEMPLATE_DIR . '/' . $dirname;

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

        return "Creating file - $pathToSave";
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
        $path =  str_replace(self::ROOT_TEMPLATE_DIR . '/' . $this->templateDir . '/', '', $path);

        $path = str_replace(['__dir__', '__file__'], $this->moduleName, $path);

        $pathParts = explode('/', $path);
        $filename = array_pop($pathParts);

        $storeDir = Helper::getEnv('STORE_DIR') . implode('/', $pathParts);

        if (!is_dir($storeDir))
            mkdir($storeDir, 0775, true);

        $path = "$storeDir/$filename";

        return $path;
    }
}
