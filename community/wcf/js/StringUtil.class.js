/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated	use prototype String object: 
 */
function StringUtil(string) {
	this.string = string;
	
	/**
	 * Strips whitespace from the beginning and end of a given string.
	 */
	this.trim = function() {
		this.string = this.string.replace(/^\s+/, '');
		this.string = this.string.replace(/\s+$/, '');
		
		return this.string;
	}
	
	/**
	 * Converts special characters to HTML entities.
	 */
	this.encodeHTML = function() {
		// ampersand
		this.replace('&', '&amp;');
		
		// double quote
		this.replace('"', '&quot;');
		
		// less then
		this.replace('<', '&lt;');
		
		// greater then
		this.replace('>', '&gt;');
		
		return this.string;
	}
	
	/**
	 * Decodes HTML entities.
	 */
	this.decodeHTML = function() {
		// ampersand
		this.replace('&amp;', '&');
		
		// double quote
		//this.replace('"', '&quot;');
		
		// less then
		this.replace('&lt;', '<');
		
		// greater then
		this.replace('&gt;', '>');
		
		return this.string;
	}
	
	/**
	 * Replaces all occurrences of the search string with the replacement string.
	 */
	this.replace = function(search, replace) {
		var searchStart = 0;
		while (true) {
			var start = this.string.indexOf(search, searchStart);
			if (start == -1) {
				break;
			}
			
			this.string = this.string.substring(0, start) + replace + this.string.substring(start + search.length);
			searchStart = start + replace.length;
		}
		
		return this.string;
	}
	
	/**
	 * Replaces all occurrences of the search string with the replacement string.
	 */
	this.firstCharToUpperCase = function() {
		
		return this.string.slice(0, 1).toUpperCase() + this.string.slice(1, this.string.length);
	}
}