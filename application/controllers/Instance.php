<?php
use Twilio\Rest\Client;

header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');
class Instance extends Clients_controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('session');
    }

    public function index()
    {
        $this->form_validation->set_rules('company_name', 'Company Name', 'required|trim');
        $this->form_validation->set_rules('instance_name', 'Instance Name', 'required|trim|alpha|is_unique[tblinstance.instance_name]');
        $this->form_validation->set_rules('phone', 'Phone Number', 'required|trim');
        $this->form_validation->set_rules('email', 'Email Address', 'required|trim|valid_email|is_unique[tblcontacts.email]');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('company_address', 'Company Address', 'required|trim');
        $this->form_validation->set_rules('city', 'City', 'required|trim');
        $this->form_validation->set_rules('state', 'State', 'trim|required');
        $this->form_validation->set_rules('zip', 'Zip Code', 'trim|numeric|required');
        // $this->form_validation->set_rules('g-recaptcha-response', 'Captcha Code', 'required');

        $recaptchaResponse = trim($this->input->post('g-recaptcha-response'));
        $userIp = $this->input->ip_address();
        $captcha_status = $this->captcha($userIp, $recaptchaResponse);

        if(empty($captcha_status))
        {
            // $this->form_validation->set_rules('recaptcha', 'Captcha Code', 'required');
        }

        if ($this->form_validation->run() == false) 
        {
            $this->form_validation->error_array();
        } 
        else 
        {
            $instance_name = strtolower($this->input->post('instance_name',TRUE));
            $phonenumber = strtolower($this->input->post('phone',TRUE));
            $panel_link = "http://".$instance_name.".mytempucheck.com";
            $text_response = $this->send_instance_msg($phonenumber,$instance_name);
            if($text_response['status'] == FALSE)
            {
                $this->session->set_flashdata('flashError', 'Phone Number Is Invalid Please Enter Correct Phone Number With country Code...! ');
                $this->session->set_flashdata('flashError', $text_response['msg']);
                $data['title'] = _l('Add Company Details');
                $data['instance_id'] = NULL;
                $data['bodyclass']     = 'company_content';
                return $this->load->view('instance/form', $data);  
                die();
            }

            $user_id = $this->add_client($this->input->post());
            if($user_id)
            {
                $company_content = array();
                $company_content['user_id'] = $user_id;
                $company_content['company_name'] = $this->input->post('company_name',TRUE);
                $company_content['instance_name'] = strtolower($this->input->post('instance_name',TRUE));
                $company_content['phone'] = $this->input->post('phone',TRUE);
                $company_content['email'] = $this->input->post('email',TRUE);
                $company_content['first_name'] = $this->input->post('first_name',TRUE);
                $company_content['last_name'] = $this->input->post('last_name',TRUE);
                $company_content['company_address'] = $this->input->post('company_address',TRUE);
                $company_content['city'] = $this->input->post('city',TRUE);
                $company_content['state'] = $this->input->post('state',TRUE);
                $company_content['zip'] = $this->input->post('zip',TRUE);
                $status = $this->db->insert('tblinstance',$company_content);

                if($status)
                {
                    $email = $this->input->post('email',TRUE);
                    $subject = 'Welcome To Tempucheck';
                    $bodymessage = 'Thank You for registering with Tempucheck <br /><br />';
                    $bodymessage .= 'Your login details: <br />';
                    $bodymessage .= 'Instance URL: https://tempucheckcrm.com/clients/login <br />';
                    $bodymessage .= ' <br />';
                    $bodymessage .= 'Username: '.$email.' <br />';
                    $bodymessage .= 'Password: 123456 <br />';

                    $this->send_email_for_instance($email, $subject, $bodymessage);
                    $this->session->set_flashdata('SUCCESS', 'Instance created successfully, you can now login with the password sent on your email address');
                }
                else
                {
                    $this->db->delete('userid',$user_id)->delete('tblclients');
                    $this->db->delete('userid',$user_id)->delete('tblcontacts');
                    $this->session->set_flashdata('flashError', 'Instance Insert Error !!');

                }
            }
            else
            {
                $this->session->set_flashdata('flashError', 'Instance Insert Error !!');
            }
            redirect(base_url('instance'));                
        }

        $data['title'] = _l('Add Company Details');
        $data['instance_id'] = NULL;
        $data['bodyclass']     = 'company_content';
        $this->load->view('instance/form', $data);    

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

    private function send_instance_msg($phonenumber, $instance_name)
    {
        $response['status'] = FALSE;
        $response['msg'] = 'Not Attemp';

        $panel_link = "http://".$instance_name.".mytempucheck.com";
        // $this->send_email_for_instance($email, $subject, $bodymessage);

        // set_alert('success', 'Instance Inserted Successfully');
        // Your Account SID and Auth Token from twilio.com/console
        // $account_sid = 'AC0c955d897be5304a7692eecc99dbcc94';
        // $auth_token = 'af9c494274ff1b53f8605f284ccd5460';
        $account_sid = 'AC4ab9522aae5d165d5328ba1934c66052';
        $auth_token = 'e34ff3fa3acd6d951d84ff9a285daaa3';

        // In production, these should be environment variables. E.g.:
        // $auth_token = $_ENV["TWILIO_AUTH_TOKEN"]

        // A Twilio number you own with SMS capabilities
        // $twilio_number = "+15005550006";
        $twilio_number = "+12057518109";
        $client = new Client($account_sid, $auth_token);

        try {
                /**** SENDING SMS PART STARTS */
                $client->messages->create(
                
                $phonenumber,// Where to send a text message (your cell phone?)
                array(
                    'from' => $twilio_number,
                    'body' => 'Thank You for registering with Tempucheck.com your admin app code is:'.$panel_link.' Download the app: http://bit.ly/318SMk2'
                )
            );
            /* SENDING SMS PART ENDS *****/
            $response['status'] = TRUE;
            $response['msg'] = 'Text Message Send Successfully..! ';
            return $response;

        }
        catch (Exception $e)
        {
            $response['status'] = FALSE;
            $response['msg'] = $e->getMessage();
            return $response;
            die( $e->getStatusCode() . ' : ' . $e->getMessage() );
        }
        return $response;
    }

    private function add_client($post)
    {
        $client = array();
        $client['company'] = $this->input->post('company_name',TRUE);
        $client['phonenumber'] = $this->input->post('phone',TRUE);
        $client['country'] = 0;
        $client['state'] = $this->input->post('state',TRUE);
        $client['city'] = $this->input->post('city',TRUE);
        $client['address'] = $this->input->post('company_address',TRUE);
        $client['zip'] = $this->input->post('zip',TRUE);
        $client['datecreated'] = date('Y-m-d H:i:s');
        $client['active'] = 1;
        $client['billing_street'] = $this->input->post('company_address',TRUE);
        $client['billing_city'] = $this->input->post('city',TRUE);
        $client['billing_state'] = $this->input->post('state',TRUE);
        $client['billing_zip'] = $this->input->post('zip',TRUE);
        $client['billing_country'] = 0;
        $client['default_currency'] = 0;
        $client['show_primary_contact'] = 1;
        $client['addedfrom'] = 0;
        $status = $this->db->insert('tblclients',$client);
        $user_id = $this->db->insert_id();

        if($user_id)
        {  
            $contact =array();
            $contact['userid'] = $user_id;
            $contact['is_primary'] = 1;
            $contact['firstname'] = $this->input->post('first_name',TRUE);
            $contact['lastname'] = $this->input->post('last_name',TRUE);
            $contact['email'] = $this->input->post('email',TRUE);
            $contact['phonenumber'] = $this->input->post('phone',TRUE);
            $contact['title'] = ' ';
            $contact['datecreated'] = date('Y-m-d H:i:s');

            $contact['password'] = '$2a$08$U2Jc.zZPzhJhi6f/ZGM7ueNvO84Qz7xPCdTwB5CxQfaIrhiZ6SORO';
            $contact['active'] = 1;
            $contact['invoice_emails'] = 1;
            $contact['estimate_emails'] = 1;
            $contact['credit_note_emails'] = 1;
            $contact['contract_emails'] = 1;
            $contact['task_emails'] = 1;
            $contact['project_emails'] = 1;
            $contact['ticket_emails'] = 1;
            $status = $this->db->insert('tblcontacts',$contact);
            $contact_id = $this->db->insert_id();
            return $user_id;
        }
        
        return false;
    }



    function send_email_for_instance($email, $subject, $bodymessage)
    {    
        $image = site_url('media/logo.png');
        $whitelist = array('127.0.0.1','::1');
        if(in_array($_SERVER['REMOTE_ADDR'], $whitelist))
        {
            $image = 'https://tempucheckcrm.com/media/logo.png';
        }      
        $this->load->config('email');
        // Simulate fake template to be parsed
        $template = new StdClass();
        
        $template->message = $bodymessage."<br /><br /><img src='".$image."' alt='' width='195' height='55' />";

        $template->fromname = 'Tempucheck Crm'; //we use email because client name is not available
        $template->subject  = $subject;

        $template = parse_email_template($template);
        $this->email->initialize();
        if (get_option('mail_engine') == 'phpmailer') 
        {
            $this->email->set_debug_output(function ($err) 
            {
                if (!isset($GLOBALS['debug'])) {
                    $GLOBALS['debug'] = '';
                }
                $GLOBALS['debug'] .= $err . '<br />';
                // p($err);
                return $err;
            });
            $this->email->set_smtp_debug(3);
            // p($this->email->set_smtp_debug(3));
        }

        $this->email->set_newline(config_item('newline'));
        $this->email->set_crlf(config_item('crlf'));
        $this->email->from(get_option('smtp_email'), $template->fromname);
        // p($email);

        $this->email->to($email); // client email addres
        $systemBCC = get_option('bcc_emails');

        if (isset($systemBCC) && !empty($systemBCC)) 
        {
            $this->email->bcc($systemBCC);
        }

        $this->email->subject($template->subject);
        $this->email->message($template->message);
        if ($this->email->send(true)) 
        {
            return true;
        } 
        else 
        {
            return false;
        }
    }
    function pdf()
    {
        $this->load->view('instance/post');
    }

}
