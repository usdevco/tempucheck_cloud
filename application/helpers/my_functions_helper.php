<?php 

//Add reply to corrct sender not main email.
add_action('before_email_template_send', 'my_add_staff_email_reply_to');

function my_add_staff_email_reply_to($data)
{
    $add_reply_to_email_types = array('invoice', 'estimate', 'proposals');
    if (in_array($data['template']->type, $add_reply_to_email_types)) {
        $CI = &get_instance();
        $id = '';
        if (is_staff_logged_in()) {
            $id = get_staff_user_id();
        }
        if ($id != '') {
            $CI->db->where('staffid', $id);
            $staff = $CI->db->get('tblstaff')->row();
            if($staff){
                $data['template']->reply_to = $staff->email;
            }
        }
    }
    return $data;
}
//End email addon

// Redirect Client after login


add_action('after_contact_login','redirect_to_tickets');

function redirect_to_tickets(){
redirect(site_url('clients/tickets'));
}
//End Redirect

//Redirect After Lead Submit
add_action('web_to_lead_form_submitted','my_web_to_lead_form_submitted_redirect_to_custom_url');

function my_web_to_lead_form_submitted_redirect_to_custom_url($data){
    echo json_encode(array(
      'success'=>true,
      'redirect_url'=>'https://www.impactwindowsmiami.net/thank-you'
    ));
    die;
}