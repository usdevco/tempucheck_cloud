
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
  <link href="<?php echo base_url('uploads/company/favicon.png'); ?>" rel="shortcut icon">
  <title>Tempucheck CRM</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/css/spacing_bs4.css'); ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/plugins/app-build/vendor.css') ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.min.css') ?>">

  <!-- <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/home.css'); ?>"> -->
  </head>

  <body class="bg-dark">

    <div class="container">

        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                   <div class="panel-body">
                      <div class="row _buttons">
                         <div class="col-md-12">
                          <h1 class="text-center"> <a  href="<?php base_url(''); ?>"> Tempucheck cloud instance registration </a></h1>
                          
                         </div>
                      </div>
                      <hr class="hr-panel-heading hr-10" />
                      <?php
                      if( $this->session->flashdata('flashError'))
                      {
                        ?>
                        <div class="alert alert-danger"  role="alert"><?php echo $this->session->flashdata('flashError')?></div>
                        <?php
                      } ?>
                      
                      
                      <?php
                      if( $this->session->flashdata('SUCCESS'))
                      {
                        ?>
                        <div class="alert alert-success" role="alert"><?php echo $this->session->flashdata('SUCCESS')?></div>

                        <?php
                      } ?>

                      <div class="clearfix"></div>

                      <div class="col-12 col-md-12 col-lg-12">
                         <div class="card">
                            <div class="card-body">

                              <form action="https://tempucheckcrm.com/api/savepdf" method="post" enctype="multipart/form-data">

                                <div class="row">

                                  <div class="col-md-12">
                                     <div class="form-group ">
                                       
                                        <input type="text"  name="userid"  value="1" id="userid" class="form-control">

                                     </div>
                                  </div>

                                  <div class="col-md-12">
                                     <div class="form-group ">
                                       
                                        <input type="file"  name="pdf_file"  id="pdf_file" class="form-control">

                                     </div>
                                  </div>


                                  <div class="col-md-8"> </div>
                                   <div class="col-md-4 text-left">
                                      <?php $saveUpdate = isset($instance_id) ? 'Update Information' : 'Save Information'; ?>
                                      <input type="submit"  value="<?php echo ucfirst($saveUpdate);?>" class="btn btn-primary px-5" name='submit'>
                                      <a href="<?php echo base_url('instance');?>" class="btn btn-danger "><?php echo 'Cancel'; ?></a>
                                   </div>
                                     
                                  <div class="clearfix"></div>
                                </div>
                              </form>
                            </div>
                         </div>
                      </div>
                   </div>
                </div>
             </div>
        </div> 
    </div>    
   

  </body>
</html>