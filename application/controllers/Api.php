<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;
use chriskacerguis\RestServer\RestController;
use Twilio\Rest\Client;

const HTTP_OK = 200;
const HTTP_CREATED = 201;
const HTTP_NOT_MODIFIED = 304;
const HTTP_BAD_REQUEST = 400;
const HTTP_UNAUTHORIZED = 401;
const HTTP_FORBIDDEN = 403;
const HTTP_NOT_FOUND = 404;
const HTTP_NOT_ACCEPTABLE = 406;
const HTTP_INTERNAL_ERROR = 500;
date_default_timezone_set('Asia/Kolkata');

class Api extends RestController {

    var $key = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJmZnZmdmYiLCJhdWQiOiJmdmZjIGMgYy';

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

        $this->load->model('temp_model');
    }

    function temp_get()
    {    

        $user_array = $this->header_verify();
        $userid = $user_array->userid;

        if(!$this->get('id'))
        {
            $this->response( [
                'status' => false,
                'message' => 'can not retrive data'
            ], 400 );
            $this->response([NULL, 400]);
        }

        $temp = $this->temp_model->get( $this->get('id') );

        if(count($temp)) {
            $this->response( [
                'status' => true,
                'data' => $temp, 
                'message' => 'success'
            ], 200 );
        }
        else
        {
            $this->response( [
                'status' => false,
                'message' => 'No data found'
            ], 404 );
            $this->response(NULL, 404);
        }
    }
     
    function temp_post()
    {
        $user_array = $this->header_verify();
        $userid = $user_array->userid;

        if( !($this->post('state')) || 
            !($this->post('reading_qty')) || 
            !$userid || 
            !($this->post('temp')) 
        ) {
            $this->response( [
                'status' => false,
                'message' => 'required fields missing'
            ], 400 );
        }

        $result = $this->temp_model->update( $this->post('id'), array(
            'state' => $this->post('state'),
            'reading_qty' => $this->post('reading_qty'), 
            'temp' => $this->post('temp'), 
            'userid' => $userid, 
        ));

        if($result === FALSE)
        {
            $this->response([
                    'status' => 'failed',
                    'message' => 'something went wrong',
                ], 404);
        }
        else
        {
            $this->response([
                    'status' => 'success', 
                    'message' => 'record inserted successfully!', 
                ], 200);
        }
         
    }
     
    function temps_get()
    {
        $user_array = $this->header_verify();
        $id = $user_array->userid;

        if(empty($id)) {
            $this->response( [
                'status' => false,
                'message' => 'Not Authorized..!'
            ], 400 );
        }

        $temps = $this->temp_model->get_all($id);

        $this->response([
            'status' => true,
            'message' => 'Success', 
            'data' => $temps
        ], 200);
    }



    function savepdf_post()
    {
        $user_array = $this->header_verify();
        $userid = $user_array->userid;

        if(!($userid)) {
            $this->response( [
                'status' => false,
                'message' => 'required fields missing'
            ], 400 );
        }

        $pdf_file = NULL;
        if(isset($_FILES['pdf_file']['name']) && $_FILES['pdf_file']['name'])
        { 
            $config['upload_path'] = './assets/uploads/pdf/';
            $config['allowed_types'] = 'gif|jpeg|jpg|png|pdf';
            $config['max_size'] = '1000000';
            $config['max_width']  = '1000000';
            $config['max_height']  = '600000';
            $new_name = time().'_'.$_FILES["pdf_file"]['name'];
            $config['file_name'] = $new_name;
    
            $this->load->library('upload', $config);
    
            if (!$this->upload->do_upload('pdf_file'))
            {
                $error = $this->upload->display_errors();
                
                $this->response( [
                                    'status' => false,
                                    'message' => 'Pdf Upload Error.. ! '
                                ], 400 );

            }
            else
            {
                $img_data = $this->upload->data();
                $pdf_file = $img_data['file_name'];
            }
        }
        else
        {
            $this->response( [
                                    'status' => false,
                                    'message' => 'Upload Pdf File First .. ! '
                                ], 400 );
        }

        if(empty($pdf_file))
        {
            $this->response( [
                                    'status' => false,
                                    'message' => 'Error Upload Pdf File First .. ! '
                                ], 400 );
        }

        $result = $this->temp_model->update( $this->post('id'), array(
            'userid' => $userid,
            'pdf_file' => base_url('assets/uploads/pdf/').$pdf_file, 
        ));

        if($result === FALSE)
        {
            $this->response([
                    'status' => 'failed', 
                ], 400);
        }
         
        else
        {
            $this->response([
                    'status' => 'success', 
                    'message' => 'PDF stored successfully', 
                ], 200);
        }
         
    }


    function sendotp_post()
    {
        if(!($this->post('mob_no')) ) {
            $this->response( [
                'status' => false,
                'message' => 'required fields missing'
            ], 400 );
        }
        $mob_no = $this->post('mob_no',true);
        $client_data = $this->db->where('phonenumber',$mob_no)->get('tblcontacts')->row();

        if(empty($client_data)) 
        {
            $this->response( [
                'status' => false,
                'message' => 'No CLient Found With This Phone Number..! '
            ], 404 );
            $this->response(NULL, 404);
        }


        $otp_data = $this->db->select_max('sent')->where('mob_no',$mob_no)->get('otp_verification')->row();
        if(!empty($otp_data->sent)) 
        {
            $sent = $otp_data->sent;
            $now = time();     
            $seconds_diff = $now - $sent;                            
            $min_diff = ($seconds_diff/60);

            if($min_diff < 1) 
            {
                $this->response( [
                    'status' => false,
                    'message' => 'Otp Resed Error Need To Wait For  1 Minute '
                ], 404 );
                $this->response(NULL, 404);
            }

        }


        $otp = rand( 100000 , 999999);
        $response = $this->send_otp_msg($mob_no,$otp,$client_data);
        if(isset($response['status']) && $response['status'] == FALSE)
        {
            $this->response( [
                'status' => false,
                'message' => $response['msg'],
            ], 404 );
            $this->response(NULL, 404);
        }

        $data['mob_no'] = $this->post('mob_no',true);
        $data['otp'] = $otp;
        $data['user_id'] = $client_data->userid;
        $data['sent'] = time();
        $data['verify'] = NULL;
        $status = $this->db->insert('otp_verification',$data);
        if($status)
        {
            $this->response([
                    'status' => 'success',
                    'message' => 'OTP sent successfully', 
                ], 200);
        }
        else
        {
            $this->response(array('status' => 'failed'));           
        } 
    }

    function verifyotp_post()
    {
        if(!($this->post('mob_no')) || !($this->post('otp')) ) {
            $this->response( [
                'status' => false,
                'message' => 'required fields missing'
            ], 400 );
        }
        $mob_no = $this->post('mob_no',true);
        $otp = $this->post('otp',true);
        $client_data = $this->db->where('phonenumber',$mob_no)->get('tblcontacts')->row();

        if(empty($client_data)) 
        {
            $this->response( [
                'status' => false,
                'message' => 'No CLient Found With This Phone Number..! '
            ], 404 );
            $this->response(NULL, 404);
        }

        $otp_data = $this->db->where('mob_no',$mob_no)->where('otp',$otp)->get('otp_verification')->row();

        if(empty($otp_data)) 
        {
            $this->response( [
                'status' => false,
                'message' => 'Sorry Otp Not Match..! '
            ], 404 );
            $this->response(NULL, 404);
        }

        $sent = $otp_data->sent;
        $now = time();     
        $seconds_diff = $now - $sent;                            
        $min_diff = ($seconds_diff/60);

        if($min_diff >= 10) 
        {
            $this->response( [
                'status' => false,
                'message' => 'Sorry Otp Has Been Expired..! '
            ], 404 );
            $this->response(NULL, 404);
        }


        $data['verify'] = time();
        $this->db->where('mob_no',$mob_no)->where('otp',$otp)->update('otp_verification',$data);
        $status =  $this->db->affected_rows();

        if($status)
        {
            // +30 minutes
            // '+1 day'
            $current_time = time();
            $next_day = strtotime('+1 day', $current_time);
            $api_response['userid'] = $client_data->userid;
            $api_response['firstname'] = $client_data->firstname;
            $api_response['lastname'] = $client_data->lastname;
            $api_response['email'] = $client_data->email;
            $api_response['phonenumber'] = $client_data->phonenumber;
            $api_response['token_create_time'] = $current_time;
            $api_response['token_expire_time'] = $next_day;
            try
            {
                $jwt_token = JWT::encode($api_response, $this->key);
            }
            catch(Exception $e)
            {
                $this->response( [
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 404 );
                $this->response(NULL, 404);                
            }
            unset($api_response['token_create_time']);
            unset($api_response['token_expire_time']);
            $this->response([
                    'status' => 'success',
                    'jwt_token'=>$jwt_token,
                    'data'=>$api_response
                ], 200);
        }
        else
        {
            $this->response(array('status' => 'failed'));           
        } 
    }





    private function send_otp_msg($phonenumber,$otp,$client_data)
    {
        $response['status'] = FALSE;
        $response['msg'] = 'Not Attemp';
        $account_sid = 'AC4ab9522aae5d165d5328ba1934c66052';
        $auth_token = 'e34ff3fa3acd6d951d84ff9a285daaa3';
        $twilio_number = "+12057518109";
        $client = new Client($account_sid, $auth_token);

        try {
                $client->messages->create(
                $phonenumber,// Where to send a text message (your cell phone?)
                array(
                    'from' => $twilio_number,
                    'body' => 'Dear '.$client_data->firstname.' '.$client_data->lastname.' Your Otp Code for Login with Tempucheck is '.$otp.' Download the app: http://bit.ly/318SMk2'
                )
            );
            /* SENDING SMS PART ENDS *****/
            $response['status'] = TRUE;
            $response['msg'] = 'Otp Send Successfully..! ';
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


    function testttt_get()
    {

        $key = "example_key";
        $payload = array(
            "iss" => "ffvfvf",
            "aud" => "fvfc c c v",
            "iat" => 1356999524,
            "nbf" => 1357000000
        );
        $jwt = JWT::encode($payload, $key);

        $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyaWQiOiI0NyIsImZpcnN0bmFtZSI6IkVyaWMiLCJsYXN0bmFtZSI6IkdpbCIsImVtYWlsIjoiZXJpYy5naWxAdXNkZXZjby5jb20iLCJwaG9uZW51bWJlciI6Iis5MTk0NjA1NTEyMzUiLCJ0b2tlbl9jcmVhdGVfdGltZSI6MTYwMDUxNzAxMiwidG9rZW5fZXhwaXJlX3RpbWUiOjE2MDA2MDM0MTJ9.oEh1EaEaUWlI5hY_Owq4gDARRF914BwpyGCYz-gllKA';

        try {
                $decoded = JWT::decode($jwt, $this->key, array('HS256'));
            }
            catch(Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }


        p($decoded);
        $decoded = JWT::decode($jwt, $key, array('HS256'));

        // $decoded_array = (array) $decoded;
        JWT::$leeway = 10; // $leeway in seconds
        $decoded = JWT::decode($jwt, $key, array('HS256'));

    }


    private function header_verify()
    {
        $headers=array();
        foreach (getallheaders() as $name => $value) 
        {
            $headers[$name] = $value;
        }
        $headers['Authorization'] = isset($headers['Authorization']) ? $headers['Authorization'] : array();


        if(empty($headers['Authorization']))
        {
             $this->response( [
                'status' => false,
                'message' => 'Invalid Request'
            ], 400 );
            $this->response([NULL, 400]);
        }

        if($headers['Authorization'])
        {

            $token = str_replace("Bearer ","",$headers['Authorization']);

            try 
            {
                $user_array = JWT::decode($token, $this->key, array('HS256'));
            }
            catch(Exception $e) 
            {
                $this->response( [
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 404 );
                $this->response(NULL, 404);    
            }

            if($user_array->token_expire_time < time())
            {
                $this->response( [
                    'status' => false,
                    'message' => 'Token Has Benn Expired ...!',
                ], 404 );
                $this->response(NULL, 404); 
            }

            $verify_client = $this->db->where('userid', $user_array->userid)->get('tblcontacts')->row();

            if(empty($verify_client))
            {
                 $this->response( [
                    'status' => false,
                    'message' => 'Invalid Token Data...!',
                ], 404 );
                $this->response(NULL, 404); 
            } else {
                return $user_array;
            }

        }

    }



}
?>