<?php
	echo form_open('C=addons_extensions'.AMP.'M=save_extension_settings', array('id'=>'extension_prefs'), array('file' => 'missing_link'));

	if (version_compare(APP_VER, '2.4.0', '<'))
	{
		$this->table->set_template($cp_table_template);
	}

	$this->table->template['thead_open'] = '<thead class="visualEscapism">';

	$this->table->set_caption(lang('enable_disable_links'));

	$this->table->set_heading('Preference', 'Setting');

	$this->table->add_row(array('colspan' => '2', 'class' => 'infoCell', 'data' => lang('controls_toggle_info')));

	foreach ($enable_links as $section => $settings)
	{
		$this->table->add_row(array(
			'data' => lang($section),
			'colspan' => 2,
			'class' => 'table_subhead'
		));

		foreach ($settings as $item)
		{
			$preference = lang(lang($item['name']), $item['name']);

			if (! empty($item['info']))
			{
				$preference .= '<span class="label_note">' . lang($item['info']) . '</span>';
			}

			switch($item['type'])
			{
				case 'checkbox':

					$controls = lang('enabled', $item['name'].'_y').NBS.
								form_radio(array(
									'name'		=> $item['name'],
									'id'		=> $item['name'].'_y',
									'value'		=> 'y',
									'checked'	=> ($item['value'] == 'y') ? TRUE : FALSE)
								).
								NBS.NBS.NBS.NBS.NBS;

					$controls .=	lang('disabled', $item['name'].'_n').NBS.
									form_radio(array(
										'name'		=> $item['name'],
										'id'		=> $item['name'].'_n',
										'value'		=> 'n',
										'checked'	=> ($item['value'] == 'n') ? TRUE : FALSE)
									);
				break;

				case 'dropdown':
					$controls = form_dropdown($item['name'], $item['options'], $item['value'], 'id="'.$item['name'].'"');
				break;
			}

			$this->table->add_row($preference, array('style'=> 'width:50%;', 'data'=>$controls));
		}
	}

	echo $this->table->generate();
	$this->table->clear();

	$this->table->set_caption(lang('compatibility_settings'));

	$this->table->set_heading('Preference', 'Setting');

	$this->table->add_row(array('colspan' => '2', 'class' => 'infoCell', 'data' => lang('compatibility_info')));

	foreach ($compatibility as $item => $value)
	{
		$preference = lang($item, $item);

		$controls = lang('enabled', $item.'_y').NBS.
					form_radio(array(
								'name'		=> $item,
								'id'		=> $item.'_y',
								'value'		=> 'y',
								'checked'	=> ($value == 'y') ? TRUE : FALSE)).
					NBS.NBS.NBS.NBS.NBS;

		$controls .=	lang('disabled', $item.'_n').NBS.
						form_radio(array(
								'name'		=> $item,
								'id'		=> $item.'_n',
								'value'		=> 'n',
								'checked'	=> ($value == 'n') ? TRUE : FALSE));

		$this->table->add_row($preference, array('style'=> 'width:50%;', 'data'=>$controls));
	}

	echo $this->table->generate();
	$this->table->clear();

	$this->table->set_caption(lang('extras'));

	$this->table->set_heading('Preference', 'Setting');

	$preference = lang(lang('action_monitoring'), 'action_monitoring').BR;
	$preference .= '<span class="label_note">' . lang('action_monitoring_info') . '</span>';

	$controls = form_dropdown('action_monitoring', $monitoring['options'], $monitoring['value'], 'id="action_monitoring"');
	$this->table->add_row(array('style'=> 'width:50%;', 'data'=>$preference), $controls);

	$preference = lang(lang('keyboard_shortcuts'), 'shortcuts').BR;
	$preference .= '<span class="label_note">' . lang('shortcuts_info');

	if($switchboard_installed)
	{
		$preference .= BR . lang('shortcuts_info_switchboard');
	}

	$preference .= '</span>';

	$controls = form_dropdown('shortcuts', $shortcuts['options'], $shortcuts['value'], 'id="shortcuts"');
	$this->table->add_row(array('style'=> 'width:50%;', 'data'=>$preference), $controls);

	echo $this->table->generate();
	$this->table->clear();
?>

<p style="margin-top: 15px; text-align:center">
	<?=form_submit(array('name' => 'ml_prefs_submit', 'value' => lang('update'), 'class' => 'submit'))?>
</p>
<?=form_close()?>