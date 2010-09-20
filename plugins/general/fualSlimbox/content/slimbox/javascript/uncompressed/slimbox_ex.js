/*
	Slimbox (Extended Version 1.3.1, 2007-02-21)
	by Yukio Arita (http://homepage.mac.com/yukikun/software/slimbox_ex/)
	- 	Support to show external content using iframe.
	- 	Support to set content size. You can add width/height parameters 
		in rev attribute of the anchor url.
		ex1) <a href="image.jpg" rev="width=50%, height=50%" rel="lightbox">
				<img src="image_thumb.jpg" alt="image"></a>
		ex2) <a href="text.html" rev="width=500, height=300" rel="lightbox">
				some text here</a>
	- 	Some rendering problem with IE6 is fixed. Now you can use Slimbox in 
		valid XHTML document with XML prolog.
	- Of course, license is same as original.

	Based on:
	Slimbox v1.3 - The ultimate lightweight Lightbox clone
	by Christophe Beyls (http://www.digitalia.be) - MIT-style license.
	Inspired by the original Lightbox v2 by Lokesh Dhakar.
	
	Zen Cart  compatiblised (that's not a word!) by Brian Tyler / FUAL
*/
var Lightbox = {
	init: function(options){
		var fualSlimboxOptions = new FualSlimboxOptions();
		this.options = Object.extend({
			resizeFps: fualSlimboxOptions.resizeFps,
			resizeDuration: fualSlimboxOptions.resizeDuration,
			resizeTransition: fualSlimboxOptions.resizeTransition,
			initialWidth: fualSlimboxOptions.initialWidth,
			initialHeight: fualSlimboxOptions.initialHeight,
			animateCaption: fualSlimboxOptions.animateCaption,
			defaultIframeWidth : fualSlimboxOptions.defaultIframeWidth, 
			defaultIframeHeight: fualSlimboxOptions.defaultIframeHeight,
			elHide: fualSlimboxOptions.elHide,
			displayVar:fualSlimboxOptions.displayVar,
			pageOf:fualSlimboxOptions.pageOf
		}, options || {});
		
		// IE 6 - XML prolog problem
		if(window.ie6 && document.compatMode=="BackCompat"){
			this.options.animateCaption = false;
		}

		this.anchors = [];
		$each(document.links, function(el){
			if (el.rel && el.rel.test(/^lightbox/i)){
				el.onclick = this.click.pass(el, this);
				this.anchors.push(el);
			}
		}, this);
		this.eventKeyDown = this.keyboardListener.bindAsEventListener(this);
		this.eventPosition = this.position.bind(this);

		/*	Build float panel
			<div id="lbOverlay"></div>
			<div id="lbCenter">
				<div id="lbCanvas">
					<a id="lbPrevLink"></a>
					<a id="lbNextLink"></a>
					<!-- img or iframe element is inserted here -->
				</div>
			</div>
			<div id="lbBottomContainer">
				<div id="lbBottom">
					<span id="lbCloseLink"></a>
					<div id="lbNCWrapper"></div>
						<div id="lbCaption"></div>
						<div style="clear:both;"></div>
						<!-- lbNumber only displays if there is more than one image in the series -->
						<div id="lbNumber"></div>
					<div style="clear:both;"></div>
				</div>
			</div>
		*/

		this.overlay = new Element('div').setProperty('id', 'lbOverlay').injectInside(document.body);
		this.center = new Element('div').setProperty('id', 'lbCenter').setStyles({width: this.options.initialWidth+'px', height: this.options.initialHeight+'px', marginLeft: '-'+(this.options.initialWidth/2)+'px', display: 'none'}).injectInside(document.body);
		this.canvas = new Element('div').setProperty('id', 'lbCanvas').injectInside(this.center);
		this.prevLink = new Element('span').setProperties({id: 'lbPrevLink' + this.options.displayVar }).setStyle('display', 'none').injectInside(this.canvas);
		this.nextLink = this.prevLink.clone().setProperty('id', 'lbNextLink' + this.options.displayVar ).injectInside(this.canvas);
		this.prevLink.onclick = this.previous.bind(this);
		this.nextLink.onclick = this.next.bind(this);

		this.bottomContainer = new Element('div').setProperty('id', 'lbBottomContainer').setStyle('display', 'none').injectInside(document.body);
		this.bottom = new Element('div').setProperty('id', 'lbBottom').injectInside(this.bottomContainer);
		
		/* FUAL */
		// Changed 'a' to 'div' in order to deal with an issue in IE / Opera which was directing back to the root page.
		this.closeLink = new Element('div').setProperty('id', 'lbCloseLink' + this.options.displayVar ).injectInside(this.bottom);
		this.closeLink.onclick = this.overlay.onclick = this.close.bind(this);
		this.closeLink.setHTML('&nbsp;');
		// Added lbNCWrapper to keep the lbCaption and lbNumber label separate from the close button.
		this.ncwrapper = new Element('div').setProperty('id', 'lbNCWrapper').injectInside(this.bottom);
		this.caption = new Element('div').setProperty('id', 'lbCaption').injectInside(this.ncwrapper);
		this.number = new Element('div').setProperty('id', 'lbNumber').injectInside(this.ncwrapper);
		/* FUAL */
		new Element('div').setStyle('clear', 'both').injectInside(this.bottom);

		/* Build effects */
		var nextEffect = this.nextEffect.bind(this);
		this.fx = {
			overlay: this.overlay.effect('opacity', {duration: 500}).hide(),
			resizeCenter: this.center.effects({duration: this.options.resizeDuration, transition: this.options.resizeTransition, onComplete: nextEffect, fps: this.options.resizeFps }),
			image: this.canvas.effect('opacity', {duration: 500, onComplete: nextEffect}),
			bottom: this.bottomContainer.effect('height', {duration: 400, onComplete: nextEffect})
		};

		this.preloadPrev = new Image();
		this.preloadNext = new Image();
	},

	click: function(link){
		if (link.rel.length == 8) return this.show(link.href, link.title, link.rev);
		var j, itemNumber, items = [];
		this.anchors.each(function(el){
			if (el.rel == link.rel){
				for (j = 0; j < items.length; j++) if(items[j][0] == el.href && items[j][2] == el.rev) break;
				if (j == items.length){
					items.push([el.href, el.title, el.rev]);
					if (el.href == link.href && el.rev == link.rev) itemNumber = j;
				}
			}
		}, this);
		return this.open(items, itemNumber);
	},

	show: function(url, title, rev){
		return this.open([[url, title, rev]], 0);
	},

	open: function(items, itemNumber){
		$$( this.options.elHide ).setStyle('visibility', 'hidden'); 		// FUAL
		this.items = items;
		this.position();
		this.setup(true);
		var wh = (window.getHeight() == 0) ? window.getScrollHeight() : window.getHeight();
		var st = document.body.scrollTop  || document.documentElement.scrollTop;
		this.top = st + (wh / 15);
		this.center.setStyles({top: this.top+'px', display: ''});
		this.fx.overlay.start(0.8);
		return this.changeItem(itemNumber);
	},

	position: function(){
		//IE6 - XML prolog problem.
		var ww = (window.getWidth() == 0) ? window.getScrollWidth()-22 : window.getWidth();
		var wh = (window.getHeight() == 0) ? window.getScrollHeight() : window.getHeight();
		var st = document.body.scrollTop  || document.documentElement.scrollTop;
		this.overlay.setStyles({top: st+'px', height: wh+'px', width:ww+'px'});
	},

	setup: function(open){
		var elements = $A(document.getElementsByTagName('object'));
		if (window.ie) elements.extend(document.getElementsByTagName('select'));
		elements.each(function(el){ el.style.visibility = open ? 'hidden' : ''; });
		var fn = open ? 'addEvent' : 'removeEvent';
		window[fn]('scroll', this.eventPosition)[fn]('resize', this.eventPosition);
		document[fn]('keydown', this.eventKeyDown);
		this.step = 0;
	},

	keyboardListener: function(event){
		switch (event.keyCode){
			case 27: case 88: case 67: this.close(); break;
			case 37: case 80: this.previous(); break;	
			case 39: case 78: this.next();
		}
	},

	previous: function(){
		return this.changeItem(this.activeItem-1);
	},

	next: function(){
		return this.changeItem(this.activeItem+1);
	},

	changeItem: function(itemNumber){
		if (this.step || (itemNumber < 0) || (itemNumber >= this.items.length)) return false;
		this.step = 1;
		this.activeItem = itemNumber;

		this.bottomContainer.style.display = this.prevLink.style.display = this.nextLink.style.display = 'none';
		this.fx.image.hide();
		this.center.className = 'lbLoading';

		// discard previous content by clicking
		this.removeCurrentItem();
		
		// check item type
		var url = this.items[this.activeItem][0];
		var rev = this.items[this.activeItem][2];
		
		var re_imageURL = /\.(jpe?g|png|gif|bmp)/i;
		if( url.match(re_imageURL) ) {
			this.preload = new Image();	// JavaScript native Object
			this.preload.datatype = 'image';
			this.preload.w = this.matchOrDefault(rev, new RegExp("width=(\\d+%?)", "i"), -1); //-1 if use original size.
			this.preload.h = this.matchOrDefault(rev, new RegExp("height=(\\d+%?)", "i"), -1);
			this.preload.onload = this.nextEffect.bind(this);
			this.preload.src = url;
		}else{
			this.preload = new Object ();	// JavaScript native Object
			this.preload.datatype = 'iframe';
			this.preload.w =  this.matchOrDefault(rev, new RegExp("width=(\\d+)", "i"), this.options.defaultIframeWidth);
			this.preload.h = this.matchOrDefault(rev, new RegExp("height=(\\d+)", "i"), this.options.defaultIframeHeight);
			this.preload.src = url;
			this.nextEffect(); //asynchronous loading
		}

		return false;
	},

	nextEffect: function(){
		switch (this.step++){
		case 1:
			this.center.className = '';

			// create HTML element
			if( this.preload.datatype == 'image' ) {
				var ws = (this.preload.w == -1) ? this.preload.width.toString() : this.preload.w.toString();
				var hs = (this.preload.h == -1) ? this.preload.height.toString() : this.preload.h.toString();
				this.p_width = ( q = ws.match(/(\d+)%/) ) ? q[1] * this.preload.width * 0.01 : ws;
				this.p_height = ( q = hs.match(/(\d+)%/) ) ? q[1] * this.preload.height * 0.01 : hs;
				new Element('img').setProperties({id: 'lbImage', src:this.preload.src, width:this.p_width, height:this.p_height}).injectInside(this.canvas);
				this.nextLink.style.right = '';
			}else{
				this.p_width = this.preload.w;
				this.p_height = this.preload.h;
				// Safari would not update iframe content that has static id.
				this.iframeId = "lbFrame_"+new Date().getTime();
				new Element('iframe').setProperties({id: this.iframeId, width: this.p_width, height: this.p_height, frameBorder:0, scrolling:'yes', src:this.preload.src}).injectInside(this.canvas);
				this.nextLink.style.right = '25px';
			}
			this.canvas.style.width = this.bottom.style.width = this.p_width+'px';
			this.canvas.style.height = this.prevLink.style.height = this.nextLink.style.height = this.p_height+'px';

			this.caption.setHTML(this.items[this.activeItem][1] || '');
			/* FUAL */
			// Don't want the lbNumber label to display if there is only 1 item.
			// This stops an oversize bottom block in IE.
			if(this.items.length == 1){
				this.number.setStyle('display', 'none');
			} else {
				this.number.setStyle('display', '');
				//this.closebutton.setStyle('margin-top', '15px');
				this.number.setHTML( this.options.pageOf.replace(/#1/,(this.activeItem+1) ).replace(/#2/, this.items.length) );
			}
			/* FUAL */
			if (this.activeItem) this.preloadPrev.src = this.items[this.activeItem-1][0];
			if (this.activeItem != (this.items.length - 1)) this.preloadNext.src = this.items[this.activeItem+1][0];
			if (this.center.clientHeight != this.canvas.offsetHeight){
				var oh = (this.p_height == this.canvas.clientHeight) ? this.canvas.offsetHeight : eval(this.p_height)+18; // fix for ie
				this.fx.resizeCenter.start({height: oh});
				break;
			}

			this.step++;
		case 2:
			if (this.center.clientWidth != this.canvas.offsetWidth){
				var ow = (this.p_width == this.canvas.clientWidth) ? this.canvas.offsetWidth : eval(this.p_width)+18; // fix for ie
				this.fx.resizeCenter.start({width: ow, marginLeft: -ow/2});
				break;
			}
			this.step++;
		case 3:
			this.bottomContainer.setStyles({top: (this.top + this.center.clientHeight)+'px', height:'0px', marginLeft: this.center.style.marginLeft, width:this.center.style.width, display: ''});
			var ncw = this.closeLink.getStyle('width');
			ncw = Number(ncw.substr(0,ncw.length - 2));
			var cw = this.center.style.width;
			cw = Number(cw.substr(0,cw.length - 2)) - ncw - 20;
			this.ncwrapper.style.width = (cw + 'px');
			this.fx.image.start(1);
			break;
		case 4:
			if (this.options.animateCaption){
				// This is not smooth animation in IE 6 with XML prolog.
				// If your site is XHTML strict with XML prolog, disable this option.
				this.fx.bottom.start(0,this.bottom.offsetHeight+10);
				break;
			}
			this.bottomContainer.style.height = (this.bottom.offsetHeight+10)+'px';
		case 5:
			if (this.activeItem){
				this.prevLink.style.display = '';
			}
			if (this.activeItem != (this.items.length - 1)){
				this.nextLink.style.display = '';
			}
			this.step = 0;
		}
	},

	close: function(){
		if (this.step < 0) return;
		this.step = -1;
		this.removeCurrentItem();	// discard content
		for (var f in this.fx) this.fx[f].stop();
		this.center.style.display = this.bottomContainer.style.display = 'none';
		this.fx.overlay.chain(this.setup.pass(false, this)).start(0);
		$$( this.options.elHide ).setStyle('visibility', 'visible'); 		// FUAL
		return false;
	},

	removeCurrentItem: function(){
		if (this.preload){
			if( this.preload.datatype == 'image' ) {
				$('lbImage').remove();
				this.preload.onload = Class.empty;
			}else{
				$(this.iframeId).remove();
			}
			this.preload = null;
		}		
	},

	matchOrDefault: function(str, re, val){
		var hasQuery = str.match(re);
		return hasQuery ? hasQuery[1] : val;
	}

};

window.addEvent('domready', Lightbox.init.bind(Lightbox));
