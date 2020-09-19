<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'new_applicant_form')); ?>
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">	
								<?php $value = (isset($applicant) ? $applicant->email : ''); ?>							
								<?php echo render_input('email','applicant_setting_email',$value,'text',array('required'=>'true')); ?>
							</div>
							<div class="col-md-6">
								<?php $value = (isset($applicant) ? $applicant->first_name : ''); ?>								
								<?php echo render_input('first_name','applicant_setting_firstname',$value,'text',array('required'=>'true')); ?>
							</div>
							<div class="col-md-6">
								<?php $value = (isset($applicant) ? $applicant->last_name : ''); ?>	
								<?php echo render_input('last_name','applicant_setting_lastname',$value,'text',array('required'=>'true')); ?>
							</div>
							<div class="col-md-6">
								<?php $value = (isset($applicant) ? $applicant->phone : ''); ?>	
								<?php echo render_input('phone','applicant_setting_phone',$value,'text',array('required'=>'true')); ?>
							</div>
							<div class="col-md-6">
								<div class="form-group select-placeholder">
									<label for="tax_specialist_name"><?php echo _l('Preffered Time'); ?></label>
									<div class="clearfix"></div>
									<?php								
									$value = (isset($applicant) ? $applicant->preffered_time : '');
									$value_ar = explode('--', $value);								
									?>	
									<select name="applicant_setting_preffered_time_from" class="selectpicker" id="applicant_setting_preffered_time_from" data-width="100%"  data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
										<option value="">Select Time</option>
										<option value="Morning : 08:00am" <?php if($value_ar[0]=='Morning : 08:00am'){echo "selected";} ?>>Morning : 08:00am</option>
										<option value="Morning : 09:00am" <?php if($value_ar[0]=='Morning : 09:00am'){echo "selected";} ?>>Morning : 09:00am</option>
										<option value="Morning : 10:00am" <?php if($value_ar[0]=='Morning : 10:00am'){echo "selected";} ?>>Morning : 10:00am</option>
										<option value="Morning : 11:00am" <?php if($value_ar[0]=='Morning : 11:00am'){echo "selected";} ?>>Morning : 11:00am</option>
										<option value="Afternoon : 12:00pm" <?php if($value_ar[0]=='Afternoon : 12:00pm'){echo "selected";} ?>>Afternoon : 12:00pm</option>
										<option value="Afternoon : 01:00pm" <?php if($value_ar[0]=='Afternoon : 01:00pm'){echo "selected";} ?>>Afternoon : 01:00pm</option>
										<option value="Afternoon : 02:00pm" <?php if($value_ar[0]=='Afternoon : 02:00pm'){echo "selected";} ?>>Afternoon : 02:00pm</option>
										<option value="Afternoon : 03:00pm" <?php if($value_ar[0]=='Afternoon : 03:00pm'){echo "selected";} ?>>Afternoon : 03:00pm"</option>
										<option value="Afternoon : 04:00pm" <?php if($value_ar[0]=='Afternoon : 04:00pm'){echo "selected";} ?>>Afternoon : 04:00pm"</option>
										<option value="Afternoon : 05:00pm" <?php if($value_ar[0]=='Afternoon : 05:00pm'){echo "selected";} ?>>Afternoon : 05:00pm"</option>
										<option value="Afternoon : 06:00pm" <?php if($value_ar[0]=='Afternoon : 06:00pm'){echo "selected";} ?>>Afternoon : 05:00pm"</option>
									</select> 
									--									
									<select name="applicant_setting_preffered_time_to" class="selectpicker" id="applicant_setting_preffered_time_to" data-width="100%"  data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
										<option value="">Select Time</option>
										<option value="Morning : 08:00am" <?php if($value_ar[1]=='Morning : 08:00am'){echo "selected";} ?>>Morning : 08:00am</option>
										<option value="Morning : 09:00am" <?php if($value_ar[1]=='Morning : 09:00am'){echo "selected";} ?>>Morning : 09:00am</option>
										<option value="Morning : 10:00am" <?php if($value_ar[1]=='Morning : 10:00am'){echo "selected";} ?>>Morning : 10:00am</option>
										<option value="Morning : 11:00am" <?php if($value_ar[1]=='Morning : 11:00am'){echo "selected";} ?>>Morning : 11:00am</option>
										<option value="Afternoon : 12:00pm" <?php if($value_ar[1]=='Afternoon : 12:00pm'){echo "selected";} ?>>Afternoon : 12:00pm</option>
										<option value="Afternoon : 01:00pm" <?php if($value_ar[1]=='Afternoon : 01:00pm'){echo "selected";} ?>>Afternoon : 01:00pm</option>
										<option value="Afternoon : 02:00pm" <?php if($value_ar[1]=='Afternoon : 02:00pm'){echo "selected";} ?>>Afternoon : 02:00pm</option>
										<option value="Afternoon : 03:00pm" <?php if($value_ar[1]=='Afternoon : 03:00pm'){echo "selected";} ?>>Afternoon : 03:00pm"</option>
										<option value="Afternoon : 04:00pm" <?php if($value_ar[1]=='Afternoon : 04:00pm'){echo "selected";} ?>>Afternoon : 04:00pm"</option>
										<option value="Afternoon : 05:00pm" <?php if($value_ar[1]=='Afternoon : 05:00pm'){echo "selected";} ?>>Afternoon : 05:00pm"</option>
										<option value="Afternoon : 06:00pm" <?php if($value_ar[1]=='Afternoon : 06:00pm'){echo "selected";} ?>>Afternoon : 05:00pm"</option>
									</select>    									
								</div>								
							</div>
							<div class="col-md-6">
								<div class="form-group select-placeholder">
									<label for="tax_specialist_name"><?php echo _l('Position'); ?></label>
									<div class="clearfix"></div>
									<?php $value = (isset($applicant) ? $applicant->position : '');	?>							
									<select name="position" class="selectpicker" id="position" data-width="100%"  data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
										<option value=""></option>
										<option value="Remote Sales Associate" <?php if($value=='Remote Sales Associate'){echo "selected";} ?>>Remote Sales Associate</option>
										<option value="Outside Sales Representative" <?php if($value=='Outside Sales Representative'){echo "selected";} ?>>Outside Sales Representative</option>										
									</select>    									
								</div>								
							</div>
							<div class="col-md-6">
								<div class="form-group select-placeholder">
									<label for="tax_specialist_name"><?php echo _l('Computer'); ?></label>
									<div class="clearfix"></div>
									<?php $value = (isset($applicant) ? $applicant->computer : '');	?>							
									<select name="computer" class="selectpicker" id="computer" data-width="100%"  data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
										<option value=""></option>
										<option value="Yes" <?php if($value=='Yes'){echo "selected";} ?>>Yes</option>
										<option value="No - I need more information" <?php if($value=='No - I need more information'){echo "selected";} ?>>No - I need more information</option>										
									</select>    									
								</div>								
							</div>
							<div class="col-md-6">
								<div class="form-group select-placeholder">
									<label for="tax_specialist_name"><?php echo _l('Screen'); ?></label>
									<div class="clearfix"></div>
									<?php $value = (isset($applicant) ? $applicant->screen : '');	?>							
									<select name="screen" class="selectpicker" id="screen" data-width="100%"  data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
										<option value=""></option>
										<option value="Yes" <?php if($value=='Yes'){echo "selected";} ?>>Yes</option>
										<option value="No" <?php if($value=='No'){echo "selected";} ?>>No</option>										
									</select>    									
								</div>								
							</div>
							<div class="col-md-6">
								<div class="form-group select-placeholder">
									<label for="tax_specialist_name"><?php echo _l('Zoom'); ?></label>
									<div class="clearfix"></div>
									<?php $value = (isset($applicant) ? $applicant->zoom : '');	?>							
									<select name="zoom" class="selectpicker" id="zoom" data-width="100%"  data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
										<option value=""></option>
										<option value="Yes" <?php if($value=='Yes'){echo "selected";} ?>>Yes</option>
										<option value="No" <?php if($value=='No'){echo "selected";} ?>>No</option>										
									</select>    									
								</div>								
							</div>	
							<div class="col-md-6">
								<div class="form-group ">
									<label for="tax_specialist_name"><?php echo _l('Upload Resume'); ?></label>
									<div class="clearfix"></div>
									<?php $value = (isset($applicant) ? $applicant->resume : '');	?>							
									<input type="file" name="upload_resume" > 
									<?php if($value!=''){ 
									 $value_arr = explode('/', $value);
									?>
                                    <a href="<?php echo site_url('/uploads/applicant/'.$value_arr[count($value_arr)-1]); ?>" target="_blank"><?php echo $value_arr[count($value_arr)-1]; ?></a>
									<?php } ?>  									
								</div>								
							</div>	
							<div class="col-md-6">
								<div class="form-group ">
									<label for="tax_specialist_name"><?php echo _l('DL'); ?></label>
									<div class="clearfix"></div>
									<?php $value = (isset($applicant) ? $applicant->DL : '');	?>							
									<input type="file" name="upload_dl" > 
									<?php if($value!=''){ 
									 $value_arr = explode('/', $value);
									?>
                                    <a href="<?php echo site_url('/uploads/applicant/'.$value_arr[count($value_arr)-1]); ?>" target="_blank"><?php echo $value_arr[count($value_arr)-1]; ?></a>
									<?php } ?>    									
								</div>								
							</div>
							
							<div class="col-md-6">
								<div class="form-group ">
									<input type="checkbox" <?php if((isset($applicant) && $applicant->t_c == "I agree to the Terms and Conditions") || !isset($applicant)){echo 'checked';} ?> name="t_c"  value="I agree to the Terms and Conditions">
								<label for="expenses"><?php echo _l('I agree to the Terms and Conditions'); ?></label>	  									
								</div>								
							</div>							
							
							<div class="col-md-6">
								<?php  $value = (isset($applicant) ? $applicant->attended_webinar : ''); ?>							
								<?php echo render_yes_no_option_custom($value,'attended_webinar','applicant_setting_attendedwebinar'); ?>
							</div>
							<div class="col-md-6">
								<?php  $value = (isset($applicant) ? $applicant->webinar_invite : ''); ?>								
								<?php echo render_yes_no_option_custom($value,'webinar_invite','applicant_webinarinvite'); ?>
							</div>
							<div class="col-md-6">
								<?php  $value = (isset($applicant) ? $applicant->opt : ''); ?>	
								<?php echo render_yes_no_option_custom($value,'opt','applicant_setting_optintolms'); ?>
							</div>
							<div class="col-md-6">
								<?php  $value = (isset($applicant) ? $applicant->completede_course : ''); ?>	
								<?php echo render_yes_no_option_custom($value,'completede_course','applicant_setting_completedecourse'); ?>
							</div>
							<div class="col-md-6">
								<?php  $value = (isset($applicant) ? $applicant->onboarded : ''); ?>	
								<?php echo render_yes_no_option_custom($value,'onboarded','applicant_setting_onboarded'); ?>
							</div>
							<div class="col-md-6">
								<?php echo $value = (isset($applicant) ? $applicant->territory_manager_interest : ''); ?>	
								<?php echo render_yes_no_option_custom($value,'territory_manager_interest','applicant_setting_territorymanagerinterest'); ?>
							</div>
							<div class="col-md-6">
								<?php  $value = (isset($applicant) ? $applicant->needs_another_webinar : ''); ?>	
								<?php echo render_yes_no_option_custom($value,'needs_another_webinar','applicant_setting_needsanotherwebinarinvite'); ?>
							</div>
							
								
						</div>
					
					<div class="col-md-12">
						<?php echo render_custom_fields('tickets'); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					
					<div class="panel-body">
						<div class="btn-bottom-toolbar text-right">
							<button type="submit" data-form="#new_ticket_form" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-info"><?php echo _l('open_applicant'); ?></button>
						</div>						
					</div>					
				</div>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>
</div>

<?php init_tail(); ?>

	</body>
	</html>
