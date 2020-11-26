<?php

class TemplateManager extends Service
{
    private $templateDir;
    private $moduleName;

    public function onRegister()
    {
        parent::onRegister();

        $this->fileManager = $this->get('fileManager');
    }

    public function setTemplateDir(string $templateDir)
    {
        if (!is_dir(ROOT_DIR . '/' . $templateDir)) {
            echo "Unable to open $templateDir!";
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
        foreach ($this->fileManager->files($this->templateDir) as $path) {
            $fileContents = $this->formatFileContents($this->fileManager->read($path));

            $pathToSave = $this->resolvePathToStore($path);

            if ($this->fileManager->create($pathToSave, $fileContents) === false)
                $output .= "There was a problem (permissions?) creating the file $pathToSave\n";
            else
                $output .= "Creating file - $pathToSave\n";
        }


        return $output;
    }

    public function deleteResources()
    {
        $output = "";
        foreach ($this->fileManager->files($this->templateDir) as $path)
            $output .= $this->deleteFile($path) . PHP_EOL;

        return $output;
    }

    // private function getFileContents($path)
    // {
    //     $contents = $this->formatFileContents(file_get_contents($path));
    //     return $contents;
    // }

    // private function copyFile($path)
    // {

    // }

    private function deleteFile($path)
    {
        $pathToDelete = $this->resolvePathToStore($path);

        if (!$this->fileManager->delete($pathToDelete)) {
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
        $path =  str_replace(ROOT_DIR . '/' . $this->templateDir . '/', '', $path);

        $path = str_replace(['__dir__', '__file__'], $this->moduleName, $path);

        $pathParts = explode('/', $path);
        $filename = array_pop($pathParts);

        $storeDir = Environment::get('STORE_DIR') . implode('/', $pathParts);

        if (!is_dir($storeDir))
            mkdir($storeDir, 0775, true);

        $path = "$storeDir/$filename";

        return $path;
    }
}
