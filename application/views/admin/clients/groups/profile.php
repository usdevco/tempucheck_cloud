<h4 class="customer-profile-group-heading"><?php echo _l('client_add_edit_profile'); ?></h4>
<div class="row">
   <?php echo form_open($this->uri->uri_string(),array('class'=>'client-form','autocomplete'=>'off')); ?>
   <div class="additional"></div>
   <div class="col-md-12">
      <div class="horizontal-scrollable-tabs">
<div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
<div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
<div class="horizontal-tabs">
      <ul class="nav nav-tabs profile-tabs row customer-profile-tabs nav-tabs-horizontal" role="tablist">
         <li role="presentation" class="<?php if(!$this->input->get('tab')){echo 'active';}; ?>">
            <a href="#contact_info" aria-controls="contact_info" role="tab" data-toggle="tab">
            <?php echo _l( 'customer_profile_details'); ?>
            </a>
         </li>
         <?php
            $customer_custom_fields = false;
            if(total_rows('tblcustomfields',array('fieldto'=>'customers','active'=>1)) > 0 ){
                 $customer_custom_fields = true;
             ?>
         <li role="presentation" class="<?php if($this->input->get('tab') == 'custom_fields'){echo 'active';}; ?>">
            <a href="#custom_fields" aria-controls="custom_fields" role="tab" data-toggle="tab">
            <?php echo do_action('customer_profile_tab_custom_fields_text',_l( 'custom_fields')); ?>
            </a>
         </li>
         <?php } ?>
         <li role="presentation">
            <a href="#billing_and_shipping" aria-controls="billing_and_shipping" role="tab" data-toggle="tab">
            <?php echo _l( 'billing_shipping'); ?>
            </a>
         </li>
         <?php do_action('after_customer_billing_and_shipping_tab',isset($client) ? $client : false); ?>
         <?php if(isset($client)){ ?>
         <li role="presentation">
            <a href="#customer_admins" aria-controls="customer_admins" role="tab" data-toggle="tab">
            <?php echo _l( 'customer_admins' ); ?>
            </a>
         </li>
         <?php do_action('after_customer_admins_tab',$client); ?>
         <?php } ?>
      </ul>
   </div>
</div>
      <div class="tab-content">
         <?php do_action('after_custom_profile_tab_content',isset($client) ? $client : false); ?>
         <?php if($customer_custom_fields) { ?>
         <div role="tabpanel" class="tab-pane <?php if($this->input->get('tab') == 'custom_fields'){echo ' active';}; ?>" id="custom_fields">
            <?php $rel_id=( isset($client) ? $client->userid : false); ?>
            <?php echo render_custom_fields( 'customers',$rel_id); ?>
         </div>
         <?php } ?>
         <div role="tabpanel" class="tab-pane<?php if(!$this->input->get('tab')){echo ' active';}; ?>" id="contact_info">
            <div class="row">
               <div class="col-md-12<?php if(isset($client) && (!is_empty_customer_company($client->userid) && total_rows('tblcontacts',array('userid'=>$client->userid,'is_primary'=>1)) > 0)) { echo ''; } else {echo ' hide';} ?>" id="client-show-primary-contact-wrapper">
                  <div class="checkbox checkbox-info mbot20 no-mtop">
                     <input type="checkbox" name="show_primary_contact"<?php if(isset($client) && $client->show_primary_contact == 1){echo ' checked';}?> value="1" id="show_primary_contact">
                     <label for="show_primary_contact"><?php echo _l('show_primary_contact',_l('invoices').', '._l('estimates').', '._l('payments').', '._l('credit_notes')); ?></label>
                  </div>
               </div>
               <div class="col-md-6">
                  <?php $value=( isset($client) ? $client->company : ''); ?>
                  <?php $attrs = (isset($client) ? array() : array('autofocus'=>true)); ?>
                  <?php echo render_input( 'company', 'client_company',$value,'text',$attrs); ?>
                  <?php if(get_option('company_requires_vat_number_field') == 1){
                     $value=( isset($client) ? $client->vat : '');
                     echo render_input( 'vat', 'client_vat_number',$value);
                     } ?>
                  <?php $value=( isset($client) ? $client->phonenumber : ''); ?>
                  <?php echo render_input( 'phonenumber', 'client_phonenumber',$value); ?>
                  <?php if((isset($client) && empty($client->website)) || !isset($client)){
                     $value=( isset($client) ? $client->website : '');
                     echo render_input( 'website', 'client_website',$value);
                     } else { ?>
                  <div class="form-group">
                     <label for="website"><?php echo _l('client_website'); ?></label>
                     <div class="input-group">
                        <input type="text" name="website" id="website" value="<?php echo $client->website; ?>" class="form-control">
                        <div class="input-group-addon">
                           <span><a href="<?php echo maybe_add_http($client->website); ?>" target="_blank" tabindex="-1"><i class="fa fa-globe"></i></a></span>
                        </div>
                     </div>
                  </div>
                  <?php }
                     $selected = array();
                     if(isset($customer_groups)){
                       foreach($customer_groups as $group){
                          array_push($selected,$group['groupid']);
                       }
                     }
                     if(is_admin() || get_option('staff_members_create_inline_customer_groups') == '1'){
                      echo render_select_with_input_group('groups_in[]',$groups,array('id','name'),'customer_groups',$selected,'<a href="#" data-toggle="modal" data-target="#customer_group_modal"><i class="fa fa-plus"></i></a>',array('multiple'=>true,'data-actions-box'=>true),array(),'','',false);
                      } else {
                        echo render_select('groups_in[]',$groups,array('id','name'),'customer_groups',$selected,array('multiple'=>true,'data-actions-box'=>true),array(),'','',false);
                      }
                     ?>
                  <?php if(!isset($client)){ ?>
                  <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('customer_currency_change_notice'); ?>"></i>
                  <?php }
                     $s_attrs = array('data-none-selected-text'=>_l('system_default_string'));
                     $selected = '';
                     if(isset($client) && client_have_transactions($client->userid)){
                        $s_attrs['disabled'] = true;
                     }
                     foreach($currencies as $currency){
                        if(isset($client)){
                          if($currency['id'] == $client->default_currency){
                            $selected = $currency['id'];
                         }
                      }
                     }
                            // Do not remove the currency field from the customer profile!
                     echo render_select('default_currency',$currencies,array('id','name','symbol'),'invoice_add_edit_currency',$selected,$s_attrs); ?>
                  <?php if(get_option('disable_language') == 0){ ?>
                  <div class="form-group select-placeholder">
                     <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?>
                     </label>
                     <select name="default_language" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value=""><?php echo _l('system_default_string'); ?></option>
                        <?php foreach(list_folders(APPPATH .'language') as $language){
                           $selected = '';
                           if(isset($client)){
                              if($client->default_language == $language){
                                 $selected = 'selected';
                              }
                           }
                           ?>
                        <option value="<?php echo $language; ?>" <?php echo $selected; ?>><?php echo ucfirst($language); ?></option>
                        <?php } ?>
                     </select>
                  </div>
                  <?php } ?>
               </div>
               <div class="col-md-6">
                  <?php $value=( isset($client) ? $client->address : ''); ?>
                  <?php echo render_textarea( 'address', 'client_address',$value); ?>
                  <?php $value=( isset($client) ? $client->city : ''); ?>
                  <?php echo render_input( 'city', 'client_city',$value); ?>
                  <?php $value=( isset($client) ? $client->state : ''); ?>
                  <?php echo render_input( 'state', 'client_state',$value); ?>
                  <?php $value=( isset($client) ? $client->zip : ''); ?>
                  <?php echo render_input( 'zip', 'client_postal_code',$value); ?>
                  <?php $countries= get_all_countries();
                     $customer_default_country = get_option('customer_default_country');
                     $selected =( isset($client) ? $client->country : $customer_default_country);
                     echo render_select( 'country',$countries,array( 'country_id',array( 'short_name')), 'clients_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex')));
                     ?>
               </div>
            </div>






<!-- Custom code for contact form starts here : prashant -->
<?php if(!isset($client)){ ?>
            <hr />

            <h4>Contact Details</h4>


                  <div class="row">
                    <div class="col-md-6">
                        <?php if(isset($contact)){ ?>
                        <img src="<?php echo contact_profile_image_url($contact->id,'thumb'); ?>" id="contact-img" class="client-profile-image-thumb">
                        <?php if(!empty($contact->profile_image)){ ?>
                        <a href="#" onclick="delete_contact_profile_image(<?php echo $contact->id; ?>); return false;" class="text-danger pull-right" id="contact-remove-img"><i class="fa fa-remove"></i></a>
                        <?php } ?>
                        <hr />
                        <?php } ?>
                        <div id="contact-profile-image" class="form-group<?php if(isset($contact) && !empty($contact->profile_image)){echo ' hide';} ?>">
                            <label for="profile_image" class="profile-image"><?php echo _l('client_profile_image'); ?></label>
                            <input type="file" name="profile_image" class="form-control" id="profile_image">
                        </div>
                        <?php if(isset($contact)){ ?>
                        <div class="alert alert-warning hide" role="alert" id="contact_proposal_warning">
                            <?php echo _l('proposal_warning_email_change',array(_l('contact_lowercase'),_l('contact_lowercase'),_l('contact_lowercase'))); ?>
                            <hr />
                            <a href="#" id="contact_update_proposals_emails" data-original-email="" onclick="update_all_proposal_emails_linked_to_contact(<?php echo $contact->id; ?>); return false;"><?php echo _l('update_proposal_email_yes'); ?></a>
                            <br />
                            <a href="#" onclick="close_modal_manually('#contact'); return false;"><?php echo _l('update_proposal_email_no'); ?></a>
                        </div>
                        <?php } ?>

                     </div>

                     <div class="col-md-6">
                        <!-- // For email exist check -->
                        <?php // echo form_hidden('contactid',$contactid); ?>
                        <?php $value=( isset($contact) ? $contact->firstname : '') ?>
                        <?php echo render_input( 'firstname', 'client_firstname',$value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value=( isset($contact) ? $contact->lastname : ''); ?>
                        <?php echo render_input( 'lastname', 'client_lastname',$value, 'text', array('required'=>false)); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value=( isset($contact) ? $contact->title : ''); ?>
                        <?php echo render_input( 'title', 'contact_position',$value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value=( isset($contact) ? $contact->email : ''); ?>
                        <?php echo render_input( 'email', 'client_email',$value, 'email'); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value=( isset($contact) ? $contact->phonenumber : ''); ?>
                        <?php echo render_input( 'phonenumber', 'client_phonenumber',$value,'text',array('autocomplete'=>'off')); ?> 



              <!-- Hide permission and email notifications item from the form starts -->
              <div class="hide">





                        <div class="form-group contact-direction-option">
                          <label for="direction"><?php echo _l('document_direction'); ?></label>
                          <select class="selectpicker" data-none-selected-text="<?php echo _l('system_default_string'); ?>" data-width="100%" name="direction" id="direction">
                            <option value="" <?php if(isset($contact) && empty($contact->direction)){echo 'selected';} ?>></option>
                            <option value="ltr" <?php if(isset($contact) && $contact->direction == 'ltr'){echo 'selected';} ?>>LTR</option>
                            <option value="rtl" <?php if(isset($contact) && $contact->direction == 'rtl'){echo 'selected';} ?>>RTL</option>
                        </select>
                  </div>
                    <?php $rel_id=( isset($contact) ? $contact->id : false); ?>
                    <?php echo render_custom_fields( 'contacts',$rel_id); ?>


                    <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                    <input  type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1" />
                    <input  type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1"/>

                    <div class="client_password_set_wrapper">
                        <label for="password" class="control-label">
                            <?php echo _l( 'client_password'); ?>
                        </label>
                        <div class="input-group">

                            <input type="password" class="form-control password" name="password" autocomplete="false" value="nOpassworD" >
                            <span class="input-group-addon">
                                <a href="#password" class="show_password" onclick="showPassword('password'); return false;"><i class="fa fa-eye"></i></a>
                            </span>
                            <span class="input-group-addon">
                                <a href="#" class="generate_password" onclick="generatePassword(this);return false;"><i class="fa fa-refresh"></i></a>
                            </span>
                        </div>
                        <?php if(isset($contact)){ ?>
                        <p class="text-muted">
                            <?php echo _l( 'client_password_change_populate_note'); ?>
                        </p>
                        <?php if($contact->last_password_change != NULL){
                            echo _l( 'client_password_last_changed');
                            echo '<span class="text-has-action" data-toggle="tooltip" data-title="'._dt($contact->last_password_change).'"> ' . time_ago($contact->last_password_change) . '</span>';
                        }
                    } ?>
                </div>
                <hr />
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="is_primary" id="contact_primary" <?php if((!isset($contact) && total_rows('tblcontacts',array('is_primary'=>1,'userid'=>$customer_id)) == 0) || (isset($contact) && $contact->is_primary == 1)){echo 'checked';}; ?> <?php if((isset($contact) && total_rows('tblcontacts',array('is_primary'=>1,'userid'=>$customer_id)) == 1 && $contact->is_primary == 1)){echo 'disabled';} ?>>
                    <label for="contact_primary">
                        <?php echo _l( 'contact_primary'); ?>
                    </label>
                </div>
                <?php if(!isset($contact) && total_rows('tblemailtemplates',array('slug'=>'new-client-created','active'=>0)) == 0){ ?>
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="donotsendwelcomeemail" id="donotsendwelcomeemail">
                    <label for="donotsendwelcomeemail">
                        <?php echo _l( 'client_do_not_send_welcome_email'); ?>
                    </label>
                </div>
                <?php } ?>
                <?php if(total_rows('tblemailtemplates',array('slug'=>'contact-set-password','active'=>0)) == 0){ ?>
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="send_set_password_email" id="send_set_password_email">
                    <label for="send_set_password_email">
                        <?php echo _l( 'client_send_set_password_email'); ?>
                    </label>
                </div>
                <?php } ?>
                <hr />

                <p class="bold"><?php echo _l('customer_permissions'); ?></p>
                <p class="text-danger"><?php echo _l('contact_permissions_info'); ?></p>
                <?php
                $default_contact_permissions = array();
                if(!isset($contact)){
                    $default_contact_permissions = @unserialize(get_option('default_contact_permissions'));
                }
                ?>
                <?php foreach($customer_permissions as $permission){ ?>
                <div class="col-md-6 row">
                    <div class="row">
                        <div class="col-md-6 mtop10 border-right">
                            <span><?php echo $permission['name']; ?></span>
                        </div>
                        <div class="col-md-6 mtop10">
                            <div class="onoffswitch">
                                <input type="checkbox" id="<?php echo $permission['id']; ?>" class="onoffswitch-checkbox" <?php if(isset($contact) && has_contact_permission($permission['short_name'],$contact->id) || is_array($default_contact_permissions) && in_array($permission['id'],$default_contact_permissions)){echo 'checked';} ?> value="<?php echo $permission['id']; ?>" name="permissions[]">
                                <label class="onoffswitch-label" for="<?php echo $permission['id']; ?>"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <?php } ?>
                 <hr />
                <p class="bold"><?php echo _l('email_notifications'); ?><?php if(is_sms_trigger_active()){echo '/SMS';} ?></p>
                <div id="contact_email_notifications">
                <div class="col-md-6 row">
                    <div class="row">
                        <div class="col-md-6 mtop10 border-right">
                            <span><?php echo _l('invoice'); ?></span>
                        </div>
                        <div class="col-md-6 mtop10">
                            <div class="onoffswitch">
                                <input type="checkbox" id="invoice_emails" data-perm-id="1" class="onoffswitch-checkbox" <?php if(isset($contact) && $contact->invoice_emails == '1'){echo 'checked';} ?>  value="invoice_emails" name="invoice_emails">
                                <label class="onoffswitch-label" for="invoice_emails"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 row">
                    <div class="row">
                        <div class="col-md-6 mtop10 border-right">
                            <span><?php echo _l('estimate'); ?></span>
                        </div>
                        <div class="col-md-6 mtop10">
                            <div class="onoffswitch">
                                <input type="checkbox" id="estimate_emails" data-perm-id="2" class="onoffswitch-checkbox" <?php if(isset($contact) && $contact->estimate_emails == '1'){echo 'checked';} ?>  value="estimate_emails" name="estimate_emails">
                                <label class="onoffswitch-label" for="estimate_emails"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 row">
                    <div class="row">
                        <div class="col-md-6 mtop10 border-right">
                            <span><?php echo _l('credit_note'); ?></span>
                        </div>
                        <div class="col-md-6 mtop10">
                            <div class="onoffswitch">
                                <input type="checkbox" id="credit_note_emails" data-perm-id="1" class="onoffswitch-checkbox" <?php if(isset($contact) && $contact->credit_note_emails == '1'){echo 'checked';} ?>  value="credit_note_emails" name="credit_note_emails">
                                <label class="onoffswitch-label" for="credit_note_emails"></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 row">
                    <div class="row">
                        <div class="col-md-6 mtop10 border-right">
                            <span><?php echo _l('project'); ?></span>
                        </div>
                        <div class="col-md-6 mtop10">
                            <div class="onoffswitch">
                                <input type="checkbox" id="project_emails" data-perm-id="6" class="onoffswitch-checkbox" <?php if(isset($contact) && $contact->project_emails == '1'){echo 'checked';} ?>  value="project_emails" name="project_emails">
                                <label class="onoffswitch-label" for="project_emails"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 row">
                    <div class="row">
                        <div class="col-md-6 mtop10 border-right">
                            <span><?php echo _l('tickets'); ?></span>
                        </div>
                        <div class="col-md-6 mtop10">
                            <div class="onoffswitch">
                                <input type="checkbox" id="ticket_emails" data-perm-id="5" class="onoffswitch-checkbox" <?php if(isset($contact) && $contact->ticket_emails == '1'){echo 'checked';} ?>  value="ticket_emails" name="ticket_emails">
                                <label class="onoffswitch-label" for="ticket_emails"></label>
                            </div>
                        </div>
                        <div class="col-md-6 mtop10 border-right">
                            <span><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('only_project_tasks'); ?>"></i> <?php echo _l('task'); ?></span>
                        </div>
                        <div class="col-md-6 mtop10">
                            <div class="onoffswitch">
                                <input type="checkbox" id="task_emails" data-perm-id="6" class="onoffswitch-checkbox" <?php if(isset($contact) && $contact->task_emails == '1'){echo 'checked';} ?>  value="task_emails" name="task_emails">
                                <label class="onoffswitch-label" for="task_emails"></label>
                            </div>
                        </div>

                    </div>
                </div>
                 <div class="col-md-6 row">
                    <div class="row">
                        <div class="col-md-6 mtop10 border-right">
                            <span><?php echo _l('contract'); ?></span>
                        </div>
                        <div class="col-md-6 mtop10">
                            <div class="onoffswitch">
                                <input type="checkbox" id="contract_emails" data-perm-id="3" class="onoffswitch-checkbox" <?php if(isset($contact) && $contact->contract_emails == '1'){echo 'checked';} ?>  value="contract_emails" name="contract_emails">
                                <label class="onoffswitch-label" for="contract_emails"></label>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

              </div>
              <!-- Hide permission and email notifications item from the form ends -->



            </div>
        </div>

<?php } ?> 
<!-- Custom code for contact form ends here : prashant -->








         </div>
         <?php if(isset($client)){ ?>
         <div role="tabpanel" class="tab-pane" id="customer_admins">
            <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
            <a href="#" data-toggle="modal" data-target="#customer_admins_assign" class="btn btn-info mbot30"><?php echo _l('assign_admin'); ?></a>
            <?php } ?>
            <table class="table dt-table">
               <thead>
                  <tr>
                     <th><?php echo _l('staff_member'); ?></th>
                     <th><?php echo _l('customer_admin_date_assigned'); ?></th>
                     <?php if(has_permission('customers','','create') || has_permission('customers','','edit')){ ?>
                     <th><?php echo _l('options'); ?></th>
                     <?php } ?>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach($customer_admins as $c_admin){ ?>
                  <tr>
                     <td><a href="<?php echo admin_url('profile/'.$c_admin['staff_id']); ?>">
                        <?php echo staff_profile_image($c_admin['staff_id'], array(
                           'staff-profile-image-small',
                           'mright5'
                           ));
                           echo get_staff_full_name($c_admin['staff_id']); ?></a>
                     </td>
                     <td data-order="<?php echo $c_admin['date_assigned']; ?>"><?php echo _dt($c_admin['date_assigned']); ?></td>
                     <?php if(has_permission('customers','','create') || has_permission('customers','','edit')){ ?>
                     <td>
                        <a href="<?php echo admin_url('clients/delete_customer_admin/'.$client->userid.'/'.$c_admin['staff_id']); ?>" class="btn btn-danger _delete btn-icon"><i class="fa fa-remove"></i></a>
                     </td>
                     <?php } ?>
                  </tr>
                  <?php } ?>
               </tbody>
            </table>
         </div>
         <?php } ?>
         <div role="tabpanel" class="tab-pane" id="billing_and_shipping">
            <div class="row">
               <div class="col-md-12">
                  <div class="row">
                     <div class="col-md-6">
                        <h4 class="no-mtop"><?php echo _l('billing_address'); ?> <a href="#" class="pull-right billing-same-as-customer"><small class="font-medium-xs"><?php echo _l('customer_billing_same_as_profile'); ?></small></a></h4>
                        <hr />
                        <?php $value=( isset($client) ? $client->billing_street : ''); ?>
                        <?php echo render_textarea( 'billing_street', 'billing_street',$value); ?>
                        <?php $value=( isset($client) ? $client->billing_city : ''); ?>
                        <?php echo render_input( 'billing_city', 'billing_city',$value); ?>
                        <?php $value=( isset($client) ? $client->billing_state : ''); ?>
                        <?php echo render_input( 'billing_state', 'billing_state',$value); ?>
                        <?php $value=( isset($client) ? $client->billing_zip : ''); ?>
                        <?php echo render_input( 'billing_zip', 'billing_zip',$value); ?>
                        <?php $selected=( isset($client) ? $client->billing_country : '' ); ?>
                        <?php echo render_select( 'billing_country',$countries,array( 'country_id',array( 'short_name')), 'billing_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex'))); ?>
                     </div>
                     <div class="col-md-6">
                        <h4 class="no-mtop">
                           <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('customer_shipping_address_notice'); ?>"></i>
                           <?php echo _l('shipping_address'); ?> <a href="#" class="pull-right customer-copy-billing-address"><small class="font-medium-xs"><?php echo _l('customer_billing_copy'); ?></small></a>
                        </h4>
                        <hr />
                        <?php $value=( isset($client) ? $client->shipping_street : ''); ?>
                        <?php echo render_textarea( 'shipping_street', 'shipping_street',$value); ?>
                        <?php $value=( isset($client) ? $client->shipping_city : ''); ?>
                        <?php echo render_input( 'shipping_city', 'shipping_city',$value); ?>
                        <?php $value=( isset($client) ? $client->shipping_state : ''); ?>
                        <?php echo render_input( 'shipping_state', 'shipping_state',$value); ?>
                        <?php $value=( isset($client) ? $client->shipping_zip : ''); ?>
                        <?php echo render_input( 'shipping_zip', 'shipping_zip',$value); ?>
                        <?php $selected=( isset($client) ? $client->shipping_country : '' ); ?>
                        <?php echo render_select( 'shipping_country',$countries,array( 'country_id',array( 'short_name')), 'shipping_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex'))); ?>
                     </div>
                     <?php if(isset($client) &&
                        (total_rows('tblinvoices',array('clientid'=>$client->userid)) > 0 || total_rows('tblestimates',array('clientid'=>$client->userid)) > 0 || total_rows('tblcreditnotes',array('clientid'=>$client->userid)) > 0)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-warning">
                           <div class="checkbox checkbox-default">
                              <input type="checkbox" name="update_all_other_transactions" id="update_all_other_transactions">
                              <label for="update_all_other_transactions">
                              <?php echo _l('customer_update_address_info_on_invoices'); ?><br />
                              </label>
                           </div>
                           <b><?php echo _l('customer_update_address_info_on_invoices_help'); ?></b>
                           <div class="checkbox checkbox-default">
                              <input type="checkbox" name="update_credit_notes" id="update_credit_notes">
                              <label for="update_credit_notes">
                              <?php echo _l('customer_profile_update_credit_notes'); ?><br />
                              </label>
                           </div>
                        </div>
                     </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php echo form_close(); ?>
</div>
<?php if(isset($client)){ ?>
<?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
<div class="modal fade" id="customer_admins_assign" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <?php echo form_open(admin_url('clients/assign_admins/'.$client->userid)); ?>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('assign_admin'); ?></h4>
         </div>
         <div class="modal-body">
            <?php
               $selected = array();
               foreach($customer_admins as $c_admin){
                  array_push($selected,$c_admin['staff_id']);
               }
               echo render_select('customer_admins[]',$staff,array('staffid',array('firstname','lastname')),'',$selected,array('multiple'=>true),array(),'','',false); ?>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
         </div>
      </div>
      <!-- /.modal-content -->
      <?php echo form_close(); ?>
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php } ?>
<?php } ?>
<?php $this->load->view('admin/clients/client_group'); ?>
