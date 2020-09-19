<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Invoice extends Clients_controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index($id, $hash)
    {
        check_invoice_restrictions($id, $hash);
        $invoice = $this->invoices_model->get($id);

        $invoice = do_action('before_client_view_invoice', $invoice);

        if (!is_client_logged_in()) {
            load_client_language($invoice->clientid);
        }
        // Handle Invoice PDF generator
        if ($this->input->post('invoicepdf')) {
            try {
                $pdf = invoice_pdf($invoice);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            $invoice_number = format_invoice_number($invoice->id);
            $companyname    = get_option('invoice_company_name');
            if ($companyname != '') {
                $invoice_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }
            $pdf->Output(mb_strtoupper(slug_it($invoice_number), 'UTF-8') . '.pdf', 'D');
            die();
        }
        // Handle $_POST payment
        if ($this->input->post('make_payment')) {
            $this->load->model('payments_model');
            if (!$this->input->post('paymentmode')) {
                set_alert('warning', _l('invoice_html_payment_modes_not_selected'));
                redirect(site_url('invoice/' . $id . '/' . $hash));
            } elseif ((!$this->input->post('amount') || $this->input->post('amount') == 0) && get_option('allow_payment_amount_to_be_modified') == 1) {
                set_alert('warning', _l('invoice_html_amount_blank'));
                redirect(site_url('invoice/' . $id . '/' . $hash));
            }
            $this->payments_model->process_payment($this->input->post(), $id);
        }
        if ($this->input->post('paymentpdf')) {
            $id                    = $this->input->post('paymentpdf');
            $payment               = $this->payments_model->get($id);
            $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
            $paymentpdf            = payment_pdf($payment);
            $paymentpdf->Output(mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf', 'D');
            die;
        }
        $this->load->library('numberword', [
            'clientid' => $invoice->clientid,
        ]);
        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        $data['payments']      = $this->payments_model->get_invoice_payments($id);
        $data['payment_modes'] = $this->payment_modes_model->get();
        $data['title']         = format_invoice_number($invoice->id);
        $this->use_navigation  = false;
        $this->use_submenu     = false;
        $data['hash']          = $hash;
        $data['invoice']       = do_action('invoice_html_pdf_data', $invoice);
        $data['bodyclass']     = 'viewinvoice';
        $this->data            = $data;
        $this->view            = 'invoicehtml';
        add_views_tracking('invoice', $id);
        do_action('invoice_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
    }


    public function save_invoice_data()
    {
        $get_wp_data = @file_get_contents('php://input');

        $order_array = json_decode($get_wp_data);
        $new_order_array = $this->remove_utf8_bom($order_array);
        $body = json_encode($new_order_array);
        
        if($order_array)
        {
            $order_id = $order_array->order_id; 
            $product_id = $order_array->product_id;


            $is_item_exist = $this->db->where('woocommerce_order_id',$order_id)->where('woocommerce_product_id',$product_id)->get('tblitems')->row();

            if(empty($is_item_exist))
            {

                $is_invoice_exist = $this->db->where('woocommerce_order_id',$order_id)->get('tblinvoices')->row();
                $invoice_id = isset($is_invoice_exist->id) ? $is_invoice_exist->id : NULL;
                
                if(empty($is_invoice_exist))
                {
                    $invoice_data['sent']                   = 0;
                    $invoice_data['datesend']               = date('Y-m-d H:i:s');
                    $invoice_data['clientid']               = 0;
                    $invoice_data['deleted_customer_name']  = $order_array->name .' '.$order_array->surname;
                    $invoice_data['number']                 = 0;
                    $invoice_data['prefix']                 = 'INV-';
                    $invoice_data['woo_commerce']                 = 'WOO-COMMERCE';
                    $invoice_data['number_format']          = 5;
                    $invoice_data['datecreated']            = date('Y-m-d H:i:s');
                    $invoice_data['date']                   = date('Y-m-d');
                    $invoice_data['currency']               = 1;
                    $invoice_data['subtotal']               = $order_array->paid_amount;
                    $invoice_data['total_tax']              = $order_array->discount;
                    $invoice_data['total']                  = floor($order_array->paid_total*100)/100;
                    $invoice_data['adjustment']             = $order_array->discount;
                    $invoice_data['addedfrom']              = 0;
                    $invoice_data['hash']                   = 'c48826243034b76139044287d1bd4c1a';
                    $invoice_data['status']                 = 1;
                    $invoice_data['clientnote']             = $body;
                    $invoice_data['adminnote']              = $body;
                    $invoice_data['last_overdue_reminder']  = NULL;
                    $invoice_data['cancel_overdue_reminders'] = 0;
                    $invoice_data['allowed_payment_modes']  = NULL;
                    $invoice_data['token']                  = $order_array->payment_method;
                    $invoice_data['discount_percent']       = 0;
                    $invoice_data['discount_total']         = 0;
                    $invoice_data['discount_type']          = 'coupon';
                    $invoice_data['recurring']              = 0;
                    $invoice_data['recurring_type']         = 0;
                    $invoice_data['custom_recurring']       = 0;
                    $invoice_data['cycles']                 = 0;
                    $invoice_data['total_cycles']           = 0;
                    $invoice_data['is_recurring_from']      = NULL;
                    $invoice_data['last_recurring_date']    = NULL;
                    $invoice_data['terms']                  = 'wordpress-order';
                    $invoice_data['sale_agent']             = 0;
                    $invoice_data['billing_street']         = $order_array->billing_address->address_1;
                    $invoice_data['billing_city']           = $order_array->billing_address->city;
                    $invoice_data['billing_state']          = $order_array->billing_address->state;
                    $invoice_data['billing_zip']            = $order_array->billing_address->postcode;
                    $invoice_data['billing_country']        = $order_array->billing_address->country;
                    $invoice_data['shipping_street']        = $order_array->billing_address->address_1;
                    $invoice_data['shipping_city']          = $order_array->billing_address->city;
                    $invoice_data['shipping_state']         = $order_array->billing_address->state;
                    $invoice_data['shipping_zip']           = $order_array->billing_address->postcode;
                    $invoice_data['shipping_country']       = $order_array->billing_address->country;
                    $invoice_data['include_shipping']       = $order_array->shipping;
                    $invoice_data['show_shipping_on_invoice'] = 1;
                    $invoice_data['show_quantity_as']       = $order_array->quantity;
                    $invoice_data['project_id']             = 0;
                    $invoice_data['subscription_id']        = 0;
                    $invoice_data['woocommerce_order_id']   = $order_id;

                    $this->db->insert('tblinvoices',$invoice_data);
                    $invoice_id = $this->db->insert_id();



                }

                $item_data['description'] = $order_array->product_name;
                $item_data['long_description'] = $order_array->product_desciption;
                $item_data['rate'] = $order_array->get_price;
                $item_data['tax'] = $order_array->discount;
                $item_data['tax2'] = 0;
                $item_data['unit'] = $order_array->quantity;
                $item_data['group_id'] = 0;
                $item_data['woocommerce_order_id'] = $order_id;
                $item_data['woocommerce_product_id'] = $product_id;

                $this->db->insert('tblitems',$item_data);
                $new_item_id = $this->db->insert_id();

                $item_in_data = array(); 
                $item_in_data['rel_id'] = $invoice_id; 
                $item_in_data['rel_type'] = 'invoice'; 
                $item_in_data['description'] = $order_array->product_desciption;
                $item_in_data['long_description'] = $order_array->product_desciption;
                $item_in_data['qty'] = $order_array->quantity; 
                $item_in_data['rate'] = $order_array->get_price;
                $item_in_data['unit'] = $order_array->quantity; 
                $item_in_data['item_order'] = $order_array->quantity; 

                try
                {
                    $this->db->insert('tblitems_in',$item_in_data);
                    $new_item_in_id = $this->db->insert_id();
                }
                catch (Exception $e)
                {
                    p($e);
                }
                
            }
            else
            {
                set_alert('danger', 'Invoice Already Added');
                echo "insert errror";
            } 
        }

        return redirect(base_url());
    }


    public function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;


        // switch (json_last_error()) 
        // {
        //     case JSON_ERROR_NONE:
        //         echo ' - No errors';
        //     break;
        //     case JSON_ERROR_DEPTH:
        //         echo ' - Maximum stack depth exceeded';
        //     break;
        //     case JSON_ERROR_STATE_MISMATCH:
        //         echo ' - Underflow or the modes mismatch';
        //     break;
        //     case JSON_ERROR_CTRL_CHAR:
        //         echo ' - Unexpected control character found';
        //     break;
        //     case JSON_ERROR_SYNTAX:
        //         echo ' - Syntax error, malformed JSON';
        //     break;
        //     case JSON_ERROR_UTF8:
        //         echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        //     break;
        //     default:
        //         echo ' - Unknown error';
        //     break;
        // }

    }

}
