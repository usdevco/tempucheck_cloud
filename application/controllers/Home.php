<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CRM_Controller
{
    public function __construct()
    {
        parent::__construct();
        do_action('after_clients_area_init', $this);
    }

    public function index()
    {

        $data['is_home'] = true;

        $data['title'] = 'Home';
        $this->data    = $data;
        $this->load->view('Home', $data);
    }

}
