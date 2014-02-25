/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function AjaxRequest() {
	this.xmlHttpRequest = null;
	this.response = null;
	
	/**
	 * Starts a new post ajax request.
	 */
	this.openPost = function(url, postData, callbackFunction) {
		url = fixURL(url);
		if (this.openXMLHttpRequest(callbackFunction)) {
			this.xmlHttpRequest.open('POST', url, true);
			this.xmlHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			this.xmlHttpRequest.send(postData);
			return true;
		}
		return false;
	}
	
	/**
	 * Starts a new get ajax request.
	 */
	this.openGet = function(url, callbackFunction) {
		url = fixURL(url);
		if (this.openXMLHttpRequest(callbackFunction)) {
			this.xmlHttpRequest.open('GET', url, true);
			this.xmlHttpRequest.send(null);
			return true;
		}
		return false;
	}
	
	/**
	 * Opens a new XMLHttpRequest.
	 */
	this.openXMLHttpRequest = function(callbackFunction) {
		if (this.xmlHttpRequest) {
			if (this.xmlHttpRequest.readyState != 0 && this.xmlHttpRequest.readyState != 4) {
				return false;
			}
		
			this.xmlHttpRequest.abort();
		}
		
		// Internet Explorer
		try {
			this.xmlHttpRequest = new ActiveXObject('Msxml2.XMLHTTP');
		}
		catch (e) {
			try {
				this.xmlHttpRequest = new ActiveXObject('Microsoft.XMLHTTP');
			} 
			catch (e) {
				this.xmlHttpRequest  = null;
			}
		}
		
		// Mozilla, Opera und Safari
		if (!this.xmlHttpRequest) {
			if (typeof XMLHttpRequest != 'undefined') {
				this.xmlHttpRequest = new XMLHttpRequest();
				if (this.xmlHttpRequest.overrideMimeType) {
					this.xmlHttpRequest.overrideMimeType('text/xml');
				}
			}
			else {
				return false;
			}
		}
		
		// add event listener
		if (callbackFunction) {
			this.xmlHttpRequest.onreadystatechange = callbackFunction;
		}
		else {
			this.xmlHttpRequest.onreadystatechange = this.handleResponse;
			if (this.xmlHttpRequest.overrideMimeType) {               
				this.xmlHttpRequest.overrideMimeType('text/plain');
			}
		}
		
		return true;
	}

	/**
	 * Handles the response of the called script.
	 */
	this.handleResponse = function() {
		if (this.readyState == 4) {
			if (this.status != 200) {
				// throw an exception
				//alert('ajax request http error '+this.status);
			}
			else if (this.responseText != '') {
				// debug output
				//alert(this.responseText);
			}
		}
	}
}