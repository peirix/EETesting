function logg(msg, val) {
	try {
		if (val != undefined) console.log(msg, val)
		else 				  console.log(msg);
	} catch(e) {}
}

$(function() {
	var height = $("#intro").height(),
		p1Top, p2Top, p3Top, p4Top;
	//$("#product").css("margin-top", height + $("header").height());
	//$(".page").height(height);

	var fixedTop = false;
	$(window).on("scroll", function() {
		if (!p1Top) {
			p1Top = $(".p1").offset().top;
			p2Top = $(".p2").offset().top;
			p3Top = $(".p3").offset().top;
			p4Top = $(".p4").offset().top;
		}
		var scrollTop = $("body").scrollTop();
		if (scrollTop >= height && !fixedTop) {
			$("header, #productImage").addClass("fixed");
			fixedTop = true;
		} else if (scrollTop < height && fixedTop) {
			$("header, #productImage").removeClass("fixed");
			fixedTop = false;
		}

		if (!fixedTop) {
			$("#intro").css("background-position", "center " + "-" + (scrollTop/10) + "px");
			$("#intro .wrapper").css("margin-top", "-" + (scrollTop/height*40) + "%");
		}

		logg(scrollTop);
		logg(p1Top);
		logg(p2Top);
		logg(p3Top);
		logg(p4Top);

		if (scrollTop+300 >= p4Top) {
			$("#productImage img").attr("src", "/assets/img/prod04.jpg");
		} else if (scrollTop+300 >= p3Top) {
			$("#productImage img").attr("src", "/assets/img/prod03.jpg");
		} else if (scrollTop+300 >= p2Top) {
			$("#productImage img").attr("src", "/assets/img/prod02.jpg");
		} else {
			$("#productImage img").attr("src", "/assets/img/prod01.jpg");
		}
	});

	var wrSt;
	$(window).on("resize", function() {
		clearTimeout(wrSt);
		wrSt = setTimeout(function() {
			height = $("#intro").height();
		}, 100);
	});

	$(document).on("click", "header nav li a", function() {
		var link = $(this).attr("href");
		var top = $(link).offset().top;
		$("body").animate({ scrollTop: top });
	});
});

