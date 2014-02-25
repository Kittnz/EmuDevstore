/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
/**
 * Checks/unchecks all checkboxes in a given form.
 */
var checkedAll = true;
function checkUncheckAll(parent) {
	var inputs = parent.getElementsByTagName('input');
	for (var i = 0, j = inputs.length; i < j; i++) {
		if (inputs[i].getAttribute('type') == 'checkbox') {
			inputs[i].checked = checkedAll;
		}
	}
	
	checkedAll = (checkedAll) ? false : true;
}

function showOptions() {
	setVisible(arguments, true);
}
function hideOptions() {
	setVisible(arguments, false);
}
function setVisible(elements, visible) {
	for (var i = 0; i < elements.length; i++) {
		var element = document.getElementById(elements[i]);
		
		if (element) {
			if (visible) element.style.display = '';
			else {
				element.style.display = 'none';
			}
		}
	}
}