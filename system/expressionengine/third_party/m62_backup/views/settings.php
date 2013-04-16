<?php $this->load->view('errors'); ?>
<?php 

$tmpl = array (
	'table_open'          => '<table class="mainTable" border="0" cellspacing="0" cellpadding="0">',

	'row_start'           => '<tr class="even">',
	'row_end'             => '</tr>',
	'cell_start'          => '<td style="width:50%;">',
	'cell_end'            => '</td>',

	'row_alt_start'       => '<tr class="odd">',
	'row_alt_end'         => '</tr>',
	'cell_alt_start'      => '<td>',
	'cell_alt_end'        => '</td>',

	'table_close'         => '</table>'
);

$this->table->set_template($tmpl); 
$this->table->set_empty("&nbsp;");
?>
<div class="clear_left shun"></div>

<?php echo form_open($query_base.'settings', array('id'=>'my_accordion'))?>
<input type="hidden" value="yes" name="go_settings" />
<h3  class="accordion"><?=lang('configure_backups')?></h3>
<div>
	<?php 
	$settings['exclude_paths'] = (is_array($settings['exclude_paths']) ? implode("\n", $settings['exclude_paths']) : $settings['exclude_paths']);
	$settings['backup_file_location'] = (is_array($settings['backup_file_location']) ? implode("\n", $settings['backup_file_location']) : $settings['backup_file_location']);
	$this->table->set_heading('&nbsp;',' ');
	$this->table->add_row('<label for="backup_file_location">'.lang('backup_file_locations').'</label><div class="subtext">'.lang('backup_file_location_instructions').'</div>', form_textarea('backup_file_location', $settings['backup_file_location'], 'id="backup_file_location" cols="90" rows="6"'. $settings_disable));
	$this->table->add_row('<label for="backup_store_location">'.lang('backup_store_location').'</label><div class="subtext">'.lang('backup_store_location_instructions').'</div>', form_input('backup_store_location', $settings['backup_store_location'], 'id="backup_store_location"'. $settings_disable));
	$this->table->add_row('<label for="allowed_access_levels">'.lang('allowed_access_levels').'</label><div class="subtext">'.lang('allowed_access_levels_instructions').'</div>', form_multiselect('allowed_access_levels[]', $member_groups, $settings['allowed_access_levels'], $settings_disable));
	$this->table->add_row('<label for="max_db_backups">'.lang('max_db_backups').' </label><div class="subtext">'.lang('max_db_backups_instructions').'</div>', form_input('max_db_backups', $settings['max_db_backups'], 'id="max_db_backups"' . $settings_disable));
	$this->table->add_row('<label for="max_file_backups">'.lang('max_file_backups').' </label><div class="subtext">'.lang('max_file_backups_instructions').'</div>', form_input('max_file_backups', $settings['max_file_backups'], 'id="max_file_backups"' . $settings_disable));
	$this->table->add_row('<label for="auto_threshold">'.lang('auto_threshold').' <!--('.$total_space_used.')--></label><div class="subtext">'.lang('auto_threshold_instructions').'</div>', form_dropdown('auto_threshold', $threshold_options, $settings['auto_threshold'], 'id="auto_threshold"' . $settings_disable).form_input('auto_threshold_custom', $settings['auto_threshold_custom'], 'id="auto_threshold_custom" style="display:none; width:40%; margin-left:10px;"'));
	$this->table->add_row('<label for="date_format">'.lang('date_format').'</label><div class="subtext">'.lang('date_format_instructions').'</div>', form_input('date_format', $settings['date_format'], 'id="date_format"'. $settings_disable));
	$this->table->add_row('<label for="exclude_paths">'.lang('exclude_paths').'</label><div class="subtext">'.lang('exclude_paths_instructions').'</div>', form_textarea('exclude_paths', $settings['exclude_paths'], 'cols="90" rows="6" id="exclude_paths"'. $settings_disable));
	echo $this->table->generate();
	$this->table->clear();	
	?>
</div>

<h3  class="accordion"><?=lang('configure_cron')?></h3>
<div>
	<?php 
	$settings['cron_notify_emails'] = (is_array($settings['cron_notify_emails']) ? implode("\n", $settings['cron_notify_emails']) : $settings['cron_notify_emails']);
	$this->table->set_heading('&nbsp;',' ');
	
		
	$this->table->add_row('<label for="cron_notify_emails">'.lang('cron_notify_emails').'</label><div class="subtext">'.lang('cron_notify_emails_instructions').'</div>', form_textarea('cron_notify_emails', $settings['cron_notify_emails'], 'id="cron_notify_emails"'. $settings_disable));
	$this->table->add_row('<label for="cron_attach_backups">'.lang('cron_attach_backups').'</label><div class="subtext">'.lang('cron_attach_backups_instructions').'</div>', form_checkbox('cron_attach_backups', '1', $settings['cron_attach_backups'], 'id="cron_attach_backups"'. $settings_disable));
	$this->table->add_row('<label for="cron_attach_threshold">'.lang('cron_attach_threshold').'</label><div class="subtext">'.lang('cron_attach_threshold_instructions').'</div>', form_input('cron_attach_threshold', $settings['cron_attach_threshold'], 'id="cron_attach_threshold"'. $settings_disable));
	
	echo $this->table->generate();
	$this->table->clear();	
	
	echo '<strong>'.lang('cron_command_instructions').'</strong>';
	if(count($cron_commands) >= 1)
	{
		$this->table->set_heading('Backup Type','Cron Commands');
		foreach($cron_commands AS $key => $value)
		{
			$this->table->add_row(lang($key), $value);
		}
		echo $this->table->generate();
		$this->table->clear();	
	}	
	//
	?>
</div>
<h3  class="accordion"><?=lang('configure_ftp')?></h3>
<div>
	<?php 
	$this->table->set_heading('&nbsp;',' ');
	
	$this->table->add_row('<label for="ftp_hostname">'.lang('ftp_hostname').'</label><div class="subtext">'.lang('ftp_hostname_instructions').'</div>', form_input('ftp_hostname', $settings['ftp_hostname'], 'id="ftp_hostname"'. $settings_disable));
	$this->table->add_row('<label for="ftp_username">'.lang('ftp_username').'</label><div class="subtext">'.lang('ftp_username_instructions').'</div>', form_input('ftp_username', $settings['ftp_username'], 'id="ftp_username"'. $settings_disable));
	$this->table->add_row('<label for="ftp_password">'.lang('ftp_password').'</label><div class="subtext">'.lang('ftp_password_instructions').'</div>', form_password('ftp_password', $settings['ftp_password'], 'id="ftp_password"'. $settings_disable));
	$this->table->add_row('<label for="ftp_port">'.lang('ftp_port').'</label><div class="subtext">'.lang('ftp_port_instructions').'</div>', form_input('ftp_port', $settings['ftp_port'], 'id="ftp_port"'. $settings_disable));
	$this->table->add_row('<label for="ftp_passive">'.lang('ftp_passive').'</label><div class="subtext">'.lang('ftp_passive_instructions').'</div>', form_checkbox('ftp_passive', '1', $settings['ftp_passive'], 'id="ftp_passive"'. $settings_disable));
	$this->table->add_row('<label for="ftp_store_location">'.lang('ftp_store_location').'</label><div class="subtext">'.lang('ftp_store_location_instructions').'</div>', form_input('ftp_store_location', $settings['ftp_store_location'], 'id="ftp_store_location"'. $settings_disable));
	
	echo $this->table->generate();
	$this->table->clear();	
	?>
</div>

<h3  class="accordion"><?=lang('configure_s3')?></h3>
<div>
	<?php 
	$this->table->set_heading('&nbsp;',' ');
		
	$this->table->add_row('<label for="s3_access_key">'.lang('s3_access_key').'</label><div class="subtext">'.lang('s3_access_key_instructions').'</div>', form_input('s3_access_key', $settings['s3_access_key'], 'id="s3_access_key"'. $settings_disable));
	$this->table->add_row('<label for="s3_secret_key">'.lang('s3_secret_key').'</label><div class="subtext">'.lang('s3_secret_key_instructions').'</div>', form_password('s3_secret_key', $settings['s3_secret_key'], 'id="s3_secret_key"'. $settings_disable));
	$this->table->add_row('<label for="s3_bucket">'.lang('s3_bucket').'</label><div class="subtext">'.lang('s3_bucket_instructions').'</div>', form_input('s3_bucket', $settings['s3_bucket'], 'id="s3_bucket"'. $settings_disable));
	
	echo $this->table->generate();
	$this->table->clear();	
	?>
</div>

<h3  class="accordion"><?=lang('configure_cf')?></h3>
<div>
	<?php 
	$cf_location_options = array('us' => 'US', 'uk' => 'UK');
	$this->table->set_heading('&nbsp;',' ');
	
	$this->table->add_row('<label for="cf_username">'.lang('cf_username').'</label><div class="subtext">'.lang('cf_username_instructions').'</div>', form_input('cf_username', $settings['cf_username'], 'id="cf_username"'. $settings_disable));
	$this->table->add_row('<label for="cf_api">'.lang('cf_api').'</label><div class="subtext">'.lang('cf_api_instructions').'</div>', form_password('cf_api', $settings['cf_api'], 'id="cf_api"'. $settings_disable));
	$this->table->add_row('<label for="cf_bucket">'.lang('cf_bucket').'</label><div class="subtext">'.lang('cf_bucket_instructions').'</div>', form_input('cf_bucket', $settings['cf_bucket'], 'id="cf_bucket"'. $settings_disable));
	$this->table->add_row('<label for="cf_location">'.lang('cf_location').'</label><div class="subtext">'.lang('cf_location_instructions').'</div>', form_dropdown('cf_location', $cf_location_options, $settings['cf_location'], 'id="cf_location"'. $settings_disable));
	
	echo $this->table->generate();
	$this->table->clear();	
	?>
</div>

<h3  class="accordion"><?php echo lang('license_number')?></h3>
<div>
	<?php 
	$this->table->set_heading('&nbsp;',' ');
	$this->table->add_row('<label for="license_number">'.lang('license_number').'</label>', form_input('license_number', $settings['license_number'], 'id="license_number"'. $settings_disable));
	echo $this->table->generate();
	$this->table->clear();	
	?>
</div>
<br />
<div class="tableFooter">
	<div class="tableSubmit">
		<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'));?>
	</div>
</div>	
<?php echo form_close()?>