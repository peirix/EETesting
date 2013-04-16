jQuery(document).ready(function(){

	Updater.Form = $('#process_form');
	Updater.Log = $('#process_log');

	$('#uploadsec').delegate('.add_file', 'click', Updater.AddonAddFile);
	$('#uploadsec').delegate('.del_file', 'click', Updater.AddonDelFile);

	$('#process_addon').click(Updater.ProcessAddonFile);
});

//********************************************************************************* //

Updater.AddonAddFile = function(Event){
	Event.preventDefault();
	var Parent = $(Event.target).closest('td');
	var Clone = Parent.find('.filerow:first').clone();
	Clone.find(':input').val('');
	Clone.find('.add_file').removeClass('add_file').addClass('del_file');
	Parent.append(Clone);
};

//********************************************************************************* //

Updater.AddonDelFile = function(Event){
	Event.preventDefault();
	var Parent = $(Event.target).closest('div');
	Parent.fadeOut('slow', function(){
		Parent.remove();
	});
};

//********************************************************************************* //

Updater.ProcessAddonFile = function(Event){
	Event.preventDefault();
	var Target = $(Event.target);

	// Is the button disabled? bail out
	if ( Target.attr('disabled') ) return;

	// Now we can disale the button
	Target.attr('disabled', 'disabled').css('background', '#DDE2E5');

	Updater.Log.find('div.not_started').slideUp();
	Updater.Log.find('div.pre_process').slideDown();

	// Backup DB/Files ?
	if ( Updater.Form.find('td.backup_db input:checked').val() == 'yes' || Updater.Form.find('td.backup_files input:checked').val() == 'yes' ) {
		Updater.Log.find('div.backup_process').slideDown();

		if (Updater.Form.find('td.backup_db input:checked').val() == 'no') Updater.Log.find('tr.action__backup_db').find('td.state').html(Updater.States.skipped);
		if (Updater.Form.find('td.backup_files input:checked').val() == 'no') Updater.Log.find('tr.action__backup_files').find('td.state').html(Updater.States.skipped);
	}

	// Show the loading indicator
	Updater.Log.find('.action__upload_file').find('.loading').show();

	Updater.Form.find('form').ajaxSubmit({
		dataType:'json', url: Updater.action_url_cp+'&task=addon_process_file',
		success: function(rData, statusText, xhr, $form){

			// Hide the loading indicator
			Updater.Log.find('.action__upload_file').find('.loading').hide();

			// Loop over all the pre-process actions
			for (var action in rData.actions) {

				// Store for quick access
				var TD = Updater.Log.find('.action__' + action);

				// Was the action an success or a failure?
				if (rData.actions[action].success == 'yes') {
					TD.find('.state').html(Updater.States.done);
					if (rData.actions[action].body) TD.find('.msg').html(rData.actions[action].body);
				} else {
					TD.find('.state').html(Updater.States.failed);
					TD.find('.msg').html('<p class="error">' + rData.actions[action].body + '</p>');
					return;
				}
			}

			if (rData.addons) Updater.addons = rData.addons;

			// Can we continue?
			if (rData.go_on == 'yes') {
				Updater.BackupDB(false);
			}

		},
		error: function(xhr){
			var TR = Updater.Log.find('.action__upload_file');
			TR.find('.state').html(Updater.States.failed);
			Updater.Log.find('.action__upload_file').find('.loading').hide();
			Updater.AJAX_error(TR, xhr);
		}
	});

};

//********************************************************************************* //

Updater.AfterBackup = function(){
	if (!Updater.addons) return;

	// Where we forced to backup DB?
	if (Updater.forced_backup_db) {
		Updater.InstallAddon(Updater.addon_update);
		return;
	}

	var DoneDiv = Updater.Log.find('.post_process');
	var Clone;

	for (var Addon in Updater.addons){
		Clone = Updater.Log.find('.addon_install_process').clone().show().removeClass('addon_install_process').addClass('addon_install');
		Clone.find('h2').append( Updater.addons[Addon].label +' '+ Updater.addons[Addon].version);
		Clone.attr('data-addon', Addon);
		DoneDiv.before(Clone);
		Clone = null;
	}

	Updater.MoveFiles();
	Updater.Log.find('div.post_process').slideDown();
};

//********************************************************************************* //

Updater.MoveFiles = function(){

	var TABLE = Updater.Log.find('.addon_install:first');
	var Addon = TABLE.attr('data-addon');

	// Are we done?
	if (TABLE.length === 0) {
		Updater.AddonsPostProcess();
	}

	var TR = TABLE.find('.action__move_files');
	TR.find('.loading').show();
	TR.find('.state').html(Updater.States.working);

	$.ajax({url: Updater.action_url_cp+'&task=addon_move_files',
		dataType: "json", type: "POST",
		data: {"XID": EE.XID, "key": Updater.key, "addon": JSON.stringify(Updater.addons[Addon])},
		error: function(xhr){
			TR.find('.state').html(Updater.States.failed);
			TR.find('.loading').hide();
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			// Hide the loading indicator
			TR.find('.loading').hide();

			if (rData.success == 'no') {
				TR.find('.state').html(Updater.States.failed);
				if (rData.body) TR.find('.msg').html(rData.body);
			} else {
				TR.find('.state').html(Updater.States.done);

				if (typeof(rData.update_notes) != 'undefined' && rData.update_notes.length > 0) {
					$('#update_notes').show();

					for (var i = 0; i < rData.update_notes.length; i++) {
						$('#update_notes').find('tbody').append('<tr><td class="even"><strong>'+Updater.addons[Addon].label+': '+rData.update_notes[i].version+'</strong></td></tr><tr><td class="odd">'+rData.update_notes[i].message+'</td></tr>');
					};
				}

				if (Updater.backup_db_done == false && typeof(rData.force_db_backup) != 'undefined' && rData.force_db_backup == 'yes') {
					Updater.addon_update = rData.update;
					Updater.BackupDB(true);
				} else {
					Updater.InstallAddon(rData.update);
				}

			}
		}
	});
};

//********************************************************************************* //

Updater.InstallAddon = function(Update){

	delete Updater.addon_update;
	delete Updater.forced_backup_db;

	var TABLE = Updater.Log.find('.addon_install:first');
	var Addon = TABLE.attr('data-addon');

	var TR = TABLE.find('.action__install_addon');
	TR.find('.loading').show();
	TR.find('.state').html(Updater.States.working);

	$.ajax({url: Updater.action_url_cp+'&task=addon_install',
		dataType: 'json', type: 'POST',
		data: {"XID":EE.XID, 'key': Updater.key, 'update':Update, 'addon': Addon},
		error: function(xhr){
			TR.find('.state').html(Updater.States.failed);
			TR.find('.loading').hide();
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			// Hide the loading indicator
			TR.find('.loading').hide();

			if (rData.success == 'no') {
				TR.find('.state').html(Updater.States.failed);
				if (rData.body) TR.find('.error').html(rData.body);
			} else {
				TR.find('.state').html(Updater.States.done);
				TABLE.removeClass('addon_install');

				if (rData.queries) {
					for (var i = 0; i < rData.queries.length; i++) {
						Updater.queries.push(rData.queries[i]+';');
					}
				}

				Updater.MoveFiles();
			}
		}
	});
};

//********************************************************************************* //

Updater.AddonsPostProcess = function(){

	var TABLE = Updater.Log.find('.post_process');

	var TR = TABLE.find('.action__remove_temp_dirs');
	TR.find('.loading').show();
	TR.find('.state').html(Updater.States.working);

	TABLE.find('.open_sql_queries').show().find('span').html(Updater.queries.length);

	$.ajax({url: Updater.action_url_cp+'&task=remove_temp_dirs',
		dataType: 'json', type: 'POST',
		data: {"XID":EE.XID, 'key': Updater.key},
		error: function(xhr){
			TR.find('.state').html(Updater.States.failed);
			TR.find('.loading').hide();
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			// Hide the loading indicator
			TR.find('.loading').hide();

			if (rData.success == 'no') {
				TR.find('.state').html(Updater.States.failed);
				if (rData.body) TR.find('.error').html(rData.body);
			} else {
				TR.find('.state').html(Updater.States.done);
				Updater.Log.find('.upgrade_done').show();
			}
		}
	});

};

//********************************************************************************* //
