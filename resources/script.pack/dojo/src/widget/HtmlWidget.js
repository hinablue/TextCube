/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/dojo.provide("dojo.widget.HtmlWidget"),dojo.require("dojo.widget.DomWidget"),dojo.require("dojo.html.util"),dojo.require("dojo.html.display"),dojo.require("dojo.html.layout"),dojo.require("dojo.lang.extras"),dojo.require("dojo.lang.func"),dojo.require("dojo.lfx.toggle"),dojo.declare("dojo.widget.HtmlWidget",dojo.widget.DomWidget,{templateCssPath:null,templatePath:null,lang:"",toggle:"plain",toggleDuration:150,initialize:function(a,b){},postMixInProperties:function(a,b){this.lang===""&&(this.lang=null),this.toggleObj=dojo.lfx.toggle[this.toggle.toLowerCase()]||dojo.lfx.toggle.plain},createNodesFromText:function(a,b){return dojo.html.createNodesFromText(a,b)},destroyRendering:function(a){try{this.bgIframe&&(this.bgIframe.remove(),delete this.bgIframe),!a&&this.domNode&&dojo.event.browser.clean(this.domNode),dojo.widget.HtmlWidget.superclass.destroyRendering.call(this)}catch(b){}},isShowing:function(){return dojo.html.isShowing(this.domNode)},toggleShowing:function(){this.isShowing()?this.hide():this.show()},show:function(){this.isShowing()||(this.animationInProgress=!0,this.toggleObj.show(this.domNode,this.toggleDuration,null,dojo.lang.hitch(this,this.onShow),this.explodeSrc))},onShow:function(){this.animationInProgress=!1,this.checkSize()},hide:function(){!this.isShowing()||(this.animationInProgress=!0,this.toggleObj.hide(this.domNode,this.toggleDuration,null,dojo.lang.hitch(this,this.onHide),this.explodeSrc))},onHide:function(){this.animationInProgress=!1},_isResized:function(a,b){if(!this.isShowing())return!1;var c=dojo.html.getMarginBox(this.domNode),d=a||c.width,e=b||c.height;if(this.width==d&&this.height==e)return!1;this.width=d,this.height=e;return!0},checkSize:function(){!this._isResized()||this.onResized()},resizeTo:function(a,b){dojo.html.setMarginBox(this.domNode,{width:a,height:b}),this.isShowing()&&this.onResized()},resizeSoon:function(){this.isShowing()&&dojo.lang.setTimeout(this,this.onResized,0)},onResized:function(){dojo.lang.forEach(this.children,function(a){a.checkSize&&a.checkSize()})}})