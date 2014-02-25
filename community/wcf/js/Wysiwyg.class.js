/**
 * @author	Benjamin Kunz, Marcel Werk
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
/** 
 * CONTRUCTOR OF ENGINE CLASS
 */
function TinyMCE_Engine() {
	this.initialized = false;
	this.instances = new Array();
	this.configs = new Array();
	this.currentConfig = 0;
	this.eventHandlers = new Array();
	this.switchClassCache = new Array();

	// Browser check
	this.isMSIE = IS_IE;		
	this.isGecko = IS_MOZILLA;	
	this.isSafari = IS_SAFARI;	
	this.isOpera = IS_OPERA;
	this.isMSIE7 = IS_IE7;
	this.isKonqueror = IS_KONQUEROR;
	this.isChrome5 = false;
	
	this.settings = new Array();
	
	if (this.isOpera) {
		this.isGecko = true;
	}
	else if (this.isSafari) {
		this.isGecko = true;
		
		// remove safari2 support
		//if (USER_AGENT.match(/version\/3\./i) || USER_AGENT.match(/chrome/i) || USER_AGENT.match(/version\/4\./i) || USER_AGENT.match(/version\/5\./i)) {
			this.isSafari3 = true;	
			this.isSafari = false;
			
			if (USER_AGENT.match(/chrome\/5\./i)) {
				this.isChrome5 = true;
			}
		//}
	}
	
	this.isFirefox = (USER_AGENT.indexOf('firefox') != -1) ? true : false;
	
	// TinyMCE editor id instance counter
	this.idCounter = 0;
	
	// Flag to detect if simplearea is active
	this.isSimpleTextarea = false;
	// TODO: This should be dynamic to be able to create more than one simple textarea
	this.simpleAreaID = 'text';
	this.simpleTextarea = null;
	
	// Define elements which should be a tiny-wysiwyg editor
	this.elements = ['text'];
	
	// Define tab ids and active class
	//this.codeTabID = "codeTab";
	//this.editorTabID = "editorTab";
	this.tabsActiveClass = "activeTabMenu";
		
	// ... toolbar elements order:
	this.toolElements = [["font","size","separator","b","i","u","s","separator","align","separator","list","separator","color","break"], 
			["undo","redo","separator","url","separator","img","separator","quotation","quote","code"]];//"copy","cut","paste","separator",		
	
};
/** 
 * CLASS-METHODS OF ENGINE 
 */
TinyMCE_Engine.prototype = {
	init : function(settings) {
		this.settings = settings;
		this.editorEnableWysiwygView = settings['editorEnableWysiwygView'];
		this.editorEnableCodeView = settings['editorEnableCodeView'];
		// Give incompatible browsers the possibility to insert BBCode tags via javascript
		// No WYSIWYG (insert BBCode tags instead)
		// If admin didn't enabled WYSIWYG or code view just show users the simple textarea
		if (typeof(document.execCommand) == 'undefined' || (tinyMCE.isSafari && !tinyMCE.isSafari3) || (!this.editorEnableWysiwygView && !this.editorEnableCodeView) || this.isKonqueror) {
			tinyMCE.isSimpleTextarea = true;
			window.setTimeout("tinyMCE.loadSimpleTextarea()", 5);
		}
		// WYSIWYG for all compatible browsers
		else {
			tinyMCE.isSimpleTextarea = false;
			
			// Get script base path
			if (!tinyMCE.baseURL) {
				var elements = document.getElementsByTagName('script');
				for (var i = 0; i < elements.length; i++) {
					if (elements[i].src && (elements[i].src.indexOf('Wysiwyg.class.js') != -1)) {
						var src = elements[i].src;
						tinyMCE.baseURL = src.substring(0, src.lastIndexOf('/'));
						break;
					}
				}
			}

			// Get document base path
			this.documentBasePath = document.location.href;
			if (this.documentBasePath.indexOf('?') != -1) {
				this.documentBasePath = this.documentBasePath.substring(0, this.documentBasePath.indexOf('?'));
			}
			this.documentURL = this.documentBasePath;
			this.documentBasePath = this.documentBasePath.substring(0, this.documentBasePath.lastIndexOf('/'));

			// If not HTTP absolute
			if (tinyMCE.baseURL.indexOf('://') == -1 && tinyMCE.baseURL.charAt(0) != '/') {
				// If site absolute
				tinyMCE.baseURL = this.documentBasePath + "/" + tinyMCE.baseURL;
			}
		
			// Sets default values on settings
			this.settings['document_base_url'] = this.documentURL;
			var baseTags = document.getElementsByTagName('base');
			if (baseTags.length > 0) {
				this.settings['document_base_url'] = baseTags[0].href;
			}

			this.settings['custom_undo_redo'] = true;
			this.settings['custom_undo_redo_levels'] = 10;
			this.settings['custom_undo_redo_keyboard_shortcuts'] = true;
			this.settings['custom_undo_redo_restore_selection'] = true;
			this.settings['entities'] = "39,#39,160,nbsp,161,iexcl,162,cent,163,pound,164,curren,165,yen,166,brvbar,167,sect,168,uml,169,copy,170,ordf,171,laquo,172,not,173,shy,174,reg,175,macr,176,deg,177,plusmn,178,sup2,179,sup3,180,acute,181,micro,182,para,183,middot,184,cedil,185,sup1,186,ordm,187,raquo,188,frac14,189,frac12,190,frac34,191,iquest,192,Agrave,193,Aacute,194,Acirc,195,Atilde,196,Auml,197,Aring,198,AElig,199,Ccedil,200,Egrave,201,Eacute,202,Ecirc,203,Euml,204,Igrave,205,Iacute,206,Icirc,207,Iuml,208,ETH,209,Ntilde,210,Ograve,211,Oacute,212,Ocirc,213,Otilde,214,Ouml,215,times,216,Oslash,217,Ugrave,218,Uacute,219,Ucirc,220,Uuml,221,Yacute,222,THORN,223,szlig,224,agrave,225,aacute,226,acirc,227,atilde,228,auml,229,aring,230,aelig,231,ccedil,232,egrave,233,eacute,234,ecirc,235,euml,236,igrave,237,iacute,238,icirc,239,iuml,240,eth,241,ntilde,242,ograve,243,oacute,244,ocirc,245,otilde,246,ouml,247,divide,248,oslash,249,ugrave,250,uacute,251,ucirc,252,uuml,253,yacute,254,thorn,255,yuml,402,fnof,913,Alpha,914,Beta,915,Gamma,916,Delta,917,Epsilon,918,Zeta,919,Eta,920,Theta,921,Iota,922,Kappa,923,Lambda,924,Mu,925,Nu,926,Xi,927,Omicron,928,Pi,929,Rho,931,Sigma,932,Tau,933,Upsilon,934,Phi,935,Chi,936,Psi,937,Omega,945,alpha,946,beta,947,gamma,948,delta,949,epsilon,950,zeta,951,eta,952,theta,953,iota,954,kappa,955,lambda,956,mu,957,nu,958,xi,959,omicron,960,pi,961,rho,962,sigmaf,963,sigma,964,tau,965,upsilon,966,phi,967,chi,968,psi,969,omega,977,thetasym,978,upsih,982,piv,8226,bull,8230,hellip,8242,prime,8243,Prime,8254,oline,8260,frasl,8472,weierp,8465,image,8476,real,8482,trade,8501,alefsym,8592,larr,8593,uarr,8594,rarr,8595,darr,8596,harr,8629,crarr,8656,lArr,8657,uArr,8658,rArr,8659,dArr,8660,hArr,8704,forall,8706,part,8707,exist,8709,empty,8711,nabla,8712,isin,8713,notin,8715,ni,8719,prod,8721,sum,8722,minus,8727,lowast,8730,radic,8733,prop,8734,infin,8736,ang,8743,and,8744,or,8745,cap,8746,cup,8747,int,8756,there4,8764,sim,8773,cong,8776,asymp,8800,ne,8801,equiv,8804,le,8805,ge,8834,sub,8835,sup,8836,nsub,8838,sube,8839,supe,8853,oplus,8855,otimes,8869,perp,8901,sdot,8968,lceil,8969,rceil,8970,lfloor,8971,rfloor,9001,lang,9002,rang,9674,loz,9824,spades,9827,clubs,9829,hearts,9830,diams,34,quot,38,amp,60,lt,62,gt,338,OElig,339,oelig,352,Scaron,353,scaron,376,Yuml,710,circ,732,tilde,8194,ensp,8195,emsp,8201,thinsp,8204,zwnj,8205,zwj,8206,lrm,8207,rlm,8211,ndash,8212,mdash,8216,lsquo,8217,rsquo,8218,sbquo,8220,ldquo,8221,rdquo,8222,bdquo,8224,dagger,8225,Dagger,8240,permil,8249,lsaquo,8250,rsaquo,8364,euro";
			this.settings['strict_loading_mode'] = document.contentType == 'application/xhtml+xml';
			
			// Forces strict loading mode to false on non Gecko browsers
			if (this.isMSIE && !this.isOpera) this.settings.strict_loading_mode = false;
	
			// Browser check: if browser is not supported -> no WYSIWYG editor.
			if (!this.isMSIE && !this.isGecko && !this.isSafari && !this.isOpera) return;
				
			// If not super absolute make it so
			var baseHREF = tinyMCE.settings['document_base_url'];
			var h = document.location.href;
			var p = h.indexOf('://');
			if (p > 0 && document.location.protocol != "file:") {
				p = h.indexOf('/', p + 3);
				h = h.substring(0, p);
	
				if (baseHREF.indexOf('://') == -1) {
					baseHREF = h + baseHREF;
				}
	
				tinyMCE.settings['document_base_url'] = baseHREF;
				tinyMCE.settings['document_base_prefix'] = h;
			}
		
			// Trims away query part
			if (baseHREF.indexOf('?') != -1) baseHREF = baseHREF.substring(0, baseHREF.indexOf('?'));
			this.settings['base_href'] = baseHREF.substring(0, baseHREF.lastIndexOf('/')) + "/";
			
			this.posKeyCodes = new Array(13,45,36,35,33,34,37,38,39,40);
			this.uniqueTag = '<div id="mceTMPElement" style="display: none">TMP</div>';
		
			// Only do this once
			if (this.configs.length == 0) {
				if (typeof(TinyMCECompressed) == "undefined") {
					tinyMCE.addEvent(window, "DOMContentLoaded", TinyMCE_Engine.prototype.onLoad);
	
					if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
						if (document.body) {
							tinyMCE.addEvent(document.body, "readystatechange", TinyMCE_Engine.prototype.onLoad);
						}
						else {
							tinyMCE.addEvent(document, "readystatechange", TinyMCE_Engine.prototype.onLoad);
						}
					}
					tinyMCE.addEvent(window, "load", TinyMCE_Engine.prototype.onLoad);
					tinyMCE._addUnloadEvents();
				}
			}
			// Setup entities
			this.settings['cleanup_entities'] = new Array();
			var entities = tinyMCE.getParam('entities', '', true, ',');
			for (var i = 0; i < entities.length; i+=2) {
				this.settings['cleanup_entities']['c' + entities[i]] = entities[i+1];
			}
	
			// Saves this config
			this.settings['index'] = this.configs.length;
			this.configs[this.configs.length] = this.settings;
		}
	},
	
	/* Gets a setting value */
	getParam : function(name, default_value, strip_whitespace, split_chr) {
		var value = (typeof(this.settings[name]) == "undefined") ? default_value : this.settings[name];

		// Fixes boolean values
		if (value == "true" || value == "false") return (value == "true");

		if (typeof(split_chr) != "undefined" && split_chr != null) {
			value = value.split(split_chr);
			var outArray = new Array();

			for (var i = 0; i < value.length; i++) {
				if (value[i] && value[i] != "")	outArray[outArray.length] = value[i];
			}
			value = outArray;
		}
		return value;
	},
	
	/* Adds a tiny control object to each element (from settings) */
	onLoad : function() {
		if (tinyMCE.isMSIE && !tinyMCE.isOpera && window.event.type == "readystatechange" && document.readyState != "complete") return true;
		if (tinyMCE.isLoaded) return true;
		tinyMCE.isLoaded = true;
		
		// Detects extra bbcodes and buttons
		tinyMCE.hasExtraBBcodes = false;
		tinyMCE.extraHTMLOpenTags = '';
		tinyMCE.extraHTMLCloseTags = '';
		tinyMCE.nonClosableHTMLTags = '';
		tinyMCE.extraAttributes = '';
		for (var code in extraBBCodes) {
			if (code['wysiwyg']) {
				tinyMCE.hasExtraBBcodes = true;
				tinyMCE.extraHTMLOpenTags += '|' + extraBBCodes[code]['htmlOpen'];
				if (extraBBCodes[code]['htmlClose'] != '') {
					tinyMCE.extraHTMLCloseTags += '|' + extraBBCodes[code]['htmlClose'];
				}
				else {
					tinyMCE.nonClosableHTMLTags += '|' + extraBBCodes[code]['htmlOpen'];
				}
				for (var i = 0; i < extraBBCodes[code]['attributes'].length; i++) {
					if (tinyMCE.extraAttributes == '') tinyMCE.extraAttributes = extraBBCodes[code]['attributes'][i]['attributeHTML'].replace(/(\w+)=.*/, '$1');
					tinyMCE.extraAttributes += '|' + extraBBCodes[code]['attributes'][i]['attributeHTML'].replace(/(\w+)=.*/, '$1');
				}
			}
		}
		
		// Builds regex for all code BBCodes
		tinyMCE.codeRegex = '(';
		var i = 0;
		for(var bbCode in sourceCodes) {
			if (i > 0) tinyMCE.codeRegex += '|';
			tinyMCE.codeRegex += bbCode;
			i++;
		}
		tinyMCE.codeRegex += ')';

		tinyMCE.dispatchCallback(null, 'onpageload', 'onPageLoad');
		for (var c = 0; c < tinyMCE.configs.length; c++) {
			tinyMCE.settings = tinyMCE.configs[c];
			var elementRefAr = new Array();

			// Adds submit triggers
			if (document.forms && !tinyMCE.submitTriggers) {
				for (var i = 0; i < document.forms.length; i++) {
					var form = document.forms[i];

					tinyMCE.addEvent(form, "submit", TinyMCE_Engine.prototype.handleEvent);
					tinyMCE.addEvent(form, "reset", TinyMCE_Engine.prototype.handleEvent);
					tinyMCE.submitTriggers = true; 

					// Patches the form.submit function
					try {
						form.mceOldSubmit = form.submit;
						form.submit = TinyMCE_Engine.prototype.submitPatch;
					} 
					catch (e) {}
				}
			}
			// Just take textareas which are in tinyMCE.elements
			var elements = tinyMCE.elements;
			for (var i = 0; i < elements.length; i++) {
				var element = document.getElementById(elements[i]);
				if (element) {
// Non multiple tiny area				
// TODO: Make split into WYSIWYG with "addMCEControl" and for simple textarea a new function "addSimpleControl"
// to be able to handle more than one simple textarea	
					tinyMCE.addMCEControl(element, elements[i]);
				}
				if (document.getElementById(elements[i]+'Div')) {
					document.getElementById(elements[i]+'Div').className += ' editor';
				}
			}
			tinyMCE.dispatchCallback(null, 'oninit', 'onInit');
		}
		
		// Checks if the div with id "editor" exists. if so add class "editor" with special width to the container.
		/*var editorDiv = document.getElementById("editor");
		if (editorDiv) editorDiv.className = editorDiv.className + ' editor'; */
		
		// Places cursor in editor or subject
		tinyMCE.initCursor();
		
		tinyMCE.initialized = true;
	},
	
	/*  */
	setupContent : function(editor_id) {
		var inst = tinyMCE.instances[editor_id];
		var doc = inst.getDoc();
		
		var head = doc.getElementsByTagName('head').item(0);
		var content = inst.startContent;
		
		tinyMCE.selectedInstance = inst;//209 changes
		inst.switchSettings();
		if (!head) {
			window.setTimeout("tinyMCE.setupContent('" + editor_id + "');", 10);
			return;
		}
		
		// Setup keyboard shortcuts
		inst.addShortcut('ctrl', 'z', language['undo.desc'], 'Undo');
		inst.addShortcut('ctrl', 'y', language['redo.desc'], 'Redo');
		inst.addShortcut('ctrl', 'k', language['link.desc'], 'mceLink');
		
		// Adds default shortcuts for Gecko
		if (tinyMCE.isGecko) {
			inst.addShortcut('ctrl', 'b', language['bold.desc'], 'Bold');
			inst.addShortcut('ctrl', 'i', language['italic.desc'], 'Italic');
			inst.addShortcut('ctrl', 'u', language['underline.desc'], 'Underline');
		}

		doc.body.dir = languageDirection;
		doc.editorId = editor_id;

		if (!tinyMCE.isMSIE) doc.documentElement.editorId = editor_id;
		
		if (!inst.editorIsActive) {
			content = tinyMCE.encodeHTMLEntities(content);
		}
		
		// Adds on document element in Mozilla
		if (!tinyMCE.isMSIE) {
			inst.getBody().innerHTML = content;
		}
		else {
			var body = inst.getBody();
			// TODO: The following line makes the IE scrollbar act crazy if the forum is nested in a <table>
			body.editorId = editor_id;
			tinyMCE._setHTML(inst.getDoc(), content);
		}

		// Setup element references // in 210 this is moved to _onadd
		var parentElm = inst.targetDoc.getElementById(inst.editorId + '_parent');
		inst.formElement = tinyMCE.isGecko ? parentElm.previousSibling : parentElm.nextSibling;

		tinyMCE.dispatchCallback(inst, 'setupcontent_callback', 'setupContent', editor_id, inst.getBody(), inst.getDoc());

		// Re-adds design mode in Mozilla
		if (/*inst.editorIsActive && */!tinyMCE.isMSIE) tinyMCE.addEventHandlers(inst);

		// Adds blur handler
		if (tinyMCE.isMSIE) {
			tinyMCE.addEvent(inst.getBody(), "blur", TinyMCE_Engine.prototype._eventPatch);
			tinyMCE.addEvent(inst.getBody(), "beforedisable", TinyMCE_Engine.prototype._eventPatch); // Bug #1439953
		}

		// Triggers node change, this call locks buttons for links and so 
		tinyMCE.selectedInstance = inst;
		tinyMCE.selectedElement = inst.contentWindow.document.body;
		
		if (inst.editorIsActive) {
			inst.startContent = tinyMCE.trim(inst.getBody().innerHTML);
		}
		inst.undoRedo.add({ content : inst.startContent });

		// Cleans up mess left from storeAwayURLs
		if (tinyMCE.isGecko) {
			// Remove mce_src from textnodes and comments
			tinyMCE.selectNodes(inst.getBody(), function(n) {
				if (n.nodeType == 3 || n.nodeType == 8) {
					n.nodeValue = n.nodeValue.replace(new RegExp('\\smce_src=\"[^\"]*\"', 'gi'), "");
					n.nodeValue = n.nodeValue.replace(new RegExp('\\smce_href=\"[^\"]*\"', 'gi'), "");
				}
				return false;
			});
		}

		tinyMCE.selectedInstance = inst;
		tinyMCE.triggerNodeChange(true);  

		// Places cursor after existing content (quote)
		if (!tinyMCE.isFirefox && inst.getBody().innerHTML.match(/<blockquote username=.*?<\/blockquote>/)) {
			var textElement = inst.getDoc().createTextNode(' ');
			inst.getBody().appendChild(textElement);
			inst.selection.selectNode(textElement);
			inst.contentWindow.focus();
		}
	},
	
	/* Adds functions to the "onUnload" event of a window */	
	_addUnloadEvents : function() {
		if (tinyMCE.isMSIE) {
			tinyMCE.addEvent(window, "unload", TinyMCE_Engine.prototype.unloadHandler);
			tinyMCE.addEvent(window.document, "beforeunload", TinyMCE_Engine.prototype.unloadHandler);
		} else {
			tinyMCE.addEvent(window, "unload", function () {tinyMCE.triggerSave(true, true);});
		}
	},
	
	/*  */
	dispatchCallback : function(i, p, n) {
		return this.callFunc(i, p, n, 0, this.dispatchCallback.arguments);
	},
	
	/* */
	callFunc : function(ins, p, n, m, a) {

		var l, i, on, o, s, v;
		s = m == 2;
		l = tinyMCE.getParam(p, '');
		if (l != '' && (v = tinyMCE.evalFunc(l, 3, a)) == s && m > 0) { // 209
			return true;
		}
		return false;
	},
	
	/* Wrapper for eval */
	evalFunc : function(f, idx, a) { //209
		o = !o ? window : o;
		f = typeof(f) == 'function' ? f : o[f];
		return f.apply(o, Array.prototype.slice.call(a, idx));
	},
	
	/* Returns if object is an instance of the control class*/
	isInstance : function(instance) {
		return instance != null && typeof(instance) == "object" && instance.isTinyMCE_Control;
	},
	
	/* Wrapper for the submit action */
	triggerSave : function(skip_cleanup, skip_callback) {
		var inst, n;

		// Default to false
		if (typeof(skip_cleanup) == "undefined") skip_cleanup = false;

		// Default to false
		if (typeof(skip_callback) == "undefined") skip_callback = false;
		
		// Real tinyMCE 
		if (tinyMCE.instances) {
			// Cleanup and set all form fields
			for (n in tinyMCE.instances) {
				inst = tinyMCE.instances[n];
				if (!tinyMCE.isInstance(inst)) continue;
				inst.triggerSave(skip_cleanup, skip_callback);
			}
		}
	},
	
	/* Wrapper for regex replace */
	regexpReplace : function(in_str, reg_exp, replace_str, opts) {
		if (in_str == null) return in_str;
		if (typeof(opts) == "undefined") opts = 'g';
		var re = new RegExp(reg_exp, opts);
		return in_str.replace(re, replace_str);
	},
	
	/* Sets the content of the editable iframe */
	_setHTML : function(doc, html_content) {
		// Try innerHTML if it fails use pasteHTML in MSIE
		try {
			tinyMCE.setInnerHTML(doc.body, html_content);
		} catch (e) {
			if (this.isMSIE) doc.body.createTextRange().pasteHTML(html_content);
		}

		// Content duplication bug fix
		if (tinyMCE.isMSIE) {
			// Remove P elements in P elements
			var paras = doc.getElementsByTagName("P");
			for (var i = 0; i < paras.length; i++) {
				var node = paras[i];
				while ((node = node.parentNode) != null) {
					if (node.nodeName == "P") {
						node.outerHTML = node.innerHTML;
					}
				}
			}
			// Content duplication bug fix (Seems to be word crap)
			var html = doc.body.innerHTML;

			// Always set the htmlText output
			tinyMCE.setInnerHTML(doc.body, html);
		}
	},

	/* Adds a control instance to the instances array. */
	addMCEControl : function(replace_element, form_element_name, target_document) {
		var id = "mce_editor_" + tinyMCE.idCounter++;
		var inst = new TinyMCE_Control(tinyMCE.settings);
		inst.editorId = id;
		inst.editorIsActive = tinyMCE.settings['editorIsActive'];
		inst.tabindex = replace_element.getAttribute('tabindex');
		inst.editorTabID = id + '_editor_tab';
		inst.codeTabID = id + '_code_tab';
		this.instances[id] = inst;
		inst._onAdd(replace_element, form_element_name, target_document);

		// Adds a textarea for change the view to code view
		inst.theTextarea = document.getElementById(id+'_codeview');
		
		// Setup content for code view (get DB content);
		if (!inst.editorIsActive) {
			inst.theTextarea.value = inst.startContent;
		}
	},
	
	/* Handle user events */
	handleEvent : function(e) {
		var inst = tinyMCE.selectedInstance;
		
		// Removes odd, error
		if (typeof(tinyMCE) == "undefined") return true;
		if (tinyMCE.executeCallback(tinyMCE.selectedInstance, 'handle_event_callback', 'handleEvent', e)) {
			return false;
		}

		switch (e.type) {
			case "beforedisable": // Was added due to bug #1439953
			case "blur":
				if (tinyMCE.selectedInstance) {
					tinyMCE.selectedInstance.execCommand('mceEndTyping');
				}
				if (!tinyMCE.isOpera) {
					inst.getBody().setAttribute('class', 'iframeBody');
					inst.getBody().parentNode.setAttribute('class', 'iframeHTML');
				}
				return;
			
			case "submit":
				tinyMCE.triggerSave();
				return;

			case "reset":
				var formObj = tinyMCE.isMSIE ? window.event.srcElement : e.target;
				for (var i = 0; i < document.forms.length; i++) {
					if (document.forms[i] == formObj) {
						window.setTimeout('tinyMCE.resetForm(' + i + ');', 10);
					}
				}
				return;

			case "keypress":

				if (!inst.editorIsActive || (inst && inst.handleShortcut(e))) return false;
				if (e.target.editorId) {
					tinyMCE.selectedInstance = tinyMCE.instances[e.target.editorId];
				} else if (e.target.ownerDocument.editorId) {
					tinyMCE.selectedInstance = tinyMCE.instances[e.target.ownerDocument.editorId];
				}

				if (tinyMCE.selectedInstance) {
					tinyMCE.selectedInstance.switchSettings();
				}
				
				// Backspace or delete
				if (e.keyCode == 8 || e.keyCode == 46) {
					tinyMCE.selectedElement = e.target;
					tinyMCE.linkElement = tinyMCE.getParentElement(e.target, "a");
					tinyMCE.imgElement = tinyMCE.getParentElement(e.target, "img");
					tinyMCE.triggerNodeChange();
				}
				return false;
			break;

			case "keydown":
			case "keyup":
				if ((!inst.editorIsActive) || (inst && inst.handleShortcut(e))) return false;
				if (e.target.editorId) tinyMCE.selectedInstance = tinyMCE.instances[e.target.editorId];
				else return;

				// TODO: Leave blockquote on key enter if cursor is at the end of the block
				if (e.type == 'keydown' && e.keyCode == 13 && tinyMCE.selectedInstance.getFocusElement().nodeName.toLowerCase() == 'blockquote') {
					var instance = tinyMCE.selectedInstance;
					var focusElement = instance.getFocusElement();
					var selection = instance.selection;
					var range = selection.getRng();
	
					// Deletes empty blockquotes
					if (tinyMCE.getInnerText(focusElement) == '') {
						focusElement.parentNode.removeChild(focusElement);
					}
					// TODO: Exit the blockquote when cursor is on the end of it
					else if(0) {
						var emptyElement = instance.getDoc().createElement('span');
						emptyElement.id = '_exitBlockQuoteDumy_';
						range.insertNode(emptyElement);
						var dummy = instance.getDoc().getElementById('_exitBlockQuoteDumy_');
						if (!dummy.nextSibling || (dummy.nextSibling.nodeName.toLowerCase() == 'br')) {
							dummy.parentNode.removeChild(dummy);
						}
					}
				}
				
				if (tinyMCE.selectedInstance) tinyMCE.selectedInstance.switchSettings();
				inst = tinyMCE.selectedInstance;
				tinyMCE.selectedElement = null;
				tinyMCE.selectedNode = null;
				
				var elm = tinyMCE.selectedInstance.getFocusElement();
				if (e.type == 'keydown' && inst.editorIsActive) inst.detectPaste(e);
				
				// Auto insert smiley if a smiley-code was written.
				// Opera has problems with detecting key events. try again
// Turned off due to performance problems				
				if (0 && e.type == 'keyup' && inst.editorIsActive && !tinyMCE.isOpera && !tinyMCE.isSafari) {
					// No strg action (paste, select all, etc.) 
					if  (!(e.ctrlKey) && (	
							(e.keyCode == 16) ||
							(e.keyCode == 18) ||
							(e.keyCode >= 48 && e.keyCode <= 57) ||
							(e.keyCode >= 65 && e.keyCode <= 90) ||
							(e.keyCode >= 96 && e.keyCode <= 105)||
							(e.keyCode == 107) ||
							(e.keyCode >= 109 && e.keyCode <= 111)||
							(e.keyCode >= 186 && e.keyCode <= 192)||
							(e.keyCode >= 219 && e.keyCode <= 222)) 
						) {
 					
						// On each keydown check if a smiley code is inside the focus element
						for (var smileyCode in smilies) {
							var smileyRegex = new RegExp(smileyCode.pregQuote()+'(?![^<]*>)', 'g'); 
						
// TODO: Instead of regexing through the whole html content, just get the actual cursor position and
//		check the actual position. no matter if someone edits the code
							
							// Smiley code found // decode to avoid &gt;) is parsed to a smiley
							if (tinyMCE.decodeHTMLEntities(elm.innerHTML).match(smileyRegex)) {
								var url = smilies[smileyCode][0];
								if (tinyMCE.isMSIE) {
									url = tinyMCE.settings['base_href'] + url;
								}

								// Inserts smiley placeholder			
								var html = '<span id="smileyPlaceHolder"></span>';
								tinyMCE.execInstanceCommand(inst.editorId, "mceInsertContent", false, html);
								
								// Gets the placeholder element
								var smileyPlaceHolder = inst.getDoc().getElementById("smileyPlaceHolder");
								smileyPlaceHolder.id = '';
								
								var previousNode = smileyPlaceHolder.previousSibling;
								var parentElement = null;
								if (previousNode) parentElement = previousNode.parentNode;
								var newNode = inst.getDoc().createElement('span');
								var smileyImage = ' <img src="' + url + '" alt="' + smileyCode + '" /> ';
								
								// The node ahead the placeholder is a text node 
								// in this node is at least the start of the smiley code
								if (previousNode && previousNode.nodeType == 3) {
									// Not encoded to avoid &gt;) is parsed to a smiley
									var leadingText = previousNode.nodeValue;

									// First check if the smileycode is in the data ahead the placeholder.
									if (leadingText.match(smileyRegex)) {
										leadingText = leadingText.replace(smileyRegex, smileyImage);
										newNode.innerHTML = leadingText + "&nbsp;";
										parentElement.replaceChild(newNode, previousNode);
									}
								}
								// The node ahead the placeholder is an element node where the smiley code is inside (at least the start of the code) 
								else if (previousNode) {
									var previousFirstChild = previousNode.firstChild;
									var leadingHTML = previousNode.innerHTML;
						
									// The smiley code is ahead the placeholder => the cursor is behind the code
									// decode to avoid &gt;) is parsed to a smiley
									if (tinyMCE.decodeHTMLEntities(leadingHTML).match(smileyRegex)) {
										newNode.innerHTML = leadingHTML.replace(smileyRegex, smileyImage) + '&nbsp;';
										previousNode.innerHTML = '';
										previousNode.appendChild(newNode);
									}
									// The smiley regex wraps around the placeholder
									// Does not work if more than one HTML element wraps around
									else {
										var nextNode = smileyPlaceHolder.nextSibling;
										var followingHTML = nextNode.innerHTML;
										var wrappingHTML = leadingHTML + followingHTML;
										// Decodes to avoid &gt;) is parsed to a smiley
										if (tinyMCE.decodeHTMLEntities(wrappingHTML).match(smileyRegex)) {
											nextNode.parentNode.removeChild(nextNode);
											var wrappingNodeName = previousNode.nodeName;
											var newNode = inst.getDoc().createElement(wrappingNodeName);
											newNode.innerHTML = wrappingHTML + "OTHER NODETYPE + CURSOR BETWEEN CODE CCC";
											newNode.style['cssText'] = previousNode.style['cssText'];
											previousNode.parentNode.replaceChild(newNode, previousNode);
										}
									}
								}
								// Sets cursor postion for Safari 3 
								if (parentElement && tinyMCE.isSafari3) {
									var newNode = inst.getDoc().createElement('span');
									parentElement.appendChild(newNode);
									inst.selection.selectNode(newNode);
									parentElement.removeChild(newNode);
								}
								
								// Deletes placeholder
								if (smileyPlaceHolder && smileyPlaceHolder.parentNode) smileyPlaceHolder.parentNode.removeChild(smileyPlaceHolder);
								// if code found no need to search for the other codes
								break;
							}
						}
					}
				}
								
				tinyMCE.linkElement = tinyMCE.getParentElement(elm, "a");
				tinyMCE.imgElement = tinyMCE.getParentElement(elm, "img");
				tinyMCE.selectedElement = elm;
			
				// Fixes empty elements on return/enter, check where enter occured
				if (tinyMCE.isMSIE && e.type == "keydown" && e.keyCode == 13) {
					tinyMCE.enterKeyElement = tinyMCE.selectedInstance.getFocusElement();
				}

				// Fixes empty elements on return/enter
				if (tinyMCE.isMSIE && e.type == "keyup" && e.keyCode == 13) {
					var elm = tinyMCE.enterKeyElement;
					if (elm) {
						var re = new RegExp('^HR|IMG|BR$','g'); // Skip these
						var dre = new RegExp('^H[1-6]$','g'); // Add double on these
						
						if (!elm.hasChildNodes() && !re.test(elm.nodeName)) {
							if (dre.test(elm.nodeName)) {
								elm.innerHTML = "&nbsp;&nbsp;";
							}
							else {
								elm.innerHTML = "&nbsp;";
							}
						}
					}
				}

				// Checkes if it's a position key
				var keys = tinyMCE.posKeyCodes;
				var posKey = false;
				for (var i = 0; i < keys.length; i++) {
					if (keys[i] == e.keyCode) {
						posKey = true;
						break;
					}
				}

				// IE custom key handling
				if (tinyMCE.isMSIE) {
					var keys = new Array(8,46); // Backspace,Delete
					for (var i = 0; i < keys.length; i++) {
						if (keys[i] == e.keyCode) {
							if (e.type == "keyup") {
								tinyMCE.triggerNodeChange();
							}
						}
					}
				}

				// If ctrl key
				if (e.keyCode == 17) return true;
				
				// Handle undo/redo when typing content


				// tinyMCE 208 changes
				if (tinyMCE.isGecko) {
					// Start typing (not a position key or ctrl key, but ctrl+x and ctrl+p is ok)
					if (!posKey && e.type == "keyup" && !e.ctrlKey || (e.ctrlKey && (e.keyCode == 86 || e.keyCode == 88))) {
						tinyMCE.execCommand("mceStartTyping");
					}
				} else {
					// IE seems to work better with this setting
					if (!posKey && e.type == "keyup") {
						tinyMCE.execCommand("mceStartTyping");
					}
				}


				// Store undo bookmark
				if (e.type == "keydown" && (posKey || e.ctrlKey) && inst) {
					inst.undoBookmark = inst.selection.getBookmark();
				}

				// End typing (position key) or some ctrl event
				if (e.type == "keyup" && (posKey || e.ctrlKey)) {
					tinyMCE.execCommand("mceEndTyping");
				}
				

				if (posKey && e.type == "keyup") {
					tinyMCE.triggerNodeChange();
				}

				if (tinyMCE.isMSIE && e.ctrlKey) {
					window.setTimeout('tinyMCE.triggerNodeChange();', 1);
				}
			break;

			case "mousedown":
			case "mouseup":
			case "click":
			case "focus":
				if (tinyMCE.selectedInstance) {
					tinyMCE.selectedInstance.switchSettings();
					tinyMCE.selectedInstance.isFocused = true;
				}
				
				// Checks instance event triggered on
				var targetBody = tinyMCE.getParentElement(e.target, "html"); 
				for (var instanceName in tinyMCE.instances) {
					if (!tinyMCE.isInstance(tinyMCE.instances[instanceName])) continue;

					var inst = tinyMCE.instances[instanceName];
					
					// Resets design mode if lost (on everything just in case)
					inst.autoResetDesignMode();

					// tinyMCE 208 changes
					if (inst.getBody().parentNode == targetBody) {
						tinyMCE.selectedInstance = inst;
						tinyMCE.selectedElement = e.target;
						tinyMCE.linkElement = tinyMCE.getParentElement(tinyMCE.selectedElement, "a");
						tinyMCE.imgElement = tinyMCE.getParentElement(tinyMCE.selectedElement, "img");
						break;
					}
				}
				
				// Adds first bookmark location
				// tinyMCE 208 changes				
				if (!tinyMCE.selectedInstance.undoRedo.undoLevels[0].bookmark && e.type == "mouseup") {
					tinyMCE.selectedInstance.undoRedo.undoLevels[0].bookmark = tinyMCE.selectedInstance.selection.getBookmark();
				}

				// Resets selected node
				if (e.type != "focus") tinyMCE.selectedNode = null;

				tinyMCE.triggerNodeChange();

				tinyMCE.execCommand("mceEndTyping");

				if (e.type == "mouseup") {
					tinyMCE.execCommand("mceAddUndoLevel");
				}

				// Just in case ...
				if (!tinyMCE.selectedInstance && e.target.editorId) {
					tinyMCE.selectedInstance = tinyMCE.instances[e.target.editorId];
				}
				return false;
			break;
		}
	},
	
	/*  */
	applyTemplate : function(h, as) {
		var i, s, ar = h.match(new RegExp('\\{\\$[a-z0-9_]+\\}', 'gi'));

		if (ar && ar.length > 0) {
			for (i=ar.length-1; i>=0; i--) {
				s = ar[i].substring(2, ar[i].length-1);

				if (as && as[s]) h = tinyMCE.replaceVar(h, s, as[s]);
				else if (tinyMCE.settings[s]) h = tinyMCE.replaceVar(h, s, tinyMCE.settings[s]);
			}
		}
		return h;
	},
	
	/* Builds <a href=""><img src=""></a> tags for a button */
	getButtonHTML : function(id, lang, img, cmd, ui, val, editorID) {
		var h = '', m, x;
		
		if (tinyMCE.isSimpleTextarea) {
			var attribute = '';
			if (cmd.match(/^Justify/)) {
				attribute = ", '" + cmd.replace(/^Justify(.)(.*)/, '$1'.toLowerCase() + '$2') + "'";
			}
			cmd = "tinyMCE.simpleExecCommand('" + cmd + "', '" + editorID + "'" + attribute + ");";
		}
		else {
			cmd = "tinyMCE.execInstanceCommand('{$editor_id}','" + cmd + "'";
			if (typeof(ui) != "undefined" && ui != null) cmd += ',' + ui;
			if (typeof(val) != "undefined" && val != null) cmd += ",'" + val + "'";
			cmd += ");";
		}
		
		if (id == 'color') cmd = 'void(0);';
		
		// Normal button
		h += '<a id="' + editorID + '_' + id + '" href="javascript:' + cmd + '" onmousedown="return false;" class="" target="_self">'; 
		h += '<img src="' + this.settings['imageURL'] + img + '" title="' + language[lang] + '" />';
		h += '</a>';
		return h;
	},
	
	/* Replaces tinyMCE variables  */
	replaceVar : function(sourceText, variableName, value) {
		return sourceText.replace(new RegExp('{\\\$' + variableName + '}', 'g'), value);
	},
	
	/* Creates iframe */
	_createIFrame : function(replace_element, doc, win) {
		var iframe, id = replace_element.getAttribute("id");
		var ah;

		if (typeof(doc) == "undefined") doc = document;
		if (typeof(win) == "undefined") win = window;
		iframe = doc.createElement("iframe");
		ah = tinyMCE.settings['height'] + "px";

		iframe.setAttribute("id", id);
		//iframe.setAttribute("tabindex", "20");
		iframe.setAttribute("name", id);
		iframe.setAttribute("class", "mceEditorIframe");
		iframe.setAttribute("border", "0");
		iframe.setAttribute("frameBorder", "0");
		iframe.setAttribute("marginWidth", "0");
		iframe.setAttribute("marginHeight", "0");
		iframe.setAttribute("leftMargin", "0");
		iframe.setAttribute("topMargin", "0");
		iframe.setAttribute("allowtransparency", "true");
		iframe.className = 'mceEditorIframe';
		iframe.style.height = ah;

		// Must have a src element in IE HTTPs breaks as well as absoute URLs
		if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
			iframe.setAttribute("src", this.settings['blankHTML']);
			replace_element.outerHTML = iframe.outerHTML;
			return win.frames[id];
		}
		else {
			replace_element.parentNode.replaceChild(iframe, replace_element);
			return iframe;
		}
	},
	
	// Creates textarea for code view
	_createTextarea : function(editorID, height) {
		var theTextarea = document.createElement('textarea');
		theTextarea.id = editorID + '_codeview';
		theTextarea.className = 'editorCodeView';
		theTextarea.style.height = height + 'px';
		return theTextarea;
	},

	/* Trims a string */
	trim : function(s) {
		return s.replace(/^\s*|\s*$/g, "");
	},
	
	/*  */
	triggerNodeChange : function(setup_content) {
		if (tinyMCE.selectedInstance) {
			var inst = tinyMCE.selectedInstance;
			var editorId = inst.editorId;
			var undoIndex = -1;
			var undoLevels = -1;
			
			var elm = (typeof(setup_content) != "undefined" && setup_content) ? tinyMCE.selectedElement : inst.getFocusElement();
			
			var anySelection = false;
			var selectedText = inst.selection.getSelectedText();
			if (setup_content && tinyMCE.isGecko && inst.isDisabled()) {
				elm = inst.getBody();
			}
			
			// Opera got no element on view change
			if (elm == null) {
				elm = tinyMCE.selectedElement;
			}
			
			inst.switchSettings();
			if (tinyMCE.selectedElement) {
				anySelection = (tinyMCE.selectedElement.nodeName.toLowerCase() == "img") || (selectedText && selectedText.length > 0);
			}
			if (tinyMCE.settings['custom_undo_redo']) {
				undoIndex = inst.undoRedo.undoIndex;
				undoLevels = inst.undoRedo.undoLevels.length;
			}
			this.handleNodeChange(editorId, elm, undoIndex, undoLevels, anySelection, setup_content);	
		}
	},
	
	/* Node change handler. enables or disables buttons */
	handleNodeChange : function (editor_id, node, undo_index, undo_levels, any_selection, setup_content) {
		function selectByValue(select_elm, value, first_index) {
			first_index = typeof(first_index) == "undefined" ? false : true;
			if (select_elm) {
				for (var i = 0; i < select_elm.options.length; i++) {
					var ov = "" + select_elm.options[i].value;
					if (first_index && ov.toLowerCase().indexOf(value.toLowerCase()) == 0) {
						select_elm.selectedIndex = i;
						return true;
					}
					if (ov == value) {
						select_elm.selectedIndex = i;
						return true;
					}
				}
			}
			return false;
		};
		
		function getAttrib(elm, name) {
			return elm.getAttribute(name) ? elm.getAttribute(name) : "";
		};
		
		var inst = tinyMCE.getInstanceById(editor_id);

		// No node provided
		if (node == null) return;

		// Resets old states
		tinyMCE.switchClass(editor_id + '_justifyleft_li', '');
		tinyMCE.switchClass(editor_id + '_justifyright_li', '');
		tinyMCE.switchClass(editor_id + '_justifycenter_li', '');
		tinyMCE.switchClass(editor_id + '_b_li', '');
		tinyMCE.switchClass(editor_id + '_i_li', '');
		tinyMCE.switchClass(editor_id + '_u_li', '');
		tinyMCE.switchClass(editor_id + '_s_li', '');
		tinyMCE.switchClass(editor_id + '_bullist_li', '');
		tinyMCE.switchClass(editor_id + '_numlist_li', '');
		tinyMCE.switchClass(editor_id + '_image_li', '');
		tinyMCE.switchClass(editor_id + '_quote_li', '');
		tinyMCE.switchClass(editor_id + '_unlink_li', '');
		
		// Resets color picker
		var colorPickerLinkElement = document.getElementById(editor_id + '_color');
		if (colorPickerLinkElement) {
			var colorPickerImageElement = colorPickerLinkElement.firstChild;
			colorPickerImageElement.src = inst.settings['imageURL'] + coreBBCodes['color']['icon'];
			colorPickerImageElement.style.backgroundColor = 'transparent';
		}

		// Resets user defined BBCode buttons
		for (var bbcode in extraBBCodes) {
			tinyMCE.switchClass(editor_id + '_' + bbcode + '_li', '');
		}
	
		// Gets link
		var anchorLink = tinyMCE.getParentElement(node, "a", "href");
		if ($(editor_id+'_link_li')) { // work-around for disabled bbcodes
			if (inst.editorIsActive && (anchorLink || any_selection)) {
				// Enables link button
				tinyMCE.switchClass(editor_id + '_link_li', anchorLink ? 'activeSubTabMenu' : '');
				document.getElementById(editor_id + '_link').href = "javascript:tinyMCE.execInstanceCommand('" + editor_id + "', 'mceLink', false);";
				
				// Disables unlink button when no link behind
				if (!anchorLink) {
					tinyMCE.switchClass(editor_id + '_unlink_li', 'mceButtonDisabled');
					document.getElementById(editor_id + '_unlink').href = 'javascript:void(0);';
				}
				// Enables unlink button: link behind
				else {
					document.getElementById(editor_id + '_unlink').href = "javascript:tinyMCE.execInstanceCommand('" + editor_id + "', 'unlink', false);";	
				}
				
				var justifyCenterButton = document.getElementById(editor_id + '_justifycenter');
				var justifyLeftButton = document.getElementById(editor_id + '_justifyleft');
				var justifyRightButton = document.getElementById(editor_id + '_justifyright');
				
				// Disables align inside links
				if (anchorLink && (node != anchorLink)) {
					tinyMCE.switchClass(editor_id + '_justifycenter_li', 'mceButtonDisabled');
					tinyMCE.switchClass(editor_id + '_justifyleft_li', 'mceButtonDisabled');
					tinyMCE.switchClass(editor_id + '_justifyright_li', 'mceButtonDisabled');
					
					justifyCenterButton.href = "javascript:void(0);";
					justifyLeftButton.href = "javascript:void(0);";
					justifyRightButton.href = "javascript:void(0);";
				}
				else {
					justifyCenterButton.href = 'javascript:tinyMCE.execInstanceCommand(\''+ editor_id +'\',\'JustifyCenter\');';
					justifyLeftButton.href = 'javascript:tinyMCE.execInstanceCommand(\''+ editor_id +'\',\'JustifyLeft\');';
					justifyRightButton.href = 'javascript:tinyMCE.execInstanceCommand(\''+ editor_id +'\',\'JustifyRight\');';
				}
			}
			else if (!inst.editorIsActive) {
				tinyMCE.switchClass(editor_id + '_link_li', '');
				tinyMCE.switchClass(editor_id + '_unlink_li', 'mceButtonDisabled');
				document.getElementById(editor_id + '_link').href = "javascript:tinyMCE.execInstanceCommand('" + editor_id + "', 'mceLink', false);";
				document.getElementById(editor_id + '_unlink').href = "javascript:void(0);";
			}
			else {
				tinyMCE.switchClass(editor_id + '_link_li', 'mceButtonDisabled');
				tinyMCE.switchClass(editor_id + '_unlink_li', 'mceButtonDisabled');
				document.getElementById(editor_id + '_link').href = 'javascript:void(0);';
				document.getElementById(editor_id + '_unlink').href = 'javascript:void(0);';
			}
		}
		
		if (!inst.editorIsActive || undo_levels != -1) {
			tinyMCE.switchClass(editor_id + '_undo_li', 'mceButtonDisabled');
			tinyMCE.switchClass(editor_id + '_redo_li', 'mceButtonDisabled');
			document.getElementById(editor_id + '_undo').href = "javascript:void(0);";
			document.getElementById(editor_id + '_redo').href = "javascript:void(0);";
		}

		// Has redo levels
		if (inst.editorIsActive && undo_index != -1 && (undo_index < undo_levels-1 && undo_levels > 0)) {
			tinyMCE.switchClass(editor_id + '_redo_li', '');
			cmd = 'javascript:tinyMCE.execInstanceCommand(\'' + editor_id + '\',\'Redo\')';
			document.getElementById(editor_id + '_redo').href = cmd;
		}

		// Has undo levels
		if (inst.editorIsActive && undo_index != -1 && (undo_index > 0 && undo_levels > 0)) {
			tinyMCE.switchClass(editor_id + '_undo_li', '');
			cmd = 'javascript:tinyMCE.execInstanceCommand(\'' + editor_id + '\',\'Undo\')';
			document.getElementById(editor_id + '_undo').href = cmd;
		}
		
		// Selects font select
		var selectElm = document.getElementById(editor_id + "_fontNameSelect");
		if (selectElm) {
			if (!tinyMCE.isSafari && (!tinyMCE.isMSIE || tinyMCE.isOpera)) { 
				var face = inst.queryCommandValue('FontName');
				face = face == null || face == "" ? "" : face;
				selectByValue(selectElm, face, face != "");
			} else {
				var elm = tinyMCE.getParentElement(node, "font", "face");
				if (elm) {
					var family = tinyMCE.getAttrib(elm, "face");
					if (family == '') {
						family = '' + elm.style.fontFamily;
					}
					if (!selectByValue(selectElm, family, family != "")) {
						selectByValue(selectElm, "");
					}
				} else {
					selectByValue(selectElm, "");
				}
			}
		}

		// Selects font size
		var selectElm = document.getElementById(editor_id + "_fontSizeSelect");
		if (selectElm) {
			if (!tinyMCE.isSafari && !tinyMCE.isOpera) { 
				var size = inst.queryCommandValue('FontSize');
				selectByValue(selectElm, size == null || size == "" ? "0" : size);
			} else {
				var elm = tinyMCE.getParentElement(node, "font", "size");
				if (!elm) elm = tinyMCE.getParentElement(node, "span", "style");
				if (elm) {
					var size = tinyMCE.getAttrib(elm, "size");
					if (size == '') size = tinyMCE.getAttrib(elm, "style");
					if (size.match(/font-size:\s*([\d]+)/i)) {
						size = RegExp.$1;
					}
					if (size == '') {
						var sizes = new Array('', '10pt', '12pt', '14pt', '18pt', '24pt', '36pt');
						size = '' + elm.style.fontSize;
						for (var i = 0; i < sizes.length; i++) {
							if (('' + sizes[i]) == size) {
								size = i;
								break;
							}
						}
					}
					
					if (!selectByValue(selectElm, size)) {
						selectByValue(selectElm, "");
					}
				} else {
					selectByValue(selectElm, "0");
				}
			}
		}
		
		// Handles align attributes
		alignNode = node;
		breakOut = false;
		do {
			if (!alignNode.getAttribute || !alignNode.getAttribute('align'))
				continue;

			switch (alignNode.getAttribute('align').toLowerCase()) {
				case "left":
					tinyMCE.switchClass(editor_id + '_justifyleft_li', 'activeSubTabMenu');
					breakOut = true;
					break;
				case "right":
					tinyMCE.switchClass(editor_id + '_justifyright_li', 'activeSubTabMenu');
					breakOut = true;
					break;
				case "middle":
				case "center":
					tinyMCE.switchClass(editor_id + '_justifycenter_li', 'activeSubTabMenu');
					breakOut = true;
					break;
			}
		} while (!breakOut && (alignNode = alignNode.parentNode) != null);

		// Div justification
		var div = tinyMCE.getParentElement(node, "div");
		if (div && div.style.textAlign == "center") {
			tinyMCE.switchClass(editor_id + '_justifycenter_li', 'activeSubTabMenu');
		}

		// Handles special text
		if (!setup_content && inst.editorIsActive && inst.getSel() != null) {

			var doc = inst.getDoc();
			
			try {
				if (doc.queryCommandState("Bold")) tinyMCE.switchClass(editor_id + '_b_li', 'activeSubTabMenu');
			}catch(e){}

			try {
				if (doc.queryCommandState("Italic")) tinyMCE.switchClass(editor_id + '_i_li', 'activeSubTabMenu');
			}catch(e){}

			try {
				if (doc.queryCommandState("Underline") && node.nodeName != "A") tinyMCE.switchClass(editor_id + '_u_li', 'activeSubTabMenu');
			}catch(e){}
			
			try {
				if (doc.queryCommandState("Strikethrough")) tinyMCE.switchClass(editor_id + '_s_li', 'activeSubTabMenu');
			}catch(e){}
			
			try {
				var colorValue = false;
				if (colorValue = doc.queryCommandValue("forecolor")) {
					if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
						if (colorValue == 255) colorValue = 16711680;
						colorValue = convertDecimalToHex(colorValue);
					}
					
					var isDefaultFontColor = false;
					
					if (inst.settings.defaultPageFontColor.match(/#\d\d\d/)) {
						inst.settings.defaultPageFontColor = inst.settings.defaultPageFontColor.replace(/^#(\d)(\d)(\d)$/g, '#$1$1$2$2$3$3');
					}
					
					if (colorValue.match(/^rgb\(\d{1,3},\d{1,3},\d{1,3}\)$/i)) {
						colorValue = convertRGBToHex(colorValue);
					}

					// Ignores color because it's the default color (IE, Safari 3 and Opera got "colorValue" from CSS not only from "execCommand")				
					if (inst.settings.defaultPageFontColor == colorValue) {
						isDefaultFontColor = true;
					}
					
					// Changes color picker button color (not for IE because its "queryCommandValue" returns wrong colors (?)
					// TODO: Check whats wrong with IE colors. if not fixable remove useless code (convertDecimalToHex ...)
					if (!isDefaultFontColor && (!tinyMCE.isMSIE || tinyMCE.isOpera)) {
						colorPickerImageElement.src = inst.settings['imageURL'] + 'fontColorPickerM.png';
						colorPickerImageElement.style.backgroundColor = colorValue;
					}
				}
			}catch(e){}
			
			// Marks quote button if selection inside quote
			if (node.nodeName.toLowerCase() == "blockquote") {
				tinyMCE.switchClass(editor_id + '_quote_li', 'activeSubTabMenu');
			}
			
			// Marks user defined BBCode buttons
			for (var bbcode in extraBBCodes) {
				if (node.nodeName.toLowerCase() == extraBBCodes[bbcode]['htmlOpen']) {
					//TODO: Check not only the tag name but attributes too
					var attributes = extraBBCodes[bbcode]['attributes'];
					var isSelected = true;
					for (var i = 0; i < attributes.length; i++) {
						var attributeName = attributes[i]['attributeHTML'].replace(/(\w+)=.*/, '$1');
						var property = '';
	
						if (attributeName == 'style') {
							property = attributes[i]['attributeHTML'].replace(/style="([^:]+).*/, '$1');
						}
						// Attribute is not in node or style with other property
						var propertyRegex = new RegExp(property, "i");					
						if (!tinyMCE.getAttrib(node, attributeName) || (property != '' && !tinyMCE.getAttrib(node,attributeName).match(propertyRegex))) {
							isSelected = false;
						}
					}
					if (isSelected) {
						tinyMCE.switchClass(editor_id + '_' + bbcode + '_li', 'activeSubTabMenu');	
					}
				}
			}
		}

		// Handles elements
		do {
			switch (node.nodeName) {
				case "UL":
					tinyMCE.switchClass(editor_id + '_bullist_li', 'activeSubTabMenu');
					break;
				case "OL":
					tinyMCE.switchClass(editor_id + '_numlist_li', 'activeSubTabMenu');
					break;
				case "IMG":
					if (getAttrib(node, 'name').indexOf('mce_') != 0) {
						tinyMCE.switchClass(editor_id + '_image_li', 'activeSubTabMenu');
					}
					break;
			}
		} while ((node = node.parentNode) != null && inst.editorIsActive);
	},
	
	/*  */
	executeCallback : function(i, p, n) {
		return this.callFunc(i, p, n, 1, this.executeCallback.arguments);
	},
	
	/* Returns an instance */
	getInstanceById : function(editor_id) {
		var inst = this.instances[editor_id];
		if (!inst) {
			for (var n in tinyMCE.instances) {
				var instance = tinyMCE.instances[n];
				if (!tinyMCE.isInstance(instance)) continue;
				if (instance.formTargetElementId == editor_id) {
					inst = instance;
					break;
				}
			}
		}
		return inst;
	},
	
	/* Wrapper for execCommand */
	execInstanceCommand : function(editor_id, command, user_interface, value, editorFocus) {
		var inst = tinyMCE.getInstanceById(editor_id);
		if (inst) {
		
			if (typeof(editorFocus) == "undefined") editorFocus = true;
			if (editorFocus && inst.contentWindow.focus) inst.contentWindow.focus();
		       	
			// Resets design mode if lost
			inst.autoResetDesignMode();
            
			this.selectedElement = inst.getFocusElement();
			this.selectedInstance = inst;
			tinyMCE.execCommand(command, user_interface, value);

			// Cancels event so it doesn't call onbeforeonunlaod
			if (tinyMCE.isMSIE && window.event != null) {
				tinyMCE.cancelEvent(window.event);
			}
		}
	},
	
	/* Wrapper for execCommand */
	execCommand : function(command, user_interface, value) {
		// Default input
		user_interface = user_interface ? user_interface : false;
		value = value ? value : null;

		if (tinyMCE.selectedInstance) {
			tinyMCE.selectedInstance.switchSettings();
		}

		switch (command) {
			case 'mceFocus':
				var inst = tinyMCE.getInstanceById(value);
				if (inst) inst.contentWindow.focus();
				return;
			case "mceResetDesignMode":
				// Resets the design mode state of the editors in Gecko
				if (!tinyMCE.isMSIE) {
					for (var n in tinyMCE.instances) {
						if (!tinyMCE.isInstance(tinyMCE.instances[n])) {
							continue;
						}

						try {
							tinyMCE.instances[n].getDoc().designMode = "on";
						} catch (e) {
							// Ignore any errors
						}
					}
				}
				return;
		}
		if (this.selectedInstance) {
			// Call control::execCommand()
			this.selectedInstance.execCommand(command, user_interface, value);
		} 
	},
	
	/*  */
	execCommandCallback : function(i, p, n) {
		return this.callFunc(i, p, n, 2, this.execCommandCallback.arguments);
	},
	
	/* Includes style.css in iframe. 
	 */
	includeCssFile : function(instance) { 
		var doc = instance.getDoc();
		var head = doc.getElementsByTagName('head')[0];
		
		// Inlcudes style-x.css 
		if (head) {
			var css = doc.createElement('link');
			css.href = tinyMCE.settings['base_href'] + tinyMCE.settings['cssFile'];
			css.type = 'text/css';
			css.rel = 'stylesheet';
			head.appendChild(css);
			
			var body = doc.getElementsByTagName('body')[0];
			body.id = 'iframe';
			body.className = 'iframeBody';
			body.parentNode.className = 'iframeHTML';
		}
		else {
			window.setTimeout("tinyMCE.includeCssFile(tinyMCE.instances[\"" + instance.editorId + "\"]);", 1000);
		}
	},
	
	resetForm : function(form_index) {
		var i, inst, n, formObj = document.forms[form_index];

		for (n in tinyMCE.instances) {
			inst = tinyMCE.instances[n];

			if (!tinyMCE.isInstance(inst))
				continue;

			inst.switchSettings();

			for (i=0; i<formObj.elements.length; i++) {
				if (inst.formTargetElementId == formObj.elements[i].name) {
					if (inst.editorIsActive) {
						inst.getBody().innerHTML = inst.startContent;
					}
					else {
						inst.theTextarea.value = inst.startContent;
					}
				}
			}
		}
	},
	isInsideBlockQuote : function(node) {
		
		while(node.parentNode) {
			if (node.nodeName.toLowerCase() == 'blockquote') {
				return true;
			}
			node = node.parentNode;
		}
		return false;
	},
	isInsideUserdefinedTag : function(node, tagName, attributes) {
		while(node.parentNode) {
			if (node.nodeName.toLowerCase() == tagName) {
				for (var i in attributes) {
					var attributeName = attributes[i]['attributeHTML'].replace(/(\w+)=.*/, '$1');
					var property = '';

					if (attributeName == 'style') {
						property = attributes[i]['attributeHTML'].replace(/style="([^:]+).*/, '$1');
					}
					// Attribute is not in node or style with other property
					var propertyRegex = new RegExp(property, "i");					
					if (!tinyMCE.getAttrib(node, attributeName) || (property != '' && !tinyMCE.getAttrib(node,attributeName).match(propertyRegex))) {
						return false;
					}
				}
				return true;
			}
			node = node.parentNode;
		}
		return false;
	},
	getInnerText : function(node) {
		var text = node.innerHTML.replace(/&nbsp;/g, ' ');
		text = text.replace(/<br[^>]*>/gi, '\n');
		return text.replace(/<[^>]+>/g, '');
	}
};
/* --- END OF ENGINE --- */

/**
 * CONTRUCTOR OF CONTROL CLASS 
 */
function TinyMCE_Control(settings) {
	var t, i, to, fu, p, x, fn, fu, pn, s = settings;

	this.undoRedoLevel = true;
	this.isTinyMCE_Control = true;
	this.settings = s;
	this.selection = new TinyMCE_Selection(this);
	this.undoRedo = new TinyMCE_UndoRedo(this);
	this.shortcuts = new Array();
	this.resizer = new Object();
	this.iframeElement;
};

/**
 * FUNCTIONS OF CONTROL
 */
TinyMCE_Control.prototype = {
	/*  */
	repaint : function() {
		if (tinyMCE.isMSIE && !tinyMCE.isOpera)
			return;
		
		var s, b;
		try {
			s = this.selection;
			b = s.getBookmark(true);
			this.getBody().style.display = 'none';
			this.getDoc().execCommand('selectall', false, null);
			this.getSel().collapseToStart();
			this.getBody().style.display = 'block';
			s.moveToBookmark(b);
		} catch (ex) {
			// Ignore
		}
	},

	// tinyMCE 208 changes	
	select : function() {
		var oldInst = tinyMCE.selectedInstance;

		if (oldInst != this) {
			if (oldInst) oldInst.execCommand('mceEndTyping');
			tinyMCE.dispatchCallback(this, 'select_instance_callback', 'selectInstance', this, oldInst);
			tinyMCE.selectedInstance = this;
		}
	},
	
	
	/*  */
	isDisabled : function() {
		if (tinyMCE.isMSIE)	return false;
		var s = this.getSel();
		// Weird, where's that cursor selection?
		return (!s || !s.rangeCount || s.rangeCount == 0);
	},
	
	/* Get selection */
	getSel : function() {
		return this.selection.getSel();
	},
	
	/* Gets the element which got focus */
	getFocusElement : function() {
		return this.selection.getFocusElement();
	},
	
	/*  */
	triggerSave : function(skip_cleanup, skip_callback) {
		var e, nl = new Array(), i, s;

		this.switchSettings();
		s = tinyMCE.settings;

		tinyMCE.settings['preformatted'] = false;

		// Default to false
		if (typeof(skip_cleanup) == "undefined") skip_cleanup = false;

		// Default to false
		if (typeof(skip_callback) == "undefined") skip_callback = false;
		
		var html = '';
		if (this.editorIsActive) {
			if (this.getDoc()) {
				tinyMCE._setHTML(this.getDoc(), this.getBody().innerHTML);
				html = this.getBody().innerHTML;
			}
		}
		else html = this.theTextarea.value;
		
		// Performs submit actions (HTML to BBCode) 
		if (!skip_callback) {
			// Parses html to bbcode
			if (this.editorIsActive) {
				// Extracts code blocks and insert unique string to reinsert it later unparsed
				var codeBlocks = new Object();
				content = tinyMCE.extractCodeBlocks(html, codeBlocks, true, true);
				content = tinyMCE.htmlToBBCode(content);

				// Resinserts code blocks
				for (var uniqueString in codeBlocks) {
					content = unescape(content.replace(uniqueString, escape(codeBlocks[uniqueString])));
				}
			}
			else var content = tinyMCE.cleanBBCode(html);
			var parentElement = document.getElementById(this.editorId+'_parent');
			tinyMCE.createEditorSettingFields(this.editorIsActive, parentElement, this.iframeElement, this.theTextarea);
		}

		// Uses callback content if available
		if ((typeof(content) != "undefined") && content != null) html = content;

		// Replaces some weird entities (Bug: #1056343)
		html = tinyMCE.regexpReplace(html, "&#40;", "(", "gi");
		html = tinyMCE.regexpReplace(html, "&#41;", ")", "gi");
		html = tinyMCE.regexpReplace(html, "&#59;", ";", "gi");
		html = tinyMCE.regexpReplace(html, "&#34;", "&quot;", "gi");
		html = tinyMCE.regexpReplace(html, "&#94;", "^", "gi");
		
		// Store content bbcoded in textarea which is in template (id="text")
		if (this.formElement) {
			this.formElement.value = html;
		}
		if (tinyMCE.isSafari && this.formElement) {
			this.formElement.innerText = html;
		}
	},
	
	/*  */
	switchSettings : function() {
		if (tinyMCE.configs.length > 1 && tinyMCE.currentConfig != this.settings['index']) {
			tinyMCE.settings = this.settings;
			tinyMCE.currentConfig = this.settings['index'];
		}
	},
	
	/* Creates the tinyMCE editor for an element (e.g. textarea)*/
	_onAdd : function(replace_element, form_element_name, target_document) {
		var hc, th, to, editorTemplate;

		var targetDoc = target_document ? target_document : document;
		this.targetDoc = targetDoc;

		if (!replace_element) {
			alert(language['noFormElement']);
			return false;
		}
		var editorTemplate = getEditorTemplate(this.settings, this.editorId);
		
    		var html = '';
		html = '<div id="' + this.editorId + '_parent">' + editorTemplate['html'];
		html = tinyMCE.replaceVar(html, "editor_id", this.editorId);

		// Set default height (from hardcoded textarea)
		if (this.settings['height'] == -1 || this.settings['height'] == 0) {
			this.settings['height'] = replace_element.offsetHeight;
		}

		html = tinyMCE.applyTemplate(html);
		this.formTargetElementId = form_element_name;

		// Get the value from element
		var htmlEncodedContent = replace_element.innerHTML;
		var htmlDecodedContent = replace_element.value;

		// Handle entities from cite or db-content and from new content
		// TODO: Fix enitity-chaos if user reloads after switching the views
		if (this.editorIsActive) {

			// Sarafi 3 doesn`t encode "&" in innerHTML. but it is needed to parse to HTML
			// if (tinyMCE.isSafari3) htmlEncodedContent = htmlEncodedContent.replace(/&(?!amp;)/g, '&amp;');

			// Content from "edit" or "cite" is called first time 
			if (htmlEncodedContent == tinyMCE.encodeHTMLEntities(htmlDecodedContent)) {
			
				// Encode new content and make HTML out of BBCodes (not in code blocks)		
				var codeBlocks = new Object();
				htmlEncodedContent = tinyMCE.extractCodeBlocks(htmlEncodedContent, codeBlocks, false);
				htmlEncodedContent = tinyMCE.bbCodeToHTML(htmlEncodedContent);

				// Reinsert code blocks
				for (var uniqueString_ in codeBlocks) {
					var codeBlock = codeBlocks[uniqueString_].replace(/\n/g, '<br>');
					htmlEncodedContent = unescape(htmlEncodedContent.replace(uniqueString_, escape(codeBlock)));
				}

			}
			// Content is already parsed and may contain new text (reload page or step back and forth etc.)
			else {
				// Opera doesn`t get decoded line breaks (<br>) in value
				if (htmlDecodedContent.match(/\n/)) {
					htmlEncodedContent = htmlDecodedContent.replace(/\n|\n\r|\\r\n\r/g, '<br>');
				}
				else {
					htmlEncodedContent = htmlDecodedContent;
				}
			}
			
			// Put old content back to all
			this.startContent = htmlEncodedContent;
		}
		else {
			if (tinyMCE.isOpera) this.startContent = htmlDecodedContent;
			else {
				htmlEncodedContent = tinyMCE.decodeHTMLEntities(htmlEncodedContent);
				this.startContent = htmlEncodedContent;
			}
		}
		
		html += '</div>';

		// Just hide the textarea element
		this.oldTargetElement = replace_element;
		this.oldTargetElement.style.display = "none";
		
		try {
			// Output HTML and set editable
			if (tinyMCE.isGecko) {
				var rng = replace_element.ownerDocument.createRange();
				rng.setStartBefore(replace_element);
				var fragment = rng.createContextualFragment(html);
				tinyMCE.insertAfter(fragment, replace_element);
			} else {
				replace_element.insertAdjacentHTML("beforeBegin", html);
			}
		}
		catch(e) {
			this.oldTargetElement.style.display = "block";
			return false;
		}
		
		// Create the textarea for code view
		this.parentContainer = document.getElementById(this.editorId+'_parent');					
		this.tabContent = this.parentContainer.childNodes[2];
		var theTextarea = tinyMCE._createTextarea(this.editorId, this.settings['height']);
		this.tabContent.firstChild.appendChild(theTextarea);
		var tElm = targetDoc.getElementById(this.editorId);
		
		// Setup iframe
		var dynamicIFrame = false;
		if (!tinyMCE.isMSIE) {
			// Node case is preserved in XML strict mode
			if (tElm && tElm.nodeName.toLowerCase() == "div") {
				tElm = tinyMCE._createIFrame(tElm, targetDoc);
				dynamicIFrame = true;
			}
			this.targetElement = tElm;
			this.iframeElement = tElm;
			this.contentDocument = tElm.contentDocument;
			this.contentWindow = tElm.contentWindow;
		} 
		else {
			if (tElm && tElm.nodeName == "DIV") {
				tElm = tinyMCE._createIFrame(tElm, targetDoc, targetDoc.parentWindow);
			}
			else {
				tElm = targetDoc.frames[this.editorId];
			}

			this.targetElement = tElm;
			this.iframeElement = targetDoc.getElementById(this.editorId);
			
			if (tinyMCE.isOpera) {
				this.contentDocument = this.iframeElement.contentDocument;
				this.contentWindow = this.iframeElement.contentWindow;
				dynamicIFrame = true;
			} else {
				this.contentDocument = tElm.window.document;
				this.contentWindow = tElm.window;
			}
			this.getDoc().designMode = "on";
		}

		// Setup base HTML
		var doc = this.contentDocument;
		if (dynamicIFrame) {

			// WBB doctype
			var html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html class="iframeHTML">'
				 + '<head xmlns="http://www.w3.org/1999/xhtml"><base href="' + tinyMCE.settings['base_href'] + '" /><title>blank_page</title><meta http-equiv="X-UA-Compatible" content="IE=7" /><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'
				 + '<body class="iframeBody"></body></html>';
	
			try {
				if (!this.isDisabled()) this.getDoc().designMode = "on";
				doc.open();
				doc.write(html);
				doc.close();
			} catch (e) {
				// Failed Mozilla 1.3
				this.getDoc().location.href = this.settings['blankHTML'];
			}
		}

		// This timeout is needed in MSIE 5.5 for some odd reason
		// It seems that document.frames isn't initialized yet?
		if (tinyMCE.isMSIE) {
			window.setTimeout("tinyMCE.addEventHandlers(tinyMCE.instances[\"" + this.editorId + "\"]);", 1);
		}

		// Display codeview 
		if (!this.editorIsActive) {
			if (!tinyMCE.isMSIE && !tinyMCE.isSafari3) {
				this.iframeElement.style.display = 'none';
			}
			else if (tinyMCE.isOpera || tinyMCE.isSafari3) this.targetElement.style.display = 'none';
			else this.iframeElement.document.getElementById(this.editorId).style.display = 'none';
			theTextarea.style.display = 'block';
		}
		
		tinyMCE.setupContent(this.editorId, true);

		// Create popup menu div for color picker
		tinyMCE.createColorPicker(this.editorId);
		
		// Init tab index
		if (this.tabindex) {
			if (this.editorIsActive) {
				this.iframeElement.setAttribute('tabindex', this.tabindex);
			}
			else {
				theTextarea.setAttribute('tabindex', this.tabindex);
			}
		}
		
		// Make css file available for the iframe document
		window.setTimeout("tinyMCE.includeCssFile(tinyMCE.instances[\"" + this.editorId + "\"]);", 200);
		return true;
	},
	
	/* Get document of active iframe */
	getDoc : function() {
        if (typeof(this.contentWindow) != "undefined" && typeof(this.contentWindow.document) != "undefined") {
		  return this.contentWindow.document;
        }
        else return false;
    	},
    	
	/* Get body of active iframe */
	getBody : function() {
	   return this.contentBody ? this.contentBody : this.getDoc().body;
	},
	
	/* Get window of active iframe */
	getWin : function() {
		return this.contentWindow;
	},
	
	/* Add shortcut to document */
	addShortcut : function(m, k, d, cmd, ui, va) {
		var n = typeof(k) == "number", ie = tinyMCE.isMSIE, c, sc, i;
		var scl = this.shortcuts;

		m = m.toLowerCase();
		k = ie && !n ? k.toUpperCase() : k;
		c = n ? null : k.charCodeAt(0);

		sc = {
			alt : m.indexOf('alt') != -1,
			ctrl : m.indexOf('ctrl') != -1,
			shift : m.indexOf('shift') != -1,
			charCode : c,
			keyCode : n ? k : (ie ? c : null),
			desc : d,
			cmd : cmd,
			ui : ui,
			val : va
		};

		for (i = 0; i < scl.length; i++) {
			if (sc.alt == scl[i].alt && sc.ctrl == scl[i].ctrl && sc.shift == scl[i].shift
				&& sc.charCode == scl[i].charCode && sc.keyCode == scl[i].keyCode) {
				return false;
			}
		}
		scl[scl.length] = sc;
		return true;
	},

	handleShortcut : function(e) {
		var i, s, o;

		// Normal key press, then ignore it
		if (!e.altKey && !e.ctrlKey) return false;
		s = this.shortcuts;

		for (i = 0; i < s.length; i++) {
			o = s[i];
			if (o.alt == e.altKey && o.ctrl == e.ctrlKey && (o.keyCode == e.keyCode || o.charCode == e.charCode)) {
				if (o.cmd && (e.type == "keydown" || (e.type == "keypress" && !tinyMCE.isOpera))) {
					tinyMCE.execCommand(o.cmd, o.ui, o.val);
				}
				tinyMCE.cancelEvent(e);
				return true;
			}
		}
		return false;
	},
	autoResetDesignMode : function() {
		// Add fix for tab/style.display none/block problems in Gecko
		if (!tinyMCE.isMSIE && this.isDisabled()) {
			eval('try { this.getDoc().designMode = "On"; this.useCSS = false;} catch(e) {}');
		}
	},
	getRng : function() {
		return this.selection.getRng();
	},
	_setUseCSS : function(b) {
		var d = this.getDoc();

		try {d.execCommand("useCSS", false, !b);} catch (ex) {}
		try {d.execCommand("styleWithCSS", false, b);} catch (ex) {}
	},

	// Handle tiny exec commands 
	execCommand : function(command, user_interface, value, selectedText) {
		var doc = this.getDoc();
		var win = this.getWin();
		var focusElm = this.getFocusElement();
		
		// Is non undo specific command
		if (!new RegExp('mceStartTyping|mceEndTyping|mceBeginUndoLevel|mceEndUndoLevel|mceAddUndoLevel', 'gi').test(command)) {
			this.undoBookmark = null;
		}
		
		// Mozilla issue
		if (!tinyMCE.isIE && !this.useCSS) {
			this._setUseCSS(false);
			this.useCSS = true;
		}

		this.contentDocument = doc; // <-- Strange, unless this is applied Mozilla 1.3 breaks
		
		if (!/mceStartTyping|mceEndTyping/.test(command)) {
			if (tinyMCE.execCommandCallback(this, 'execcommand_callback', 'execCommand', this.editorId, this.getBody(), command, user_interface, value)) {
				return;
			}
		}

		switch (command.toLowerCase()) {

			// Switch editor view to code view
			case "mcecodeview":
				if (!this.editorIsActive) return true;
			
				// Change innerHTML (HTML to BBCode)
				var bbCode = this.getBody().innerHTML;
				bbCode = tinyMCE.htmlToBBCode(bbCode);
				this.theTextarea.value = bbCode;
				
				// Hide iframe and show textarea
				this.iframeElement.style.display = 'none';
				this.theTextarea.style.display = 'block';
				
				// Set tab index
				if (this.tabindex) {
					this.iframeElement.removeAttribute('tabindex');
					this.theTextarea.setAttribute('tabindex', this.tabindex);
				}
				this.theTextarea.focus();

				// Flag to detect that code view is now active
				this.editorIsActive = false;

				// Disable editor tab enable code view tab
				var editorTab = document.getElementById(this.editorTabID);
				var codeTab = document.getElementById(this.codeTabID);
				editorTab.className = '';
				codeTab.className = tinyMCE.tabsActiveClass;

				// Set buttons to normal state 
				tinyMCE.triggerNodeChange();
				
				return true;

			// Change to iframe (WYSIWYG mode)
			case "mcewysiwygeditor":
				if (this.editorIsActive) return true;

				// Hide textarea and show iframe
				this.theTextarea.style.display = 'none';
				this.iframeElement.style.display = 'block';
				
				// Set tab index
				if (this.tabindex) {
					this.theTextarea.removeAttribute('tabindex');
					this.iframeElement.setAttribute('tabindex', this.tabindex);
				}
								
				// Flag to detect that WYSIWYG is now active
				this.editorIsActive = true;
				
				// Enable editor tab disable code view tab
				var editorTab = document.getElementById(this.editorTabID);
				var codeTab = document.getElementById(this.codeTabID);
				editorTab.className = tinyMCE.tabsActiveClass;
				codeTab.className = '';

				// Parse BBCode to HTML
				var html = tinyMCE.bbCodeToHTML(this.theTextarea.value, true);
				this.getBody().innerHTML = html;
				
				// Use original href (wcf_href) instead of the browser-inserted href
				var aElements = this.getDoc().getElementsByTagName('A');
				for (var i = 0; i < aElements.length; i++) {
					if (href = aElements[i].getAttribute('wcf_href')) {
						aElements[i].setAttribute("href", href);
					}
				}
				
				// Use original src (wcf_src) instead of the browser-inserted src
				var imgElements = this.getDoc().getElementsByTagName('IMG');
				for (var i = 0; i < imgElements.length; i++) {
					if (src = imgElements[i].getAttribute('wcf_src')) {
						imgElements[i].setAttribute('src', src);
					}
				}

				// Set buttons to normal state 
				tinyMCE.triggerNodeChange();
				
				// Focus on iframe
				window.setTimeout('tinyMCE.execInstanceCommand(\''+this.editorId+'\', \'mceFocus\');', 10);
								
				// IE doesn`t jump on body end when textnode is lastchild
				if (tinyMCE.isMSIE) var textNode = this.getDoc().createElement("span");
				// FF doesn`t work well with empty span (cursor small and shows no space)
				else var textNode = this.getDoc().createTextNode('');
				this.getBody().appendChild(textNode); 
								
				// Cursor at end of body
				var self = this;
				window.setTimeout(function(){self.selection.selectNode(textNode); textNode.parentNode.removeChild(textNode); return true;}, 10);
			
				return true;	
			
			case "mceimage":
				if (this.editorIsActive) {
					var src = "", title = "", alt = "";
					var img = tinyMCE.imgElement;
					
					if (tinyMCE.selectedElement != null && tinyMCE.selectedElement.nodeName.toLowerCase() == "img") {
						img = tinyMCE.selectedElement;
						tinyMCE.imgElement = img;
					}
					
					// Get src of selected image
					if (img) {
						src = tinyMCE.getAttrib(img, 'wcf_src');
						alt = tinyMCE.getAttrib(img, 'alt');

						// Try polling out the title
						if (alt == "") alt = tinyMCE.getAttrib(img, 'title');
	
						// Fix width/height attributes if the style is specified
						if (tinyMCE.isGecko) {
							var w = img.style.width;
							if (w != null && w != "") img.setAttribute("width", w);

							var h = img.style.height;
							if (h != null && h != "") img.setAttribute("height", h);
						}
					}
					
					// Prompt for changing src
					if (src != '') {
						src = prompt(language['image.insert'], src);
					}
					// Prompt for adding src
					else {
						src = this.selection.getSelectedText();
						if (src == null || typeof(src) == "undefined" || src == '') {
							src = prompt(language['image.insert'], '');
						}
					}					
					
					// Insert image 
					if (src != null && src != '') {
						tinyMCE.insertImage(this.editorId, src);
					}
					// Remove image
					else if (img && src == '') {
						img.parentNode.removeChild(img);
					}
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}
				else {
					tinyMCE.simpleExecCommand('mceImage', this.editorId+'_codeview');//, url);
				}
				return true;
			case "mcequote":
				if (this.editorIsActive) {
					var selectedElement = tinyMCE.selectedElement;
					
					// Remove blockquote
					if (tinyMCE.isInsideBlockQuote(selectedElement) && (tinyMCE.trim(this.selection.getSelectedText()) == tinyMCE.trim(tinyMCE.getInnerText(selectedElement)) || selectedElement.innerHTML == '') ){
						var newNode = this.getDoc().createElement('div');
						newNode.innerHTML = (selectedElement.innerHTML);
						selectedElement.parentNode.replaceChild(newNode, selectedElement);
					}
					// Exit blockquote
					else if (tinyMCE.isInsideBlockQuote(selectedElement)){
						// TODO: IE dont selects the textelement
						var textElement = this.getDoc().createTextNode(' ');	
						tinyMCE.insertAfter(textElement, selectedElement);
						this.selection.selectNode(textElement);
					}
					// Insert blockquote
					else {
						// xXx
						var	 selectedText = this.selection.getSelectedHTML();
						if (!tinyMCE.isOpera) {
							var quoteString = '<span>&nbsp;</span><blockquote class="wysiwygQuote container-4">' + selectedText +'&nbsp;<span id="quoteTagInserted">&nbsp;</span></blockquote><br>';
						}
						else {
							var quoteString = '[quote]' + selectedText + '<span id="quoteTagInserted">&nbsp;</span>[/quote]';
						}
						
						this.execCommand("mceInsertContent", false, quoteString);		
						// Place cursor between the tags
						if (!tinyMCE.isOpera) {
							var cursorPositionElement = this.getDoc().getElementById('quoteTagInserted');
							var emtpyTextNode = this.getDoc().createTextNode(' ');
							var parentElement = cursorPositionElement.parentNode;
							parentElement.replaceChild(emtpyTextNode, cursorPositionElement);
							this.selection.selectNode(emtpyTextNode);
						}
					}
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}
				else {
					tinyMCE.simpleExecCommand('mceQuote', this.editorId+'_codeview');
				}
				return true;
				
			case "mcequotation":
				if (this.editorIsActive) {
					this.insertContent('"');
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}
				else tinyMCE.simpleExecCommand('mceQuotation', this.editorId+'_codeview');
				return true;	
				
			case "mcecodetag":
				if (this.editorIsActive) {
					this.insertContent('[code]', '[/code]');
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");	
				}
				else tinyMCE.simpleExecCommand('code', this.editorId+'_codeview');
				return true;
				
			case "insertunorderedlist":
			case "insertorderedlist":
				if (this.editorIsActive) {
					if (tinyMCE.isSafari) {
						selectedText = this.selection.getSelectedText();
						var startTag = command == "InsertUnorderedList" ? '[list]' : '[list=1]'; 
						this.execCommand("mceInsertContent", false, startTag + '[*]'+ selectedText +'[/list]');
					}
					else this.getDoc().execCommand(command, user_interface, value);
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}
				else {
					var listType = command == "InsertUnorderedList" ? '' : '1';
					tinyMCE.simpleExecCommand(command, this.editorId+'_codeview');
				}
				tinyMCE.triggerNodeChange();
				break;	

			case "fontname":
				if (this.editorIsActive) {
					this.getDoc().execCommand('FontName', false, value);
					if (tinyMCE.isGecko) {
						window.setTimeout('tinyMCE.triggerNodeChange();', 1);
					}
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}
				else {
					tinyMCE.simpleExecCommand('FontName', this.editorId+'_codeview', value);
				}
				return;

			case "fontsize":
				if (this.editorIsActive) {
					this.getDoc().execCommand('FontSize', false, value);
					if (tinyMCE.isGecko) {
						window.setTimeout('tinyMCE.triggerNodeChange();', 1);
					}
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}else {
					tinyMCE.simpleExecCommand('FontSize', this.editorId+'_codeview', value);
				}
				return;

			case "forecolor":
				if (this.editorIsActive) {
					if (tinyMCE.isSafari) command = "FontColor";
					
					// Remove all formats and add all other formats but color
					if (value == 'transparent') {
						var bold, italic, underline = false;
						var fontName = '';
						var fontSize = 0;
						
						if (this.getDoc().queryCommandState("Bold")) bold = true;
						if (this.getDoc().queryCommandState("Italic")) italic = true;
						if (this.getDoc().queryCommandState("Underline")) underline = true;
						fontName = this.getDoc().queryCommandValue("FontName");
						fontSize = this.getDoc().queryCommandValue("FontSize");
						
						this.getDoc().execCommand("RemoveFormat", false, null);					
						
						if (bold) this.getDoc().execCommand("Bold", false, null);
						if (italic) this.getDoc().execCommand("Italic", false, null);
						if (underline) this.getDoc().execCommand("Underline", false, null);
						this.getDoc().execCommand("Fontname", false, fontName);
						this.getDoc().execCommand("FontSize", false, fontSize);
					}
					else {
						this.getDoc().execCommand(command, false, value);
					}
					
					selectedText = this.selection.getSelectedText();
					if (selectedText == '') {
						tinyMCE.execInstanceCommand(this.editorId, 'mceFocus');
					}
					
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}
				else {
					tinyMCE.simpleExecCommand('color', this.editorId+'_codeview', value);
				}
				break;
			case "justifyleft":
			case "justifycenter":
			case "justifyright":
			case "justifyfull":
				if (this.editorIsActive) {
					if (tinyMCE.isSafari3) {
						var align = 'center';
						if (command.toLowerCase() == 'justifyleft') align = 'left';
						else if (command.toLowerCase() == 'justifyright') align = 'right';
						else if (command.toLowerCase() == 'justifyfull') align = 'justify';
						selectedText = this.selection.getSelectedText();
						this.execCommand("mceInsertContent", false, '<div style="text-align: '+ align +'">'+ selectedText +'</div>');
					}
					else {
						this.getDoc().execCommand(command, user_interface, value);
					}
					if (tinyMCE.isGecko) {
						window.setTimeout('tinyMCE.triggerNodeChange();', 1);
					}
					else tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}
				else {
					tinyMCE.simpleExecCommand(command, this.editorId + '_codeview');
				}
				break;	
				
			case 'bold':
			case 'italic':
			case 'underline':
			case 'strikethrough':
				if (this.editorIsActive) {
					this.getDoc().execCommand(command, user_interface, value);
					if (tinyMCE.isGecko) {
						window.setTimeout('tinyMCE.triggerNodeChange();', 1);
					}
					else tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}
				else {
				    tinyMCE.simpleExecCommand(command, this.editorId+'_codeview');
				}
			break;	
				
			case "mcelink":
				if (this.editorIsActive) {
					tinyMCE.insertLink(this.editorId);
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand("mceAddUndoLevel");
				}
				else{
					tinyMCE.simpleExecCommand('mceLink', this.editorId+'_codeview');
				}
			break;	

			case "unlink":
				this.getDoc().execCommand(command, user_interface, value);
				tinyMCE.triggerNodeChange();
				tinyMCE.execCommand("mceAddUndoLevel");
			break;

			case "cut":
			case "copy":
			case "paste":
				this.getDoc().execCommand(command, user_interface, value);
				if (command == 'paste') {
					var self = this;
					window.setTimeout(function(){self.cleanupHTML(); return true;}, 100);
				}
				tinyMCE.triggerNodeChange();
			break;

			case "mceinsertcontent":
				// Force empty string
				if (!value) value = '';

				var insertHTMLFailed = false;
				// tinyMCE 208 changes				
				
				// Opera and Firefox
				if (!tinyMCE.isSafari && (tinyMCE.isOpera || tinyMCE.isGecko)) {
					try {
						this.getDoc().execCommand('inserthtml', false, value);
					} catch (ex) {
						insertHTMLFailed = true;
					}

					if (!insertHTMLFailed) {
						tinyMCE.triggerNodeChange();
						return;
					}

					// tinyMCE 208 changes									
				}
				
				// Safari and Konqueror
				if (!tinyMCE.isMSIE) {
					var isHTML = value.indexOf('<') != -1;
					var sel = this.getSel();
					var rng = this.getRng();
					
					if (isHTML) {
						//alert('isHTML');
						if (tinyMCE.isSafari) {
							var tmpRng = this.getDoc().createRange();
							tmpRng.setStart(this.getBody(), 0);
							tmpRng.setEnd(this.getBody(), 0);
							value = tmpRng.createContextualFragment(value);
						} 
						else {
							value = rng.createContextualFragment(value);
						}
					} 
					else {
						// Setup text node
						var el = document.createElement("div");
						el.innerHTML = value;
						value = el.firstChild.nodeValue;
						value = doc.createTextNode(value);
					}

					// Insert plain text in Safari
					if (tinyMCE.isSafari && !isHTML) {
						this.execCommand('InsertText', false, value.nodeValue);
						tinyMCE.triggerNodeChange();
						return true;
					} else if (tinyMCE.isSafari && isHTML) {
						// TODO: Insert HTML on cursor position					
						this.getFocusElement().appendChild(value);
						tinyMCE.triggerNodeChange();
						return true;
					}

					rng.deleteContents();

					// If target node is text do special treatment, (Mozilla 1.3 fix)
					if (rng.startContainer.nodeType == 3) {
						var node = rng.startContainer.splitText(rng.startOffset);
						node.parentNode.insertBefore(value, node); 
					} else {
						rng.insertNode(value);
					}

					if (!isHTML) {
						// Removes weird selection trails
						sel.selectAllChildren(doc.body);
						sel.removeAllRanges();

						// Move cursor to end of content
						var rng = doc.createRange();
						rng.selectNode(value);
						rng.collapse(false);
						sel.addRange(rng);
					} 
					else {
						rng.collapse(false);
					}

				// IE & Opera
				} else {
					var rng = doc.selection.createRange();
					var c = value.indexOf('<!--') != -1;

					// Fix comment bug, add tag before comments
					if (c) value = tinyMCE.uniqueTag + value;

					// Watch out! IE erases '"' from attribute values in pasteHTML
					if (rng.item) rng.item(0).outerHTML = value;
					else {
						try {	
							// If IE got no focus if an error occurs
							rng.pasteHTML(value);
						}
						catch (e) {
							tinyMCE.selectedInstance.getWin().focus();
							rng.pasteHTML(value);
						}
					}

					// Remove unique tag
					if (c) {
						var e = this.getDoc().getElementById('mceTMPElement');
						e.parentNode.removeChild(e);
					}
				}
				// tinyMCE 208 changes
				tinyMCE.execCommand("mceAddUndoLevel");
				tinyMCE.triggerNodeChange();
				break;
			
			case "mcestarttyping":
				if (tinyMCE.settings['custom_undo_redo'] && this.undoRedo.typingUndoIndex == -1) {
					this.undoRedo.typingUndoIndex = this.undoRedo.undoIndex;
					this.execCommand('mceAddUndoLevel');
				}
				break;

			case "mceendtyping":
				if (tinyMCE.settings['custom_undo_redo'] && this.undoRedo.typingUndoIndex != -1) {
					this.execCommand('mceAddUndoLevel');
					this.undoRedo.typingUndoIndex = -1;
				}
				break;

			case "mcebeginundolevel":
				this.undoRedoLevel = false;
				break;

			case "mceendundolevel":
				this.undoRedoLevel = true;
				this.execCommand('mceAddUndoLevel');
				break;

			case "mceaddundolevel":
				if (tinyMCE.settings['custom_undo_redo'] && this.undoRedoLevel) {
					if (this.undoRedo.add()) {
						tinyMCE.triggerNodeChange();
					}
				}
				break;

			case "undo":
				if (tinyMCE.settings['custom_undo_redo']) {
					tinyMCE.execCommand("mceEndTyping");
					this.undoRedo.undo();
					tinyMCE.triggerNodeChange();
				} else
					this.getDoc().execCommand(command, user_interface, value);
				break;

			case "redo":
				if (tinyMCE.settings['custom_undo_redo']) {
					tinyMCE.execCommand("mceEndTyping");
					this.undoRedo.redo();
					tinyMCE.triggerNodeChange();
				} else {
					this.getDoc().execCommand(command, user_interface, value);
				}
				break;
			case "createlink":
					this.getDoc().execCommand(command, user_interface, value);
				break;
			// Handle user defined BBCodes
			default:
				command = command.replace(/^mce_/, '');
				var attributes = extraBBCodes[command]['attributes'];
				if (this.editorIsActive) {
					var selectedText = this.selection.getSelectedHTML();
					if (extraBBCodes[command]['wysiwyg']) {
						var selectedElement = tinyMCE.selectedElement;
						var attributesString = '';
						var validInsert = true;
			
						// Remove user defined BBCodes
						if (tinyMCE.isInsideUserdefinedTag(selectedElement, extraBBCodes[command]['htmlOpen'], attributes) && (tinyMCE.trim(this.selection.getSelectedText()) == tinyMCE.trim(tinyMCE.getInnerText(selectedElement)) || selectedElement.innerHTML == '') ){
							var newNode = this.getDoc().createElement('span');
							newNode.innerHTML = (selectedElement.innerHTML);
							selectedElement.parentNode.replaceChild(newNode, selectedElement);
						}
						// Exit user defined BBCodes
						else if (tinyMCE.isInsideUserdefinedTag(selectedElement, extraBBCodes[command]['htmlOpen'], attributes)){
							var textElement = this.getDoc().createElement('SPAN');	
							textElement.innerHTML = "&nbsp;";
							tinyMCE.insertAfter(textElement, selectedElement);
							this.selection.selectNode(textElement);
						}
						// Insert user defined BBCodes
						else {
							// build attributes string
							attributesString = this.buildAttributeString(attributes, command, true);						
							if (validInsert) {
								var insert = '<' + extraBBCodes[command]['htmlOpen'] + attributesString + '>' + selectedText;
								if (extraBBCodes[command]['htmlClose']) {
									insert += '</' + extraBBCodes[command]['htmlClose'] + '>';
								}
								this.execCommand("mceInsertContent", false, insert);
							}
						}
					}
					else {
						this.insertContent('[' + command + ']', '[/' + command + ']');
					}
				}
				else {
					attributesString = this.buildAttributeString(attributes, command, false);						
					tinyMCE.simpleExecCommand(command, this.editorId+'_codeview', attributesString);	
				}
				return true;	
		}
	},
	insertContent : function(startTag, endTag) {
		if (typeof(endTag) == "undefined") endTag = startTag;	
		if (!tinyMCE.isOpera && !tinyMCE.isSafari) {
			var range = this.getRng();
			var startOffset = 0;
			startOffset = range.startOffset;

			var selectedText = this.selection.getSelectedHTML();
			var cursorPosition = '';
			if (selectedText == '') cursorPosition = "<span id=\"codeCurserPosition\"></span>";
			this.execCommand("mceInsertContent", false, startTag + selectedText + cursorPosition + endTag);

			// Place cursor between tags
			if (selectedText == '') {
				var cursorPosElement = this.getDoc().getElementById("codeCurserPosition");
				this.selection.selectNode(cursorPosElement);
				cursorPosElement.parentNode.removeChild(cursorPosElement);
			}
		}
		else {
			selectedText = this.selection.getSelectedHTML();
			this.execCommand("mceInsertContent", false, startTag + selectedText + endTag);
		}
	},
	queryCommandValue : function(c) {
		try {
			return this.getDoc().queryCommandValue(c);
		} catch (e) {
			return null;
		}
	},
	buildAttributeString : function(attributes, command, editorIsActive) {
		var attributesString = '';
		for (var i = 0; i < attributes.length; i++) {
			var attribute = attributes[i];
			var attributeHTML = attribute['attributeHTML'];
			var attributeValue = '';
								
			// Prompt user for attribute value
			if (attribute['attributeHTML'] != '' && attribute['attributeHTML'].match(/%s/)) {
				var value = prompt(language[command+'.attribute'+(i+1)+'.promptText'], '');
								
				// Check if prompt is optional
				if (value == null) {
					if (attribute['required']) {
						validInsert = false;
					}
					else validInsert = false;
				}
				// Validate prompt value
				else if (attribute['validationPattern'] != '') {
					if (value.match(attribute['validationPattern'])) {
						attributeValue = value;
					}
					else {
						alert(language['extraBBCodeNotValid']);
						validInsert = false;	
						break;
					}
				}
				// Take prompt value
				else attributeValue = value;
			}
							
			if (editorIsActive) {
				attributesString += ' ' + attribute['attributeHTML'].replace(/%s+/, attributeValue);		
			}
			else {
				if(attributesString != '') attributesString += ','; 
				attributesString += attributeValue;
			}
		}
		return attributesString;
	},
	
//-------------------------- CLEAN PASTE -------------------------------\\	
	
	// Check for pasted content 
	detectPaste : function(keyEvent) {
		var keyPressed = null;
		var theEvent = null;
		
		if (keyEvent) theEvent = keyEvent;
		else theEvent = event;
		
		if (!tinyMCE.isOpera && theEvent.ctrlKey && theEvent.keyCode == 86)	{
			var self = this;

			// Because Mozilla can't access the clipboard directly, we have to rely on timeout to check pasted differences in main content 
			window.setTimeout(function(){self.cleanupHTML(); return true;}, 100);
		}
		return true;
	},
	
	/*  Clean up content (no tables etc.) */
	cleanupHTML : function() {
		// Save cursor position to place it later after setting innerHTML 
		if (!tinyMCE.isMSIE || tinyMCE.isOpera) {
			var pasteRange = this.selection.getRng();
			var pastePositionElement = this.getDoc().createElement('span');
			pastePositionElement.id = 'pasteCursorPosition';
			pasteRange.insertNode(pastePositionElement);
			// TODO: Cursor position wrong:
			// http://beta.woltlab.de/index.php?page=Thread&threadID=6994
		}
		// Save cursor position to place it later after setting innerHTML
		else if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
			var pasteRange = this.getDoc().selection.createRange();
			pasteRange.pasteHTML('<span id="pasteCursorPosition"></span>');
		}
		content = this.getBody().innerHTML;
		
		if (tinyMCE.isGecko) {
			// Erase tabs (\t). Firefox inserts them from HTML source code
			content = content.replace(/\t/g, ''); 

			// Erase newlines (\n). Relevant newlines are turned to <br> by browser 
			content = content.replace(/\n/g, ' ');
		}
		// IE displays pasted code in one line
		else if (tinyMCE.isMSIE) {
			// tabs (\t) to 4 x &nbsp;
			content = content.replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;'); 
		}

		content = tinyMCE.cleanupHTML_(content);

		// Don't convert smileys in code blocks
		var codeBlocks = new Object();
		// Do not decode and no newline to breaks: otherwise pasted encoded HTML will be parsed to decoded HTML (&lt;strong&gt;  -to-  <strong>) and thus real HTML
		// http://www.woltlab.de/forum/index.php?page=Thread&postID=716898#post716898
		content = tinyMCE.extractCodeBlocks(content, codeBlocks, false, false); 

		// Add absolute src and add wcf_src to all images
		content = content.replace(/<img(.*?)src="(.*?)"/gi, function(thisMatch, attributes, src) {
			if (!src.match(/^http/)) src = tinyMCE.settings['base_href'] + src;
			return '<img'+ attributes +'src="' + src + '" wcf_src='+src;
		});
		
		// Add absolute href and add wcf_href to all links
		content = content.replace(/<a(.*?)href="(.*?)"/gi, function(thisMatch, attributes, href) {
			if (!href.match(/^http/)) href = tinyMCE.settings['base_href'] + href;
			return '<a'+ attributes +'href="' + href + '" wcf_href='+href;
		});


		// Extract entities and insert unique string to reinsert them later 
		var entities = new Object();
		content = tinyMCE.extractEntities(content, entities);

		// Replace smiley codes with images
		for (var smileyCode in smilies) {

			var smileyRegex = new RegExp('' + smileyCode.pregQuote() + '(?!")', 'g');

			// Smiley code found
			if (content.match(smileyRegex)) {
				var url = smilies[smileyCode][0];
				if (tinyMCE.isMSIE) url = tinyMCE.settings['base_href'] + url;
							
				// Replace code with smiley image
				var html = '<img src="' + url + '" wcf_src="' + url + '" alt="' + smileyCode + '" />';							
				content = content.replace(smileyRegex, html);
			}
		}
		
		// Reinsert entities
		for (var uniqueString in entities) {
			var entity = entities[uniqueString];
			content = content.replace(uniqueString, entity);
		}
		
		// Reinsert code blocks
		for (var uniqueString in codeBlocks) {
			content = unescape(content.replace(uniqueString, escape(codeBlocks[uniqueString])));
		}
		
		// Check if content is valid XHTML 
		try {
			// This could lead to a javascript error in Firefox
			// but the catch block will at least clean up the content to "valid" XHTML 
			// (only wrong nested tags, but for example not if an <a> is inside an <a>).
// TODO: The catch block was only used for "application/xhtml" mode. maybe this catch could be removed?			
			this.theTextarea.value = content;
		}
		// Does some more validation (close nonclosed tags and close them in correct order)
		catch(e) {
			// Splits text into tags
			var nodes = content.split("<");
			var openNodes = new Array();
			for (var i = 0; i < nodes.length; i++) {
				if (i > 0) nodes[i] = '<' + nodes[i];
				// Found open tag
				if (nodes[i].match(/^<(\w+)(.*?)>/i)) { 
					var openTagName = RegExp.$1;
					var tagStuff = RegExp.$2;
					// Stores closeable tag
					if (!openTagName.match(/(img|br)/)) {
						openNodes[openNodes.length] = RegExp.$1;
					}
					// Replaces non closeable invalid tags
					else if (!tagStuff.match(/\/s\/$/)) {
						nodes[i] = nodes[i].replace(/<(\w+)(.*?)(\/)?>/, '<$1 $2/>');
					}
					
				}
				// Found close tag
				else if (nodes[i].match(/^<\/(\w+)>/i)) {
					if (openNodes.length > 0) {
						var openTagName =  openNodes.pop();
						// Closes last open tag
						if (openTagName != RegExp.$1) {
							nodes[i] = nodes[i].replace(/<\/\w+>/, '</' + openTagName + '>');
						}
					}
				}
			}
			if (openNodes.length > 0) {
				openNodes = openNodes.reverse();
				var closingNodes = '';
				for (var i = 0; i < openNodes.length; i++)  {
					closingNodes += openNodes[i].replace(/(\w+)/, '</$1>');
				}
				nodes[nodes.length] = closingNodes;
			}
			this.theTextarea.value = nodes.join('');
		}
		this.refreshDisplay();
		return true;
	},

	/* Updates hidden input to reflect editor contents, for submission */
	refreshDisplay : function() {
		if (this.editorIsActive) {
			// TODO: Check lost control characters (IE shows code in one line)
			this.getBody().innerHTML = this.theTextarea.value; 

			// Places cursor after pasted content
			var cursorPositionElement = this.getDoc().getElementById('pasteCursorPosition');
			if (cursorPositionElement) {
				this.selection.selectNode(cursorPositionElement);
				var emtpyTextNode = this.getDoc().createTextNode('');
				var parentElement = cursorPositionElement.parentNode;
				parentElement.replaceChild(emtpyTextNode, cursorPositionElement);
			}
			// TODO: In case this function was called by the paste button, place the cursor correct too.
		}
		return true;
	},
	
	getViewPort : function() {
		return tinyMCE.getViewPort(this.getWin());
	}
	
//-------------------------- END CLEAN PASTE -------------------------------\\		
}
/* END CONTROL FUNCTIONS */

/* Create the HTML code for the editor */
function getEditorTemplate (settings, editorId) {
		var template = new Array();
		var toolbarHTML = '';	
		
		toolbarHTML += '<ul>';
		toolbarHTML += tinyMCE.getToolbarElements('{$editor_id}');
		toolbarHTML += '</ul>';

		var editorInstance = tinyMCE.getInstanceById(editorId);
		
		// Setup tabs	
		var tabs = '<div class="tabMenu">';
		var wysiwygClass = 'activeTabMenu';
		var codeClass = '';
		if (!editorInstance.editorIsActive) {
			wysiwygClass = '';
			codeClass = 'activeTabMenu'; 		
		}
		// "Tab Menu"
		// Both views available
		if (tinyMCE.editorEnableWysiwygView && tinyMCE.editorEnableCodeView) {
			tabs += '<ul><li class="' + wysiwygClass + '" id="' + editorInstance.editorTabID + '"><a href="javascript:tinyMCE.execInstanceCommand(\'' + editorId + '\', \'mceWysiwygEditor\', false);"><span>' + language['view.wysiwyg'] + '</span></a></li>';
			tabs += '<li class="' + codeClass + '" id="' + editorInstance.codeTabID + '"><a href="javascript:tinyMCE.execInstanceCommand(\'' + editorId + '\', \'mceCodeView\', false);"><span>' + language['view.code'] + '</span></a></li></ul></div>';
		}
		// Only WYSIWYG available
		else if (tinyMCE.editorEnableWysiwygView) {
			tabs += '<ul><li class="' + wysiwygClass + '" id="' + editorInstance.editorTabID + '"><a href="javascript:tinyMCE.execInstanceCommand(\'' + editorId + '\', \'mceWysiwygEditor\', false);"><span>' + language['view.wysiwyg'] + '</span></a></li></ul></div>';		
		}
		// Only code view available
		else {
			tabs += '<ul><li class="' + codeClass + '" id="' + editorInstance.codeTabID + '"><a href="javascript:tinyMCE.execInstanceCommand(\'' + editorId + '\', \'mceCodeView\', false);"><span>' + language['view.code'] + '</span></a></li></ul></div>';
		}
		
		
		// "Sub Tab Menu" including toolbar
		tabs += '<div class="subTabMenu"><div class="containerHead">';
		tabs += '<div id="{$editor_id}_toolBar" class="mceToolbar">';
		tabs += toolbarHTML;
		tabs += '</div></div></div>';
		
		// WYSIWYG/code tabs
		template['html'] = tabs;
		
		// "Tab Menu Content"
		var errorBorder = '';
		if (typeof(errorField) != 'undefined' && errorField) errorBorder = ' mceErrorBorder';  
		template['html'] += '<div id="{$editor_id}_tabContent" class="border' + errorBorder + '"><div class="tabMenuContent container-1">';

		// Editable content (iframe or textarea)
		template['html'] += '<div id="{$editor_id}"></div>';
		
		// Close wrapping tabcontent
		template['html'] += '</div></div>';
		
		// Resize box (is used to make editor resizeable)
		template['html'] += '<div id="{$editor_id}_resize_box" class="mceResizeBox"></div>';
		
		// Resizer icon
		template['html'] += '<div class="border mceResizeIconRow"><div class="container-1">';
		template['html'] += '<div id="{$editor_id}_resize" class="mceResizeIcon" onmousedown="tinyMCE.setResizing(event,\'{$editor_id}\',true);">';
		template['html'] += '</div></div></div>';
		
		return template;
}
TinyMCE_Engine.prototype.getToolbarElements = function(editorId) {
	var toolbarElements = tinyMCE.toolElements;
	var toolbarHTML = '';
		
	// Try executing copy command
	// Not in use because only Safari does this with default settings, but paste doesn't work. And: this "try ... copy"  empties the clipboard.
	/* 
	if (!tinyMCE.isSimpleTextarea && (!tinyMCE.isMSIE)) {
		var cmdFailed = false;
		var isClipboardAccessible = true;
		eval('try {document.execCommand(\'Copy\', false, null);} catch (e) {cmdFailed = true;}');
		if (cmdFailed) isClipboardAccessible = false;
	}
	*/
	
	// Font family options 
	fontFamilyOptions = '<option value="">' + language['fontFamily.default'] + '</option>'+
		'<option style="font-family:Arial, Helvetica, sans-serif" value="Arial, Helvetica, sans-serif">Arial</option>'+
		'<option style="font-family:Chicago, Impact, Compacta, sans-serif" value="Chicago, Impact, Compacta, sans-serif">Chicago</option>'+
		'<option style="font-family:\'Comic Sans MS\', sans-serif" value="\'Comic Sans MS\', sans-serif">Comic Sans MS</option>'+
		'<option style="font-family:\'Courier New\', Courier, mono" value="\'Courier New\', Courier, mono">Courier New</option>'+
		'<option style="font-family:Geneva, Arial, Helvetica, sans-serif" value="Geneva, Arial, Helvetica, sans-serif">Geneva</option>'+
		'<option style="font-family:Georgia, \'Times New Roman\', Times, serif" value="Georgia, \'Times New Roman\', Times, serif">Georgia</option>'+
		'<option style="font-family:Helvetica, Verdana, sans-serif" value="Helvetica, Verdana, sans-serif">Helvetica</option>'+
		'<option style="font-family:Impact, Compacta, Chicago, sans-serif" value="Impact, Compacta, Chicago, sans-serif">Impact</option>'+
		'<option style="font-family:\'Lucida Sans\', Monaco, Geneva, sans-serif" value="\'Lucida Sans\', Monaco, Geneva, sans-serif">Lucida Sans</option>'+
		'<option style="font-family:Tahoma, Arial, Helvetica, sans-serif" value="Tahoma, Arial, Helvetica, sans-serif">Tahoma</option>'+
		'<option style="font-family:\'Times New Roman\', Times, Georgia, serif" value="\'Times New Roman\', Times, Georgia, serif">Times New Roman</option>'+
		'<option style="font-family:\'Trebuchet MS\', Arial, sans-serif" value="\'Trebuchet MS\', Arial, sans-serif">Trebuchet MS</option>'+
		'<option style="font-family:Verdana, Helvetica, sans-serif" value="Verdana, Helvetica, sans-serif">Verdana</option>';
	
	// Font sizes options
	fontSizeOptions = '<option value="0">' + language['fontsize.default'] + '</option>'+
		'<option style="font-size:8pt" value="1">8 pt</option>'+
		'<option style="font-size:10pt" value="2">10 pt</option>'+
		'<option style="font-size:12pt" value="3">12 pt</option>'+
		'<option style="font-size:14pt" value="4">14 pt</option>'+
		'<option style="font-size:18pt" value="5">18 pt</option>'+
		'<option style="font-size:24pt" value="6">24 pt</option>'+
		'<option style="font-size:36pt" value="7">36 pt</option>';
	
	// Adds toolbar elements
	var afterSeparator = true;
	for (var i = 0; i < toolbarElements.length; i++) {
		for (var j = 0; j < toolbarElements[i].length; j++) {

			//var isButton = false;
			var toolbarElement = toolbarElements[i][j];
			
			// Checks if clipboard buttons should be displayed
			// Don't display copy & paste and separator after them if: IE || no clipboard access 
			// Not in use because only Safari does this with default settings, but paste doesn't work. And: this "try ... copy"  empties the clipboard.
			if (0 && !tinyMCE.isSimpleTextarea && ((tinyMCE.isMSIE && !tinyMCE.isOpera) || !isClipboardAccessible) && 
				(toolbarElements[i][j].match(/(cut|copy|paste)/) || (i == 1 && j == 3 && toolbarElements[i][j] == 'separator'))) {
				continue;
			}
			// Don't display copy & paste and undo/redo (and separators behind) if simple textarea
			else if (tinyMCE.isSimpleTextarea && (toolbarElements[i][j].match(/(cut|copy|paste|undo|redo)/) || (toolbarElements[i][j] == 'separator' && (toolbarElements[i][j-1] == 'paste' || toolbarElements[i][j-1] == 'redo')))) {
				continue;
			}
			
			// Insert bbcode element (button & selects)
			var bbCodeElement = false;
			for (var code in coreBBCodes) {
				
				if (code == toolbarElement) {
					var bbCodeElement = coreBBCodes[code];
					var execCommand = '';
					
					switch (code) {
						case 'b': execCommand = 'Bold'; break;
						case 'i': execCommand = 'Italic'; break;
						case 'u': execCommand = 'Underline'; break;
						case 's': execCommand = 'Strikethrough'; break;
						case 'quote': execCommand = 'mceQuote'; break;
						case 'img': execCommand = 'mceImage'; break;
						case 'code': execCommand = 'mceCodeTag'; break;
					}
											
					// One code one button
					if (code.match(/^(b|i|u|s|quote|img)$/) || bbCodeElement['sourceCode']) {
						var userInterface = false;
						if (code == 'img') userInterface = true;
						toolbarHTML += '<li id="' + editorId + '_' + code + '_li">';
						toolbarHTML += tinyMCE.getButtonHTML(code, code+".desc", bbCodeElement['icon'], execCommand, userInterface, false, editorId);
						toolbarHTML += '</li>';
						bbCodeElement = true;
						break;
					}
					else if (code == 'color') {
						toolbarHTML += '<li id="' + editorId + '_color_li">';
						toolbarHTML += '<a id="' + editorId + '_color" href="javascript:void(0)" onmousedown="return false;" class="" target="_self">'; 
						toolbarHTML += '<img src="' + this.settings['imageURL'] +  bbCodeElement['icon'] + '" title="' + language['color.desc'] + '" style="background-color: transparent;" />';
						toolbarHTML += '</a>';
						toolbarHTML += '</li>';
						
						bbCodeElement = true;
						break;
					}
					// One code more buttons
					else if (code.match(/^(align|url|list)$/)) {
							switch (code) {
							case 'align': 
								toolbarHTML += '<li id="' + editorId + '_justifyleft_li">';
								toolbarHTML += tinyMCE.getButtonHTML('justifyleft', "textAlignLeft.desc", 'textAlignLeftM.png', 'JustifyLeft', false, false, editorId);
								toolbarHTML += '</li><li id="' + editorId + '_justifycenter_li">';
								toolbarHTML += tinyMCE.getButtonHTML('justifycenter', "textAlignCenter.desc", 'textAlignCenterM.png', 'JustifyCenter', false, false, editorId);
								toolbarHTML += '</li><li id="' + editorId + '_justifyright_li">';
								toolbarHTML += tinyMCE.getButtonHTML('justifyright', "textAlignRight.desc", 'textAlignRightM.png', 'JustifyRight', false, false, editorId);
								toolbarHTML += '</li><li id="' + editorId + '_justifyfull_li">';
								toolbarHTML += tinyMCE.getButtonHTML('justifyfull', "textJustify.desc", 'textJustifyM.png', 'JustifyFull', false, false, editorId);
								toolbarHTML += '</li>';
							break; 
							case 'url': 
								toolbarHTML += '<li id="' + editorId + '_link_li">';
								toolbarHTML += tinyMCE.getButtonHTML('link', "link.desc", 'linkInsertM.png', 'mceLink', true, false, editorId);							
								toolbarHTML += '</li>';
								if (!tinyMCE.isSimpleTextarea) {
									toolbarHTML += '<li id="' + editorId + '_unlink_li">';
									toolbarHTML += tinyMCE.getButtonHTML('unlink', "unlink.desc", 'linkRemoveM.png', 'unlink', false, false, editorId);							
									toolbarHTML += '</li>';
								}
							break; 
							case 'list': 
								toolbarHTML += '<li id="' + editorId + '_list_li">';
								toolbarHTML += tinyMCE.getButtonHTML('bullist', "bullist.desc", 'listStyleUnorderedM.png', 'InsertUnorderedList', false, false, editorId);
								toolbarHTML += '</li><li id="' + editorId + '_numlist_li">';							
								toolbarHTML += tinyMCE.getButtonHTML('numlist', "numlist.desc", 'listStyleOrderedM.png', 'InsertOrderedList', false, false, editorId);							
								toolbarHTML += '</li>';
							break; 
						}
						bbCodeElement = true;
						break;
					}
					// Select
					else if (code.match(/^(font|size)$/)) {
						var selectID = execCommand = options = ''
						if (code == 'font') {
							selectID = 'fontNameSelect';
							execCommand = 'FontName';
							options = fontFamilyOptions;
							
						}
						else {
							selectID = 'fontSizeSelect';
							execCommand = 'FontSize';
							options = fontSizeOptions;
						}
						toolbarHTML += '<li id="' + editorId + '_' + selectID + '_li">';
						var onChange = '';
						if (tinyMCE.isSimpleTextarea) {
							onChange = 'tinyMCE.simpleExecCommand(\''+ execCommand +'\', \''+ editorId +'\', this.options[this.selectedIndex].value);';
							toolbarHTML += '<select class="fontFormat" id="'+ editorId + '_' + selectID +'" name="{$editor_id}_'+ selectID +'" onfocus="tinyMCE.addSelectAccessibility(event, this, window);" onchange="' + onChange + '">'
								+ options
								+ '</select>';
						}
						else {
							onChange = 'tinyMCE.execInstanceCommand(\'{$editor_id}\',\''+ execCommand +'\',false,this.options[this.selectedIndex].value);';
							toolbarHTML += '<select class="fontFormat" id="{$editor_id}_'+ selectID +'" name="{$editor_id}_'+ selectID +'" onfocus="tinyMCE.addSelectAccessibility(event, this, window);" onchange="' + onChange + '">'
								+ options
								+ '</select>';
						}
						bbCodeElement = true;
						toolbarHTML += '</li>';
						break;
					}
				}
			}

			// Not a BBCode element
			if (!bbCodeElement) {
				switch (tinyMCE.toolElements[i][j]) {
					case "cut":
					case "copy":
					case "paste":
					case "undo":
					case "redo":
					case "quotation":
						var element = tinyMCE.toolElements[i];
						var elementName = element[j];
						var image = '';
						var execCommand = '';
						
						if (elementName == 'quotation') {
							image = 'quotationMarksM.png';
							execCommand = 'mceQuotation';
						}
						else {
							image = elementName +'M.png';
							execCommand = elementName.replace(/^(.)/, '$1'.toUpperCase());
						}
						toolbarHTML += '<li id="' + editorId + '_' + elementName + '_li">';							
						toolbarHTML += tinyMCE.getButtonHTML(elementName, elementName+".desc", image, execCommand, false, false, editorId);
						toolbarHTML += '</li>';
						afterSeparator = false;
						break;	
					case "separator":
						if (!afterSeparator) {
							toolbarHTML += '<li>';
							toolbarHTML += '<img src="' + tinyMCE.settings['imageURL'] + 'separatorM.png" class="mceSeparator" />';
							toolbarHTML += '</li>';
							afterSeparator = true;
						}
						break;
					case "break":
						toolbarHTML += '</ul><ul>';
						afterSeparator = true;
						break;
				}
			}
			else {
				afterSeparator = false;
			}
		}
	}
		
	// Displays extra BBCodes 
	var separator = true;	
	for (var code in extraBBCodes) {
		if (extraBBCodes[code]['icon'] != '') {
			if (separator) {
				toolbarHTML += '<li><img src="' + tinyMCE.settings['imageURL'] + 'separatorM.png" class="mceSeparator" /></li>';
				separator = false;
			}
			toolbarHTML += '<li id="' + editorId + '_' + code + '_li">';
			toolbarHTML += tinyMCE.getButtonHTML(code, code + '.title', extraBBCodes[code]['icon'], 'mce_' + code, false, null, editorId);
			toolbarHTML += '</li>';
		}
	}
	return toolbarHTML;
}
		
//--------------------- RESIZE FUNCTIONS ----------------------------
/* Starts/stops the editor resizing */
TinyMCE_Engine.prototype.setResizing = function(resizeEvent, editorID, state) {
	resizeEvent = typeof(resizeEvent) == "undefined" ? window.event : resizeEvent;
	
	var instance = tinyMCE.getInstanceById(editorID);
	var resizer = instance.resizer;
	var tabContent = instance.tabContent;
	var resizeBox = document.getElementById(editorID + '_resize_box');

	if (state) {
		// Places box over the editor area
		var height = tabContent.clientHeight;
		if (height == 0) height = tabContent.offsetHeight - 2;
		resizeBox.style.height = height + "px";
		resizer.iframeHeight = instance.iframeElement.clientHeight;

		widthFix = 0;
		if (tinyMCE.isGecko) widthFix = 1;
		var width = tabContent.clientWidth;
		
		if (width == 0) width = tabContent.offsetWidth - 2;
	
		// Hides editor and shows resize box
		tabContent.style.display = "none";
		resizeBox.style.display = "block";

		// Adds event handlers, only once
		if (!resizer.eventHandlers) {
			if (tinyMCE.isMSIE) {
				tinyMCE.addEvent(document, "mousemove", tinyMCE.resizeEventHandler);
			}
			else {
				tinyMCE.addEvent(window, "mousemove", tinyMCE.resizeEventHandler);
			}
			tinyMCE.addEvent(document, "mouseup", tinyMCE.resizeEventHandler);
			resizer.eventHandlers = true;
		}
		resizer.resizing = true;
		resizer.downY = resizeEvent.screenY;
		resizer.height = parseInt(resizeBox.style.height);
		resizer.editorId = editorID;
		resizer.resizeBox = resizeBox;
	} else {
		resizer.resizing = false;
		resizeBox.style.display = "none";
		tabContent.style.display = "block";
		tinyMCE.execCommand('mceResetDesignMode');
	}
}

/* Handles resizing events */
TinyMCE_Engine.prototype.resizeEventHandler = function(event) {
	var instance = tinyMCE.selectedInstance;
	var resizer = instance.resizer;

	// Do nothing
	if (!resizer.resizing) return;

	event = typeof(event) == "undefined" ? window.event : event;

	var deltaHeight = event.screenY - resizer.downY;
	var resizeBox = resizer.resizeBox;
	
	switch (event.type) {
		case "mousemove":
			var height = resizer.height + deltaHeight;
			height = height < 1 ? 1 : height;
			resizeBox.style.height = height + "px";
		break;

		case "mouseup":
			tinyMCE.setResizing(event, resizer.editorId, false);
			tinyMCE.resize(instance, resizer.height + deltaHeight);
		break;
	}
}

TinyMCE_Engine.prototype.resize = function(inst, height) {
	// Gets elements
	var tabContent = inst.tabContent;
	var iframe = inst.iframeElement;
	var theTextarea = inst.theTextarea;
	
	// No value no resizing
	if (height == null || height == "null") return;

	// Changes height of iframe & textarea
	theTextarea.style.height = iframe.style.height = (height - 2) + "px";
	inst.settings['height'] = (height - 2);
}

/* Creates hidden input fields for passing data to PHP for editor height and view mode (WYSIWYG or mode) */
TinyMCE_Engine.prototype.createEditorSettingFields = function(editorIsActive, parentElement, iframe, textarea) {

	// Creates input elements
	var inputWysiswygHeight = document.createElement('input');
	var inputWysiswygMode = document.createElement('input');
	inputWysiswygHeight.type = 'hidden';
	inputWysiswygMode.type = 'hidden';
	inputWysiswygHeight.name = 'wysiwygEditorHeight';
	inputWysiswygMode.name = 'wysiwygEditorMode';

	// Detects mode
	if (editorIsActive) inputWysiswygMode.value = 1;
	else if (editorIsActive === false) inputWysiswygMode.value = 0;
	else inputWysiswygMode.value = this.settings['editorIsActive'];
	
	// Detects height (height is changed directly by resizer in this array. no need to get clientheight)
	inputWysiswygHeight.value = this.settings['height'];

	// Appends elements	
	parentElement.appendChild(inputWysiswygHeight);
	parentElement.appendChild(inputWysiswygMode);
}

//------------------------- SIMPLE TEXTAREA ---------------------------\\
TinyMCE_Engine.prototype.loadSimpleTextarea = function() {
	
	// Attention: Opera 8.5 searches for both ID and name attribute 
	var textarea = document.getElementById(this.simpleAreaID);
	if (!textarea) return;
	
	var editorTemplate = tinyMCE.getSimpleTextareaTemplate(this.simpleAreaID);
	
	// Gets content of textarea
	var textareaContent = textarea.value;
	var parentElement = textarea.parentNode;

	// Hides "message" label
	var labelElements = document.getElementsByTagName('label');
	for (var i = 0; i < labelElements.length; i++) {
        	if (labelElements[i].getAttribute('for') == this.simpleAreaID) {
		        labelElements[i].style.display = 'none';
		} 	
	}    
	
	// Adds simple textarea to template
	if (tinyMCE.isOpera95 || tinyMCE.isGecko || tinyMCE.isKonqueror) {
		var rng = textarea.ownerDocument.createRange();
		rng.setStartBefore(textarea);
		var fragment = rng.createContextualFragment(editorTemplate);
		parentElement.replaceChild(fragment, textarea);
	} 
	else {
		textarea.insertAdjacentHTML("beforeBegin", editorTemplate);
		document.getElementById('text').style.display = 'none';
	}
		
	// Creates color picker popup
	tinyMCE.createColorPicker(this.simpleAreaID, true);

	// Adds content from DB to textarea
	this.simpleTextarea = document.getElementById(this.simpleAreaID);
	this.simpleTextarea.value = textareaContent; 
	
	// Adds setting input fields
	tinyMCE.addEvent(window, "load", TinyMCE_Engine.prototype.simpleEditorSettingFields);
	
	// Places cursor in editor or subject
	tinyMCE.initCursor();
	tinyMCE.initialized = true;
}

TinyMCE_Engine.prototype.simpleEditorSettingFields = function() {
	var parentElement = document.getElementById(tinyMCE.simpleAreaID).parentNode;
	tinyMCE.createEditorSettingFields(null, parentElement, null, tinyMCE.simpleTextarea);
}

TinyMCE_Engine.prototype.getSimpleTextareaTemplate = function(simpleAreaID) {
	var html = '';
	html = '<div class="tabMenu"><ul><li class="activeTabMenu"><a><span>' + language['view.code'] + '</span></a></li></ul></div>';
	html += '<div class="subTabMenu"><div class="containerHead">';
	html += '<div id="toolBar" class="mceToolbar"><ul>'+ tinyMCE.getToolbarElements(simpleAreaID) + '</ul></div></div></div>';
	html += '<div id="tabContent" class="border tabMenuContent"><textarea class="mceInputText" name="text" id="text" cols="20" rows="20"></textarea></div>';
	return html;
}
TinyMCE_Engine.prototype.simpleExecCommand = function(command, textareaID, attribut) {
	if (typeof(attribut) == 'undefined') attribut = '';
	var tag = tagRegex = '';

	switch (command) {
		case 'Bold': 		tag = 'b'; break;
		case 'Italic': 		tag = 'i'; break;
		case 'Underline':	tag = 'u'; break;
		case 'Strikethrough':	tag = 's'; break;
		case 'JustifyLeft':	attribut = 'left'; tag = 'align';break;
		case 'JustifyRight':	attribut = 'right'; tag = 'align';break;
		case 'JustifyCenter':	attribut = 'center'; tag = 'align';break;
		case 'JustifyFull':	attribut = 'justify'; tag = 'align';break;
		case 'FontName':	tag = 'font'; break;
		case 'FontSize': 	tag = 'size'; break;
		case 'color': 		tag = 'color'; break;
		case 'InsertOrderedList':	attribut = '1';
		case 'InsertUnorderedList':	tag = 'list';break;
		case 'mceLink':		tag = 'url'; break;
		case 'mceImage':	tag = 'img'; break;
		case 'mceQuote': 	tag = 'quote'; break;
		case 'mceQuotation': 	tag = '"'; break;
		case 'mceCodeTag':	tag = 'code'; break;
		case 'smiley':
		case 'mceText':
					tag = '';
		                       	attribut = attribut;
					break;
		default: 				
			tag = command.replace(/^mce_/, '');
			if (tag != 'attach') tagRegex = '|' + command;
	}

	if (command == 'FontSize') {
	   // Array for converting "size" (1-7) to pt (8-36)
	   	var fontPointSizes = new Array();
		
	   fontPointSizes[1] = 8;
	   fontPointSizes[2] = 10;
	   fontPointSizes[3] = 12;
	   fontPointSizes[4] = 14;
	   fontPointSizes[5] = 18;
	   fontPointSizes[6] = 24;
	   fontPointSizes[7] = 36;
	   attribut = fontPointSizes[attribut];
	}
	
	// Selects default value for font select boxes
	if (command == 'FontSize' || command == 'FontName') {
		var cmd = command.substr(0,1).toLowerCase() + command.substr(1);  
		var editorID = textareaID.replace(/(mce_editor_\d+)_.*/, '$1');
		var selectBox = document.getElementById(editorID + '_' + cmd + 'Select');
		selectBox.selectedIndex = 0;

		if (tinyMCE.isSafari3 && command == 'FontSize' && typeof(attribut) == 'undefined') {
			return;
		}
		
	}
	
	var startTag = '';
	var endTag = '';
	if (tag != '' && tag != '"') {
		// define tags
		startTag = '[' + tag + ']';
		endTag = '[/' + tag + ']';
		if (tag == 'list') {
			startTag += '[*]';
			endTag = '\n'+ endTag;
		}
		
		// [font='Trebuchet MS', Arial, sans-serif] to [font='Trebuchet MS, Arial, sans-serif']
		if (tag == 'font') { 
			attribut = attribut.replace(/'/g, '');
			attribut = "'" + attribut + "'";
		}
	}
	else if (tag == '"') startTag = endTag = tag;
	
	tinyMCE.insertCode(textareaID, startTag, endTag, attribut, tagRegex);
}

TinyMCE_Engine.prototype.insertCode = function (textareaID, startTag, endTag, attribut, tagRegex) {
	if(typeof(tagRegex) == "undefined") tagRegex = '';
 	var textarea = document.getElementById(textareaID);
	textarea.focus();

	/* Gecko, Opera, etc. */
	if(typeof(textarea.selectionStart) != 'undefined')  {

		// Insert BBCode
		var start = textarea.selectionStart;
		var end = textarea.selectionEnd;
		var insText = textarea.value.substring(start, end);
		
		if (startTag == '[url]') {
			var returnValues = tinyMCE.handleURLTag(insText, startTag);
			insText = returnValues[0];
			startTag = returnValues[1];
			if (startTag == '') return;
		}
		else if (startTag == '[img]') {
			if (insText == '') insText = prompt(language['image.insert'], '');
			if (insText == null || insText == '') return;
				
		}
		// Handle BBCode start tags with optional attribute
		else if (insText == '' && attribut != '') {
			// Real attribute
			var regex = new RegExp("(align|list|size|font|color" + tagRegex + ")");
			if (startTag.match(regex)) {
				startTag = startTag.replace(/\[(\w+)\]/, '[$1=' + attribut + ']');
			}
			// Value between tags
			else insText = attribut.toString();
		}	
		else if (attribut != '') {
			startTag = startTag.replace(/\[(\w+)\]/, '[$1=' + attribut + ']');
			
			// Inserts smiley or text with active selection: 
			if (startTag == '') {
				startTag = attribut;
				insText = '';
			}
		}
		
		// Stores cursor position to scroll to insert position later
		scrollY = textarea.scrollLeft;
		scrollX =  textarea.scrollTop;
		
		textarea.value = textarea.value.substr(0, start) + startTag + insText + endTag + textarea.value.substr(end);
		/* Moves cursor */ 
		// TODO: Safari3 doesn't set the focus on the textarea after using a toolbar button (but selections are ok)
		var startPos;
		var endPos;
		
		// Places cursor between the tags
		if (insText.length == 0) {
			startPos = endPos = start + startTag.length;
		}
		// Places cursor behind inserted smiley
		else if (startTag == '') {
			startPos = endPos = start + insText.length;
		}
		// Marks inserted text
		else {
			endPos = start + startTag.length + insText.length;
			startPos = start + startTag.length;
		}
		
		// Timeout call because Konqueror sets the selection incorrect. Seems that it needs a little time to set the value of the textarea.
		window.setTimeout("tinyMCE.setSelectionPos('" + textareaID + "', '" + startPos + "', '" + endPos + "', '" + scrollY + "', '" + scrollX + "')", 5);
	}
	/* IE */
  	else if (typeof(document.selection) != 'undefined') {
		
		// Get selected text 
		// TODO: this doesn't work in code view when selected text is the first in the area (insText empty)
		var range = document.selection.createRange();
		var insText = range.text;

		if (startTag == '[url]') {
			var returnValues = tinyMCE.handleURLTag(insText, startTag);
			insText = returnValues[0];
			startTag = returnValues[1];
			if (startTag == '') return;
		}
		else if (startTag == '[img]') {
			if (insText == '') insText = prompt(language['image.insert'], '');
			if (insText == null || insText == '') return;
		}
		// Handle BBCode start tags with optional attribute
		else if (insText == '' && typeof(attribut) != "undefined" && attribut != '') {
			// real attribut
			var regex = new RegExp("(align|list|size|font|color" + tagRegex + ")");
			if (startTag.match(regex)) {
				startTag = startTag.replace(/\[(\w+)\]/, '[$1=' + attribut + ']');
			}
			// Value between tags
			else insText = attribut;
		}
		// Smiley
		else if (insText != '' && startTag == '' && attribut != '') {
			insText = attribut;
		}	
		else if (startTag != '' && attribut != '') {
			startTag = startTag.replace(/\[(\w+)\]/, '[$1=' + attribut + ']');
		}
		
		// Inserts tagged text or only tags
		range.text = startTag + insText + endTag;

		// Moves cursor
		range = document.selection.createRange();
		if (insText.length == 0) {
			range.move('character', -endTag.length);
			range.select();
		} 
		else if (startTag != '') {
			range.moveStart('character', 0);      
			range.moveEnd('character', startTag.length + insText.length + endTag.length);      
		}
		
	}
	/* Browsers which don't support any of the above selection methods */
	else {
		var pos = textarea.value.length;
		// Insert code
		var insText = '';
		
		// Normal tag
		if (startTag != '') {
			insText = prompt(language['insertText']);
			// insert attribut
			if (attribut != '') {
				startTag = startTag.replace(/\[(\w+)\]/, '[$1=' + attribut + ']');
			}
		}
		// Handles smileys
		else if (startTag == '' && attribut != '') {
			insText = attribut;
		}	
		
		if (typeof(insText) == 'undefined') insText = '';
		textarea.value = textarea.value.substr(0, pos) + startTag + insText + endTag;
	}
	
}

TinyMCE_Engine.prototype.setSelectionPos = function(textareaID, startPos, endPos, scrollX, scrollY) {
	
	var textarea = document.getElementById(textareaID);
	
	textarea.focus(); // TODO: Which browser needs this? 
	textarea.selectionStart = startPos;
	textarea.selectionEnd = endPos;
	
	// Safari 3 (Mac and Win) looses focus in code view when inserting BBCodes. this is an ugly workaround to get the focus back. a simple blur doesn`t work
	if (tinyMCE.isSafari3) {
		// Toggles focus
		var otherElement = document.forms[0][0];
		if (otherElement) otherElement.focus();
		textarea.focus();
	}
	textarea.scrollLeft = scrollX;
	textarea.scrollTop = scrollY;
}

	
TinyMCE_Engine.prototype.handleURLTag = function(insText, startTag) {
	// Text is selected. prompt for URL 
	if (insText != '') {
		var url = prompt(language['link.insert.url.optional'], '');
		
		// Cancel. no link
		if (url == null) return new Array('', '');
		
		if (url != '') startTag = "[url='" + url + "']";
	}
	// No selection (prompt for link name use it as insText and attribut for tag)
	else {
		insText = prompt(language['link.insert.name'], '');
		
		// Cancel
		if (insText == null) return new Array('','');
		
		var url = prompt(language['link.insert.url'], '');
		
		// Cancel
		if (url == null) return new Array('','');
		
		if (url == '') return new Array('', '');
		else if (insText == '') insText = url;
		else startTag = "[url='" + url + "']";
	}
	return new Array(insText, startTag);
}

//------------------- END SIMPLE AREA -----------------------------------\\

/* Inserts a smiley */
TinyMCE_Engine.prototype.insertSmiley = function(url, title, code) {
	if (!tinyMCE.isSimpleTextarea) {
		var inst = tinyMCE.selectedInstance;
		if (inst.editorIsActive) this.insertImage(inst.editorId, url, code, title);
		else tinyMCE.simpleExecCommand('smiley', inst.editorId+'_codeview', ' ' + code + ' ');
	}
	else tinyMCE.simpleExecCommand('smiley', 'text', ' ' + code + ' ');
};

/* Inserts an attachment */
TinyMCE_Engine.prototype.insertAttachment = function (attachmentID) {
	if (!tinyMCE.isSimpleTextarea) {
		var inst = tinyMCE.selectedInstance;
		if (inst.editorIsActive) {
			if (tinyMCE.isMSIE && !tinyMCE.isOpera)	tinyMCE.selectedInstance.getWin().focus();
			var selectedText = '';
			// IE crashes if double inserted attachment: check the function "getSelectedHTML()"
			if (!tinyMCE.isMSIE || tinyMCE.isOpera) selectedText = inst.selection.getSelectedHTML();
			var tag = '';
			if (selectedText != "undefined" && selectedText != '') tag = '[attach=' + attachmentID + ']' + selectedText + '[/attach]';				
			else tag = '[attach=' + attachmentID + '][/attach]';
			tag += '<span id="attachTagInserted"> </span>';
			inst.execCommand("mceInsertContent", false, tag);	
						
			// Places cursor behind ending tag
			var cursorPositionElement = inst.getDoc().getElementById('attachTagInserted');
			var emtpyTextNode = inst.getDoc().createTextNode('');
			var parentElement = cursorPositionElement.parentNode;
			parentElement.replaceChild(emtpyTextNode, cursorPositionElement)			
		}
		else tinyMCE.simpleExecCommand('attach', inst.editorId + '_codeview', attachmentID);
	}
	else tinyMCE.simpleExecCommand('attach', 'text', attachmentID);
}

/* Inserts text */
/* IE 7 inserts text at beginning of editor when inserting via "onclick", not inside an input field 
works: <input onclick="insertText('text');" .....>
works: <a href="javascript:insertText('text');" ...>
doesn`t work: <div onclick="insertText('text');" .....> ("doesn't work" means: it will be inserted but always on first position in WYSIWYG editor)
*/
TinyMCE_Engine.prototype.insertText = function(text) {
	if (!tinyMCE.isSimpleTextarea) {
		var inst = tinyMCE.selectedInstance;
		if (inst.editorIsActive) {
			if (tinyMCE.isMSIE && !tinyMCE.isOpera)	tinyMCE.selectedInstance.getWin().focus();
			
			text += '<span id="textInserted"> </span>';
			inst.execCommand("mceInsertContent", false, text);	
						
			// Place cursor behind ending tag
			var cursorPositionElement = inst.getDoc().getElementById('textInserted');
			var emtpyTextNode = inst.getDoc().createTextNode('');
			var parentElement = cursorPositionElement.parentNode;
			parentElement.replaceChild(emtpyTextNode, cursorPositionElement);
			tinyMCE.selectedInstance.getWin().focus();
		}
		else tinyMCE.simpleExecCommand('mceText', inst.editorId + '_codeview', text);
	}
	else tinyMCE.simpleExecCommand('mceText', 'text', text);
}

TinyMCE_Engine.prototype.insertBBCodes = function(text) {
	if (!tinyMCE.isSimpleTextarea) {
		var inst = tinyMCE.selectedInstance;
		if (inst.editorIsActive) {
			tinyMCE.execInstanceCommand(inst.editorId, "mceInsertContent", false, tinyMCE.bbCodeToHTML(text));
		}
		else tinyMCE.simpleExecCommand('mceText', inst.editorId + '_codeview', text);
	}
	else tinyMCE.simpleExecCommand('mceText', 'text', text);
}

/* Inserts an image */
TinyMCE_Engine.prototype.insertImage = function(editorID, src, alt, title) {
	if (src == null || src == "") return;
	if (alt == null || typeof(alt) == 'undefined') alt = src.replace(/.*\//g, '');
	if (title == null || typeof(title) == 'undefined') title = alt;
	if (tinyMCE.isMSIE && !tinyMCE.isOpera && !src.match(/^http/)) src = this.documentBasePath + "/" + src;  
	var html = '&nbsp;<img src="' + src + '" wcf_src="' + src + '" alt="' + alt + '" title="' + title + '"  />&nbsp;';
	tinyMCE.execInstanceCommand(editorID, "mceInsertContent", false, html);
};

TinyMCE_Engine.prototype.addEvent = function(object, eventName, handler) {
	if (object.attachEvent) object.attachEvent("on" + eventName, handler);
	else object.addEventListener(eventName, handler, false);
};
TinyMCE_Engine.prototype.setInnerHTML = function(e, h) {
	var i, nl, n;
	if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
		// Since IE handles invalid HTML better than valid XHTML we
		// need to make some things invalid. <hr /> gets converted to <hr>.
		h = h.replace(/\s\/>/g, '>');

		// Since IE auto generates emtpy P tags sometimes we have to tell it to keep the real ones
		h = h.replace(/<p([^>]*)>\u00A0?<\/p>/gi, '<p$1 mce_keep="true">&nbsp;</p>'); // Keep empty paragraphs
		h = h.replace(/<p([^>]*)>\s*&nbsp;\s*<\/p>/gi, '<p$1 mce_keep="true">&nbsp;</p>'); // Keep empty paragraphs
		h = h.replace(/<p([^>]*)>\s+<\/p>/gi, '<p$1 mce_keep="true">&nbsp;</p>'); // Keep empty paragraphs

		// Removes first comment
		e.innerHTML = tinyMCE.uniqueTag + h;
		e.firstChild.removeNode(true);

		// Removes weird auto generated empty paragraphs unless they're supposed to be there
		nl = e.getElementsByTagName("p");
		for (i=nl.length-1; i>=0; i--) {
			n = nl[i];

			if (n.nodeName == 'P' && !n.hasChildNodes() && !n.mce_keep) {
				n.parentNode.removeChild(n);
			}
		}
	} else e.innerHTML = h;
};


TinyMCE_Engine.prototype.selectNodes = function(n, f, a) {
	var i;
	if (!a) a = new Array();
	if (f(n)) a[a.length] = n;
	if (n.hasChildNodes()) {
		for (i = 0; i < n.childNodes.length; i++) {
			tinyMCE.selectNodes(n.childNodes[i], f, a);
		}
	}
	return a;
};
TinyMCE_Engine.prototype.addEventHandlers = function(inst) {
	var doc = inst.getDoc();
	inst.switchSettings();
	
	if (tinyMCE.isMSIE) {
		tinyMCE.addEvent(doc, "keypress", TinyMCE_Engine.prototype._eventPatch);
		tinyMCE.addEvent(doc, "keyup", TinyMCE_Engine.prototype._eventPatch);
		tinyMCE.addEvent(doc, "keydown", TinyMCE_Engine.prototype._eventPatch);
		tinyMCE.addEvent(doc, "mouseup", TinyMCE_Engine.prototype._eventPatch);
		tinyMCE.addEvent(doc, "mousedown", TinyMCE_Engine.prototype._eventPatch);
		tinyMCE.addEvent(doc, "click", TinyMCE_Engine.prototype._eventPatch);
		
	} else {
		tinyMCE.addEvent(doc, "keypress", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "keydown", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "keyup", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "click", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "mouseup", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "mousedown", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "focus", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "blur", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "resize", tinyMCE.handleEvent);
		eval('try { doc.designMode = "On"; } catch(e) {}'); 
	}
	window.onresize = tinyMCE.resizeIframe;
};
/* Resizes iframe if user resizes the window */
TinyMCE_Engine.prototype.resizeIframe = function() {
	for (n in tinyMCE.instances) {
		inst = tinyMCE.instances[n];
		if (!tinyMCE.isInstance(inst)) continue;
		inst.iframeElement.style.width = inst.theTextarea.style.width = inst.iframeElement.parentNode.style.width;
	}
}


TinyMCE_Engine.prototype.decodeHTMLEntities = function(text) {
	text = text.replace(/&gt;/g, '>');
	text = text.replace(/&lt;/g, '<');
	text = text.replace(/&nbsp;/g, ' ');
	text = text.replace(/&amp;/g, '&');
	return text;
};
TinyMCE_Engine.prototype.encodeHTMLEntities = function(text) {
	text = text.replace(/&/g, '&amp;');
	text = text.replace(/</g, '&lt;');
	text = text.replace(/>/g, '&gt;');
	return text;
};

TinyMCE_Engine.prototype.getViewPort = function(w) {
	var d = w.document, m = d.compatMode == 'CSS1Compat', b = d.body, de = d.documentElement;

	return {
		left : w.pageXOffset || (m ? de.scrollLeft : b.scrollLeft),
		top : w.pageYOffset || (m ? de.scrollTop : b.scrollTop),
		width : w.innerWidth || (m ? de.clientWidth : b.clientWidth),
		height : w.innerHeight || (m ? de.clientHeight : b.clientHeight)
	};
};

TinyMCE_Engine.prototype.getAbsPosition = function(n, cn) {
	var l = 0, t = 0;
	while (n && n != cn) {
		l += n.offsetLeft;
		t += n.offsetTop;
		n = n.offsetParent;
	}
	return {absLeft : l, absTop : t};
};



function TinyMCE_Selection(editorInstance) {
	this.instance = editorInstance;
};
TinyMCE_Selection.prototype = {
	getSelectedHTML : function() {
		var element, range = this.getRng(), h;
		// tinyMCE 208 changes

		element = document.createElement("body");
		if (range.cloneContents) {
			var fragment = range.cloneContents();
			// Safari problem: if the range is empty, null is returned instead of an empty documentFragment
			if (fragment == null) fragment = this.instance.getDoc().createDocumentFragment();
			element.appendChild(fragment);
		}
		else if (range.item) element.innerHTML = range.item(0).outerHTML;
		else if (range.htmlText != undefined) element.innerHTML = range.htmlText;
		else if (range.toString) element.innerHTML = range.toString();
		else return '';
		var text = element.innerHTML;
		
		// IE inserts <p>&nbsp;</p>
		if (tinyMCE.isMSIE) {
			text = text.replace(/^<p>(.*)<\/p>$/i, '$1');
			text = text.replace(/^&nbsp;$/, '');
			if (tinyMCE.isOpera) {
				// Opera range sometimes gets the whole body as content
				text = text.replace(/^<body(.|\s)*$/i, '');
			}
		}
		
		return text;
	},
	getSelectedText : function() {
		var inst = this.instance;
		var d, r, s, t;
	
		if (tinyMCE.isMSIE) {
			d = inst.getDoc();
	
			if (d.selection.type == "Text") {
				r = d.selection.createRange();
				t = r.text;
			} else	t = '';
		} else {
			s = this.getSel();
			if (s && s.toString) t = s.toString();
			else	t = '';
		}
		if (typeof(t) == "undefined") t = s;
		return t;
	},
	getSel : function() {
		var inst = this.instance;
		
		var active_document = null;
		if (inst.editorIsActive) {
			if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
				return inst.getDoc().selection;
			}
			else {
				return inst.contentWindow.getSelection();
			}
		}
		// IE 
		else if (typeof(document.selection) != 'undefined' && !tinyMCE.isOpera){
			return document.selection;
		}
		// Gecko & Opera
		else if (window.getSelection) {
			return window.getSelection();
		}
	},
	getRng : function() {
		var sel = this.getSel();

		if (sel == null) return null;
		
		if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
			return sel.createRange();
		}
		
		// Opera knows getRangeAt and getSelection but in this case it likes 
		// the getSelection method.	
		if ((tinyMCE.isSafari || tinyMCE.isOpera) && !sel.getRangeAt) { 
			return '' + window.getSelection();
		}
		if (sel.rangeCount > 0) return sel.getRangeAt(0);
		return null;
	},
	selectNode : function(node, collapse, select_text_node, to_start) {
		var inst = this.instance, sel, rng, nodes;

		if (!node) return;
		if (typeof(collapse) == "undefined") collapse = true;
		if (typeof(select_text_node) == "undefined") select_text_node = false;
		if (typeof(to_start) == "undefined") to_start = true;
		if (tinyMCE.isMSIE && !tinyMCE.isOpera) { // in tinymce opera use this block too (not the elseblock)
			rng = inst.getBody().createTextRange();

			try {
				rng.moveToElementText(node);

				if (collapse) rng.collapse(to_start);
				rng.select();
			} catch (e) {
				// Throws illegal argument in IE sometimes
			}
		} else {
			sel = this.getSel();

			if (!sel) return;
			if (tinyMCE.isSafari) {
				sel.setBaseAndExtent(node, 0, node, node.innerText.length);

				if (collapse) {
					if (to_start) sel.collapseToStart();
					else sel.collapseToEnd();
				}
				this.scrollToNode(node);
				return;
			}

			rng = inst.getDoc().createRange();

			if (select_text_node) {
				// Finds first text node in tree
				nodes = tinyMCE.getNodeTree(node, new Array(), 3);
				if (nodes.length > 0) rng.selectNodeContents(nodes[0]);
				else rng.selectNodeContents(node);
			} else	rng.selectNode(node);

			if (collapse) {
				// Special treatment of text node collapse
				if (!to_start && node.nodeType == 3) {
					rng.setStart(node, node.nodeValue.length);
					rng.setEnd(node, node.nodeValue.length);
				} else	rng.collapse(to_start);
			}

			sel.removeAllRanges();
			sel.addRange(rng);
		}

		this.scrollToNode(node);

		// Sets selected element
		tinyMCE.selectedElement = null;
		if (node.nodeType == 1) tinyMCE.selectedElement = node;
	},
	scrollToNode : function(node) {
		var inst = this.instance;
		var w = inst.getWin();
		var vp = inst.getViewPort();
		var pos = tinyMCE.getAbsPosition(node);
		
		// Only scroll if out of visible area
		if (pos.absLeft < vp.left || pos.absLeft > vp.left + vp.width || pos.absTop < vp.top || pos.absTop > vp.top + (vp.height-25)) {
			w.scrollTo(pos.absLeft, pos.absTop - vp.height + 25);
		}
	},

	// tinyMCE 208 changes	
	getBookmark : function(simple) {
		var inst = this.instance;
		var rng = this.getRng();
		var doc = inst.getDoc();
		var body = inst.getBody();
		var sp, le, s, e, nl, i, si, ei, w;
		var trng, sx, sy, xx = -999999999;
		var viewPort = inst.getViewPort();
		
		sx = viewPort.left;
		sy = viewPort.top;

		if (tinyMCE.isSafari || tinyMCE.isGecko) {
			return {rng : rng, scrollX : sx, scrollY : sy};
		}
		
		if (tinyMCE.isMSIE) {
			if (rng.item) {
				e = rng.item(0);

				// New change. check IE 
				//nl = doc.getElementsByTagName(e.nodeName);
				nl = body.getElementsByTagName(e.nodeName);

				for (i = 0; i < nl.length; i++) {
					if (e == nl[i]) {
						sp = i;
						break;
					}
				}

				return {
					tag : e.nodeName,
					index : sp,
					scrollX : sx,
					scrollY : sy
				};
			} else if (rng.duplicate) {
				trng = rng.duplicate();
				trng.collapse(true);
				sp = Math.abs(trng.move('character', xx));

				trng = rng.duplicate();
				trng.collapse(false);
				le = Math.abs(trng.move('character', xx)) - sp;

				return {
					start : sp,
					length : le,
					scrollX : sx,
					scrollY : sy
				};
			}
		}
		return null;
	},
	moveToBookmark : function(bookmark) {
		var rng, nl, i, sd;
		var inst = this.instance;
		var doc = inst.getDoc();
		var win = inst.getWin();
		var sel = this.getSel();
		var body = inst.getBody();

		if (!bookmark) return false;

		if (tinyMCE.isSafari) {
			// tinyMCE 208 changes			
			sel.setBaseAndExtent(bookmark.rng.startContainer, bookmark.rng.startOffset, bookmark.rng.endContainer, bookmark.rng.endOffset);
			return true;
		}

		if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
			if (bookmark.rng) {
				try {
					bookmark.rng.select();
				}
				catch (e) {}
				return true;
			}
			win.focus();
			if (bookmark.tag) {
				rng = body.createControlRange();
				// tinyMCE 208 changes (body instead of doc)
				nl = body.getElementsByTagName(bookmark.tag);
				if (nl.length > bookmark.index) {
					try {
						rng.addElement(nl[bookmark.index]);
					} catch (ex) {
						// Might be thrown if the node no longer exists
					}
				}
			} else {
				try {
					// Incorrect bookmark
					if (bookmark.start < 0) return true;
					
					rng = inst.getSel().createRange();
					rng.moveToElementText(inst.getBody());
					rng.collapse(true);
					rng.moveStart('character', bookmark.start);
					rng.moveEnd('character', bookmark.length);
				}
				catch (e) {return true;}
			}
			rng.select();
			win.scrollTo(bookmark.scrollX, bookmark.scrollY);
			return true;
		}

		if (tinyMCE.isGecko || tinyMCE.isOpera) {
			if (!sel) return false;
			
			if (bookmark.rng) {
				sel.removeAllRanges();
				sel.addRange(bookmark.rng);
			}

			if (bookmark.start != -1 && bookmark.end != -1) {
				try {
					sd = this._getTextPos(body, bookmark.start, bookmark.end);

					rng = doc.createRange();
					rng.setStart(sd.startNode, sd.startOffset);
					rng.setEnd(sd.endNode, sd.endOffset);
					sel.removeAllRanges();
					sel.addRange(rng);
					win.focus();
				} catch (ex) {
					// Ignore
				}
			}

			win.scrollTo(bookmark.scrollX, bookmark.scrollY);
			return true;
		}
		

		return false;
	},
	
	_getPosText : function(r, sn, en) {
		var w = document.createTreeWalker(r, NodeFilter.SHOW_TEXT, null, false), n, p = 0, d = {};

		while ((n = w.nextNode()) != null) {
			if (n == sn) d.start = p;

			if (n == en) {
				d.end = p;
				return d;
			}
			p += n.nodeValue ? n.nodeValue.length : 0;
		}
		return null;
	},
	
	_getTextPos : function(r, sp, ep) {
		var w = document.createTreeWalker(r, NodeFilter.SHOW_TEXT, null, false), n, p = 0, d = {};

		while ((n = w.nextNode()) != null) {
			p += n.nodeValue ? n.nodeValue.length : 0;
			if (p >= sp && !d.startNode) {
				d.startNode = n;
				d.startOffset = sp - (p - n.nodeValue.length);
			}
			if (p >= ep) {
				d.endNode = n;
				d.endOffset = ep - (p - n.nodeValue.length);
				return d;
			}
		}
		return null;
	},
	
	
	getFocusElement : function() {
		var inst = this.instance;

		if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
			var doc = inst.getDoc();
			var rng = doc.selection.createRange();
			var elm = rng.item ? rng.item(0) : rng.parentElement();
		} else {
			if (inst.isDisabled()) return inst.getBody();
			var sel = this.getSel();
			var rng = this.getRng();

			if (!sel || !rng) {
				return null; 
			}

			var elm = rng.commonAncestorContainer;

			// Handles selecting images or other control like elements such as anchors
			if (!rng.collapsed) {
				// Is selection small
				if (rng.startContainer == rng.endContainer) {
					if (rng.startOffset - rng.endOffset < 2) {
						if (rng.startContainer.hasChildNodes())
							elm = rng.startContainer.childNodes[rng.startOffset];
					}
				}
			}
			// Gets the parent element of the node
			elm = tinyMCE.getParentElement(elm);
		}
		return elm;
	}
};
function TinyMCE_UndoRedo(inst) {
	this.instance = inst;
	this.undoLevels = new Array();
	this.undoIndex = 0;
	this.typingUndoIndex = -1;
	this.undoRedo = true;
};
TinyMCE_UndoRedo.prototype = {
	add : function(l) {
		var b;

		if (l) {
			this.undoLevels[this.undoLevels.length] = l;
			return true;
		}

		var inst = this.instance;

		if (this.typingUndoIndex != -1) {
			this.undoIndex = this.typingUndoIndex;
		}

		var newHTML = tinyMCE.trim(inst.getBody().innerHTML);
		if (this.undoLevels[this.undoIndex] && newHTML != this.undoLevels[this.undoIndex].content) {
			tinyMCE.dispatchCallback(inst, 'onchange_callback', 'onChange', inst);

			// Time to compress
			var customUndoLevels = tinyMCE.settings['custom_undo_redo_levels'];
			if (customUndoLevels != -1 && this.undoLevels.length > customUndoLevels) {
				for (var i = 0; i < this.undoLevels.length-1; i++) {
					this.undoLevels[i] = this.undoLevels[i+1];
				}
				this.undoLevels.length--;
				this.undoIndex--;
			}
			b = inst.undoBookmark;
			if (!b) 	b = inst.selection.getBookmark();
			this.undoIndex++;
			this.undoLevels[this.undoIndex] = {
				content : newHTML,
				bookmark : b
			};
			this.undoLevels.length = this.undoIndex + 1;
			return true;
		}
		return false;
	},

	undo : function() {
		var inst = this.instance;

		// Does undo
		if (this.undoIndex > 0) {
			this.undoIndex--;
			tinyMCE.setInnerHTML(inst.getBody(), this.undoLevels[this.undoIndex].content);
			inst.repaint();
			if (inst.settings.custom_undo_redo_restore_selection) {
				inst.selection.moveToBookmark(this.undoLevels[this.undoIndex].bookmark);
			}
		}
	},

	redo : function() {
		var inst = this.instance;

		tinyMCE.execCommand("mceEndTyping");

		if (this.undoIndex < (this.undoLevels.length-1)) {
			this.undoIndex++;
			tinyMCE.setInnerHTML(inst.getBody(), this.undoLevels[this.undoIndex].content);
			inst.repaint();
			if (inst.settings.custom_undo_redo_restore_selection) {
				inst.selection.moveToBookmark(this.undoLevels[this.undoIndex].bookmark);
			}
		}

		tinyMCE.triggerNodeChange();
	}
};

TinyMCE_Engine.prototype.insertAfter = function(n, r){
	if (r.nextSibling) r.parentNode.insertBefore(n, r.nextSibling);
	else r.parentNode.appendChild(n);
};

TinyMCE_Engine.prototype.getParentElement = function(node, names, attrib_name, attrib_value) {
	if (typeof(names) == "undefined") {
		if (node.nodeType == 1) return node;

		// Finds parent node that is an element
		while ((node = node.parentNode) != null && node.nodeType != 1) ;
		return node;
	}

	if (node == null) return null;
	var namesAr = names.toUpperCase().split(',');

	do {
		for (var i = 0; i < namesAr.length; i++) {
			if (node.nodeName == namesAr[i] || names == "*") {
				if (typeof(attrib_name) == "undefined")
					return node;
				else if (node.getAttribute(attrib_name)) {
					if (typeof(attrib_value) == "undefined") {
						if (node.getAttribute(attrib_name) != "")
							return node;
					} else if (node.getAttribute(attrib_name) == attrib_value)
						return node;
				}
			}
		}
	} while ((node = node.parentNode) != null);
	return null;
};
TinyMCE_Engine.prototype.switchClass = function(ei, c) {
	var e;

	if (tinyMCE.switchClassCache[ei]) e = tinyMCE.switchClassCache[ei];
	else e = tinyMCE.switchClassCache[ei] = document.getElementById(ei);
	if (e) {
		// Keeps tile mode
		if (tinyMCE.settings.button_tile_map && e.className && e.className.indexOf('mceTiledButton') == 0) {
			c = 'mceTiledButton ' + c;
		}
		e.className = c;
	}
};
TinyMCE_Engine.prototype.addSelectAccessibility = function(e, s, w) {
	// Adds event handlers 
	if (!s._isAccessible) {
		s.onkeydown = tinyMCE.accessibleEventHandler;
		s.onblur = tinyMCE.accessibleEventHandler;
		s._isAccessible = true;
		s._win = w;
	}
	return false;
};
TinyMCE_Engine.prototype.accessibleEventHandler = function(e) {
	var win = this._win;
	e = tinyMCE.isMSIE ? win.event : e;
	var elm = tinyMCE.isMSIE ? e.srcElement : e.target;

	// Unpiggyback onchange on blur
	if (e.type == "blur") {
		if (elm.oldonchange) {
			elm.onchange = elm.oldonchange;
			elm.oldonchange = null;
		}
		return true;
	}

	// Piggyback onchange
	if (elm.nodeName == "SELECT" && !elm.oldonchange) {
		elm.oldonchange = elm.onchange;
		elm.onchange = null;
	}

	// Executes onchange and remove piggyback
	if (e.keyCode == 13 || e.keyCode == 32) {
		elm.onchange = elm.oldonchange;
		elm.onchange();
		elm.oldonchange = null;

		tinyMCE.cancelEvent(e);
		return false;
	}
	return true;
};
TinyMCE_Engine.prototype.setAttrib = function(element, name, value) {
	if (typeof(value) == "number" && value != null) value = "" + value;
	if (name == "style") element.style.cssText = value;
	if (name == "class") element.className = value;
	if (value != null && value != "" && value != -1) element.setAttribute(name, value);
	else element.removeAttribute(name);
};
TinyMCE_Engine.prototype.getElementsByAttributeValue = function(n, e, a, v) {
	var i, nl = n.getElementsByTagName(e), o = new Array();
	for (i = 0; i < nl.length; i++) {
		if (unescape(tinyMCE.getAttrib(nl[i], a)).indexOf(v) != -1) {
			o[o.length] = nl[i];
		}
	}
	return o;
};
TinyMCE_Engine.prototype.getAttrib = function(elm, name, default_value) {
	if (typeof(default_value) == "undefined") default_value = "";

	// Not an element
	if (!elm || elm.nodeType != 1) return default_value;
	var v = elm.getAttribute(name);

	// Try className for class attribute
	if (name == "class" && !v) v = elm.className;

	// Workaround for an issue with Firefox 1.5rc2+
	if (tinyMCE.isGecko && name == "src" && elm.src != null && elm.src != "") v = elm.src;

	// Workaround for an issue with Firefox 1.5rc2+
	if (tinyMCE.isGecko && name == "href" && elm.href != null && elm.href != "") v = elm.href;
	if (name == "http-equiv" && tinyMCE.isMSIE) v = elm.httpEquiv;
	if (name == "style" && !tinyMCE.isOpera) v = elm.style.cssText;
	return (v && v != "") ? v : default_value;
};

TinyMCE_Engine.prototype._eventPatch = function(editor_id) {
	var n, inst, win, e;

	// Removes odd, error
	if (typeof(tinyMCE) == "undefined") return true;

	try {
		// Tries selected instance first
		if (tinyMCE.selectedInstance) {
			win = tinyMCE.selectedInstance.getWin();

			if (win && win.event) {
				e = win.event;

				if (!e.target) {
					e.target = e.srcElement;
				}

				TinyMCE_Engine.prototype.handleEvent(e);
				return;
			}
		}

		// Searches for it
		for (n in tinyMCE.instances) {
			inst = tinyMCE.instances[n];

			if (!tinyMCE.isInstance(inst)) continue;

			tinyMCE.selectedInstance = inst;
			win = inst.getWin();

			if (win && win.event) {
				e = win.event;

				if (!e.target) e.target = e.srcElement;

				TinyMCE_Engine.prototype.handleEvent(e);
				return;
			}
		}
	} 
	catch (ex) {}
};
TinyMCE_Engine.prototype.unloadHandler = function() {
	tinyMCE.triggerSave(true, true);
};
TinyMCE_Engine.prototype.cancelEvent = function(e) {
	if (tinyMCE.isMSIE) {
		e.returnValue = false;
		e.cancelBubble = true;
	} else	e.preventDefault();
};


/* Parsing BBCode to HTML is simple parsing because each BBCode tag (start and end) has its exact opponent. */
TinyMCE_Engine.prototype.bbCodeToHTML = function (bbCode, switchView) { 
	var html = bbCode;

	if (typeof(switchView) == 'undefined') switchView = false;

	// Extracts code blocks and inserts unique string to reinsert it later unparsed
	var codeBlocks = new Object();
	
	html = tinyMCE.extractCodeBlocks(html, codeBlocks, false, false, switchView);
	
	// 3 second parse-all-smiley-limit 
	var maxTime = 3000; 
	var startTime = new Date().getTime();

	// Smiley code to <img> 
	for (var smileyCode in smilies) {
		if  ((new Date().getTime() - startTime) < maxTime)	{
			
			// Builds smiley regex but dont get smiley codes in alt attributes
			var smileyRegex = new RegExp('(^|\\s|>|])' + smileyCode.pregQuote() + '(?![^\\[]*\\])', 'g');
			
			// Sets image URL
			var url = smilies[smileyCode][0];
			if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
				url = tinyMCE.settings['base_href'] + url;
			}
			html = html.replace(smileyRegex, '$1<img src="' + url + '" alt="' + smileyCode + '" />');
			
		}
		else break;
	}

	// Extracts smileys and inserts unique string to reinsert it later decoded
	var smileyBlocks = new Object();
	html = extractSmilies(html, smileyBlocks);
	// Generates HTML entities because in code view they could be written decoded

	if (switchView) html = tinyMCE.encodeHTMLEntities(html);
	
	// Erases \n in lists 
	// Disabled here because of this thread: http://beta.woltlab.de/index.php?page=Thread&postID=41021#post41021
	// wanted br in list (shift+enter) should be kept.
	//html = html.replace(/\[\*\]\n+/g, '[*]');
	//html = html.replace(/\[\*\]([^\[]+?)\n+/g, '[*]$1');
	
	// \n to <br> if not before block elements
	html = html.replace(/\n(?!\[\*\])/g, '<br>'); 

	// [b] to <b>
	html = html.replace(/\[(\/)?b\]/gi, '<$1b>');
	
	// [i] to <i>
	html = html.replace(/\[(\/)?i\]/gi, '<$1i>');
	
	// [u] to <u> 
	html = html.replace(/\[(\/)?u\]/gi, '<$1u>');
	
	// [s] to <strike> 
	html = html.replace(/\[(\/)?s\]/gi, '<$1strike>');
	
	// [font=arial] to <font face="arial">
	html = html.replace(/\[font=([^\]]+)\]/gi, function(thisMatch, family) {
		if (family.match(/^["']/)) return '<font face="' + family.replace(/^["'](.*)["']$/, '$1') + '">'
		return '<font face="' + family + '">'
	});

	// [color=#000000] to <font color="#000000">
	html = html.replace(/\[color=["']?([^"'\]]+)["']?\]/gi, '<font color="$1">');
	
	// [size=5] to <font size="5">
	html = html.replace(/\[size=["']?([^"'\]]+)["']?\]/gi, function(thisMatch, size) {
		// array for converting from "size" (1-7)  to pt (8-36)
		var fontPointSizes = new Array();
		fontPointSizes[8] = 1;
		fontPointSizes[10] = 2;
		fontPointSizes[12] = 3;
		fontPointSizes[14] = 4;
		fontPointSizes[18] = 5;
		fontPointSizes[24] = 6;
		fontPointSizes[36] = 7;
		
		return '<font size="' + fontPointSizes[size] + '">';
	
	}); 
	
	// [/font|/color|/size] to </font>
	html = html.replace(/\[\/(font|color|size)\]/gi, '</font>');
	
	// [align=left|center|right] to <div style="text-align:">
	html = html.replace(/\[align=["']?(\w+)["']?\]/gi, '<div style="text-align:$1">');
	html = html.replace(/\[CENTER\]/gi, '<div style="text-align:center">');
	
	// [/align] to </div>
	html = html.replace(/\[\/(align|CENTER)]/gi, '</div>');

	// [url... to <a href=...
	// TODO: Check what happens with line breaks in link text
	if (!this.isChrome5) {
		html = html.replace(/\[url(.*?)\[\/url\]/gi, function (thisMatch, between) {
			// url as link text
			if (between.indexOf(']') == 0) {
				var text = between.substr(1);
				var decodedText = tinyMCE.decodeHTMLEntities(text);
				return '<a href="' + decodedText + '" wcf_href="' + decodedText + '" >' + text + '</a>';
			}
			else if (between.indexOf('=') == 0 && between.match(/=["']([^"']+)["']\](.*)/)) {
				var href = RegExp.$1;
				var text = RegExp.$2;
				if (!text) text = href;
				var decodedHref = tinyMCE.decodeHTMLEntities(href);
				return '<a href="' + decodedHref + '" wcf_href="' + decodedHref + '">' + text + '</a>';
			}
			else if (between.indexOf('=') == 0 && between.match(/=(.+?)\](.*)/)) {
				var href = RegExp.$1;
				var text = RegExp.$2;
				if (!text) text = href;
				var decodedHref = tinyMCE.decodeHTMLEntities(href);
				return '<a href="' + decodedHref + '" wcf_href="' + decodedHref + '">' + text + '</a>';
			}
			
			return thisMatch;
		});
	}

	// [img]src[/img] to <img src="src" wcf_src="src" alt="wysiwyg image" />
	html = html.replace(/\[img\]([^\[]+)\[\/img\]/gi, '<img src="$1" wcf_src="$1" alt="wysiwyg image" />');

	// [img='src']src[/img] to <img src="src" wcf_src="src" alt="" />
	html = html.replace(/\[img=('([^']+)'|([^,\]]+))\](.|\n)*?\[\/img\]/gi, '<img src="$2$3" wcf_src="$2$3" alt="wysiwyg image" />');

	// [img='src',(right|left)]src[/img] to <img src="src" wcf_src="src" alt="wysiwyg image" style="float:(right|left)" />
	html = html.replace(/\[img=('([^']+)'|([^,\]]+))(,(right|left))?\](.|\n)*?\[\/img\]/gi, '<img src="$2$3" wcf_src="$2$3" alt="wysiwyg image" style="float:$5" />');

	// [img=src]src[/img] to <img src="src" wcf_src="src" alt="wysiwyg image" />
	//html = html.replace(/\[img=(?!("|'))([^\]]+)\](.|\n)*?\[\/img\]/gi, '<img src="$1" wcf_src="$1" alt="wysiwyg image" />');

	// Erases empty [*] 
	html = html.replace(/\[\*\][\s]*(\[(?:\*|\/list)\])/gi, '$1');

	// [*] sometext to <li> sometext </li>
	html = html.replace(/\[\*\]((.|\n)*?)\[\/list\]/gi, function(thisMatch, listEntries) {
		return '<li>' + listEntries.replace(/\[\*\]/gi, '</li><li>') + '</li>[/list]';
	});

	// Stores order of open list tags to detect later what kind of list has to be closed
	var openListTags = html.match(/\[list(=[^\]]+)?\]/gi);

	// [list=1] to <ol> 
	html = html.replace(/\[list='?1'?\]/gi, '<ol>');
	
	// [list=a] to <ol > 
	html = html.replace(/\[list='?a'?\]/gi, '<ol style="list-style-type:lower-latin">');
	
	// [list=(ol-types)] to <ol style="list-style-type:type"> 
	html = html.replace(/\[list='?(decimal|lower-roman|upper-roman|lower-greek|decimal-leading-zero|lower-latin|upper-latin|armenian|georgian)'?\]/gi, '<ol style="list-style-type:$1">');
	
	// [list=(ul-types)] to <ul style="list-style-type:type"> 
	html = html.replace(/\[list='?(circle|square|none|disc)'?\]/gi, '<ul style="list-style-type:$1">');
	
	// [list] to <ul>
	html = html.replace(/\[list\]/gi, '<ul>');

	// Handles same closing BBCode tags for lists to the HTML different tags.	
	// [/list] to </ul> or </ol>
	if (openListTags) {
		for (var i = 0; i < openListTags.length; i++) {
			openListTags[i].match(/\[list(='?([\w-]+)'?)?\]/);
			switch (RegExp.$2) {
				case 'circle':
				case 'square':
				case 'none':
				case 'disc':
				case '':
					html = html.replace(/\[\/list\]/i, '</ul>');
				break;
				case 'lower-roman':
				case 'upper-roman':
				case 'lower-greek':
				case 'upper-latin':
				case 'lower-latin':
				case 'decimal-leading-zero':
				case 'armenian':
				case 'georgian':
				case 'decimal':
					html = html.replace(/\[\/list\]/i, '</ol>');
				break;
				case '1':
				case 'a':
					// Due to WBB 2 compatibility [/list=(a|1)] is converted too
					html = html.replace(/\[\/list(=(a|1))?\]/i, '</ol>');
				break;
				default: 
					html = html.replace(/\[\/list\]/i, '</ol>');
			}
		}
	}
	
	// [quote] to <blockquote>
	if (!tinyMCE.isOpera) {
		// [quote] => <blockquote class="wysiwygQuote">
		html = html.replace(/\[quote\]/gi, '<br><blockquote class="wysiwygQuote container-4">');

		// [quote='[clan]name'] => <blockquote username="name" class="wysiwygQuote">
		html = html.replace(/\[quote='([^']+)'\]/gi, '<br><blockquote username="$1" class="wysiwygQuote container-4">');

		// [quote='[clan]name',url] => <blockquote username="name" linkhref="url" class="wysiwygQuote">
		html = html.replace(/\[quote='([^']+)',([^\]]+)\]/gi, '<br><blockquote username="$1" linkhref="$2" class="wysiwygQuote container-4">');

		// [quote=name,url] => <blockquote username="name" linkhref="url" class="wysiwygQuote">
		html = html.replace(/\[quote=([^\,\]]+),([^\]]+)\]/gi, '<br><blockquote username="$1" linkhref="$2" class="wysiwygQuote conainter-4">');

		// [quote=name] => <blockquote username="name" class="wysiwygQuote">
		html = html.replace(/\[quote=([^\]]+)\]/gi, '<br><blockquote username="$1" class="wysiwygQuote container-4">');

		// [/quote] => </blockquote>
		html = html.replace(/\[\/quote\]/gi, '</blockquote><br>');
	}

	// Replaces installed BBCodes [userdefined-code] to <html-counterpart>
	for (var bbCodeTag in extraBBCodes) {
		if (!extraBBCodes[bbCodeTag]['wysiwyg']) continue;
		var bbCodePregQuoted = bbCodeTag.pregQuote();
		
		// BBCode without attributes
		if (extraBBCodes[bbCodeTag]['attributes'].length <= 0) {
			var openBBRegex = new RegExp('\\[' + bbCodePregQuoted + '\\]', 'g');
			html = html.replace(openBBRegex, '<' + extraBBCodes[bbCodeTag]['htmlOpen'] + '>');
		}
		else {
			// Handle codes with attributes
			var attributes = extraBBCodes[bbCodeTag]['attributes'];
			var replaceTag = '<' + extraBBCodes[bbCodeTag]['htmlOpen'];
			var count = 1;
			for (var i in attributes) {
				var attributeName = attributes[i]['attributeHTML'].replace(/^\s*(\w+=)["']?([^"';]+);?["']?/, '$1')
				if (attributeName.toLowerCase() == 'style=') {
					var style = RegExp.$2;
					style.match(/([^:]+):/);
					var definition = RegExp.$1;					
					// Build html tag with placeholder: <div align="$1" style="$2" dummyattribut="$3">
					replaceTag += ' ' + attributeName + '"' + definition + ': $' + count + '"';
				}
				else {
					replaceTag += ' ' + attributeName + '"$' + count + '"';	
				}
				count++;
			}
			replaceTag += '>';
			var attributesString = '';
			for (var i = 1; i < count; i++) {
				if (attributesString == '') attributesString = '=';
				else attributesString += ',\\s*';
				attributesString += '(\\w+)';
			}
			var openBBRegex = new RegExp('\\[' + bbCodePregQuoted + attributesString + '\\]', 'g');
			html = html.replace(openBBRegex, replaceTag);
		}
		
		// Replaces closing [/userdefined] with </userdefined>
		if (extraBBCodes[bbCodeTag]['htmlClose'] != '') {
			var closeBBRegex = new RegExp('\\[\\/' + bbCodePregQuoted + '\\]', 'g');
			html = html.replace(closeBBRegex, '</' + extraBBCodes[bbCodeTag]['htmlClose'] + '>');
		}
	}
	
	// Reinserts smiley blocks
	for (var uniqueString in smileyBlocks) {
		html = html.replace(uniqueString, smileyBlocks[uniqueString]);
	}

	// Reinserts code blocks
	for (var uniqueString in codeBlocks) {
		var codeBlock = codeBlocks[uniqueString].replace(/\n/g, '<br>');
		codeBlock = codeBlock.replace(/\t/g, '&nbsp;&nbsp;&nbsp; ');// 
		html = html.replace(uniqueString, codeBlock);
	}
	return html;
};

/* Adds this preg_quote to string objects */
String.prototype.pregQuote = function() { 
	return this.replace(/([\^\$\.\*\+\?\=\!\:\|\\\/\(\)\[\]\{\}])/g,"\\$1");
};

/* This function takes code from textarea and cleans it up a little bit (i.e. \n[*]) */
TinyMCE_Engine.prototype.cleanBBCode = function (bbCode) {
	var code = bbCode;

	// Removes newlines and spaces before [/url] elements
	code = code.replace(/\s*\n*\s*\[\/url\]/g, '[/url]');
	return code;
}

/*
* HTML to BBCODE
*
*/
TinyMCE_Engine.prototype.htmlToBBCode = function (html) {
	var code = tinyMCE.cleanupHTML_(html);

	// Removes single <br> if nothing else is inside the iframe.
	// Firefox inserts this
	code = code.replace(/^\s*<br[^>]*>\s*$/gi, '');

	// Removes alt 0160
	code = code.replace(/\xA0/gi, ' ');

	// Deletes <br> before and after <blockquote>
	//code = code.replace(/<br>(\n|\r\n|\r|\n\r)?<blockquote((.|\s)*?)<\/blockquote>/gi, "<blockquote$2</blockquote>");
	code = code.replace(/<br>(\n|\r\n|\r|\n\r)?<blockquote/gi, "<blockquote");
	code = code.replace(/<\/blockquote>(\n|\r\n|\r|\n\r)?<br>/gi, "</blockquote>");

	// Turns <br> to \n 
	code = code.replace(/<br[^>]*>/gi, '\n');

	// Adds slash to avoid deletion of non-closable tags
	code = code.replace(/(<img [^>]+[^\/])>/gi, "$1 />");

	// Deletes empty tags
	// Safari uses emtpy divs to make line breaks
	// Opera uses empty paragraphs to make line breaks
	if (!tinyMCE.isSafari3 && !tinyMCE.isOpera) {
		code = code.replace(/(<[^\/]>|<[^\/][^>]*[^\/]>)\s*<\/[^>]*>/gi, '');
	}
	// Safari <div>\n</div> to \n
	else if (tinyMCE.isSafari3) {
		code = code.replace(/<div[^>]*?>(\n*?)<\/div>/gi, '$1');
	}
	else if (tinyMCE.isOpera) {
		code = code.replace(/<p[^>]*?>(\n*?)<\/p>/gi, '$1');
	}

	// Erases empty <li></li> elements (gecko does this automatic)
	if (tinyMCE.isMSIE && !tinyMCE.isOpera) {
		code = code.replace(/<li><\/li>/gi, '');
	}
	// Opera does not close empty <li>
	else if (tinyMCE.isOpera) {
		code = code.replace(/(<li>)+<li>/gi, '<li>');
	}
	
	// Removes newlines before list elements
	if (tinyMCE.isMSIE && !tinyMCE.isOpera)	 {
		code = code.replace(/\n*<(li[^>]*)>/gi, '<$1>');
	}

	// Removes newlines and spaces before </a> elements
	code = code.replace(/\s*\n*\s*<\/a>/g, '</a>');

	// Extracts code blocks and inserts unique strings to reinsert them later unparsed
	var codeBlocks = new Object();
	code = tinyMCE.extractCodeBlocks(code, codeBlocks, true);

	// Replaces smileys
	var uniqueSmilies = new Object();
	for (var smileyCode in smilies) {
		var smileyCount = 0;
		var smileyRegex = new RegExp('(<img([^>]+?|\\s+)alt=["\']?'+smileyCode.pregQuote()+'["\'\\s][^>]*?(\\salign="(left|center|right)")?.*?>)', 'gi');
		code = code.replace(smileyRegex, smileyCode);
	}

	// Splits the content 
	var nodes = code.split('<');
	var openTags = new Array();
	var k = 0;
	bbCode = code;
	code = '';
	if (nodes.length > 1) {
		// Array for converting from "size" (1-7)  to pt (8-36)
		var fontPointSizes = new Array();
		fontPointSizes[1] = 8;
		fontPointSizes[2] = 10;
		fontPointSizes[3] = 12;
		fontPointSizes[4] = 14;
		fontPointSizes[5] = 18;
		fontPointSizes[6] = 24;
		fontPointSizes[7] = 36;
		
		// Array for converting from "size" (x-small to -webkit-xxx-large) to pt (8-36) 
		var fontPointSizes_ = new Object();
		fontPointSizes_['x-small'] 		= 8;
		fontPointSizes_['small'] 		= 10;
		fontPointSizes_['medium'] 		= 12;
		fontPointSizes_['large'] 		= 14;
		fontPointSizes_['x-large'] 		= 18;
		fontPointSizes_['xx-large'] 		= 24;
		fontPointSizes_['-webkit-xxx-large'] 	= 36;
				
		var allowedHTMLOpenTags = new RegExp('^<(blockquote|span|div|font|strong|i|em|b|u|strike|p|ul|ol|li|a|img' + tinyMCE.extraHTMLOpenTags + ')(\\s+([^>]+?))?(\\s?\\/)?>', 'i');
		var allowedHTMLCloseTags = new RegExp('^<\\/(blockquote|span|div|font|strong|i|em|b|u|strike|p|ul|ol|li|a|img' + tinyMCE.extraHTMLOpenTags + ')>', 'i');

		// Handles each HTML tag
		for (var i = 0; i < nodes.length ; i++) {
			if (i > 0) nodes[i] = '<'+nodes[i];	
			var node = nodes[i];
			
			// Matches an open tag
			if (node.match(allowedHTMLOpenTags)) { 
				var tagName = RegExp.$1;													
				// Gets attributes				
				var matchedAttributes = new Array();
				if (RegExp.$3 != '') {
					var attributes = RegExp.$3;

					// Attributes with double quotes, single quotes and without quotes
					matchedAttributes = attributes.match(/(\w+)=".*?(?!\\)"/gi);
					var singleQuotetAttributes = attributes.match(/(\w+)='.*?(?!\\)'/gi);
					if (singleQuotetAttributes != null) {
						if (matchedAttributes == null) matchedAttributes = singleQuotetAttributes;
						else matchedAttributes = matchedAttributes.concat(singleQuotetAttributes);
					}
					var attributesWithoutQuotes = attributes.match(/(?:\s)*\w+=(?!["'])([^\s"']+)/gi);
					if (attributesWithoutQuotes != null) {
						if (matchedAttributes == null) {
							matchedAttributes = new Array();
						}
						for (var j = 0; j < attributesWithoutQuotes.length; j++) {
							matchedAttributes.push(attributesWithoutQuotes[j]);
						}					
					}
				}
				
				// Stores attributes in an array
				var attributes = new Object();
				if (matchedAttributes != null) {
					for (var j = 0; j < matchedAttributes.length; j++) {

						// Gets attribute names and values
						var attribute = matchedAttributes[j];
						var attributeName = attribute.substr(0, attribute.indexOf('='));
						var attributeValue = attribute.substr((attribute.indexOf('=') + 1));
						if (attributeValue.substr(0,1).match(/["']/)) {
							attributeValue = attributeValue.substr(1, (attributeValue.length - 2));
						}
						attributes[attributeName.toLowerCase().replace(/\s/g, '')] = attributeValue;
					}
				}
				
				// Builds BBCode start-tags
				var replaceTag = '';
				var tagName = tagName.toLowerCase();

				switch (tagName) {

					// Blockquote
					case 'blockquote':
						var username = typeof(attributes['username']) != 'undefined' ? '=\'' + attributes['username'] + '\'' : '';
						var linkhref = typeof(attributes['linkhref']) != 'undefined' ? ',' + attributes['linkhref'] : '';
						replaceTag = '[quote' + username + linkhref + ']';
					break;
					
					// Bold
					case 'b':
					case 'strong':
						if (attributes['style']) {
							replaceTag = '[b]';
							var bbStyle = tinyMCE.getBBCodeFromStyleAttribute(tagName, attributes, replaceTag);
							replaceTag = bbStyle[0];
						}
						else replaceTag = '[b]';
					break;
				
					// Italic
					case 'i':
					case 'em':
						if (attributes['style']) {
							replaceTag = '[i]';
							var bbStyle = tinyMCE.getBBCodeFromStyleAttribute(tagName, attributes, replaceTag);
							replaceTag = bbStyle[0];
						}
						else replaceTag = '[i]';
					break;
				
					// Underline
					case 'u':
						if (attributes['style']) {
							replaceTag = '[u]';
							var bbStyle = tinyMCE.getBBCodeFromStyleAttribute(tagName, attributes, replaceTag);
							replaceTag = bbStyle[0];
						}
						else replaceTag = '[u]';
					break;
					
					// Strikethrough
					case 'strike':
						if (attributes['style']) {
							replaceTag = '[s]';
							var bbStyle = tinyMCE.getBBCodeFromStyleAttribute(tagName, attributes, replaceTag);
							replaceTag = bbStyle[0];
						}
						else replaceTag = '[s]';
					break;
					
					// List
					case 'ol':
					case 'ul':
						var listType = '';
						if (attributes['style']) {
							var bbStyle = tinyMCE.getBBCodeFromStyleAttribute(tagName, attributes, replaceTag);
							replaceTag = bbStyle[0];
							listType = '[list=' + bbStyle[1] + ']';
						}
						else {
							listType = tagName == 'ol' ? '[list=1]' : '[list]';
						}	
											
						if (attributes['align']) {
							replaceTag += '[align=' + attributes['align'] + ']';
						}
						
						replaceTag += listType;
					break;
					
					// Font -color -face -size
					case 'font':
						if (matchedAttributes != null && matchedAttributes.length > 0) {
							for (var attributName in attributes) {
								switch (attributName) {
									case 'color':
										replaceTag += '[color=' + attributes['color'] + ']';
									break;
									case 'size':
										var size = fontPointSizes[attributes['size']];
										if (typeof(size) == "undefined") size = fontPointSizes_[attributes['size']];
										if (typeof(size) == "undefined") size = 10; 
										replaceTag += '[size=' + size + ']';
									break;
									case 'face':
										replaceTag += "[font='" + attributes['face'].replace(/'/g, '') + "']";
									break;
									case 'style':
										var bbStyle = tinyMCE.getBBCodeFromStyleAttribute(tagName, attributes, replaceTag);
										replaceTag += bbStyle[0];
									break;
								}
							}
						}
					break;
					
					// Image
					case 'img':
						if (attributes['src']) {
							if (attributes['align']) {
								replaceTag = '[align=' + attributes['align'] + '][img]';
							}
							else if (attributes['style']) {
								var bbStyle = tinyMCE.getBBCodeFromStyleAttribute(tagName, attributes, replaceTag);
								replaceTag = bbStyle[0] != '' ? bbStyle[0] : '[img]'; 
							}
							else replaceTag = '[img]';
						}
					break;
					
					// Align & Style values 
					case 'div':
					case 'span':
					case 'p':
					case 'a':
					case 'li':
						// Gecko creates divs or spans with style
						if (attributes['style']) {
							var bbStyle = tinyMCE.getBBCodeFromStyleAttribute(tagName, attributes, replaceTag);	
							replaceTag += bbStyle[0];
							if (tagName == 'li') {
								replaceTag = '[*]' + replaceTag;
							}
						}
						// Opera div with align and IE p with align
						else if (attributes['align']) {
							replaceTag = '[align=' + attributes['align'] + ']';
						}
						else if (tagName == 'li') {
							replaceTag = '[*]';
						}
						else if (tagName == 'a') {
							node.match(/[^>]+>((.|\n)*)/);
							// Link text is the same as the href attribute. use BBCode without attribute
							// 1. decodeUri was added to compare urls and browsers may have made encoded urls out of href attributes
							// 2. Escape was added because a normal % sign leads to an uri error
							// 3. Don't decode linkt text because link text with צהü will lead to an uri error 
							//if (decodeURI(escape(RegExp.$1)) == decodeURI(escape(attributes['wcf_href'])) || decodeURI(escape(RegExp.$1)) == decodeURI(escape(attributes['href']))) {
							if (RegExp.$1 == attributes['wcf_href'] || RegExp.$1 == attributes['href']) {
								replaceTag = '[url]';
							}
							// Link text differs. use attribute in bbcode tag
							else {
								// Don't decodeURI because if a "normal" % appears this function will lead to an error (try to insert into code [url=%]link[url] and switch to wysiwyg and back)
								//var href = attributes['wcf_href'] ? decodeURI(attributes['wcf_href']) : decodeURI(attributes['href']);
								var href = attributes['wcf_href'] ? attributes['wcf_href'] : attributes['href'];
								replaceTag = "[url='" + href + "']";
							}
						}
						// Ignores all other attributes not handled above
						// IE uses p as line breaks
						else if (tagName == 'p'){
							replaceTag = '[MSIE_newline_start]';
						}
						else if (tagName == 'div'){
							replaceTag = '[Safari_newline_start]';
						}
						// Unwanted tag. dont parse
						else {
							replaceTag = '';
						}
						
						// Handles extra BBCodes with HTML tags used from default too
						if (replaceTag == '' && tinyMCE.hasExtraBBcodes) {
							replaceTag = tinyMCE.getExtraBBCodeTag(tagName, node);
						}
						
					break;

					
					default:
						if (tinyMCE.hasExtraBBcodes) {
							replaceTag = tinyMCE.getExtraBBCodeTag(tagName, node);
						}
						else {
							// Removes not supported HTML tags 
							replaceTag = '';
						}
				}

				var nonClosableRegex = new RegExp('(br|img' + tinyMCE.nonClosableHTMLTags + ')', 'i');
				if (tagName.match(nonClosableRegex)) {
					// Replaces non closable HTML tags 
					nodes[i] = tinyMCE.closeMultipleBBCodeTags(replaceTag, nodes[i], true);
				}
				// Stores open tag in stack to be able to close them properly later
				else {
					// Now replace HTML with BBCode 
					nodes[i] = node.replace(/^<[^>]+>/, replaceTag);

					// Stores open tag bbcoded
					openTags[k] = replaceTag;
					k++;
				}
			}
			// Matches a closing tag 
			else if (nodes[i].match(allowedHTMLCloseTags)) {
				k--;
				
				// Gets last open node
				var lastOpenNode = openTags.pop();	

				// If an open tag is found that we don't support just erase closing tag too
				// or if it is a non closing BBCode tag like [*] 
				if (lastOpenNode == '' || (lastOpenNode != null && lastOpenNode.match(/(\[\*\]|\[MSIE_newline_start\]|\[Safari_newline_start\])/))) { 
					// Checks for [*] with styles inside
					if (RegExp.$1 == '[*]' && lastOpenNode.match(/\[\*\](.+)/)) {
						var openNodes = RegExp.$1.match(/(\[[^\]]+\])/g);
						openNodes = openNodes.reverse();
						var closingTags = '';
						for (var j = 0; j < openNodes.length; j++) {
							var closeTag = openNodes[j].replace(/^[^\[]*\[([a-z0-9\*]+)[^\]]*\](.|\n)*/i, "[/$1]");
							closingTags += closeTag; 						
						}
						
						// Replaces HTML with BBCode
						nodes[i] = node.replace(/^<([^>]+>)/, closingTags);
					}
					// Handles IE <p> newlines 
					else if (lastOpenNode.match(/\[MSIE_newline_start\]/)) {
						nodes[i] = node.replace(/^<([^>]+>)/, '[MSIE_newline_end]');
					}
					// Handles Safari <div> newlines 
					else if (lastOpenNode.match(/\[Safari_newline_start\]/)) {
						nodes[i] = node.replace(/^<([^>]+>)/, '');
					}
					else {
						// Removes closing node
						nodes[i] = node.replace(/^<[^>]+>/, '');
					}
				}
				else if (lastOpenNode != null){
					nodes[i] = tinyMCE.closeMultipleBBCodeTags(lastOpenNode, nodes[i], false);
				}
				// Removes closing tag if no tag is open.
				else {
					nodes[i] = nodes[i].replace(/<\/[^>]+>/, '');
				}
			}
		}
		// Builds complete string out of the split and replaced parts
		var bbCode = nodes.join("");
	}

	// Decodes entities
	bbCode = tinyMCE.decodeHTMLEntities(bbCode);	
	
	// Deletes multiple white spaces. 
	bbCode = bbCode.replace(/[\t ]{2,}/g, ' ');
	
	// Deletes whitespaces on line start (// TODO: ?WHY THIS?)
	bbCode = bbCode.replace(/\n /g, '\n');
	
	// Turns IE newlines to normal newlines (<p></p> to \n).
	bbCode = bbCode.replace(/\[MSIE_newline_end\]/g, '\n');
	bbCode = bbCode.replace(/\[MSIE_newline_start\]/g, '');

	// Turns Safari newlines to normal newlines (<div></div> to \n).
	bbCode = bbCode.replace(/\[Safari_newline_start\]/g, '\n');

	// Resinserts code blocks
	for (var uniqueString in codeBlocks) {
		bbCode = unescape(bbCode.replace(uniqueString, escape(codeBlocks[uniqueString])));
	}
	
	return bbCode;
};

TinyMCE_Engine.prototype.getBBCodeFromStyleAttribute = function (tagName, attributes, replaceTag) {
	var styleDefinitions = attributes['style'].match(/[^:]+:[^;]+;?/g);
	
	// BBCode attribute for list types
	var listType = '';
	for (var j = 0; j < styleDefinitions.length; j++) {
		styleDefinitions[j].match(/\s*([^:]+):\s*([^;]+)/);
		
		var styleProperty = RegExp.$1;
		var propertyValue = tinyMCE.trim(RegExp.$2);
		switch (styleProperty.toLowerCase()) {
			case 'text-align':
				replaceTag += '[align=' + propertyValue + ']';
			break;
			case 'font-weight':
				if (propertyValue.toLowerCase() == 'bold') {
					replaceTag += '[b]';
				}
				else if (propertyValue.toLowerCase() == 'normal') {
					replaceTag = '';
				}
			break;
			case 'font-style':
				if (propertyValue.toLowerCase() == 'italic') {
					replaceTag += '[i]';
				}
			break;
			case 'font-family':
				var family = propertyValue.replace(/([^,]+).*/, '$1');
				replaceTag += '[font=' + family + ']';
			break;
			case 'font-size':
				if (tinyMCE.isSafari3) {
					// array for converting from "size" (x-small to -webkit-xxx-large) to (8-36) 
					var fontPointSizes = new Object();
					fontPointSizes['x-small'] 		= 8;
					fontPointSizes['small'] 		= 10;
					fontPointSizes['medium'] 		= 12;
					fontPointSizes['large'] 		= 14;
					fontPointSizes['x-large'] 		= 18;
					fontPointSizes['xx-large'] 		= 24;
					fontPointSizes['-webkit-xxx-large'] 	= 36;
					var size = fontPointSizes[propertyValue];
				}
				else var size = propertyValue.replace(/([\d]+).*/, '$1');
				
				if (typeof(size) == 'undefined' || size == '' || size > 36) size = 10;
				replaceTag += '[size=' + size + ']';
			break;
			case 'color':
				var hexColor = convertRGBToHex(propertyValue);
				replaceTag += '[color=' + hexColor + ']';
			break;
			case 'text-decoration':
				if (propertyValue.toLowerCase() == 'underline') {
					replaceTag += '[u]';
				}
				if (propertyValue.toLowerCase() == 'line-through') {
					replaceTag += '[s]';
				}
			break;
			case 'float':
				if (tagName == 'img' && propertyValue.toLowerCase().match(/(left|right)/i)) {
					var src = attributes['wcf_src'] ? attributes['wcf_src'] : attributes['src'];
					replaceTag += '[img=\'' + src + '\',' + RegExp.$1 + ']';
				}
			break;
			case 'list-style-type':
				if (propertyValue.match(/(circle|square|none|disc|lower-roman|upper-roman|lower-greek|upper-latin|lower-latin|decimal-leading-zero|armenian|georgian)/)) {
					listType = propertyValue;
				}
				else if (propertyValue.match(/(lower-alpha|upper-alpha)/)) {
					listType = propertyValue.replace(/(lower|upper)-alpha/, '$1-latin');
				}
			break;
		}
	}
	if (tagName == 'a') {
		replaceTag += '[url=' + attributes['href'] + ']';
	}
	return new Array(replaceTag, listType);
};

TinyMCE_Engine.prototype.closeMultipleBBCodeTags = function(bbCode, html, isImage) {
	var openNodes = new Array();
	// Extracts strings to avoid [url='www.test.de/[nothing]'] link will be handled as multiple BBCodes
	tmpBBCode = bbCode.replace(/'[^']*'/, '');

	if (openNodes = tmpBBCode.match(/(\[[^\]]+\])/g)) {
		if (openNodes.length > 1) {
			// Multiple open tags (i.e.: [b][i]) from <span style="font-weight:bold;font-style:italic;">
			// Closes them all 
			openNodes.reverse();
			var closingTags = '';
			for (var j = 0; j < openNodes.length; j++) {
				var closeTag = openNodes[j].replace(/^[^\[]*\[([a-z0-9\*]+)[^\]]*\](.|\n)*/i, "[/$1]");
				closingTags += closeTag; 						
			}
			
			// Replaces HTML with BBCode
			if (isImage && /\[img\]/.test(bbCode)) {
				return html.replace(/<img.*?src="(.*?)".*?(?:wcf_src="(.*?)")(?:.*)>/i, function (thisMatch, src, wcf_src) {
					if (typeof(wcf_src) == "undefined" || wcf_src == '') return bbCode + src + closingTags;
					else return bbCode + wcf_src + closingTags;
				});
			}
			else if (isImage && /\[img=/.test(bbCode)) {
				return html.replace(/<img.*?src=".*?".*?>/i, bbCode + closingTags);
			}
			else return html.replace(/^<([^>]+>)/, closingTags); 
		}
		// No multiple tag found
		else {
			var endTag = bbCode.replace(/\[(\w+).*/, '[/$1]');
			if (isImage && /\[img\]/.test(bbCode)){
				return html.replace(/<img.*?src="(.*?)".*?(?:wcf_src="(.*?)")?(?:.*?)>/i, function (thisMatch, src, wcf_src) {
					if (typeof(wcf_src) == "undefined" || wcf_src == '') return bbCode + src + endTag;
					else return bbCode + wcf_src + endTag;
				});
			}
			else if (isImage && /\[img=/.test(bbCode)) {
				return html.replace(/<img.*?src=".*?".*?>/i, bbCode + endTag);
			}
			else return html.replace(/<\/.*?>/, endTag);
		}
	}
}

TinyMCE_Engine.prototype.getExtraBBCodeTag = function(tagName, node) {
	replaceTag = '';
	for (var bbCodeTag in extraBBCodes) {
		if (tagName.toLowerCase() == extraBBCodes[bbCodeTag]['htmlOpen']) {
			
			// Handles attributed codes
			if (extraBBCodes[bbCodeTag]['attributes'].length > 0) {
				var attributeString = '';
				var attributes = extraBBCodes[bbCodeTag]['attributes'];
				for (var j in attributes) {
					if (attributeString != '') attributeString += ',';
					var attributeName = attributes[j]['attributeHTML'].replace(/^\s*(\w+=).*/, '$1')
					
					// Styles got whitespaces: style="background: #eee;"
					if (attributeName.toLowerCase() == 'style=') {
						var regex = new RegExp(attributeName + '["\']?\\s*([^"\';]+)["\']?', 'i'); 
					}
					// Other attributes may got no quotes: id=dummyID (IE). whitespace is the end of attribute value
					else {
						var regex = new RegExp(attributeName + '["\']?\\s*([^"\';>\\s]+)["\']?', 'i'); 
					}
					
					// Attribute from actual BBCode is inside the actual tag
					if (node.match(regex)) {
						if (attributeName.toLowerCase() == 'style=') {
							var style = RegExp.$1;
							style.match(/([^:]+):\s*([^\s;]*)/);
							var value = RegExp.$2;
							var property = attributes[j]['attributeHTML'].replace(/^\s*style=["']([^:]+):.*/, '$1');
							var regex = new RegExp(property, 'i');
							if (style.match(regex)) {
								replaceTag = '[' + bbCodeTag;
								attributeString += value;
							}
						}
						else {
							replaceTag = '[' + bbCodeTag;
							attributeString += RegExp.$1;	
						}
					}
					// Attribute doesn't exist in actual tag
					// Dont use actual BBCode for this tag
					else {
						replaceTag = '';
						attributeString = '';
						break; 
					}
				}
				replaceTag += (attributeString != '' ? '=' : '') + attributeString;
			}
			// Got no attributes. needs to be only BBCode with this HTML tag. 
			// Otherwise the one in array overrules previous tags
			else {
				replaceTag = '[' + bbCodeTag;
			}
		}
	}
	if (replaceTag != '') replaceTag += ']';
	return replaceTag;
}

TinyMCE_Engine.prototype.setStyleAttrib = function(elm, name, value) {
	eval('elm.style.' + name + '=value;');

	// Style attributes deleted
	if (tinyMCE.isMSIE && value == null || value == '') {
		var str = tinyMCE.serializeStyle(tinyMCE.parseStyle(elm.style.cssText));
		elm.style.cssText = str;
		elm.setAttribute("style", str);
	}
};

/* Takes a string and replaces code blocks with unique strings */
TinyMCE_Engine.prototype.extractCodeBlocks = function(code, codeBlocks, decode, brTolineBreaks, switchView) {
	var codeBlockCount = 0;
	var sourceCodeRegex = new RegExp("(\\[" + tinyMCE.codeRegex + "=?[^\\]]*(.|\\n)*?\\[\\/\\2\\])");

	while (code.match(sourceCodeRegex)) {
		var uniqueString = Math.random().toString().substr(2) + "_codeblock_" + codeBlockCount;  
		var codeBlock = RegExp.$1;

		// Handles tabs (WYSIWYG to code view)
		if (decode) {
			// IE inserts one space for a tab
			if (tinyMCE.isIE && !tinyMCE.isOpera) {
				codeBlock = codeBlock.replace(/\s/g, '\t');
			}
			// Firefox inserts 3 spaces for one tab (i.e. from clipboard)
			else if (tinyMCE.isGecko) {
				codeBlock = codeBlock.replace(/&nbsp;&nbsp;&nbsp; /g, '\t');
			}
		}

		// No image tag for smileys in code but just the smiley code
		for (var smileyCode in smilies) {
			// Build smiley regex but dont get smiley codes in alt attribute
			var smileyRegex = new RegExp('<img\\s[^>]*?alt="'+smileyCode.pregQuote()+'"[^>]*?>', 'gi'); 
			codeBlock = codeBlock.replace(smileyRegex, smileyCode);
		}

		// Generates entities before removing HTML code (code view to WYSIWYG not if content is loaded)
		if (!decode && switchView) {
			codeBlock = tinyMCE.encodeHTMLEntities(codeBlock);
		}

		// Keeps line breaks (submit -> WYSIWYG to code view)
		if (brTolineBreaks) {
			codeBlock = codeBlock.replace(/<br[^>]*>/gi, '\n');
		}

		// Encodes entities (WYSIWYG to code view)
		if (decode) {
			// Removes HTML in code blocks. only entities will remain. (html which was inserted in WYSIWYG mode). 
			codeBlock = codeBlock.replace(/<[^>]+>/g, '');
			codeBlock = tinyMCE.decodeHTMLEntities(codeBlock);	
		}
		// Stores code blocks in array
		codeBlocks[uniqueString] = codeBlock;
		code = code.replace(sourceCodeRegex, uniqueString);
		codeBlockCount++;
	}
	return code;
}

TinyMCE_Engine.prototype.extractEntities = function(content, entities) {
	var entityCount = 0;
	var entityRegex = new RegExp("(&nbsp;|&quot;|&gt;|&lt;|&amp;)");
	while (content.match(entityRegex)) {
		var uniqueString = Math.random().toString().substr(2) + "_entity_" + entityCount+' ';  
		var entity = RegExp.$1;
	
		// Stores entities in array
		entities[uniqueString] = entity;
		content = content.replace(entityRegex, uniqueString);
		entityCount++;
	}
	return content;
}


/* Creates the popup div for the color picker */
TinyMCE_Engine.prototype.createColorPicker = function(editorID, isSimpleArea) {
	var forecolorElement = document.getElementById(editorID + '_color');
	if (!forecolorElement) return;

	var forecolorDiv = document.createElement('div');
	forecolorDiv.id = editorID + '_colorMenu';
	forecolorDiv.className = 'hidden';
	
	// Defines the color picker colors
	var colors = new Array("#000000","#333333","#666666","#999999","#cccccc","#ffffff","transparent",
				"#000066","#006666","#006600","#666600","#663300","#660000","#660066",
				"#000099","#009999","#009900","#999900","#993300","#990000","#990099",
				"#0000ff","#00ffff","#00ff00","#ffff00","#ff6600","#ff0000","#ff00ff",
				"#9999ff","#99ffff","#99ff99","#ffff99","#ffcc99","#ff9999","#ff99ff");
		
	// Builds inner HTML for color picker
	var colorPickerHTML = "";
	var url = tinyMCE.settings['imageURL'];
	colorPickerHTML += '<div class="mceColors"><ul>';
	
	for (var i = 0; i < colors.length; i++) {
		var color = colors[i];
		if (color == 'transparent') {		
			var command = "javascript:tinyMCE.execCommand('forecolor', false, 'transparent');"
			if (isSimpleArea) command = "javascript:void();";	
			colorPickerHTML += '<li><a style="background-image: url('+tinyMCE.settings['iconURL']+'colorPickerEmptyS.png);" href="' + command + '" title="transparent"></a></li>';
		}
		else {
			var command = "javascript:tinyMCE.execCommand('forecolor', false, '" + color + "');"
			if (isSimpleArea) command = "javascript:tinyMCE.simpleExecCommand('color', '" + editorID + "', '" + color + "');";	
			colorPickerHTML += '<li><a style="background-color:' + color + '; border-color:' + color + ';" href="' + command + '" title="' + color + '"></a></li>';
		}
	}
	colorPickerHTML += '</ul></div>';
	forecolorDiv.innerHTML = colorPickerHTML;
			
	// Adds color picker to the document and registeres the color picker icon as a popup menu
	var parentElement = forecolorElement.parentNode;
	parentElement.appendChild(forecolorDiv);
	popupMenuList.register(editorID + '_color');
}

TinyMCE_Engine.prototype.insertLink = function (editor_id) {
	var inst = tinyMCE.getInstanceById(editor_id);
	var elm = inst.getFocusElement();
	
	var oldHref = '';
	// Element is an <a> node. get content of the href attribute
	if (elm.nodeType == 1 && elm.nodeName.toLowerCase() == 'a') {
		oldHref = elm.getAttribute("href");
	}
	else if (aElm = tinyMCE.getParentElement(elm, "a")) {
		oldHref = aElm.getAttribute("href");
	}
	
	var href = prompt(language['link.insert.url.optional'], oldHref);
	
	// Cancel was hit. insert nothing
	if (href == null) return;
	
	if (href == '') href = inst.selection.getSelectedText();
	elm = tinyMCE.getParentElement(elm, "a");
	tinyMCE.execCommand("mceBeginUndoLevel");
	
	// Creates new anchor elements
	if (elm == null && href != null && href != '') {
		tinyMCE.execCommand("createlink", false, '#mce_temp_url#');
		
		var elementArray = tinyMCE.getElementsByAttributeValue(inst.getBody(), "a", "href", '#mce_temp_url#');
		for (var i = 0; i < elementArray.length; i++) {
			var elm = elementArray[i];
			
			elm.href = href;
			// Moves cursor behind the new anchor
			if (tinyMCE.isGecko) {
				var sp = inst.getDoc().createTextNode(" ");

				if (elm.nextSibling) {
					elm.parentNode.insertBefore(sp, elm.nextSibling);
				}
				else {
					elm.parentNode.appendChild(sp);
				}

				// Sets range after link
				var rng = inst.getDoc().createRange();
				rng.setStartAfter(elm);
				rng.setEndAfter(elm);

				// Updates selection
				var sel = inst.getSel();
				sel.removeAllRanges();
				sel.addRange(rng);
			}
			
			else if (tinyMCE.isOpera) {
				// Opera doesn't leave the </a> tag 
				// TODO: Do something special here so that Opera can leave it
   			}
		}
	}
	else if (href != null && href != '') {
		elm.setAttribute("href", href);
	}
	
	if (!tinyMCE.isOpera) tinyMCE.execCommand("mceEndUndoLevel"); // opera gets a range error while inserting a link
};

TinyMCE_Engine.prototype.initCursor = function () {
	
	// Guest cursor in user name input field
	var username = document.getElementById('username');
	if (username) username.focus();
	else {
		for (var i = 0; i < document.forms.length; i++) {
			var form = document.forms[i];
			if (form.action && typeof(form.action) != 'object' && typeof(form.action) == 'string') {
				// Thread add cursor in subject input field
				if (form.action.match(/form=ThreadAdd/)) {
					document.getElementById('subject').focus();				
				}
				// Post add cursor in editor
				else if (form.action.match(/form=PostAdd/)) {
					var instance = tinyMCE.selectedInstance;
					// Simple area
					if (tinyMCE.isSimpleTextarea) document.getElementById(tinyMCE.simpleAreaID).focus();
					// WYSIWYG
					else if (instance.editorIsActive) instance.getWin().focus();	
					// Code view
					else {
						instance.theTextarea.focus();
						if (tinyMCE.isSafari3 && instance.theTextarea.value != '') {
							instance.theTextarea.setSelectionRange(instance.theTextarea.value.length,instance.theTextarea.value.length); 
						}
					}
				}
			}
		}
	}
}

TinyMCE_Engine.prototype.cleanupHTML_ = function(content) {

	// TODO: Not yet tested if auto detection works and if this "cleanWord" function works either						
	// the entire content will be cleaned.
	// Cleanup pasted MS Word Contents 
	var regex = /<\w[^>]*(( class="?MsoNormal"?)|(="mso-))/gi ;
	if (0 && regex.test(content)) content = cleanWord(content);

	/* Puts quotes around unquoted attributes */
	content = content.replace(/<[^>]*>/g, function(thisMatch) {
		thisMatch = thisMatch.replace(/( [^=]+=)([^"][^ >]*)/g, "$1\"$2\"");
		return thisMatch;
	});
		
	// Removes script tags (incl. content of the tags)
	content = content.replace(/<script.*?>(.|\n)*?<\/script>/gi, ''); 
		
	// Removes HTML comments 
	content = content.replace(/<!--(.|\s)*?-->/g, '');
		
	// Removes anchor-links
	content = content.replace(/<a.*?name="[^"]+".*?>((.|\n)*?)<\/a>/gi, '$1');		
		
	// Removes javascript links
	content = content.replace(/<a.*?href="javascript:[^"]*".*?>((.|\n)*?)<\/a>/gi, '$1');
		
	// Removes select boxes (text inside too)
	content = content.replace(/<select[^>]*>(.|\n)*?<\/select>/gi, '');
	
	// Removes unwanted start tags
	var allowedStartTags = new RegExp("<(?!(\\/\\w+|b|strong|em|i|u|strike|ol|ul|li|p(?!re)|br|a|img|font|div|span|blockquote" + tinyMCE.extraHTMLOpenTags + ")[\\s>])[^>]+>", "gi");
	content = content.replace(allowedStartTags, '');

	// Removes unwanted end tags. If it is a block element insert a <br>
	var allowedEndTags = new RegExp("</(?!(b|strong|em|i|u|strike|ol|ul|li|p(?!re)|br|a|img|font|div|span|blockquote" + tinyMCE.extraHTMLOpenTags + "))\\w+>", "gi");
	content = content.replace(allowedEndTags, function(thisMatch) {
		if (thisMatch.match(/address|center|dl|dir|fieldset|form|h[1-6]|hr|isindex|menu|noframes|noscript|pre|table/i)) return '<br>';
		else return '';
		}
	);
	// Strips out unaccepted attributes 
	content = content.replace(/<[^>]*?>/g, function(thisMatch) {
		thisMatch = thisMatch.replace(/ ([^=]+)="([^"]*)"/g, function(match2, attributeName, attributeValue) {
			if ((attributeName == "id" && attributeValue == 'pasteCursorPosition') 
				|| attributeName == "alt" || attributeName == "href" 
				|| attributeName == "src" || attributeName == "title" 
				|| attributeName == "align" || attributeName == "style" 
				|| (attributeName == "class" && attributeValue == 'wysiwygQuote') 
				|| attributeName == 'username' || attributeName == 'linkhref' 
				|| attributeName.match(tinyMCE.extraAttributes) != ''
				|| attributeName.match(/font|face|size|color/)) {

				if (attributeName == "style" && attributeValue != '') {
					var styleDefinitions = attributeValue.match(/[^:]+:[^;]+;?/g);
					for (var j = 0; j < styleDefinitions.length; j++) {
						styleDefinitions[j].match(/\s*([^:]+):\s*([^;]+)/);
						var styleProperty = RegExp.$1;
						var propertyValue = tinyMCE.trim(RegExp.$2);
	
						switch (styleProperty.toLowerCase()) {
							case 'text-align':
							case 'font-family':
							case 'font-size':
							case 'color':
							case 'float':
							break;
							case 'font-weight':
								if (propertyValue.toLowerCase() != 'bold') return '';
								break;
							case 'font-style':
								if (propertyValue.toLowerCase() != 'italic') return '';
								break;
							case 'text-decoration':
								if (propertyValue.toLowerCase() != 'underline' && propertyValue.toLowerCase() != 'line-through') return '';
								break;
							case 'list-style-type':
								if (!propertyValue.match(/(circle|square|none|disc|lower-roman|upper-roman|lower-greek|upper-latin|lower-latin|decimal-leading-zero|armenian|georgian)/)) {
									return '';
								}
								break;
							default:
								// Check userdefined BBcode style properties
								var allowedProperty = false; 
								for (var code in extraBBCodes) {
								if (!extraBBCodes[code]['wysiwyg']) continue;
								var regex = new RegExp(extraBBCodes[code]['htmlOpen'], 'i');
								if (thisMatch.match(regex)) {				
									var attributes = extraBBCodes[code]['attributes'];
									for (var i = 0; i < attributes.length; i++) {
										if (attributes[i]['attributeHTML'].match(styleProperty.toLowerCase())) {
											allowedProperty = true;
											break;																												
										}
									}
								}
								if (allowedProperty) break;
							}
							if (!allowedProperty) return '';
						}//switch
					}//for
				}//if
				return match2;
			}//if
			return "";
		});//replace
		return thisMatch;
	});//replace
	return content;
}

// FUNCTIONS WHICH DON'T HAVE TO BE A CLASS METHOD
/* Convert RGB values to hexadecimal values */
function convertRGBToHex (col) {
	var re = new RegExp("rgb\\s*\\(\\s*([0-9]+).*,\\s*([0-9]+).*,\\s*([0-9]+).*\\)", "gi");

	var rgb = col.replace(re, "$1,$2,$3").split(',');
	if (rgb.length == 3) {
		r = parseInt(rgb[0]).toString(16);
		g = parseInt(rgb[1]).toString(16);
		b = parseInt(rgb[2]).toString(16);

		r = r.length == 1 ? '0' + r : r;
		g = g.length == 1 ? '0' + g : g;
		b = b.length == 1 ? '0' + b : b;

		return "#" + r + g + b;
	}
	return col;
}

function convertDecimalToHex(decimal){
	var dstr = (decimal & 16711680) >> 16;
	var dstg = (decimal & 65280) >> 8;
	var dstb = decimal & 255;
	return convertRGBToHex('rgb(' + dstr + ',' + dstg + ',' + dstb +')');
}

/**
 * Takes a string and replaces smileys with unique strings.
 *
 * @param	string	code	
 * @param   	object	smileyBlocks	assoziative array which is called by reference
 * @return	string	code		code with encoded smiley images
 */
function extractSmilies(code, smileyBlocks) {

	for (var smileyCode in smilies) {
		// Builds smiley regex 
		var smileyRegex = new RegExp('(<img\\s[^>]*?alt="'+smileyCode.pregQuote()+'"[^>]*?>)', 'i'); 

		var smileyBlockCount = 0;
		while (code.match(smileyRegex)) {
		
			var uniqueString = Math.random().toString().substr(2) + "_" + smileyCode + "_" + smileyBlockCount;  
			var smileyBlock = RegExp.$1;
			// Stores smiley block in array
			smileyBlocks[uniqueString] = smileyBlock;
			code = code.replace(smileyRegex, uniqueString);
			smileyBlockCount++;
		}
	}
	return code;
}

/* Takes a string and replaces MS Word cruft */
function cleanWord(html){
	html = html.replace(/<o:p>\s*<\/o:p>/g, "");
	html = html.replace(/<o:p>(.|\n)*?<\/o:p>/g, "&nbsp;");
	
	// Removes mso-xxx styles
	html = html.replace(/\s*mso-[^:]+:[^;"]+;?/gi, "");

	// Removes margin styles
	html = html.replace(/\s*MARGIN: 0cm 0cm 0pt\s*;/gi, "");
	html = html.replace(/\s*MARGIN: 0cm 0cm 0pt\s*"/gi, "\"");
	html = html.replace(/\s*TEXT-INDENT: 0cm\s*;/gi, "");
	html = html.replace(/\s*TEXT-INDENT: 0cm\s*"/gi, "\"");
	html = html.replace(/\s*TEXT-ALIGN: [^\s;]+;?"/gi, "\"");
	html = html.replace(/\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"");
	html = html.replace(/\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"");
	html = html.replace(/\s*tab-stops:[^;"]*;?/gi, "");
	html = html.replace(/\s*tab-stops:[^"]*/gi, "");

	// Removes font face attributes
	html = html.replace(/\s*face="[^"]*"/gi, "");
	html = html.replace(/\s*face=[^ >]*/gi, "");
	html = html.replace(/\s*FONT-FAMILY:[^;"]*;?/gi, "");
	
	// Removes class attributes
	html = html.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3");

	// Removes styles
	html = html.replace(/<(\w[^>]*) style="([^\"]*)"([^>]*)/gi, "<$1$3");

	// Removes empty styles
	html =  html.replace(/\s*style="\s*"/gi, '');
	
	html = html.replace(/<SPAN\s*[^>]*>\s*&nbsp;\s*<\/SPAN>/gi, '&nbsp;');
	html = html.replace(/<SPAN\s*[^>]*><\/SPAN>/gi, '');
	
	// Removes lang attributes
	html = html.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3");
	
	html = html.replace(/<SPAN\s*>((.|\n)*?)<\/SPAN>/gi, '$1');
	
	html = html.replace(/<FONT\s*>((.|\n)*?)<\/FONT>/gi, '$1');

	// Removes XML elements and declarations
	html = html.replace(/<\\?\?xml[^>]*>/gi, "");
	
	// Removes tags with XML namespace declarations: <o:p><\/o:p>
	html = html.replace(/<\/?\w+:[^>]*>/gi, "");
	html = html.replace(/<H\d>\s*<\/H\d>/gi, '');
	html = html.replace(/<H1([^>]*)>/gi, '<div$1><b><font size="6">');
	html = html.replace(/<H2([^>]*)>/gi, '<div$1><b><font size="5">');
	html = html.replace(/<H3([^>]*)>/gi, '<div$1><b><font size="4">');
	html = html.replace(/<H4([^>]*)>/gi, '<div$1><b><font size="3">');
	html = html.replace(/<H5([^>]*)>/gi, '<div$1><b><font size="2">');
	html = html.replace(/<H6([^>]*)>/gi, '<div$1><b><font size="1">');
	html = html.replace(/<\/H\d>/gi, '<\/font><\/b><\/div>');
	html = html.replace(/<(U|I|STRIKE)>&nbsp;<\/\1>/g, '&nbsp;');

	// Removes empty tags
	html = html.replace(/<([^\s>]+)[^>]*>\s*<\/\1>/g, '');

	// Transforms <P> to <DIV>
	var re = new RegExp("(<P)([^>]*>(.|\\n)*?)(<\\/P>)","gi");	
	html = html.replace(re, "<div$2<\/div>");
	return html ;
}

// Create object of tinyMCE
var tinyMCE = new TinyMCE_Engine();