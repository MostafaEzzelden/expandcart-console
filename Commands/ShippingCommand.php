<?php


class ShippingCommand extends Command
{
    public function help(array $arguments)
    {
        $output = "- shipping\n";
        $output .= "\tcreate:shipping\t\tcreate new shipping example: console create:shipping [shipping_name]\n";
        $output .= "\tdelete:shipping\t\tdelete shipping example: console delete:shipping [shipping_name]";

        return $output;
    }

    public function create($arguments)
    {

        $moduleName = isset($arguments[0]) ? trim($arguments[0]) : null;
        if (empty($moduleName) || $moduleName == "--help")
            return $this->help($arguments);

        $templateManager = $this->get('templateManager');

        $output = $templateManager->setTemplateDir('shipping')
            ->setModuleName($moduleName)
            ->createResources();

        $output .= PHP_EOL . 'Successfully created ' . $moduleName . ' shipping.';
        return $output;
    }

    public function delete($arguments)
    {
        $moduleName = isset($arguments[0]) ? trim($arguments[0]) : null;
        if (empty($moduleName) || $moduleName == "--help")
            return $this->help($arguments);

        $templateManager = $this->get('templateManager');

        $output = $templateManager->setTemplateDir('shipping')
            ->setModuleName($moduleName)
            ->deleteResources();

        $output .= PHP_EOL . 'Successfully deleted ' . $moduleName . ' shipping.';
        return $output;
    }
}
