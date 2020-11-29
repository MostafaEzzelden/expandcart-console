<?php

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Inventory\Clients\Odoo;

class ModelModuleOdoo extends Model
{

    private $inventory;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->setInventory(new Inventory(new Odoo, $this->getSettings()));
    }

    /**
     * default app settings
     *
     * @return array
     */
    private function getDefaultSettings()
    {
        return [
            'status' => 0,
            'url' => null,
            'database' => null,
            'username' => null,
            'password' => null,
        ];
    }

    /**
     * Get the value of inventory
     */
    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    /**
     * Set the value of inventory
     *
     * @return  self
     */
    public function setInventory(Inventory $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'odoo',
            $inputs
        );

        return true;
    }

    /**
     * app settings
     *
     * @return array app settings
     */
    public function getSettings()
    {
        return array_merge(
            $this->getDefaultSettings(),
            $this->config->get('odoo') ?? []
        );
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return Extension::isInstalled('odoo');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        $settings = $this->getSettings();

        return $this->isInstalled()
            && (int) $settings['status'] === 1
            && $settings['url']
            && $settings['database']
            && $settings['username']
            && $settings['password'];
    }

    /**
     *   Install the required values for the application.
     *
     *   @return boolean whether successful or not.
     */
    public function install($store_id = 0)
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "odoo_products` (
                `product_id` INT UNSIGNED NOT NULL,
                `odoo_product_id` VARCHAR(255) NOT NULL
            )");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *   Remove the values from the database.
     *
     *   @return boolean whether successful or not.
     */
    public function uninstall($store_id = 0, $group = 'odoo')
    {
        try {

            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "odoo_products`");

            return true;
        } catch (Exception $th) {
            return false;
        }
    }
}
