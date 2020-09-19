<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <?php if(!is_gdpr()) { ?>
         <div class="panel_s">
            <div class="panel-body">
               <div class="col-md-12 text-center">
                  <h4>GDPR not enabled.</h4>
                  <a href="<?php echo admin_url('gdpr/enable'); ?>" class="btn btn-info">ENABLE GDPR</a>
               </div>
            </div>
         </div>
         <?php } else { ?>
         <?php if($save == true){ ?>
         <?php echo form_open(admin_url('gdpr/save?page='.$page)); ?>
         <?php } ?>
         <div class="col-md-3">
            <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked">
               <li<?php if($page == 'general'){echo ' class="active"'; } ?>>
                  <a href="<?php echo admin_url('gdpr/index?page=general'); ?>">General</a>
               </li>
               <li<?php if($page == 'portability'){echo ' class="active"'; } ?>>
                  <a href="<?php echo admin_url('gdpr/index?page=portability'); ?>">Right to data portability</a>
               </li>
               <li<?php if($page == 'forgotten'){echo ' class="active"'; } ?>>
                  <a href="<?php echo admin_url('gdpr/index?page=forgotten'); ?>">Right to be forgotten</a>
               </li>
               <li<?php if($page == 'informed'){echo ' class="active"'; } ?>>
                  <a href="<?php echo admin_url('gdpr/index?page=informed'); ?>">Right to be informed</a>
               </li>
               <li<?php if($page == 'rectification'){echo ' class="active"'; } ?>>
                  <a href="<?php echo admin_url('gdpr/index?page=rectification'); ?>">Right of access/right to rectification</a>
               </li>
               <li<?php if($page == 'consent'){echo ' class="active"'; } ?>>
                  <a href="<?php echo admin_url('gdpr/index?page=consent'); ?>">Consent</a>
               </li>
            </ul>
         </div>
         <div class="col-md-9">
            <div class="panel_s">
               <div class="panel-body">
                  <?php $this->load->view('admin/gdpr/pages/'.$page); ?>
               </div>
            </div>
         </div>
         <?php if($save == true){ ?>
         <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
            <button type="submit" class="btn btn-info">Save</button>
         </div>
         <?php echo form_close(); ?>
         <?php } ?>
         <?php } ?>
      </div>
   </div>
</div>
<div id="page-tail"></div>
<?php init_tail(); ?>
<script>
   $(function(){
     $('.removalStatus').on('change', function(e){
       var id = $(this).attr('data-id');
       var val = $(this).selectpicker('val');

       // Event is invoked twice? Second is jQuery object
       if(typeof(val) != 'string') {
          return;
       }
       requestGet('gdpr/change_removal_request_status/'+id+'/'+val);
     });
   });
</script>
</body>
</html>
