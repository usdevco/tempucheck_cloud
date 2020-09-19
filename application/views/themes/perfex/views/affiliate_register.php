<div class="content">
   <div class="row">
      <?php if(isset($member)){ ?>
      <div class="col-md-12">
         <div class="panel_s">
            <div class="panel-body no-padding-bottom">
               <?php $this->load->view('admin/staff/stats'); ?>
            </div>
         </div>
      </div>
      <div class="member">
         <?php echo form_hidden('isedit'); ?>
         <?php echo form_hidden('memberid',$member->staffid); ?>
      </div>
      <?php } ?>
      <?php if(isset($member)){ ?>

      <div class="col-md-12">
         <?php if(total_rows('tbldepartments',array('email'=>$member->email)) > 0) { ?>
            <div class="alert alert-danger">
               The staff member email exists also as support department email, according to the docs, the support department email must be unique email in the system, you must change the staff email or the support department email in order all the features to work properly.
            </div>
         <?php } ?>
         <div class="panel_s">
            <div class="panel-body">
               <h4 class="no-margin"><?php echo $member->firstname . ' ' . $member->lastname; ?>
                  <?php if($member->last_activity && $member->staffid != get_staff_user_id()){ ?>
                  <small> - <?php echo _l('last_active'); ?>:
                        <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($member->last_activity); ?>">
                              <?php echo time_ago($member->last_activity); ?>
                        </span>
                     </small>
                  <?php } ?>
                  <a href="#" onclick="small_table_full_view(); return false;" data-placement="left" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="toggle_view pull-right">
                  <i class="fa fa-expand"></i></a>
               </h4>
            </div>
         </div>
      </div>
      <?php } ?>
      <?php echo form_open_multipart($this->uri->uri_string(),array('class'=>'staff-form','autocomplete'=>'off')); ?>
      <div class="col-md-<?php if(!isset($member)){echo '8 col-md-offset-2';} else {echo '5';} ?>" id="small-table">
         <div class="panel_s">
            <div class="panel-body">

               <div class="tab-content">
                  <div role="tabpanel" class="tab-pane active" id="tab_staff_profile">
                     <?php if((isset($member) && $member->profile_image == NULL) || !isset($member)){ ?>
                     <div class="form-group">
                        <label for="profile_image" class="profile-image"><?php echo _l('staff_edit_profile_image'); ?></label>
                        <input type="file" name="profile_image" class="form-control" id="profile_image">
                     </div>
                     <?php } ?>
                     <?php if(isset($member) && $member->profile_image != NULL){ ?>
                     <div class="form-group">
                        <div class="row">
                           <div class="col-md-9">
                              <?php echo staff_profile_image($member->staffid,array('img','img-responsive','staff-profile-image-thumb'),'thumb'); ?>
                           </div>
                           <div class="col-md-3 text-right">
                              <a href="<?php echo admin_url('staff/remove_staff_profile_image/'.$member->staffid); ?>"><i class="fa fa-remove"></i></a>
                           </div>
                        </div>
                     </div>
                     <?php } ?>
                     <?php $value = (isset($member) ? $member->firstname : ''); ?>
                     <?php $attrs = (isset($member) ? array() : array('autofocus'=>true)); ?>
                     <?php echo render_input('firstname','staff_add_edit_firstname',$value,'text',$attrs); ?>
                     <?php echo form_error('firstname'); ?>

                     <?php $value = (isset($member) ? $member->lastname : ''); ?>
                     <?php echo render_input('lastname','staff_add_edit_lastname',$value); ?>
                     <?php echo form_error('lastname'); ?>

                     <?php $value = (isset($member) ? $member->email : ''); ?>
                     <?php echo render_input('email','staff_add_edit_email',$value,'email',array('autocomplete'=>'off')); ?>
                     <?php echo form_error('email'); ?>

                     <div class="form-group hide">
                        <label for="hourly_rate"><?php echo _l('staff_hourly_rate'); ?></label>
                        <div class="input-group">
                           <input type="number" name="hourly_rate" value="<?php if(isset($member)){echo $member->hourly_rate;} else {echo 0;} ?>" id="hourly_rate" class="form-control">
                           <span class="input-group-addon">
                           <?php echo $base_currency->symbol; ?>
                           </span>
                        </div>
                     </div>
                     <?php $value = (isset($member) ? $member->phonenumber : ''); ?>
                     <?php echo render_input('phonenumber','staff_add_edit_phonenumber',$value); ?>
                     <div class="form-group">
                        <label for="facebook" class="control-label"><i class="fa fa-facebook"></i> <?php echo _l('staff_add_edit_facebook'); ?></label>
                        <input type="text" class="form-control" name="facebook" value="<?php if(isset($member)){echo $member->facebook;} ?>">
                     </div>
                     <div class="form-group">
                        <label for="linkedin" class="control-label"><i class="fa fa-linkedin"></i> <?php echo _l('staff_add_edit_linkedin'); ?></label>
                        <input type="text" class="form-control" name="linkedin" value="<?php if(isset($member)){echo $member->linkedin;} ?>">
                     </div>
                     <div class="form-group">
                        <label for="skype" class="control-label"><i class="fa fa-skype"></i> <?php echo _l('staff_add_edit_skype'); ?></label>
                        <input type="text" class="form-control" name="skype" value="<?php if(isset($member)){echo $member->skype;} ?>">
                     </div>
                     <?php if(get_option('disable_language') == 0){ ?>
                     <div class="form-group select-placeholder">
                        <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?></label>
                        <select name="default_language" data-live-search="true" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value=""><?php echo _l('system_default_string'); ?></option>
                           <?php foreach($this->app->get_available_languages() as $language){
                              $selected = '';
                              if(isset($member)){
                               if($member->default_language == $language){
                                $selected = 'selected';
                              }
                              }
                              ?>
                           <option value="<?php echo $language; ?>" <?php echo $selected; ?>><?php echo ucfirst($language); ?></option>
                           <?php } ?>
                        </select>
                     </div>
                     <?php } ?>
                     <?php /*
                     <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('staff_email_signature_help'); ?>"></i>
                     <?php $value = (isset($member) ? $member->email_signature : ''); ?>
                     <?php echo render_textarea('email_signature','settings_email_signature',$value); ?>
                     */ ?>

                     <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                     <div class="clearfix form-group"></div>
                     <label for="password" class="control-label"><?php echo _l('staff_add_edit_password'); ?></label>
                     <div class="form-group">
                        <input type="password" class="form-control password" name="password" autocomplete="off">
                        <?php echo form_error('password'); ?>
                     </div>

                     <div class="clearfix form-group"></div>
                     <label for="password" class="control-label"><?php echo _l('clients_register_password_repeat'); ?></label>
                     <div class="form-group">
                        <input type="password" class="form-control" name="passwordr" autocomplete="off">
                        <?php echo form_error('passwordr'); ?>
                     </div>




                     <div class="checkbox">
                        <input type="checkbox" name="terms_condition" id="terms_condition" required>
                        <label for="terms_condition">
                           I agree to the <strong class="text-info">terms and conditions </strong>
                        </label>
                        <?php echo form_error('terms_condition'); ?>

                        <a class="pull-right" target="_blank" download href="https://www.irs.gov/pub/irs-pdf/fw9.pdf" >download W9</a>
                     </div>




                  </div>

               </div>
            </div>
         </div>
      </div>


      <div class="row">
          <div class="col-md-12 text-center">
              <div class="form-group">
                  <button type="submit" autocomplete="off" class="btn btn-info"><?php echo _l('clients_register_string'); ?></button>
              </div>
          </div>
      </div>

      <?php echo form_close(); ?>

       </div>
   <div class="btn-bottom-pusher"></div>
</div>
