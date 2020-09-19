<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <a href="<?php echo admin_url('applicants/applicant');?>" class="btn mright5 btn-info pull-left display-block">
                     <?php echo _l('new_applicants'); ?>
                     </a>
                     <?php if(is_admin() || get_option('allow_non_admin_members_to_import_applicants') == '1'){ ?>
                     <a href="<?php echo admin_url('applicants/import'); ?>" class="btn btn-info pull-left display-block hidden-xs">
                     <?php echo _l('import_applicants'); ?>
                     </a>
                     <?php } ?>
                     
                     <div class="clearfix"></div>
                     <div class="row hide leads-overview">
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                           <h4 class="no-margin"><?php echo _l('leads_summary'); ?></h4>
                        </div>
                        <?php
                           foreach($summary as $status) { ?>
                              <div class="col-md-2 col-xs-6 border-right">
                                    <h3 class="bold">
                                       <?php
                                          if(isset($status['percent'])) {
                                             echo '<span data-toggle="tooltip" data-title="'.$status['total'].'">'.$status['percent'].'%</span>';
                                          } else {
                                             // Is regular status
                                             echo $status['total'];
                                          }
                                       ?>
                                    </h3>
                                   <span style="color:<?php echo $status['color']; ?>" class="<?php echo isset($status['junk']) || isset($status['lost']) ? 'text-danger' : ''; ?>"><?php echo $status['name']; ?></span>
                              </div>
                           <?php } ?>
                     </div>
                  </div>
                  <hr class="hr-panel-heading" />
                  <div class="tab-content">
                     <?php
                        if($this->session->has_userdata('leads_kanban_view') && $this->session->userdata('leads_kanban_view') == 'true') { ?>
                     <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                        <div class="kanban-leads-sort">
                           <span class="bold"><?php echo _l('leads_sort_by'); ?>: </span>
                           <a href="#" onclick="leads_kanban_sort('dateadded'); return false" class="dateadded">
                           <?php if(get_option('default_leads_kanban_sort') == 'dateadded'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_leads_kanban_sort_type')).'"></i> ';} ?><?php echo _l('leads_sort_by_datecreated'); ?>
                           </a>
                           |
                           <a href="#" onclick="leads_kanban_sort('leadorder');return false;" class="leadorder">
                           <?php if(get_option('default_leads_kanban_sort') == 'leadorder'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_leads_kanban_sort_type')).'"></i> ';} ?><?php echo _l('leads_sort_by_kanban_order'); ?>
                           </a>
                           |
                           <a href="#" onclick="leads_kanban_sort('lastcontact');return false;" class="lastcontact">
                           <?php if(get_option('default_leads_kanban_sort') == 'lastcontact'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_leads_kanban_sort_type')).'"></i> ';} ?><?php echo _l('leads_sort_by_lastcontact'); ?>
                           </a>
                        </div>
                        <div class="row">
                           <div class="container-fluid leads-kan-ban">
                              <div id="kan-ban"></div>
                           </div>
                        </div>
                     </div>
                     <?php } else { ?>
                     <div class="row" id="leads-table">
                        
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                           <a href="#" data-toggle="modal" data-table=".table-leads" data-target="#leads_bulk_actions" class="hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
                           <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1" role="dialog">
                              <div class="modal-dialog" role="document">
                                 <div class="modal-content">
                                    <div class="modal-header">
                                       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                       <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                       <?php if(has_permission('leads','','delete')){ ?>
                                       <div class="checkbox checkbox-danger">
                                          <input type="checkbox" name="mass_delete" id="mass_delete">
                                          <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                       </div>
                                       <hr class="mass_delete_separator" />
                                       <?php } ?>
                                       <div id="bulk_change">
                                          <?php echo render_select('move_to_status_leads_bulk',$statuses,array('id','name'),'ticket_single_change_status'); ?>
                                          <?php
                                             echo render_select('move_to_source_leads_bulk',$sources,array('id','name'),'lead_source');
                                             echo render_datetime_input('leads_bulk_last_contact','leads_dt_last_contact');
                                             if(has_permission('leads','','edit')){
                                               echo render_select('assign_to_leads_bulk',$staff,array('staffid',array('firstname','lastname')),'leads_dt_assigned');
                                             }
                                             ?>
                                          <div class="form-group">
                                             <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                             <input type="text" class="tagsinput" id="tags_bulk" name="tags_bulk" value="" data-role="tagsinput">
                                          </div>
                                          <hr />
                                          <div class="form-group no-mbot">
                                             <div class="radio radio-primary radio-inline">
                                                <input type="radio" name="leads_bulk_visibility" id="leads_bulk_public" value="public">
                                                <label for="leads_bulk_public">
                                                <?php echo _l('lead_public'); ?>
                                                </label>
                                             </div>
                                             <div class="radio radio-primary radio-inline">
                                                <input type="radio" name="leads_bulk_visibility" id="leads_bulk_private" value="private">
                                                <label for="leads_bulk_private">
                                                <?php echo _l('private'); ?>
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="modal-footer">
                                       <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                       <a href="#" class="btn btn-info" onclick="leads_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                    </div>
                                 </div>
                                 <!-- /.modal-content -->
                              </div>
                              <!-- /.modal-dialog -->
                           </div>
                           <!-- /.modal -->
                           <?php

                              $table_data = array();
                           $_table_data = array(
                            '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="clients"><label></label></div>',                            
                            _l('Applicants Name'),
                            // _l('applicants_last_name'),
                            _l('Applicants Email'),
                            _l('Applicants Phone'),
                            _l('Resume'),
                            _l('Create Employee'),
                            );
                              foreach($_table_data as $_t){
                               array_push($table_data,$_t);
                              }
                              
                
                              $table_data = do_action('applicants',$table_data);
                //echo "<pre>";print_r($table_data);echo"</pe>";
                              //$table_data = do_action('customers_table_columns',$table_data);

                render_datatable($table_data,'applicants',[],[
                     'data-last-order-identifier' => 'applicants',
                     'data-default-order'         => get_table_last_order('applicants'),
                ]);
                ?>
                        </div>
                     </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script id="hidden-columns-table-leads" type="text/json">
   <?php echo get_staff_meta(get_staff_user_id(), 'hidden-columns-table-leads'); ?>
</script>

<?php init_tail(); ?>
<script>
   var openLeadID = '<?php echo $leadid; ?>';
   $(function(){
      leads_kanban();
   });
</script>
<script>
    $(function(){
        var CustomersServerParams = {};
        $.each($('._hidden_inputs._filters input'),function(){
           CustomersServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
       });
        CustomersServerParams['exclude_inactive'] = '[name="exclude_inactive"]:checked';

        var tAPI = initDataTable('.table-applicants', admin_url+'applicants/table', [0], [0], CustomersServerParams,<?php echo do_action('applicant_table_default_order',json_encode(array(2,'asc'))); ?>);
        $('input[name="exclude_inactive"]').on('change',function(){
            tAPI.ajax.reload();
        });
    });
    function customers_bulk_action(event) {
        var r = confirm(appLang.confirm_action_prompt);
        if (r == false) {
            return false;
        } else {
            var mass_delete = $('#mass_delete').prop('checked');
            var ids = [];
            var data = {};
            if(mass_delete == false || typeof(mass_delete) == 'undefined'){
                data.groups = $('select[name="move_to_groups_customers_bulk[]"]').selectpicker('val');
                if (data.groups.length == 0) {
                    data.groups = 'remove_all';
                }
            } else {
                data.mass_delete = true;
            }
            var rows = $('.table-clients').find('tbody tr');
            $.each(rows, function() {
                var checkbox = $($(this).find('td').eq(0)).find('input');
                if (checkbox.prop('checked') == true) {
                    ids.push(checkbox.val());
                }
            });
            data.ids = ids;
            $(event).addClass('disabled');
            setTimeout(function(){
              $.post(admin_url + 'clients/bulk_action', data).done(function() {
               window.location.reload();
           });
          },50);
        }
    }


  $(document).on("click",".add_users",function(e)
  {
    var applicant_id = parseInt($(this).attr("data-id"));     
    if(applicant_id)
    {

        if (confirm('- Are You Sure To Convert Applicant To User !  ')) 
        {

            $.ajax({
                    url: admin_url+'applicants/convert_to_user/'+applicant_id,
                    type: "post",
                    data: {applicant_id : applicant_id,} ,
                    dataType: 'json',
                    success: function (response) 
                    {
                        // response = $.parseJSON(response);
                        
                        if(response.success)
                        {
                            console.log(response.message);
                            location.reload(); 
                        }
                        else
                        {
                            console.log(response.message);
                            location.reload();
                        }                     
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                    }
                });
          }

    }
    else
    {
        alert('Sorry Invalid Applicant Id ! ');
    }
  });

$(document).on("click",".user_added",function(){
    alert('This Applicant Is Already Converted To User !');
});  

</script>


</body>
</html>
