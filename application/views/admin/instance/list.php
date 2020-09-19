<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="row _buttons">
                     <div class="col-md-12">
                        <a class="btn btn-primary pull-right" href="<?php echo base_url('admin/instance/add_instance'); ?>">Add Instance</a>
                     </div>
                  </div>
                  <hr class="hr-panel-heading hr-10" />
                  <div class="clearfix"></div>

                     <?php $this->load->view('admin/instance/table',array('bulk_actions'=>false)); ?>
             
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>

<script>
    $(function(){
        var CustomtaskServerParams = {};
        $.each($('._hidden_inputs._filters input'),function(){
           CustomtaskServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
       });
        CustomtaskServerParams['exclude_inactive'] = '[name="exclude_inactive"]:checked';

        var tAPI = initDataTable('.table-tblinstance', admin_url+'instance/table', [0], [0], CustomtaskServerParams,<?php echo do_action('instance_table_default_order',json_encode(array(2,'asc'))); ?>);
        $('input[name="exclude_inactive"]').on('change',function(){
            tAPI.ajax.reload();
        });
        
    });
</script>

</body>
</html>
