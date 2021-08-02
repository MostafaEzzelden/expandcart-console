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
        if (empty($moduleName) || $moduleName == "--help") return $this->help($arguments);

        $output = $this->container->templateManager->setTemplateDir('tpl/shipping')->setModuleName($moduleName)->createResources();

        if ($output) {
            $output .= PHP_EOL . 'Successfully created ' . $moduleName . ' shipping.';
        } else {
            $output = PHP_EOL . 'Shipping ' . $moduleName . ' already exist!';
        }

        return $output;
    }

    public function delete($arguments)
    {
        $moduleName = isset($arguments[0]) ? trim($arguments[0]) : null;
        if (empty($moduleName) || $moduleName == "--help")
            return $this->help($arguments);

        $output = $this->container->templateManager->setTemplateDir('tpl/shipping')
            ->setModuleName($moduleName)
            ->deleteResources();

        if ($output) {
            $output .= PHP_EOL . 'Successfully deleted ' . $moduleName . ' shipping.';
        } else {
            $output = PHP_EOL . 'Shipping ' . $moduleName . ' not exist!';
        }
        return $output;
    }
}
