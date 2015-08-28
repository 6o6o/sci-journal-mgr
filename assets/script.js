(function(){
	function domWalker(el,dir,expr,supnod){
		var descend = dir ? 'firstChild' : 'lastChild', skip, txt, i = 0;
		dir = dir ? 'nextSibling' : 'previousSibling';
		while(el[descend] || el[dir] || el.parentNode) {
			if(!skip && el[descend]) {
				el = el[descend];
			} else if(el[dir]) {
				el = el[dir];
				skip = 0;
			} else {
				el = el.parentNode;
				skip = 1;
				i++;
				if(!supnod) {
					supnod = el.currentStyle ? el.currentStyle.display :
							getComputedStyle(el, null).display;
					if(supnod == 'block') break;
					else supnod = null;
				}
				continue;
			}
			txt = el.nodeValue;
			if(i && txt && expr.test(txt)) break;
		}
		return el || {};
	}			
	function wrapEl(c,q,n) {
		var el = c.querySelectorAll(q||'*');
		var txt = c.innerText || c.textContent;
		var done = n ? ~txt.indexOf('<'+n+'>') : c.querySelector('.hl');
		if(!done) for (var i = 0; i < el.length; i++) {
			if(q) {
				var prt = el[i].parentNode;
				var tag = prt.querySelectorAll(q);
				var a = el[i].innerHTML;
				if(prt.childNodes.length > 1 && alchar.test(a) && prt.childNodes.length != tag.length){
					// omit sequetial tags <i>Proc. 16</i><i>th</i>, omit spaces and comas
					a = a.replace(/^([\s,]+)|^/, '$1'+(el[i-1] === domWalker(el[i],0,alchar).parentNode ? '' : '&lt;'+n+'&gt;'));
					a = a.replace(/([\s,]+)$|$/, (el[i+1] === domWalker(el[i],1,alchar).parentNode ? '' : '&lt;/'+n+'&gt;')+'$1');
					el[i].innerHTML = a;
				}
			} else {
				if(el[i].childNodes.length == 1 && el[i].firstChild.nodeType == 3)
				el[i].innerHTML = el[i].innerHTML.replace(dchar, '<span class="hl">$1</span>');
			}
		}
	}
	var ctn = document.querySelector('div[contenteditable]');
	var dchar = /([^\x09-\x0D\x20-\xFF\u0100-\u017F\u2013-\u2044]+)/g;
	var alchar = /[\w\-\xC0-\xFF\u0100-\u0148\u2013()]+/;
	var form = document.forms;
	for(var i=0; i<form.length; i++) {
		form[i].onsubmit = function() {
			var ignore = this.ignore && this.ignore.checked;
			var handle = {
				SELECT: 'selectedIndex'
			}
			if(!ignore) for(var i=0; i<this.length-1; i++) {
				var inp = this[i], prohib;
				if(!inp[handle[inp.nodeName] || 'value']) {
					prohib = true;
					inp.className = '';
					inp.style.background = '#f99';
					var redraw = inp.offsetHeight;
					inp.className = 'fade-out';
					inp.style.background = '';
					inp.onfocus = function() {
						this.className = '';
					}
				}
			}
			if ((!ignore && prohib) || (newabs && !confirm('Sure?'))) return false;
		}
	}
	var newabs = document.forms.newabs;
	var abform = document.querySelectorAll('a[href^="#add"]');
	for (var i=0; i<abform.length; i++) abform[i].onclick = function() {
		var nx = this.nextSibling;
		while(nx && nx.nodeType !== 1) nx = nx.nextSibling;
		if (nx) nx.style.display = 'block';
		this.style.display = 'none';
		return false;
	}
	var tr = document.querySelectorAll('.trlit div[data-ph]');
	var cfg = document.querySelectorAll('.trlit input');
	for (var i=0; i<cfg.length; i++) cfg[i].onchange = function() {
		tr[0].onkeyup();
	}
	if(tr[1]) tr[0].onkeyup = function() {
		tr[1].innerHTML = translit(this.innerHTML,cfg);
	}
	var panhed = document.querySelectorAll('.panel .h');
	for (var i=0; i<panhed.length; i++) {
		panhed[i].onclick = function() {
			var panel = this.parentNode;
			var cont = panel.querySelector('* ~ div');
			var hh = this.offsetHeight-1;
			var ph = panel.offsetHeight-2;
			if(!panel.style.height) {
				panel.style.height = ph+'px';
				cont.style.display = 'block';
				panel.offsetHeight;
			}
			if(Math.abs(parseInt(panel.style.height) - hh) < 5) {
				panel.style.height = hh+1+cont.offsetHeight+'px';
			} else {
				panel.style.height = hh+'px';
			}
		}
	}

	if (newabs) {
		if(location.search) {
			for(var i=0; i<newabs.length-1; i++) {
				newabs[i].onchange = function(){
					if(this.name && this.name.indexOf('update'))
					this.name = 'update['+this.name+']';
				}
			}
		}
		ctn.onkeyup = function() {
			autofill();
			var prt = window.getSelection().getRangeAt(0).startContainer.parentNode;
			if(prt.className == 'hl') {
				var bef = prt.innerHTML;
				var aft = bef.replace(dchar, '<span class="hl">$1</span>');
				if(bef == aft) prt.outerHTML = aft;
			}
		}
		newabs.refs.onpaste = function(e) {
			var data = this.value, proc;
			if (data && e && e.clipboardData) {
				var tmp = document.createElement('div');
				tmp.innerHTML = data;
				var olddata = tmp.innerText || tmp.textContent;
				var newdata = e.clipboardData.getData('text/plain');
				olddata = olddata.split(/(?:\r?\n|\r)+/);
				newdata = newdata.replace(/(\S)(\r?\n|\r)(\S)/g, '$1 $3');
				newdata = newdata.split(/(?:(?:\r?\n|\r) *){2}/);
				data = data.split(/(?:\r?\n|\r)+/);
				for(var i=0; i<olddata.length; i++) {
					for(var j=0; j<newdata.length; j++) {
						if(!newdata[j].indexOf(olddata[i])) {
							data[i] += newdata[j].substr(olddata[i].length);
							proc = true;
						}
					}
				}
				if(proc) {
					this.value = data.join('\n');
					this.onchange();
					return false;
				}
			}
		}
		newabs.section.onchange = function(){
			var handle = newabs[0].onchange;
			if(handle) handle.call(this);
			if(!parseInt(this.value)) {
				var prt = this.parentNode;
				prt.removeChild(this);
				prt.innerHTML += '<input name="section" type="text" placeholder="'+this.value+'">';
				prt.lastChild.onchange = handle;
				prt.lastChild.focus();
			}
		}
		ctn.onpaste = autofill;
		
		function autofill(){
		setTimeout(function(){
			wrapEl(ctn,'[style*=italic],em,i','i');
			wrapEl(ctn,'[style*=super],sup','sup');
			wrapEl(ctn,'[style*=sub],sub','sub');
			wrapEl(ctn);
			var line = 0;
			var data = ctn.innerText || ctn.textContent; // todo: fix firefox
			data = data.split(/(?:\r?\n|\r)+/);
//data.unshift(data[3]);
//data.splice(4,1);
			for(var i=0; i<data.length; i++) {
				var c = data[i].replace(/^\s+|\s+$/g, '');
				var x = (c.substring(0,c.indexOf(' '))||c).toLowerCase();
				if(!x.indexOf('abstract') || !x.indexOf('keywords') || !x.indexOf('references'))
					c = c.substring(x.length+1);
				if(c) {
					if(line > 8) {
						newabs.refs.value += '\n'+c;
						newabs.refs.rows = line-5;
					} else if(line > 2) {
						if(line == 6 && c.length < 200) {
							line--;
							newabs[line+3].value += '\n'+c;
							newabs.inst.rows = i-4;
						} else newabs[line+3].value = c;
					} else {
					for(var j=0; j<newabs.section.length; j++) {
						if(newabs.section[j].innerHTML == c)
							newabs.section.value = newabs.section[j].value;
					}
					if(!!~c.indexOf('doi.org'))
						newabs.doi.value = c;
					else {
						c = c.match(/\d+/g);
						if(c && c.length > 4) {
							newabs.vol.value = c[1];
							newabs.issue.value = c[2];
							newabs.start_page.value = c[3];
							newabs.end_page.value = c[4];
						}
					}}
					line++;
				}
			}
		},1)};
	}
	function translit(cyr,inp) {
		var lat = '';
		var letter = {
			'А':'A','а':'a',
			'Б':'B','б':'b',
			'В':'V','в':'v',
			'Г':'G','г':'g',
			'Ґ':'G','ґ':'g',
			'Д':'D','д':'d',
			'Е':'E','е':'e',
			'Э':'E','э':'e',
			'Є':'Ye','є':'ye',
			'Ё':'Yo','ё':'yo',
			'Ж':'Zh','ж':'zh',
			'З':'Z','з':'z',
			'І':'I','і':'i',
			'Ї':'Yi','ї':'yi',
			'И':'Y','и':'y',
			'Й':'Y','й':'y',
			'Ы':'Y','ы':'y',
			'К':'K','к':'k',
			'Л':'L','л':'l',
			'М':'M','м':'m',
			'Н':'N','н':'n',
			'О':'O','о':'o',
			'П':'P','п':'p',
			'Р':'R','р':'r',
			'С':'S','с':'s',
			'Т':'T','т':'t',
			'У':'U','у':'u',
			'Ф':'F','ф':'f',
			'Х':'Kh','х':'kh',
			'Ц':'Ts','ц':'ts',
			'Ч':'Ch','ч':'ch',
			'Ш':'Sh','ш':'sh',
			'Щ':'Shch','щ':'shch',
			'Ю':'Yu','ю':'yu',
			'Я':'Ya','я':'ya',
			'Ь':'<!>','ь':'<!>',
			'Ъ':'<!>','ъ':'<!>'
		};
		var alt = { 'И':'I','и':'i' };
		var hhh = { 'Г':'H','г':'h' };
		var vow = {a:1,o:1,y:1};
		var yy = {y:1};
		if(inp[0].checked) for (var atr in alt) { letter[atr] = alt[atr]; }
		if(inp[1].checked) for (var atr in hhh) { letter[atr] = hhh[atr]; }
		for(var i=0; i<cyr.length; i++) {
			var ch = cyr.charAt(i);
			if(inp[2].checked && vow[letter[cyr.charAt(i-1)]] && ch == 'ї' ||
			inp[3].checked && yy[letter[cyr.charAt(i-1)]] && yy[letter[ch]])
				ch = 'i';
			lat += letter[ch] || ch;
		}
		return lat;
	}
})();