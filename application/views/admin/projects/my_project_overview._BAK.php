<div class="row">
   <div class="col-md-6 border-right project-overview-left">
      <div class="row">
       <div class="col-md-12">
            <p class="project-info bold font-size-14">
            <?php echo _l('overview'); ?>
         </p>
       </div>
         <?php if(count($project->shared_vault_entries) > 0){ ?>
         <?php $this->load->view('admin/clients/vault_confirm_password'); ?>
         <div class="col-md-12">
            <p class="bold mtop10">
              <a href="#" onclick="slideToggle('#project_vault_entries'); return false;">
                <i class="fa fa-cloud"></i> <?php echo _l('project_shared_vault_entry_login_details'); ?>
              </a>
            </p>
            <div id="project_vault_entries" class="hide">
               <?php foreach($project->shared_vault_entries as $vault_entry){ ?>
               <div class="row" id="<?php echo 'vaultEntry-'.$vault_entry['id']; ?>">
                  <div class="col-md-6">
                     <p class="mtop5">
                        <b><?php echo _l('server_address'); ?>: </b><?php echo $vault_entry['server_address']; ?>
                     </p>
                     <p>
                        <b><?php echo _l('port'); ?>: </b><?php echo !empty($vault_entry['port']) ? $vault_entry['port'] : _l('no_port_provided'); ?>
                     </p>
                     <p>
                        <b><?php echo _l('vault_username'); ?>: </b><?php echo $vault_entry['username']; ?>
                     </p>
                     <p class="no-margin">
                        <b><?php echo _l('vault_password'); ?>: </b><span class="vault-password-fake">
                        <?php echo str_repeat('&bull;',10);?>  </span><span class="vault-password-encrypted"></span> <a href="#" class="vault-view-password mleft10" data-toggle="tooltip" data-title="<?php echo _l('view_password'); ?>" onclick="vault_re_enter_password(<?php echo $vault_entry['id']; ?>,this); return false;"><i class="fa fa-lock" aria-hidden="true"></i></a>
                     </p>
                  </div>
                  <div class="col-md-6">
                     <?php if(!empty($vault_entry['description'])){ ?>
                     <p>
                        <b><?php echo _l('vault_description'); ?>: </b><br /><?php echo $vault_entry['description']; ?>
                     </p>
                     <?php } ?>
                  </div>
               </div>
               <hr class="hr-10" />
               <?php } ?>
            </div>
            <hr class="hr-panel-heading project-area-separation" />
         </div>
         <?php } ?>
         <div class="col-md-7">
            <table class="table no-margin project-overview-table">
               <tbody>
                  <?php if(has_permission('customers','','view') || is_customer_admin($project->clientid)){ ?>
                  <tr>
                     <td class="bold"><?php echo _l('project_customer'); ?></td>
                     <td><a href="<?php echo admin_url(); ?>clients/client/<?php echo $project->clientid; ?>"><?php echo $project->client_data->company; ?></a>
                     </td>
                  </tr>
                  <?php } ?>
                  <?php if(has_permission('projects','','create') || has_permission('projects','','edit')){ ?>
                  <tr>
                     <td class="bold"><?php echo _l('project_billing_type'); ?></td>
                     <td>
                        <?php
                           if($project->billing_type == 1){
                             $type_name = 'project_billing_type_fixed_cost';
                           } else if($project->billing_type == 2){
                             $type_name = 'project_billing_type_project_hours';
                           } else {
                             $type_name = 'project_billing_type_project_task_hours';
                           }
                           echo _l($type_name);
                           ?>
                     </td>
                     <?php if($project->billing_type == 1 || $project->billing_type == 2){
                        echo '<tr>';
                        if($project->billing_type == 1){
                          echo '<td class="bold">'._l('project_total_cost').'</td>';
                          echo '<td>'.format_money($project->project_cost,$currency->symbol).'</td>';
                        } else {
                          echo '<td class="bold">'._l('project_rate_per_hour').'</td>';
                          echo '<td>'.format_money($project->project_rate_per_hour,$currency->symbol).'</td>';
                        }
                        echo '<tr>';
                        }
                        }
                        ?>
                  <tr>
                     <td class="bold"><?php echo _l('project_status'); ?></td>
                     <td><?php echo $project_status['name']; ?></td>
                  </tr>
                  <tr>
                     <td class="bold"><?php echo _l('project_datecreated'); ?></td>
                     <td><?php echo _d($project->project_created); ?></td>
                  </tr>
                  <tr>
                     <td class="bold"><?php echo _l('project_start_date'); ?></td>
                     <td><?php echo _d($project->start_date); ?></td>
                  </tr>
                  <?php if($project->deadline){ ?>
                  <tr>
                     <td class="bold"><?php echo _l('project_deadline'); ?></td>
                     <td><?php echo _d($project->deadline); ?></td>
                  </tr>
                  <?php } ?>
                  <?php if($project->date_finished){ ?>
                  <tr>
                     <td class="bold"><?php echo _l('project_completed_date'); ?></td>
                     <td class="text-success"><?php echo _dt($project->date_finished); ?></td>
                  </tr>
                  <?php } ?>
                  <?php if($project->estimated_hours && $project->estimated_hours != '0'){ ?>
                  <tr>
                     <td class="bold<?php if(hours_to_seconds_format($project->estimated_hours) < (int)$project_total_logged_time){echo ' text-warning';} ?>"><?php echo _l('estimated_hours'); ?></td>
                     <td><?php echo str_replace('.', ':', $project->estimated_hours); ?></td>
                  </tr>
                  <?php } ?>
                  <?php if(has_permission('projects','','create')){ ?>
                  <tr>
                     <td class="bold"><?php echo _l('project_overview_total_logged_hours'); ?></td>
                     <td><?php echo seconds_to_time_format($project_total_logged_time); ?></td>
                  </tr>
                  <?php } ?>
                  <?php $custom_fields = get_custom_fields('projects');
                     if(count($custom_fields) > 0){ ?>
                  <?php foreach($custom_fields as $field){ ?>
                  <?php $value = get_custom_field_value($project->id,$field['id'],'projects');
                     if($value == ''){continue;} ?>
                  <tr>
                     <td class="bold"><?php echo ucfirst($field['name']); ?></td>
                     <td><?php echo $value; ?></td>
                  </tr>
                  <?php } ?>
                  <?php } ?>
               </tbody>
            </table>
         </div>
         <div class="col-md-5 text-center project-percent-col mtop10">
            <p class="bold"><?php echo _l('project'). ' ' . _l('project_progress'); ?></p>
            <div class="project-progress relative mtop15" data-value="<?php echo $percent_circle; ?>" data-size="150" data-thickness="22" data-reverse="true">
               <strong class="project-percent"></strong>
            </div>
         </div>
      </div>
      <?php $tags = get_tags_in($project->id,'project'); ?>
      <?php if(count($tags) > 0){ ?>
      <div class="clearfix"></div>
      <div class="tags-read-only-custom">
         <hr class="hr-panel-heading project-area-separation" />
         <?php echo '<p class="font-size-14"><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
         <input type="text" class="tagsinput read-only" id="tags" name="tags" value="<?php echo prep_tags_input($tags); ?>" data-role="tagsinput">
      </div>
      <div class="clearfix"></div>
      <?php } ?>
      <div class="tc-content">
         <hr class="hr-panel-heading project-area-separation" />
         <p class="bold font-size-14 project-info"><?php echo _l('project_description'); ?></p>
         <?php if(empty($project->description)){
            echo '<p class="text-muted no-mbot mtop15">' . _l('no_description_project') . '</p>';
            }
            echo check_for_links($project->description); ?>
      </div>
      <div class="team-members">
         <hr class="hr-panel-heading project-area-separation" />
         <?php if(has_permission('projects','','edit') || has_permission('projects','','create')){ ?>
         <div class="inline-block pull-right mright10 project-member-settings" data-toggle="tooltip" data-title="<?php echo _l('add_edit_members'); ?>">
            <a href="#" data-toggle="modal" class="pull-right" data-target="#add-edit-members"><i class="fa fa-cog"></i></a>
         </div>
         <?php } ?>
         <p class="bold font-size-14 project-info">
            <?php echo _l('project_members'); ?>
         </p>
         <div class="clearfix"></div>
         <?php
            if(count($members) == 0){
               echo '<p class="text-muted mtop10 no-mbot">'._l('no_project_members').'</p>';
            }
            foreach($members as $member){ ?>
         <div class="media">
            <div class="media-left">
               <a href="<?php echo admin_url('profile/'.$member["staff_id"]); ?>">
               <?php echo staff_profile_image($member['staff_id'],array('staff-profile-image-small','media-object')); ?>
               </a>
            </div>
            <div class="media-body">
               <?php if(has_permission('projects','','edit') || has_permission('projects','','create')){ ?>
               <a href="<?php echo admin_url('projects/remove_team_member/'.$project->id.'/'.$member['staff_id']); ?>" class="pull-right text-danger _delete"><i class="fa fa fa-times"></i></a>
               <?php } ?>
               <h5 class="media-heading mtop5"><a href="<?php echo admin_url('profile/'.$member["staff_id"]); ?>"><?php echo get_staff_full_name($member['staff_id']); ?></a>
                  <?php if(has_permission('projects','','create') || $member['staff_id'] == get_staff_user_id()){ ?>
                  <br /><small class="text-muted"><?php echo _l('total_logged_hours_by_staff') .': '.seconds_to_time_format($member['total_logged_time']); ?></small>
                  <?php } ?>
               </h5>
            </div>
         </div>
         <?php } ?>
      </div>
   </div>
   <div class="col-md-6 project-overview-right">
      <div class="row">
         <div class="col-md-<?php echo ($project->deadline ? 6 : 12); ?> project-progress-bars">
            <?php $tasks_not_completed_progress = round($tasks_not_completed_progress,2); ?>
            <?php $project_time_left_percent = round($project_time_left_percent,2); ?>
            <div class="row">
               <div class="col-md-9">
                  <p class="text-uppercase bold text-dark font-medium">
                     <?php echo $tasks_not_completed; ?> / <?php echo $total_tasks; ?> <?php echo _l('project_open_tasks'); ?>
                  </p>
                  <p class="text-muted bold"><?php echo $tasks_not_completed_progress; ?>%</p>
               </div>
               <div class="col-md-3 text-right">
                  <i class="fa fa-check-circle<?php if($tasks_not_completed_progress >= 100){echo ' text-success';} ?>" aria-hidden="true"></i>
               </div>
               <div class="col-md-12 mtop5">
                  <div class="progress no-margin progress-bar-mini">
                     <div class="progress-bar light-green-bg no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $tasks_not_completed_progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $tasks_not_completed_progress; ?>">
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <?php if($project->deadline){ ?>
         <div class="col-md-6 project-progress-bars">
            <div class="row">
               <div class="col-md-9">
                  <p class="text-uppercase bold text-dark font-medium">
                     <?php echo $project_days_left; ?> / <?php echo $project_total_days; ?> <?php echo _l('project_days_left'); ?>
                  </p>
                  <p class="text-muted bold"><?php echo $project_time_left_percent; ?>%</p>
               </div>
               <div class="col-md-3 text-right">
                  <i class="fa fa-calendar-check-o<?php if($project_time_left_percent >= 100){echo ' text-success';} ?>" aria-hidden="true"></i>
               </div>
               <div class="col-md-12 mtop5">
                  <div class="progress no-margin progress-bar-mini">
                     <div class="progress-bar<?php if($project_time_left_percent == 0){echo ' progress-bar-warning ';} else { echo ' progress-bar-success ';} ?>no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $project_time_left_percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $project_time_left_percent; ?>">
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <?php } ?>
      </div>
      <hr class="hr-panel-heading" />

      <?php if(has_permission('projects','','create')) { ?>
      <div class="row">
         <?php if($project->billing_type == 3 || $project->billing_type == 2){ ?>
         <div class="col-md-12">
            <div class="col-md-3">
               <?php
                  $data = $this->projects_model->total_logged_time_by_billing_type($project->id);
                  ?>
               <p class="text-uppercase text-muted"><?php echo _l('project_overview_logged_hours'); ?> <span class="bold"><?php echo $data['logged_time']; ?></span></p>
               <p class="bold font-medium"><?php echo format_money($data['total_money'],$currency->symbol); ?></p>
            </div>
            <div class="col-md-3">
               <?php
                  $data = $this->projects_model->data_billable_time($project->id);
                  ?>
               <p class="text-uppercase text-info"><?php echo _l('project_overview_billable_hours'); ?> <span class="bold"><?php echo $data['logged_time'] ?></span></p>
               <p class="bold font-medium"><?php echo format_money($data['total_money'],$currency->symbol); ?></p>
            </div>
            <div class="col-md-3">
               <?php
                  $data = $this->projects_model->data_billed_time($project->id);
                  ?>
               <p class="text-uppercase text-success"><?php echo _l('project_overview_billed_hours'); ?> <span class="bold"><?php echo $data['logged_time']; ?></span></p>
               <p class="bold font-medium"><?php echo format_money($data['total_money'],$currency->symbol); ?></p>
            </div>
            <div class="col-md-3">
               <?php
                  $data = $this->projects_model->data_unbilled_time($project->id);
                  ?>
               <p class="text-uppercase text-danger"><?php echo _l('project_overview_unbilled_hours'); ?> <span class="bold"><?php echo $data['logged_time']; ?></span></p>
               <p class="bold font-medium"><?php echo format_money($data['total_money'],$currency->symbol); ?></p>
            </div>
            <div class="clearfix"></div>
               <hr class="hr-panel-heading" />
         </div>
         <?php } ?>
      </div>
      <div class="row">
         <div class="col-md-12">
            <div class="col-md-3">
               <p class="text-uppercase text-muted"><?php echo _l('project_overview_expenses'); ?></p>
               <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses',array('where'=>array('project_id'=>$project->id),'field'=>'amount')),$currency->symbol); ?></p>
            </div>
            <div class="col-md-3">
               <p class="text-uppercase text-info"><?php echo _l('project_overview_expenses_billable'); ?></p>
               <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses',array('where'=>array('project_id'=>$project->id,'billable'=>1),'field'=>'amount')),$currency->symbol); ?></p>
            </div>
            <div class="col-md-3">
               <p class="text-uppercase text-success"><?php echo _l('project_overview_expenses_billed'); ?></p>
               <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses',array('where'=>array('project_id'=>$project->id,'invoiceid !='=>'NULL','billable'=>1),'field'=>'amount')),$currency->symbol); ?></p>
            </div>
            <div class="col-md-3">
               <p class="text-uppercase text-danger"><?php echo _l('project_overview_expenses_unbilled'); ?></p>
               <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses',array('where'=>array('project_id'=>$project->id,'invoiceid IS NULL','billable'=>1),'field'=>'amount')),$currency->symbol); ?></p>
            </div>
         </div>
      </div>
      <?php } ?>
        <hr class="hr-panel-heading" />
       <div class="dropdown pull-right">
         <a href="#" class="dropdown-toggle" type="button" id="dropdownMenuProjectLoggedTime" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
         <?php if(!$this->input->get('overview_chart')){
            echo _l('this_week');
            } else {
            echo _l($this->input->get('overview_chart'));
            }
            ?>
         <span class="caret"></span>
         </a>
         <ul class="dropdown-menu" aria-labelledby="dropdownMenuProjectLoggedTime">
            <li><a href="<?php echo admin_url('projects/view/'.$project->id.'?group=project_overview&overview_chart=this_week'); ?>"><?php echo _l('this_week'); ?></a></li>
            <li><a href="<?php echo admin_url('projects/view/'.$project->id.'?group=project_overview&overview_chart=last_week'); ?>"><?php echo _l('last_week'); ?></a></li>
            <li><a href="<?php echo admin_url('projects/view/'.$project->id.'?group=project_overview&overview_chart=this_month'); ?>"><?php echo _l('this_month'); ?></a></li>
            <li><a href="<?php echo admin_url('projects/view/'.$project->id.'?group=project_overview&overview_chart=last_month'); ?>"><?php echo _l('last_month'); ?></a></li>
         </ul>
      </div>
      <div class="clearfix"></div>
      <canvas id="timesheetsChart" style="max-height:300px;" width="300" height="300"></canvas>

</div>
   </div>
</div>
   
		
        
            <div class="row bs-wizard" style="border-bottom:0;">
            <input type="hidden" id="project_id_num" data-value="<?php echo $project->id;?>"/>


           <?php $data_get=$this->projects_model->email_track_number($project->id);
?>
<input type="hidden" id="email_num" data-value="<?php echo $data_get?>"/>
<?php
           $f=$s=$t=$fourth=$fif=$six="disabled";

       //    if($data_get==1){
           	echo '<div class="img1" style="display:none;"><center>';

         echo '<img style="width:100%" class="img-process" src="http://www.signatureimpactwindows.net/emails/welcome/images/siwd-welcome_02.png">';
         echo '</center></div>';

        //   }
         //   if($data_get==1){
           	echo '<div class="img2" style="display:none;"><center>';

         echo '<img style="width:100%" class="img-process" src="http://www.signatureimpactwindows.net/emails/permit/images/permit_02.jpg">';
         echo '</center></div>';

          // }
          //   if($data_get==3){
           		echo '<div class="img3" style="display:none;"><center>';

         echo '<img style="width:100%" class="img-process" src="http://www.signatureimpactwindows.net/newcrm/order-process.png">';
         echo '</center></div>';


         //  }
         //   if($data_get==4){
          	echo '<div class="img4" style="display:none;"><center>';

         echo '<img style="width:100%" class="img-process" src="http://www.signatureimpactwindows.net/emails/cp/images/City-Approved_02.jpg">';
         echo '</center></div>';


         //  }
       //    if($data_get==5){
         	echo '<div class="img5" style="display:none;"><center>';

         echo '<img style="width:100%" class="img-process" src="http://www.signatureimpactwindows.net/emails/oa/images/Order-Arrived_02.jpg">';
         echo '</center></div>';


       //    }
          
        //   if($data_get==6){
           		echo '<div class="img6" style="display:none;"><center>';

         echo '<img style="width:100%" class="img-process" src="http://www.signatureimpactwindows.net/emails/oc/images/Order-Completed_02.jpg">';
         echo '</center></div>';

         //  }


            ?>
                
               <!-- <div class="<?php echo $f.' col-xs-2 bs-wizard-step'; ?>">
                 <div class="text-center bs-wizard-stepnum">Step 1 </div>
                  <div class="progress www"><div class="progress-bar"></div></div>
                  <a  class="bs-wizard-dot" data-value="1"></a>
                
                </div>
                <div class="<?php echo $s.' col-xs-2 bs-wizard-step'; ?>">
                  <div class="text-center bs-wizard-stepnum">Step 2</div>
                  <div class="progress"><div class="progress-bar"></div></div>
                  <a class="bs-wizard-dot" data-value="2"></a>
                  <div class="bs-wizard-info text-center">Permit</div>
                </div>
                <div class="<?php echo $t.' col-xs-2 bs-wizard-step'; ?>">
                  <div class="text-center bs-wizard-stepnum">Step 3</div>
                  <div class="progress"><div class="progress-bar"></div></div>
                  <a  class="bs-wizard-dot" data-value="3"></a>
                  <div class="bs-wizard-info text-center">Order Processing</div>
                </div>
                <div class="<?php echo $fourth.' col-xs-2 bs-wizard-step'; ?>">
                  <div class="text-center bs-wizard-stepnum">Step 4</div>
                  <div class="progress"><div class="progress-bar"></div></div>
                  <a class="bs-wizard-dot" data-value="4"></a>
                  <div class="bs-wizard-info text-center">City Approved</div>
                </div>
                  <div class="<?php echo $fif.' col-xs-2 bs-wizard-step'; ?>">
                  <div class="text-center bs-wizard-stepnum">Step 5</div>
                  <div class="progress"><div class="progress-bar"></div></div>
                  <a  class="bs-wizard-dot" data-value="5"></a>
                  <div class="bs-wizard-info text-center">Order Arrived</div>
                </div>
                  <div class="<?php echo $six.' col-xs-2 bs-wizard-step'; ?>">
                  <div class="text-center bs-wizard-stepnum">Step 6</div>
                  <div class="progress"><div class="progress-bar"></div></div>
                  <a  class="bs-wizard-dot" data-value="6"></a>
                  <div class="bs-wizard-info text-center">Order Completed</div>
                </div>

            </div>
            -->
            <button class="btn-primary bs-wizard-dot" id="email_step" style="display:none;"  >Send Email </button>
	
<div class="modal fade" id="add-edit-members" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <?php echo form_open(admin_url('projects/add_edit_members/'.$project->id)); ?>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('project_members'); ?></h4>
         </div>
         <div class="modal-body">
            <?php
               $selected = array();
               foreach($members as $member){
                 array_push($selected,$member['staff_id']);
               }
               echo render_select('project_members[]',$staff,array('staffid',array('firstname','lastname')),'project_members',$selected,array('multiple'=>true));
               ?>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('submit'); ?></button>
         </div>
      </div>
      <!-- /.modal-content -->
      <?php echo form_close(); ?>
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php if(isset($project_overview_chart)){ ?>
<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>
<script>
   var project_overview_chart = <?php echo json_encode($project_overview_chart); ?>;

   $(document).ready(function(){
  /*
   		var project_id=$("#project_id_num").attr('data-value');
	var  email_number=$("#email_num").attr('data-value');
	//alert(project_id+'ddd'+email_number);
	if(email_number==1){
		 $.ajax({
        
        url: "<?php echo site_url('admin/Projects/email_send'); ?>",
        
         type: 'POST',
        data:{'email_number':email_number,'project_id':project_id},
     
         
        success: function(response) {
           // alert('welcome Email checking ');
        },
        error: function(data) {
            alert('something went wrong');
        }
    });


	}*/
		var email_number=$("#email_num").attr('data-value');
		if (parseInt(email_number)<=6) {
		$('.bs-wizard-dot').css('display','block');
		var classs='.img'+email_number;
		//alert(classs);
		$(classs).css('display','block');


}
$('.bs-wizard-dot').click(function(e) {
	if (parseInt(email_number)>6) {
		$(this).css('display','none');

}
	else{
	
	e.preventDefault();
	 if (confirm('Are you sure You want to send email?')) {


        
	//project_id_num
	var project_id=$("#project_id_num").attr('data-value');
	//var email_number=$("#email_num").attr('data-value');
//	alert(email_number);
	var next_c=parseInt(email_number);
	++next_c;
++email_number;
    $.ajax({
        
        url: "<?php echo site_url('admin/Projects/email_send'); ?>",
        
         type: 'POST',
        data:{'email_number':next_c,'project_id':project_id},
     
         
        success: function(response) {
          
            var number=parseInt(next_c);
            --number;
            var classs_show='.img'+email_number;
            var classs_hide='.img'+number;
		//alert(classs);
		if (parseInt(email_number)>=6) {
		$('.bs-wizard-dot').css('display','none');
	}
		$(classs_hide).css('display','none');
		$(classs_show).css('display','block');
		//alert(classs);
		  alert('Email sent successfully!!');

        },
        error: function(data) {
            alert('something went wrong');
        }
    });

//	$(this).parent().removeClass('disabled');/
//	$(this).parent().addClass('complete');
//	$(this).parent().removeClass('next_class');
//	$('.disabled').first().addClass('next_class');
	

}
}
});
});
</script>
<?php } ?>
<style>
.progress.www {
    width: 50%;
    margin-left: 50% !important;
}
.bs-wizard {margin-top: 40px;}

/*Form Wizard*/
.bs-wizard {border-bottom: solid 1px #e0e0e0; padding: 0 0 10px 0;}
.bs-wizard > .bs-wizard-step {padding: 0; position: relative;}
.bs-wizard > .bs-wizard-step + .bs-wizard-step {}
.bs-wizard > .bs-wizard-step .bs-wizard-stepnum {color: #595959; font-size: 16px; margin-bottom: 5px;}
.bs-wizard > .bs-wizard-step .bs-wizard-info {color: black;
    font-size: 18px;
    font-weight: bold;
    letter-spacing: 0.1em;}
.bs-wizard > .bs-wizard-step > .bs-wizard-dot {    cursor: pointer;position: absolute; width: 30px; height: 30px; display: block; background: #fbe8aa; top: 45px; left: 50%; margin-top: -15px; margin-left: -15px; border-radius: 50%;} 
.bs-wizard > .bs-wizard-step > .bs-wizard-dot:after {content: ' '; width: 14px; height: 14px; background: #fbbd19; border-radius: 50px; position: absolute; top: 8px; left: 8px; } 
.bs-wizard > .bs-wizard-step > .progress {position: relative; border-radius: 0px; height: 8px; box-shadow: none; margin: 20px 0;}
.bs-wizard > .bs-wizard-step > .progress > .progress-bar {width:0px; box-shadow: none; background: #ffe17f;}
.bs-wizard > .bs-wizard-step.complete > .progress > .progress-bar {width:100%;}
.bs-wizard > .bs-wizard-step.active > .progress > .progress-bar {width:50%;}
.bs-wizard > .bs-wizard-step:first-child.active > .progress > .progress-bar {width:0%;}
.bs-wizard > .bs-wizard-step:last-child.active > .progress > .progress-bar {width: 100%;}
.bs-wizard > .bs-wizard-step.disabled > .bs-wizard-dot {background-color: #f5f5f5;}
.bs-wizard > .bs-wizard-step.disabled > .bs-wizard-dot:after {opacity: 0;}
.bs-wizard > .bs-wizard-step:first-child  > .progress {left: 50%; width: 50%;}
.bs-wizard > .bs-wizard-step:last-child  > .progress {width: 50%;}
button#email_step {
       /* float: right; */
    align-items: center;
    font-size: 25px;
    margin-right: 50px;
    /* margin-top: 10px; */
    /* width: 50%; */
    margin: 0 auto;
    margin-top: 30px;
    border: 1px solid #03a9f4;
}
.progress-bar {
    color:#ffe17f !important;
    background-color: #28b8da;
}
.bs-wizard > .bs-wizard-step.disabled a.bs-wizard-dot{ pointer-events: non}
</style>
