<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Backup Pro
 *
 * @package		mithra62:m62_backup
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2011, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/backup-pro/
 * @version		1.8.3
 * @filesource 	./system/expressionengine/third_party/m62_backup/
 */
 
 /**
 * Backup Pro - Js Library
 *
 * Javascript Library class
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/libraries/M62_backup_js.php
 */
class M62_backup_js
{
	
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	public function get_accordian_css()
	{
		return ' $("#my_accordion").accordion({autoHeight: false,header: "h3"}); ';
	}
	
	public function get_backup_progressbar($proc_url, $url_base)
	{
		$js = "
			var kill_progress = false;
			
			$.ajax({
				url: '".html_entity_decode($proc_url)."',
				cache: false,
				dataType: 'json',
				success: function(data) {
					$('#progressbar').progressbar('option', 'value', 100);
					kill_progress = true;
					$.ajax({
						url: '".html_entity_decode($url_base)."progress',
						cache: false,
						dataType: 'json',
						success: function(data) {
							$('#active_item').html('');
							$('#total_items').html(data['total_items']);	
							$('#active_item').html(data['msg']);
							$('#item_number').html(data['item_number']);
							$('div.heading h2.edit').html('".lang('backup_progress_bar_stop')."');
							document.title = '".lang('backup_progress_bar_stop')."';
							$('#breadCrumb li:last').html('".lang('backup_progress_bar_stop')."');
							$('#backup_instructions').hide();
						}
					});			
				}
			});
				
			function updateProgress() {
				var progress;
				progress = $('#progressbar').progressbar('option','value');
				if (progress < 100 && !kill_progress) {
		
					$.ajax({
						url: '".html_entity_decode($url_base)."progress',
						cache: false,
						dataType: 'json',
						success: function(data) {
							progress = Math.floor(data['item_number']/data['total_items']*100);
							$('#progressbar').progressbar('option', 'value', progress);
							$('#total_items').html(data['total_items']);	
							$('#active_item').html(data['msg']);
							$('#item_number').html(data['item_number']);
							if(data['total_items'] > 0 && data['item_number'] > 0 && data['item_number'] == data['total_items'])
							{
								$('div.heading h2.edit').html('".lang('backup_progress_bar_stop')."');
								document.title = '".lang('backup_progress_bar_stop')."';
								$('#breadCrumb li:last').html('".lang('backup_progress_bar_stop')."');
								$('#backup_instructions').hide();
							}
							
						}
					});
					setTimeout(updateProgress, 2000);
				}
				else
				{
		
				}
			}	  
			setTimeout(updateProgress, 2000);		
		";
		return $js;
	}
	
	public function get_settings_form()
	{
		return array('
				
			if($("#auto_threshold").val() == "custom")
			{
				$("#auto_threshold_custom").show();
			}
			
			var def_assign = "0";
			$("#auto_threshold").change(function(){
				var new_assign = $("#auto_threshold").val();
				if(new_assign == def_assign || new_assign != "custom")
				{
					$("#auto_threshold_custom").hide();
					$("#auto_threshold_custom").val(new_assign);
				}
				else
				{
					$("#auto_threshold_custom").show();
				}
			});				
		');
	}
	
	public function get_check_toggle()
	{
		return array(
						'$(".toggle_all_db").toggle(
							function(){
								$("input.toggle_db").each(function() {
									this.checked = true;
								});
							}, function (){
								var checked_status = this.checked;
								$("input.toggle_db").each(function() {
									this.checked = false;
								});
							}
						);',
		
						'$(".toggle_all_files").toggle(
							function(){
								$("input.toggle_files").each(function() {
									this.checked = true;
								});
							}, function (){
								var checked_status = this.checked;
								$("input.toggle_files").each(function() {
									this.checked = false;
								});
							}
						);'		
					);		
	}
}