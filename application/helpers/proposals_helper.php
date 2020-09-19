<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check if proposal hash is equal
 * @param  mixed $id   proposal id
 * @param  string $hash proposal hash
 * @return void
 */
function check_proposal_restrictions($id, $hash)
{
    $CI = & get_instance();
    $CI->load->model('proposals_model');
    if (!$hash || !$id) {
        show_404();
    }
    $proposal = $CI->proposals_model->get($id);
    if (!$proposal || ($proposal->hash != $hash)) {
        show_404();
    }
}

/**
 * Check if proposal email template for expiry reminders is enabled
 * @return boolean
 */
function is_proposals_email_expiry_reminder_enabled()
{
    return total_rows('tblemailtemplates', ['slug' => 'proposal-expiry-reminder', 'active' => 1]) > 0;
}

/**
 * Check if there are sources for sending proposal expiry reminders
 * Will be either email or SMS
 * @return boolean
 */
function is_proposals_expiry_reminders_enabled()
{
    return is_proposals_email_expiry_reminder_enabled() || is_sms_trigger_active(SMS_TRIGGER_PROPOSAL_EXP_REMINDER);
}

/**
 * Return proposal status color class based on twitter bootstrap
 * @param  mixed  $id
 * @param  boolean $replace_default_by_muted
 * @return string
 */
function proposal_status_color_class($id, $replace_default_by_muted = false)
{
    if ($id == 1) {
        $class = 'default';
    } elseif ($id == 2) {
        $class = 'danger';
    } elseif ($id == 3) {
        $class = 'success';
    } elseif ($id == 4 || $id == 5) {
        // status sent and revised
        $class = 'info';
    } elseif ($id == 6) {
        $class = 'default';
    }
    if ($class == 'default') {
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    }

    return $class;
}
/**
 * Format proposal status with label or not
 * @param  mixed  $status  proposal status id
 * @param  string  $classes additional label classes
 * @param  boolean $label   to include the label or return just translated text
 * @return string
 */
function format_proposal_status($status, $classes = '', $label = true)
{
    $id = $status;
    if ($status == 1) {
        $status      = _l('proposal_status_open');
        $label_class = 'default';
    } elseif ($status == 2) {
        $status      = _l('proposal_status_declined');
        $label_class = 'danger';
    } elseif ($status == 3) {
        $status      = _l('proposal_status_accepted');
        $label_class = 'success';
    } elseif ($status == 4) {
        $status      = _l('proposal_status_sent');
        $label_class = 'info';
    } elseif ($status == 5) {
        $status      = _l('proposal_status_revised');
        $label_class = 'info';
    } elseif ($status == 6) {
        $status      = _l('proposal_status_draft');
        $label_class = 'default';
    }

    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status proposal-status-' . $id . '">' . $status . '</span>';
    }

    return $status;
}

/**
 * Function that format proposal number based on the prefix option and the proposal id
 * @param  mixed $id proposal id
 * @return string
 */
function format_proposal_number($id)
{
    return get_option('proposal_number_prefix') . str_pad($id, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
}


/**
 * Function that return proposal item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_proposal_item_taxes($itemid)
{
    $CI = & get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'proposal');
    $taxes = $CI->db->get('tblitemstax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}


/**
 * Calculate proposal percent by status
 * @param  mixed $status          proposal status
 * @param  mixed $total_estimates in case the total is calculated in other place
 * @return array
 */
function get_proposals_percent_by_status($status, $total_proposals = '')
{
    $has_permission_view                 = has_permission('proposals', '', 'view');
    $has_permission_view_own             = has_permission('proposals', '', 'view_own');
    $allow_staff_view_proposals_assigned = get_option('allow_staff_view_proposals_assigned');
    $staffId                             = get_staff_user_id();

    $whereUser = '';
    if (!$has_permission_view) {
        if ($has_permission_view_own) {
            $whereUser = '(addedfrom=' . $staffId;
            if ($allow_staff_view_proposals_assigned == 1) {
                $whereUser .= ' OR assigned=' . $staffId;
            }
            $whereUser .= ')';
        } else {
            $whereUser .= 'assigned=' . $staffId;
        }
    }

    if (!is_numeric($total_proposals)) {
        $total_proposals = total_rows('tblproposals', $whereUser);
    }

    $data            = [];
    $total_by_status = 0;
    $where           = 'status=' . $status;
    if (!$has_permission_view) {
        $where .= ' AND (' . $whereUser . ')';
    }

    $total_by_status = total_rows('tblproposals', $where);
    $percent         = ($total_proposals > 0 ? number_format(($total_by_status * 100) / $total_proposals, 2) : 0);

    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_proposals;

    return $data;
}

/**
 * Function that will search possible proposal templates in applicaion/views/admin/proposal/templates
 * Will return any found files and user will be able to add new template
 * @return array
 */
function get_proposal_templates()
{
    $proposal_templates = [];
    if (is_dir(VIEWPATH . 'admin/proposals/templates')) {
        foreach (list_files(VIEWPATH . 'admin/proposals/templates') as $template) {
            $proposal_templates[] = $template;
        }
    }

    return $proposal_templates;
}
/**
 * Check if staff member can view proposal
 * @param  mixed $id proposal id
 * @param  mixed $staff_id
 * @return boolean
 */
function user_can_view_proposal($id, $staff_id = false)
{
    $CI = &get_instance();

    $staff_id = $staff_id ? $staff_id : get_staff_user_id();

    if (has_permission('proposals', $staff_id, 'view')) {
        return true;
    }

    $CI->db->select('id, addedfrom, assigned');
    $CI->db->from('tblproposals');
    $CI->db->where('id', $id);
    $proposal = $CI->db->get()->row();

    if ((has_permission('proposals', $staff_id, 'view_own') && $proposal->addedfrom == $staff_id)
            || ($proposal->assigned == $staff_id && get_option('allow_staff_view_proposals_assigned') == 1)) {
        return true;
    }

    return false;
}
function parse_proposal_content_merge_fields($proposal)
{
    $id           = is_array($proposal) ? $proposal['id'] : $proposal->id;
    $merge_fields = [];
    $merge_fields = array_merge($merge_fields, get_proposal_merge_fields($id));
    $merge_fields = array_merge($merge_fields, get_other_merge_fields());
    foreach ($merge_fields as $key => $val) {
        $content = is_array($proposal) ? $proposal['content'] : $proposal->content;

        if (stripos($content, $key) !== false) {
            if (is_array($proposal)) {
                $proposal['content'] = str_ireplace($key, $val, $content);
            } else {
                $proposal->content = str_ireplace($key, $val, $content);
            }
        } else {
            if (is_array($proposal)) {
                $proposal['content'] = str_ireplace($key, '', $content);
            } else {
                $proposal->content = str_ireplace($key, '', $content);
            }
        }
    }

    return $proposal;
}

/**
 * Check if staff member have assigned proposals / added as sale agent
 * @param  mixed $staff_id staff id to check
 * @return boolean
 */
function staff_has_assigned_proposals($staff_id = '')
{
    $CI         = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->object_cache->get('staff-total-assigned-proposals-' . $staff_id);
    if (is_numeric($cache)) {
        $result = $cache;

    } else {
        $result = total_rows('tblproposals', ['assigned' => $staff_id]);
        $CI->object_cache->add('staff-total-assigned-proposals-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}

function get_proposals_sql_where_staff($staff_id)
{
    $has_permission_view_own            = has_permission('proposals', '', 'view_own');
    $allow_staff_view_invoices_assigned = get_option('allow_staff_view_proposals_assigned');
    $whereUser                          = '';
    if ($has_permission_view_own) {
        $whereUser = '((tblproposals.addedfrom=' . $staff_id . ' AND tblproposals.addedfrom IN (SELECT staffid FROM tblstaffpermissions JOIN tblpermissions ON tblpermissions.permissionid=tblstaffpermissions.permissionid WHERE tblpermissions.name = "proposals" AND can_view_own=1))';
        if ($allow_staff_view_invoices_assigned == 1) {
            $whereUser .= ' OR assigned=' . $staff_id;
        }
        $whereUser .= ')';
    } else {
        $whereUser .= 'assigned=' . $staff_id;
    }

    return $whereUser;
}

function prepare_proposals_for_export($rel_id, $rel_type)
{
    // $readProposalsDir = '';
    // $tmpDir           = get_temp_dir();

    $CI               = &get_instance();

    if (!class_exists('proposals_model')) {
        $CI->load->model('proposals_model');
    }

    $CI->db->where('rel_id', $rel_id);
    $CI->db->where('rel_type', $rel_type);

    $proposals = $CI->db->get('tblproposals')->result_array();

    $CI->db->where('show_on_client_portal', 1);
    $CI->db->where('fieldto', 'proposal');
    $CI->db->order_by('field_order', 'asc');
    $custom_fields = $CI->db->get('tblcustomfields')->result_array();
/*
    if (count($proposals) > 0) {
        $uniqueIdentifier = $tmpDir . $rel_id . time() . '-proposals';
        $readProposalsDir = $uniqueIdentifier;
    }*/
    $CI->load->model('currencies_model');
    foreach ($proposals as $proposaArrayKey => $proposal) {

        // $proposal['attachments'] = _prepare_attachments_array_for_export($CI->proposals_model->get_attachments($proposal['id']));

       // $proposals[$proposaArrayKey] = parse_proposal_content_merge_fields($proposal);

        $proposals[$proposaArrayKey]['country'] = get_country($proposal['country']);

        $proposals[$proposaArrayKey]['currency'] = $CI->currencies_model->get($proposal['currency']);

        $proposals[$proposaArrayKey]['items'] = _prepare_items_array_for_export(get_items_by_type('proposal', $proposal['id']), 'proposal');

        $proposals[$proposaArrayKey]['comments'] = $CI->proposals_model->get_comments($proposal['id']);

        $proposals[$proposaArrayKey]['views'] = get_views_tracking('proposal', $proposal['id']);

        $proposals[$proposaArrayKey]['tracked_emails'] = get_tracked_emails($proposal['id'], 'proposal');

        $proposals[$proposaArrayKey]['additional_fields'] = [];
        foreach ($custom_fields as $cf) {
            $proposals[$proposaArrayKey]['additional_fields'][] = [
                    'name'  => $cf['name'],
                    'value' => get_custom_field_value($proposal['id'], $cf['id'], 'proposal'),
                ];
        }

      /*  $tmpProposalsDirName = $uniqueIdentifier;
        if (!is_dir($tmpProposalsDirName)) {
            mkdir($tmpProposalsDirName, 0755);
        }

        $tmpProposalsDirName = $tmpProposalsDirName . '/' . $proposal['id'];

        mkdir($tmpProposalsDirName, 0755);*/

/*        if (count($proposal['attachments']) > 0 || !empty($proposal['signature'])) {
            $attachmentsDir = $tmpProposalsDirName . '/attachments';
            mkdir($attachmentsDir, 0755);

            foreach ($proposal['attachments'] as $att) {
                xcopy(get_upload_path_by_type('proposal') . $proposal['id'] . '/' . $att['file_name'], $attachmentsDir . '/' . $att['file_name']);
            }

            if (!empty($proposal['signature'])) {
                xcopy(get_upload_path_by_type('proposal') . $proposal['id'] . '/' . $proposal['signature'], $attachmentsDir . '/' . $proposal['signature']);
            }
        }*/

        // unset($proposal['id']);

        // $fp = fopen($tmpProposalsDirName . '/proposal.json', 'w');
        // fwrite($fp, json_encode($proposal, JSON_PRETTY_PRINT));
        // fclose($fp);
    }

    return $proposals;
}

function create_proposal_to_customer($proposalId)
{
    $CI = get_instance();

    $CI->db->where('id', $proposalId);
    $CI->db->where('rel_type', 'lead');

    $proposals = $CI->db->get('tblproposals')->result_array();
    foreach($proposals as $proposal) {
        
        $CI->db->where('id', $proposal['rel_id']);
        $leads = $CI->db->get('tblleads')->result_array();
        foreach($leads as $lead) {
            if (!is_staff_member()) {
                access_denied('Lead Convert to Customer');
            }
            $data = array(
                'leadid' => $lead['id'],
                'default_language' => '',
                'firstname' => $lead['name'],
                'lastname' => '.',
                //'title' => $lead['title'],
                'email' => $lead['email'],
                'company' => '',
                'phonenumber' => $lead['phonenumber'],
                'website' => $lead['website'],
                'address' => $lead['address'],
                'city' => $lead['city'],
                'state' => $lead['state'],
                'country' => $lead['country'],
                'zip' => $lead['zip'],
                'fakeusernameremembered' => '',
                'fakepasswordremembered' => '',
                'password' => '',
                'send_set_password_email' => 'on',
                'billing_street' => $lead['address'],
                'billing_city' => $lead['city'],
                'billing_state' => $lead['state'],
                'billing_zip' => $lead['zip'],
                'billing_country' => $lead['country']
            );
            
            //echo "<pre>";print_r($data);die; 
            $CI->load->model('clients_model');
            $CI->load->model('leads_model');
            
            $id = $CI->clients_model->add($data, true);
            
            if ($id) {
                update_primary_contact_user_id($id);
                $primary_contact_id = get_primary_contact_user_id1($id);  

                //create project
                createproject($id, $proposal);
                
                
                $CI->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted', false, serialize([
                    get_staff_full_name(),
                ]));
                $default_status = $CI->leads_model->get_status('', [
                    'isdefault' => 1,
                ]);
                
                $CI->db->where('id', $data['leadid']);
                $CI->db->update('tblleads', [
                    'date_converted' => date('Y-m-d H:i:s'),
                    'status'         => $default_status[0]['id'],
                    'junk'           => 0,
                    'lost'           => 0,
                ]);
                
                // Check if lead email is different then client email
                /*$contact = $CI->clients_model->get_contact(get_primary_contact_user_id1($id));
                if ($contact->email != $original_lead_email) {
                    if ($original_lead_email != '') {
                        $CI->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted_email', false, serialize([
                            $original_lead_email,
                            $contact->email,
                        ]));
                    }
                }*/
                if (isset($include_leads_custom_fields)) {
                    foreach ($include_leads_custom_fields as $fieldid => $value) {
                        // checked don't merge
                        if ($value == 5) {
                            continue;
                        }
                        // get the value of this leads custom fiel
                        $CI->db->where('relid', $data['leadid']);
                        $CI->db->where('fieldto', 'leads');
                        $CI->db->where('fieldid', $fieldid);
                        $lead_custom_field_value = $CI->db->get('tblcustomfieldsvalues')->row()->value;
                        // Is custom field for contact ot customer
                        if ($value == 1 || $value == 4) {
                            if ($value == 4) {
                                $field_to = 'contacts';
                            } else {
                                $field_to = 'customers';
                            }
                            $CI->db->where('id', $fieldid);
                            $field = $CI->db->get('tblcustomfields')->row();
                            // check if this field exists for custom fields
                            $CI->db->where('fieldto', $field_to);
                            $CI->db->where('name', $field->name);
                            $exists               = $CI->db->get('tblcustomfields')->row();
                            $copy_custom_field_id = null;
                            if ($exists) {
                                $copy_custom_field_id = $exists->id;
                            } else {
                                // there is no name with the same custom field for leads at the custom side create the custom field now
                                $CI->db->insert('tblcustomfields', [
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
                                $new_customer_field_id = $CI->db->insert_id();
                                if ($new_customer_field_id) {
                                    $copy_custom_field_id = $new_customer_field_id;
                                }
                            }
                            if ($copy_custom_field_id != null) {
                                $insert_to_custom_field_id = $id;
                                if ($value == 4) {
                                    $insert_to_custom_field_id = get_primary_contact_user_id1($id);
                                }
                                $CI->db->insert('tblcustomfieldsvalues', [
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
                                    $CI->db->where('iso2', $lead_custom_field_value);
                                    $CI->db->or_where('short_name', $lead_custom_field_value);
                                    $CI->db->or_like('long_name', $lead_custom_field_value);
                                    $country = $CI->db->get('tblcountries')->row();
                                    if ($country) {
                                        $lead_custom_field_value = $country->country_id;
                                    } else {
                                        $lead_custom_field_value = 0;
                                    }
                                }
                                $CI->db->where('userid', $id);
                                $CI->db->update('tblclients', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        } elseif ($value == 3) {
                            if (isset($merge_db_contact_fields)) {
                                $db_field = $merge_db_contact_fields[$fieldid];
                                if ($db_field == '') {
                                    continue;
                                }
                                $CI->db->where('id', $primary_contact_id);
                                $CI->db->update('tblcontacts', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        }
                    }
                }
                // set the lead to status client in case is not status client
                $CI->db->where('isdefault', 1);
                $status_client_id = $CI->db->get('tblleadsstatus')->row()->id;
                $CI->db->where('id', $data['leadid']);
                $CI->db->update('tblleads', [
                    'status' => $status_client_id,
                ]);

                set_alert('success', _l('lead_to_client_base_converted_success'));

                if (is_gdpr() && get_option('gdpr_after_lead_converted_delete') == '1') {
                    $CI->leads_model->delete($data['leadid']);

                    $CI->db->where('userid', $id);
                    $CI->db->update('tblclients', ['leadid' => null]);
                }

                logActivity('Created Lead Client Profile [LeadID: ' . $data['leadid'] . ', ClientID: ' . $id . ']');
                do_action('lead_converted_to_customer', ['lead_id' => $data['leadid'], 'customer_id' => $id]);
                //redirect(admin_url('clients/client/' . $id));
                
                
                
                //create invoice
                
            }
            
        }
    }
}

/**
 * Get primary contact user id for specific customer
 * @param  mixed $userid
 * @return mixed
 */
function get_primary_contact_user_id1($userid)
{
    $CI = &get_instance();
    $CI->db->where('userid', $userid);
    $CI->db->where('is_primary', 1);
    $row = $CI->db->get('tblcontacts')->row();

    if ($row) {
        return $row->id;
    }

    return false;
}

function update_primary_contact_user_id($userid)
{
    $CI = &get_instance();
    $CI->db->where('userid', $userid);
    $CI->db->where('is_primary', 1);
    $row = $CI->db->get('tblcontacts')->row();

    if (!$row) {
        $CI->db->where('userid', $userid);
        $setData = array(
        'is_primary' => 1
        );
        $CI->db->update('tblcontacts', $setData);
    }
}

function createproject($userid, $proposal)
{
    $CI = &get_instance();
    $CI->load->model('projects_model');
    $data = array();
    $data['name'] = $proposal['subject'];
    $data['description'] = '';
    $data['status'] = 2;
    $data['clientid'] = $userid;
    $data['billing_type'] = 1;
    $data['start_date'] = date('m/d/Y');
    $data['project_created'] = date('m/d/Y');
    $data['progress'] = 0;
    $data['progress_from_tasks'] = 1;
    $data['project_cost'] = $proposal['total'];
    $data['addedfrom'] = $proposal['addedfrom'];
    $id = $CI->projects_model->add($data, true);
    createTask($id, 'Signed Questionnaire');
    createTask($id, 'Signed 1040');
    createTask($id, 'DL');
    createTask($id, 'Tax Documents');
}

function createTask($projectId, $task)
{
    $CI = &get_instance();
    $CI->load->model('tasks_model');
    $date = time();
    $date = strtotime("+7 day", $date);      
            
    $data = Array
    (
        'is_public' => 'on',
        'billable' => 'on',
        'name' => $task,
        'hourly_rate' => 0,
        'milestone' => 0,
        'startdate' => date("m/d/Y"),
        'duedate' => date('m/d/Y', $date),
        'priority' => 2,
        'repeat_every' => 0,
        'repeat_every_custom' => 1,
        'repeat_type_custom' => 'day',
        'rel_type' => 'project',
        'rel_id' => $projectId,
        'tags' => '',
        'description' => $task
    );
    $id = $CI->tasks_model->add($data, true);
}