/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
function Calendar(months, days, startOfWeek) {
	this.months = months.split(',');
	this.days = days.split(',');
	this.startOfWeek = 0;
	this.activeCalendar = '';
	this.space = 0;
	this.currentDate;
	this.monthsLength = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	
	this.setStartOfWeek = function(startOfWeek) {
		this.startOfWeek = startOfWeek;
	
		// Change the Days Array if the Start Date is different
		var y = 0;
		var newDays = new Array();
		for (var x = startOfWeek; x <= 6; x++) {
			newDays[y] = this.days[x];
			y++;
		}
	
		for (var x = 0; x < startOfWeek; x++) {
			newDays[y] = this.days[x];
			y++;
		}
		
		this.days = newDays;
	}
	
	this.setStartOfWeek(startOfWeek);

	/**
	 * Inits a calendar.
	 */
	this.init = function(name) {
		// get button
		var button = document.getElementById(name+'Button');
		if (button) {
			// make visible
			button.style.display = 'block';
			
			// add listener
			button.name = name;
			button.onclick = function() { calendar.show(this.name); return false; }
		}
		
		// get day field
		var dayField = document.getElementById(name+'Day');
		if (dayField) {
			// add listener
			dayField.calendar = name;
			dayField.onchange = function() {  calendar.changeDate(this.calendar); }
		}
		
		// get month field
		var monthField = document.getElementById(name+'Month');
		if (monthField) {
			// add listener
			monthField.calendar = name;
			monthField.onchange = function() { calendar.changeDate(this.calendar); }
		}
		
		// get year field
		var yearField = document.getElementById(name+'Year');
		if (yearField) {
			// add listener
			yearField.calendar = name;
			yearField.onkeyup = function() { calendar.changeDate(this.calendar); }
		}
	}
	
	/**
	 * Changes the current selected date.
	 */
	this.changeDate = function(calendarName) {
		if (calendarName == this.activeCalendar) {
			this.setCurrentDate();
			this.generate();
		}
	}
	
	/**
	 * Closes the active calendar.
	 */
	this.close = function() {
		// get calendar
		var calendarInstance = document.getElementById(this.activeCalendar+'Calendar');
		if (calendarInstance) {
			calendarInstance.style.display = 'none';
		}
		
		this.activeCalendar = '';
	}
	
	/**
	 * Shows a calendar.
	 */
	this.show = function(name) {
		if (this.activeCalendar != '') {
			if (this.activeCalendar == name) {
				return this.close();
			}
			else {
				this.close();
			}
		}
		
		this.activeCalendar = name;
		this.setCurrentDate();
		this.generate();
	}
	
	/**
	 * Calculates the space between the first box in the calendar and the first box in which the 1 is displayed (for the first day of the month)
	 */
	this.setSpace = function() {
		var tempDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
		var day = tempDate.getDay();

		this.space = (6 - this.startOfWeek) + day + 1;
		if (this.space >= 7) this.space = this.space - 7;
	}
	
	/**
	 * Checks if a year is a leap year.
	 */
	this.isLeapYear = function(year) {
		var year = (year >= 2000) ? year : ((year < 80) ? year + 2000 : year + 1900);
		var check1 = year % 4;
		var check2 = year % 100;
		var check3 = year % 400;
		var result = ((check3 == "0") ? (1) : ((check2 == "0") ? (0) : ((check1 == "0") ? (1) : (0))));
		if (result == 0) return false;
		return true;
	}
	
	/**
	 * Checks if a Variable is a Year (space of time: 1900 - 2100)
	 */
	this.isYear = function(value) {
		if (value == "") return false;
	
		for (var x = 1900; x <= 2100; x++) {
			if (value == x) return true;
		}
	
		return false;
	}

	/**
	 * Generates the HTML-Code for the Calendar.
	 */
	this.generate = function() {
		this.setSpace();
		var start = 1;
		var end = this.monthsLength[this.currentDate.getMonth()];
		if (this.currentDate.getMonth() == 1 && this.isLeapYear(this.currentDate.getFullYear())) end = 29;

		// get calendar
		var calendarInstance = document.getElementById(this.activeCalendar+'Calendar');
		if (!calendarInstance) return;
		
		// remove all children
		for (var i = calendarInstance.childNodes.length - 1; i >= 0; i--) {
			calendarInstance.removeChild(calendarInstance.childNodes[i]);
		}
		
		// show calendar
		calendarInstance.style.display = 'block';
		
		// create header table element
		var table = document.createElement('table');
		table.className = 'inlineCalendarHeader';
		calendarInstance.appendChild(table);
		var row = document.createElement('tr');
		table.appendChild(row);
		
		// months
		// backward
		var cell = document.createElement('td');
		cell.className = 'changeElement';
		row.appendChild(cell);
		var link = document.createElement('a');
		if (IS_IE) link.onclick = "calendar.backwardMonth()";
		else link.onclick = function() { calendar.backwardMonth(); };
		cell.appendChild(link);
		link.innerHTML = '&laquo;';
		//link.appendChild(document.createTextNode('<<'));
		
		// current
		cell = document.createElement('td');
		row.appendChild(cell);
		var span = document.createElement('span');
		cell.appendChild(span);
		span.appendChild(document.createTextNode(this.months[this.currentDate.getMonth()]));
		
		// forward
		cell = document.createElement('td');
		cell.className = 'changeElement';
		row.appendChild(cell);
		link = document.createElement('a');
		if (IS_IE) link.onclick = "calendar.forwardMonth()";
		else link.onclick = function() { calendar.forwardMonth(); };
		cell.appendChild(link);
		link.innerHTML = '&raquo;';
		//link.appendChild(document.createTextNode('>>'));
		
		// days
		table = document.createElement('table');
		table.className = 'inlineCalendarTable';
		calendarInstance.appendChild(table);
		row = document.createElement('tr');
		table.appendChild(row);

		// week days
		for (var index = 0; index <= 6; index++) {
			cell = document.createElement('td');
			cell.className = 'weekDays';
			row.appendChild(cell);
			cell.appendChild(document.createTextNode(this.days[index]));
		}
		
		// spaces
		row = document.createElement('tr');
		table.appendChild(row);
		for (var index = 0; index < this.space; index++) {
			cell = document.createElement('td');
			row.appendChild(cell);
		}
		var e = start - 1;
		var mark = 0;
		
		if (this.isYear(document.getElementById(this.activeCalendar+"Year").value) == true) {
			if (document.getElementById(this.activeCalendar+"Year").value == this.currentDate.getFullYear() && 
				this.currentDate.getMonth() == document.getElementById(this.activeCalendar+'Month').selectedIndex - 1) {
				mark = document.getElementById(this.activeCalendar+'Day').selectedIndex;
			}
		}	
	
		// days
		for (var index = 1; index <= end - e; index++) {
			var border = index + this.space;
			var className = 'dayField';
			if (index == mark) {
				className = 'markedDayField';
			}
			
			cell = document.createElement('td');
			cell.className = className;
			row.appendChild(cell);
			
			link = document.createElement('a');
			if (IS_IE) link.onclick = "calendar.setDate("+start+");";
			else {
				link.name = start;
				link.onclick = function() { calendar.setDate(this.name); };
			}
			cell.appendChild(link);
			
			link.appendChild(document.createTextNode(start));
			
			start++;
			if ((border == 7 || border == 14 || border == 21 || border == 28 || border == 35) && index + 1 <= end - e) {
				row = document.createElement('tr');
				table.appendChild(row);
			}
		}
		
		// spaces 2
		while (border % 7 != 0) {
			cell = document.createElement('td');
			row.appendChild(cell);
			border++;
		}
		
		// years
		table = document.createElement('table');
		table.className = 'inlineCalendarFooter';
		calendarInstance.appendChild(table);
		row = document.createElement('tr');
		table.appendChild(row);
		
		// backward
		cell = document.createElement('td');
		cell.className = 'changeElement';
		row.appendChild(cell);
		link = document.createElement('a');
		if (IS_IE) link.onclick = "calendar.backwardYear();";
		else link.onclick = function() { calendar.backwardYear(); };
		cell.appendChild(link);
		link.innerHTML = '&laquo;';
		//link.appendChild(document.createTextNode('<<'));
		
		// current
		cell = document.createElement('td');
		row.appendChild(cell);
		span = document.createElement('span');
		cell.appendChild(span);
		span.appendChild(document.createTextNode(this.currentDate.getFullYear()));
		
		// forward
		cell = document.createElement('td');
		cell.className = 'changeElement';
		row.appendChild(cell);
		link = document.createElement('a');
		if (IS_IE) link.onclick = "calendar.forwardYear();";
		else link.onclick = function() { calendar.forwardYear(); };
		cell.appendChild(link);
		link.innerHTML = '&raquo;';
		//link.appendChild(document.createTextNode('>>'));
		
		// fix ie bugs
		if (IS_IE) {
			calendarInstance.innerHTML = calendarInstance.innerHTML;
			
			// get parent height
			var parentHeight = calendarInstance.parentNode ? calendarInstance.parentNode.offsetHeight : 0;
			calendarInstance.style.marginTop = (- calendarInstance.offsetHeight - parentHeight)+'px';
		}
	}
	
	/**
	 * Internal Handler to set the Current Date which is in Work
	 */
	this.setCurrentDate = function() {
		var today = new Date();

		// get year
		var year = today.getFullYear();
		if (this.isYear(document.getElementById(this.activeCalendar+"Year").value) == true) {
			year = document.getElementById(this.activeCalendar+"Year").value;
		}
		
		// get month
		var month = today.getMonth();
		if (document.getElementById(this.activeCalendar+"Month").selectedIndex != 0) {
			month = document.getElementById(this.activeCalendar+"Month").selectedIndex - 1;
		}
	
		// get day
		var day = today.getDate();
		if (document.getElementById(this.activeCalendar+"Day").selectedIndex != 0) {
			var day = document.getElementById(this.activeCalendar+"Day").selectedIndex;
			if (day > this.monthsLength[month]) {
				day = this.monthsLength[month];
				
				if (month == 1 && this.isLeapYear(year)) {
					day = 29;
				}
			}
		}

		this.currentDate = new Date(year, month, day);
	}
	
	/**
	 * 1 Month backward
	 */
	this.backwardMonth = function() {
		if (this.currentDate.getMonth() == 0) {
			this.currentDate = new Date(this.currentDate.getFullYear() - 1, 11, 1);
		}
		else {
			this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
		}
		
		this.generate();
	}

	/**
	 * 1 Month forward
	 */
	this.forwardMonth = function() {
		if (this.currentDate.getMonth() == 11) {
			this.currentDate = new Date(this.currentDate.getFullYear() + 1, 0, 1);
		}
		else {
			this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
		}
		
		this.generate();
	}
	
	/**
	 * 1 Year backward
	 */
	this.backwardYear = function() {
		this.currentDate = new Date(this.currentDate.getFullYear() - 1, this.currentDate.getMonth(), this.currentDate.getDate());
		this.generate();
	}

	/**
	 * 1 Year forward
	 */
	this.forwardYear = function() {
		this.currentDate = new Date(this.currentDate.getFullYear() + 1, this.currentDate.getMonth(), this.currentDate.getDate());
		this.generate();
	}

	/**
	 * Sets the option-Fields and the input-Field to the Current Date
	 */
	this.setDate = function(day) {
		document.getElementById(this.activeCalendar+'Day').selectedIndex = day;
		document.getElementById(this.activeCalendar+'Month').selectedIndex = this.currentDate.getMonth() + 1;
		document.getElementById(this.activeCalendar+'Year').value = this.currentDate.getFullYear();
		this.close();
	}
}