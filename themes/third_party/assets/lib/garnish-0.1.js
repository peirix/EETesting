/*!
 * Garnish UI toolkit
 *
 * @copyright 2013 Pixel & Tonic, Inc.. All rights reserved.
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @version   0.1
 */
(function($){

/*!
	Base.js, version 1.1a
	Copyright 2006-2010, Dean Edwards
	License: http://www.opensource.org/licenses/mit-license.php
*/

var Base = function() {
	// dummy
};

Base.extend = function(_instance, _static) { // subclass
	var extend = Base.prototype.extend;
	
	// build the prototype
	Base._prototyping = true;
	var proto = new this;
	extend.call(proto, _instance);
  proto.base = function() {
    // call this method from any other method to invoke that method's ancestor
  };
	delete Base._prototyping;
	
	// create the wrapper for the constructor function
	//var constructor = proto.constructor.valueOf(); //-dean
	var constructor = proto.constructor;
	var klass = proto.constructor = function() {
		if (!Base._prototyping) {
			if (this._constructing || this.constructor == klass) { // instantiation
				this._constructing = true;
				constructor.apply(this, arguments);
				delete this._constructing;
			} else if (arguments[0] != null) { // casting
				return (arguments[0].extend || extend).call(arguments[0], proto);
			}
		}
	};
	
	// build the class interface
	klass.ancestor = this;
	klass.extend = this.extend;
	klass.forEach = this.forEach;
	klass.implement = this.implement;
	klass.prototype = proto;
	klass.toString = this.toString;
	klass.valueOf = function(type) {
		//return (type == "object") ? klass : constructor; //-dean
		return (type == "object") ? klass : constructor.valueOf();
	};
	extend.call(klass, _static);
	// class initialisation
	if (typeof klass.init == "function") klass.init();
	return klass;
};

Base.prototype = {
	extend: function(source, value) {
		if (arguments.length > 1) { // extending with a name/value pair
			var ancestor = this[source];
			if (ancestor && (typeof value == "function") && // overriding a method?
				// the valueOf() comparison is to avoid circular references
				(!ancestor.valueOf || ancestor.valueOf() != value.valueOf()) &&
				/\bbase\b/.test(value)) {
				// get the underlying method
				var method = value.valueOf();
				// override
				value = function() {
					var previous = this.base || Base.prototype.base;
					this.base = ancestor;
					var returnValue = method.apply(this, arguments);
					this.base = previous;
					return returnValue;
				};
				// point to the underlying method
				value.valueOf = function(type) {
					return (type == "object") ? value : method;
				};
				value.toString = Base.toString;
			}
			this[source] = value;
		} else if (source) { // extending with an object literal
			var extend = Base.prototype.extend;
			// if this object has a customised extend method then use it
			if (!Base._prototyping && typeof this != "function") {
				extend = this.extend || extend;
			}
			var proto = {toSource: null};
			// do the "toString" and other methods manually
			var hidden = ["constructor", "toString", "valueOf"];
			// if we are prototyping then include the constructor
			var i = Base._prototyping ? 0 : 1;
			while (key = hidden[i++]) {
				if (source[key] != proto[key]) {
					extend.call(this, key, source[key]);

				}
			}
			// copy each of the source object's properties to this object
			for (var key in source) {
				if (!proto[key]) extend.call(this, key, source[key]);
			}
		}
		return this;
	}
};

// initialise
Base = Base.extend({
	constructor: function() {
		this.extend(arguments[0]);
	}
}, {
	ancestor: Object,
	version: "1.1",
	
	forEach: function(object, block, context) {
		for (var key in object) {
			if (this.prototype[key] === undefined) {
				block.call(context, object[key], key, object);
			}
		}
	},
		
	implement: function() {
		for (var i = 0; i < arguments.length; i++) {
			if (typeof arguments[i] == "function") {
				// if it's a function, call it
				arguments[i](this.prototype);
			} else {
				// add the interface using the extend method
				this.prototype.extend(arguments[i]);
			}
		}
		return this;
	},
	
	toString: function() {
		return String(this.valueOf());
	}
});


/*!
 * Garnish
 */

// Bail if Garnish is already defined
if (typeof Garnish != 'undefined')
{
	throw 'Garnish is already defined!';
}


var isMobileBrowser;


Garnish = {

	// jQuery objects for common elements
	$win: $(window),
	$doc: $(document),
	$bod: $(document.body),

	// Key code constants
	DELETE_KEY:  8,
	SHIFT_KEY:  16,
	CTRL_KEY:   17,
	ALT_KEY:    18,
	RETURN_KEY: 13,
	ESC_KEY:    27,
	SPACE_KEY:  32,
	LEFT_KEY:   37,
	UP_KEY:     38,
	RIGHT_KEY:  39,
	DOWN_KEY:   40,
	A_KEY:      65,
	CMD_KEY:    91,

	// Mouse button constants
	PRIMARY_CLICK:   0,
	SECONDARY_CLICK: 2,

	// Axis constants
	X_AXIS: 'x',
	Y_AXIS: 'y',

	// Node types
	TEXT_NODE: 3,

	/**
	 * Logs a message to the browser's console, if the browser has one.
	 *
	 * @param string msg
	 */
	log: function(msg)
	{
		if (typeof console != 'undefined' && typeof console.log == 'function')
		{
			console.log(msg);
		}
	},

	/**
	 * Returns whether this is a mobile browser.
	 * Detection script courtesy of http://detectmobilebrowsers.com
	 *
	 * @return bool
	 */
	isMobileBrowser: function()
	{
		if (typeof isMobileBrowser == 'undefined')
		{
			var a = navigator.userAgent || navigator.vendor || window.opera;
			isMobileBrowser = (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)));
		}

		return isMobileBrowser;
	},

	/**
	 * Returns whether a variable is an array.
	 *
	 * @param mixed val
	 * @return bool
	 */
	isArray: function(val)
	{
		return (val instanceof Array);
	},

	/**
	 * Returns whether a variable is a jQuery collection.
	 *
	 * @param mixed val
	 * @return bool
	 */
	isJquery: function(val)
	{
		return (val instanceof jQuery);
	},

	/**
	 * Returns whether a variable is a plain object (not an array, element, or jQuery collection).
	 *
	 * @param mixed val
	 * @return bool
	 */
	isObject: function(val)
	{
		return (typeof val == 'object' && !Garnish.isArray(val) && !Garnish.isJquery(val) && typeof val.nodeType == 'undefined');
	},

	/**
	 * Returns whether a variable is a string.
	 *
	 * @param mixed val
	 * @return bool
	 */
	isString: function(val)
	{
		return (typeof val == 'string');
	},

	/**
	 * Returns whether something is a text node.
	 *
	 * @param object elem
	 * @return bool
	 */
	isTextNode: function(elem)
	{
		return (elem.nodeType == Garnish.TEXT_NODE);
	},

	/**
	 * Returns the distance between two coordinates.
	 *
	 * @param int x1 The first coordinate's X position.
	 * @param int y1 The first coordinate's Y position.
	 * @param int x2 The second coordinate's X position.
	 * @param int y2 The second coordinate's Y position.
	 * @return float
	 */
	getDist: function(x1, y1, x2, y2)
	{
		return Math.sqrt(Math.pow(x1-x2, 2) + Math.pow(y1-y2, 2));
	},

	/**
	 * Returns whether an element is touching an x/y coordinate.
	 *
	 * @param int    x    The coordinate's X position.
	 * @param int    y    The coordinate's Y position.
	 * @param object elem Either an actual element or a jQuery collection.
	 * @return bool
	 */
	hitTest: function(x, y, elem)
	{
		Garnish.hitTest._$elem = $(elem),
		Garnish.hitTest._offset = Garnish.hitTest._$elem.offset(),
		Garnish.hitTest._x1 = Garnish.hitTest._offset.left,
		Garnish.hitTest._y1 = Garnish.hitTest._offset.top,
		Garnish.hitTest._x2 = Garnish.hitTest._x1 + Garnish.hitTest._$elem.outerWidth(),
		Garnish.hitTest._y2 = Garnish.hitTest._y1 + Garnish.hitTest._$elem.outerHeight();

		return (x >= Garnish.hitTest._x1 && x < Garnish.hitTest._x2 && y >= Garnish.hitTest._y1 && y < Garnish.hitTest._y2);
	},

	/**
	 * Returns whether the cursor is touching an element.
	 *
	 * @param object ev   The mouse event object containing pageX and pageY properties.
	 * @param object elem Either an actual element or a jQuery collection.
	 * @return bool
	 */
	isCursorOver: function(ev, elem)
	{
		return Garnish.hitTest(ev.pageX, ev.pageY, elem);
	},

	/**
	 * Copies text styles from one element to another.
	 *
	 * @param object source The source element. Can be either an actual element or a jQuery collection.
	 * @param object target The target element. Can be either an actual element or a jQuery collection.
	 */
	copyTextStyles: function(source, target)
	{
		var $source = $(source),
			$target = $(target);

		$target.css({
			lineHeight:    $source.css('lineHeight'),
			fontSize:      $source.css('fontSize'),
			fontFamily:    $source.css('fontFamily'),
			fontWeight:    $source.css('fontWeight'),
			letterSpacing: $source.css('letterSpacing'),
			textAlign:     $source.css('textAlign')
		});
	},

	/**
	 * Returns the body's real scrollTop, discarding any window banding in Safari.
	 *
	 * @return int
	 */
	getBodyScrollTop: function()
	{
		Garnish.getBodyScrollTop._scrollTop = document.body.scrollTop;

		if (Garnish.getBodyScrollTop._scrollTop < 0)
		{
			Garnish.getBodyScrollTop._scrollTop = 0;
		}
		else
		{
			Garnish.getBodyScrollTop._maxScrollTop = Garnish.$bod.outerHeight() - Garnish.$win.height();

			if (Garnish.getBodyScrollTop._scrollTop > Garnish.getBodyScrollTop._maxScrollTop)
			{
				Garnish.getBodyScrollTop._scrollTop = Garnish.getBodyScrollTop._maxScrollTop;
			}
		}

		return Garnish.getBodyScrollTop._scrollTop;
	},

	/**
	 * Scrolls a container element to an element within it.
	 *
	 * @param object container Either an actual element or a jQuery collection.
	 * @param object elem      Either an actual element or a jQuery collection.
	 */
	scrollContainerToElement: function(container, elem)
	{
		var $container = $(container),
			$elem = $(elem);

		var scrollTop = $container.scrollTop(),
				elemOffset = $elem.offset().top;

		if ($container[0] == window)
		{
			var elemScrollOffset = elemOffset - scrollTop;
		}
		else
		{
			var elemScrollOffset = elemOffset - $container.offset().top;
		}

		// Is the element above the fold?
		if (elemScrollOffset < 0)
		{
			$container.scrollTop(scrollTop + elemScrollOffset);
		}
		else
		{
			var elemHeight = $elem.outerHeight(),
				containerHeight = ($container[0] == window ? window.innerHeight : $container[0].clientHeight);

			// Is it below the fold?
			if (elemScrollOffset + elemHeight > containerHeight)
			{
				$container.scrollTop(scrollTop + (elemScrollOffset - (containerHeight - elemHeight)));
			}
		}
	},

	SHAKE_STEPS: 10,
	SHAKE_STEP_DURATION: 25,

	/**
	 * Shakes an element.
	 *
	 * @param mixed  elem Either an actual element or a jQuery collection.
	 * @param string prop The property that should be adjusted (default is 'margin-left').
	 */
	shake: function(elem, prop)
	{
		var $elem = $(elem);

		if (!prop)
		{
			prop = 'margin-left';
		}

		var startingPoint = parseInt($elem.css(prop));
		if (isNaN(startingPoint))
		{
			startingPoint = 0;
		}

		for (var i = 0; i <= Garnish.SHAKE_STEPS; i++)
		{
			(function(i)
			{
				setTimeout(function()
				{
					Garnish.shake._properties = {};
					Garnish.shake._properties[prop] = startingPoint + (i % 2 ? -1 : 1) * (10-i);
					$elem.animate(Garnish.shake._properties, Garnish.SHAKE_STEP_DURATION);
				}, (Garnish.SHAKE_STEP_DURATION * i));
			})(i);
		}
	},

	/**
	 * Returns the first element in an array or jQuery collection.
	 *
	 * @param mixed elem
	 * @return mixed
	 */
	getElement: function(elem)
	{
		return $.makeArray(elem)[0];
	},

	/**
	 * Returns the beginning of an input's name= attribute value with any [bracktes] stripped out.
	 *
	 * @param object elem
	 * @return string|null
	 */
	getInputBasename: function(elem)
	{
		var name = $(elem).attr('name');

		if (name)
		{
			return name.replace(/\[.*/, '');
		}
		else
		{
			return null;
		}
	},

	/**
	 * Returns an input's value as it would be POSTed.
	 * So unchecked checkboxes and radio buttons return null,
	 * and multi-selects whose name don't end in "[]" only return the last selection
	 *
	 * @param jQuery $input
	 * @return mixed
	 */
	getInputPostVal: function($input)
	{
		var type = $input.attr('type'),
			val  = $input.val();

		// Is this an unchecked checkbox or radio button?
		if ((type == 'checkbox' || type == 'radio'))
		{
			if ($input.prop('checked'))
			{
				return val;
			}
			else
			{
				return null;
			}
		}

		// Flatten any array values whose input name doesn't end in "[]"
		//  - e.g. a multi-select
		else if (Garnish.isArray(val) && $input.attr('name').substr(-2) != '[]')
		{
			if (val.length)
			{
				return val[val.length-1];
			}
			else
			{
				return null;
			}
		}

		// Just return the value
		else
		{
			return val;
		}
	},

	/**
	 * Returns the inputs within a container
	 *
	 * @param mixed container The container element. Can be either an actual element or a jQuery collection.
	 * @return jQuery
	 */
	findInputs: function(container)
	{
		return $(container).find('input,text,textarea,select,button');
	},

	/**
	 * Returns the post data within a container.
	 *
	 * @param mixed container
	 * @return array
	 */
	getPostData: function(container)
	{
		var postData = {},
			arrayInputCounters = {},
			$inputs = Garnish.findInputs(container);

		for (var i = 0; i < $inputs.length; i++)
		{
			var $input = $($inputs[i]);

			var inputName = $input.attr('name');
			if (!inputName)
			{
				continue;
			}

			var inputVal = Garnish.getInputPostVal($input);
			if (inputVal === null)
			{
				continue;
			}

			var isArrayInput = (inputName.substr(-2) == '[]');

			if (isArrayInput)
			{
				// Get the cropped input name
				var croppedName = inputName.substring(0, inputName.length-2);

				// Prep the input counter
				if (typeof arrayInputCounters[croppedName] == 'undefined')
				{
					arrayInputCounters[croppedName] = 0;
				}
			}

			if (!Garnish.isArray(inputVal))
			{
				inputVal = [inputVal];
			}

			for (var j = 0; j < inputVal.length; j++)
			{
				if (isArrayInput)
				{
					var inputName = croppedName+'['+arrayInputCounters[croppedName]+']';
					arrayInputCounters[croppedName]++;
				}

				postData[inputName] = inputVal[j];
			}
		}

		return postData;
	}
};


/**
 * Garnish base class
 */
Garnish.Base = Base.extend({

	settings: null,

	_namespace: null,
	_$listeners: null,

	constructor: function()
	{
		this._namespace = '.Garnish'+Math.floor(Math.random()*1000000000);
		this._$listeners = $();
		this.init.apply(this, arguments);
	},

	init: $.noop,

	setSettings: function(settings, defaults)
	{
		var baseSettings = (typeof this.settings == 'undefined' ? {} : this.settings);
		this.settings = $.extend(baseSettings, defaults, settings);
	},

	_formatEvents: function(events)
	{
		if (typeof events == 'string')
		{
			events = events.split(',');

			for (var i = 0; i < events.length; i++)
			{
				events[i] = $.trim(events[i]);
			}
		}

		for (var i = 0; i < events.length; i++)
		{
			events[i] += this._namespace;
		}

		return events.join(' ');
	},

	addListener: function(elem, events, func)
	{
		var $elem = $(elem);
		events = this._formatEvents(events);

		if (typeof func == 'function')
		{
			func = $.proxy(func, this);
		}
		else
		{
			func = $.proxy(this, func);
		}

		$elem.on(events, func);

		// Remember that we're listening to this element
		this._$listeners = this._$listeners.add(elem);

		// Prep for activate event?
		if (events.search(/\bactivate\b/) != -1)
		{
			if (!$elem.data('activatable'))
			{
				var activateNamespace = this._namespace+'-activate';

				// Prevent buttons from getting focus on click
				$elem.on('mousedown'+activateNamespace, function(ev)
				{
					ev.preventDefault();
				});

				$elem.on('click'+activateNamespace, function(ev)
				{
					ev.preventDefault();

					var elemIndex = $.inArray(ev.currentTarget, $elem),
						$evElem = $(elem[elemIndex]);

					if (!$evElem.hasClass('disabled'))
					{
						$evElem.trigger('activate');
					}
				});

				$elem.on('keydown'+activateNamespace, function(ev)
				{
					var elemIndex = $.inArray(ev.currentTarget, $elem);
					if (elemIndex != -1 && ev.keyCode == Garnish.SPACE_KEY)
					{
						ev.preventDefault();
						var $evElem = $($elem[elemIndex]);

						if (!$evElem.hasClass('disabled'))
						{
							$evElem.addClass('active');

							Garnish.$doc.on('keyup'+activateNamespace, function(ev)
							{
								$elem.removeClass('active');
								if (ev.keyCode == Garnish.SPACE_KEY)
								{
									ev.preventDefault();
									$evElem.trigger('activate');
								}
								Garnish.$doc.off('keyup'+activateNamespace);
							});
						}
					}
				});

				if (!$elem.hasClass('disabled'))
				{
					$elem.attr('tabindex', '0');
				}
				else
				{
					$elem.removeAttr('tabindex');
				}

				$elem.data('activatable', true);
			}

		}
	},

	removeListener: function(elem, events)
	{
		events = this._formatEvents(events);
		$(elem).off(events);
	},

	removeAllListeners: function(elem)
	{
		$(elem).off(this._namespace);
	},

	destroy: function()
	{
		this.removeAllListeners(this._$listeners);
	}
});


/**
 * Base drag class
 *
 * Does all the grunt work for manipulating elements via click-and-drag,
 * while leaving the actual element manipulation up to a subclass.
 */
Garnish.BaseDrag = Garnish.Base.extend({

	$items: null,

	dragging: false,

	mousedownX: null,
	mousedownY: null,
	mouseDistX: null,
	mouseDistY: null,
	$targetItem: null,
	targetItemMouseDiffX: null,
	targetItemMouseDiffY: null,
	mouseX: null,
	mouseY: null,
	lastMouseX: null,
	lastMouseY: null,

	/**
	 * Init
	 */
	init: function(items, settings)
	{
		// Param mapping
		if (!settings && Garnish.isObject(items))
		{
			// (settings)
			settings = items;
			items = null;
		}

		this.settings = $.extend({}, Garnish.BaseDrag.defaults, settings);

		this.$items = $();

		if (items) this.addItems(items);
	},

	/**
	 * On Mouse Down
	 */
	onMouseDown: function(ev)
	{
		// Ignore right clicks
		if (ev.button != Garnish.PRIMARY_CLICK)
		{
			return;
		}

		// ignore if we already have a target
		if (this.$targetItem) return;

		// Make sure the target isn't a button (unless the button is the handle)
		if (this.settings.ignoreButtons && ev.currentTarget != ev.target)
		{
			var $target = $(ev.target);
			if ($target.hasClass('btn') || $target.closest('.btn').length)
			{
				return;
			}
		}

		ev.preventDefault();

		// capture the target
		this.$targetItem = $($.data(ev.currentTarget, 'drag-item'));

		// capture the current mouse position
		this.mousedownX = this.mouseX = ev.pageX;
		this.mousedownY = this.mouseY = ev.pageY;

		// capture the difference between the mouse position and the target item's offset
		var offset = this.$targetItem.offset();
		this.targetItemMouseDiffX = ev.pageX - offset.left;
		this.targetItemMouseDiffY = ev.pageY - offset.top;

		// listen for mousemove, mouseup
		this.addListener(Garnish.$doc, 'mousemove', 'onMouseMove');
		this.addListener(Garnish.$doc, 'mouseup', 'onMouseUp');
	},

	/**
	 * On Moues Move
	 */
	onMouseMove: function(ev)
	{
		ev.preventDefault();

		if (this.settings.axis != Garnish.Y_AXIS) this.mouseX = ev.pageX;
		if (this.settings.axis != Garnish.X_AXIS) this.mouseY = ev.pageY;

		this.mouseDistX = this.mouseX - this.mousedownX;
		this.mouseDistY = this.mouseY - this.mousedownY;

		if (!this.dragging)
		{
			// Has the mouse moved far enough to initiate dragging yet?
			this.onMouseMove._mouseDist = Garnish.getDist(this.mousedownX, this.mousedownY, this.mouseX, this.mouseY);
			if (this.onMouseMove._mouseDist >= Garnish.BaseDrag.minMouseDist)
			{
				this.startDragging();
			}
			else
			{
				return;
			}
		}

		this.onDrag();
	},

	/**
	 * On Moues Up
	 */
	onMouseUp: function(ev)
	{
		// unbind the document events
		this.removeAllListeners(Garnish.$doc);

		if (this.dragging)
		{
			this.stopDragging();
		}

		this.$targetItem = null;
	},

	/**
	 * Start Dragging
	 */
	startDragging: function()
	{
		this.dragging = true;
		this.onDragStart();
	},

	/**
	 * Stop Dragging
	 */
	stopDragging: function()
	{
		this.dragging = false;

		this.onDragStop();
	},

	/**
	 * On Drag Start
	 */
	onDragStart: function()
	{
		this.settings.onDragStart();
	},

	/**
	 * On Drag
	 */
	onDrag: function()
	{
		this.settings.onDrag();
	},

	/**
	 * On Drag Stop
	 */
	onDragStop: function()
	{
		this.settings.onDragStop();
	},

	/**
	 * Add Items
	 */
	addItems: function(items)
	{
		items = $.makeArray(items);

		for (var i = 0; i < items.length; i++)
		{
			var item = items[i];

			// Make sure this element doesn't belong to another dragger
			if ($.data(item, 'drag'))
			{
				Garnish.log('Element was added to more than one dragger');
				$.data(item, 'drag').removeItems(item);
			}

			// Add the item
			$.data(item, 'drag', this);
			this.$items = this.$items.add(item);

			// Get the handle
			if (this.settings.handle)
			{
				if (typeof this.settings.handle == 'object')
				{
					var $handle = $(this.settings.handle);
				}
				else if (typeof this.settings.handle == 'string')
				{
					var $handle = $(item).find(this.settings.handle);
				}
				else if (typeof this.settings.handle == 'function')
				{
					var $handle = $(this.settings.handle(item));
				}
			}
			else
			{
				var $handle = $(item);
			}

			$.data(item, 'drag-handle', $handle);
			$handle.data('drag-item', item);
			this.addListener($handle, 'mousedown', 'onMouseDown');
		}
	},

	/**
	 * Remove Items
	 */
	removeItems: function(items)
	{
		items = $.makeArray(items);

		for (var i = 0; i < items.length; i++)
		{
			var item = items[i];

			// Make sure we actually know about this item
			var index = $.inArray(item, this.$items);
			if (index != -1)
			{
				var $handle = $.data(item, 'drag-handle');
				$handle.data('drag-item', null);
				$.data(item, 'drag', null);
				$.data(item, 'drag-handle', null);
				this.removeAllListeners($handle);
				this.$items.splice(index, 1);
			}
		}
	},

	/**
	 * Remove All Items
	 */
	removeAllItems: function()
	{
		for (var i = 0; i < this.$items.length; i++)
		{
			var item = this.$items[i],
				$handle = $.data(item, 'drag-handle');

			$.data(item, 'drag', null);

			if ($handle)
			{
				$.data(item, 'drag-handle', null);
				$handle.data('drag-item', null);
				this.removeAllListeners($handle);
			}
		}

		this.$items = $();
	}
},
{
	minMouseDist: 1,

	defaults: {
		handle: null,
		axis: null,
		ignoreButtons: true,

		onDragStart: $.noop,
		onDrag:      $.noop,
		onDragStop:  $.noop
	}
});



/**
 * Checkbox select class
 */
Garnish.CheckboxSelect = Garnish.Base.extend({

	$container: null,
	$all: null,
	$options: null,

	init: function(container)
	{
		this.$container = $(container);

		// Is this already a checkbox select?
		if (this.$container.data('checkboxSelect'))
		{
			Garnish.log('Double-instantiating a checkbox select on an element');
			this.$container.data('checkbox-select').destroy();
		}

		this.$container.data('checkboxSelect', this);

		var $checkboxes = this.$container.find('input');
		this.$all = $checkboxes.filter('.all:first');
		this.$options = $checkboxes.not(this.$all);

		this.addListener(this.$all, 'change', 'onAllChange');
	},

	onAllChange: function()
	{
		var isAllChecked = this.$all.prop('checked');

		this.$options.attr({
			checked:  isAllChecked,
			disabled: isAllChecked
		});
	}

});


/**
 * Context Menu
 */
Garnish.ContextMenu = Garnish.Base.extend({

	$target: null,
	options: null,
	$menu: null,
	showingMenu: false,

	/**
	 * Constructor
	 */
	init: function(target, options, settings)
	{
		this.$target = $(target);

		// Is this already a context menu target?
		if (this.$target.data('contextmenu'))
		{
			Garnish.log('Double-instantiating a context menu on an element');
			this.$target.data('contextmenu').destroy();
		}

		this.$target.data('contextmenu', this);

		this.options = options;
		this.setSettings(settings, Garnish.ContextMenu.defaults);

		Garnish.ContextMenu.counter++;

		this.enable();
	},

	/**
	 * Build Menu
	 */
	buildMenu: function()
	{
		this.$menu = $('<ul class="'+this.settings.menuClass+'" style="display: none" />');

		for (var i in this.options)
		{
			var option = this.options[i];

			if (option == '-')
			{
				$('<li class="'+this.settings.optionBreakClass+'"></li>').appendTo(this.$menu);
			}
			else
			{
				var $li = $('<li></li>').appendTo(this.$menu),
					$a = $('<a>'+option.label+'</a>').appendTo($li);

				if (typeof option.onClick == 'function')
				{
					// maintain the current $a and options.onClick variables
					(function($a, onClick)
					{
						setTimeout($.proxy(function(){
							$a.mousedown($.proxy(function(ev)
							{
								this.hideMenu();
								// call the onClick callback, with the scope set to the item,
								// and pass it the event with currentTarget set to the item as well
								onClick.call(this.currentTarget, $.extend(ev, { currentTarget: this.currentTarget }));
							}, this));
						}, this), 1);
					}).call(this, $a, option.onClick);
				}
			}
		}
	},

	/**
	 * Show Menu
	 */
	showMenu: function(ev)
	{
		// Ignore left mouse clicks
		if (ev.type == 'mousedown' && ev.button != Garnish.SECONDARY_CLICK)
		{
			return;
		}

		if (ev.type == 'contextmenu')
		{
			// Prevent the real context menu from showing
			ev.preventDefault();
		}

		// Ignore if already showing
		if (this.showing && ev.currentTarget == this.currentTarget)
		{
			return;
		}

		this.currentTarget = ev.currentTarget;

		if (! this.$menu)
		{
			this.buildMenu();
		}

		this.$menu.appendTo(document.body);
		this.$menu.show();
		this.$menu.css({ left: ev.pageX+1, top: ev.pageY-4 });

		this.showing = true;

		setTimeout($.proxy(function()
		{
			this.addListener(Garnish.$doc, 'mousedown', 'hideMenu');
		}, this), 0);
	},

	/**
	 * Hide Menu
	 */
	hideMenu: function()
	{
		this.removeListener(Garnish.$doc, 'mousedown');
		this.$menu.hide();
		this.showing = false;
	},

	/**
	 * Enable
	 */
	enable: function()
	{
		this.addListener(this.$target, 'contextmenu,mousedown', 'showMenu');
	},

	/**
	 * Disable
	 */
	disable: function()
	{
		this.removeListener(this.$target, 'contextmenu,mousedown');
	}

},
{
	defaults: {
		menuClass: 'contextmenu',
		optionBreakClass: 'contextmenu-break',
	},
	counter: 0
});


/**
 * Drag class
 *
 * Builds on the BaseDrag class by "picking up" the selceted element(s),
 * without worrying about what to do when an element is being dragged.
 */
Garnish.Drag = Garnish.BaseDrag.extend({

	$draggee: null,
	otherItems: null,
	totalOtherItems: null,
	helpers: null,
	helperTargets: null,
	helperPositions: null,
	helperLagIncrement: null,
	updateHelperPosInterval: null,

	/**
	 * init
	 */
	init: function(items, settings)
	{
		// Param mapping
		if (!settings && Garnish.isObject(items))
		{
			// (settings)
			settings = items;
			items = null;
		}

		settings = $.extend({}, Garnish.Drag.defaults, settings);
		this.base(items, settings);
	},

	/**
	 * On Drag Start
	 */
	onDragStart: function()
	{
		this.helpers = [];
		this.helperTargets = [];
		this.helperPositions = [];

		this.getDraggee();
		this.draggeeIndex = $.inArray(this.$draggee[0], this.$items);

		// save their display style (block/table-row) so we can re-apply it later
		this.draggeeDisplay = this.$draggee.css('display');

		this.createHelpers();

		// remove/hide the draggee
		if (this.settings.removeDraggee)
		{
			this.$draggee.hide();
		}
		else
		{
			this.$draggee.css('visibility', 'hidden');
		}

		this.lastMouseX = this.lastMouseY = null;

		// -------------------------------------------
		//  Deal with the remaining items
		// -------------------------------------------

		// create an array of all the other items
		this.otherItems = [];

		for (var i = 0; i < this.$items.length; i++)
		{
			var item = this.$items[i];

			if ($.inArray(item, this.$draggee) == -1)
			{
				this.otherItems.push(item);
			}
		};

		this.totalOtherItems = this.otherItems.length;

		// keep the helpers following the cursor, with a little lag to smooth it out
		this.helperLagIncrement = this.helpers.length == 1 ? 0 : Garnish.Drag.helperLagIncrementDividend / (this.helpers.length-1);
		this.updateHelperPosInterval = setInterval($.proxy(this, 'updateHelperPos'), Garnish.Drag.updateHelperPosInterval);

		this.base();
	},

	/**
	 * On Drag Stop
	 */
	onDragStop: function()
	{
		// clear the helper interval
		clearInterval(this.updateHelperPosInterval);

		this.base();
	},

	/**
	 * Get the draggee(s) based on the filter setting, with the clicked item listed first
	 */
	getDraggee: function()
	{
		switch (typeof this.settings.filter)
		{
			case 'function':
			{
				this.$draggee = this.settings.filter();
				break;
			}

			case 'string':
			{
				this.$draggee = this.$items.filter(this.settings.filter);
				break;
			}

			default:
			{
				this.$draggee = this.$targetItem;
			}
		}

		// put the target item in the front of the list
		this.$draggee = $([ this.$targetItem[0] ].concat(this.$draggee.not(this.$targetItem[0]).toArray()));
	},

	/**
	 * Creates helper clones of the draggee(s)
	 */
	createHelpers: function()
	{
		for (var i = 0; i < this.$draggee.length; i++)
		{
			var $draggee = $(this.$draggee[i]),
				$draggeeHelper = $draggee.clone();

			$draggeeHelper.css({
				width: $draggee.width(),
				height: $draggee.height(),
				margin: 0
			});

			if (typeof this.settings.helper == 'function')
				$draggeeHelper = this.settings.helper($draggeeHelper);
			else if (this.settings.helper)
				$draggeeHelper = $(this.settings.helper).append($draggeeHelper);

			$draggeeHelper.appendTo(Garnish.$bod);

			var helperPos = this.getHelperTarget(i);

			$draggeeHelper.css({
				position: 'absolute',
				top: helperPos.top,
				left: helperPos.left,
				zIndex: Garnish.Drag.helperZindex + this.$draggee.length - i,
				opacity: this.settings.helperOpacity
			});

			this.helperPositions[i] = {
				top:  helperPos.top,
				left: helperPos.left
			};

			this.helpers.push($draggeeHelper);
		}
	},

	/**
	 * Get the helper position for a draggee helper
	 */
	getHelperTarget: function(i)
	{
		return {
			left: this.mouseX - this.targetItemMouseDiffX + (i * Garnish.Drag.helperSpacingX),
			top:  this.mouseY - this.targetItemMouseDiffY + (i * Garnish.Drag.helperSpacingY)
		};
	},

	/**
	 * Update Helper Position
	 */
	updateHelperPos: function()
	{
		// has the mouse moved?
		if (this.mouseX !== this.lastMouseX || this.mouseY !== this.lastMouseY)
		{
			// get the new target helper positions
			for (this.updateHelperPos._i = 0; this.updateHelperPos._i < this.helpers.length; this.updateHelperPos._i++)
			{
				this.helperTargets[this.updateHelperPos._i] = this.getHelperTarget(this.updateHelperPos._i);
			}

			this.lastMouseX = this.mouseX;
			this.lastMouseY = this.mouseY;
		}

		// gravitate helpers toward their target positions
		for (this.updateHelperPos._j = 0; this.updateHelperPos._j < this.helpers.length; this.updateHelperPos._j++)
		{
			this.updateHelperPos._lag = Garnish.Drag.helperLagBase + (this.helperLagIncrement * this.updateHelperPos._j);

			this.helperPositions[this.updateHelperPos._j] = {
				left: this.helperPositions[this.updateHelperPos._j].left + ((this.helperTargets[this.updateHelperPos._j].left - this.helperPositions[this.updateHelperPos._j].left) / this.updateHelperPos._lag),
				top:  this.helperPositions[this.updateHelperPos._j].top  + ((this.helperTargets[this.updateHelperPos._j].top  - this.helperPositions[this.updateHelperPos._j].top) / this.updateHelperPos._lag)
			};

			this.helpers[this.updateHelperPos._j].css(this.helperPositions[this.updateHelperPos._j]);
		}
	},

	/**
	 * Return Helpers to Draggees
	 */
	returnHelpersToDraggees: function()
	{
		for (var i = 0; i < this.$draggee.length; i++)
		{
			var $draggee = $(this.$draggee[i]),
				$helper = this.helpers[i],
				draggeeOffset = $draggee.offset();

			// preserve $draggee and $helper for the end of the animation
			(
				function($draggee, $helper)
				{
					$helper.animate({left: draggeeOffset.left, top: draggeeOffset.top}, 'fast',
						function()
						{
							$draggee.css('visibility', 'visible');
							$helper.remove();
						}
					);
				}
			)($draggee, $helper);
		}
	}
},
{
	helperZindex: 1000,
	helperLagBase: 1,
	helperLagIncrementDividend: 1.5,
	updateHelperPosInterval: 20,
	helperSpacingX: 5,
	helperSpacingY: 5,

	defaults: {
		removeDraggee: false,
		helperOpacity: 1,
		helper: null
	}
});


/**
 * Drag-and-drop class
 *
 * Builds on the Drag class by allowing you to set up "drop targets"
 * which the dragged elemements can be dropped onto.
 */
Garnish.DragDrop = Garnish.Drag.extend({

	$dropTargets: null,
	$activeDropTarget: null,

	/**
	 * Constructor
	 */
	init: function(settings)
	{
		settings = $.extend({}, Garnish.DragDrop.defaults, settings);
		this.base(settings);
	},

	/**
	 * On Drag Start
	 */
	onDragStart: function()
	{
		if (this.settings.dropTargets)
		{
			if (typeof this.settings.dropTargets == 'function')
			{
				this.$dropTargets = $(this.settings.dropTargets());
			}
			else
			{
				this.$dropTargets = $(this.settings.dropTargets);
			}

			// ignore if an empty array
			if (!this.$dropTargets.length)
			{
				this.$dropTargets = null;
			}
		}

		this.$activeDropTarget = null;

		this.base();
	},

	/**
	 * On Drag
	 */
	onDrag: function()
	{
		if (this.$dropTargets)
		{
			this.onDrag._activeDropTarget = null;

			// is the cursor over any of the drop target?
			for (this.onDrag._i = 0; this.onDrag._i < this.$dropTargets.length; this.onDrag._i++)
			{
				this.onDrag._elem = this.$dropTargets[this.onDrag._i];

				if (Garnish.hitTest(this.mouseX, this.mouseY, this.onDrag._elem))
				{
					this.onDrag._activeDropTarget = this.onDrag._elem;
					break;
				}
			}

			// has the drop target changed?
			if (
				(this.$activeDropTarget && this.onDrag._activeDropTarget != this.$activeDropTarget[0]) ||
				(!this.$activeDropTarget && this.onDrag._activeDropTarget !== null)
			)
			{
				// was there a previous one?
				if (this.$activeDropTarget)
				{
					this.$activeDropTarget.removeClass(this.settings.activeDropTargetClass);
				}

				// remember the new one
				if (this.onDrag._activeDropTarget)
				{
					this.$activeDropTarget = $(this.onDrag._activeDropTarget).addClass(this.settings.activeDropTargetClass);
				}
				else
				{
					this.$activeDropTarget = null;
				}

				this.settings.onDropTargetChange(this.$activeDropTarget);
			}
		}

		this.base();
	},

	/**
	 * On Drag Stop
	 */
	onDragStop: function()
	{
		if (this.$dropTargets && this.$activeDropTarget)
		{
			this.$activeDropTarget.removeClass(this.settings.activeDropTargetClass);
		}

		this.base();
	},

	/**
	 * Fade Out Helpers
	 */
	fadeOutHelpers: function()
	{
		for (var i = 0; i < this.helpers.length; i++)
		{
			(function($draggeeHelper)
			{
				$draggeeHelper.fadeOut('fast', function() {
					$draggeeHelper.remove();
				});
			})(this.helpers[i]);
		}
	}
},
{
	defaults: {
		dropTargets: null,
		onDropTargetChange: $.noop,
		activeDropTargetClass: 'active'
	}
});


/**
 * Drag-to-move clas
 *
 * Builds on the BaseDrag class by simply moving the dragged element(s) along with the mouse.
 */
Garnish.DragMove = Garnish.BaseDrag.extend({

	onDrag: function(items, settings)
	{
		this.$targetItem.css({
			left: this.mouseX - this.targetItemMouseDiffX,
			top:  this.mouseY - this.targetItemMouseDiffY
		});
	}

});


/**
 * Drag-to-sort class
 *
 * Builds on the Drag class by allowing you to sort the elements amongst themselves.
 */
Garnish.DragSort = Garnish.Drag.extend({

	$heightedContainer: null,
	$insertion: null,
	$caboose: null,
	startDraggeeIndex: null,
	closestItem: null,

	/**
	 * Constructor
	 */
	init: function(items, settings)
	{
		// Param mapping
		if (!settings && Garnish.isObject(items))
		{
			// (settings)
			settings = items;
			items = null;
		}

		settings = $.extend({}, Garnish.DragSort.defaults, settings);
		this.base(items, settings);

		if (this.settings.caboose)
		{
			// is it a function?
			if (typeof this.settings.caboose == 'function')
			{
				this.$caboose = $(this.settings.caboose());
			}
			else
			{
				this.$caboose = $(this.settings.caboose);
			}
		}
	},

	/**
	 * On Drag Start
	 */
	onDragStart: function()
	{
		this.base();

		// add the caboose?
		if (this.settings.container && this.$caboose)
		{
			this.$caboose.appendTo(this.settings.container);
			this.otherItems.push(this.$caboose[0]);
			this.totalOtherItems++;
		}

		this.closestItem = null;
		this.setMidpoints();
		this.setInsertion();

		// -------------------------------------------
		//  Get the closest container that has a height
		// -------------------------------------------

		if (this.settings.container)
		{
			this.$heightedContainer = $(this.settings.container);

			while (! this.$heightedContainer.height())
			{
				this.$heightedContainer = this.$heightedContainer.parent();
			}
		}

		this.startDraggeeIndex = this.draggeeIndex;
	},

	/**
	 * Sets the insertion element
	 */
	setInsertion: function()
	{
		// get the insertion
		if (this.settings.insertion)
		{
			if (typeof this.settings.insertion == 'function')
			{
				this.$insertion = $(this.settings.insertion(this.$draggee));
			}
			else
			{
				this.$insertion = $(this.settings.insertion);
			}
		}
	},

	/**
	 * Sets the item midpoints up front so we don't have to keep checking on every mouse move
	 */
	setMidpoints: function()
	{
		for (var i = 0; i < this.totalOtherItems; i++)
		{
			var $item = $(this.otherItems[i]),
				offset = $item.offset();

			$item.data('midpoint', {
				left: offset.left + $item.outerWidth() / 2,
				top:  offset.top + $item.outerHeight() / 2
			});
		}
	},

	/**
	 * On Drag
	 */
	onDrag: function()
	{
		// if there's a container set, make sure that we're hovering over it
		if (this.$heightedContainer && !Garnish.hitTest(this.mouseX, this.mouseY, this.$heightedContainer))
		{
			if (this.closestItem)
			{
				this.closestItem = null;

				if (this.$insertion)
				{
					this.$insertion.remove();
				}
			}
		}
		else
		{
			// is there a new closest item?
			if (this.closestItem != (this.closestItem = this.getClosestItem()))
			{
				this.onInsertionPointChange();
			}
		}

		this.base();
	},

	/**
	 * Returns the closest item to the cursor.
	 */
	getClosestItem: function()
	{
		this.getClosestItem._closestItem = null;
		this.getClosestItem._closestItemMouseDiff = null;

		for (this.getClosestItem._i = 0; this.getClosestItem._i < this.totalOtherItems; this.getClosestItem._i++)
		{
			this.getClosestItem._$item = $(this.otherItems[this.getClosestItem._i]);
			this.getClosestItem._midpoint = this.getClosestItem._$item.data('midpoint');
			this.getClosestItem._mouseDiff = Garnish.getDist(this.getClosestItem._midpoint.left, this.getClosestItem._midpoint.top, this.mouseX, this.mouseY);

			if (this.getClosestItem._closestItem === null || this.getClosestItem._mouseDiff < this.getClosestItem._closestItemMouseDiff)
			{
				this.getClosestItem._closestItem = this.getClosestItem._$item[0];
				this.getClosestItem._closestItemMouseDiff = this.getClosestItem._mouseDiff;
			}
		}

		return this.getClosestItem._closestItem;
	},

	/**
	 * On Insertion Point Change
	 */
	onInsertionPointChange: function()
	{
		if (this.closestItem)
		{
			this.$draggee.insertBefore(this.closestItem);

			if (this.$insertion)
			{
				this.$insertion.insertBefore(this.closestItem);
			}
		}

		this.settings.onInsertionPointChange();
	},

	/**
	 * On Drag Stop
	 */
	onDragStop: function()
	{
		// remove the caboose
		if (this.$caboose)
		{
			this.$caboose.remove();
		}

		if (this.$insertion)
		{
			this.$insertion.remove();
		}

		// "show" the drag items, but make them invisible
		this.$draggee.css({
			display:    this.draggeeDisplay,
			visibility: 'hidden'
		});

		// return the helpers to the draggees
		this.returnHelpersToDraggees();

		this.base();

		// has the item actually moved?
		this.$items = $().add(this.$items);
		var newDraggeeIndex = $.inArray(this.$draggee[0], this.$items);
		if (this.startDraggeeIndex != newDraggeeIndex)
		{
			this.settings.onSortChange();
		}
	}
},
{
	defaults: {
		container: null,
		insertion: null,
		onInsertionPointChange: $.noop,
		onSortChange: $.noop
	}
});


/**
 * HUD
 */
Garnish.HUD = Garnish.Base.extend({

	/**
	 * Constructor
	 */
	init: function(trigger, contentsHtml, settings) {

		this.$trigger = $(trigger);
		this.setSettings(settings, Garnish.HUD.defaults);

		this.showing = false;

		this.$hud = $('<div class="'+this.settings.hudClass+'" />').appendTo(Garnish.$bod);
		this.$tip = $('<div class="'+this.settings.tipClass+'" />').appendTo(this.$hud);
		this.$contents = $('<div class="'+this.settings.contentsClass+'" />').appendTo(this.$hud).html(contentsHtml);

		this.show();

		// Prevent clicks on the HUD from hiding itself
		this.addListener(this.$hud, 'click', function(ev)
		{
			ev.stopPropagation();
		});
	},

	/**
	 * Show
	 */
	show: function(ev) {

		if (this.showing)
		{
			return;
		}

		if (Garnish.HUD.active)
		{
			Garnish.HUD.active.hide();
		}

		this.$hud.show();

		// -------------------------------------------
		//  Get all relevant dimensions, lengths, etc
		// -------------------------------------------

		this.windowWidth = Garnish.$win.width();
		this.windowHeight = Garnish.$win.height();

		this.windowScrollLeft = Garnish.$win.scrollLeft();
		this.windowScrollTop = Garnish.$win.scrollTop();

		// get the trigger element's dimensions
		this.triggerWidth = this.$trigger.outerWidth();
		this.triggerHeight = this.$trigger.outerHeight();

		// get the offsets for each side of the trigger element
		this.triggerOffset = this.$trigger.offset();
		this.triggerOffsetRight = this.triggerOffset.left + this.triggerWidth;
		this.triggerOffsetBottom = this.triggerOffset.top + this.triggerHeight;
		this.triggerOffsetLeft = this.triggerOffset.left;
		this.triggerOffsetTop = this.triggerOffset.top;

		// get the HUD dimensions
		this.width = this.$hud.width();
		this.height = this.$hud.height();

		// get the minimum horizontal/vertical clearance needed to fit the HUD
		this.minHorizontalClearance = this.width + this.settings.triggerSpacing + this.settings.windowSpacing;
		this.minVerticalClearance = this.height + this.settings.triggerSpacing + this.settings.windowSpacing;

		// find the actual available right/bottom/left/top clearances
		this.rightClearance = this.windowWidth + this.windowScrollLeft - this.triggerOffsetRight;
		this.bottomClearance = this.windowHeight + this.windowScrollTop - this.triggerOffsetBottom;
		this.leftClearance = this.triggerOffsetLeft - this.windowScrollLeft;
		this.topClearance = this.triggerOffsetTop - this.windowScrollTop;

		// -------------------------------------------
		//  Where are we putting it?
		//   - Ideally, we'll be able to find a place to put this where it's not overlapping the trigger at all.
		//     If we can't find that, either put it to the right or below the trigger, depending on which has the most room.
		// -------------------------------------------

		// below?
		if (this.bottomClearance >= this.minVerticalClearance)
		{
			var top = this.triggerOffsetBottom + this.settings.triggerSpacing;
			this.$hud.css('top', top);
			this._setLeftPos();
			this._setTipClass('top');
		}
		// to the right?
		else if (this.rightClearance >= this.minHorizontalClearance)
		{
			var left = this.triggerOffsetRight + this.settings.triggerSpacing;
			this.$hud.css('left', left);
			this._setTopPos();
			this._setTipClass('left');
		}
		// to the left?
		else if (this.leftClearance >= this.minHorizontalClearance)
		{
			var left = this.triggerOffsetLeft - (this.width + this.settings.triggerSpacing);
			this.$hud.css('left', left);
			this._setTopPos();
			this._setTipClass('right');
		}
		// above?
		else if (this.topClearance >= this.minVerticalClearance)
		{
			var top = this.triggerOffsetTop - (this.height + this.settings.triggerSpacing);
			this.$hud.css('top', top);
			this._setLeftPos();
			this._setTipClass('bottom');
		}
		// ok, which one comes the closest -- right or bottom?
		else
		{
			var rightClearanceDiff = this.minHorizontalClearance - this.rightClearance,
				bottomClearanceDiff = this.minVerticalClearance - this.bottomClearance;

			if (rightClearanceDiff >= bottomClearanceDiff)
			{
				var left = this.windowWidth - (this.width + this.settings.windowSpacing),
					minLeft = this.triggerOffsetLeft + this.settings.triggerSpacing;
				if (left < minLeft) left = minLeft;
				this.$hud.css('left', left);
				this._setTopPos();
				this._setTipClass('left');
			}
			else
			{
				var top = this.windowHeight - (this.height + this.settings.windowSpacing),
					minTop = this.triggerOffsetTop + this.settings.triggerSpacing;
				if (top < minTop) top = minTop;
				this.$hud.css('top', top);
				this._setLeftPos();
				this._setTipClass('top');
			}
		}

		if (ev && ev.stopPropagation)
		{
			ev.stopPropagation();
		}

		this.addListener(Garnish.$bod, 'click', 'hide');

		if (this.settings.closeBtn)
		{
			this.addListener(this.settings.closeBtn, 'activate', 'hide');
		}

		this.showing = true;
		Garnish.HUD.active = this;

		// onShow callback
		this.settings.onShow();
	},

	/**
	 * Set Top
	 */
	_setTopPos: function()
	{
		var maxTop = (this.windowHeight + this.windowScrollTop) - (this.height + this.settings.windowSpacing),
			minTop = (this.windowScrollTop + this.settings.windowSpacing),

			triggerCenter = this.triggerOffsetTop + Math.round(this.triggerHeight / 2),
			top = triggerCenter - Math.round(this.height / 2);

		// adjust top position as needed
		if (top > maxTop) top = maxTop;
		if (top < minTop) top = minTop;

		this.$hud.css('top', top);

		// set the tip's top position
		var tipTop = (triggerCenter - top) - (this.settings.tipWidth / 2);
		this.$tip.css({ top: tipTop, left: '' });
	},

	/**
	 * Set Left
	 */
	_setLeftPos: function()
	{
		var maxLeft = (this.windowWidth + this.windowScrollLeft) - (this.width + this.settings.windowSpacing),
			minLeft = (this.windowScrollLeft + this.settings.windowSpacing),

			triggerCenter = this.triggerOffsetLeft + Math.round(this.triggerWidth / 2),
			left = triggerCenter - Math.round(this.width / 2);

		// adjust left position as needed
		if (left > maxLeft) left = maxLeft;
		if (left < minLeft) left = minLeft;

		this.$hud.css('left', left);

		// set the tip's left position
		var tipLeft = (triggerCenter - left) - (this.settings.tipWidth / 2);
		this.$tip.css({ left: tipLeft, top: '' });
	},

	/**
	 * Set Tip Class
	 */
	_setTipClass: function(c)
	{
		if (this.tipClass)
		{
			this.$tip.removeClass(this.tipClass);
		}

		this.tipClass = this.settings.tipClass+'-'+c;
		this.$tip.addClass(this.tipClass);
	},

	/**
	 * Hide
	 */
	hide: function()
	{
		this.$hud.hide();
		this.showing = false;

		Garnish.HUD.active = null;

		// onHide callback
		this.settings.onHide();
	}
},
{
	defaults: {
		hudClass: 'hud',
		tipClass: 'tip',
		contentsClass: 'contents',
		triggerSpacing: 7,
		windowSpacing: 20,
		tipWidth: 8,
		onShow: $.noop,
		onHide: $.noop,
		closeBtn: null
	}
});


/**
 * Light Switch
 */
Garnish.LightSwitch = Garnish.Base.extend({

	settings: null,
	$outerContainer: null,
	$innerContainer: null,
	$input: null,
	$toggleTarget: null,
	on: null,
	dragger: null,

	dragStartMargin: null,

	init: function(outerContainer, settings)
	{
		this.$outerContainer = $(outerContainer);

		// Is this already a switch?
		if (this.$outerContainer.data('lightswitch'))
		{
			Garnish.log('Double-instantiating a switch on an element');
			this.$outerContainer.data('lightswitch').destroy();
		}

		this.$outerContainer.data('lightswitch', this);

		this.setSettings(settings, Garnish.LightSwitch.defaults);

		this.$innerContainer = this.$outerContainer.find('.container:first');
		this.$input = this.$outerContainer.find('input:first');
		this.$toggleTarget = $(this.$outerContainer.attr('data-toggle'));

		this.on = this.$outerContainer.hasClass('on');

		this.addListener(this.$outerContainer, 'mousedown', '_onMouseDown');
		this.addListener(this.$outerContainer, 'keydown', '_onKeyDown');

		this.dragger = new Garnish.BaseDrag(this.$outerContainer, {
			axis: Garnish.X_AXIS,
			ignoreButtons: false,
			onDragStart: $.proxy(this, '_onDragStart'),
			onDrag:      $.proxy(this, '_onDrag'),
			onDragStop:  $.proxy(this, '_onDragStop')
		});
	},

	turnOn: function()
	{
		this.$innerContainer.stop().animate({marginLeft: 0}, 'fast');
		this.$input.val(Garnish.Y_AXIS);
		this.on = true;
		this.settings.onChange();

		this.$toggleTarget.show();
		this.$toggleTarget.height('auto');
		var height = this.$toggleTarget.height();
		this.$toggleTarget.height(0);
		this.$toggleTarget.stop().animate({height: height}, 'fast', $.proxy(function() {
			this.$toggleTarget.height('auto');
		}, this));
	},

	turnOff: function()
	{
		this.$innerContainer.stop().animate({marginLeft: Garnish.LightSwitch.offMargin}, 'fast');
		this.$input.val('');
		this.on = false;
		this.settings.onChange();

		this.$toggleTarget.stop().animate({height: 0}, 'fast');
	},

	toggle: function(ev)
	{
		if (!this.on)
		{
			this.turnOn();
		}
		else
		{
			this.turnOff();
		}
	},

	_onMouseDown: function()
	{
		this.addListener(Garnish.$doc, 'mouseup', '_onMouseUp')
	},

	_onMouseUp: function()
	{
		this.removeListener(Garnish.$doc, 'mouseup');

		// Was this a click?
		if (!this.dragger.dragging)
			this.toggle();
	},

	_onKeyDown: function(ev)
	{
		switch (ev.keyCode)
		{
			case Garnish.SPACE_KEY:
			{
				this.toggle();
				ev.preventDefault();
				break;
			}

			case Garnish.RIGHT_KEY:
			{
				this.turnOn();
				ev.preventDefault();
				break;
			}

			case Garnish.LEFT_KEY:
			{
				this.turnOff();
				ev.preventDefault();
				break;
			}
		}
	},

	_getMargin: function()
	{
		return parseInt(this.$innerContainer.css('marginLeft'))
	},

	_onDragStart: function()
	{
		this.dragStartMargin = this._getMargin();
	},

	_onDrag: function()
	{
		var margin = this.dragStartMargin + this.dragger.mouseDistX;

		if (margin < Garnish.LightSwitch.offMargin)
		{
			margin = Garnish.LightSwitch.offMargin;
		}
		else if (margin > 0)
		{
			margin = 0;
		}

		this.$innerContainer.css('marginLeft', margin);
	},

	_onDragStop: function()
	{
		var margin = this._getMargin();

		if (margin > -16)
		{
			this.turnOn();
		}
		else
		{
			this.turnOff();
		}
	},

	destroy: function()
	{
		this.base();
		this.dragger.destroy();
	}

},
{
	offMargin: -50,
	defaults: {
		onChange: $.noop
	}
});


/**
 * Menu
 */
Garnish.Menu = Garnish.Base.extend({

	settings: null,

	$container: null,
	$options: null,
	$btn: null,

	/**
	 * Constructor
	 */
	init: function(container, settings)
	{
		this.setSettings(settings, Garnish.Menu.defaults);

		this.$container = $(container).appendTo(Garnish.$bod);
		this.$options = this.$container.find('a');
		this.$options.data('menu', this);

		if (this.settings.attachToButton)
		{
			this.$btn = $(this.settings.attachToButton);
		}

		this.addListener(this.$options, 'mousedown', 'selectOption');
	},

	setPositionRelativeToButton: function()
	{
		var btnOffset = this.$btn.offset(),
			btnWidth = this.$btn.outerWidth(),
			css = {
				top: btnOffset.top + this.$btn.outerHeight(),
				minWidth: (btnWidth - 32)
			};

		if (this.$container.attr('data-align') == 'right')
		{
			css.right = 1 + Garnish.$win.width() - (btnOffset.left + btnWidth);
		}
		else
		{
			css.left = 1 + btnOffset.left;
		}

		this.$container.css(css);
	},

	show: function()
	{
		if (this.$btn)
		{
			this.setPositionRelativeToButton();
		}

		this.$container.fadeIn(50);
	},

	hide: function()
	{
		this.$container.fadeOut('fast');
	},

	selectOption: function(ev)
	{
		this.settings.onOptionSelect(ev.currentTarget);
	}

},
{
	defaults: {
		attachToButton: null,
		onOptionSelect: $.noop
	}
});


/**
 * Menu Button
 */
Garnish.MenuBtn = Garnish.Base.extend({

	$btn: null,
	menu: null,
	showingMenu: false,

	/**
	 * Constructor
	 */
	init: function(btn, settings)
	{
		this.$btn = $(btn);

		// Is this already a menu button?
		if (this.$btn.data('menubtn'))
		{
			Garnish.log('Double-instantiating a menu button on an element');
			this.$btn.data('menubtn').destroy();
		}

		this.$btn.data('menubtn', this);

		this.setSettings(settings, Garnish.MenuBtn.defaults);

		var $menu = this.$btn.next('.menu');
		this.menu = new Garnish.Menu($menu, {
			attachToButton: this.$btn,
			onOptionSelect: $.proxy(this, 'onOptionSelect')
		});

		this.addListener(this.$btn, 'mousedown', 'onMouseDown');
	},

	onMouseDown: function(ev)
	{
		if (ev.button != Garnish.PRIMARY_CLICK || ev.metaKey)
		{
			return;
		}

		ev.preventDefault();

		if (this.showingMenu)
		{
			this.hideMenu();
		}
		else
		{
			this.showMenu();
		}
	},

	showMenu: function()
	{
		this.menu.show();
		this.$btn.addClass('active');
		this.showingMenu = true;

		setTimeout($.proxy(function() {
			this.addListener(Garnish.$doc, 'mousedown', 'onMouseDown');
		}, this), 1);

		if (!Garnish.isMobileBrowser())
		{
			this.addListener(Garnish.$win, 'resize', 'hideMenu');
		}
	},

	hideMenu: function()
	{
		this.menu.hide();
		this.$btn.removeClass('active');
		this.showingMenu = false;

		this.removeListener(Garnish.$doc, 'mousedown');

		if (!Garnish.isMobileBrowser())
		{
			this.removeListener(Garnish.$doc, 'resize');
		}
	},

	onOptionSelect: function(option)
	{
		this.settings.onOptionSelect(option);
	}

},
{
	defaults: {
		onOptionSelect: $.noop
	}
});



Garnish.MixedInput = Garnish.Base.extend({

	$container: null,
	elements: null,
	focussedElement: null,
	blurTimeout: null,

	init: function(container, settings)
	{
		this.$container = $(container);
		this.setSettings(settings, Garnish.MixedInput.defaults);

		this.elements = [];

		// Allow the container to receive focus
		this.$container.attr('tabindex', 0);
		this.addListener(this.$container, 'focus', 'onFocus');
	},

	getElementIndex: function($elem)
	{
		return $.inArray($elem, this.elements);
	},

	isText: function($elem)
	{
		return ($elem.prop('nodeName') == 'INPUT');
	},

	onFocus: function(ev)
	{
		// Set focus to the first element
		if (this.elements.length)
		{
			var $elem = this.elements[0];
			this.setFocus($elem);
			this.setCarotPos($elem, 0);
		}
		else
		{
			this.addTextElement();
		}
	},

	addTextElement: function(index)
	{
		var text = new TextElement(this);
		this.addElement(text.$input, index);
		return text;
	},

	addElement: function($elem, index)
	{
		// Was a target index passed, and is it valid?
		if (typeof index == 'undefined')
		{
			if (this.focussedElement)
			{
				var focussedElement = this.focussedElement,
					focussedElementIndex = this.getElementIndex(focussedElement);

				// Is the focus on a text element?
				if (this.isText(focussedElement))
				{
					var selectionStart = focussedElement.prop('selectionStart'),
						selectionEnd = focussedElement.prop('selectionEnd'),
						val = focussedElement.val(),
						preVal = val.substring(0, selectionStart),
						postVal = val.substr(selectionEnd);

					if (preVal && postVal)
					{
						// Split the input into two
						focussedElement.val(preVal).trigger('change');
						var newText = new TextElement(this);
						newText.$input.val(postVal).trigger('change');
						this.addElement(newText.$input, focussedElementIndex+1);

						// Insert the new element in between them
						index = focussedElementIndex+1;
					}
					else if (!preVal)
					{
						// Insert the new element before this one
						index = focussedElementIndex;
					}
					else
					{
						// Insert it after this one
						index = focussedElementIndex + 1;
					}
				}
				else
				{
					// Just insert the new one after this one
					index = focussedElementIndex + 1;
				}
			}
			else
			{
				// Insert the new element at the end
				index = this.elements.length;
			}
		}

		// Add the element
		if (typeof this.elements[index] != 'undefined')
		{
			$elem.insertBefore(this.elements[index]);
			this.elements.splice(index, 0, $elem);
		}
		else
		{
			// Just for safe measure, set the index to what it really will be
			index = this.elements.length;

			this.$container.append($elem);
			this.elements.push($elem);
		}

		// Make sure that there are text elements surrounding all non-text elements
		if (!this.isText($elem))
		{
			// Add a text element before?
			if (index == 0 || !this.isText(this.elements[index-1]))
			{
				this.addTextElement(index);
				index++;
			}

			// Add a text element after?
			if (index == this.elements.length-1 || !this.isText(this.elements[index+1]))
			{
				this.addTextElement(index+1);
			}
		}

		// Add event listeners
		this.addListener($elem, 'click', function() {
			this.setFocus($elem);
		});

		// Set focus to the new element
		setTimeout($.proxy(function() {
			this.setFocus($elem);
		}, this), 1);
	},

	removeElement: function($elem)
	{
		var index = this.getElementIndex($elem);
		if (index != -1)
		{
			this.elements.splice(index, 1);

			if (!this.isText($elem))
			{
				// Combine the two now-adjacent text elements
				var $prevElem = this.elements[index-1],
					$nextElem = this.elements[index];

				if (this.isText($prevElem) && this.isText($nextElem))
				{
					var prevElemVal = $prevElem.val(),
						newVal = prevElemVal + $nextElem.val();
					$prevElem.val(newVal).trigger('change');
					this.removeElement($nextElem);
					this.setFocus($prevElem);
					this.setCarotPos($prevElem, prevElemVal.length);
				}
			}

			$elem.remove();
		}
	},

	setFocus: function($elem)
	{
		this.$container.addClass('focus');

		if (!this.focussedElement)
		{
			// Prevent the container from receiving focus
			// as long as one of its elements has focus
			this.$container.attr('tabindex', '-1');
		}
		else
		{
			// Blur the previously-focussed element
			this.blurFocussedElement();
		}

		$elem.attr('tabindex', '0');
		$elem.focus();
		this.focussedElement = $elem;

		this.addListener($elem, 'blur', function() {
			this.blurTimeout = setTimeout($.proxy(function() {
				if (this.focussedElement == $elem)
				{
					this.blurFocussedElement();
					this.focussedElement = null;
					this.$container.removeClass('focus');

					// Get ready for future focus
					this.$container.attr('tabindex', '0');
				}
			}, this), 1);
		});
	},

	blurFocussedElement: function()
	{
		this.removeListener(this.focussedElement, 'blur');
		this.focussedElement.attr('tabindex', '-1');
	},

	focusPreviousElement: function($from)
	{
		var index = this.getElementIndex($from);

		if (index > 0)
		{
			var $elem = this.elements[index-1];
			this.setFocus($elem);

			// If it's a text element, put the carot at the end
			if (this.isText($elem))
			{
				var length = $elem.val().length;
				this.setCarotPos($elem, length);
			}
		}
	},

	focusNextElement: function($from)
	{
		var index = this.getElementIndex($from);

		if (index < this.elements.length-1)
		{
			var $elem = this.elements[index+1];
			this.setFocus($elem);

			// If it's a text element, put the carot at the beginning
			if (this.isText($elem))
			{
				this.setCarotPos($elem, 0)
			}
		}
	},

	setCarotPos: function($elem, pos)
	{
		$elem.prop('selectionStart', pos);
		$elem.prop('selectionEnd', pos);
	}

});



var TextElement = Garnish.Base.extend({

	parentInput: null,
	$input: null,
	$stage: null,
	val: null,
	focussed: false,
	interval: null,

	init: function(parentInput)
	{
		this.parentInput = parentInput;

		this.$input = $('<input type="text"/>').appendTo(this.parentInput.$container);
		this.$input.css('margin-right', (2-TextElement.padding)+'px');

		this.setWidth();

		this.addListener(this.$input, 'focus', 'onFocus');
		this.addListener(this.$input, 'blur', 'onBlur');
		this.addListener(this.$input, 'keydown', 'onKeyDown');
		this.addListener(this.$input, 'change', 'checkInput');
	},

	getIndex: function()
	{
		return this.parentInput.getElementIndex(this.$input);
	},

	buildStage: function()
	{
		this.$stage = $('<stage/>').appendTo(Garnish.$bod);

		// replicate the textarea's text styles
		this.$stage.css({
			position: 'absolute',
			top: -9999,
			left: -9999,
			wordWrap: 'nowrap'
		});

		Garnish.copyTextStyles(this.$input, this.$stage);
	},

	getTextWidth: function(val)
	{
		if (!this.$stage)
		{
			this.buildStage();
		}

		if (val)
		{
			// Ampersand entities
			val = val.replace(/&/g, '&amp;');

			// < and >
			val = val.replace(/</g, '&lt;');
			val = val.replace(/>/g, '&gt;');

			// Spaces
			val = val.replace(/ /g, '&nbsp;');
		}

		this.$stage.html(val);
		this.stageWidth = this.$stage.width();
		return this.stageWidth;
	},

	onFocus: function()
	{
		this.focussed = true;
		this.interval = setInterval($.proxy(this, 'checkInput'), Garnish.NiceText.interval);
		this.checkInput();
	},

	onBlur: function()
	{
		this.focussed = false;
		clearInterval(this.interval);
		this.checkInput();
	},

	onKeyDown: function(ev)
	{
		setTimeout($.proxy(this, 'checkInput'), 1);

		switch (ev.keyCode)
		{
			case Garnish.LEFT_KEY:
			{
				if (this.$input.prop('selectionStart') == 0 && this.$input.prop('selectionEnd') == 0)
				{
					// Set focus to the previous element
					this.parentInput.focusPreviousElement(this.$input);
				}
				break;
			}

			case Garnish.RIGHT_KEY:
			{
				if (this.$input.prop('selectionStart') == this.val.length && this.$input.prop('selectionEnd') == this.val.length)
				{
					// Set focus to the next element
					this.parentInput.focusNextElement(this.$input);
				}
				break;
			}

			case Garnish.DELETE_KEY:
			{
				if (this.$input.prop('selectionStart') == 0 && this.$input.prop('selectionEnd') == 0)
				{
					// Set focus to the previous element
					this.parentInput.focusPreviousElement(this.$input);
					ev.preventDefault();
				}
			}
		}
	},

	getVal: function()
	{
		this.val = this.$input.val();
		return this.val;
	},

	setVal: function(val)
	{
		this.$input.val(val);
		this.checkInput();
	},

	checkInput: function()
	{
		// Has the value changed?
		var changed = (this.val !== this.getVal());
		if (changed)
		{
			this.setWidth();
			this.onChange();
		}

		return changed;
	},

	setWidth: function()
	{
		// has the width changed?
		if (this.stageWidth !== this.getTextWidth(this.val))
		{
			// update the textarea width
			var width = this.stageWidth + TextElement.padding;
			this.$input.width(width);
		}
	},

	onChange: $.noop
},
{
	padding: 20
});


/**
 * Modal
 */
Garnish.Modal = Garnish.Base.extend({

	$container: null,
	$header: null,
	$body: null,
	$scrollpane: null,
	$footer: null,
	$footerBtns: null,
	$submitBtn: null,

	_headerHeight: null,
	_footerHeight: null,

	visible: false,

	dragger: null,

	init: function(container, settings)
	{
		// Param mapping
		if (!settings && Garnish.isObject(container))
		{
			// (settings)
			settings = container;
			items = null;
		}

		this.setSettings(settings, Garnish.Modal.defaults);

		if (container)
		{
			this.setContainer(container);
			this.show();
		}

		Garnish.Modal.instances.push(this);
	},

	setContainer: function(container)
	{
		this.$container = $(container);

		// Is this already a modal?
		if (this.$container.data('modal'))
		{
			Garnish.log('Double-instantiating a modal on an element');
			this.$container.data('modal').destroy();
		}

		this.$container.data('modal', this);

		this.$header = this.$container.find('.pane-head:first');
		this.$body = this.$container.find('.pane-body:first');
		this.$scrollpane = this.$body.children('.scrollpane:first');
		this.$footer = this.$container.find('.pane-foot:first');
		this.$footerBtns = this.$footer.find('.btn');
		this.$submitBtn = this.$footerBtns.filter('.submit:first');
		this.$closeBtn = this.$footerBtns.filter('.close:first');

		if (this.settings.draggable)
		{
			var $dragHandles = this.$header.add(this.$footer);
			if ($dragHandles.length)
			{
				this.dragger = new Garnish.DragMove(this.$container, {
					handle: this.$container
				});
			}
		}

		this.addListener(this.$container, 'keydown', 'onKeyDown');
		this.addListener(this.$closeBtn, 'click', 'hide');
	},

	show: function()
	{
		if (Garnish.Modal.visibleModal)
		{
			Garnish.Modal.visibleModal.hide();
		}

		if (this.$container)
		{
			this.$container.show();

			// Center it vertically
			var modalHeight = this.getHeight();
			this.$container.css('margin-top', -Math.round(modalHeight/2));

			// Make sure it's not too wide
			var windowWidth = Garnish.$win.width();
			if (this.$container.width() > windowWidth)
			{
				this.$container.css({
					width: windowWidth,
					marginLeft: -Math.round(windowWidth/2)
				});
			}

			this.$container.delay(50).fadeIn();
		}

		this.visible = true;
		Garnish.Modal.visibleModal = this;
		Garnish.Modal.$shade.fadeIn(50);
		this.addListener(Garnish.Modal.$shade, 'click', 'hide');
	},

	hide: function()
	{
		if (this.$container)
		{
			this.$container.fadeOut('fast');
			this.removeListener(Garnish.$win, 'resize');
		}

		this.visible = false;
		Garnish.Modal.visibleModal = null;
		Garnish.Modal.$shade.fadeOut('fast');
		this.removeListener(Garnish.Modal.$shade, 'click');
	},

	getHeight: function()
	{
		if (!this.$container)
		{
			throw 'Attempted to get the height of a modal whose container has not been set.';
		}

		if (!this.visible)
		{
			this.$container.show();
		}

		var height = this.$container.outerHeight();

		if (!this.visible)
		{
			this.$container.hide();
		}

		return height;
	},

	getWidth: function()
	{
		if (!this.$container)
		{
			throw 'Attempted to get the width of a modal whose container has not been set.';
		}

		if (!this.visible)
		{
			this.$container.show();
		}

		var width = this.$container.outerWidth();

		if (!this.visible)
		{
			this.$container.hide();
		}

		return width;
	},

	positionRelativeTo: function(elem)
	{
		if (!this.$container)
		{
			throw 'Attempted to position a modal whose container has not been set.';
		}

		var $elem = $(elem),
			elemOffset = $elem.offset(),
			bodyScrollTop = Garnish.$bod.scrollTop(),
			topClearance = elemOffset.top - bodyScrollTop,
			modalHeight = this.getHeight();

		if (modalHeight < topClearance + Garnish.navHeight + Garnish.Modal.relativeElemPadding*2)
		{
			var top = elemOffset.top - modalHeight - Garnish.Modal.relativeElemPadding;
		}
		else
		{
			var top = elemOffset.top + $elem.height() + Garnish.Modal.relativeElemPadding;
		}

		this.$container.css({
			top: top,
			left: elemOffset.left
		});
	},

	onKeyDown: function(ev)
	{
		if (ev.target.nodeName != 'TEXTAREA' && ev.keyCode == Garnish.RETURN_KEY)
		{
			this.$submitBtn.click();
		}
	},

	destroy: function()
	{
		this.base();

		if (this.dragger)
		{
			this.dragger.destroy();
		}
	}
},
{
	relativeElemPadding: 8,
	defaults: {
		draggable: true
	},
	instances: [],
	visibleModal: null,
	$shade: $('<div class="modal-shade"/>').appendTo(Garnish.$bod)
});


/**
 * Nice Text
 */
Garnish.NiceText = Garnish.Base.extend({

	$input: null,
	$hint: null,
	$stage: null,
	autoHeight: null,
	focussed: false,
	showingHint: false,
	val: null,
	stageHeight: null,
	minHeight: null,
	interval: null,

	init: function(input, settings)
	{
		this.$input = $(input);
		this.settings = $.extend({}, Garnish.NiceText.defaults, settings);

		// Is this already a transparent text input?
		if (this.$input.data('nicetext'))
		{
			Garnish.log('Double-instantiating a transparent text input on an element');
			this.$input.data('nicetext').destroy();
		}

		this.$input.data('nicetext', this);

		this.getVal();

		this.autoHeight = (this.settings.autoHeight && this.$input.prop('nodeName') == 'TEXTAREA');
		if (this.autoHeight)
		{
			this.minHeight = this.getStageHeight('');
			this.setHeight();

			this.addListener(Garnish.$win, 'resize', 'setHeight');
		}

		if (this.settings.hint)
		{
			this.$hintContainer = $('<div class="texthint-container"/>').insertBefore(this.$input);
			this.$hint = $('<div class="texthint">'+this.settings.hint+'</div>').appendTo(this.$hintContainer);
			this.$hint.css({
				top:  (parseInt(this.$input.css('borderTopWidth'))  + parseInt(this.$input.css('paddingTop'))),
				left: (parseInt(this.$input.css('borderLeftWidth')) + parseInt(this.$input.css('paddingLeft')) + 1)
			});
			Garnish.copyTextStyles(this.$input, this.$hint);

			if (this.val)
			{
				this.$hint.hide();
			}
			else
			{
				this.showingHint = true;
			}

			// Focus the input when clicking on the hint
			this.addListener(this.$hint, 'mousedown', function(ev) {
				ev.preventDefault();
				this.$input.focus();
			});
		}

		this.addListener(this.$input, 'focus', 'onFocus');
		this.addListener(this.$input, 'blur', 'onBlur');
		this.addListener(this.$input, 'keydown', 'onKeyDown');
	},

	getVal: function()
	{
		this.val = this.$input.val();
		return this.val;
	},

	showHint: function()
	{
		this.$hint.fadeIn(Garnish.NiceText.hintFadeDuration);
		this.showingHint = true;
	},

	hideHint: function()
	{
		this.$hint.fadeOut(Garnish.NiceText.hintFadeDuration);
		this.showingHint = false;
	},

	checkInput: function()
	{
		// Has the value changed?
		var changed = (this.val !== this.getVal());
		if (changed)
		{
			if (this.$hint)
			{
				if (this.showingHint && this.val)
				{
					this.hideHint();
				}
				else if (!this.showingHint && !this.val)
				{
					this.showHint();
				}
			}

			if (this.autoHeight)
			{
				this.setHeight();
			}
		}

		return changed;
	},

	buildStage: function()
	{
		this.$stage = $('<stage/>').appendTo(Garnish.$bod);

		// replicate the textarea's text styles
		this.$stage.css({
			position: 'absolute',
			top: -9999,
			left: -9999,
			wordWrap: 'break-word'
		});

		Garnish.copyTextStyles(this.$input, this.$stage);
	},

	getStageHeight: function(val)
	{
		if (!this.$stage)
		{
			this.buildStage();
		}

		this.$stage.css('width', this.$input.width());

		if (!val)
		{
			val = '&nbsp;';
			for (var i = 1; i < this.$input.prop('rows'); i++)
			{
				val += '<br/>&nbsp;';
			}
		}
		else
		{
			// Ampersand entities
			val = val.replace(/&/g, '&amp;');

			// < and >
			val = val.replace(/</g, '&lt;');
			val = val.replace(/>/g, '&gt;');

			// Spaces
			val = val.replace(/ /g, '&nbsp;');

			// Line breaks
			val = val.replace(/[\n\r]$/g, '<br/>&nbsp;');
			val = val.replace(/[\n\r]/g, '<br/>');

			// One extra line for fun
			val += '<br/>&nbsp;';
		}

		this.$stage.html(val);
		this.stageHeight = this.$stage.height();
		return this.stageHeight;
	},

	setHeight: function()
	{
		// has the height changed?
		if (this.stageHeight !== this.getStageHeight(this.val))
		{
			// update the textarea height
			var height = this.stageHeight;
			if (height < this.minHeight)
			{
				height = this.minHeight;
			}
			this.$input.height(height);
		}
	},

	onFocus: function()
	{
		this.focussed = true;
		this.interval = setInterval($.proxy(this, 'checkInput'), Garnish.NiceText.interval);
		this.checkInput();
	},

	onBlur: function()
	{
		this.focussed = false;
		clearInterval(this.interval);

		this.checkInput();
	},

	onKeyDown: function()
	{
		setTimeout($.proxy(this, 'checkInput'), 1);
	},

	destroy: function()
	{
		this.base();
		this.$hint.remove();
		this.$stage.remove();
	}

},
{
	interval: 100,
	hintFadeDuration: 50,
	defaults: {
		autoHeight: true
	}
});


/**
 * Password Input
 */
Garnish.PasswordInput = Garnish.Base.extend({

	$passwordInput: null,
	$textInput: null,
	$currentInput: null,

	$showPasswordToggle: null,
	showingPassword: null,
	showingCapsIcon: null,

	init: function(passwordInput)
	{
		this.$passwordInput = $(passwordInput);

		// Is this already a password input?
		if (this.$passwordInput.data('passwordInput'))
		{
			Garnish.log('Double-instantiating a password input on an element');
			this.$passwordInput.data('passwordInput').destroy();
		}

		this.$passwordInput.data('passwordInput', this);

		this.showingCapsIcon = false;

		this.$showPasswordToggle = $('<a/>').hide();
		this.$showPasswordToggle.addClass('password-toggle');
		this.$showPasswordToggle.insertAfter(this.$passwordInput);
		this.addListener(this.$showPasswordToggle, 'mousedown', 'onToggleMouseDown');
		this.hidePassword();
	},

	setCurrentInput: function($input)
	{
		if (this.$currentInput)
		{
			// Swap the inputs, while preventing the focus animation
			$input.addClass('focus');
			this.$currentInput.replaceWith($input);
			$input.focus();
			$input.removeClass('focus');

			// Restore the input value
			$input.val(this.$currentInput.val());
		}

		this.$currentInput = $input;

		this.addListener(this.$currentInput, 'focus', 'onFocus');
		this.addListener(this.$currentInput, 'keypress', 'onKeyPress');
		this.addListener(this.$currentInput, 'keypress,keyup,change,blur', 'onInputChange');
	},

	updateToggleLabel: function(label)
	{
		this.$showPasswordToggle.text(label);
	},

	showPassword: function()
	{
		if (this.showingPassword)
		{
			return;
		}

		this.hideCapsIcon();

		if (!this.$textInput)
		{
			this.$textInput = this.$passwordInput.clone(true);
			this.$textInput.attr('type', 'text');
		}

		this.setCurrentInput(this.$textInput);
		this.updateToggleLabel(Garnish.PasswordInput.lang.Hide);
		this.showingPassword = true;
	},

	hidePassword: function()
	{
		// showingPassword could be null, which is acceptable
		if (this.showingPassword === false)
		{
			return;
		}

		this.setCurrentInput(this.$passwordInput);
		this.updateToggleLabel(Garnish.PasswordInput.lang.Show);
		this.showingPassword = false;

		// Alt key temporarily shows the password
		this.addListener(this.$passwordInput, 'keydown', 'onKeyDown');
	},

	togglePassword: function()
	{
		if (this.showingPassword)
		{
			this.hidePassword();
		}
		else
		{
			this.showPassword();
		}
	},

	showCapsIcon: function()
	{
		if (this.showingCapsIcon)
		{
			return;
		}

		this.$currentInput.addClass('capslock');
		this.showingCapsIcon = true;
	},

	hideCapsIcon: function()
	{
		if (!this.showingCapsIcon)
		{
			return;
		}

		this.$currentInput.removeClass('capslock');
		this.showingCapsIcon = false;
	},

	onFocus: function()
	{
		this.hideCapsIcon();
	},

	onKeyDown: function(ev)
	{
		if (ev.keyCode == Garnish.ALT_KEY && this.$currentInput.val())
		{
			this.showPassword();
			this.$showPasswordToggle.hide();
			this.addListener(this.$textInput, 'keyup', 'onKeyUp');
		}
	},

	onKeyUp: function(ev)
	{
		ev.preventDefault();

		if (ev.keyCode == Garnish.ALT_KEY)
		{
			this.hidePassword();
			this.$showPasswordToggle.show();
		}
	},

	onKeyPress: function(ev)
	{
		// No need to show the caps lock indicator if we're showing the password
		if (this.showingPassword)
		{
			return;
		}

		if (!ev.shiftKey && !ev.metaKey)
		{
			var str = String.fromCharCode(ev.which);

			if (str.toUpperCase() === str && str.toLowerCase() !== str)
			{
				this.showCapsIcon();
			}
			else if (str.toLowerCase() === str && str.toUpperCase() !== str)
			{
				this.hideCapsIcon();
			}
		}
	},

	onInputChange: function()
	{
		if (this.$currentInput.val())
		{
			this.$showPasswordToggle.show();
		}
		else
		{
			this.$showPasswordToggle.hide();
		}
	},

	onToggleMouseDown: function(ev)
	{
		// Prevent focus change
		ev.preventDefault();

		if (this.$currentInput[0].setSelectionRange)
		{
			var selectionStart = this.$currentInput[0].selectionStart,
				selectionEnd   = this.$currentInput[0].selectionEnd;
		}

		this.togglePassword();

		if (this.$currentInput[0].setSelectionRange)
		{
			this.$currentInput[0].setSelectionRange(selectionStart, selectionEnd);
		}
	}
},
{
	lang: {
		Show: 'Show',
		Hide: 'Hide'
	}
});


/**
 * Pill
 */
Garnish.Pill = Garnish.Base.extend({

	$outerContainer: null,
	$innerContainer: null,
	$btns: null,
	$selectedBtn: null,
	$input: null,

	init: function(outerContainer)
	{
		this.$outerContainer = $(outerContainer);

		// Is this already a pill?
		if (this.$outerContainer.data('pill'))
		{
			Garnish.log('Double-instantiating a pill on an element');
			this.$outerContainer.data('pill').destroy();
		}

		this.$outerContainer.data('pill', this);

		this.$innerContainer = this.$outerContainer.find('.btngroup:first');
		this.$btns = this.$innerContainer.find('.btn');
		this.$selectedBtn = this.$btns.filter('.active:first');
		this.$input = this.$outerContainer.find('input:first');

		Garnish.preventOutlineOnMouseFocus(this.$innerContainer);
		this.addListener(this.$btns, 'mousedown', 'onMouseDown');
		this.addListener(this.$innerContainer, 'keydown', 'onKeyDown');
	},

	select: function(btn)
	{
		this.$selectedBtn.removeClass('active');
		var $btn = $(btn);
		$btn.addClass('active');
		this.$input.val($btn.attr('data-value'));
		this.$selectedBtn = $btn;
	},

	onMouseDown: function(ev)
	{
		this.select(ev.currentTarget);
	},

	_getSelectedBtnIndex: function()
	{
		if (typeof this.$selectedBtn[0] != 'undefined')
		{
			return $.inArray(this.$selectedBtn[0], this.$btns);
		}
		else
		{
			return -1;
		}
	},

	onKeyDown: function(ev)
	{
		switch (ev.keyCode)
		{
			case Garnish.RIGHT_KEY:
			{
				if (!this.$selectedBtn.length)
					this.select(this.$btns[this.$btns.length-1]);
				else
				{
					var nextIndex = this._getSelectedBtnIndex() + 1;
					if (typeof this.$btns[nextIndex] != 'undefined')
						this.select(this.$btns[nextIndex]);
				}
				ev.preventDefault();
				break;
			}

			case Garnish.LEFT_KEY:
			{
				if (!this.$selectedBtn.length)
					this.select(this.$btns[0]);
				else
				{
					var prevIndex = this._getSelectedBtnIndex() - 1;
					if (typeof this.$btns[prevIndex] != 'undefined')
						this.select(this.$btns[prevIndex]);
				}
				ev.preventDefault();
				break;
			}
		}
	}

});


/**
 * Select
 */
Garnish.Select = Garnish.Base.extend({

	$container: null,
	$items: null,

	totalSelected: null,

	mousedownX: null,
	mousedownY: null,
	mouseUpTimeout: null,
	mouseUpTimeoutDuration: null,
	callbackTimeout: null,

	$focusable: null,
	$first: null,
	first: null,
	$last: null,
	last: null,

	/**
	 * Init
	 */
	init: function(container, items, settings)
	{
		this.$container = $(container);

		// Param mapping
		if (!settings && Garnish.isObject(items))
		{
			// (container, settings)
			settings = items;
			items = null;
		}

		// Is this already a select?
		if (this.$container.data('select'))
		{
			Garnish.log('Double-instantiating a select on an element');
			this.$container.data('select').destroy();
		}

		this.$container.data('select', this);

		this.setSettings(settings, Garnish.Select.defaults);
		this.mouseUpTimeoutDuration = (this.settings.waitForDblClick ? 300 : 0);

		this.$items = $();
		this.addItems(items);

		// --------------------------------------------------------------------

		this.addListener(this.$container, 'click', function(ev)
		{
			if (this.ignoreClick)
			{
				this.ignoreClick = false;
			}
			else
			{
				// deselect all items on container click
				this.deselectAll(true);
			}
		});
	},

	// --------------------------------------------------------------------

	/**
	 * Get Item Index
	 */
	getItemIndex: function($item)
	{
		return this.$items.index($item[0]);
	},

	/**
	 * Is Selected?
	 */
	isSelected: function($item)
	{
		return $item.hasClass(this.settings.selectedClass);
	},

	/**
	 * Select Item
	 */
	selectItem: function($item)
	{
		if (!this.settings.multi)
		{
			this.deselectAll();
		}

		$item.addClass(this.settings.selectedClass);

		this.$first = this.$last = $item;
		this.first = this.last = this.getItemIndex($item);

		this.setFocusableItem($item);
		$item.focus();

		this.totalSelected++;

		this.setCallbackTimeout();
	},

	selectAll: function()
	{
		if (!this.settings.multi || !this.$items.length)
		{
			return;
		}

		this.first = 0;
		this.last = this.$items.length-1;
		this.$first = $(this.$items[this.first]);
		this.$last = $(this.$items[this.last]);

		this.$items.addClass(this.settings.selectedClass);
		this.totalSelected = this.$items.length;
		this.setCallbackTimeout();
	},

	/**
	 * Select Range
	 */
	selectRange: function($item)
	{
		if (!this.settings.multi)
		{
			return this.selectItem($item);
		}

		this.deselectAll();

		this.$last = $item;
		this.last = this.getItemIndex($item);

		this.setFocusableItem($item);
		$item.focus();

		// prepare params for $.slice()
		if (this.first < this.last)
		{
			var sliceFrom = this.first,
				sliceTo = this.last + 1;
		}
		else
		{
			var sliceFrom = this.last,
				sliceTo = this.first + 1;
		}

		this.$items.slice(sliceFrom, sliceTo).addClass(this.settings.selectedClass);

		this.totalSelected = sliceTo - sliceFrom;

		this.setCallbackTimeout();
	},

	/**
	 * Deselect Item
	 */
	deselectItem: function($item)
	{
		$item.removeClass(this.settings.selectedClass);

		var index = this.getItemIndex($item);
		if (this.first === index) this.$first = this.first = null;
		if (this.last === index) this.$last = this.last = null;

		this.totalSelected--;

		this.setCallbackTimeout();
	},

	/**
	 * Deselect All
	 */
	deselectAll: function(clearFirst)
	{
		this.$items.removeClass(this.settings.selectedClass);

		if (clearFirst)
		{
			this.$first = this.first = this.$last = this.last = null;
		}

		this.totalSelected = 0;

		this.setCallbackTimeout();
	},

	/**
	 * Deselect Others
	 */
	deselectOthers: function($item)
	{
		this.deselectAll();
		this.selectItem($item);
	},

	/**
	 * Toggle Item
	 */
	toggleItem: function($item)
	{
		if (! this.isSelected($item))
		{
			this.selectItem($item);
		}
		else
		{
			this.deselectItem($item);
		}
	},

	// --------------------------------------------------------------------

	clearMouseUpTimeout: function()
	{
		clearTimeout(this.mouseUpTimeout);
	},

	/**
	 * On Mouse Down
	 */
	onMouseDown: function(ev)
	{
		// ignore right clicks
		if (ev.button != Garnish.PRIMARY_CLICK)
		{
			return;
		}

		this.mousedownX = ev.pageX;
		this.mousedownY = ev.pageY;

		var $item = $($.data(ev.currentTarget, 'select-item'));

		if (ev.metaKey)
		{
			this.toggleItem($item);
		}
		else if (this.first !== null && ev.shiftKey)
		{
			this.selectRange($item);
		}
		else if (! this.isSelected($item))
		{
			this.deselectAll();
			this.selectItem($item);
		}
	},

	/**
	 * On Mouse Up
	 */
	onMouseUp: function(ev)
	{
		// ignore right clicks
		if (ev.button != Garnish.PRIMARY_CLICK)
		{
			return;
		}

		var $item = $($.data(ev.currentTarget, 'select-item'));

		// was this a click?
		if (! ev.metaKey && ! ev.shiftKey && Garnish.getDist(this.mousedownX, this.mousedownY, ev.pageX, ev.pageY) < 1)
		{
			this.selectItem($item);

			// wait a moment before deselecting others
			// to give the user a chance to double-click
			this.clearMouseUpTimeout();
			this.mouseUpTimeout = setTimeout($.proxy(function() {
				this.deselectOthers($item);
			}, this), this.mouseUpTimeoutDuration);
		}
	},

	// --------------------------------------------------------------------

	/**
	 * On Key Down
	 */
	onKeyDown: function(ev)
	{
		var metaKey = (ev.metaKey || ev.ctrlKey);

		if (this.settings.arrowsChangeSelection || !this.$focusable.length)
		{
			var anchor = ev.shiftKey ? this.last : this.first;
		}
		else
		{
			var anchor = $.inArray(this.$focusable[0], this.$items);

			if (anchor == -1)
			{
				anchor = 0;
			}
		}

		// Ok, what are we doing here?
		switch (ev.keyCode)
		{
			case Garnish.LEFT_KEY:
			{
				ev.preventDefault();

				// Select the last item if none are selected
				if (this.first === null)
				{
					var $item = this.getLastItem();
				}
				else
				{
					if (metaKey)
					{
						var $item = this.getFurthestItemToTheLeft(anchor);
					}
					else
					{
						var $item = this.getItemToTheLeft(anchor);
					}
				}

				break;
			}

			case Garnish.RIGHT_KEY:
			{
				ev.preventDefault();

				// Select the first item if none are selected
				if (this.first === null)
				{
					var $item = this.getFirstItem();
				}
				else
				{
					if (metaKey)
					{
						var $item = this.getFurthestItemToTheRight(anchor);
					}
					else
					{
						var $item = this.getItemToTheRight(anchor);
					}
				}

				break;
			}

			case Garnish.UP_KEY:
			{
				ev.preventDefault();

				// Select the last item if none are selected
				if (this.first === null)
				{
					var $item = this.getLastItem();
				}
				else
				{
					if (metaKey)
					{
						var $item = this.getFurthestItemAbove(anchor);
					}
					else
					{
						var $item = this.getItemAbove(anchor);
					}

					if (!$item)
					{
						$item = this.getFirstItem();
					}
				}

				break;
			}

			case Garnish.DOWN_KEY:
			{
				ev.preventDefault();

				// Select the first item if none are selected
				if (this.first === null)
				{
					var $item = this.getFirstItem();
				}
				else
				{
					if (metaKey)
					{
						var $item = this.getFurthestItemBelow(anchor);
					}
					else
					{
						var $item = this.getItemBelow(anchor);
					}

					if (!$item)
					{
						$item = this.getLastItem();
					}
				}

				break;
			}

			case Garnish.SPACE_KEY:
			{
				if (!metaKey)
				{
					ev.preventDefault();

					if (this.isSelected(this.$focusable))
					{
						this.deselectItem(this.$focusable);
					}
					else
					{
						this.selectItem(this.$focusable);
					}
				}

				break;
			}

			case Garnish.A_KEY:
			{
				if (metaKey)
				{
					ev.preventDefault();
					this.selectAll();
				}

				break;
			}
		}

		// Is there an item queued up for focus/selection?
		if ($item && $item.length)
		{
			if (this.settings.arrowsChangeSelection)
			{
				// select it
				if (this.first !== null && ev.shiftKey)
				{
					this.selectRange($item);
				}
				else
				{
					this.deselectAll();
					this.selectItem($item);
				}
			}
			else
			{
				// just set the new item to be focussable
				this.setFocusableItem($item);
				$item.focus();
			}
		}
	},

	getFirstItem: function()
	{
		if (this.$items.length)
		{
			return $(this.$items[0]);
		}
	},

	getLastItem: function()
	{
		if (this.$items.length)
		{
			return $(this.$items[this.$items.length-1]);
		}
	},

	isPreviousItem: function(index)
	{
		return (index > 0);
	},

	isNextItem: function(index)
	{
		return (index < this.$items.length-1);
	},

	getPreviousItem: function(index)
	{
		if (this.isPreviousItem(index))
		{
			return $(this.$items[index-1]);
		}
	},

	getNextItem: function(index)
	{
		if (this.isNextItem(index))
		{
			return $(this.$items[index+1]);
		}
	},

	getItemToTheLeft: function(index)
	{
		if (this.isPreviousItem(index))
		{
			if (this.settings.horizontal)
			{
				return this.getPreviousItem(index);
			}
			if (!this.settings.vertical)
			{
				return this.getClosestItem(index, Garnish.X_AXIS, '<');
			}
		}
	},

	getItemToTheRight: function(index)
	{
		if (this.isNextItem(index))
		{
			if (this.settings.horizontal)
			{
				return this.getNextItem(index);
			}
			else if (!this.settings.vertical)
			{
				return this.getClosestItem(index, Garnish.X_AXIS, '>');
			}
		}
	},

	getItemAbove: function(index)
	{
		if (this.isPreviousItem(index))
		{
			if (this.settings.vertical)
			{
				return this.getPreviousItem(index);
			}
			else if (!this.settings.horizontal)
			{
				return this.getClosestItem(index, Garnish.Y_AXIS, '<');
			}
		}
	},

	getItemBelow: function(index)
	{
		if (this.isNextItem(index))
		{
			if (this.settings.vertical)
			{
				return this.getNextItem(index);
			}
			else if (!this.settings.horizontal)
			{
				return this.getClosestItem(index, Garnish.Y_AXIS, '>');
			}
		}
	},

	getClosestItem: function(index, axis, dir)
	{
		var axisProps = Garnish.Select.closestItemAxisProps[axis],
			dirProps = Garnish.Select.closestItemDirectionProps[dir];

		var $thisItem = $(this.$items[index]),
			thisOffset = $thisItem.offset(),
			thisMidpoint = thisOffset[axisProps.midpointOffset] + Math.round($thisItem[axisProps.midpointSizeFunc]()/2),
			otherRowPos = null,
			smallestMidpointDiff = null,
			$closestItem = null;

		for (var i = index + dirProps.step; (typeof this.$items[i] != 'undefined'); i += dirProps.step)
		{
			var $otherItem = $(this.$items[i]),
				otherOffset = $otherItem.offset();

			// Are we on the next row yet?
			if (dirProps.isNextRow(otherOffset[axisProps.rowOffset], thisOffset[axisProps.rowOffset]))
			{
				// Is this the first time we've seen this row?
				if (otherRowPos === null)
				{
					otherRowPos = otherOffset[axisProps.rowOffset];
				}
				// Have we gone too far?
				else if (otherOffset[axisProps.rowOffset] != otherRowPos)
				{
					break;
				}

				var otherMidpoint = otherOffset[axisProps.midpointOffset] + Math.round($otherItem[axisProps.midpointSizeFunc]()/2),
					midpointDiff = Math.abs(thisMidpoint - otherMidpoint);

				// Are we getting warmer?
				if (smallestMidpointDiff === null || midpointDiff < smallestMidpointDiff)
				{
					smallestMidpointDiff = midpointDiff;
					$closestItem = $otherItem;
				}
				// Getting colder?
				else
				{
					break;
				}
			}
			// Getting colder?
			else if (dirProps.isWrongDirection(otherOffset[axisProps.rowOffset], thisOffset[axisProps.rowOffset]))
			{
				break;
			}
		}

		return $closestItem;
	},

	getFurthestItemToTheLeft: function(index)
	{
		return this.getFurthestItem(index, 'ToTheLeft');
	},

	getFurthestItemToTheRight: function(index)
	{
		return this.getFurthestItem(index, 'ToTheRight');
	},

	getFurthestItemAbove: function(index)
	{
		return this.getFurthestItem(index, 'Above');
	},

	getFurthestItemBelow: function(index)
	{
		return this.getFurthestItem(index, 'Below');
	},

	getFurthestItem: function(index, dir)
	{
		var $item, $testItem;

		while ($testItem = this['getItem'+dir](index))
		{
			$item = $testItem;
			index = this.getItemIndex($item);
		}

		return $item;
	},

	// --------------------------------------------------------------------

	/**
	 * Get Total Selected
	 */
	getTotalSelected: function()
	{
		return this.totalSelected;
	},

	/**
	 * Add Items
	 */
	addItems: function(items)
	{
		var $items = $(items);

		for (var i = 0; i < $items.length; i++)
		{
			var item = $items[i];

			// Make sure this element doesn't belong to another selector
			if ($.data(item, 'select'))
			{
				Garnish.log('Element was added to more than one selector');
				$.data(item, 'select').removeItems(item);
			}

			// Add the item
			$.data(item, 'select', this);
			this.$items = this.$items.add(item);

			// Get the handle
			if (this.settings.handle)
			{
				if (typeof this.settings.handle == 'object')
				{
					var $handle = $(this.settings.handle);
				}
				else if (typeof this.settings.handle == 'string')
				{
					var $handle = $(item).find(this.settings.handle);
				}
				else if (typeof this.settings.handle == 'function')
				{
					var $handle = $(this.settings.handle(item));
				}
			}
			else
			{
				var $handle = $(item);
			}

			$.data(item, 'select-handle', $handle);
			$handle.data('select-item', item);

			this.addListener($handle, 'mousedown', 'onMouseDown');
			this.addListener($handle, 'mouseup', 'onMouseUp');
			this.addListener($handle, 'keydown', 'onKeyDown');
			this.addListener($handle, 'click', function(ev)
			{
				this.ignoreClick = true;
			});
		}

		this.totalSelected += $items.filter('.'+this.settings.selectedClass).length;

		this.updateIndexes();
	},

	/**
	 * Remove Items
	 */
	removeItems: function(items)
	{
		items = $.makeArray(items);

		for (var i = 0; i < items.length; i++)
		{
			var item = items[i];

			// Make sure we actually know about this item
			var index = $.inArray(item, this.$items);
			if (index != -1)
			{
				var $handle = $.data(item, 'select-handle');
				$handle.data('select-item', null);
				$.data(item, 'select', null);
				$.data(item, 'select-handle', null);
				this.removeAllListeners($handle);
				this.$items.splice(index, 1);
			}
		}

		this.updateIndexes();
	},

	/**
	 * Refresh Item Order
	 */
	refreshItemOrder: function()
	{
		this.$items = $(this.$items);
	},

	/**
	 * Destroy
	 */
	destroy: function()
	{
		this.base();

		// clear timeout
		this.clearCallbackTimeout();
	},

	/**
	 * Update First/Last indexes
	 */
	updateIndexes: function()
	{
		if (this.first !== null)
		{
			this.first = this.getItemIndex(this.$first);
			this.last = this.getItemIndex(this.$last);
			this.setFocusableItem(this.$first);
		}
		else if (this.$items.length)
		{
			this.setFocusableItem($(this.$items[0]));
		}
	},

	/**
	 * Reset Item Order
	 */
	 resetItemOrder: function()
	 {
	 	this.$items = $().add(this.$items);
	 	this.updateIndexes();
	 },

	/**
	 * Sets the focusable item.
	 *
	 * We only want to have one focusable item per selection list, so that the user
	 * doesn't have to tab through a million items.
	 *
	 * @param object $item
	 */
	setFocusableItem: function($item)
	{
		if (this.$focusable)
		{
			this.$focusable.removeAttr('tabindex');
		}

		this.$focusable = $item.attr('tabindex', '0');
	},

	// --------------------------------------------------------------------

	/**
	 * Clear Callback Timeout
	 */
	clearCallbackTimeout: function()
	{
		if (this.settings.onSelectionChange)
		{
			clearTimeout(this.callbackTimeout);
		}
	},

	/**
	 * Set Callback Timeout
	 */
	setCallbackTimeout: function()
	{
		if (this.settings.onSelectionChange)
		{
			// clear the last one
			this.clearCallbackTimeout();

			this.callbackTimeout = setTimeout($.proxy(function()
			{
				this.callbackTimeout = null;
				this.settings.onSelectionChange();
			}, this), 300);
		}
	},

	/**
	 * Get Selected Items
	 */
	getSelectedItems: function()
	{
		return this.$items.filter('.'+this.settings.selectedClass);
	}
},
{
	defaults: {
		selectedClass: 'sel',
		multi: false,
		vertical: false,
		horizontal: false,
		waitForDblClick: false,
		arrowsChangeSelection: true,
		handle: null,
		onSelectionChange: $.noop
	},

	closestItemAxisProps: {
		x: {
			midpointOffset:   'top',
			midpointSizeFunc: 'outerHeight',
			rowOffset:        'left'
		},
		y: {
			midpointOffset:   'left',
			midpointSizeFunc: 'outerWidth',
			rowOffset:        'top'
		}
	},

	closestItemDirectionProps: {
		'<': {
			step: -1,
			isNextRow: function(a, b) { return (a < b); },
			isWrongDirection: function(a, b) { return (a > b); }
		},
		'>': {
			step: 1,
			isNextRow: function(a, b) { return (a > b); },
			isWrongDirection: function(a, b) { return (a < b); }
		}
	}
});


/**
 * Select Menu
 */
Garnish.SelectMenu = Garnish.Menu.extend({

	/**
	 * Constructor
	 */
	init: function(btn, options, settings, callback)
	{
		// argument mapping
		if (typeof settings == 'function')
		{
			// (btn, options, callback)
			callback = settings;
			settings = {};
		}

		settings = $.extend({}, Garnish.SelectMenu.defaults, settings);

		this.base(btn, options, settings, callback);

		this.selected = -1;
	},

	/**
	 * Build
	 */
	build: function()
	{
		this.base();

		if (this.selected != -1)
		{
			this._addSelectedOptionClass(this.selected);
		}
	},

	/**
	 * Select
	 */
	select: function(option)
	{
		// ignore if it's already selected
		if (option == this.selected) return;

		if (this.dom.ul)
		{
			if (this.selected != -1)
			{
				this.dom.options[this.selected].className = '';
			}

			this._addSelectedOptionClass(option);
		}

		this.selected = option;

		// set the button text to the selected option
		this.setBtnText($(this.options[option].label).text());

		this.base(option);
	},

	/**
	 * Add Selected Option Class
	 */
	_addSelectedOptionClass: function(option)
	{
		this.dom.options[option].className = 'sel';
	},

	/**
	 * Set Button Text
	 */
	setBtnText: function(text)
	{
		this.dom.$btnLabel.text(text);
	}

},
{
	defaults: {
		ulClass: 'menu select'
	}
});


})(jQuery);
