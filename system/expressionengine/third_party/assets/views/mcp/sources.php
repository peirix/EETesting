<?php

echo form_open($base.AMP.'method=edit_source');

if (!empty($s3_accounts))
{
	$this->table->set_heading(
		array('data' => lang('id'), 'style' => 'width: 5%;'),
		array('data' => lang('source_name')),
		array('data' => lang('edit'), 'style' => 'width: 5%; text-align: center;'),
		array('data' => lang('delete'), 'style' => 'width: 5%; text-align: center;')
	);

	foreach ($s3_accounts as $account)
	{
		$settings = json_decode($account->settings);

		$this->table->add_row(
			array('data' => $account->source_id),
			array('data' => '<strong>'.$account->name.'</strong>'),
			array('data' => '<a href="'.BASE.AMP.$base.AMP.'method=edit_source'.AMP.'source_id='.$account->source_id.'"><img src="'.$cp_theme_url.'images/icon-edit.png" alt="'.lang('edit').'" /></a>', 'style' => 'text-align: center'),
			array('data' => '<a class="delete_source" href="javascript:;" data-source-id="'.$account->source_id.'" data-source-name="'.$account->name.'"><img src="'.$cp_theme_url.'images/icon-delete.png" alt="'.lang('delete').'" /></a>', 'style' => 'text-align: center')
		);
	}

	echo $this->table->generate();
}
else
{
	echo '<p>'.lang('no_sources_exist').'</p>';
}

echo form_submit(array('name' => 'submit', 'value' => lang('add_new_source'), 'class' => 'submit'));
echo form_close();


if (!empty($s3_accounts))
{
	echo form_open($base.AMP.'method=delete_source', 'id="delete_source"');
	echo '<input id="source_id" type="hidden" name="source_id" value="0" />';
	echo form_close();
}
