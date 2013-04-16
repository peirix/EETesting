Updater.InstallerURL = window.location.protocol+'//'+window.location.host+window.location.pathname + '?/dupdater/';
// ********************************************************************************* //

jQuery(document).ready(function(){

	Updater.Form = $('#process_form');
	Updater.Log = $('#process_log');
	$('#process_ee').click(Updater.ProcessEEFile);
});

//********************************************************************************* //

Updater.ProcessEEFile = function(Event){
	Event.preventDefault();

	var Target = $(Event.target);

	// Is the button disabled? bail out
	if ( Target.attr('disabled') ) return;

	// Now we can disale the button
	Target.attr('disabled', 'disabled').css('background', '#DDE2E5');

	Updater.Log.find('div.not_started').slideUp();
	Updater.Log.find('div.pre_process').slideDown();

	if ( Updater.Form.find('td.backup_db input:checked').val() == 'yes' || Updater.Form.find('td.backup_files input:checked').val() == 'yes' ) {
		Updater.Log.find('div.backup_process').slideDown();

		if (Updater.Form.find('td.backup_db input:checked').val() == 'no') Updater.Log.find('tr.action__backup_db').find('td.state').html(Updater.States.skipped);
		if (Updater.Form.find('td.backup_files input:checked').val() == 'no') Updater.Log.find('tr.action__backup_files').find('td.state').html(Updater.States.skipped);
	}

	// Show the loading indicator
	Updater.Log.find('.action__upload_file').find('.loading').show();

	Updater.Form.find('form').ajaxSubmit({
		dataType:'json', url: Updater.action_url_cp+'&task=ee_process_file',
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
	Updater.UpgradeInitSiteOffline();
};

//********************************************************************************* //

Updater.UpgradeInitSiteOffline = function(){

	var Wrapper = Updater.Log.find('.update_init').slideDown();
	Wrapper.show();

	var TR = Wrapper.find('.action__site_off');
	TR.find('.loading').show();
	TR.find('.state').html(Updater.States.working);

	$.ajax({url: Updater.action_url_cp+'&task=ee_backup_init' + '&cache=' + (new Date()).getTime(),
		dataType: 'json', type: 'POST', data: {"XID": EE.XID, 'action': 'site_offline'},
		error: function(xhr){
			TR.find('.state').html(Updater.States.failed);
			TR.find('.loading').hide();
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			// Hide the loading indicator
			TR.find('.loading').hide();

			if (rData.success == 'yes') {
				TR.find('.state').html(Updater.States.done);
				Updater.UpgradeInitInstaller();
			} else {
				TR.find('.state').html(Updater.States.failed);
				if (rData.body) TR.find('.msg').html(rData.body);
			}
		}
	});

};

//********************************************************************************* //

Updater.UpgradeInitInstaller = function(){

	var Wrapper = Updater.Log.find('.update_init').slideDown();
	Wrapper.show();

	var TR = Wrapper.find('.action__copy_installer');
	TR.find('.loading').show();
	TR.find('.state').html(Updater.States.working);

	$.ajax({url: Updater.action_url_cp+'&task=ee_backup_init' + '&cache=' + (new Date()).getTime(),
		dataType: 'json', type: 'POST', cache: false,
		data: {"XID": EE.XID, 'action': 'copy_installer', 'key': Updater.key},
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

				Updater.Updates = rData.updates;
				Updater.Server = rData.server;

				// Is this a build update?
				if (Updater.Updates.length === 0) {
					Updater.WaitForInstaller();
					return;
				}

				var TABLE = Updater.Log.find('.update_ee').slideDown();

				var TEMP = [];
				for (var i = 0; i < Updater.Updates.length; i++) {
					TEMP.push("<tr class='not_done' data-version='"+Updater.Updates[i].version+"'> <td class='action'><label>"+Updater.Updates[i].label+"</label></td> <td class='state'>"+Updater.States.waiting+"</td> <td class='msg'>"+Updater.ee_loading+"</td></tr>");
				}

				TABLE.find('tbody').html(TEMP.join(''));
				Updater.WaitForInstaller();
			}
		}
	});

};

//********************************************************************************* //

Updater.WaitForInstaller = function(Times){

	if (!Times) Times = 1;
	else Times++;

	var Wrapper = Updater.Log.find('.update_init').slideDown();
	Wrapper.show();

	var TR = Wrapper.find('.action__wait_installer');
	TR.find('.loading').show();
	TR.find('.attempts_wrapper').show();
	TR.find('.state').html(Updater.States.working);

	TR.find('.attempts').html(Times);

	$.ajax({url:Updater.InstallerURL+'index/' + (new Date()).getTime(),
		dataType: 'json', type: 'GET',
		timeout:3000,
		error: function(xhr){
			setTimeout(function(){
				Updater.WaitForInstaller(Times);
			}, 1000);
		},
		success: function(rData){
			if (!rData || !rData.success) {
				setTimeout(function(){
					Updater.WaitForInstaller(Times);
				}, 1000);
			}
			else {
				TR.find('.loading').hide();
				TR.find('.state').html(Updater.States.done);
				Updater.Upgrade();
			}
		}
	});
};

//********************************************************************************* //

Updater.Upgrade = function(){

	var TR = Updater.Log.find('.update_ee tr.not_done:first');

	if (TR.length === 0) {
		Updater.UpgradeCopyFilesPrepare();
		return;
	}

	TR.find('.msg .loading').show();
	TR.find('.state').html(Updater.States.working);

	$.ajax({url:Updater.InstallerURL + 'update_ee/'+ TR[0].getAttribute('data-version') + '/' + (new Date()).getTime(),
		dataType: 'json', type: 'POST',
		data: {"XID": EE.XID, 'version': TR[0].getAttribute('data-version'), 'key':Updater.key, 'server':JSON.stringify(Updater.Server)},
		error: function(xhr){

			TR.find('.state').html(Updater.States.failed);
			TR.find('.loading').hide();
			Updater.AJAX_error(TR, xhr, true);
		},
		success: function(rData, textStatus,xhr){
			if (rData == null){
				TR.find('.state').html(Updater.States.failed);
				TR.find('.loading').hide();
				Updater.AJAX_error(TR, xhr, true);
				return;
			}

			if (rData.queries) {
				for (var i = 0; i < rData.queries.length; i++) {
					Updater.queries.push(rData.queries[i]+';');
				}
			}

			if (rData.success == 'yes'){
				TR.removeClass('not_done').addClass('done');
				TR.find('.state').html(Updater.States.done);
				TR.find('.msg .loading').hide();

				Updater.Upgrade();
			} else {
				TR.find('.state').html(Updater.States.failed);
				TR.find('.loading').hide();
				if (rData.body) TR.find('.msg').html(rData.body);
				TR.find('.msg').append('&nbsp;' + Updater.retry_lang);
				Updater.Log.find('.update_post').find('.open_sql_queries').show().find('span').html(Updater.queries.length);
			}
		}
	});
};

//********************************************************************************* //

Updater.UpgradeCopyFilesPrepare = function(){

	Updater.Log.find('.update_post').slideDown();
	var TR = Updater.Log.find('.update_post .action__copy_files');

	// Show the loading indicator
	TR.find('.loading').show();
	TR.find('.state').html(Updater.States.working);

	$.ajax({url: Updater.InstallerURL+'copy_files_prepare' + '/' + (new Date()).getTime(),
		dataType: 'json', type: 'POST',
		data: {"XID": EE.XID, 'key':Updater.key, 'server':JSON.stringify(Updater.Server) },
		error: function(xhr){
			TR.find('.loading').hide();
			TR.find('.state').html(Updater.States.failed);
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			if (rData.success == 'yes'){
				Updater.CopyDirs = rData.dirs;
				TR.find('.progress').show();
				Updater.UpgradeCopyFiles(0, TR);
			} else {
				TR.find('.state').html(Updater.States.failed);
				TR.find('.loading').hide();
				if (rData.body) TR.find('.msg').html(rData.body);
			}
		}
	});

};

//********************************************************************************* //

Updater.UpgradeCopyFiles = function(Index, TR){

	if (typeof(Updater.CopyDirs[Index]) == 'undefined') {
		TR.find('.state').html(Updater.States.done);
		TR.find('.loading').hide();
		TR.find('.progress').hide();
		Updater.UpdateModules();
		return;
	}

	var Percent = (100/Updater.CopyDirs.length) * Index;
	TR.find('div.bar').css('width', Percent+'%');
	TR.find('.loading').html(Updater.CopyDirs[Index]);

	$.ajax({url: Updater.InstallerURL+'copy_files/'+encodeURIComponent(Updater.CopyDirs[Index].replace(/\//g,'-')) + '/' + (new Date()).getTime(),
		dataType: 'json', type: 'POST',
		data: {"XID": EE.XID, 'key':Updater.key, 'server':JSON.stringify(Updater.Server), 'dir': Updater.CopyDirs[Index] },
		error: function(xhr){
			TR.find('.loading').hide();
			TR.find('.state').html(Updater.States.failed);
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			if (rData.success == 'yes') {
				Updater.UpgradeCopyFiles((Index+1), TR);
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

Updater.UpdateModules = function(){

	var TR = Updater.Log.find('.update_post .action__update_modules');

	// Show the loading indicator
	TR.find('.loading').show();
	TR.find('.state').html(Updater.States.working);

	$.ajax({url: Updater.InstallerURL+'update_modules' + '/' + (new Date()).getTime(),
		dataType: 'json', type: 'POST',
		data: {"XID": EE.XID, 'key':Updater.key, 'server':JSON.stringify(Updater.Server) },
		error: function(xhr){
			TR.find('.loading').hide();
			TR.find('.state').html(Updater.States.failed);
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){

			if (rData.queries) {
				for (var i = 0; i < rData.queries.length; i++) {
					Updater.queries.push(rData.queries[i]+';');
				}
			}

			if (rData.success == 'yes'){
				TR.find('.loading').hide();
				TR.find('.state').html(Updater.States.done);
				Updater.UpgradeCleanup();
			} else {
				TR.find('.state').html(Updater.States.failed);
				TR.find('.loading').hide();
				if (rData.body) TR.find('.msg').html(rData.body);
			}
		}
	});

};

//********************************************************************************* //

Updater.UpgradeCleanup = function(){

	var TR = Updater.Log.find('.update_post .action__cleanup');

	Updater.Log.find('.update_post').find('.open_sql_queries').show().find('span').html(Updater.queries.length);

	// Show the loading indicator
	TR.find('.loading').show();
	TR.find('.state').html(Updater.States.working);

	$.ajax({url: Updater.InstallerURL+'cleanup' + '/' + (new Date()).getTime(),
		dataType: 'json', type: 'POST',
		data: {"XID": EE.XID, 'key':Updater.key, 'server':JSON.stringify(Updater.Server) },
		error: function(xhr){
			TR.find('.loading').hide();
			TR.find('.state').html(Updater.States.failed);
			Updater.AJAX_error(TR, xhr);
		},
		success: function(rData){
			TR.find('.loading').hide();
			TR.find('.state').html(Updater.States.done);
			Updater.UpgradeDone();
		}
	});

};

//********************************************************************************* //

Updater.UpgradeDone = function(){
	Updater.Log.find('.upgrade_done').slideDown();
};

//********************************************************************************* //
