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
 
dukt_log = function() {
	
	// Add a prefix to each log
	
	// Not working because of console array like object arguments
	// arguments.splice(0, 0, "dukt_videos");
	
	// Thanks for your help in dealing with...
	
	// array-like objects
	// http://www.hacksparrow.com/javascript-array-like-objects.html

	// array splice and apply()
	// http://stackoverflow.com/questions/1348178/a-better-way-to-splice-an-arrray-into-an-array-in-javascript	
	
	var slice_args = [0, 0].concat("dukt log : ");

	Array.prototype.splice.apply(arguments, slice_args);
	
	
	// apply arguments to the log
	
	if(typeof(dukt_debug) != 'undefined')
	{
	    if(console && dukt_debug){
	        console.debug.apply(console, arguments);
	    }
    }
};

/* End of file dukt_log.js */
/* Location: ./dukt-videos-universal/resources/js/dukt_log.js */