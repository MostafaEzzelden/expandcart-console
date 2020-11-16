<?php

use ExpandCart\Foundation\Support\Facades\Url;

class ControllerShipping__class__ extends Controller
{

    protected $errors = [];

    public function install()
    {
        $this->load->model('shipping/__name__');
        $this->model_shipping___name__->install();
    }

    public function uninstall()
    {
        $this->load->model('shipping/__name__');
        $this->model_shipping___name__->uninstall();
    }

    public function index()
    {
        $this->load->language('shipping/__name__');

        $this->initializer([
            'shipping/__name__',
            'localisation/geo_zone'
        ]);

        if ($this->request->isPost()) {
            return $this->updateSettings();
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => Url::addPath(['common', 'home'])->format(),
                'separator' => false
            ],
            [
                'text' => $this->language->get('text_shipping'),
                'href' => Url::addPath(['extension', 'shipping'])->format(),
                'separator' => ' :: '
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => Url::addPath(['shipping', '__name__'])->format(),
                'separator' => ' :: '
            ],
        ];


        $this->data['settings'] = $this->model_shipping___name__->getSettings();

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $this->template = 'shipping/__name__/shipment/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );


        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        $this->initializer(['shipping/__name__']);
        $this->load->language('shipping/__name__');


        $validateForm = function () {
            if (!$this->user->hasPermission('modify', 'shipping/__name__'))
                $this->errors['error'] = $this->language->get('error_permission');

            $request = $this->request->post['settings'];

            if (!$request['email'] || empty($request['email']))
                $this->errors['settings_email'] = $this->language->get('error_email_required');

            if (!$request['password'] || empty($request['password']))
                $this->errors['settings_password'] = $this->language->get('error_password_required');

            if ($this->errors && !isset($this->errors['error']))
                $this->errors['warning'] = $this->language->get('error_warning');

            return $this->errors ? false : true;
        };

        if ($this->request->isPost()) {

            if ($validateForm()) {
                $this->model_shipping___name__->updateSettings($this->request->post['settings']);
                $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;
            }
        } else {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->errors;
        }

        $this->response->setOutput(json_encode($result_json));
    }

    public function shipment()
    {
        $this->load->model('sale/order');
        $this->load->model('shipping/__name__');
        $this->load->model('localisation/language');
        $this->language->load('shipping/__name__');

        $order_id = $this->request->get['order_id'] ?? 0;

        $order_info = $this->model_sale_order->getOrder($order_id);

        // order not found
        if (!$order_info) {
            $this->language->load('error/not_found');
            $this->document->setTitle($this->language->get('heading_title'));
            $this->data['heading_title'] = $this->language->get('heading_title');
            $this->data['text_not_found'] = $this->language->get('text_not_found');
            $this->data['breadcrumbs'] = array();
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', '', 'SSL'),
                'separator' => false
            );
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link('error/not_found', '', 'SSL'),
                'separator' => ' :: '
            );

            $this->template = 'error/not_found.expand';
            $this->base = "common/base";
            $this->response->setOutput($this->render());
            return false;
        } // end order not found

        $shipmentDetails = $this->model_shipping___name__->getShipmentDetails($order_id);

        $shipping_address = [
            $order_info['shipping_address_1'] ?? $order_info['payment_address_1'],
            $order_info['shipping_address_2'] ?? $order_info['payment_address_2'],
            $order_info['shipping_zone'] ?? $order_info['payment_zone'],
            $order_info['shipping_city'] ?? $order_info['payment_city'],
            $order_info['shipping_country'] ?? $order_info['payment_country']
        ];

        $this->data['shipping_address'] = implode(', ', array_filter($shipping_address));

        $order_info['total'] = (float) $order_info['total'];


        $this->data['create_shipment_link'] = $this->url->link('shipping/__name__/createShipment', '', 'SSL');
        $this->data['cancel_shipment_link'] = $this->url->link('shipping/__name__/cancelShipment', '', 'SSL');
        $this->data['order_status_update_link'] = $this->url->link('shipping/__name__/updateOrderStatus', '', 'SSL');

        $this->data['settings'] = $this->model_shipping___name__->getSettings();
        $this->data['order_info'] = $order_info;
        $this->data['products'] = $this->model_sale_order->getOrderProducts($order_id);
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        if (!empty($shipmentDetails)) $this->data['shipment_details'] = $shipmentDetails;

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get(empty($shipmentDetails) ? 'create_shipment' : 'update_shipment'),
            'href'      => $this->url->link('sale/order', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'shipping/__name__/shipment/create.expand';
        $this->children = array(
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render_ecwig());
        return false;
    }

    public function createShipment()
    {

        if (!$this->request->isPost()) {
            $this->response->setOutput(json_encode(['success' => 0, 'errors' => ['invalid request']]));
            return;
        }

        $data = $this->request->post;

        $this->load->language('shipping/__name__');

        $validateShipmentInputs = function ($data) {
            return empty($this->errors);
        };

        if (!$validateShipmentInputs($data)) {
            $errorsData = ['warning' => $this->language->get('error_inputs_validation')];
            $errorsData = array_merge($errorsData, $this->errors);
            $this->response->setOutput(json_encode(['errors' => $errorsData]));
            return;
        }

        // valid input data
        $this->initializer(['shipping/__name__']);

        if ($this->model_shipping___name__->createShipment($data)) {
            $this->response->setOutput(json_encode([
                'success' => 1,
                'success_msg' => '',
                'redirect' => 1,
                'to' => $this->request->server['HTTP_REFERER']
            ]));
            return;
        }

        $this->response->setOutput(json_encode(['errors' => ['warning' => $this->language->get('error_api')]]));

        return;
    }

    public function cancelShipment()
    {
        # code...
    }

    public function updateOrderStatus()
    {
        # code...
    }
}
