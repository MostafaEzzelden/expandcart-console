<?php

use ExpandCart\Foundation\Providers\Extension;

class ModelShipping__class__ extends Model
{
    /**
     * get module settings
     *
     * @return array
     */
    public function getSettings()
    {
        return array_merge($this->getDefaultSettings(), $this->config->get('__name__') ?? []);
    }

    /**
     * get default settings
     *
     * @return array
     */
    private function getDefaultSettings()
    {
        return [
            'status' => 0,
            'email' => null,
            'password' => null,
        ];
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

        $this->model_setting_setting->insertUpdateSetting('__name__', ['__name__' => $inputs]);
        return true;
    }

    /**
     *   Install the required values for the application.
     *
     *   @return boolean __name__ install successful or not.
     */
    public function install($store_id = 0)
    {
        try {
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *   Remove the values from the database.
     *
     *   @return boolean __name__ uninstall successful or not.
     */
    public function uninstall($store_id = 0, $group = '__name__')
    {
        try {
            return true;
        } catch (Exception $th) {
            return false;
        }
    }

    /**
     * Check if app installed
     *
     * @return boolean __name__ install or not.
     */
    public function isInstalled()
    {
        return Extension::isInstalled('__name__');
    }

    /**
     * Check if app active
     *
     * @return boolean __name__ is installed and status is on or not
     */
    public function isActive()
    {
        return $this->isInstalled() && (int) $this->getSettings()['status'] === 1;
    }

    public function createShipment($order)
    {
        # code...
    }

    public function getShipmentDetails($order_id)
    {
        # code...
    }
}
