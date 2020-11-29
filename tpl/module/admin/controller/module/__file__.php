<?php
class ControllerModule__class__ extends Controller
{

    /**
     * @var Class __class__ model
     */
    private $module;

    /**
     * Errors bag
     *
     * @var array
     */
    private $errorsBag = [];

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->module = $this->load->model("module/__name__", ['return' => 1]);
    }

    public function install()
    {
        $this->module->install();
    }

    public function uninstall()
    {
        $this->module->uninstall();
    }

    public function index()
    {
        $this->language->load('module/__name__');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/__name__', '', 'SSL'),
            'separator' => ' :: '
        );

        if ($this->module->isInstalled()) {
            $data['settings'] = $this->module->getSettings();
        }

        $this->template = 'module/__name__.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/__name__/updateSettings', '', 'SSL');

        $data['cancel'] = $this->url . 'marketplace/home';

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/__name__');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->errorsBag;
        } else {
            if (!$this->validateForm($this->request->post)) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errorsBag;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            $this->module->updateSettings(['__name__' => $this->request->post]);
            $this->session->data['success'] = $this->language->get('text_settings_success');
            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }

        $this->response->setOutput(json_encode($result_json));

        return;
    }

    private function validateForm($data)
    {
        if (empty($data['url'])) $this->errorsBag['url'] = $this->language->get('error_invalid_url');
        if (empty($data['database'])) $this->errorsBag['database'] = $this->language->get('error_invalid_database');
        if (empty($data['username'])) $this->errorsBag['username'] = $this->language->get('error_invalid_username');
        if (empty($data['password'])) $this->errorsBag['password'] = $this->language->get('error_invalid_password');
        if ($this->errorsBag && !isset($this->errorsBag['error'])) $this->errorsBag['warning'] = $this->language->get('error_warning');

        return empty($this->errorsBag);
    }
}
