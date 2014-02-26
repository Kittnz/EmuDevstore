/**
 * @package FusionHub
 * @author Jesper Lindström
 * @author Xavier Geernick
 * @link http://raxezdev.com/fusioncms
 */

var UI = {

	/**
	 * Initialze the necessary UI components
	 */
	initialize: function()
	{
		// Give older browsers some html5-placeholder love!
		$('input[placeholder], textarea[placeholder]').placeholder();

		setTimeout(UI.fancyHeader, 300);
		
		UI.Slider.initialize();
		UI.Router.initialize();
	},

	/**
	 * Shows an alert box
	 * @param String message
	 */
	alert: function(question, element)
	{
		if(!question && element)
		{
			var question = $(element).html();
		}

		if(question.length == 0)
		{
			question = '<img src="http://img-cache.cdn.gaiaonline.com/c57f77cb596aae50b0725174b806e3ee/http://i1243.photobucket.com/albums/gg544/luzcyfer/Meme/okay-meme-1.jpg" />';
		}
		
		// Put question and button text
		$("#alert_message").html(question);

		// Show box
		$("#popup_bg").fadeTo(200, 0.5);
		$("#alert").fadeTo(200, 1);
		
		$("#alert_message").css({marginBottom:"10px"});
		$(".popup_links").show();

		// Assign click event
		$("#alert_button").bind('click', function()
		{
			UI.hidePopup();	
		});
		
		// Assign hide-function to background
		$("#popup_bg").bind('click', function()
		{
			UI.hidePopup();
		});

		// Assign key events
		$(document).keypress(function(event)
		{
			// If "enter"
			if(event.which == 13)
			{
				UI.hidePopup();
			}
		});
	},

	/**
	 * Shows a confirm box
	 * @param String question
	 * @param String button
	 * @param Function callback
	 */
	confirm: function(question, button, callback)
	{
		$(".popup_links").show();
		
		// Put question and button text
		$("#confirm_question").html(question);
		$("#confirm_button").html(button);

		// Show box
		$("#popup_bg").fadeTo(200, 0.5);
		$("#confirm").fadeTo(200, 1);

		// Assign click event
		$("#confirm_button").bind('click', function()
		{
			callback();
			UI.hidePopup();	
		});

		// Assign hide-function to background
		$("#popup_bg").bind('click', function()
		{
			UI.hidePopup();
		});

		// Assign key events
		$(document).keypress(function(event)
		{
			// If "enter"
			if(event.which == 13)
			{
				callback();
				UI.hidePopup();
			}
		});
	},

	/**
	 * Hides the current popup box
	 */
	hidePopup: function()
	{
		// Hide box
		$("#popup_bg").hide();
		$("#confirm").hide();
		$("#alert").hide();
		$("#vote_reminder").hide();

		// Remove events
		$("#confirm_button").unbind('click');
		$("#alert_button").unbind('click');
		$(document).unbind('keypress');
	},

	/**
	 * Animate the header
	 */
	fancyHeader: function()
	{
		if(typeof isIE == "undefined")
		{
			$("#header_bg").transition({opacity:1}, ($("#header_bg").is(":visible"))?200:500, function()
			{
				$(this).find("h1").transition({
					opacity:1,
					scale:1
				}, 300, function()
				{
					var h2 = $("#header_bg").find("h2");

					if(h2.is(":visible"))
					{
						h2.transition({
							opacity:1,
							scale:1
						}, 300, function()
						{
							setTimeout(function()
							{
								$("#header_buttons").transition({
									opacity:1,
									scale:1
								}, 150);
							}, 150);
						});
					}
				});
			});
		}
	},

	/**
	 * AJAX router
	 */
	Router: {

		first: true,
		page: null,

		initialize: function()
		{
			// Check for pushState support
			if(history.pushState)
			{
				// Assign AJAX loading behavior to all our internal links
				$("a[href*='" + Config.URL + "']").each(function()
				{
					// Make sure it has not been assigned already
					if(typeof $(this).data('events') == "undefined" && $(this).attr("target") != "_blank")
					{
						// Add the event listener
						$(this).click(function(event)
						{
							// Get the link
							var href = $(this).attr("href");

							// Load it via AJAX
							UI.Router.load(href);

							// Add it to the history object
							history.pushState('', 'New URL: ' + href, href);

							// Prevent it from refreshing the whole page
							event.preventDefault();
						});
					}
				});
			}
		},

		/**
		 * Load an internal page via AJAX
		 * @param String url
		 */
		load: function(url)
		{
			if(/logout/.test(url))
			{
				window.location = url;

				return false;
			}
			
			if(UI.Router.first)
			{
				UI.Router.first = false;

				// Make it load the page if they press back or forward
				$(window).bind('popstate', function()
				{
					UI.Router.load(location.pathname);
				});
			}

			$("#main").html('<center style="padding:100px;"><img src="' + Config.URL + 'static/images/ajax.gif" /></center>');

			if(UI.Slider.intervalObject)
			{
				clearInterval(UI.Slider.intervalObject);
			}

			UI.Router.page = url;
			UI.Router.setActiveState(url);

			$.get(url, { is_ajax:1 }, function(data)
			{
				try
				{
					data = JSON.parse(data);
				}
				catch(error)
				{
					data = {
						title: "FusionHub",
						content: "<center style='padding:100px'>Something went wrong!<br /><br /><b>Technical data:</b> " + data + "</center>",
						headline: "Something went wrong",
						big: false
					};
				}

				UI.Router.changeContent(data, url);

			}).error(function()
			{
				var data = {
						title: "FusionHub",
						content: "<center style='padding:100px'><img src='" + Config.URL + "static/images/404.jpg' /></center>",
						headline: "404",
						big: false
					};

				UI.Router.changeContent(data, url);
			});
		},

		changeContent: function(data, url)
		{
			if(UI.Router.page == url)
			{
				$("title").html(data.title);

				UI.Router.setActiveState(url);
				UI.Router.setHeadline(data.headline, data.big);
				
				$("#main").transition({opacity:0}, 500, function()
				{
					$("#main").html(data.content).transition({opacity:1}, 500, function()
					{
						UI.Router.initialize();
						UI.Slider.initialize();
					});
				});

				window.scrollTo(0, 0);
			}
		},

		/**
		 * Set the header headline
		 * @param String headline
		 * @param Boolean big
		 */
		setHeadline: function(headline, big)
		{
			var header_bg = $("#header_bg");

			if(big)
			{
				header_bg.removeClass("normal_header");

				if(typeof isIE != "undefined")
				{
					
					header_bg.removeClass("header_small").css({height:"398px"}, 300);
					header_bg.find("h1").css({paddingTop:"40px"}, 300, function()
					{
						header_bg.find(".right").fadeIn(100);
						$(this).html(headline);
					});
					
				}
				else
				{	
					header_bg.removeClass("header_small").transition({height:"398px"}, 300);
					header_bg.find("h1").transition({paddingTop:"40px",opacity:0,scale:1.6}, 300, function()
					{
						$(this).html(headline);
						header_bg.find(".right").fadeTo(0,1);
						header_bg.find("h2").transition({scale:0,opacity:0},0);
						$("#header_buttons").transition({scale:0,opacity:0}, 0);
						UI.fancyHeader();
					});
				}
			}
			else
			{
				header_bg.addClass("normal_header");

				if(typeof isIE != "undefined")
				{
					header_bg.find(".right").fadeOut(150, function()
					{
						header_bg.css({height:"120px"}, 300);
						header_bg.find("h1").css({paddingTop:"30px"}, 300, function()
						{
							$(this).html(headline);
						});
					});
				}
				else
				{
					header_bg.find(".right").fadeOut(150, function()
					{
						header_bg.transition({height:"120px"}, 300);
						header_bg.find("h1").transition({paddingTop:"30px",opacity:0,scale:1.6}, 300, function()
						{
							$(this).html(headline);
							UI.fancyHeader();
						});
					});
				}
			}
		},

		/**
		 * Set the link active state
		 * @param String url
		 */
		setActiveState: function(url)
		{
			if($("#top li .active").attr("href") != url)
			{
				$("#top li .active").removeClass("active");

				$("#top li a").each(function()
				{
					if(url == $(this).attr("href"))
					{
						$(this).addClass("active");
					}
				});
			}
		}
	},

	/**
	 * Image slider
	 */
	Slider: {

		interval: 5000,
		intervalObject: false,
		element: null,
		current: null,

		initialize: function()
		{
			UI.Slider.element = $("#gallery_wrapper");

			if(this.element.length)
			{
				this.current = $(this.element.children("img")[0]);
				
				this.current.fadeTo(1000, 1, function()
				{
					UI.Slider.intervalObject = setInterval(UI.Slider.change, UI.Slider.interval);
				});
			}
		},

		change: function()
		{
			if($("#gallery_wrapper").length)
			{
				var current = UI.Slider.current,
					next = UI.Slider.current.next().length ? $(UI.Slider.current.next()[0]) : $(UI.Slider.element.children("img")[0]);

				current.css({"z-index":1});
				next.css({marginLeft:-598, "z-index":0});

				next.transition({marginLeft:0,opacity:1}, 500);

				current.transition({marginLeft:598,opacity:0}, 500, function()
				{
					UI.Slider.current = next;
				});
			}
			else
			{
				clearInterval(UI.Slider.intervalObject);
			}
		}
	}
}