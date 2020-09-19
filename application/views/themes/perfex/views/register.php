<div class="col-md-4 col-md-offset-4 text-center mbot15">
    <h1 class="text-uppercase"><?php echo _l('clients_register_heading'); ?></h1>
</div>
<div class="col-md-10 col-md-offset-1">
    <?php echo form_open('clients/register'); ?>
    <div class="panel_s">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12 mbot30">
                            <h2 class="bold"><?php echo _l('client_register_contact_info'); ?></h2>
                        </div>
                        <div class="clearfix "></div>

                        <div class="col-md-12 ">
                            <div class="form-group">
                                <label class="control-label" for="firstname"><?php echo _l('clients_firstname'); ?></label>
                                <input type="text" class="form-control" name="firstname" id="firstname" value="<?php echo set_value('firstname'); ?>">
                                <?php echo form_error('firstname'); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="lastname"><?php echo _l('clients_lastname'); ?></label>
                                <input type="text" class="form-control" name="lastname" id="lastname" value="<?php echo set_value('lastname'); ?>">
                                <?php echo form_error('lastname'); ?>
                            </div>
                        </div>
                        
                        

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="email"><?php echo _l('clients_email'); ?></label>
                                <input type="text" class="form-control" name="email" id="email" value="<?php echo set_value('email'); ?>">
                                <?php echo form_error('email'); ?>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="password"><?php echo _l('clients_register_password'); ?></label>
                                <input type="password" class="form-control" name="password" id="password">
                                <?php echo form_error('password'); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="passwordr"><?php echo _l('clients_register_password_repeat'); ?></label>
                                <input type="password" class="form-control" name="passwordr" id="passwordr">
                                <?php echo form_error('passwordr'); ?>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                            <?php echo render_custom_fields( 'contacts','',array('show_on_client_portal'=>1)); ?>
                        

                        <?php 
                        if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions') == 1) 
                        { ?>
                            <div class="col-md-12">
                                <div class="text-center">
                                   <div class="checkbox">
                                        <input type="checkbox" name="accept_terms_and_conditions" id="accept_terms_and_conditions" <?php echo set_checkbox('accept_terms_and_conditions', 'on'); ?>>
                                        <label for="accept_terms_and_conditions">
                                            <?php echo _l('gdpr_terms_agree', terms_url()); ?>
                                        </label>
                                    </div>
                                    <?php echo form_error('accept_terms_and_conditions'); ?>
                                </div>
                            </div>
                            <?php 
                        } ?>
                        <?php if(get_option('use_recaptcha_customers_area') == 1 && get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != ''){ ?>
                        <div class="col-md-12">
                           <div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_site_key'); ?>"></div>
                           <?php echo form_error('g-recaptcha-response'); ?>
                       </div>
                       <?php } ?>
                    </div>
                </div>
                <div class="col-md-3"></div>

                <div class="col-md-12 text-center">
                    <div class="form-group mtop15">
                        <button type="submit" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-info"><?php echo _l('clients_register_string'); ?></button>
                    </div>
                </div>
                </div>
        </div>
    </div>

<?php echo form_close(); ?>
</div>
