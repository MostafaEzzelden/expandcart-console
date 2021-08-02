<?php

class TemplateManager extends Service
{
    private $templateDir;

    private $moduleName;

    public function onRegister()
    {
        parent::onRegister();
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
        $output = [];

        foreach ($this->container->fileManager->files($this->templateDir) as $path) {

            $pathToSave = $this->resolvePathToStore($path);

            if ($this->container->fileManager->has($pathToSave)) {
                continue;
            }

            $fileContents = $this->formatFileContents($this->container->fileManager->read($path));

            if ($this->container->fileManager->create($pathToSave, $fileContents) === false) {
                $this->container->logger->warning("There was a problem (permissions?) creating the file $pathToSave");
                continue;
            }

            $output[] = "Creating file - $pathToSave";
        }

        return implode(PHP_EOL, $output);
    }

    public function deleteResources()
    {
        $output = [];

        foreach ($this->container->fileManager->files($this->templateDir) as $path) {
            if ($success = $this->deleteFile($path)) {
                $output[] = $success;
            }
        }

        return implode(PHP_EOL, $output);
    }

    private function deleteFile($path)
    {
        $pathToDelete = $this->resolvePathToStore($path);

        if (!$this->container->fileManager->delete($pathToDelete)) {
            $this->container->logger->warning("Unable to delete the file " . $pathToDelete);
            return false;
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
