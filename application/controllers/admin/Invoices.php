<?php

defined('BASEPATH') or exit('No direct script access allowed');

// die(__DIR__ . './../../../vendor/autoload.php');
require_once(__DIR__ . './../../../vendor/autoload.php');

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Item;

class Invoices extends Admin_controller
{



    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
        $this->load->model('credit_notes_model');
    }


    function parseAuthRedirectUrl($url)
    {
        parse_str($url,$qsArray);
        return array(
            'code' => $qsArray['code'],
            'realmId' => $qsArray['realmId']
        );
    }

    /* Get all invoices in case user go on index page */
    public function index($id = '')
    {
        $this->list_invoices($id);
    }


    /* List all invoices datatables */
    public function callback($id = '')
    {

        $dataService = DataService::Configure(array(
            'auth_mode' => $this->config->item('auth_mode'),
            'ClientID' => $this->config->item('client_id'),
            'ClientSecret' => $this->config->item('client_secret'),
            'RedirectURI' => $this->config->item('oauth_redirect_uri'),
            'scope' => $this->config->item('oauth_scope'),
            'baseUrl' => $this->config->item('baseUrl'),
        ));

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $parseUrl = $this->parseAuthRedirectUrl($_SERVER['QUERY_STRING']);

        /*
         * Update the OAuth2Token
         */
        $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);
        $dataService->updateOAuth2Token($accessToken);

        /*
         * Setting the accessToken for session variable
         */
        $_SESSION['sessionAccessToken'] = $accessToken;
        // echo '<pre>';print_r($_SESSION);die;

    }



    /* List all invoices datatables */
    public function list_invoices($id = '')
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
        } 

        $dataService = DataService::Configure(array(
            'auth_mode' => $this->config->item('auth_mode'),
            'ClientID' => $this->config->item('client_id'),
            'ClientSecret' => $this->config->item('client_secret'),
            'RedirectURI' => $this->config->item('oauth_redirect_uri'),
            'scope' => $this->config->item('oauth_scope'),
            'baseUrl' => $this->config->item('baseUrl'),
        ));


        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();


        // Store the url in PHP Session Object;
        $_SESSION['authUrl'] = $authUrl;

        //set the access token using the auth object
        if (isset($_SESSION['sessionAccessToken'])) 
        {

            $accessToken = $_SESSION['sessionAccessToken'];
            $accessTokenJson = array('token_type' => 'bearer',
                'access_token' => $accessToken->getAccessToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
                'expires_in' => $accessToken->getAccessTokenExpiresAt()
            );
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService -> getOAuth2LoginHelper();
            $CompanyInfo = $dataService->getCompanyInfo();

            // $CompanyInfo = $dataService->getCompanyInfo();
        }



        if (!has_permission('invoices', '', 'view') && !has_permission('invoices', '', 'view_own') && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('invoices');
        }

        close_setup_menu();

        $this->load->model('payment_modes_model');

        $data['payment_modes']        = $this->payment_modes_model->get('', [], true);
        $data['invoiceid']            = $id;
        $data['title']                = _l('invoices');
        $data['invoices_years']       = $this->invoices_model->get_invoices_years();
        $data['invoices_sale_agents'] = $this->invoices_model->get_sale_agents();
        $data['invoices_statuses']    = $this->invoices_model->get_statuses();
        $data['bodyclass']            = 'invoices-total-manual';
        $data['authUrl']              = $authUrl;

        $this->load->view('admin/invoices/manage', $data);
    }

    public function table($clientid = '')
    {
        if (!has_permission('invoices', '', 'view') && !has_permission('invoices', '', 'view_own') && get_option('allow_staff_view_invoices_assigned') == '0') {
            ajax_access_denied();
        }

        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [], true);

        $this->app->get_table_data('invoices', [
            'clientid' => $clientid,
            'data'     => $data,
        ]);
    }

    public function client_change_data($customer_id, $current_invoice = '')
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('projects_model');
            $data                     = [];
            $data['billing_shipping'] = $this->clients_model->get_customer_billing_and_shipping_details($customer_id);
            $data['client_currency']  = $this->clients_model->get_customer_default_currency($customer_id);

            $data['customer_has_projects'] = customer_has_projects($customer_id);
            $data['billable_tasks']        = $this->tasks_model->get_billable_tasks($customer_id);

            if ($current_invoice != '') {
                $this->db->select('status');
                $this->db->where('id', $current_invoice);
                $current_invoice_status = $this->db->get('tblinvoices')->row()->status;
            }

            $_data['invoices_to_merge'] = !isset($current_invoice_status) || (isset($current_invoice_status) && $current_invoice_status != 5) ? $this->invoices_model->check_for_merge_invoice($customer_id, $current_invoice) : [];

            $data['merge_info'] = $this->load->view('admin/invoices/merge_invoice', $_data, true);

            $this->load->model('currencies_model');

            $__data['expenses_to_bill'] = !isset($current_invoice_status) || (isset($current_invoice_status) && $current_invoice_status != 5) ? $this->invoices_model->get_expenses_to_bill($customer_id) : [];

            $data['expenses_bill_info'] = $this->load->view('admin/invoices/bill_expenses', $__data, true);
            echo json_encode($data);
        }
    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('invoices', '', 'edit')) {
            $affected_rows = 0;

            $this->db->where('id', $id);
            $this->db->update('tblinvoices', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $affected_rows++;
            }

            if ($affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('invoice'));
            }
        }
        echo json_encode($response);
        die;
    }

    public function validate_invoice_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $date            = $this->input->post('date');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');
        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }
        if (total_rows('tblinvoices', [
            'YEAR(date)' => date('Y', strtotime(to_sql_date($date))),
            'number' => $number,
        ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_invoice($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'invoice', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_invoice($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'invoice');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function pause_overdue_reminders($id)
    {
        if (has_permission('invoices', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update('tblinvoices', ['cancel_overdue_reminders' => 1]);
        }
        redirect(admin_url('invoices/list_invoices/' . $id));
    }

    public function resume_overdue_reminders($id)
    {
        if (has_permission('invoices', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update('tblinvoices', ['cancel_overdue_reminders' => 0]);
        }
        redirect(admin_url('invoices/list_invoices/' . $id));
    }

    public function mark_as_cancelled($id)
    {
        if (!has_permission('invoices', '', 'edit') && !has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }

        $success = $this->invoices_model->mark_as_cancelled($id);

        if ($success) {
            set_alert('success', _l('invoice_marked_as_cancelled_successfully'));
        }

        redirect(admin_url('invoices/list_invoices/' . $id));
    }

    public function unmark_as_cancelled($id)
    {
        if (!has_permission('invoices', '', 'edit') && !has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        $success = $this->invoices_model->unmark_as_cancelled($id);
        if ($success) {
            set_alert('success', _l('invoice_unmarked_as_cancelled'));
        }
        redirect(admin_url('invoices/list_invoices/' . $id));
    }

    public function copy($id)
    {
        if (!$id) {
            redirect(admin_url('invoices'));
        }
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        $new_id = $this->invoices_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('invoice_copy_success'));
            redirect(admin_url('invoices/invoice/' . $new_id));
        } else {
            set_alert('success', _l('invoice_copy_fail'));
        }
        redirect(admin_url('invoices/invoice/' . $id));
    }

    public function get_merge_data($id)
    {
        $invoice = $this->invoices_model->get($id);
        $cf      = get_custom_fields('items');

        $i = 0;

        foreach ($invoice->items as $item) {
            $invoice->items[$i]['taxname']          = get_invoice_item_taxes($item['id']);
            $invoice->items[$i]['long_description'] = clear_textarea_breaks($item['long_description']);
            $this->db->where('item_id', $item['id']);
            $rel              = $this->db->get('tblitemsrelated')->result_array();
            $item_related_val = '';
            $rel_type         = '';
            foreach ($rel as $item_related) {
                $rel_type = $item_related['rel_type'];
                $item_related_val .= $item_related['rel_id'] . ',';
            }
            if ($item_related_val != '') {
                $item_related_val = substr($item_related_val, 0, -1);
            }
            $invoice->items[$i]['item_related_formatted_for_input'] = $item_related_val;
            $invoice->items[$i]['rel_type']                         = $rel_type;

            $invoice->items[$i]['custom_fields'] = [];

            foreach ($cf as $custom_field) {
                $custom_field['value']                 = get_custom_field_value($item['id'], $custom_field['id'], 'items');
                $invoice->items[$i]['custom_fields'][] = $custom_field;
            }
            $i++;
        }
        echo json_encode($invoice);
    }

    public function get_bill_expense_data($id)
    {
        $this->load->model('expenses_model');
        $expense = $this->expenses_model->get($id);

        $expense->qty              = 1;
        $expense->long_description = clear_textarea_breaks($expense->description);
        $expense->description      = $expense->name;
        $expense->rate             = $expense->amount;
        if ($expense->tax != 0) {
            $expense->taxname = [];
            array_push($expense->taxname, $expense->tax_name . '|' . $expense->taxrate);
        }
        if ($expense->tax2 != 0) {
            array_push($expense->taxname, $expense->tax_name2 . '|' . $expense->taxrate2);
        }
        echo json_encode($expense);
    }

    /* Add new invoice or update existing */
    public function invoice($id = '')
    {
        if ($this->input->post()) {
            $invoice_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('invoices', '', 'create')) {
                    access_denied('invoices');
                }
                $id = $this->invoices_model->add($invoice_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('invoice')));
                    redirect(admin_url('invoices/list_invoices/' . $id));
                }
            } else {
                if (!has_permission('invoices', '', 'edit')) {
                    access_denied('invoices');
                }
                $success = $this->invoices_model->update($invoice_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('invoice')));
                }
                redirect(admin_url('invoices/list_invoices/' . $id));
            }
        }
        if ($id == '') {
            $title                  = _l('create_new_invoice');
            $data['billable_tasks'] = [];
        } else {
            $invoice = $this->invoices_model->get($id);

            if (!$invoice || !user_can_view_invoice($id)) {
                blank_page(_l('invoice_not_found'));
            }

            $data['invoices_to_merge'] = $this->invoices_model->check_for_merge_invoice($invoice->clientid, $invoice->id);
            $data['expenses_to_bill']  = $this->invoices_model->get_expenses_to_bill($invoice->clientid);

            $data['invoice']        = $invoice;
            $data['edit']           = true;
            $data['billable_tasks'] = $this->tasks_model->get_billable_tasks($invoice->clientid, !empty($invoice->project_id) ? $invoice->project_id : '');

            $title = _l('edit', _l('invoice_lowercase')) . ' - ' . format_invoice_number($invoice->id);
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');

        $data['ajaxItems'] = false;
        if (total_rows('tblitems') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['staff']     = $this->staff_model->get('', ['active' => 1]);
        $data['title']     = $title;
        $data['bodyclass'] = 'invoice';
        $this->load->view('admin/invoices/invoice', $data);
    }




    

    /* Get all invoice data used when user click on invoiec number in a datatable left side*/
    public function get_invoice_data_ajax($id)
    {
        if (!has_permission('invoices', '', 'view') && !has_permission('invoices', '', 'view_own') && get_option('allow_staff_view_invoices_assigned') == '0') {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die(_l('invoice_not_found'));
        }

        $invoice = $this->invoices_model->get($id);

        if (!$invoice || !user_can_view_invoice($id)) {
            echo _l('invoice_not_found');
            die;
        }

        $invoice->date    = _d($invoice->date);
        $invoice->duedate = _d($invoice->duedate);
        $template_name    = 'invoice-send-to-client';
        if ($invoice->sent == 1) {
            $template_name = 'invoice-already-send';
        }

        $template_name = do_action('after_invoice_sent_template_statement', $template_name);

        $contact = $this->clients_model->get_contact(get_primary_contact_user_id($invoice->clientid));
        $email   = '';
        if ($contact) {
            $email = $contact->email;
        }

        $data['template'] = get_email_template_for_sending($template_name, $email);

        $data['invoices_to_merge'] = $this->invoices_model->check_for_merge_invoice($invoice->clientid, $id);
        $data['template_name']     = $template_name;
        $this->db->where('slug', $template_name);
        $this->db->where('language', 'english');
        $template_result = $this->db->get('tblemailtemplates')->row();

        $data['template_system_name'] = $template_result->name;
        $data['template_id']          = $template_result->emailtemplateid;

        $data['template_disabled'] = false;
        if (total_rows('tblemailtemplates', ['slug' => $data['template_name'], 'active' => 0]) > 0) {
            $data['template_disabled'] = true;
        }
        // Check for recorded payments
        $this->load->model('payments_model');
        $data['members']                    = $this->staff_model->get('', ['active' => 1]);
        $data['payments']                   = $this->payments_model->get_invoice_payments($id);
        $data['activity']                   = $this->invoices_model->get_invoice_activity($id);
        $data['totalNotes']                 = total_rows('tblnotes', ['rel_id' => $id, 'rel_type' => 'invoice']);
        $data['invoice_recurring_invoices'] = $this->invoices_model->get_invoice_recurring_invoices($id);

        $data['applied_credits'] = $this->credit_notes_model->get_applied_invoice_credits($id);
        // This data is used only when credit can be applied to invoice
        if (credits_can_be_applied_to_invoice($invoice->status)) {
            $data['credits_available'] = $this->credit_notes_model->total_remaining_credits_by_customer($invoice->clientid);

            if ($data['credits_available'] > 0) {
                $data['open_credits'] = $this->credit_notes_model->get_open_credits($invoice->clientid);
            }

            $customer_currency = $this->clients_model->get_customer_default_currency($invoice->clientid);
            $this->load->model('currencies_model');

            if ($customer_currency != 0) {
                $data['customer_currency'] = $this->currencies_model->get($customer_currency);
            } else {
                $data['customer_currency'] = $this->currencies_model->get_base_currency();
            }
        }

        $data['invoice'] = $invoice;
        $this->load->view('admin/invoices/invoice_preview_template', $data);
    }

    public function apply_credits($invoice_id)
    {
        $total_credits_applied = 0;
        foreach ($this->input->post('amount') as $credit_id => $amount) {
            $success = $this->credit_notes_model->apply_credits($credit_id, [
            'invoice_id' => $invoice_id,
            'amount'     => $amount,
        ]);
            if ($success) {
                $total_credits_applied++;
            }
        }

        if ($total_credits_applied > 0) {
            update_invoice_status($invoice_id, true);
            set_alert('success', _l('invoice_credits_applied'));
        }
        redirect(admin_url('invoices/list_invoices/' . $invoice_id));
    }

    public function get_invoices_total()
    {
        if ($this->input->post()) {
            load_invoices_total_template();
        }
    }

    /* Record new inoice payment view */
    public function record_invoice_payment_ajax($id)
    {
        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $data['invoice']  = $invoice  = $this->invoices_model->get($id);
        $data['payments'] = $this->payments_model->get_invoice_payments($id);
        $this->load->view('admin/invoices/record_payment_template', $data);
    }

    /* This is where invoice payment record $_POST data is send */
    public function record_payment()
    {
        if (!has_permission('payments', '', 'create')) {
            access_denied('Record Payment');
        }
        if ($this->input->post()) {
            $this->load->model('payments_model');
            $id = $this->payments_model->process_payment($this->input->post(), '');
            if ($id) {
                set_alert('success', _l('invoice_payment_recorded'));
                redirect(admin_url('payments/payment/' . $id));
            } else {
                set_alert('danger', _l('invoice_payment_record_failed'));
            }
            redirect(admin_url('invoices/list_invoices/' . $this->input->post('invoiceid')));
        }
    }

    /* Send invoiece to email */
    public function send_to_email($id)
    {
        $canView = user_can_view_invoice($id);
        if (!$canView) {
            access_denied('Invoices');
        } else {
            if (!has_permission('invoices', '', 'view') && !has_permission('invoices', '', 'view_own') && $canView == false) {
                access_denied('Invoices');
            }
        }

        try {
            $success = $this->invoices_model->send_invoice_to_client($id, '', $this->input->post('attach_pdf'), $this->input->post('cc'));
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('invoice_sent_to_client_success'));
        } else {
            set_alert('danger', _l('invoice_sent_to_client_fail'));
        }
        redirect(admin_url('invoices/list_invoices/' . $id));
    }

    /* Delete invoice payment*/
    public function delete_payment($id, $invoiceid)
    {
        if (!has_permission('payments', '', 'delete')) {
            access_denied('payments');
        }
        $this->load->model('payments_model');
        if (!$id) {
            redirect(admin_url('payments'));
        }
        $response = $this->payments_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('payment')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('payment_lowercase')));
        }
        redirect(admin_url('invoices/list_invoices/' . $invoiceid));
    }

    /* Delete invoice */
    public function delete($id)
    {
        if (!has_permission('invoices', '', 'delete')) {
            access_denied('invoices');
        }
        if (!$id) {
            redirect(admin_url('invoices/list_invoices'));
        }
        $success = $this->invoices_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('invoice')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('invoice_lowercase')));
        }
        if (strpos($_SERVER['HTTP_REFERER'], 'list_invoices') !== false) {
            redirect(admin_url('invoices/list_invoices'));
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->invoices_model->delete_attachment($id);
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /* Will send overdue notice to client */
    public function send_overdue_notice($id)
    {
        $canView = user_can_view_invoice($id);
        if (!$canView) {
            access_denied('Invoices');
        } else {
            if (!has_permission('invoices', '', 'view') && !has_permission('invoices', '', 'view_own') && $canView == false) {
                access_denied('Invoices');
            }
        }

        $send = $this->invoices_model->send_invoice_overdue_notice($id);
        if ($send) {
            set_alert('success', _l('invoice_overdue_reminder_sent'));
        } else {
            set_alert('warning', _l('invoice_reminder_send_problem'));
        }
        redirect(admin_url('invoices/list_invoices/' . $id));
    }

    /* Generates invoice PDF and senting to email of $send_to_email = true is passed */
    public function pdf($id)
    {
        if (!$id) {
            redirect(admin_url('invoices/list_invoices'));
        }

        $canView = user_can_view_invoice($id);
        if (!$canView) {
            access_denied('Invoices');
        } else {
            if (!has_permission('invoices', '', 'view') && !has_permission('invoices', '', 'view_own') && $canView == false) {
                access_denied('Invoices');
            }
        }

        $invoice        = $this->invoices_model->get($id);
        $invoice        = do_action('before_admin_view_invoice_pdf', $invoice);
        $invoice_number = format_invoice_number($invoice->id);

        try {
            $pdf = invoice_pdf($invoice);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(mb_strtoupper(slug_it($invoice_number)) . '.pdf', $type);
    }

    public function mark_as_sent($id)
    {
        if (!$id) {
            redirect(admin_url('invoices/list_invoices'));
        }
        if (!user_can_view_invoice($id)) {
            access_denied('Invoice Mark As Sent');
        }
        $success = $this->invoices_model->set_invoice_sent($id, true);
        if ($success) {
            set_alert('success', _l('invoice_marked_as_sent'));
        } else {
            set_alert('warning', _l('invoice_marked_as_sent_failed'));
        }
        redirect(admin_url('invoices/list_invoices/' . $id));
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('invoice_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }


/*************************************************************************************/


    public function save_qb_order_invoice($invoice_id)
    {

        $order_data = $this->db->select('tblinvoices.*')->where('id',$invoice_id)->get('tblinvoices')->row();
        if($order_data)
        {
            if($order_data->qb_customer_id)
            {
                $response['success'] = FALSE;
                $response['message'] = _l('Sorry Invoice Is Already Added To QuickBook Invoice !');
                set_alert('danger', _l('Sorry Invoice Is Already Added To QuickBook Invoice !'));
                echo json_encode($response);
                exit;
            }

            if($order_data->woo_commerce != 'WOO-COMMERCE')
            {
                $response['success'] = FALSE;
                $response['item_id'] = FALSE;
                $response['message'] = _l('Sorry you can only export invoices of woocommerce To QuickBook !');
                set_alert('danger', _l('Sorry you can only export invoices of woocommerce To QuickBook !'));
                // return $response;
                echo json_encode($response);
                exit;
            }
        }
        else
        {
            $response['success'] = FALSE;
            $response['message'] = _l('Sorry No Data Available For This Invoice ID !');
            set_alert('danger', _l('Sorry No Data Available For This Invoice ID !'));
            echo json_encode($response);
            // return $response;
            exit;
        }

        $accessToken = isset($_SESSION['sessionAccessToken']) ? $_SESSION['sessionAccessToken'] : false;

        if($accessToken)
        {
        
            $dataService = DataService::Configure(array(
            'auth_mode' => $this->config->item('auth_mode'),
            'ClientID' => $this->config->item('client_id'),
            'ClientSecret' => $this->config->item('client_secret'),
            'RedirectURI' => $this->config->item('oauth_redirect_uri'),
            'scope' => $this->config->item('oauth_scope'),
            'baseUrl' => $this->config->item('baseUrl'),
            'accessTokenKey'  =>  $accessToken->getAccessToken(),
            'refreshTokenKey' =>  $accessToken->getRefreshToken(),
            'QBORealmID'      =>  $accessToken->getRealmID(),
            ));


            $dataService->updateOAuth2Token($accessToken);
            $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
            $dataService->throwExceptionOnError(true);

            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $updated_refresh_token = $OAuth2LoginHelper->refreshToken();

            $dataService->updateOAuth2Token($updated_refresh_token);
            $_SESSION['sessionAccessToken'] = $updated_refresh_token;

            $error = $OAuth2LoginHelper->getLastError();

            if($error)
            {
                $response['success'] = FALSE;
                $response['message'] = _l('Refresh Token Has Been Expired Please connect to QuickBooks Again');
                $response['item_id'] = NULL;
                set_alert('danger', 'Refresh Token Has Been Expired Please connect to QuickBooks Again');
                echo json_encode($response);
                exit();
                
            }
        }
        else
        {

            $response['success'] = FALSE;
            $response['message'] = _l('Please connect to QuickBooks first');
            set_alert('danger', _l('Please connect to QuickBooks first'));
            echo json_encode($response);
            exit();
        }

        $customer_response = $this->save_qb_customers($invoice_id);
        
        if(empty($customer_response['customer_id']))
        {
            $response['success'] = FALSE;
            $response['message'] = $customer_response['message'];
            set_alert('danger', $customer_response['message']);
            echo json_encode($response);
            exit;
 
        }

        $item_response = $this->save_qb_items($invoice_id);
        
        if(empty($item_response['item_id']))
        {
            $response['success'] = FALSE;
            $response['message'] = $item_response['message'];
            set_alert('danger', $item_response['message']);
            echo json_encode($response);
            exit;
 
        }

        $customer_id = $customer_response['customer_id'];
        $item_id = $item_response['item_id'];
        $woocommerce_data = json_decode($order_data->clientnote);

        if(empty($woocommerce_data))
        {
            $response['success'] = FALSE;
            $response['customer_id'] = FALSE;
            $response['message'] = _l('Sorry Invalid woocommerce Invoice Record !');
            set_alert('danger', 'Sorry Invalid woocommerce Invoice Record ! ');
            echo json_encode($response);
            exit;
        }

        $billing_address = $woocommerce_data->billing_address;
        $product_desciption =  strip_tags($woocommerce_data->product_desciption);
        $product_desciption = strlen($product_desciption) > 900 ? substr($product_desciption,0,900)."..." : $product_desciption;

        
        //Add a new Invoice
        $theResourceObj = Invoice::create([
            "TxnDate" => "2020-08-06",
            "domain" => "QBO", 
            "PrintStatus" => "NeedToPrint", 
            "TotalAmt" => $woocommerce_data->get_price,
            "Line" => 
            [
                [
                "Amount" => $woocommerce_data->get_price,
                "Description"=> $product_desciption, 
                "DetailType" => "SalesItemLineDetail",
                "SalesItemLineDetail" => 
                    [
                        "TaxCodeRef" => ["value"=> "TAX",], 
                        "Qty"=> 1, 
                        "UnitPrice" => $woocommerce_data->get_price,
                        "ItemRef" => [ "value" => $item_id, "name" => $woocommerce_data->product_name,]
                    ],
                ],
            ],
            "CustomerRef"=> [
              "value"=> $customer_id,
              // "value"=> 1,
            ],
            "BillEmail" => [
                "Address" => $billing_address->email,
            ],
            "BillEmailCc" => [
                "Address" => $billing_address->email,
            ],
            "BillEmailBcc" => [
                "Address" => $billing_address->email,
            ],
            "DocNumber"=> $invoice_id, 
        ]);
        
            try
            {
                $resultingObj = $dataService->Add($theResourceObj);

                 if($resultingObj)
                   {
                        $response['success'] = true;
                        $response['message'] = _l('Invoice Created successfully in Quickbooks');
                        set_alert('success', _l(' Invoice Created successfully in Quickbooks'));

                        $qb_invoice_data = array();
                        $qb_invoice_data['qb_invoice_id'] = $resultingObj->Id;
                        $qb_invoice_data['qb_customer_id'] = $resultingObj->Id;
                        $this->db->where('id',$invoice_id); 
                        $this->db->update('tblinvoices',$qb_invoice_data);
                    }
                    else
                    {   
                        $response['success'] = FALSE;
                        $response['message'] = _l('Quick Book Invoice Insert Error Connect Quick Book Again');
                        set_alert('danger', _l('Quick Book Invoice Insert Error Connect Quick Book Again'));
                        $error = $dataService->getLastError();
                        if(($error->getHttpStatusCode() == 401))
                        {
                            $response['message'] = _l('Token Expired Please connect to QuickBooks first');
                            unset($_SESSION['sessionAccessToken']);
                        }                    }



            } 
            catch (Exception $e) 
            {
               
                $response['success'] = FALSE;
                $response['message'] = _l('Quick Book Invoice Insert Exception Connect Quick Book Again');
                set_alert('danger', _l('Quick Book Invoice Insert Exception Connect Quick Book Again'));
                $error = $dataService->getLastError();
                if(($error->getHttpStatusCode() == 401))
                {
                    $response['message'] = _l('Token Expired Please connect to QuickBooks first');
                    unset($_SESSION['sessionAccessToken']);
                }
               
            }
        
        
        echo json_encode($response); 
        exit;
    }



/******************************************************************************************/


    public function save_qb_items($invoice_id)
    {

        $response['success'] = false;
        $response['message'] = NULL;
        $response['item_id'] = NULL;

        $order_data = $this->db->where('id',$invoice_id)->get('tblinvoices')->row();
        if($order_data)
        {
            $last_qb_item_id_array = $order_data->qb_item_ids ? json_decode($order_data->qb_item_ids) : array();

            if($order_data->qb_customer_id)
            {
                $response['success'] = TRUE;
                $response['item_id'] = isset($last_qb_item_id_array[0]) ? $last_qb_item_id_array[0] : 1;
                $response['message'] = _l('Item Is Already Added To QuickBook Invoice !');
                return $response;
            }

            if($order_data->woo_commerce != 'WOO-COMMERCE')
            {
                $response['success'] = FALSE;
                $response['item_id'] = FALSE;
                $response['message'] = _l('Sorry you can only export invoices of woocommerce To QuickBook !');
                return $response;
            }
        }
        else
        {
            $response['item_id'] = FALSE;
            $response['success'] = FALSE;
            $response['message'] = _l('Sorry No Item Data Available For This Invoice ID !');
            return $response;
        }

        $woocommerce_data = json_decode($order_data->clientnote);

        if(empty($woocommerce_data))
        {
            $response['success'] = FALSE;
            $response['item_id'] = FALSE;
            $response['message'] = _l('Sorry Invalid woocommerce Item Record !');
            return $response;
        }

        $accessToken = isset($_SESSION['sessionAccessToken']) ? $_SESSION['sessionAccessToken'] : false;
        $item_id = NULL;

        if($accessToken)
        {

            $dataService = DataService::Configure(array(
                'auth_mode' => $this->config->item('auth_mode'),
                'ClientID' => $this->config->item('client_id'),
                'ClientSecret' => $this->config->item('client_secret'),
                'RedirectURI' => $this->config->item('oauth_redirect_uri'),
                'scope' => $this->config->item('oauth_scope'),
                'baseUrl' => $this->config->item('baseUrl'),
                'accessTokenKey'  =>  $accessToken->getAccessToken(),
                'refreshTokenKey' =>  $accessToken->getRefreshToken(),
                'QBORealmID'      =>  $accessToken->getRealmID(),
            ));   

            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $error = $OAuth2LoginHelper->getLastError();
            if($error)
            {
                $response['success'] = FALSE;
                $response['message'] = _l('Refresh Token Has Been Expired Please connect to QuickBooks Again');
                $response['item_id'] = NULL;
                return $response;
                exit;
            }

  
            $item_name = $woocommerce_data->product_name;
            $temp_item_id = NULL;
            
            $entities = $dataService->Query("select * from Item Where Name like '%".$item_name."%'"); 

            if($entities)
            {
                foreach ($entities as $key => $item_array) 
                {
                     if($item_array->UQCId == $invoice_id)
                     {
                        $item_id = $item_array->Id;
                        $response['item_id'] = $item_id;
                        $response['success'] = true;
                        $response['message'] = _l('Item Already Exist In Quick Book');
                        break;
                     }
                      $temp_item_id  = $item_array->Id;
                }
            }

            if(empty($item_id) && $temp_item_id)
            {
                $item_id = $temp_item_id;
                $response['item_id'] = $item_id;
                $response['success'] = true;
                $response['message'] = _l('Item Already Exist In Quick Book');
            }
            $product_desciption =  strip_tags($woocommerce_data->product_desciption);
            $product_desciption = strlen($product_desciption) > 900 ? substr($product_desciption,0,900)."..." : $product_desciption;
            
            if(empty($item_id))
            {
                $dataService->updateOAuth2Token($accessToken);
                $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
                $dataService->updateOAuth2Token($accessToken);
              
                // $allAccounts = $dataService->FindAll('Account');
                $IncomeAccountRef_id = NULL;
                $IncomeAccountRef_name = NULL;

                $ExpenseAccountRef_id = NULL;
                $ExpenseAccountRef_name = NULL;

                $AssetAccountRef_id = NULL;
                $AssetAccountRef_name = NULL;

                /****************************** IncomeAccountRef *******************************/

                $IncomeAccountRef = $dataService->Query("select * from Account Where AccountType='Income' AND AccountSubType='SalesOfProductIncome'");

                if($IncomeAccountRef)
                {
                    $IncomeAccountRef_id = $IncomeAccountRef[0]->Id;
                    $IncomeAccountRef_name = $IncomeAccountRef[0]->Name;
                }
                else
                {
                    $response['item_id'] = FALSE;
                    $response['success'] = FALSE;
                    $response['message'] = _l('Sorry Please Create Income Account First Note Account Type=`Income` And  Account Sub Type =`SalesOfProductIncome` ! ');
                    return $response;                
                }


                /****************************** IncomeAccountRef *******************************/

                $ExpenseAccountRef = $dataService->Query("select * from Account Where AccountType='Cost of Goods Sold' AND AccountSubType='SuppliesMaterialsCogs'");

                if($ExpenseAccountRef)
                {
                    $ExpenseAccountRef_id = $ExpenseAccountRef[0]->Id;
                    $ExpenseAccountRef_name = $ExpenseAccountRef[0]->Name;
                }
                else
                {
                    $response['item_id'] = FALSE;
                    $response['success'] = FALSE;
                    $response['message'] = _l('Sorry Please Create Expense Account First Note Account Type=`Cost of Goods Sold` And  Account Sub Type =`Supplies Materials Cogs` ! ');
                    return $response;                
                }


                /****************************** AssetAccountRef *******************************/

                $AssetAccountRef = $dataService->Query("select * from Account Where AccountType='Cost of Goods Sold' AND AccountSubType='SuppliesMaterialsCogs'");

                if($AssetAccountRef)
                {
                    $AssetAccountRef_id = $AssetAccountRef[0]->Id;
                    $AssetAccountRef_name = $AssetAccountRef[0]->Name;
                }
                else
                {
                    $response['item_id'] = FALSE;
                    $response['success'] = FALSE;
                    $response['message'] = _l('Sorry Please Create Asset Account First Note Account Type=`Cost of Goods Sold` And  Account Sub Type =`Supplies Materials Cogs` ! ');
                    return $response;                
                }

                 // $allAccounts_entities = $dataService->Query("select * from Account Where Id='127'"); 
                 // P($allAccounts_entities);


                $dateTime = new \DateTime('NOW');
                $Item = Item::create([
                      "Name" => $item_name,
                      "Description" => $product_desciption,
                      "Active" => true,
                      "FullyQualifiedName" => $item_name,
                      "Taxable" => false,
                      "UnitPrice" => $woocommerce_data->get_price,
                      "Type" => "Service",
                      // "Type" => "Inventory",
                      "IncomeAccountRef"=> [
                        "value"=> $IncomeAccountRef_id,
                        "name" => "$IncomeAccountRef_name"
                      ],
                      "PurchaseDesc"=> $product_desciption,
                      "PurchaseCost"=> $woocommerce_data->get_price, 
                        "sparse" => false, 
                        "Active" => true, 
                        "SyncToken" => "0", 
                        "InvStartDate" => date('Y-m-d'), 
                      "ExpenseAccountRef"=> [
                        "value"=> $ExpenseAccountRef_id,
                        "name"=> "$ExpenseAccountRef_name"
                      ],
                      "AssetAccountRef"=> [
                        "value"=> $AssetAccountRef_id ,
                        "name"=> "$AssetAccountRef_name"
                      ],
                      // "TrackQtyOnHand" => true,
                      "TrackQtyOnHand" => false,
                      "QtyOnHand"=> $woocommerce_data->quantity,
                      // "Qty" =>  $woocommerce_data->quantity,
                      "UQCId" => $invoice_id,
                      // "InvStartDate"=> $dateTime,
                        "MetaData" => [
                          "CreateTime"=> $dateTime, 
                          "LastUpdatedTime"=> $dateTime,
                        ],
                       
                ]);

                try
                {
                    $resultingObj = $dataService->Add($Item);
                   if($resultingObj)
                   {
                    $response['success'] = true;
                    $response['message'] = _l('New Item Inserted In Quick Book');
                    $response['item_id'] = $resultingObj->Id;
                    $item_id = $resultingObj->Id;

                    $last_qb_item_id_array = $order_data->qb_item_ids ? json_decode($order_data->qb_item_ids) : array();
                    $last_qb_item_id_array[] = $item_id;

                    $qb_invoice_data = array();
                    $qb_invoice_data['qb_item_ids'] = json_encode($last_qb_item_id_array);
                    $this->db->where('id',$invoice_id); 
                    $this->db->update('tblinvoices',$qb_invoice_data);

                    }
                    else
                    {   

                        $response['success'] = FALSE;
                        $response['message'] = _l('Item Insert Error Please connect to QuickBooks first');
                        $response['item_id'] = NULL;
                        $error = $dataService->getLastError();
                        if(($error->getHttpStatusCode() == 401))
                        {
                            $response['message'] = _l('Token Expired Please connect to QuickBooks first');
                            unset($_SESSION['sessionAccessToken']);
                        }                    }
                }
                catch(Exception $e)
                {

                    $response['success'] = FALSE;
                    $response['message'] = _l('Item Exception Please connect to QuickBooks first');
                    $response['item_id'] = NULL;
                    $error = $dataService->getLastError();
                    if(($error->getHttpStatusCode() == 401))
                    {
                        $response['message'] = _l('Token Expired Please connect to QuickBooks first');
                        unset($_SESSION['sessionAccessToken']);
                    }                    
                }                
            }
        }
        else
        {
            $response['success'] = FALSE;
            $response['message'] = _l('Please connect to QuickBooks first');
        }

        return $response;
    }




    /******************************************************************************************/




    public function save_qb_customers($invoice_id)
    {

        $customer_id = NULL;
        $response['success'] = false;
        $response['message'] = NULL;
        $response['customer_id'] = NULL;

        $order_data = $this->db->where('id',$invoice_id)->get('tblinvoices')->row();
        if($order_data)
        {
            if($order_data->qb_customer_id)
            {
                $response['success'] = TRUE;
                $response['customer_id'] = $order_data->qb_customer_id;
                $response['message'] = _l('Customer Is Already Added To QuickBook Invoice !');
                return $response;
            }

            if($order_data->woo_commerce != 'WOO-COMMERCE')
            {
                $response['success'] = FALSE;
                $response['customer_id'] = FALSE;
                $response['message'] = _l('Sorry you can only export invoices of woocommerce To QuickBook !');
                return $response;
            }

        }
        else
        {
            $response['customer_id'] = FALSE;
            $response['success'] = FALSE;
            $response['message'] = _l('Sorry No Customer Data Available For This Invoice ID !');
            return $response;
        }

        $accessToken = isset($_SESSION['sessionAccessToken']) ? $_SESSION['sessionAccessToken'] : false;
        $woocommerce_data = json_decode($order_data->clientnote);

        if(empty($woocommerce_data))
        {
            $response['success'] = FALSE;
            $response['customer_id'] = FALSE;
            $response['message'] = _l('Sorry Invalid woocommerce Customer Record !');
            return $response;
        }

        if($accessToken)
        {

        $dataService = DataService::Configure(array(
            'auth_mode' => $this->config->item('auth_mode'),
            'ClientID' => $this->config->item('client_id'),
            'ClientSecret' => $this->config->item('client_secret'),
            'RedirectURI' => $this->config->item('oauth_redirect_uri'),
            'scope' => $this->config->item('oauth_scope'),
            'baseUrl' => $this->config->item('baseUrl'),
            'accessTokenKey'  =>  $accessToken->getAccessToken(),
            'refreshTokenKey' =>  $accessToken->getRefreshToken(),
            'QBORealmID'      =>  $accessToken->getRealmID(),
        ));
        
            $dataService->updateOAuth2Token($accessToken);
            $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $OAuth2LoginHelper->refreshToken();
            $error = $OAuth2LoginHelper->getLastError();
            if($error)
            {
                $response['success'] = FALSE;
                $response['message'] = _l('Refresh Token Has Been Expired Please connect to QuickBooks Again');
                $response['customer_id'] = NULL;
                return $response;
                exit;
            }


            $temp_customer_id = NULL;
            $customer_name = $order_data->deleted_customer_name;

            $entities = $dataService->Query("select * from Customer Where DisplayName like '%".$customer_name."%'");  
            if($entities)
            {
                foreach ($entities as $key => $customer_array) 
                {
                     if($customer_array->DisplayName == $customer_name)
                     {
                        $customer_id = $customer_array->Id;
                        $response['customer_id'] = $customer_id;
                        $response['success'] = true;
                        $response['message'] = _l('Customer Already Exist In Quick Book');
                        break;
                     }
                     $temp_customer_id = $customer_array->Id;
                }
            }

            if(empty($customer_id) && $temp_customer_id)
            {
                $customer_id = $temp_customer_id;
                $response['customer_id'] = $customer_id;
                $response['success'] = TRUE;
                $response['message'] = _l('Customer Already Exist In Quick Book');
            }

            $billing_address = isset($woocommerce_data->billing_address) ? $woocommerce_data->billing_address : array();
            $shipping_address = isset($woocommerce_data->shipping_address) ? $woocommerce_data->shipping_address : array();


            if(empty($customer_id))
            {
                // Add a customer
                $customerObj = Customer::create([
                  "BillAddr" => [
                     "Line1"=>  $billing_address->address_1.' '.$billing_address->address_2,
                     "City"=>  $billing_address->city,
                     "Country"=>  $billing_address->country,
                     "CountrySubDivisionCode"=>  $billing_address->state,
                     "PostalCode"=>  $billing_address->postcode,
                 ],
                 "Notes" =>  "Here are other details.",
                 "Title"=>  "Mr",
                 "GivenName"=>  $woocommerce_data->name,
                 "MiddleName"=>  " ",
                 "FamilyName"=>  $woocommerce_data->surname,
                 "Suffix"=>  "M.",
                 "FullyQualifiedName"=>  $customer_name,
                 "CompanyName"=>  $customer_name,
                 "DisplayName"=>  $customer_name,
                 "PrimaryPhone"=>  [
                     "FreeFormNumber"=>  $billing_address->phone,
                 ],
                 "PrimaryEmailAddr"=>  [
                     "Address" => $billing_address->email,
                 ]
                ]);

                try
                {
                    $resultingCustomerObj = $dataService->Add($customerObj);
                    if(empty($resultingCustomerObj))
                    {   
                        $error = $dataService->getLastError();
                       
                        $response['success'] = FALSE;
                        $response['message'] = _l('Please connect to QuickBooks first');
                        $response['customer_id'] = NULL;
                        if(($error->getHttpStatusCode() == 401))
                        {
                            $response['message'] = _l('Token Expired Please connect to QuickBooks first');
                            unset($_SESSION['sessionAccessToken']);
                        }
                        
                    }
                    else
                    {
                        $response['success'] = true;
                        $response['message'] = _l('New Customer Inserted In Quick Book');
                        $response['customer_id'] = $resultingCustomerObj->Id;
                        $customer_id = $resultingCustomerObj->Id;
                    }
                } 
                catch (Exception $e) 
                {
                    $error = $dataService->getLastError();
                    $response['success'] = FALSE;
                    $response['message'] = _l('Quick Book Customere Insert Error Connect Quick Book Again');
                    if(($error->getHttpStatusCode() == 401))
                    {
                        $response['message'] = _l('Token Expired Please connect to QuickBooks first');
                        unset($_SESSION['sessionAccessToken']);
                    }
                }
            }

        }
        else
        {
            $response['success'] = FALSE;
            $response['message'] = _l('Please connect to QuickBooks first');
        }

        return $response;
    }


    public function remove_qb_order_invoice($qb_invoice_id = NULL, $invoice_id = NULL)
    {
        if($qb_invoice_id && $invoice_id)
        {
            $order_data = $this->db->select('tblinvoices.*')->where('id',$invoice_id)->get('tblinvoices')->row();
            
            if(empty($order_data))
            {
                $response['success'] = FALSE;
                $response['message'] = _l('Sorry No Data Available For This Invoice ID !');
                set_alert('danger', _l('Sorry No Data Available For This Invoice ID !'));
                echo json_encode($response);
                // return $response;
                exit;
            }

            $accessToken = isset($_SESSION['sessionAccessToken']) ? $_SESSION['sessionAccessToken'] : false;

            if($accessToken)
            {
            
                $dataService = DataService::Configure(array(
                'auth_mode' => $this->config->item('auth_mode'),
                'ClientID' => $this->config->item('client_id'),
                'ClientSecret' => $this->config->item('client_secret'),
                'RedirectURI' => $this->config->item('oauth_redirect_uri'),
                'scope' => $this->config->item('oauth_scope'),
                'baseUrl' => $this->config->item('baseUrl'),
                'accessTokenKey'  =>  $accessToken->getAccessToken(),
                'refreshTokenKey' =>  $accessToken->getRefreshToken(),
                'QBORealmID'      =>  $accessToken->getRealmID(),
                ));


                $dataService->updateOAuth2Token($accessToken);
                $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
                $dataService->throwExceptionOnError(true);


                $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
                $accessToken = $OAuth2LoginHelper->refreshToken();

                $dataService->updateOAuth2Token($accessToken);
                $_SESSION['sessionAccessToken'] = $accessToken;

                $error = $OAuth2LoginHelper->getLastError();
                if($error)
                {
                    $response['success'] = FALSE;
                    $response['message'] = _l('Refresh Token Has Been Expired Please connect to QuickBooks Again');
                    set_alert('danger', 'Refresh Token Has Been Expired Please connect to QuickBooks Again');
                    echo json_encode($response);
                    exit;
                
                }


            
                try
                {
                    // $entities = $dataService->Query("select * from Invoice"); 

                    $given_invoice = Invoice::create([
                        "Id" => $qb_invoice_id,
                        "SyncToken" => "0"
                    ]);

                    $currentResultObj = $dataService->Delete($given_invoice);


                    if($currentResultObj->Id)
                    {
                        $qb_invoice_data = array();
                        $qb_invoice_data['qb_invoice_id'] = NULL;
                        $qb_invoice_data['qb_customer_id'] = NULL;
                        $qb_invoice_data['qb_item_ids'] = NULL;
                        $this->db->where('id',$invoice_id); 
                        $this->db->update('tblinvoices',$qb_invoice_data);
                        $response['success'] = true;
                        $response['message'] = _l(' Invoice removed successfully ');
                        set_alert('success', _l('Invoice removed successfully '));


                    }
                    else
                    {   
                        $response['success'] = FALSE;
                        $response['message'] = _l('Quick Book Invoice Delete Error Connect Quick Book Again');
                        set_alert('danger', _l('Quick Book Invoice Delete Error Connect Quick Book Again'));
                        
                        $error = $dataService->getLastError();
                        if(($error->getHttpStatusCode() == 401))
                        {
                            $response['message'] = _l('Token Expired Please connect to QuickBooks first');
                            unset($_SESSION['sessionAccessToken']);
                        }                    }

                } 
                catch (Exception $e) 
                {
                    $response['success'] = FALSE;
                    $response['message'] = _l('Quick Book Invoice Delete Exception Connect Quick Book Again');
                    set_alert('danger', _l('Quick Book Invoice Delete Exception Connect Quick Book Again'));

                    $error = $dataService->getLastError();
                    if(($error->getHttpStatusCode() == 401))
                    {
                        $response['message'] = _l('Token Expired Please connect to QuickBooks first');
                        unset($_SESSION['sessionAccessToken']);
                    }
                   
                }
            }
            else
            {
                $response['success'] = FALSE;
                $response['message'] = _l('Please connect to QuickBooks first');
                set_alert('danger', _l('Please connect to QuickBooks first'));
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

}