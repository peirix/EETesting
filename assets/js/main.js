function logg(msg, val) {
	try {
		if (val != undefined) console.log(msg, val)
		else 				  console.log(msg);
	} catch(e) {}
}

$(function() {
	var height = $("#intro").height(),
		menuHeight = $("header").outerHeight(),
		p1Top, p2Top, p3Top, p4Top,
		s1Top, s2Top, s3Top, s4Top,
		$stImg1 = $("#story .imageWrapper"),
		$stImg2 = $("#story2 .imageWrapper"),
		$stImg3 = $("#story3 .imageWrapper"),
		$stImg4 = $("#story4 .imageWrapper");

	var fixedMenu = false,
		showingStory = false;
	$(window).on("scroll", function() {
		if (!p1Top) {
			p1Top = $(".p1").offset().top;
			p2Top = $(".p2").offset().top;
			p3Top = $(".p3").offset().top;
			p4Top = $(".p4").offset().top;
			s1Top = $("#story").offset().top;
			s2Top = $("#story2").offset().top;
			s3Top = $("#story3").offset().top;
			s4Top = $("#story4").offset().top;
		}
		var scrollTop = $(window).scrollTop();
		if (scrollTop >= height - menuHeight && !fixedMenu) {
			$("header, #productImage").addClass("fixed");
			$("#intro").css("display", "none");
			fixedMenu = true;
			$("header nav li a").removeClass("active").eq(0).addClass("active");
		} else if (scrollTop < height - menuHeight && fixedMenu) {
			$("header, #productImage").removeClass("fixed");
			$("#intro").css("display", "");
			fixedMenu = false;
			$("header nav li a").removeClass("active");
			$(".story .imageWrapper").css("display", "");
		}

		if (!fixedMenu) {
			$("#intro").css("background-position", "center " + "-" + (scrollTop/10) + "px");
			$("#intro .wrapper").css("margin-top", "-" + (scrollTop/height*40) + "%");
			$stImg1.css("display", "");
		}

		//product
		if (scrollTop + height < s1Top && showingStory) {
			$("#productImage").removeClass("bottom").addClass("fixed");
			$(".product").css("z-index", "");
			showingStory = false;
			
			$stImg1.css("display", "");
		} else if (scrollTop + height >= s1Top) {
			$("#productImage").removeClass("fixed").addClass("bottom");
			$(".product").css("z-index", 10).eq(0).css("z-index", 11);
			$stImg1.css("display", "block");
			showingStory = true;
			
		} else if (scrollTop+300 >= p4Top) {
			$("#productImage img").attr("src", "/assets/img/prod04.jpg");
		} else if (scrollTop+300 >= p3Top) {
			$("#productImage img").attr("src", "/assets/img/prod03.jpg");
		} else if (scrollTop+300 >= p2Top) {
			$("#productImage img").attr("src", "/assets/img/prod02.jpg");
		} else if (scrollTop+300 >= p1Top) {
			$("#productImage img").attr("src", "/assets/img/prod01.jpg");
		}

		//story
		var diff = scrollTop - height + 100;
		if (scrollTop + height >= s4Top - 100) {
			$stImg1.css("display", "");
			$stImg2.css("display", "");
			$stImg4.css({"display": "block", "height":"100%"});
			$stImg3.css("display", "block");
			$stImg3.css("height", s3Top - diff);
		} else if (scrollTop + height >= s3Top - 100) {
			$stImg1.css("display", "");
			$stImg3.css("display", "block");
			$stImg2.css("display", "block");
			$stImg4.css("display", "");
			$stImg2.css("height", s2Top - diff);
			$stImg3.css("height", s3Top - diff);
		} else if (scrollTop + height >= s2Top - 100) {
			$stImg1.css("display", "block");
			$stImg2.css("display", "block");
			$stImg3.css("display", "");
			$stImg4.css("display", "");
			$stImg1.css("height", s1Top - diff);
			$stImg2.css("height", s2Top - diff);
			$("header nav li a").removeClass("active").eq(1).addClass("active");
		} else if (scrollTop + height >= s1Top) {
			$stImg1.css("display", "block");
			$stImg2.css("display", "");
			$stImg3.css("display", "");
			$stImg4.css("display", "");
			$stImg1.css("height", s1Top - diff);
			$("header nav li a").removeClass("active").eq(0).addClass("active");
		}

		//contact
		if (scrollTop >= s4Top) {
			$("footer").css("bottom", scrollTop - s4Top - 200);
		} else {
			$("footer").removeAttr("style");
		}
	});

	var wrSt;
	$(window).on("resize", function() {
		clearTimeout(wrSt);
		wrSt = setTimeout(function() {
			height = $("#intro").height();
			menuHeight = $("header").outerHeight();
			p1Top = 0;
		}, 100);
	});

	$(document).on("click", "header a", function() {
		var link = $(this).attr("href");
		logg(link, $(link));
		if ($(link).length > 0) {
			var top = $(link).position().top;
			$("body").animate({ scrollTop: top });
		}
	});

	$(document).on("click", "a[href=#contact]", function() {
		if ($("footer").hasClass("show")) {
			$("footer").animate({ "bottom": -200 }, function() {
				$(this).removeClass("show").removeAttr("style");
			});
		} else {
			$("footer").addClass("show");
		}
	});

	//preload images
	$(".story").each(function() {
		$("<img/>").get(0).src = $(this).find("img").attr("src");
	});
});

