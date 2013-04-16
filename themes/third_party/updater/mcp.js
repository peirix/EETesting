var Updater = Updater ? Updater : {};
Updater.Server = {};
Updater.forced_backup_db = false;
Updater.backup_db_done = false;
Updater.backup_files_done = false;
// ********************************************************************************* //

jQuery(document).ready(function(){

	setTimeout(function(){
		if ( ! $('#hideSidebarLink').is(':hidden') ) $('#hideSidebarLink').trigger('click');
	}, 1000);

	var UBODY = $('#ubody');
	UBODY.delegate('.show_error', 'click', Updater.ShowAJAXError);
	UBODY.delegate('span.retry', 'click', Updater.RetryAction);
	UBODY.delegate('#test_settings', 'click', Updater.TestSettings);
	UBODY.delegate('.open_sql_queries', 'click', Updater.OpenSQLQueries);

	// Send a test AJAX request
	Updater.TestAJAX(Updater.action_url);
	Updater.TestAJAX(Updater.action_url_cp);
});

//********************************************************************************* //

Updater.TestAJAX = function(action_url){
	$.ajax({url:action_url+'&task=test_ajax_call',
		dataType: 'json', type: 'POST',
		error: function(xhr, a, b){
			$('#test_ajax_error').show();

			if (xhr.status === 0) {
				$('#test_ajax_error').append('<br><br><strong>Response Code:</strong> ' + xhr.status + '&nbsp;&nbsp;&nbsp;<strong>Response Text</strong>: (Probably Cross-Domain AJAX Error)');
			}
			else if (xhr.status > 200) {
				$('#test_ajax_error').append('<br><br><strong>Response Code:</strong> ' + xhr.status + '&nbsp;&nbsp;&nbsp;<strong>Response Text</strong>: ' + xhr.statusText);
			}
		},
		success: function(rData){
			if (!rData) $('#test_ajax_error').show();
		}
	});
};

//********************************************************************************* //

Updater.TestSettings = function(Event){

	Event.preventDefault();
	var Modal = $('#test_transfer_method');

	Modal.css({width:'800px', 'margin-left': function () {
            return -($(this).width() / 2);
	}}).modal().find('.loading').show();
	Modal.find('.wrapper').empty();

	$.post(Updater.action_url_cp+'&task=test_transfer_method', {}, function(rData){
		Modal.find('.loading').hide();
		Modal.find('.wrapper').html(rData);
	});

};

//********************************************************************************* //

Updater.BackupDB = function(forced){
	var Setting = Updater.Form.find('td.backup_db input:checked').val();
	var TR = Updater.Log.find('.backup_process .action__backup_db');

	if (Setting == 'no' && forced == false) {
		Updater.BackupFiles();
		return;
	}

	// Forced Backup DB
	if (forced == true) {
		Updater.forced_backup_db = true;
		Updater.Log.find('div.backup_process').slideDown();

		Updater.Log.find('tr.action__backup_db').find('td.state').html(Updater.States.forced);
		if (Updater.Form.find('td.backup_files input:checked').val() == 'no') Updater.Log.find('tr.action__backup_files').find('td.state').html(Updater.States.skipped);

	} else {
		// Show the normal indicator
		TR.find('.state').html(Updater.States.working);
	}

	// Mark it
	Updater.backup_db_done = true;

	TR.find('.loading').show();

	$.ajax({url: Updater.action_url_cp+'&task=backup_database_prepare' + '&cache=' + (new Date()).getTime(),
		dataType: 'json', type: 'POST', data: {"XID": EE.XID, 'key': Updater.key},
		error: function(xhr){
			TR.find('.loading').hide();
			TR.find('.state').html(Updater.States.failed);
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			Updater.DBTables = rData.tables;
			TR.find('.progress').show();
			Updater.BackupDBTables(0, TR);
		}
	});

};

//********************************************************************************* //

Updater.BackupDBTables = function(Index, TR){

	if (typeof(Updater.DBTables[Index]) == 'undefined') {
		TR.find('.state').html(Updater.States.done);
		TR.find('.loading').hide();
		TR.find('.progress').hide();

		if (Updater.forced_backup_db == true) {
			Updater.AfterBackup();
		} else {
			Updater.BackupFiles();
		}

		return;
	}

	var Percent = (100/Updater.DBTables.length) * Index;
	TR.find('div.bar').css('width', Percent+'%');
	TR.find('.loading').html(Updater.DBTables[Index]);

	$.ajax({url:Updater.action_url_cp+'&task=backup_database' + '&cache=' + (new Date()).getTime(),
		dataType: 'json', type: 'POST',
		data: {"XID": EE.XID,
			'action': 'backup', 'key': Updater.key,
			'table': Updater.DBTables[Index]
		},
		error: function(xhr){
			TR.find('.state').html(Updater.States.failed);
			TR.find('.loading').hide();
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			if (rData.success == 'yes') {
				Updater.BackupDBTables((Index+1), TR);
			} else {
				TR.find('.loading').hide();
				TR.find('.progress').hide();
				TR.find('.state').html(Updater.States.failed);
				if (rData.body) TR.find('.error').html(rData.body);
			}
		}
	});
};

//********************************************************************************* //

Updater.BackupFiles = function(){
	var Setting = Updater.Form.find('td.backup_files input:checked').val();
	var TR = Updater.Log.find('.backup_process .action__backup_files');

	if (Setting == 'no') {
		Updater.AfterBackup();
		return;
	}

	// Show the loading indicator
	TR.find('.loading').show();
	TR.find('.state').html(Updater.States.working);

	// Mark it
	Updater.backup_files_done = true;

	$.ajax({url: Updater.action_url_cp+'&task=backup_files_prepare' + '&cache=' + (new Date()).getTime(),
		dataType: 'json', type: 'POST', data: {"XID": EE.XID, 'key': Updater.key},
		error: function(xhr){
			TR.find('.loading').hide();
			TR.find('.state').html(Updater.States.failed);
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			Updater.BackupDirs = rData.dirs;
			TR.find('.progress').show();
			Updater.BackupFilesDirs(0, TR);
		}
	});

};

//********************************************************************************* //

Updater.BackupFilesDirs = function(Index, TR){

	if (typeof(Updater.BackupDirs[Index]) == 'undefined') {
		TR.find('.state').html(Updater.States.done);
		TR.find('.loading').hide();
		TR.find('.progress').hide();
		Updater.AfterBackup();
		return;
	}

	var Percent = (100/Updater.BackupDirs.length) * Index;
	TR.find('div.bar').css('width', Percent+'%');
	TR.find('.loading').html(Updater.BackupDirs[Index]);

	$.ajax({url:Updater.action_url_cp+'&task=backup_files' + '&cache=' + (new Date()).getTime(),
		dataType: 'json', type: 'POST',
		data: {"XID": EE.XID,
			'action': 'backup', 'key': Updater.key,
			'dir': Updater.BackupDirs[Index]
		},
		error: function(xhr){
			TR.find('.state').html(Updater.States.failed);
			TR.find('.loading').hide();
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			if (rData.success == 'yes') {
				Updater.BackupFilesDirs((Index+1), TR);
			} else {
				TR.find('.loading').hide();
				TR.find('.progress').hide();
				TR.find('.state').html(Updater.States.failed);
				if (rData.body) TR.find('.error').html(rData.body);
			}
		}
	});

};

//********************************************************************************* //

Updater.Debug = function(msg){
	try {
		console.log(msg);
	}
	catch (e) {	}
};

//********************************************************************************* //

Updater.OpenSQLQueries = function(Event){
	Event.preventDefault();
	$('#queries_executed').slideDown();

	$('html, body').stop().animate({
		scrollTop: $('#queries_executed').offset().top
	}, 1000);

	$('#queries_executed').find('textarea').html(Updater.queries.join("\n"));
};

//********************************************************************************* //

Updater.AJAX_error = function(TR, xhr, retry){
	TR.find('.msg').append(Updater.show_error_html);
	$('#error_log').find('.body').html(xhr.responseText);

	if (retry) {
		TR.find('.msg').append('&nbsp;' + Updater.retry_lang);
	}
};

//********************************************************************************* //

Updater.ShowAJAXError = function(Event){
	Event.preventDefault();
	$('#error_log').slideDown();

	$('html, body').stop().animate({
		scrollTop: $('#error_log').offset().top
	}, 1000);
};

//********************************************************************************* //

Updater.RetryAction = function(Event) {

	var Parent = $(Event.target).closest('div');
	var ParentTR = $(Event.target).closest('tr');

	if (Parent.hasClass('update_ee') === true) {
		ParentTR.find('.msg').html('');
		ParentTR.addClass('not_done');
		Updater.Upgrade();
	}
}

//********************************************************************************* //
