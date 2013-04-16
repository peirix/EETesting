

    google.load("language", "1");

    function translate() {
    
      // Grabbing the text to translate
      var text = $('#google_translate_from').attr('value');
    
      // Translate from Spanish to English, and have the callback of the request
      // put the resulting translation in the "translation" div.
      // Note: by putting in an empty string for the source language ('es') then the translation
      // will auto-detect the source language.
      var lang_from = $('#google_translate_language_from').attr('value');
      var lang_to = $('#google_translate_language_to').attr('value');
      google.language.translate(text, lang_from, lang_to, function(result) {

      	if(result.translation) {

      		x = document.createElement('div');
			x.innerHTML = result.translation;
      		$('#google_translate_to').attr('value', x.innerHTML);
			$.cookie('text_to', x.innerHTML);
      	}
          
      });
    }
    
    
	$(document).ready(function() {
	
		var langs = google.language.Languages;
			$('#google_translate_language_from').html('');
			$('#google_translate_language_to').html('');
			
		for (langKey in langs) { 
			if(langKey != "UNKNOWN") {
				$('#google_translate_language_from').append('<option value="'+langs[langKey]+'">'+langKey+'</option>');
				$('#google_translate_language_to').append('<option value="'+langs[langKey]+'">'+langKey+'</option>');
			}

		}
		
		if($.cookie('language_from') !== null) {
			$("#google_translate_language_from").attr('value', $.cookie('language_from'));
		} else {
			$("#google_translate_language_from").attr('value', 'en');
		}
		
		
		if($.cookie('language_to') !== null) {
			$("#google_translate_language_to").attr('value', $.cookie('language_to'));
		} else {
			$("#google_translate_language_to").attr('value', 'fr');
		}
		
		
		if($.cookie('text_from') !== null) {
			$("#google_translate_from").attr('value', $.cookie('text_from'));
		} else {
			$("#google_translate_language_from").attr('value', 'en');
		}
		
		
		if($.cookie('text_to') !== null) {
			$("#google_translate_to").attr('value', $.cookie('text_to'));
		} else {
			$("#google_translate_to").attr('value', '');
		}
		
		$("#google_translate_from").keyup(function() {
			$.cookie('text_from', $(this).attr('value'));
		});
		
		
		$("#google_translate_to").keyup(function() {
			$.cookie('text_to', $(this).attr('value'));
		});
		
		$("#google_translate_language_from").change(function() {
			$.cookie('language_from', $(this).attr('value'));
		});
		
		$("#google_translate_language_to").change(function() {
			$.cookie('language_to', $(this).attr('value'));
		});
	
		$("#google_translate_btn").click(function() {
			translate();
		});
	});
