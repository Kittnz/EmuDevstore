/**
 * Animated snow
 * @package FusionHub
 * @author Jesper Lindström
 * @link http://raxezdev.com
 */

define(function()
{
	"use strict";

	var module = {};

	var count = 30;
	var parentElement;

	/**
	 * Spawn snowflakes
	 */
	module.initialize = function()
	{
		parentElement = $("#snow");

		// Create our desired amount of snowflakes
		for(var i = 0; i < count; i++)
		{
			setTimeout(function()
			{
				spawnSnowflake(true);
			}, Math.floor(Math.random() * 5000));
		}
	};

	/**
	 * Spawn a snowflake and keep it alive forever
	 */
	var spawnSnowflake = function(isFirst, element)
	{
		if(element || isFirst)
		{
			// Randomize coordinates
			var x = Math.floor(Math.random() * 1300);
			var y = -50;

			if(isFirst)
			{
				y = Math.floor(Math.random() * 200) - 50;

				parentElement.append('<div class="snowflake"></div>');

				var element = parentElement.children(".snowflake").last();
			}

			element.css({marginTop:y + "px", marginLeft:x + "px", opacity:0, scale: Math.random() * 0.5 + 0.5});
			
			element.transition({
				perspective: '100px',
				rotateY: (Math.floor(Math.random() * 360)) + 'deg',
				marginTop: "365px",
				opacity: 1
			}, (Math.floor(Math.random() * 4000) + 2000), "linear", function()
			{
				$(this).animate({opacity:0}, 500, function()
				{
					spawnSnowflake(false, $(this));
				});
			});
		}
	}

	return module;
});