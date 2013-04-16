function logg(msg, val) {
	try {
		if (val != undefined) console.log(msg, val)
		else 				  console.log(msg);
	} catch(e) {}
}

$(function() {
	var height = $("#intro").height(),
		p1Top, p2Top, p3Top, p4Top,
		s1Top, s2Top, s3Top, s4Top;
	//$("#product").css("margin-top", height + $("header").height());
	//$(".page").height(height);

	var fixedMenu = false;
	$(window).on("scroll", function() {
		if (!p1Top) {
			p1Top = $(".p1").offset().top;
			p2Top = $(".p2").offset().top;
			p3Top = $(".p3").offset().top;
			p4Top = $(".p4").offset().top;
			s1Top = $(".story").eq(0).offset().top;
			s2Top = $(".story").eq(1).offset().top;
			s3Top = $(".story").eq(2).offset().top;
			s4Top = $(".story").eq(3).offset().top;
		}
		var scrollTop = $("body").scrollTop();
		if (scrollTop >= height && !fixedMenu) {
			$("header, #productImage").addClass("fixed");
			fixedMenu = true;
		} else if (scrollTop < height && fixedMenu) {
			$("header, #productImage").removeClass("fixed");
			fixedMenu = false;
		}

		if (!fixedMenu) {
			$("#intro").css("background-position", "center " + "-" + (scrollTop/10) + "px");
			$("#intro .wrapper").css("margin-top", "-" + (scrollTop/height*40) + "%");
		}

		//product
		if (scrollTop + height >= s1Top) {
			$("#productImage").removeClass("fixed");
		} else if (scrollTop+300 >= p4Top) {
			$("#productImage img").attr("src", "/assets/img/prod04.jpg");
		} else if (scrollTop+300 >= p3Top) {
			$("#productImage img").attr("src", "/assets/img/prod03.jpg");
		} else if (scrollTop+300 >= p2Top) {
			$("#productImage img").attr("src", "/assets/img/prod02.jpg");
		} else {
			$("#productImage img").attr("src", "/assets/img/prod01.jpg");
		}

		//story
		if (scrollTop + height >= s4Top) {
			$(".story .imageWrapper").hide().eq(3).show();
		} else if (scrollTop + height >= s3Top) {
			$(".story .imageWrapper").hide().eq(2).show();
		} else if (scrollTop + height >= s2Top) {
			$(".story .imageWrapper").hide().eq(1).show();
		} else if (scrollTop + height >= s1Top) {
			$(".story .imageWrapper").hide().eq(0).show();
			$(".product").css("z-index", "3");
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

