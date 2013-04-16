jQuery(document).ready(function(){

	Updater.GetServerInfo();

	var UBODY = $('#ubody');
	UBODY.delegate('.test_file_transfer', 'click', Updater.TestFileTransfer);

	$('#file_transfer_options').delegate('input', 'click', Updater.ToggleFileTransferOptions);
	Updater.ToggleFileTransferOptions();
});

//********************************************************************************* //

Updater.GetServerInfo = function(){

	$.ajax({url: Updater.action_url+'&task=get_server_info',
		dataType: 'json', type: 'GET',
		data: {},
		error: function(){

		},
		success: function(rData){
			Updater.Server = rData.server;

			for (var Path in Updater.Server) {
				if ($('input.path__'+Path).val() == '') {
					$('input.path__'+Path).attr('value', Updater.Server[Path]);
				}
			}
		}
	});

};

//********************************************************************************* //

Updater.ToggleFileTransferOptions = function(){
	var Options = $('#file_transfer_options');
	var Value = Options.find('input:checked').val();
	var Parent = Options.closest('.utable');
	Parent.find('.settings_overlay').removeClass('settings_overlay');
	Parent.find('.'+Value).addClass('settings_overlay');
};

//********************************************************************************* //

Updater.TestFileTransfer = function(Event){
	Event.preventDefault();
	var Modal = $('#test_transfer_method');

	Modal.css({width:'800px', 'margin-left': function () {
            return -($(this).width() / 2);
	}}).modal().find('.loading').show();
	Modal.find('.wrapper').empty();

	var Params = $(Event.target).closest('form').find(':input').serializeArray();

	$.post(Updater.action_url+'&task=test_transfer_method', Params, function(rData){
		Modal.find('.loading').hide();
		Modal.find('.wrapper').html(rData);
	});
};

//********************************************************************************* //

