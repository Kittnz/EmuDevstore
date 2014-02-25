/**
 * @package FusionHub
 * @author Jesper Lindström
 * @author Xavier Geernick
 * @link http://raxezdev.com/fusioncms
 */

var Main = {
	
	/**
	 * Submit the log in fields
	 * @param Object form
	 * @param String redirect
	 */
	login: function(form, redirect)
	{
		var data = {
			username: $("#username").val(),
			password: $("#password").val()
		};

		var errors = 0;

		if(!data.username.length)
		{
			errors++;

			$("#username").addClass("form_error");
		}
		else
		{
			$("#username").removeClass("form_error");
		}

		if(!data.password.length)
		{
			errors++;

			$("#password").addClass("form_error");
		}
		else
		{
			$("#password").removeClass("form_error");
		}

		if(errors == 0)
		{
			$("#username").attr("disabled", "disabled");
			$("#password").attr("disabled", "disabled");
			$(form).find("input[type=submit]").attr("disabled", "disabled");

			$.post(Config.URL + "login", data, function(response)
			{
				console.log(response);
				$("#username").removeAttr("disabled");
				$("#password").removeAttr("disabled");
				$(form).find("input[type=submit]").removeAttr("disabled");

				switch(response)
				{
					// Correct
					case '1':
						if(redirect)
						{
							window.location = Config.URL + redirect;
						}
						else
						{
							window.location.reload(true);
						}
						break;
				
					// Wrong password
					case '2':
						$("#password").addClass("form_error");
						break;

					// Wrong username
					case '3':
						$("#username").addClass("form_error");
						$("#password").addClass("form_error");
						break;
				}
			});
		}
	},

	createUser: function()
	{
		var real_name = $("#register_real_name").val();
		var email = $("#register_email").val();
		var password = $("#register_password").val();

		$.post(Config.URL + "create", {real_name: real_name, email: email, password: password}, function(data)
		{
			if(data == '1')
			{
				window.location.reload(true);
			}
			else
			{
				$("#register_result").html(data);
			}
		});
	},

	updateLicense: function()
	{
		var domain = $("#license_domain").val();
		
		$("#license_key").fadeOut(150, function()
		{
			$(this).html("Generating license key...").fadeIn(150, function()
			{
				$.post(Config.URL + "account/createLicense", {domain:domain}, function(data)
				{
					$("#license_key").fadeOut(150, function()
					{
						$(this).html(data).fadeIn(150);
					});
				});
			});
		});
	},

	saveSettings: function()
	{
		var real_name = $("#real_name").val();
		var email = $("#email").val();
		var website = $("#website").val();

		$.post(Config.URL + "account/saveSettings", {real_name: real_name, email: email, website: website}, function(data)
		{
			$("#settings_result").html(data);
		});
	},

	changePassword: function()
	{
		var old_password = $("#old_password").val();
		var new_password = $("#new_password").val();

		$.post(Config.URL + "account/changePassword", {old_password:old_password, new_password:new_password}, function(data)
		{
			$("#password_result").html(data);
		});
	},

	submitForgot: function()
	{
		var email = $("#email").val();

		$.post(Config.URL + "forgot/send", {email:email}, function(data)
		{
			$("#email_results").html(data);
		});
	},

	/**
	 * Contact object
	 */
	Contact: {

		/**
		 * Submit the form via AJAX
		 */
		submitForm: function()
		{
			var name = document.getElementById("real_name"),
				email = document.getElementById("email"),
				subject = document.getElementById("subject"),
				message = document.getElementById("message");

			this.Validate.check(name, "name");
			this.Validate.check(email, "email");
			this.Validate.check(subject, "subject");
			this.Validate.check(message, "message");

			var invalid = $("#contact .form_error");

			// No errors
			if(invalid.size() == 0)
			{
				$("#contact form").fadeOut(150, function()
				{
					$("#email_results").html('<img src="' + Config.URL + 'static/images/ajax.gif" />');
					
					$(this).fadeIn(150, function()
					{
						$.post(Config.URL + "contact/send",
						{
							name: name.value,
							email: email.value,
							subject: subject.value,
							message: $(message).val()
						},
						function(data)
						{
							$("#contact form").fadeOut(150, function()
							{
								if(data == "1")
								{
									$(this).html('Thanks for your message, we will reply as soon as possible!').fadeIn(150);
								}
								else
								{
									$("#email_results").html(data);
									$(this).fadeIn(150);
								}
							});
						});
					});
				});
			}
		},

		Validate: {

			/**
			 * Main validator
			 * @param String field
			 * @param String type
			 * @param Boolean optional
			 */
			check: function(field, type, optional)
			{
				var valid = false;

				if(optional
				&& field.value.length == 0)
				{
					valid = true;
				}
				else
				{
					switch(type)
					{
						case "email": valid = Main.Contact.Validate.email(field); break;
						case "website": valid = Main.Contact.Validate.website(field); break;
						case "name": valid = Main.Contact.Validate.name(field); break;
						case "subject": valid = Main.Contact.Validate.subject(field); break;
						case "message": valid = Main.Contact.Validate.message(field); break;
					}
				}

				if(valid == true)
				{
					$(field).removeClass("form_error");
				}
				else
				{
					$(field).addClass("form_error");
				}
			},

			/**
			 * Email validator
			 * @param String field
			 */
			email: function(field)
			{
				var content = field.value;

				if(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/.test(content))
				{
					return true;
				}
				else
				{
					return false;
				}
			},

			/**
			 * Website validator
			 * @param String field
			 */
			website: function(field)
			{
				var content = field.value;

				if(/\./.test(content))
				{
					return true;
				}
				else
				{
					return false;
				}
			},

			/**
			 * Name validator
			 * @param String field
			 */
			name: function(field)
			{
				var content = field.value;

				// Foreign character support
				content = content.replace("", "");

				if(/^[A-Za-z -]*$/.test(content)
				&& content.length > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			},

			/**
			 * Subject validator
			 * @param String field
			 */
			subject: function(field)
			{
				var content = field.value;

				if(content.length > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			},

			/**
			 * Message validator
			 */
			message: function(field)
			{
				var content = field.value;

				if(content.length > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
	},

	liveCounter: function()
	{
		var spot = $("#live_counter");

		$.get(Config.URL + "admin/getTotal", function(data)
		{
			console.log(data);
			setTimeout(Main.liveCounter, 5000);

			if(data != spot.html())
			{
				spot.transition({rotateX:"90deg"}, 300, function(){
					spot.html(data);
					spot.transition({rotateX:"0deg"}, 300);
				});
			}
		});
	}
}