<?php

defined('BASEPATH') or exit('No direct script access allowed');

$total_applicants = total_rows('tblapplicants', ['addedfrom' => get_staff_user_id()]);


$aColumns = array(
	'id',
    'first_name',
    // 'last_name',
    'email',
	'phone',
	'resume',
	'is_staff',
);

$sIndexColumn = 'id';
$sTable       = 'tblapplicants';
$join         = [];

$where = ['AND addedfrom=' . get_staff_user_id()];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['tblapplicants.id as id, last_name']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $rowName = '<a href="' . admin_url('applicants/applicant/' . $aRow['id']) . '">' . $aRow['first_name'] .' '.$aRow['last_name']. '</a>';

    $rowName .= '<div class="row-options">';

    $rowName .= '<a href="' . admin_url('applicants/applicant/' . $aRow['id']) . '" >' . _l('edit') . '</a>';
	$rowName .= ' | <a href="' . admin_url('applicants/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';

    $rowName .= '</div>';

	$row[] = '<div class="checkbox"><input type="checkbox" value="'.$aRow['id'].'"><label></label></div>';
    $row[] = $rowName;

    $row[] = '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>';
	$row[] = $aRow['phone'];
	
	$row[] = '<a href="'.$aRow['resume'].'" class="btn btn-sm btn-success" download><i class="fa fa-download"></i></a>';

	if($aRow['is_staff'])
	{
		$row[] = '<a href="javascript:void(0)" disabled data-id="'.$aRow['id'].'" class="btn btn-sm btn-primary user_added"><i class="fa fa-user-plus"></i></a>';
	}
	else
	{
		$row[] = '<a href="javascript:void(0)" data-id="'.$aRow['id'].'" class="btn btn-sm btn-warning add_users"><i class="fa fa-user-plus"></i></a>';
	}


    $row[] = (!empty($aRow['created_at']) ? '<span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($aRow['created_at']) . '">' . time_ago($aRow['created_at']) . '</span>' : '');

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
