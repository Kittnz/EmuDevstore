/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function Captcha() {
	this.ajaxRequest = null;
	this.imageWidth = 0;
	this.imageHeight = 0;
	this.zoomIcon;
	this.src = 'index.php?page=Captcha';
	
	/**
	 * Initialises the reload function.
	 */
	this.init = function() {
		// get captcha image
		var image = document.getElementById('captchaImage');
		if (image) {
			var p = document.createElement('p');
			image.parentNode.appendChild(p);
			
			var a = document.createElement('a');
			a.onclick = function() { captcha.reload(); };
			p.appendChild(a);
			
			var title = document.createTextNode(captchaLanguage['wcf.captcha.reload']);
			a.appendChild(title);
			
			// add onload event
			image.onload = function() { captcha.initImageResizer(); };
		}
	}
	
	this.initImageResizer = function() {
		var image = document.getElementById('captchaImage');
		if (image) {
			this.imageWidth = image.width;
			this.imageHeight = image.height;
			if (this.imageWidth > 0 && this.imageHeight > 0) {
				// change cursor
				image.style.cursor = 'pointer';
			
				// append zoom icon
				this.zoomIcon = document.createElement('img');
				image.parentNode.insertBefore(this.zoomIcon, image.nextSibling);
				this.zoomIcon.style.display = 'none';
				this.zoomIcon.style.cursor = 'pointer';
				this.zoomIcon.style.verticalAlign = 'top';
				this.zoomIcon.style.marginLeft = '-20px';
				this.zoomIcon.style.paddingTop = '4px';
			
				// add events
				image.onmouseover = function() { captcha.zoomIcon.style.display = ''; };
				this.zoomIcon.onmouseover = image.onmouseover;
				image.onmouseout = function() { captcha.zoomIcon.style.display = 'none'; };
			
				this.minimize();
			}
		}
	}
	
	this.minimize = function() {
		var image = document.getElementById('captchaImage');
		if (image) {
			// half image size
			image.width = Math.round(this.imageWidth / 2);
			image.height = Math.round(this.imageHeight / 2);
			
			// add onlick event
			image.onclick = function() { captcha.maximize(); };
			
			// change zoom icon
			this.zoomIcon.src = RELATIVE_WCF_DIR + 'icon/zoomInS.png';
			image.title = captchaLanguage['wcf.captcha.maximize'];
			this.zoomIcon.title = captchaLanguage['wcf.captcha.maximize'];
		}
	}
	
	this.maximize = function() {
		var image = $('captchaImage');
		if (image) {
			
			image.up().insert('<div id="captchaHelper"></div>');
			var captchaHelper = $('captchaHelper');
			
			if (captchaHelper.getWidth() < this.imageWidth) {
				image.width = captchaHelper.getWidth();
				image.height = this.imageHeight * (captchaHelper.getWidth() / this.imageWidth);
				captchaHelper.remove();
			}
			else {
				// set original image size
				image.width = this.imageWidth;
				image.height = this.imageHeight;
			}
			
			// add onlick event
			image.onclick = function() { captcha.minimize(); };
			
			// change zoom icon
			this.zoomIcon.src = RELATIVE_WCF_DIR + 'icon/zoomOutS.png';
			image.title = captchaLanguage['wcf.captcha.minimize'];
			this.zoomIcon.title = captchaLanguage['wcf.captcha.minimize'];
		}
	}
	
	/**
	 * Starts the reload of the captcha.
	 */
	this.reload = function() {
		if (this.ajaxRequest == null) {
			// request new captcha id
			var date = new Date();
			this.ajaxRequest = new AjaxRequest();
			this.ajaxRequest.openGet(this.src + '&action=newCaptchaID&t='+date.getTime()+SID_ARG_2ND, function() { captcha.receiveResponse() });
		}
	}
	
	this.receiveResponse = function() {
		if (this.ajaxRequest && this.ajaxRequest.xmlHttpRequest.readyState == 4 && this.ajaxRequest.xmlHttpRequest.status == 200 && this.ajaxRequest.xmlHttpRequest.responseXML) {
			// get new captcha id
			var captchaID = 0;
			var tags = this.ajaxRequest.xmlHttpRequest.responseXML.getElementsByTagName('captchaid');
			if (tags.length > 0) {
				captchaID = tags[0].childNodes[0].nodeValue;
			}
			this.ajaxRequest.xmlHttpRequest.abort();
			this.ajaxRequest = null;
			
			// insert new captcha id in hidden field
			var hidden = document.getElementById('captchaID');
			if (hidden) {
				hidden.value = captchaID;
			}
			
			// reload captcha image
			var image = document.getElementById('captchaImage');
			if (image) {
				image.onload = function() {};
				image.src = this.src + '&captchaID='+captchaID+SID_ARG_2ND;
			}
		}
	}
	
	this.init();
}

var captcha = new Captcha();