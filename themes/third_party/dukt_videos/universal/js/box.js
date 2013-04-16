/**
 * Dukt Videos
 *
 * @package		Dukt Videos
 * @version		Version 1.0
 * @author		Benjamin David
 * @copyright	Copyright (c) 2013 - DUKT
 * @link		http://dukt.net/videos/
 *
 */

/* -------------------------------------------------------------------- */

/* SPIN */

$.fn.spin = function(opts) {
	dukt_log('$.fn.spin');
	
	this.each(function() {
	var $this = $(this),
	    data = $this.data();
	
	if (data.spinner) {
	  data.spinner.stop();
	  delete data.spinner;
	}
	if (opts !== false) {
	  data.spinner = new Spinner($.extend({color: $this.css('color')}, opts)).spin(this);
	}
	});
	return this;
};

/* -------------------------------------------------------------------- */

/* DOCUMENT READY */

$(document).ready(function() {

	var opts = {
	  lines: 13, // The number of lines to draw
	  length: 4, // The length of each line
	  width: 2, // The line thickness
	  radius: 4, // The radius of the inner circle
	  corners: 1, // Corner roundness (0..1)
	  rotate: 0, // The rotation offset
	  color: '#000', // #rgb or #rrggbb
	  speed: 2.2, // Rounds per second
	  trail: 60, // Afterglow percentage
	  shadow: false, // Whether to render a shadow
	  hwaccel: false, // Whether to use hardware acceleration
	  className: 'spinner', // The CSS class to assign to the spinner
	  zIndex: 2e9, // The z-index (defaults to 2000000000)
	  top: '7px', // Top position relative to parent in px
	  left: '6px' // Left position relative to parent in px
	};

	$('.dv-box .dv-status .dv-spin').spin(opts);
	
	var opts = {
	  lines: 13, // The number of lines to draw
	  length: 3, // The length of each line
	  width: 2, // The line thickness
	  radius: 4, // The radius of the inner circle
	  corners: 1, // Corner roundness (0..1)
	  rotate: 0, // The rotation offset
	  color: '#000', // #rgb or #rrggbb
	  speed: 2.2, // Rounds per second
	  trail: 60, // Afterglow percentage
	  shadow: false, // Whether to render a shadow
	  hwaccel: false, // Whether to use hardware acceleration
	  className: 'spinner', // The CSS class to assign to the spinner
	  zIndex: 2e9, // The z-index (defaults to 2000000000)
	  top: '0', // Top position relative to parent in px
	  left: '0' // Left position relative to parent in px
	};
	
	$('.dv-box .dv-search .dv-spin').spin(opts);

});


var dukt_videos_box = {};

var dukt_videos_ajax_stack = [];



jQuery(document).ready(function($) {
    
    // $() will work as an alias for jQuery() inside of this function

	dukt_log('document ready');
	
	dukt_videos_box = {
		init: function()
		{
			dukt_log('dukt_videos_box.init()');
			//dukt_videos_box.box.init();
			dukt_videos_box.lightbox.init();

		},
		
		box: false,
		
		utils: {
			current_service: function() {
				return $($('.dv-box .dv-accounts > ul > li.selected > a').get(0)).data('service');
			}
		}
	};
	
	/* -------------------------------------------------------------------- */
	
	/**
	* Lightbox
	*
	*/
	dukt_videos_box.lightbox = {
		init: function()
		{
			dukt_log('dukt_videos_box.lightbox.init()');
			
			var overlay = $('<div class="dv-overlay loading"><div class="spin"></div></div>');
			
			$('body').append(overlay);
			
			$('.dv-overlay').live('click', function(){
				dukt_videos_box.lightbox.hide();
			});
			
			var opts = {
			  lines: 13, // The number of lines to draw
			  length: 5, // The length of each line
			  width: 2, // The line thickness
			  radius: 5, // The radius of the inner circle
			  corners: 1, // Corner roundness (0..1)
			  rotate: 0, // The rotation offset
			  color: '#fff', // #rgb or #rrggbb
			  speed: 2.2, // Rounds per second
			  trail: 60, // Afterglow percentage
			  shadow: false, // Whether to render a shadow
			  hwaccel: false, // Whether to use hardware acceleration
			  className: 'spinner', // The CSS class to assign to the spinner
			  zIndex: 2e9, // The z-index (defaults to 2000000000)
			  top: '0', // Top position relative to parent in px
			  left: '0' // Left position relative to parent in px
			};
		
			$('.dv-overlay .spin').spin(opts);
			
			dukt_videos_box.lightbox.resize();
			
			$(window).resize(function() {		
				dukt_videos_box.lightbox.resize();	
			});
		},
		
		show: function()
		{
			dukt_log('dukt_videos_box.lightbox.show()');
			
			$('.dv-overlay').css('display', 'block');
			$('.dv-box').css('display', 'block');
			
			if($('.dv-preview-video iframe').length > 0)
			{
				var embed = $('.dv-preview-video iframe').data('embedz');
				
				embed = embed.replace('autoplay=1', 'autoplay=0');
				
				$('.dv-preview-video iframe').attr('src', embed);
			}

			$('.dv-box .videos li').each(function() {
				if($(this).hasClass('selected'))
				{
					var data = {
						method : 'box_preview',
						service : dukt_videos_box.utils.current_service(),
						site_id : Dukt_videos.site_id,
						video_page : $(this).data('video-page'),
						autoplay : 0,
						rel : 0
					}
			
					$('.dv-box .preview').data('video-page', $(this).data('video-page'));
			
					$('.dv-box .controls').css('display', 'block');
			
					dukt_videos_box.browser.go(data, 'preview', function() {
			
						// init favorite button
						
						$('.dv-box .controls').css('display', 'block');
						
						dukt_videos_box.box.resize(true);
					});
				}
			});
		},
		
		hide: function()
		{
			dukt_log('dukt_videos_box.lightbox.hide()');
			
			// hide box and overlay
			
			$('.dv-overlay').css('display', 'none');
			$('.dv-box').css('display', 'none');
			
			
			// disable video embed
			
			/*
			$('.dv-box .dv-preview-inject').html('');
			$('.dv-preview .dv-controls').css('display', 'none');			
			*/
			
			if( $('.dv-preview-video iframe').length > 0)
			{
				var embed = $('.dv-preview-video iframe').attr('src');
				$('.dv-preview-video iframe').data('embedz', embed);

				$('.dv-preview-video iframe').attr('src', '');
			}
			
			dukt_videos_box.browser.abort('preview');
			
			
			
		},
		
		resize: function()
		{
			dukt_log('dukt_videos_box.lightbox.resize()');
			
			var winW = $(window).width();
			var winH = $(window).height();
			
			var boxW = $('.dv-box').outerWidth();
			var boxH = $('.dv-box').outerHeight();
			
			var boxX = Math.round((winW - boxW) / 2);
			var boxY = Math.round((winH - boxH) / 2);
	
			$('.dv-box').css({
				'left': boxX,
				'top': boxY
			});
		
			$('.dv-overlay').css({
				'width' : winW,
				'height' : winH
			});
			
		}
	};
	
	/* -------------------------------------------------------------------- */
	
	/**
	* Ajax Stack
	*
	*/
	dukt_videos_box.ajax_stack = {
		init: function()
		{
			dukt_log('dukt_videos_box.ajax_stack.init()');
			
			$('.dv-box .dv-status .dv-reload').live('click', function() {
				
				dukt_log('dukt_videos_box.ajax_stack.init()', 'reload all');
				
				$('.dv-box .dv-accounts ul ul > li > a').each(function(i, el)
				{
					
					var listing = $(el).data('listing');
					var service = $(el).data('service');
					var method = $(el).data('method');
							
					data = {
						'method': 	method,
						'site_id': 	Dukt_videos.site_id,
						'service':	service
					};
				
					var listing_view = false;
				
					$('.dv-box .dv-listing-view').each(function(i, el) {
						if($(el).data('listing') == listing && $(el).data('service') == service)
						{
							listing_view = $(el);
						}
					});
					
					// add request to stack
					
					var k = $(el).data('service') + $(el).data('listing');
				
					dukt_videos_box.ajax_stack.add(k, function() {
						return $.ajax({
							url: Dukt_videos.ajax_endpoint,
							type:"post",
							data : data,
							
							beforeSend:function()
							{
								$('.dv-box .videos', listing_view).addClass('dukt-videos-loading');
							},
							
							success: function( data ) {
								
								// remove request from stack
				
								dukt_videos_box.ajax_stack.remove(k);
								
								$('.dv-videos-inject', listing_view).html(data);
								
								// $('.dv-box .videos-empty', listing_view).css('display', 'none');
							}
						});
					});
					
				});
			});
		},
		
		add: function(k, callback)
		{
			dukt_log('dukt_videos_box.ajax_stack.add()', k);
			
			dukt_videos_ajax_stack[k] = callback();
			
			dukt_videos_box.ajax_stack.updateStatus();
		},
		
		remove: function(k)
		{
			dukt_log('dukt_videos_box.ajax_stack.remove()', k);
			
			//dukt_videos_ajax_stack.splice(k, 1);
			
			for(key in dukt_videos_ajax_stack)
			{
				if(key == k)
				{
					delete dukt_videos_ajax_stack[key];	
				}
			}
			
			dukt_videos_box.ajax_stack.updateStatus();
		},
		
		updateStatus: function()
		{
			dukt_log('dukt_videos_box.ajax_stack.updateStatus()');
			
			if(dukt_videos_box.ajax_stack.count() > 0)
			{
				$('.dv-box .dv-status').addClass('dv-loading');
			}
			else
			{
				$('.dv-box .dv-status').removeClass('dv-loading');
			}
		},
		
		count: function()
		{		
			dukt_log('dukt_videos_box.ajax_stack.count()');
			var i = 0;
			
			for(key in dukt_videos_ajax_stack)
			{
				i++;
			}
			
			return i;
		}
	};
	
	/* -------------------------------------------------------------------- */
	
	/**
	* Listings
	*
	*/
	dukt_videos_box.listings = {
		reload: function(listing, service, method)
		{
			dukt_log('dukt_videos_box.listings.reload()', listing, service, method);
			
			var k = service + listing;

			dukt_videos_box.ajax_stack.add(k, function() {
				data = {
					'method': 	method,
					'site_id': 	Dukt_videos.site_id,
					'service':	service
				};
				
				var listing_view = false;
		
				$('.dv-box .dv-listing-view').each(function(i, el) {
					if($(el).data('listing') == listing && $(el).data('service') == service)
					{
						listing_view = $(el);
					}
				});

			
				return $.ajax({
					url: Dukt_videos.ajax_endpoint,
					type:"post",
					data : data,
					
					beforeSend:function()
					{

					},
					
					success: function( data ) {
						
						// remove request from stack
	
						dukt_videos_box.ajax_stack.remove(k);
						
						$('.dv-videos-inject', listing_view).html(data);
					}
				});
			});
		}
	};
	
	/* -------------------------------------------------------------------- */
	
	/**
	* Box
	*
	*/
	dukt_videos_box.box = {
		
		init: function(callback)
		{
			dukt_log('dukt_videos_box.box.init()');
			dukt_videos_box.ajax_stack.init();
			dukt_videos_box.box.accounts();
			dukt_videos_box.box.videos();
			dukt_videos_box.search.init();
			dukt_videos_box.box.resize();
	
			$(window).resize(function() {
				dukt_videos_box.box.resize();
			});
					
			var opts = {
			  lines: 13, // The number of lines to draw
			  length: 4, // The length of each line
			  width: 2, // The line thickness
			  radius: 4, // The radius of the inner circle
			  corners: 1, // Corner roundness (0..1)
			  rotate: 0, // The rotation offset
			  color: '#000', // #rgb or #rrggbb
			  speed: 2.2, // Rounds per second
			  trail: 60, // Afterglow percentage
			  shadow: false, // Whether to render a shadow
			  hwaccel: false, // Whether to use hardware acceleration
			  className: 'spinner', // The CSS class to assign to the spinner
			  zIndex: 2e9, // The z-index (defaults to 2000000000)
			  top: '7px', // Top position relative to parent in px
			  left: '6px' // Left position relative to parent in px
			};
		
			$('.dv-box .dv-status .dv-spin').spin(opts);
			
			var opts = {
			  lines: 13, // The number of lines to draw
			  length: 4, // The length of each line
			  width: 2, // The line thickness
			  radius: 4, // The radius of the inner circle
			  corners: 1, // Corner roundness (0..1)
			  rotate: 0, // The rotation offset
			  color: '#fff', // #rgb or #rrggbb
			  speed: 2.2, // Rounds per second
			  trail: 60, // Afterglow percentage
			  shadow: false, // Whether to render a shadow
			  hwaccel: false, // Whether to use hardware acceleration
			  className: 'spinner', // The CSS class to assign to the spinner
			  zIndex: 2e9, // The z-index (defaults to 2000000000)
			  top: '7px', // Top position relative to parent in px
			  left: '6px' // Left position relative to parent in px
			};

			$('.dv-box .dv-preview .dv-spin').spin(opts);
			
			if(typeof(callback) !== 'undefined')
			{
				callback();
			}
		},
	
		
		resize : function(ipadfix_enabled)
		{
			dukt_log('dukt_videos_box.box.resize()');
			
			if(typeof(ipadfix_enabled) == "undefined")
			{
				ipadfix_enabled = false;
			}
			
			var winH = $('.dv-box').height();
			var winW = $(window).width();
			
			// var headH = $('#head').outerHeight();
			var headH = 0;
			
			var topH = $('.dv-top').outerHeight();
			var bottomH = $('.dv-bottom').outerHeight();
			
			// var previewPlayerH = $('.dv-box .preview-video').outerHeight();
			
			var previewPlayerW = $('.dv-box').width() - ($('.dv-accounts').outerWidth() + $('.dv-listings').outerWidth());
			
			var hdW = 1280;
			var hdH = 720;
			
			var previewPlayerH = previewPlayerW * hdH / hdW; // hard set because the div doesn't exists before a video is launched

			previewPlayerH = Math.round(previewPlayerH);			
			
			var commonH = winH;
			
			var previewInjectH = commonH - topH - bottomH;

			var previewDescriptionH = commonH - topH - previewPlayerH - bottomH; 
			
			var previewPlayerPercentH =  previewPlayerH * 100 / (previewPlayerH + previewDescriptionH);
			
			
			if(previewPlayerPercentH > 60)
			{
				previewPlayerH = 60 * (previewPlayerH + previewDescriptionH) / 100;
				previewPlayerH = Math.round(previewPlayerH);
			}

			// recalculate description
			
			previewDescriptionH = commonH - topH - previewPlayerH - bottomH; 
			


			var fullscreenH = commonH - topH - bottomH;
			
			$('.dv-box .dv-box-in').css('height', commonH);
			$('.dv-box .dv-accounts').css('height', commonH);
			$('.dv-box .dv-listings').css('height', commonH);
			$('.dv-box .dv-preview').css('height', commonH);
			$('.dv-box .dv-preview-video, .dv-box .dv-preview-video iframe').css('height', previewPlayerH);
			$('.dv-box .dv-preview-video, .dv-box .dv-preview-video iframe').css('width', previewPlayerW);
			
			if(ipadfix_enabled)
			{
				var ipadfix = previewPlayerW - 200;
	
				$('.dv-box .dv-preview-video iframe').css('display', 'none');
				
				setTimeout(function() {		
					$('.dv-box .dv-preview-video iframe').css('width', ipadfix);
					
	
					setTimeout(function() {		
						$('.dv-box .dv-preview-video iframe').css('width', previewPlayerW);
						
						$('.dv-box .dv-preview-video iframe').css('display', 'block');
					}, 400);				
				}, 100);
			}		


			// $('.dv-box .preview iframe').css('width', '200');	
			
			$('.dv-box .dv-fullscreen .dv-box .dv-preview-video, .dv-box .dv-fullscreen .dv-box .dv-preview-video iframe').css('height', fullscreenH);
			
			$('.dv-box .dv-preview-inject').css('height', previewInjectH);
			$('.dv-box .dv-preview-description').css('height', previewDescriptionH);
			$('.dv-box .dv-videos').css('height', previewInjectH);


			dukt_videos_box.lightbox.resize();
		}
	};
	
	
	/* -------------------------------------------------------------------- */
	
	/**
	* Accounts
	*
	*/
	dukt_videos_box.box.accounts = function()
	{
		dukt_log('dukt_videos_box.listings.accounts()');

		$('.dv-box .dv-close').live('click', function(){
	
			dukt_videos_box.lightbox.hide();
	
		});
	
		// services
	
		$('.dv-box .dv-accounts > ul > li > a').live('click', function() {
	
			if($(this).parent().hasClass('selected'))
			{
				return false;
			}
	
	
			var el = $(this);
	
			current_method = $('.dv-box .dv-accounts ul ul > li a.selected').data('method');
	
			$('.dv-box .dv-accounts > ul > li').removeClass('selected');
			$('.dv-box .dv-accounts ul ul > li a').removeClass('selected');
	
			$(this).parent().addClass('selected');
	
			method_found = false;
	
			$(this).parent().find('ul > li a').each(function() {
				if($(this).data('method') == current_method)
				{
					method_found = true;
					$(this).trigger('click');
	
				}
			});
	
			if(!method_found)
			{
				$(this).parent().find('ul > li:first-child a').addClass('selected');
	
				q = $($('.dv-box .dv-search input')[0]).attr('value');
	
	
				// ajax browse to account
				
				var data = {
					method: 'service_search',
					service:dukt_videos_box.utils.current_service(),
					site_id: Dukt_videos.site_id,
					q: q
				}
	
				//dukt_videos_box.browser.go(data, 'videos');
			}
	
			$('.dv-box .dv-accounts > ul > li').each(function() {
				if(!el.hasClass('selected'))
				{
					$(this).parent().find('ul').slideUp({easing:'easeOutCubic', duration:400});
				}
			});
	
			$(this).parent().find('ul').slideDown({easing:'easeOutCubic', duration:400});
		});
	
	
		// services listings init ajax calls
		
		setTimeout(function() {
			$('.dv-box .dv-accounts ul ul > li > a').each(function(i, el)
			{
	
				var listing = $(el).data('listing');
				var service = $(el).data('service');
				var method = $(el).data('method');
	
				data = {
					'method': 	method,
					'site_id': 	Dukt_videos.site_id,
					'service':	service
				};
		
				var listing_view = false;
		
				$('.dv-box .dv-listing-view').each(function(i, el) {
					if($(el).data('listing') == listing && $(el).data('service') == service)
					{
						listing_view = $(el);
					}
				});
				
				// add request to stack
				
				var k = $(el).data('service') + $(el).data('listing');
	
				dukt_videos_box.ajax_stack.add(k, function() {
					return $.ajax({
						url: Dukt_videos.ajax_endpoint,
						type:"post",
						data : data,
						
						beforeSend:function()
						{
							$('.dv-videos', listing_view).addClass('dv-loading');
							// $('.dv-box .videos-empty', listing_view).css('display', 'none');
						},
						
						success: function( data ) {
	
							// remove request from stack
		
							dukt_videos_box.ajax_stack.remove(k);
	
							var myel = $('.dv-videos-inject', listing_view);
	
							myel.html(data);
							
							$('.dv-videos', listing_view).removeClass('dv-loading');
						}
					});
				});
				
			});
		}, 500);

		
		
		// clicking a listing option
	
		$('.dv-box .dv-accounts ul ul > li > a').live('click', function() {
		
			// selected button
			
			if($(this).hasClass('selected'))
			{
				var listing = $(this).data('listing');
				var service = $(this).data('service');
				var method = $(this).data('method');
				
				dukt_videos_box.listings.reload(listing, service, method);
			}
			
			$('.dv-box .dv-accounts ul ul > li > a').removeClass('selected');
			
			$(this).addClass('selected');
			
			
			var listing = $(this).data('listing');
			var service = $(this).data('service');
			var method = $(this).data('method');
			
			var listing_view = false;
			
			$('.dv-box .dv-listing-view').css('display', 'none');
			
			$('.dv-box .dv-listing-view').each(function(i, el) {
				if($(el).data('listing') == listing && $(el).data('service') == service)
				{
					listing_view = $(el);
					listing_view.css('display', 'block');
				}
			});
			
			return false;
		});
	
	
		// fire out some stuff at init
		
		// hide submenus
		
		$('.dv-box .dv-accounts li ul').css('display', 'none');
		
		$('.dv-box .dv-services > li:first-child > a').trigger('click');
	
		$('.dv-box .dv-accounts > ul > li').each(function() {
			if($(this).hasClass('selected'))
			{
				$(this).find('ul').slideDown({easing:'easeOutCubic', duration:400});
				$(this).find('ul li:first-child a').trigger('click');
			}
		});
	
	};
	
	/* -------------------------------------------------------------------- */
	
	/**
	* Videos
	*
	*/
	dukt_videos_box.box.videos = function() {
		
		// FOR COL LISTINGS
		
			// clickable video list
		
			$('.dv-listings .dv-videos li').live('click', function() {
	
				if($(this).hasClass('dv-videos-more'))
				{
					return false;
				}
		
				$('.dv-preview .dv-preview-inject').html('');
		
				$('.dv-listings .dv-videos li').removeClass('selected');
		
				$(this).addClass('selected');
		
		
				// ajax
		
				var data = {
					method : 'box_preview',
					service : dukt_videos_box.utils.current_service(),
					site_id : Dukt_videos.site_id,
					video_page : $(this).data('video-page'),
					autoplay : 1,
					rel : 0
				}
		
				$('.dv-preview').data('video-page', $(this).data('video-page'));
	
				$('.dv-preview .dv-controls').css('display', 'block');
		
				dukt_videos_box.browser.go(data, 'preview', function() {
		
					// init favorite button
					$('.dv-preview .dv-controls').css('display', 'block');
	
					// http://stackoverflow.com/questions/6980677/check-if-iframe-is-loaded-one-time-jquery
							
					dukt_videos_box.box.resize(true);
	
	
				});
			});
			

		
			// load more video when scrolled to absolute bottom (for search listings only ?)
		
			$('.dv-listings li.dv-videos-more').live('click', function() {
			
				var currentLI = $(this);
				var parentUL = $(this).parent();
				var listingView = $(currentLI).parents('.dv-listing-view');

				if($('.dv-videos-more-btn', currentLI).css('display') != "none")
				{
					$('.dv-videos-more-btn', currentLI).css('display', 'none');
					$('.dv-videos-more-loading', currentLI).css('display', 'inline');
		
					var q = $('.dv-search input', listingView).attr('value');
		
					var data = {
						action: 'dukt_videos',
						method: $('.dv-box .dv-services > li.selected li a.selected').data('method'),
						service:dukt_videos_box.utils.current_service(),
						site_id: Dukt_videos.site_id,
						q: q,
						page: $(this).data('next-page')
					};
		
					$.ajax({
					  url: Dukt_videos.ajax_endpoint,
					  type:"post",
					  data : data,
					  beforeSend:function()
					  {
					  },
					  success: function( data ) {
		
						currentLI.remove();
		
						var html_before = parentUL.html();
		
						var html = html_before + data;
		
						parentUL.html(html);
					  }
					});
				}
			});
		
/*
			$($('.dv-listings .dv-videos').get(0)).scroll(function(eventData) {
				scrollDifference = $(this).get(0).scrollHeight - $(this).scrollTop();
		
				if(scrollDifference == $(this).height())
				{
					$('.dv-listings .dv-videos-more').trigger('click');
				}
			});
*/
			
			$('.dv-listings .dv-videos').scroll(function(eventData) {
				var scrollHeight = $(this).get(0).scrollHeight;
				var scrollTop = $(this).scrollTop();
				
				scrollDifference = scrollHeight - scrollTop;
		
				if(scrollDifference == $(this).height())
				{
					$('.dv-videos-more', this).trigger('click');
				}
			});
		
		
		// FOR COL PREVIEW
		
			// set as favorite
	
			$('.dv-preview .dv-preview-favorite').live('click', function() {
	
				favorite_enabled = false;
				if($(this).hasClass('dv-preview-favorite-selected'))
				{
				  	$(this).removeClass('dv-preview-favorite-selected');
				}
				else
				{
				  	$(this).addClass('dv-preview-favorite-selected');
				  	favorite_enabled = true;
				}
				
				var service = $(this).data('service');
	
				// ajax browse to account
				var data = {
					action: 'dukt_videos',
					method: 'favorite',
					service:service,
					site_id: Dukt_videos.site_id,
					video_page:$('.dv-box .dv-preview').data('video-page'),
					favorite_enabled:favorite_enabled
				}
		
				var method = 'service_favorites';
				
				$.ajax({
				  url: Dukt_videos.ajax_endpoint,
				  type:"post",
				  data : data,
				  success: function( data ) {
				  	dukt_videos_box.listings.reload('favorites', service, method);
		
				  }
				});
		
			});
			
			// fullscreen mode
		
			$('.dv-box .dv-preview-fullscreen').live('click', function() {
				if($('.dv-box .dv-box-in').hasClass('dv-fullscreen'))
				{
					$('.dv-box .dv-box-in').removeClass('dv-fullscreen');
	
					dukt_videos_box.box.resize();
				}
				else
				{
					$('.dv-box .dv-box-in').addClass('dukt-videos-fullscreen');
					
					dukt_videos_box.box.resize();
				}
			});

	};
	
	/* -------------------------------------------------------------------- */
	
	/**
	* Browser
	*
	*/
	dukt_videos_box.browser = {
	
		current_request : [],
	
		go:function(data, frame, callback)
		{
			dukt_log('dukt_videos_box.browser.go(data, frame, callback)', data, frame, callback);
			
			/*
			if(typeof(dukt_videos_box.browser.current_request[frame]) != "undefined")
			{
				if(dukt_videos_box.browser.current_request[frame] != false)
				{
					dukt_videos_box.browser.current_request[frame].abort();
			
					dukt_videos_box.browser.current_request[frame] = false;
				}
			}
			else
			{
				dukt_videos_box.browser.current_request[frame] = false;
			}
			*/
		
			data['action'] = 'dukt_videos';
			
			dukt_videos_box.browser.current_request[frame] = $.ajax({
			  url: Dukt_videos.ajax_endpoint,
			  type:"post",
			  data : data,
			  beforeSend:function()
			  {
			  	$('.dv-box .dv-'+frame).addClass('dv-frame-loading');
				// $('.dv-box .videos-empty').css('display', 'none');
			  },
			  success: function( data ) {
				  	dukt_log('dukt_videos_box.browser.go(data, frame, callback) success');
				  	dukt_videos_box.browser.current_request[frame] = false;
			
					$('.dv-box .dv-'+frame+'-inject').html(data);
				  	$('.dv-box .dv-'+frame).removeClass('dv-frame-loading');
	  				$('.dv-box .dv-search').removeClass('dv-loading');
	  				
	  				dukt_videos_box.box.resize();
			
					if(dukt_videos_box.search.timer){
						clearTimeout(dukt_videos_box.search.timer);
						dukt_videos_box.search.timer = false;
					}
			
			  		if(typeof(callback) == "function")
			  		{
			  			callback();
			  		}
			  }
			});
		},
		
		abort:function(frame)
		{
			dukt_log('dukt_videos_box.browser.abort(frame)', frame);
			
			if(typeof(dukt_videos_box.browser.current_request[frame]) != "undefined")
			{
				if(dukt_videos_box.browser.current_request[frame] != false)
				{
					dukt_videos_box.browser.current_request[frame].abort();
	
					dukt_videos_box.browser.current_request[frame] = false;
				}
			}
			else
			{
				dukt_videos_box.browser.current_request[frame] = false;
			}
		}
	};
	
	/* -------------------------------------------------------------------- */	
	
	/**
	* Search
	*
	*/
	dukt_videos_box.search = {
	
		textFocus: false,
	
		init:function()
		{
			var opts = {
			  lines: 13, // The number of lines to draw
			  length: 3, // The length of each line
			  width: 2, // The line thickness
			  radius: 4, // The radius of the inner circle
			  corners: 1, // Corner roundness (0..1)
			  rotate: 0, // The rotation offset
			  color: '#000', // #rgb or #rrggbb
			  speed: 2.2, // Rounds per second
			  trail: 60, // Afterglow percentage
			  shadow: false, // Whether to render a shadow
			  hwaccel: false, // Whether to use hardware acceleration
			  className: 'spinner', // The CSS class to assign to the spinner
			  zIndex: 2e9, // The z-index (defaults to 2000000000)
			  top: '0', // Top position relative to parent in px
			  left: '0' // Left position relative to parent in px
			};
			
			$('.dv-listings .dv-search .dv-spin').spin(opts);

			// search reset
	
			$('.dv-listings .dv-search-reset').live('click', function(){
				$('.dv-listings .dv-search input').attr('value', '');
				$(this).css('display', 'none');
				$('.dv-listings .dv-search input').trigger('keyup');
			});
	
	
			// live key watcher
		
			dukt_videos_box.search.timer = false;
		
			var abort = false;
	
			$('.dv-listings .dv-search input').live('keydown', function(e) {
	
				if(e.keyCode == 91) //command
				{
					abort=true;
				}
	
			}).live('keyup',
			
			function(e)
			{				
				$('.dv-accounts > ul > li.selected ul li a').removeClass('selected');
				$('.dv-accounts > ul > li.selected ul li:first-child a').addClass('selected');
	
				var el = $(this);
				var q = el.attr('value');
	
				if(q !== "")
				{
					$('.dv-listings .dv-search-reset').css('display', 'block');
				}
				else
				{
					$('.dv-listings .dv-search-reset').css('display', 'none');
				}
	

	
				if(abort == true && e.keyCode != 91)
				{
					abort = false;
					return false;
				}
	
	
				switch(e.keyCode)
				{
					case 91: // command
						abort = false;
						return false;
					break;
	
					case 18: // alt
					case 16: // shift
					case 37:
					case 38:
					case 39:
					case 40:
						return false;
					break;
				}
	
	
				if(dukt_videos_box.search.timer)
				{
					clearTimeout(dukt_videos_box.search.timer);
				}
	
				if(!dukt_videos_box.search.timer)
				{
	  				$('.dv-listings .dv-search').addClass('dv-loading');
				}
	
				// listing view
					
				var listing = $(this).data('listing');
				var service = $(this).data('service');
		
				var listing_view = false;
		
				$('.dv-listings .dv-listing-view').each(function(i, el) {
					if($(el).data('listing') == listing && $(el).data('service') == service)
					{
						listing_view = $(el);
					}
				});
				
	
				dukt_videos_box.search.timer = setTimeout(function()
				{	
					var data = {
						method: 'service_search',
						service:dukt_videos_box.utils.current_service(),
						site_id: Dukt_videos.site_id,
						q: q
					};
							
					$.ajax({
						url: Dukt_videos.ajax_endpoint,
						type:"post",
						data : data,
						
						beforeSend:function()
						{
			  				$('.dv-listings .dv-search').addClass('dv-loading');
							$('.dv-videos', listing_view).addClass('dv-loading');
						},
						
						success: function(data)
						{
							$('.dv-videos-inject', listing_view).html(data);
							
							$('.dv-listings .dv-search').removeClass('dv-loading');							
							$('.dv-videos', listing_view).removeClass('dv-loading');							
						}
					});
	
				}, 500);
			});
		}
	};
	
		$(document).ready(function() {
		dukt_videos_box.init();
	});
	

});

/* End of file box.js */
/* Location: ./dukt-videos-universal/resources/js/box.js */