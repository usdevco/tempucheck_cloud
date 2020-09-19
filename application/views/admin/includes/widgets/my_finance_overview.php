  <?php  if(has_permission('invoices','','view') || has_permission('proposals','','view') || has_permission('estimates','','view') || has_permission('estimates','','view_own') || has_permission('proposals','','view_own') || has_permission('invoices','','view_own')){ ?>
  <div class="finance-summary">
    <div class="panel_s">
     <div class="panel-body">
      <div class="row home-summary">

<?php if(has_permission('estimates','','view') || has_permission('estimates','','view_own')){ ?>
<div class="col-md-12">
  <div class="row">
   <div class="col-md-12 text-stats-wrapper">
    <p class="text-dark text-uppercase"><?php echo _l('home_estimate_overview'); ?></p>
    <hr class="no-mtop" />
  </div>
  <?php
  // Add not sent
  array_splice( $estimate_statuses, 1, 0, 'not_sent' );
  foreach($estimate_statuses as $status){
    $url = admin_url('estimates/list_estimates?status='.$status);
    if(!is_numeric($status)){
      $url = admin_url('estimates/list_estimates?filter='.$status);
    }
    $percent_data = get_estimates_percent_by_status($status);
    ?>
    <div class="col-md-12 text-stats-wrapper">
     <a href="<?php echo $url; ?>" class="text-<?php echo estimate_status_color_class($status,true); ?> mbot15 inline-block estimate-status-dashboard-<?php echo estimate_status_color_class($status,true); ?>">
      <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span>
    </span> <?php echo format_estimate_status($status,'',false); ?>
  </a>
</div>
<div class="col-md-12 text-right progress-finance-status">
  <?php echo $percent_data['percent']; ?>%
  <div class="progress no-margin progress-bar-mini">
   <div class="progress-bar progress-bar-<?php echo estimate_status_color_class($status); ?> no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
   </div>
 </div>
</div>
<?php } ?>
</div>
</div>
<?php } ?>
		
		  <div style="clear: both; padding: 15px"></div>
		  
<?php // if(has_permission('proposals','','view') || has_permission('proposals','','view_own')){ ?>
<!--
<div class="col-md-12">
  <div class="row">
   <div class="col-md-12 text-stats-wrapper">
    <p class="text-dark text-uppercase"><?php echo _l('home_proposal_overview'); ?></p>
    <hr class="no-mtop" />
  </div>
  <?php // foreach($proposal_statuses as $status){
   // $url = admin_url('proposals/list_proposals?status='.$status);
   // $percent_data = get_proposals_percent_by_status($status);
    ?>
    <div class="col-md-12 text-stats-wrapper">
      <a href="<?php echo $url; ?>" class="text-<?php echo proposal_status_color_class($status,true); ?> mbot15 inline-block">
        <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span> <?php echo format_proposal_status($status,'',false); ?>
      </a>
    </div>
    <div class="col-md-12 text-right progress-finance-status">
     <?php // echo $percent_data['percent']; ?>%
     <div class="progress no-margin progress-bar-mini">
      <div class="progress-bar progress-bar-<?php echo proposal_status_color_class($status); ?> no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php // echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php // echo $percent_data['percent']; ?>">
      </div>
    </div>
  </div>
  <?php // } ?>
  <div class="clearfix"></div>
</div>
</div>
-->
<?php // } ?>
</div>
<?php // if(has_permission('invoices','','view') || has_permission('invoices','','view_own')){ ?>
<!--<hr />
<a href="#" class="hide invoices-total initialized"></a>
<div id="invoices_total" class="invoices-total-inline">
  <?php // load_invoices_total_template(); ?>
</div>-->
<?php // } ?>
</div>
</div>
</div>
<?php } ?>

