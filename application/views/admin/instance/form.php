<?php init_head();
?>
<?php //echo app_stylesheet('assets/plugins/tagsinput/css','tagsinput.css'); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                   <div class="panel-body">
                      <div class="row _buttons">
                         <div class="col-md-12">
                           <h2> <?php echo $title; ?></h2>
                         </div>
                      </div>
                      <hr class="hr-panel-heading hr-10" />

                      <div class="clearfix"></div>

                      <div class="col-12 col-md-12 col-lg-12">
                         <div class="card">
                            <div class="card-body">

                              <?php echo form_open_multipart('', array('role'=>'form','novalidate'=>'novalidate')); ?> 

                                <div class="row">

                                  <div class="col-md-12">
                                     <div class="form-group <?php echo form_error('company_name') ? ' has-error' : ''; ?>">
                                        <?php echo  form_label('Company Name 111', 'company_name'); ?> 
                                        <span class="required text-danger">*</span>
                                        <?php 
                                        $populateData = $this->input->post('company_name') ? $this->input->post('company_name') : (isset($company_data_array->company_name) ? $company_data_array->company_name :  '' );
                                        ?>

                                        <input type="text" name="company_name"  value="<?php echo $populateData;?>" id="company_name" class="form-control">

                                        <span class="small form-error"> <?php echo strip_tags(form_error('company_name')); ?> </span>
                                     </div>
                                  </div>

                                  <div class="clearfix"></div>

                                  <div class="col-md-12">
                                     <div class="form-group <?php echo form_error('instance_name') ? ' has-error' : ''; ?>">
                                        <?php echo  form_label('Instance Name', 'instance_name'); ?> 
                                        <span class="required text-danger">*</span>
                                        <?php 
                                        $populateData = $this->input->post('instance_name') ? $this->input->post('instance_name') : (isset($company_data_array->instance_name) ? $company_data_array->instance_name :  '' );
                                        ?>
                                        <div class="input-group">
                                        <input type="text" name="instance_name"  value="<?php echo $populateData;?>" id="instance_name" class="form-control input-lg" >
                                         <span class="input-group-btn small">
                                            <button type="button" class="btn btn-default small py-3 " style="font-size: 10px;">tempucheck.com</button>
                                        </span>
                                        </div>

                                        <span class="small form-error"> <?php echo strip_tags(form_error('instance_name')); ?> </span>
                                     </div>
                                  </div>

                                  <div class="clearfix"></div>



                                  <div class="col-md-12">
                                     <div class="form-group <?php echo form_error('phone') ? ' has-error' : ''; ?>">
                                        <?php echo  form_label('Phone Number', 'phone'); ?> 
                                        <span class="required text-danger">*</span>
                                        <?php 
                                        $populateData = $this->input->post('phone') ? $this->input->post('phone') : (isset($company_data_array->phone) ? $company_data_array->phone :  '' );
                                        ?>

                                        <input type="text" name="phone"  value="<?php echo $populateData;?>" id="phone" class="form-control">

                                        <span class="small form-error"> <?php echo strip_tags(form_error('phone')); ?> </span>
                                     </div>
                                  </div>

                                  <div class="clearfix"></div>




                                  <div class="col-md-12">
                                     <div class="form-group <?php echo form_error('first_name') ? ' has-error' : ''; ?>">
                                        <?php echo  form_label('First Name', 'first_name'); ?> 
                                        <span class="required text-danger">*</span>
                                        <?php 
                                        $populateData = $this->input->post('first_name') ? $this->input->post('first_name') : (isset($company_data_array->first_name) ? $company_data_array->first_name :  '' );
                                        ?>

                                        <input type="text" name="first_name"  value="<?php echo $populateData;?>" id="first_name" class="form-control">

                                        <span class="small form-error"> <?php echo strip_tags(form_error('first_name')); ?> </span>
                                     </div>
                                  </div>

                                  <div class="clearfix"></div>





                                  <div class="col-md-12">
                                     <div class="form-group <?php echo form_error('last_name') ? ' has-error' : ''; ?>">
                                        <?php echo  form_label('Last Name', 'last_name'); ?> 
                                        <span class="required text-danger">*</span>
                                        <?php 
                                        $populateData = $this->input->post('last_name') ? $this->input->post('last_name') : (isset($company_data_array->last_name) ? $company_data_array->last_name :  '' );
                                        ?>

                                        <input type="text" name="last_name"  value="<?php echo $populateData;?>" id="last_name" class="form-control">

                                        <span class="small form-error"> <?php echo strip_tags(form_error('last_name')); ?> </span>
                                     </div>
                                  </div>

                                  <div class="clearfix"></div>





                                  <div class="col-md-12">
                                     <div class="form-group <?php echo form_error('company_address') ? ' has-error' : ''; ?>">
                                        <?php echo  form_label('Company Address', 'company_address'); ?> 
                                        <span class="required text-danger">*</span>
                                        <?php 
                                        $populateData = $this->input->post('company_address') ? $this->input->post('company_address') : (isset($company_data_array->company_address) ? $company_data_array->company_address :  '' );
                                        ?>

                                        <textarea name="company_address" rows="5"  id="company_address" class="form-control"><?php echo $populateData;?></textarea>

                                        <span class="small form-error"> <?php echo strip_tags(form_error('company_address')); ?> </span>
                                     </div>
                                  </div>

                                  <div class="clearfix"></div>






                                  <div class="col-md-12">
                                     <div class="form-group <?php echo form_error('city') ? ' has-error' : ''; ?>">
                                        <?php echo  form_label('City Name', 'city'); ?> 
                                        <span class="required text-danger">*</span>
                                        <?php 
                                        $populateData = $this->input->post('city') ? $this->input->post('city') : (isset($company_data_array->city) ? $company_data_array->city :  '' );
                                        ?>

                                        <input type="text" name="city"  value="<?php echo $populateData;?>" id="city" class="form-control">

                                        <span class="small form-error"> <?php echo strip_tags(form_error('city')); ?> </span>
                                     </div>
                                  </div>

                                  <div class="clearfix"></div>





                                  <div class="col-md-12">
                                     <div class="form-group <?php echo form_error('state') ? ' has-error' : ''; ?>">
                                        <?php echo  form_label('State Name', 'state'); ?> 
                                        <span class="required text-danger">*</span>
                                        <?php 
                                        $populateData = $this->input->post('state') ? $this->input->post('state') : (isset($company_data_array->state) ? $company_data_array->state :  '' );
                                        ?>

                                        <input type="text" name="state"  value="<?php echo $populateData;?>" id="state" class="form-control">

                                        <span class="small form-error"> <?php echo strip_tags(form_error('state')); ?> </span>
                                     </div>
                                  </div>

                                  <div class="clearfix"></div>


                                  <div class="col-md-12">
                                     <div class="form-group <?php echo form_error('zip') ? ' has-error' : ''; ?>">
                                        <?php echo  form_label('Zip Code', 'zip'); ?> 
                                        <span class="required text-danger">*</span>
                                        <?php 
                                        $populateData = $this->input->post('zip') ? $this->input->post('zip') : (isset($company_data_array->zip) ? $company_data_array->zip :  '' );
                                        ?>

                                        <input type="text" name="zip"  value="<?php echo $populateData;?>" id="zip" class="form-control">

                                        <span class="small form-error"> <?php echo strip_tags(form_error('zip')); ?> </span>
                                     </div>
                                  </div>

                                  <div class="clearfix"></div>

                                  <div class="col-md-12">
                                    <?php 
                                    $captcha_err = form_error('g-recaptcha-response') ? ' has-error' : (form_error('recaptcha') ? ' has-error ' : '');

                                    $captcha_err_msg = form_error('g-recaptcha-response') ? strip_tags(form_error('g-recaptcha-response')) : (form_error('recaptcha') ? strip_tags(form_error('recaptcha')) : '');
                                    ?>
                                      <div class="g-recaptcha form-group <?php echo $captcha_err;  ?>" data-sitekey="<?php echo $this->config->item('google_key') ?>"></div> 
                                      <span class="small form-error text-danger"> <?php echo $captcha_err_msg; ?> </span>
                                  </div>

                                  <hr>
                                  <div class="clearfix"></div>

                                  <div class="col-md-8"> </div>
                                   <div class="col-md-4 text-left">
                                      <?php $saveUpdate = isset($instance_id) ? 'Update Information' : 'Save Information'; ?>
                                      <input type="submit"  value="<?php echo ucfirst($saveUpdate);?>" class="btn btn-primary px-5" name='submit'>
                                      <a href="<?php echo base_url('admin/instance');?>" class="btn btn-danger "><?php echo 'Cancel'; ?></a>
                                   </div>
                                     
                                  <div class="clearfix"></div>
                                </div>
                              <?php echo form_close();?>
                            </div>
                         </div>
                      </div>
                   </div>
                </div>
             </div>
        </div> 
    </div>          
</div>
<?php init_tail(); ?>
<?php// echo app_script('assets/plugins/tagsinput/js','tagsinput.js'); ?>

 <script src="https://www.google.com/recaptcha/api.js"></script>

 <script>
   function onSubmit(token) {
     document.getElementById("demo-form").submit();
   }
 </script>