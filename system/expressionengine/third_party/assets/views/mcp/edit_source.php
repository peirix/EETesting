<?php

if (! $is_new)
{
	$source_settings = $source->settings();
}

echo form_open($base.AMP.'method=save_source', array('id' => 'source_form'));
echo '<input type="hidden" class="setting_field" name="source_id" value="' . ($is_new ? '0' : $source->get_source_row()->source_id) . '" />';
echo '<input type="hidden" id="bucket_location" name="bucket_location" value="'.(!empty($source_settings) ? $source_settings->location : '').'" />';

$this->table->set_template($cp_table_template);
$this->table->set_heading(array(
	array('colspan' => '2', 'data' => lang('source_settings'))
));

$this->table->add_row(
	array('style' => 'width: 50%', 'data' => lang('source_name', 'source_name') . '<br/>' . lang('source_name_instructions')),
	form_input('source_name', $is_new ? '' : $source->get_source_row()->name, 'id="source_name" style="width: 98%"')
);

foreach ($setting_fields as $field)
{
	$this->table->add_row(
		array('style' => 'width: 50%', 'data' => lang($field, $field)),
		form_input($field, $is_new ? '' : $source_settings->{$field}, 'id="' . $field . '" class="setting_field" style="width: 98%"')
	);

}

$bucket_html = '<span id="buckets">';

if (!empty($source_settings))
{
	$bucket_html .= '<select disabled="disabled">' .
		'<option>'.$source_settings->bucket.'</option>' .
		'</select>' .
		form_hidden('bucket', $source_settings->bucket);
}

$bucket_html .= '</span>' .
	' <a href="" class="refresh_buckets">'.lang('refresh').'</a>';

$this->table->add_row(array(
	array('data' => lang('bucket', 'bucket'), 'style' => 'width: 50%;'),
	$bucket_html
));


$this->table->add_row(array(
	lang('url_prefix', 'bucket_url_prefix').'<br />'.lang('url_prefix_instructions'),
	form_input(array(
		'id'    => 'bucket_url_prefix',
		'name'  => 'bucket_url_prefix',
		'value' => (!empty($source_settings) ? $source_settings->url_prefix : '')
	)),
));

echo $this->table->generate();

echo form_submit(array('name' => 'save_source', 'value' => lang('save_source'), 'class' => 'submit'));
echo form_close();
