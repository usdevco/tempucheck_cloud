 <?php

   $table_data = array();
   $_table_data = array(
     _l('No'),
    _l('Company Name'),
    _l('Instance Name'),
    _l('Phone'),
    _l('Name'),
    _l('Action'),

    );

   foreach($_table_data as $_t){
    array_push($table_data,$_t);
}

$custom_fields = get_custom_fields('tblinstance',array('show_on_table'=>1));
foreach($custom_fields as $field){
    array_push($table_data,$field['name']);
}

$table_data = do_action('tblinstance',$table_data);

render_datatable($table_data,'tblinstance',[],[
         'data-last-order-identifier' => 'tblinstance',
         'data-default-order'         => get_table_last_order('tblinstance'),
]);

?>
