<?php echo $this->view('menu'); ?>
<div id="ubody">

<?php if ($this->session->userdata['group_id'] != 1):?>
<p style="padding:20px 20px 0"><strong><?=lang('only_super_admins')?></strong></p>
<?php else:?>


<?=form_open($base_url_short.AMP.'method=update_settings', array('enctype' => 'multipart/form-data', 'method'=>'POST'));?>

<div class="utable">
<h2>
	<?=lang('transfer_method')?>
	<a href="#" data-type="ftp" class="test_file_transfer"><?=lang('test_settings')?></a>
	<span class="options" id="file_transfer_options">
		<?php if (isset($override_settings['file_transfer_method']) == TRUE):?>
		<input name="settings[file_transfer_method]" disabled="disabled" type="radio" value="local" <?php if ($override_settings['file_transfer_method'] == 'local') echo 'checked'?>> <?=lang('local')?>&nbsp;&nbsp;
		<input name="settings[file_transfer_method]" disabled="disabled" type="radio" value="ftp" <?php if ($override_settings['file_transfer_method'] == 'ftp') echo 'checked'?>> <?=lang('ftp')?>&nbsp;&nbsp;
		<input name="settings[file_transfer_method]" disabled="disabled" type="radio" value="sftp" <?php if ($override_settings['file_transfer_method'] == 'sftp') echo 'checked'?>> <?=lang('sftp')?>&nbsp;&nbsp;
		<?php else:?>
		<input name="settings[file_transfer_method]" type="radio" value="local" <?php if ($settings['file_transfer_method'] == 'local') echo 'checked'?>> <?=lang('local')?>&nbsp;&nbsp;
		<input name="settings[file_transfer_method]" type="radio" value="ftp" <?php if ($settings['file_transfer_method'] == 'ftp') echo 'checked'?>> <?=lang('ftp')?>&nbsp;&nbsp;
		<input name="settings[file_transfer_method]" type="radio" value="sftp" <?php if ($settings['file_transfer_method'] == 'sftp') echo 'checked'?>> <?=lang('sftp')?>&nbsp;&nbsp;
		<?php endif;?>
	</span>
</h2>
<table class="file_transfer_methods">
	<thead>
		<tr class="heading">
			<th colspan="2" class="ftp"><?=lang('ftp')?> <?=lang('settings')?></th>
			<th colspan="2" class="sftp"><?=lang('sftp')?> <?=lang('settings')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="2" class="subtable ftp">
				<table>
					<tr>
						<td style="width:120px">
							<label><?=lang('hostname')?></label>
						</td>
						<td>
							<?php if (isset($override_settings['ftp']['hostname']) == TRUE):?>
							<?=form_input('settings[ftp][hostname]', $override_settings['ftp']['hostname'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[ftp][hostname]', $settings['ftp']['hostname'])?>
							<?php endif;?>
						</td>
						<td style="width:60px;text-align:right"><label><?=lang('port')?></label></td>
						<td>
							<?php if (isset($override_settings['ftp']['port']) == TRUE):?>
							<?=form_input('settings[ftp][port]', $override_settings['ftp']['port'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[ftp][port]', $settings['ftp']['port'])?>
							<?php endif;?>
						</td>
					</tr>
				</table>
			</td>
			<td colspan="2" class="subtable sftp">
				<table>
					<tr>
						<td style="width:120px"><label><?=lang('hostname')?></label></td>
						<td>
							<?php if (isset($override_settings['sftp']['hostname']) == TRUE):?>
							<?=form_input('settings[sftp][hostname]', $override_settings['sftp']['hostname'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[sftp][hostname]', $settings['sftp']['hostname'])?>
							<?php endif;?>
						</td>
						<td style="width:60px;text-align:right"><label><?=lang('port')?></label></td>
						<td>
							<?php if (isset($override_settings['sftp']['port']) == TRUE):?>
							<?=form_input('settings[sftp][port]', $override_settings['sftp']['port'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[sftp][port]', $settings['sftp']['port'])?>
							<?php endif;?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td style="width:120px" class="ftp"><label><?=lang('username')?></label></td>
			<td class="ftp">
				<?php if (isset($override_settings['ftp']['username']) == TRUE):?>
				<?=form_input('settings[ftp][username]', $override_settings['ftp']['username'], 'disabled="disabled"')?>
				<?php else:?>
				<?=form_input('settings[ftp][username]', $settings['ftp']['username'])?>
				<?php endif;?>
			</td>
			<td style="width:120px" class="sftp"><label><?=lang('username')?></label></td>
			<td class="sftp">
				<?php if (isset($override_settings['sftp']['username']) == TRUE):?>
				<?=form_input('settings[sftp][username]', $override_settings['sftp']['username'], 'disabled="disabled"')?>
				<?php else:?>
				<?=form_input('settings[sftp][username]', $settings['sftp']['username'])?>
				<?php endif;?>
			</td>
		</tr>
		<tr>
			<td style="width:120px" class="ftp"><label><?=lang('password')?></label></td>
			<td class="ftp">
				<?php if (isset($override_settings['ftp']['password']) == TRUE):?>
				<?=form_password('settings[ftp][password]', $override_settings['ftp']['password'], 'disabled="disabled"')?>
				<?php else:?>
				<?=form_password('settings[ftp][password]', $settings['ftp']['password'])?>
				<?php endif;?>
			</td>
			<td style="width:120px" class="sftp"><label><?=lang('password')?></label></td>
			<td class="sftp">
				<?php if (isset($override_settings['sftp']['password']) == TRUE):?>
				<?=form_password('settings[sftp][password]', $override_settings['sftp']['password'], 'disabled="disabled"')?>
				<?php else:?>
				<?=form_password('settings[sftp][password]', $settings['sftp']['password'])?>
				<?php endif;?>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="subtable ftp">
				<table>
					<tr>
						<td style="width:120px"><label><?=lang('passive')?></label></td>
						<td>
							<?php if (isset($override_settings['ftp']['passive']) == TRUE):?>
							<input name="settings[ftp][passive]" disabled="disabled" type="radio" value="yes" <?php if ($override_settings['ftp']['passive'] == 'yes') echo 'checked'?>> <?=lang('yes')?>&nbsp;&nbsp;
							<input name="settings[ftp][passive]" disabled="disabled" type="radio" value="no" <?php if ($override_settings['ftp']['passive'] == 'no') echo 'checked'?>> <?=lang('no')?>
							<?php else:?>
							<input name="settings[ftp][passive]" type="radio" value="yes" <?php if ($settings['ftp']['passive'] == 'yes') echo 'checked'?>> <?=lang('yes')?>&nbsp;&nbsp;
							<input name="settings[ftp][passive]" type="radio" value="no" <?php if ($settings['ftp']['passive'] == 'no') echo 'checked'?>> <?=lang('no')?>
							<?php endif;?>
						</td>
						<td style="width:60px;text-align:right"><label><?=lang('ssl')?></label></td>
						<td>
							<?php if (isset($override_settings['ftp']['ssl']) == TRUE):?>
							<input name="settings[ftp][ssl]" disabled="disabled" type="radio" value="yes" <?php if ($override_settings['ftp']['ssl'] == 'yes') echo 'checked'?>> <?=lang('yes')?>&nbsp;&nbsp;
							<input name="settings[ftp][ssl]" disabled="disabled" type="radio" value="no" <?php if ($override_settings['ftp']['ssl'] == 'no') echo 'checked'?>> <?=lang('no')?>
							<?php else:?>
							<input name="settings[ftp][ssl]" type="radio" value="yes" <?php if ($settings['ftp']['ssl'] == 'yes') echo 'checked'?>> <?=lang('yes')?>&nbsp;&nbsp;
							<input name="settings[ftp][ssl]" type="radio" value="no" <?php if ($settings['ftp']['ssl'] == 'no') echo 'checked'?>> <?=lang('no')?>
							<?php endif;?>
						</td>
					</tr>
				</table>
			</td>
			<td colspan="2"></td>
		</tr>
	</tbody>
</table>
</div>

<div class="utable">
<h2>
	<?=lang('path_map')?>
	<small><?=lang('path_map_exp')?></small>
</h2>
<table>
	<tbody>
		<tr>
			<td colspan="2" class="subtable">
				<table>
					<tr>
						<td style="width:150px"><label><?=lang('dir:root')?></label></td>
						<td>
							<?php if (isset($override_settings['path_map']['root']) == TRUE):?>
							<?=form_input('settings[path_map][root]', $override_settings['path_map']['root'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[path_map][root]', $settings['path_map']['root'], ' class="path__root" ')?>
							<?php endif;?>
						</td>
						<td style="width:150px"><label><?=lang('dir:backup')?></label></td>
						<td>
							<?php if (isset($override_settings['path_map']['backup']) == TRUE):?>
							<?=form_input('settings[path_map][backup]', $override_settings['path_map']['backup'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[path_map][backup]', $settings['path_map']['backup'], ' class="path__backup" ')?>
							<?php endif;?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="subtable">
				<table>
					<tr>
						<td style="width:150px"><label><?=lang('dir:system')?></label></td>
						<td>
							<?php if (isset($override_settings['path_map']['system']) == TRUE):?>
							<?=form_input('settings[path_map][system]', $override_settings['path_map']['system'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[path_map][system]', $settings['path_map']['system'], ' class="path__system" ')?>
							<?php endif;?>
						</td>
						<td style="width:150px"><label><?=lang('dir:system_third_party')?></label></td>
						<td>
							<?php if (isset($override_settings['path_map']['system_third_party']) == TRUE):?>
							<?=form_input('settings[path_map][system_third_party]', $override_settings['path_map']['system_third_party'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[path_map][system_third_party]', $settings['path_map']['system_third_party'], ' class="path__system_third_party" ')?>
							<?php endif;?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="subtable">
				<table>
					<tr>
						<td style="width:150px"><label><?=lang('dir:themes')?></label></td>
						<td>
							<?php if (isset($override_settings['path_map']['themes']) == TRUE):?>
							<?=form_input('settings[path_map][themes]', $override_settings['path_map']['themes'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[path_map][themes]', $settings['path_map']['themes'], ' class="path__themes" ')?>
							<?php endif;?>
						</td>
						<td style="width:150px"><label><?=lang('dir:themes_third_party')?></label></td>
						<td>
							<?php if (isset($override_settings['path_map']['themes_third_party']) == TRUE):?>
							<?=form_input('settings[path_map][themes_third_party]', $override_settings['path_map']['themes_third_party'], 'disabled="disabled"')?>
							<?php else:?>
							<?=form_input('settings[path_map][themes_third_party]', $settings['path_map']['themes_third_party'], ' class="path__themes_third_party" ')?>
							<?php endif;?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
</div>

<div class="utable">
<h2><input type="submit" class="submit" id="process_ee" value="<?=lang('update_settings')?>"></h2>
</div>



<br clear="all">



<?=form_close();?>
</div> <!-- #ubody -->


<?php endif;?>



<?php echo $this->view('footer'); ?>
