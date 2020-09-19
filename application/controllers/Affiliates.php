<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Affiliates extends Clients_controller
{
    public function __construct()
    {
        parent::__construct();
        do_action('after_clients_area_init', $this);
    }

    public function index()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('clients/login'));
        }
        $data['is_home'] = true;
        $this->load->model('reports_model');
        $data['payments_years'] = $this->reports_model->get_distinct_customer_invoices_years();

        $data['project_statuses'] = $this->projects_model->get_project_statuses();
        $data['title'] = get_company_name(get_client_user_id());
        $this->data    = $data;
        $this->view    = 'home';
        $this->layout();
    }

    public function register()
    {

        $this->form_validation->set_rules('firstname', _l('client_firstname'), 'required');
        $this->form_validation->set_rules('lastname', _l('client_lastname'), 'required');
        $this->form_validation->set_rules('email', _l('client_email'), 'trim|required|is_unique[tblstaff.email]|valid_email');
        $this->form_validation->set_rules('password', _l('clients_register_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('clients_register_password_repeat'), 'required|matches[password]');
        $this->form_validation->set_rules('terms_condition', 'terms and conditions', 'required');
        

        if (get_option('use_recaptcha_customers_area') == 1 && get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '') {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }

        if ($this->input->post()) {

            if ($this->form_validation->run() !== false) {

                $data = $this->input->post();
                // Unset recaptchafield
                if (isset($data['g-recaptcha-response'])) {
                    unset($data['g-recaptcha-response']);
                }

                // $data['email_signature'] = nl2br_save_html($data['email_signature']);
                $this->load->helper('phpass');
                $hasher              = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
                $data['password']    = $hasher->HashPassword($data['password']);
                $data['datecreated'] = date('Y-m-d H:i:s');
                $data['role'] = 7;
                if (isset($data['passwordr'])) {
                    unset($data['passwordr']);
                }

                // echo '<pre>';print_r($this->input->post());die;
                // echo '<pre>';print_r($data);die;
                $staffid = $this->db->insert('tblstaff', $data);
                // print_r($staffid);die;

                if ($staffid) {
                    // do_action('after_client_register', $clientid);
                    $redUrl = site_url();

                    set_alert('warning', _l('clients_account_created_but_not_logged_in'));
                    redirect(site_url('admin'));
                }
            }
            // die("After IF");

        } else {
            // die("Asd");
        }

        $data['title']     = _l('clients_register_heading');
        $data['bodyclass'] = 'register';
        $this->data        = $data;
        $this->view        = 'affiliate_register';
        $this->layout();
    }

}
