<?php

header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');
class Instance extends Admin_controller
{
    private $not_importable_leads_fields;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }

    /* List all leads */
    public function index()
    {
        close_setup_menu();

        if (!is_staff_member()) {
            access_denied('Applicants');
        }

        $staff_id = get_staff_user_id();
        $data['title'] = _l('Instance List');
        $data['instance_id'] = NULL;
        $data['bodyclass']     = 'company_content';
        $this->load->view('admin/instance/list', $data);    
    }

    public function table()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $this->app->get_table_data('instance');
    }


    public function add_instance ()
    {

        close_setup_menu();
        $staff_id = get_staff_user_id();
        $this->form_validation->set_rules('company_name', 'Company Name', 'required|trim');
        $this->form_validation->set_rules('instance_name', 'Instance Name', 'required|trim');
        $this->form_validation->set_rules('phone', 'Phone Number', 'required|trim|numeric');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('company_address', 'Company Address', 'required|trim');
        $this->form_validation->set_rules('city', 'City', 'required|trim');
        $this->form_validation->set_rules('state', 'State', 'trim|required');
        $this->form_validation->set_rules('zip', 'Zip Code', 'trim|numeric|required');
        $this->form_validation->set_rules('g-recaptcha-response', 'Captcha Code', 'required');


        $recaptchaResponse = trim($this->input->post('g-recaptcha-response'));
        $userIp = $this->input->ip_address();
        $captcha_status = $this->captcha($userIp, $recaptchaResponse);

        if(empty($captcha_status))
        {
            $this->form_validation->set_rules('recaptcha', 'Captcha Code', 'required');
        }

        if ($this->form_validation->run() == false) 
        {
            $this->form_validation->error_array();
        } 
        else 
        {

            $company_content = array();

            $company_content['company_name'] = $this->input->post('company_name',TRUE);
            $company_content['instance_name'] = $this->input->post('instance_name',TRUE);
            $company_content['phone'] = $this->input->post('phone',TRUE);
            $company_content['first_name'] = $this->input->post('first_name',TRUE);
            $company_content['last_name'] = $this->input->post('last_name',TRUE);
            $company_content['company_address'] = $this->input->post('company_address',TRUE);
            $company_content['city'] = $this->input->post('city',TRUE);
            $company_content['state'] = $this->input->post('state',TRUE);
            $company_content['zip'] = $this->input->post('zip',TRUE);

            $status = $this->db->insert('tblinstance',$company_content);

            if($status)
            {                
                 set_alert('success', 'Instance Inserted Successfully');
            }
            else
            {
                 set_alert('warning', 'Instance Insert Error');
            }
             redirect(base_url('admin/instance'));            
        }

        $data['title'] = _l('Add Company Details');
        $data['instance_id'] = NULL;
        $data['bodyclass']     = 'company_content';
        $this->load->view('admin/instance/form', $data);    

    }


    public function update_instance($instance_id = NULL)
    {
        close_setup_menu();
        $staff_id = get_staff_user_id();

        $company_data_array = $this->db->where('id',$instance_id)->get('tblinstance')->row();

        if(empty($company_data_array))
        {
            set_alert('warning', 'Invalid Company Id !');
            return redirect(base_url('admin/instance'));
        }

        $this->form_validation->set_rules('company_name', 'Company Name', 'required|trim');
        $this->form_validation->set_rules('instance_name', 'Instance Name', 'required|trim');
        $this->form_validation->set_rules('phone', 'Phone Number', 'required|trim|numeric');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('company_address', 'Company Address', 'required|trim');
        $this->form_validation->set_rules('city', 'City', 'required|trim');
        $this->form_validation->set_rules('state', 'State', 'trim|required');
        $this->form_validation->set_rules('zip', 'Zip Code', 'trim|numeric|required');
        $this->form_validation->set_rules('g-recaptcha-response', 'Captcha Code', 'required');


        $recaptchaResponse = trim($this->input->post('g-recaptcha-response'));
        $userIp=$this->input->ip_address();
        $captcha_status = $this->captcha($userIp, $recaptchaResponse);
        if(empty($captcha_status))
        {
            $this->form_validation->set_rules('recaptcha', 'Captcha Code', 'required');
        }

        if ($this->form_validation->run() == false) 
        {
            $this->form_validation->error_array();
        } 
        else 
        {
            $company_content = array();

            $company_content['company_name'] = $this->input->post('company_name',TRUE);
            $company_content['instance_name'] = $this->input->post('instance_name',TRUE);
            $company_content['phone'] = $this->input->post('phone',TRUE);
            $company_content['first_name'] = $this->input->post('first_name',TRUE);
            $company_content['last_name'] = $this->input->post('last_name',TRUE);
            $company_content['company_address'] = $this->input->post('company_address',TRUE);
            $company_content['city'] = $this->input->post('city',TRUE);
            $company_content['state'] = $this->input->post('state',TRUE);
            $company_content['zip'] = $this->input->post('zip',TRUE);

            $this->db->where('id',$instance_id)->update('tblinstance',$company_content);
            $status = $this->db->affected_rows();

            if($status)
            {                
                 set_alert('success', 'Instance Update Successfully');
            }
            else
            {
                 set_alert('warning', 'Instance Update Error');
            }

            redirect(base_url('admin/instance'));            
        }

      
        $data['company_data_array'] = $company_data_array;
        $data['title'] = _l('Update Company Details');
        $data['instance_id'] = $instance_id;
        $data['bodyclass']     = 'company_content';
        $this->load->view('admin/instance/form', $data);    
    }
    public function captcha($userIp, $recaptchaResponse)
    {
        $secret = $this->config->item('google_secret');
        $url="https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$recaptchaResponse."&remoteip=".$userIp;
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        curl_close($ch);      
        $status= json_decode($output, true);

        if($status['success'])
        {
            return 1;
        }
        else
        {
            return NULL;
        }

    }


}
