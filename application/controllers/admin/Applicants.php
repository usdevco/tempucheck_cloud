<?php

header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');
class Applicants extends Admin_controller
{
    private $not_importable_leads_fields;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->not_importable_leads_fields = do_action('not_importable_leads_fields', ['id', 'source', 'assigned', 'status', 'dateadded', 'last_status_change', 'addedfrom', 'leadorder', 'date_converted', 'lost', 'junk', 'is_imported_from_email_integration', 'email_integration_uid', 'is_public', 'dateassigned', 'client_id', 'lastcontact', 'last_lead_status', 'from_form_id', 'default_language']);
        $this->load->model('applicants_model');
    }

    /* List all leads */
    public function index($id = '')
    {
        close_setup_menu();

        if (!is_staff_member()) {
            access_denied('Applicants');
        }

        $data['switch_kanban'] = true;

        if ($this->session->userdata('leads_kanban_view') == 'true') {
            $data['switch_kanban'] = false;
            $data['bodyclass']     = 'kan-ban-body';
        }

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }
        $data['summary']  = get_leads_summary();
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $data['title']    = _l('applicants');
        // in case accesed the url leads/index/ directly with id - used in search
        $data['leadid'] = $id;
        $this->load->view('admin/applicants/manage_applicants', $data);
    }

    public function table()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $this->app->get_table_data('applicants');
    }

    public function kanban()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $data['statuses'] = $this->leads_model->get_status();
        echo $this->load->view('admin/leads/kan-ban', $data, true);
    }

    /* Add or update applicant */
    public function applicant($id = '')
    {
       /* if (!is_staff_member() || ($id != '' && !$this->leads_model->staff_can_access_lead($id))) {
		 	
            $this->access_denied_ajax();
        }*/

        if ($this->input->post()) {
			
			$data=$this->input->post();				
			$resume=$this->upload_applicant_resume();
			$dl=$this->upload_applicant_dl();
			$data['resume']	= $resume;
			$data['DL']	= $dl;			
				
            if ($id == '') {				
                $id      = $this->applicants_model->add($data);
                $message = $id ? _l('added_successfully', _l('lead')) : '';
               
            } else { 
			    if($data['resume']==''){
					unset($data['resume']);
				}
				if($data['DL']==''){
					unset($data['DL']);
				}
                //print_r($this->input->post()); echo $id;  die;
                $success         = $this->applicants_model->update($data, $id);

                if ($success) {
                    
                    $message = _l('updated_successfully', _l('applicant'));
                }
                
            }
            redirect('admin/applicants'); 
        }
	    if ($id == '') {
            $title                            = "Add new applicant";
        } else {
            $data['applicant']                               = $this->applicants_model->get($id);             
			$title                            = "Edit applicant";
        }	

        echo $this->load->view('admin/applicants/applicant', $data, true);
    }
	
	function upload_applicant_resume()
	{
		if (isset($_FILES['upload_resume']['name']) && $_FILES['upload_resume']['name'] != '') {
			$path = get_upload_path_by_type('applicant');
			// Get the temp file path
			$tmpFilePath = $_FILES['upload_resume']['tmp_name'];
			// Make sure we have a filepath
			if (!empty($tmpFilePath) && $tmpFilePath != '') {

				// Setup our new file path
				$filename    = $_FILES['upload_resume']['name'];
			    $newFilePath = $path . $filename; 
				//_maybe_create_upload_path($path);
				// Upload the file into the company uploads dir
				if (move_uploaded_file($tmpFilePath, $newFilePath)) {					
                    //echo $newFilePath ; die;
					return $newFilePath;
				}else{
					//echo "not uploaded"; die;
				}
			}
		}

		return '';
	}
	function upload_applicant_dl()
	{
		if (isset($_FILES['upload_dl']['name']) && $_FILES['upload_dl']['name'] != '') {
			$path = get_upload_path_by_type('applicant');
			// Get the temp file path
			$tmpFilePath = $_FILES['upload_dl']['tmp_name'];
			// Make sure we have a filepath
			if (!empty($tmpFilePath) && $tmpFilePath != '') {

				// Setup our new file path
				$filename    = $_FILES['upload_dl']['name'];
				$newFilePath = $path . $filename;
				//_maybe_create_upload_path($path);
				// Upload the file into the company uploads dir
				if (move_uploaded_file($tmpFilePath, $newFilePath)) {					

					return $newFilePath;
				}
			}
		}

		return '';
	}	

    private function _get_lead_data($id = '')
    {
        $reminder_data       = '';
        $data['lead_locked'] = false;
        $data['openEdit']        = $this->input->get('edit') ? true : false;
        $data['members']     = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $data['status_id']   = $this->input->get('status_id') ? $this->input->get('status_id') : get_option('leads_default_status');

        if (is_numeric($id)) {
            $leadWhere = (has_permission('leads', '', 'view') ? [] : '(assigned = ' . get_staff_user_id() . ' OR addedfrom=' . get_staff_user_id() . ' OR is_public=1)');

            $lead = $this->leads_model->get($id, $leadWhere);

            if (!$lead) {
                header('HTTP/1.0 404 Not Found');
                echo _l('lead_not_found');
                die;
            }

            if (total_rows('tblclients', ['leadid' => $id ]) > 0) {
                $data['lead_locked'] = ((!is_admin() && get_option('lead_lock_after_convert_to_customer') == 1) ? true : false);
            }

            $reminder_data = $this->load->view('admin/includes/modals/reminder', [
                    'id'             => $lead->id,
                    'name'           => 'lead',
                    'members'        => $data['members'],
                    'reminder_title' => _l('lead_set_reminder_title'),
                ], true);

            $data['lead']          = $lead;
            $data['mail_activity'] = $this->leads_model->get_mail_activity($id);
            $data['notes']         = $this->misc_model->get_notes($id, 'lead');
            $data['activity_log']  = $this->leads_model->get_lead_activity_log($id);

            if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                $this->load->model('gdpr_model');
                $data['purposes'] = $this->gdpr_model->get_consent_purposes($lead->id, 'lead');
                $data['consents'] = $this->gdpr_model->get_consents(['lead_id' => $lead->id]);
            }
        }


        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();

        $data = do_action('lead_view_data', $data);

        return [
            'data'          => $this->load->view('admin/leads/lead', $data, true),
            'reminder_data' => $reminder_data,
        ];
    }

    public function leads_kanban_load_more()
    {
        if (!is_staff_member()) {
            $this->access_denied_ajax();
        }

        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $this->db->where('id', $status);
        $status = $this->db->get('tblleadsstatus')->row_array();

        $leads = $this->leads_model->do_kanban_query($status['id'], $this->input->get('search'), $page, [
            'sort_by' => $this->input->get('sort_by'),
            'sort'    => $this->input->get('sort'),
        ]);

        foreach ($leads as $lead) {
            $this->load->view('admin/leads/_kan_ban_card', [
                'lead'   => $lead,
                'status' => $status,
            ]);
        }
    }

    public function switch_kanban($set = 0)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'leads_kanban_view' => $set,
        ]);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function export($id)
    {
        if (is_admin()) {
            export_lead_data($id);
        }
    }

    /* Delete lead from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('applicants'));
        }

       /* if (!is_lead_creator($id) && !has_permission('leads', '', 'delete')) {
            access_denied('Delte Lead');
        }*/

        $response = $this->applicants_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_lowercase')));
        } elseif ($response === true) {
            set_alert('success', _l('deleted', _l('applicant')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('applicant_lowercase')));
        }
        $ref = $_SERVER['HTTP_REFERER'];

        // if user access leads/inded/ID to prevent redirecting on the same url because will throw 404
        if (!$ref ) {
            redirect(admin_url('applicants'));
        }

        redirect($ref);
    }

    public function mark_as_lost($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            $this->access_denied_ajax();
        }
        $message = '';
        $success = $this->leads_model->mark_as_lost($id);
        if ($success) {
            $message = _l('lead_marked_as_lost');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'leadView' => $this->_get_lead_data($id),
            'id'       => $id,
        ]);
    }

    public function unmark_as_lost($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            $this->access_denied_ajax();
        }
        $message = '';
        $success = $this->leads_model->unmark_as_lost($id);
        if ($success) {
            $message = _l('lead_unmarked_as_lost');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'leadView' => $this->_get_lead_data($id),
            'id'       => $id,
        ]);
    }

    public function mark_as_junk($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            $this->access_denied_ajax();
        }
        $message = '';
        $success = $this->leads_model->mark_as_junk($id);
        if ($success) {
            $message = _l('lead_marked_as_junk');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'leadView' => $this->_get_lead_data($id),
            'id'       => $id,
        ]);
    }

    public function unmark_as_junk($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            $this->access_denied_ajax();
        }
        $message = '';
        $success = $this->leads_model->unmark_as_junk($id);
        if ($success) {
            $message = _l('lead_unmarked_as_junk');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'leadView' => $this->_get_lead_data($id),
            'id'       => $id,
        ]);
    }

    public function add_activity()
    {
        $leadid = $this->input->post('leadid');
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($leadid)) {
            $this->access_denied_ajax();
        }
        if ($this->input->post()) {
            $message = $this->input->post('activity');
            $aId     = $this->leads_model->log_lead_activity($leadid, $message);
            if ($aId) {
                $this->db->where('id', $aId);
                $this->db->update('tblleadactivitylog', ['custom_activity' => 1]);
            }
            echo json_encode(['leadView' => $this->_get_lead_data($leadid), 'id' => $leadid]);
        }
    }

    public function get_convert_data($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            $this->access_denied_ajax();
        }
        if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
            $this->load->model('gdpr_model');
            $data['purposes'] = $this->gdpr_model->get_consent_purposes($id, 'lead');
        }
        $data['lead'] = $this->leads_model->get($id);
        $this->load->view('admin/leads/convert_to_customer', $data);
    }

    /**
     * Convert lead to client
     * @since  version 1.0.1
     * @return mixed
     */
    public function convert_to_customer()
    {
        if (!is_staff_member()) {
            access_denied('Lead Convert to Customer');
        }

        if ($this->input->post()) {
            $default_country  = get_option('customer_default_country');
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            $original_lead_email = $data['original_lead_email'];
            unset($data['original_lead_email']);

            if (isset($data['transfer_notes'])) {
                $notes = $this->misc_model->get_notes($data['leadid'], 'lead');
                unset($data['transfer_notes']);
            }

            if (isset($data['transfer_consent'])) {
                $this->load->model('gdpr_model');
                $consents = $this->gdpr_model->get_consents(['lead_id' => $data['leadid']]);
                unset($data['transfer_consent']);
            }

            if (isset($data['merge_db_fields'])) {
                $merge_db_fields = $data['merge_db_fields'];
                unset($data['merge_db_fields']);
            }

            if (isset($data['merge_db_contact_fields'])) {
                $merge_db_contact_fields = $data['merge_db_contact_fields'];
                unset($data['merge_db_contact_fields']);
            }

            if (isset($data['include_leads_custom_fields'])) {
                $include_leads_custom_fields = $data['include_leads_custom_fields'];
                unset($data['include_leads_custom_fields']);
            }

            if ($data['country'] == '' && $default_country != '') {
                $data['country'] = $default_country;
            }

            $data['billing_street']  = $data['address'];
            $data['billing_city']    = $data['city'];
            $data['billing_state']   = $data['state'];
            $data['billing_zip']     = $data['zip'];
            $data['billing_country'] = $data['country'];

            $data['is_primary'] = 1;
            $id                 = $this->clients_model->add($data, true);
            if ($id) {
                $primary_contact_id = get_primary_contact_user_id($id);

                if (isset($notes)) {
                    foreach ($notes as $note) {
                        $this->db->insert('tblnotes', [
                            'rel_id'         => $id,
                            'rel_type'       => 'customer',
                            'dateadded'      => $note['dateadded'],
                            'addedfrom'      => $note['addedfrom'],
                            'description'    => $note['description'],
                            'date_contacted' => $note['date_contacted'],
                            ]);
                    }
                }
                if (isset($consents)) {
                    foreach ($consents as $consent) {
                        unset($consent['id']);
                        unset($consent['purpose_name']);
                        $consent['lead_id']    = 0;
                        $consent['contact_id'] = $primary_contact_id;
                        $this->gdpr_model->add_consent($consent);
                    }
                }
                if (!has_permission('customers', '', 'view') && get_option('auto_assign_customer_admin_after_lead_convert') == 1) {
                    $this->db->insert('tblcustomeradmins', [
                        'date_assigned' => date('Y-m-d H:i:s'),
                        'customer_id'   => $id,
                        'staff_id'      => get_staff_user_id(),
                    ]);
                }
                $this->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted', false, serialize([
                    get_staff_full_name(),
                ]));
                $default_status = $this->leads_model->get_status('', [
                    'isdefault' => 1,
                ]);
                $this->db->where('id', $data['leadid']);
                $this->db->update('tblleads', [
                    'date_converted' => date('Y-m-d H:i:s'),
                    'status'         => $default_status[0]['id'],
                    'junk'           => 0,
                    'lost'           => 0,
                ]);
                // Check if lead email is different then client email
                $contact = $this->clients_model->get_contact(get_primary_contact_user_id($id));
                if ($contact->email != $original_lead_email) {
                    if ($original_lead_email != '') {
                        $this->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted_email', false, serialize([
                            $original_lead_email,
                            $contact->email,
                        ]));
                    }
                }
                if (isset($include_leads_custom_fields)) {
                    foreach ($include_leads_custom_fields as $fieldid => $value) {
                        // checked don't merge
                        if ($value == 5) {
                            continue;
                        }
                        // get the value of this leads custom fiel
                        $this->db->where('relid', $data['leadid']);
                        $this->db->where('fieldto', 'leads');
                        $this->db->where('fieldid', $fieldid);
                        $lead_custom_field_value = $this->db->get('tblcustomfieldsvalues')->row()->value;
                        // Is custom field for contact ot customer
                        if ($value == 1 || $value == 4) {
                            if ($value == 4) {
                                $field_to = 'contacts';
                            } else {
                                $field_to = 'customers';
                            }
                            $this->db->where('id', $fieldid);
                            $field = $this->db->get('tblcustomfields')->row();
                            // check if this field exists for custom fields
                            $this->db->where('fieldto', $field_to);
                            $this->db->where('name', $field->name);
                            $exists               = $this->db->get('tblcustomfields')->row();
                            $copy_custom_field_id = null;
                            if ($exists) {
                                $copy_custom_field_id = $exists->id;
                            } else {
                                // there is no name with the same custom field for leads at the custom side create the custom field now
                                $this->db->insert('tblcustomfields', [
                                    'fieldto'        => $field_to,
                                    'name'           => $field->name,
                                    'required'       => $field->required,
                                    'type'           => $field->type,
                                    'options'        => $field->options,
                                    'display_inline' => $field->display_inline,
                                    'field_order'    => $field->field_order,
                                    'slug'           => slug_it($field_to . '_' . $field->name, [
                                        'separator' => '_',
                                    ]),
                                    'active'        => $field->active,
                                    'only_admin'    => $field->only_admin,
                                    'show_on_table' => $field->show_on_table,
                                    'bs_column'     => $field->bs_column,
                                ]);
                                $new_customer_field_id = $this->db->insert_id();
                                if ($new_customer_field_id) {
                                    $copy_custom_field_id = $new_customer_field_id;
                                }
                            }
                            if ($copy_custom_field_id != null) {
                                $insert_to_custom_field_id = $id;
                                if ($value == 4) {
                                    $insert_to_custom_field_id = get_primary_contact_user_id($id);
                                }
                                $this->db->insert('tblcustomfieldsvalues', [
                                    'relid'   => $insert_to_custom_field_id,
                                    'fieldid' => $copy_custom_field_id,
                                    'fieldto' => $field_to,
                                    'value'   => $lead_custom_field_value,
                                ]);
                            }
                        } elseif ($value == 2) {
                            if (isset($merge_db_fields)) {
                                $db_field = $merge_db_fields[$fieldid];
                                // in case user don't select anything from the db fields
                                if ($db_field == '') {
                                    continue;
                                }
                                if ($db_field == 'country' || $db_field == 'shipping_country' || $db_field == 'billing_country') {
                                    $this->db->where('iso2', $lead_custom_field_value);
                                    $this->db->or_where('short_name', $lead_custom_field_value);
                                    $this->db->or_like('long_name', $lead_custom_field_value);
                                    $country = $this->db->get('tblcountries')->row();
                                    if ($country) {
                                        $lead_custom_field_value = $country->country_id;
                                    } else {
                                        $lead_custom_field_value = 0;
                                    }
                                }
                                $this->db->where('userid', $id);
                                $this->db->update('tblclients', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        } elseif ($value == 3) {
                            if (isset($merge_db_contact_fields)) {
                                $db_field = $merge_db_contact_fields[$fieldid];
                                if ($db_field == '') {
                                    continue;
                                }
                                $this->db->where('id', $primary_contact_id);
                                $this->db->update('tblcontacts', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        }
                    }
                }
                // set the lead to status client in case is not status client
                $this->db->where('isdefault', 1);
                $status_client_id = $this->db->get('tblleadsstatus')->row()->id;
                $this->db->where('id', $data['leadid']);
                $this->db->update('tblleads', [
                    'status' => $status_client_id,
                ]);

                set_alert('success', _l('lead_to_client_base_converted_success'));

                if (is_gdpr() && get_option('gdpr_after_lead_converted_delete') == '1') {
                    $this->leads_model->delete($data['leadid']);

                    $this->db->where('userid', $id);
                    $this->db->update('tblclients', ['leadid' => null]);
                }

                logActivity('Created Lead Client Profile [LeadID: ' . $data['leadid'] . ', ClientID: ' . $id . ']');
                do_action('lead_converted_to_customer', ['lead_id' => $data['leadid'], 'customer_id' => $id]);
                redirect(admin_url('clients/client/' . $id));
            }
        }
    }

    /* Used in kanban when dragging and mark as */
    public function update_lead_status()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $this->leads_model->update_lead_status($this->input->post());
        }
    }

    public function update_status_order()
    {
        if ($post_data = $this->input->post()) {
            $this->leads_model->update_status_order($post_data);
        }
    }

    public function add_lead_attachment()
    {
        $id       = $this->input->post('id');
        $lastFile = $this->input->post('last_file');

        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            $this->access_denied_ajax();
        }

        handle_lead_attachments($id);
        echo json_encode(['leadView' => $lastFile ? $this->_get_lead_data($id) : [], 'id' => $id]);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->leads_model->add_attachment_to_database($this->input->post('lead_id'), $this->input->post('files'), $this->input->post('external'));
        }
    }

    public function delete_attachment($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            $this->access_denied_ajax();
        }
        echo json_encode([
            'success' => $this->leads_model->delete_lead_attachment($id),
        ]);
    }

    public function delete_note($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            $this->access_denied_ajax();
        }
        echo json_encode([
            'success' => $this->misc_model->delete_note($id),
        ]);
    }

    public function update_all_proposal_emails_linked_to_lead($id)
    {
        $success = false;
        $email   = '';
        if ($this->input->post('update')) {
            $this->load->model('proposals_model');

            $this->db->select('email');
            $this->db->where('id', $id);
            $email = $this->db->get('tblleads')->row()->email;

            $proposals = $this->proposals_model->get('', [
                'rel_type' => 'lead',
                'rel_id'   => $id,
            ]);
            $affected_rows = 0;

            foreach ($proposals as $proposal) {
                $this->db->where('id', $proposal['id']);
                $this->db->update('tblproposals', [
                    'email' => $email,
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }
            }

            if ($affected_rows > 0) {
                $success = true;
            }
        }

        echo json_encode([
            'success' => $success,
            'message' => _l('proposals_emails_updated', [
                _l('lead_lowercase'),
                $email,
            ]),
        ]);
    }

    public function save_form_data()
    {
        $data = $this->input->post();

        // form data should be always sent to the request and never should be empty
        // this code is added to prevent losing the old form in case any errors
        if (!isset($data['formData']) || isset($data['formData']) && !$data['formData']) {
            echo json_encode([
                'success' => false,
            ]);
            die;
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tblwebtolead', [
            'form_data' => $data['formData'],
        ]);
        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'success' => true,
                'message' => _l('updated_successfully', _l('web_to_lead_form')),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function form($id = '')
    {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }
        if ($this->input->post()) {
            if ($id == '') {
                $data = $this->input->post();
                $id   = $this->leads_model->add_form($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('web_to_lead_form')));
                    redirect(admin_url('leads/form/' . $id));
                }
            } else {
                $success = $this->leads_model->update_form($id, $this->input->post());
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('web_to_lead_form')));
                }
                redirect(admin_url('leads/form/' . $id));
            }
        }

        $data['formData'] = [];
        $custom_fields    = get_custom_fields('leads', 'type != "link"');

        $cfields       = format_external_form_custom_fields($custom_fields);
        $data['title'] = _l('web_to_lead');

        if ($id != '') {
            $data['form'] = $this->leads_model->get_form([
                'id' => $id,
            ]);
            $data['title']    = $data['form']->name . ' - ' . _l('web_to_lead_form');
            $data['formData'] = $data['form']->form_data;
        }

        $this->load->model('roles_model');
        $data['roles']    = $this->roles_model->get();
        $data['sources']  = $this->leads_model->get_source();
        $data['statuses'] = $this->leads_model->get_status();

        $data['members'] = $this->staff_model->get('', [
            'active'       => 1,
            'is_not_staff' => 0,
        ]);

        $data['languages'] = $this->app->get_available_languages();
        $data['cfields']   = $cfields;

        $db_fields = [];
        $fields    = [
            'name',
            'title',
            'email',
            'phonenumber',
            'company',
            'address',
            'city',
            'state',
            'country',
            'zip',
            'description',
            'website',
        ];

        $fields = do_action('lead_form_available_database_fields', $fields);

        $className = 'form-control';

        foreach ($fields as $f) {
            $_field_object = new stdClass();
            $type          = 'text';
            $subtype       = '';
            if ($f == 'email') {
                $subtype = 'email';
            } elseif ($f == 'description' || $f == 'address') {
                $type = 'textarea';
            } elseif ($f == 'country') {
                $type = 'select';
            }

            if ($f == 'name') {
                $label = _l('lead_add_edit_name');
            } elseif ($f == 'email') {
                $label = _l('lead_add_edit_email');
            } elseif ($f == 'phonenumber') {
                $label = _l('lead_add_edit_phonenumber');
            } else {
                $label = _l('lead_' . $f);
            }

            $field_array = [
                'subtype'   => $subtype,
                'type'      => $type,
                'label'     => $label,
                'className' => $className,
                'name'      => $f,
            ];

            if ($f == 'country') {
                $field_array['values'] = [];
                $countries             = get_all_countries();
                foreach ($countries as $country) {
                    $selected = false;
                    if (get_option('customer_default_country') == $country['country_id']) {
                        $selected = true;
                    }
                    array_push($field_array['values'], [
                        'label'    => $country['short_name'],
                        'value'    => (int) $country['country_id'],
                        'selected' => $selected,
                    ]);
                }
            }

            if ($f == 'name') {
                $field_array['required'] = true;
            }

            $_field_object->label    = $label;
            $_field_object->name     = $f;
            $_field_object->fields   = [];
            $_field_object->fields[] = $field_array;
            $db_fields[]             = $_field_object;
        }
        $data['bodyclass'] = 'web-to-lead-form';
        $data['db_fields'] = $db_fields;
        $this->load->view('admin/leads/formbuilder', $data);
    }

    public function forms($id = '')
    {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('web_to_lead');
        }

        $data['title'] = _l('web_to_lead');
        $this->load->view('admin/leads/forms', $data);
    }

    public function delete_form($id)
    {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }

        $success = $this->leads_model->delete_form($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('web_to_lead_form')));
        }

        redirect(admin_url('leads/forms'));
    }

    // Sources
    /* Manage leads sources */
    public function sources()
    {
        if (!is_admin()) {
            access_denied('Leads Sources');
        }
        $data['sources'] = $this->leads_model->get_source();
        $data['title']   = 'Leads sources';
        $this->load->view('admin/leads/manage_sources', $data);
    }

    /* Add or update leads sources */
    public function source()
    {
        if (!is_admin() && get_option('staff_members_create_inline_lead_source') == '0') {
            access_denied('Leads Sources');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }

                $id = $this->leads_model->add_source($data);

                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('lead_source')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : fales, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->leads_model->update_source($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('lead_source')));
                }
            }
        }
    }

    /* Delete leads source */
    public function delete_source($id)
    {
        if (!is_admin()) {
            access_denied('Delete Lead Source');
        }
        if (!$id) {
            redirect(admin_url('leads/sources'));
        }
        $response = $this->leads_model->delete_source($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_source_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_source')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_source_lowercase')));
        }
        redirect(admin_url('leads/sources'));
    }

    // Statuses
    /* View leads statuses */
    public function statuses()
    {
        if (!is_admin()) {
            access_denied('Leads Statuses');
        }
        $data['statuses'] = $this->leads_model->get_status();
        $data['title']    = 'Leads statuses';
        $this->load->view('admin/leads/manage_statuses', $data);
    }

    /* Add or update leads status */
    public function status()
    {
        if (!is_admin() && get_option('staff_members_create_inline_lead_status') == '0') {
            access_denied('Leads Statuses');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }
                $id = $this->leads_model->add_status($data);
                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('lead_status')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : fales, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->leads_model->update_status($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('lead_status')));
                }
            }
        }
    }

    /* Delete leads status from databae */
    public function delete_status($id)
    {
        if (!is_admin()) {
            access_denied('Leads Statuses');
        }
        if (!$id) {
            redirect(admin_url('leads/statuses'));
        }
        $response = $this->leads_model->delete_status($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_status_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_status')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_status_lowercase')));
        }
        redirect(admin_url('leads/statuses'));
    }

    /* Add new lead note */
    public function add_note($rel_id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($rel_id)) {
            $this->access_denied_ajax();
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            if ($data['contacted_indicator'] == 'yes') {
                $contacted_date         = to_sql_date($data['custom_contact_date'], true);
                $data['date_contacted'] = $contacted_date;
            }

            unset($data['contacted_indicator']);
            unset($data['custom_contact_date']);

            // Causing issues with duplicate ID or if my prefixed file for lead.php is used
            $data['description'] = isset($data['lead_note_description']) ? $data['lead_note_description'] : $data['description'];

            if (isset($data['lead_note_description'])) {
                unset($data['lead_note_description']);
            }

            $note_id = $this->misc_model->add_note($data, 'lead', $rel_id);

            if ($note_id) {
                if (isset($contacted_date)) {
                    $this->db->where('id', $rel_id);
                    $this->db->update('tblleads', [
                        'lastcontact' => $contacted_date,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $this->leads_model->log_lead_activity($rel_id, 'not_lead_activity_contacted', false, serialize([
                            get_staff_full_name(get_staff_user_id()),
                            _dt($contacted_date),
                        ]));
                    }
                }
            }
        }
        echo json_encode(['leadView' => $this->_get_lead_data($rel_id), 'id' => $rel_id]);
    }

    public function test_email_integration()
    {
        if (!is_admin()) {
            access_denied('Leads Test Email Integration');
        }

        require_once(APPPATH . 'third_party/php-imap/Imap.php');

        $mail = $this->leads_model->get_email_integration();
        $ps   = $mail->password;
        if (false == $this->encryption->decrypt($ps)) {
            set_alert('danger', _l('failed_to_decrypt_password'));
            redirect(admin_url('leads/email_integration'));
        }
        $mailbox    = $mail->imap_server;
        $username   = $mail->email;
        $password   = $this->encryption->decrypt($ps);
        $encryption = $mail->encryption;
        // open connection
        $imap = new Imap($mailbox, $username, $password, $encryption);

        if ($imap->isConnected() === false) {
            set_alert('danger', _l('lead_email_connection_not_ok') . '<br /><b>' . $imap->getError() . '</b>');
        } else {
            set_alert('success', _l('lead_email_connection_ok'));
        }

        redirect(admin_url('leads/email_integration'));
    }

    public function email_integration()
    {
        if (!is_admin()) {
            access_denied('Leads Email Intregration');
        }
        if ($this->input->post()) {
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            if (isset($data['fakeusernameremembered'])) {
                unset($data['fakeusernameremembered']);
            }
            if (isset($data['fakepasswordremembered'])) {
                unset($data['fakepasswordremembered']);
            }

            $success = $this->leads_model->update_email_integration($data);
            if ($success) {
                set_alert('success', _l('leads_email_integration_updated'));
            }
            redirect(admin_url('leads/email_integration'));
        }
        $data['roles']    = $this->roles_model->get();
        $data['sources']  = $this->leads_model->get_source();
        $data['statuses'] = $this->leads_model->get_status();

        $data['members'] = $this->staff_model->get('', [
            'active'       => 1,
            'is_not_staff' => 0,
        ]);

        $data['title']     = _l('leads_email_integration');
        $data['mail']      = $this->leads_model->get_email_integration();
        $data['bodyclass'] = 'leads-email-integration';
        $this->load->view('admin/leads/email_integration', $data);
    }

    public function change_status_color()
    {
        if ($this->input->post()) {
            $this->leads_model->change_status_color($this->input->post());
        }
    }

    public function import()
    {
        //echo "in import";
         //echo '<pre>';print_r($rows);die;
        if (!is_admin() && get_option('allow_non_admin_members_to_import_leads') != '1') {
            access_denied('Leads Import');
        }

    
        $simulate_data  = [];
        $total_imported = 0;
        if ($this->input->post()) {
         
            $file = $_FILES['file_csv']['tmp_name'];
            $handle = fopen($file, "r");
            $c = 0;//
             $entries;
            while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
            {

                if($c==0)
                {
                    $c++;
                    continue;
                }
    
        // echo $filesop[2];

        // $this->db->select('*');
        // $this->db->from('tblapplicants');
        // $this->db->where('email', $filesop[2]);
        // $query = $this->db->get();
    
       if ($query->num_rows() == 0 ){
                if($filesop[0]){
               $entries[] = array( 
                'first_name'=>$filesop[0],
                'last_name'=>$filesop[1],
                'email'=>$filesop[2],
                'phone'=>$filesop[3],
                'preffered_time'=>$filesop[4],
                'position'=>$filesop[5],
                'computer'=>$filesop[6],
                'zoom'=>$filesop[7],
                'screen'=>$filesop[8],
                'resume'=>$filesop[9],
                'DL'=>$filesop[10],
                'T&C'=>$filesop[11],
                'screen'=>$filesop[12],
                'webinar_invite'=>$filesop[13],
                'opt'=>$filesop[14],
                '_e_course'=>$filesop[15],
                'onboarded'=>$filesop[16],
                'territory_manager_interest'=>$filesop[17],
                'needs_another_webinar'=>$filesop[18],
                );
               }

            }
        }
         $this->db->insert_batch('tblapplicants', $entries); 
    }

         
       $this->load->view('admin/applicants/import', $data);
    
 }
    public function email_exists()
    {
        if ($this->input->post()) {
            // First we need to check if the email is the same
            $leadid = $this->input->post('leadid');

            if ($leadid != '') {
                $this->db->where('id', $leadid);
                $_current_email = $this->db->get('tblleads')->row();
                if ($_current_email->email == $this->input->post('email')) {
                    echo json_encode(true);
                    die();
                }
            }
            $exists = total_rows('tblleads', [
                'email' => $this->input->post('email'),
            ]);
            if ($exists > 0) {
                echo 'false';
            } else {
                echo 'true';
            }
        }
    }

    public function bulk_action()
    {
        if (!is_staff_member()) {
            $this->access_denied_ajax();
        }

        do_action('before_do_bulk_action_for_leads');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids                   = $this->input->post('ids');
            $status                = $this->input->post('status');
            $source                = $this->input->post('source');
            $assigned              = $this->input->post('assigned');
            $visibility            = $this->input->post('visibility');
            $tags                  = $this->input->post('tags');
            $last_contact          = $this->input->post('last_contact');
            $has_permission_delete = has_permission('leads', '', 'delete');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($has_permission_delete) {
                            if ($this->leads_model->delete($id)) {
                                $total_deleted++;
                            }
                        }
                    } else {
                        if ($status || $source || $assigned || $last_contact || $visibility) {
                            $update = [];
                            if ($status) {
                                // We will use the same function to update the status
                                $this->leads_model->update_lead_status([
                                    'status' => $status,
                                    'leadid' => $id,
                                ]);
                            }
                            if ($source) {
                                $update['source'] = $source;
                            }
                            if ($assigned) {
                                $update['assigned'] = $assigned;
                            }
                            if ($last_contact) {
                                $last_contact          = to_sql_date($last_contact, true);
                                $update['lastcontact'] = $last_contact;
                            }

                            if ($visibility) {
                                if ($visibility == 'public') {
                                    $update['is_public'] = 1;
                                } else {
                                    $update['is_public'] = 0;
                                }
                            }

                            if (count($update) > 0) {
                                $this->db->where('id', $id);
                                $this->db->update('tblleads', $update);
                            }
                        }
                        if ($tags) {
                            handle_tags_save($tags, $id, 'lead');
                        }
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_leads_deleted', $total_deleted));
        }
    }

    private function access_denied_ajax()
    {
        header('HTTP/1.0 404 Not Found');
        echo _l('access_denied');
        die;
    }

    public function convert_to_user($applicant_id = NULL)
    {
        $data = array();

        if($applicant_id)
        {
            $applicant_data = $this->db->where('id',$applicant_id)->get('tblapplicants')->row();
            
            if(empty($applicant_data))
            {
                $response['success'] = FALSE;
                $response['message'] = _l('Sorry No Data Available For This Applicant ID !');
                set_alert('danger', _l('Sorry No Data Available For This Applicant ID !'));
                echo json_encode($response);
                // return $response;
                exit;
            }

            if($applicant_data->is_staff)
            {
                $response['success'] = FALSE;
                $response['message'] = _l('Sorry This Applicant Is Already Converted To User !');
                set_alert('danger', _l('Sorry This Applicant Is Already Converted To User !'));
                echo json_encode($response);
                // return $response;
                exit;
            }

            $staff_data = array();
            $staff_data['email'] = $applicant_data->email;
            $staff_data['firstname'] = $applicant_data->first_name;
            $staff_data['lastname'] = $applicant_data->last_name;
            $staff_data['facebook'] = NULL;
            $staff_data['linkedin'] = NULL;
            $staff_data['phonenumber'] = $applicant_data->phone;
            $staff_data['skype'] = NULL;
            $staff_data['password'] = '$2a$08$nQnv4/fF0C5PBMWV43sy3Ot0xaiLi8CY0rkZLhzHXEbI7Edu41zYa';
            $staff_data['datecreated'] = date('Y-m-d H:i:s');
            $staff_data['profile_image'] = NULL;
            $staff_data['last_ip'] = NULL;
            $staff_data['last_login'] = NULL;
            $staff_data['last_activity'] = NULL;
            $staff_data['last_password_change'] = NULL;
            $staff_data['new_pass_key'] = NULL;
            $staff_data['new_pass_key_requested'] = NULL;
            $staff_data['admin'] = 0;
            $staff_data['role'] = 1;
            $staff_data['active'] = 1;
            $staff_data['default_language'] = NULL;
            $staff_data['direction'] = NULL;
            $staff_data['media_path_slug'] = $applicant_data->first_name;
            $staff_data['is_not_staff'] = 0;
            $staff_data['hourly_rate'] = 0.00;
            $staff_data['two_factor_auth_enabled'] = NULL;
            $staff_data['two_factor_auth_code_requested'] = NULL;
            $staff_data['email_signature'] = NULL;
            $staff_data['terms_condition'] = 1;

           
            try
            {

                $this->db->insert('tblstaff', $staff_data);
                $insert_id = $this->db->insert_id();

                if($insert_id)
                {
                    $tblapplicants_data = array();
                    $tblapplicants_data['is_staff'] = 1;
                    $tblapplicants_data['staff_id'] = $insert_id;
                    $this->db->where('id',$applicant_id); 
                    $this->db->update('tblapplicants',$tblapplicants_data);

                    $status = $this->sent_smtp_test_email($applicant_data);
                    $response['success'] = true;
                    $response['message'] = _l(' Applicant Converted To User successfully ');
                    set_alert('success', _l(' Applicant Converted To User successfully '));


                }
                else
                {   
                    $response['success'] = FALSE;
                    $response['message'] = _l('Error During Applicant Converting To User Please Try Again Latter ! ');
                    set_alert('danger', _l('Error During Applicant Converting To User Please Try Again Latter ! '));
                    
                }
            } 
            catch (Exception $e) 
            {
                $response['success'] = FALSE;
                $response['message'] = _l('Exception During Applicant Converting To User Please Try Again Latter ! ');
                set_alert('danger', _l('Exception During Applicant Converting To User Please Try Again Latter ! '));
               
            }
           
        }
        else
        {
            $response['success'] = FALSE;
            $response['message'] = _l('Some Thing Went Wrong Please Try Again Latter ! ');
            set_alert('danger', _l('Some Thing Went Wrong Please Try Again Latter ! '));
        }

        echo json_encode($response);
        exit;
    }



    public function sent_smtp_test_email($user_data)
    {
        if ($user_data) 
        {
            $this->load->config('email');
            // Simulate fake template to be parsed

            $template           = new StdClass();
            $template->fromname = get_option('companyname') != '' ? get_option('companyname') : 'Tempucheck Team';
            $template->subject  = 'Welcome to the Tempucheck Team';
            // $user_data->email = 'sbtssc@gmail.com';
            $data['email'] = $user_data->email;
            $data['password'] ='123456';
            $data['companyname'] = $template->fromname;
            $data['subject'] = $template->subject;

            $template->message = $this->load->view('admin/applicants/welcome_mail', $data,true);
            
            $template = parse_email_template($template);

            // do_action('before_send_test_smtp_email');

            $this->email->initialize();

            if (get_option('mail_engine') == 'phpmailer') {
                $this->email->set_debug_output(function ($err) {
                    if (!isset($GLOBALS['debug'])) {
                        $GLOBALS['debug'] = '';
                    }
                    $GLOBALS['debug'] .= $err . '<br />';
                    return false;
                    // return $err;
                });
                $this->email->set_smtp_debug(3);
            }

            $this->email->set_newline(config_item('newline'));
            $this->email->set_crlf(config_item('crlf'));

            $this->email->from(get_option('smtp_email'), $template->fromname);
            $this->email->to($user_data->email);

            // $systemBCC = get_option('bcc_emails');

            // if ($systemBCC != '') {
            //     $this->email->bcc($systemBCC);
            // }

            $this->email->subject($template->subject);
            $this->email->message($template->message);

            if ($this->email->send(true)) 
            {
                set_alert('success', 'Email Send Successfully ! ');

                return true;

                // do_action('smtp_test_email_success');
            } else 
            {
                 set_alert('danger', 'Email Send Error ! ');

                // set_debug_alert('<h1>Your SMTP settings are not set correctly here is the debug log.</h1><br />' . $this->email->print_debugger() . (isset($GLOBALS['debug']) ? $GLOBALS['debug'] : ''));

                // do_action('smtp_test_email_failed');
            }
        }

        return false;
    }


    public function add_compamy()
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
            set_alert('danger', 'Plz Check Captcha Code Then Submit Form');
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

            $status = $this->db->insert('tblcomany',$company_content);

            if($status)
            {                
                 set_alert('success', 'Company Data Inserted Successfully');
            }
            else
            {
                 set_alert('warning', 'Company Data Insert Error');
            }
             redirect(base_url('admin/applicants'));            
        }

        $data['title'] = _l('Add Company Details');
        $data['company_id'] = NULL;
        $data['bodyclass']     = 'company_content';
        $this->load->view('admin/company/form', $data);    

    }


    public function update_company($company_id = NULL)
    {
        close_setup_menu();
        $staff_id = get_staff_user_id();

        $company_data_array = $this->db->where('id',$company_id)->get('tblcomany')->row();
        if(empty($company_data_array))
        {
            set_alert('warning', 'Invalid Company Id !');
            return redirect(base_url('admin/applicants'));
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
            set_alert('danger', 'Plz Check Captcha Code Then Submit Form');
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

            $this->db->where('id',$company_id)->update('tblcomany',$company_content);
            $status = $this->db->affected_rows();

            if($status)
            {                
                 set_alert('success', 'Company Data Update Successfully');
            }
            else
            {
                 set_alert('warning', 'Company Data Update Error');
            }

            redirect(base_url('admin/applicants'));            
        }

      
        $data['company_data_array'] = $company_data_array;
        $data['title'] = _l('Update Company Details');
        $data['company_id'] = $company_id;
        $data['bodyclass']     = 'company_content';
        $this->load->view('admin/company/form', $data);    
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
