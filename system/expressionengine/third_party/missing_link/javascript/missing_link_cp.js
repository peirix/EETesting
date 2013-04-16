// ================================= 
// ! Missing Link control panel JS   
// ================================= 

// parseUri 1.2.2
// (c) Steven Levithan <stevenlevithan.com>
// MIT License

function parseUri (str) {
	var	o   = parseUri.options,
		m   = o.parser[o.strictMode ? "strict" : "loose"].exec(str),
		uri = {},
		i   = 14;

	while (i--) uri[o.key[i]] = m[i] || "";

	uri[o.q.name] = {};
	uri[o.key[12]].replace(o.q.parser, function ($0, $1, $2) {
		if ($1) uri[o.q.name][$1] = $2;
	});

	return uri;
};

parseUri.options = {
	strictMode: false,
	key: ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
	q:   {
		name:   "queryKey",
		parser: /(?:^|&)([^&=]*)=?([^&]*)/g
	},
	parser: {
		strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
		loose:  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
	}
};

$(document).ready(function() {

	var getVar = parseUri(document.URL).queryKey;
	var success_msg = $('div#notice_texts_container div.notice_success p');
	
	if(typeof parent.ml_action_monitoring != 'undefined')
	{
		var ml_action_monitoring = parent.ml_action_monitoring;
	}
	else
	{
		var ml_action_monitoring = '';
	}

	if(typeof parent.$.fancybox  == 'function'
		&& (success_msg.length > 0 || getVar.C == 'content_publish' && getVar.M == 'view_entry')
		&& parseUri(window.parent.$("div#ml_dialog").data('active_link')).relative == parseUri(document.referrer).relative
	)
	{

		if(ml_action_monitoring == 'reload')
		{
			parent.do_reload(300);
			parent.$.fancybox.close();
		}
		else if(ml_action_monitoring == 'dialog')
		{

			if(success_msg.length > 0)
			{
				var dialog_title = $('div#notice_texts_container div.notice_success p').text();
			}
			else
			{
				var dialog_title = ml_entry_updated;
			}
		
			jQuery('body').append('<div id="ml_confirm_dialog" title="'+dialog_title+'"><span class="ui-icon ui-icon-refresh" style="float:left; margin:0 7px 20px 0;"></span>'+ml_reload_view_changes+'</div>');
			
			var dialog_buttons = {};

			dialog_buttons[ml_reload_now] = function() {
				parent.do_reload(100);
				parent.$.fancybox.close();
			};

			dialog_buttons[ml_not_done_yet] = function() {
				jQuery(this).dialog( "close" );
			}

			jQuery('div#ml_confirm_dialog').dialog({
				resizable: false,
				minHeight: 	80,
				width: 300,
				modal: true,
				buttons: dialog_buttons,
				open: function() {
					jQuery(this).parents('.ui-dialog-buttonpane button:eq(0)').focus();
					jQuery('body').css('overflow','hidden'); $('.ui-widget-overlay').css('width','100%'); // disable scrolling on the underlying page
				},
			    close: function(event, ui){ jQuery('body').css('overflow','auto'); }
			});
		}
	}
});