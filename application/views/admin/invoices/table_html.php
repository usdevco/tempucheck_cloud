<?php

    if (get_staff_user_id() || is_admin()) { 
     $table_data = array(
      _l('invoice_dt_table_heading_number'),
      _l('invoice_dt_table_heading_amount'),
      _l('invoice_total_tax'),
      array(
        'name'=>_l('invoice_estimate_year'),
        'th_attrs'=>array('class'=>'not_visible')
      ),
      _l('invoice_dt_table_heading_date'),
     array(
       'name'=>_l('invoice_dt_table_heading_client'),
       'th_attrs'=>array('class'=>(isset($client) ? 'not_visible' : ''))
       ),
      _l('project'),
      _l('tags'),
      _l('invoice_dt_table_heading_duedate'),
      _l('invoice_dt_table_heading_status'),
      _l('Save In QB'));

   }
   else
   {
      $table_data = array(
      _l('invoice_dt_table_heading_number'),
      _l('invoice_dt_table_heading_amount'),
      _l('invoice_total_tax'),
      array(
        'name'=>_l('invoice_estimate_year'),
        'th_attrs'=>array('class'=>'not_visible')
      ),
      _l('invoice_dt_table_heading_date'),
     array(
       'name'=>_l('invoice_dt_table_heading_client'),
       'th_attrs'=>array('class'=>(isset($client) ? 'not_visible' : ''))
       ),
      _l('project'),
      _l('tags'),
      _l('invoice_dt_table_heading_duedate'),
      _l('invoice_dt_table_heading_status'));
   }

     array_unshift($table_data, [
      'name'     => '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="invoices"><label></label></div>',
      'th_attrs' => ['class' => (isset($bulk_actions) ? '' : 'not_visiblee')],
  ]);

     $custom_fields = get_custom_fields('invoice',array('show_on_table'=>1));

     foreach($custom_fields as $field){
      array_push($table_data,$field['name']);
    }

    $table_data = do_action('invoices_table_columns',$table_data);

    render_datatable($table_data, (isset($class) ? $class : 'invoices'));
?>
