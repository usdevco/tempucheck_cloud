<div class="row">

	<?php 
	if($temp_data)
	{ $i = 0; ?>
		<div class="col-md-12">
			<table class="table table-bordered">
			  	<thead>
				    <tr>
				      	<th scope="col">No</th>
				      	<th scope="col">State</th>
				      	<th scope="col">Reading Qty</th>
				      	<th scope="col">Temp</th>
				      	<th scope="col">Date Or Time</th>
				    </tr>
			  	</thead>			
			  	<?php
				foreach ($temp_data as $temp_value) 
				{ $i++;	?>
				  	<tbody><?php
				  		if($temp_value->pdf_file)
				  		{ ?>
				  			<tr>
							    <td scope="row"><?php echo $i; ?></td>
							    <td ><a target="_blank" href="<?php echo $temp_value->pdf_file; ?>" class="btn btn-link"><?php echo  basename($temp_value->pdf_file, '/pdf/');?></a></td>
							    <td colspan="3"> <a href="<?php echo base_url('clients/download/').$temp_value->id; ?>" class="btn btn-link">Download</a></td>
						    </tr> <?php

				  		}
				  		else
				  		{?>
						    <tr>
							    <td scope="row"><?php echo $i; ?></td>
							    <td ><?php echo $temp_value->state ? $temp_value->state : '-'; ?></td>
							    <td><?php echo $temp_value->reading_qty > 0 ? $temp_value->reading_qty : '-'; ?></td>
							    <td><?php echo $temp_value->temp ? $temp_value->temp.'°C Avg' : '-'; ?></td>
							    <td><?php echo  date("D d-M-Y", strtotime($temp_value->added)); ?></td>
						    </tr> <?php
					    } ?>
				  	</tbody>				
				  	<?php		
				} ?>
			</table>
		 </div>
		<?php	
	} ?>

	<div class="col-md-12">
		<?php if(has_contact_permission('projects')) 
		{ ?>
			<div class="panel_s">
				<div class="panel-body">
				<h3 class="text-success no-mtop"><?php echo _l('projects_summary'); ?></h3>
				<div class="row">
					<?php get_template_part('projects/project_summary'); ?>
				</div>
				</div>
			</div>
			<?php 
		} ?>
		<div class="panel_s">
			<?php
			if(has_contact_permission('invoices'))
			{ ?>
				<div class="panel-body">
					<p class="bold"><?php echo _l('clients_quick_invoice_info'); ?></p>
					<?php if(has_contact_permission('invoices'))
					{ ?>
						<a href="<?php echo site_url('clients/statement'); ?>"><?php echo _l('view_account_statement'); ?></a>
						<?php 
					} ?>
					<hr />
					<?php get_template_part('invoices_stats'); ?>
					<hr />

					<div class="row">
						<div class="col-md-3">
							<?php if(count($payments_years) > 0)
							{ ?>
								<div class="form-group">
									<select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" class="form-control" id="payments_year" name="payments_years" data-width="100%" onchange="total_income_bar_report();" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
										<?php foreach($payments_years as $year) { ?>
											<option value="<?php echo $year['year']; ?>"<?php if($year['year'] == date('Y')){echo 'selected';} ?>>
												<?php echo $year['year']; ?>
											</option>
											<?php } ?>
										</select>
									</div>
									<?php 
							} 

							if(is_client_using_multiple_currencies())
							{ ?>
								<div id="currency" class="form-group mtop15" data-toggle="tooltip" title="<?php echo _l('clients_home_currency_select_tooltip'); ?>">
									<select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" class="form-control" name="currency">
										<?php foreach($currencies as $currency){
											$selected = '';
											if($currency['isdefault'] == 1){
												$selected = 'selected';
											}
											?>
											<option value="<?php echo $currency['id']; ?>" <?php echo $selected; ?>><?php echo $currency['symbol']; ?> - <?php echo $currency['name']; ?></option>
											<?php } ?>
										</select>
									</div>
									<?php 
							} ?>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="relative" style="max-height:400px;">
								<canvas id="client-home-chart" height="400" class="animated fadeIn"></canvas>
							</div>
						</div>
					</div>
				</div>
				<?php 
			} ?>
		</div>
	</div>
</div>