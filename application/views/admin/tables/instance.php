<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionEdit   = has_permission('tasks', '', 'edit');
$hasPermissionDelete = has_permission('tasks', '', 'delete');

$aColumns = [
    '1', // bulk actions
    'company_name',
    'instance_name',
    'phone',
    'first_name',
    'id', 
];

$sIndexColumn = 'id';
$sTable       = 'tblinstance';

$where = [];
$join  = [];

$custom_fields = get_table_custom_fields('tblinstance');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, '(SELECT value FROM tblcustomfieldsvalues WHERE tblcustomfieldsvalues.relid=tblstafftasks.id AND tblcustomfieldsvalues.fieldid=' . $field['id'] . ' AND tblcustomfieldsvalues.fieldto="' . $field['fieldto'] . '" LIMIT 1) as ' . $selectAs);
}

$aColumns = do_action('tblinstance', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    ['id','last_name',]
);



$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $key => $aRow) 
{
    $i = $key + 1;
    $row = [];

    $row[] = $i;

    $row[] = $aRow['company_name'];
    $row[] = $aRow['instance_name'];
    $row[] = $aRow['phone'];
    $row[] = $aRow['first_name'].' '.$aRow['last_name'];

    $row[] = '  <a href="'.base_url("admin/instance/update_instance/").$aRow["id"].'" class="btn btn-warning btn-sm" title="Edit"><i class="fa fa-edit"></i> </a>
                <a href="http://'.$aRow["instance_name"].'.mytempucheck.com" target="_blank" class="btn btn-info btn-sm" title="Edit">Visit Subdomain </a>';

    $output['aaData'][] = $row;
}
