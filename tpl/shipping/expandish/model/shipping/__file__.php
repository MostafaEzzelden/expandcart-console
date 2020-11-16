<?php

use ExpandCart\Foundation\Providers\Extension;

class ModelShipping__class__ extends Model
{

    public function isInstalled()
    {
        return Extension::isInstalled('__name__');
    }

    public function isActive()
    {
        return $this->isInstalled() && (int)$this->getSettings()['status'] === 1;
    }

    public function getSettings()
    {
        return  $this->config->get('__name__') ?? ['status' => 0];
    }

    public function getQuote($address)
    {

        $this->language->load_json('shipping/__name__');

        $orderShipmentCost = 0;

        return [
            'code' => '__name__',
            'title' => $this->language->get('text_title'),
            'quote' => [
                '__name__' => [
                    'code' => '__name__.__name__',
                    'title' => $this->language->get('text_title'),
                    'cost' => $orderShipmentCost,
                    'tax_class_id' => 0,
                    'text' => $this->currency->format($orderShipmentCost, $this->currency->getCode())
                ]
            ],
            'sort_order' => 2,
            'error' => false
        ];
    }
}
