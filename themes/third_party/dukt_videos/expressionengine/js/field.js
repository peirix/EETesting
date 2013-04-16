/**
 * Dukt Videos
 *
 * @package		Dukt Videos
 * @version		Version 1.0.2
 * @author		Benjamin David
 * @copyright	Copyright (c) 2013 - DUKT
 * @link		http://dukt.net/add-ons/expressionengine/dukt-videos/
 *
 */

(function($) {

  	// plugin definition

	$.fn.dukt_videos_field = function(options)
	{		
		// build main options before element iteration
		// iterate and reformat each matched element

		return this.each(
			function()
			{
				field = $(this);

				$.fn.dukt_videos_field.init_field(field);
			}
		);
	};
	
	$.fn.dukt_videos_field.current_field = false;
	
	$.fn.dukt_videos_field.init = function()
	{
		dukt_log('field.js : ', '$.fn.dukt_videos_field.init()');
		
		// load box
	
		var data = {
			method: 'box',
			site_id: Dukt_videos.site_id,
		};
		
		$.ajax({
			url: Dukt_videos.ajax_endpoint,
			type:"post",
			data : data,
			success: function(data)
			{
				$('body').append(data);
			
				// init dukt videos box
				
				dukt_videos_box.box.init(function() {
					if($('.dv-overlay').css('display') != 'none')
					{
						dukt_videos_box.lightbox.show();
					}
				});
			}
		});
		

		// cancel
		
		$('.dv-cancel').live('click', function() {
			dukt_videos_box.lightbox.hide();
		});
		
		
		// submit
		
		$('.dv-submit').live('click', function() {
			var field = $.fn.dukt_videos_field.current_field;
			var video_url = $('.dv-current').data('video-url');
			
			$('input', field).attr('value', video_url);
			
			dukt_videos_box.lightbox.hide();
			
			$.fn.dukt_videos_field.callback_add();
		});
		
		
  		// matrix compatibility

  		if(typeof(Matrix) != "undefined")
  		{
			Matrix.bind("dukt_videos", "display", function(cell) {

				// we remove event triggers because they are all going to be redefined
				// will be improved with single field initialization

				if (cell.row.isNew)
				{
					var field = $('> .dv-field', cell.dom.$td);

					$.fn.dukt_videos_field.init_field(field);
				}
			});
		}		
	};
	
	
	$.fn.dukt_videos_field.init_field = function(field)
	{
		inputValue = $('input', field).attr('value');

		if(inputValue != "")
		{
			field.find('.dv-preview').html('');
			field.find('.dv-preview').css('display', 'block');
			field.find('.dv-preview').addClass('dv-field-preview-loading');

			video_page = inputValue;

			data = {
				'method': 'field_preview',
				'video_page': video_page,
				'site_id': Dukt_videos.site_id
			};

			$('input[type="hidden"]', field).attr('value', video_page);

			$.ajax({
			  url: Dukt_videos.ajax_endpoint,
			  type:"post",
			  data : data,
			  success: function(data)
			  {
		  		field.find('.dv-preview').html(data);
				field.find('.dv-preview').removeClass('dv-field-preview-loading');
			  }
			});

			$('.dv-change', field).css('display', 'inline-block');
			$('.dv-remove', field).css('display', 'inline-block');
		}
		else
		{
			$('.dv-add', field).css('display', 'inline-block');
		}

		$('.dv-add', field).click(function(){
			$.fn.dukt_videos_field.add(field);
		});
	
	
		$('.dv-change', field).click(function(){
			$.fn.dukt_videos_field.change(field);
		});
	
		$('.dv-remove', field).click(function(){
			$.fn.dukt_videos_field.remove(field);
		});
	
		$('.dv-field-embed-btn').live('click', function() {
			$('.dv-overlay').css('display', 'block');
			$('.dv-overlay').addClass('dv-overlay-loading');
	
			data = {
				'method': $(this).data('method'),
				'video_page': $(this).data('video-page'),
				'site_id': VideoPlayer.site_id
			};
	
			$.ajax({
			  url: VideoPlayer.ajax_endpoint,
			  type:"post",
			  data : data,
			  success: function( data ) {
	
		  		$('body').append(data);
	
				$('.dv-overlay').removeClass('dv-overlay-loading');
				$.fn.dukt_videos_field.lightbox.resize();
	
			  }
			});
		});
	
	};
	
	$.fn.dukt_videos_field.callback_add = function()
	{
		field = $.fn.dukt_videos_field.current_field;
	
		field.find('.dv-add').css('display', 'none');
		field.find('.dv-change').css('display', 'inline-block');
		field.find('.dv-remove').css('display', 'inline-block');
		field.find('.dv-preview').html('');
		field.find('.dv-preview').css('display', 'block');
		field.find('.dv-preview').addClass('videoplayer-field-preview-loading');
	
		video_page = $('.dv-preview').data('video-page');
	
		data = {
			'method': 'field_preview',
			'video_page': video_page,
			'site_id': Dukt_videos.site_id
		};
	
		$('input[type="hidden"]', field).attr('value', video_page);

		$.ajax({
		  url: Dukt_videos.ajax_endpoint,
		  type:"post",
		  data : data,
		  success: function( data )
		  {
	  		field.find('.dv-preview').html(data);
			field.find('.dv-preview').removeClass('dv-field-preview-loading');
		  }
		});
	};
	
		
	$.fn.dukt_videos_field.add = function(field)
	{
		$.fn.dukt_videos_field.current_field = field;
		
		dukt_videos_box.lightbox.show();
	};
	
	$.fn.dukt_videos_field.change = function(field)
	{
		$.fn.dukt_videos_field.current_field = field;
		dukt_videos_box.lightbox.show();
		
		// video page
		
		var video_page = field.find('input').attr('value');
		var current_service = $('.dv-services li.selected a.dv-service').data('service');
		
		// ajax browse to account
		
		var data = {
			method: 'box_preview',
			// service: current_service,
			site_id: Dukt_videos.site_id,
			video_page: video_page,
			autoplay: 0
		}
		
		if($('.dv-preview').data('video-page') != video_page)
		{
			$('.dv-preview').data('video-page', video_page);			

			dukt_videos_box.browser.go(data, 'preview', function() {
				$('.dv-preview .dv-controls').css('display', 'block');
			});
		}
	};
	
	$.fn.dukt_videos_field.remove = function(field)
	{		
		field.find('input').attr('value', '');
		
		field.find('.dv-add').css('display', 'inline-block');
		field.find('.dv-change').css('display', 'none');
		field.find('.dv-remove').css('display', 'none');
		field.find('.dv-preview').css('display', 'none');
	};


	// Initialization

	$(document).ready(function() {
		$.fn.dukt_videos_field.init();
	});


})(jQuery);

$().ready(function()
{
	$('.dv-field').dukt_videos_field();
});

/* End of file videoplayer.field.js */
/* Location: ./themes/third_party/videoplayer/js/videoplayer.field.js */