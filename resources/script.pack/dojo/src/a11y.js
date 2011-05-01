/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/dojo.provide("dojo.a11y"),dojo.require("dojo.uri.*"),dojo.require("dojo.html.common"),dojo.a11y={imgPath:dojo.uri.moduleUri("dojo.widget","templates/images"),doAccessibleCheck:!0,accessible:null,checkAccessible:function(){this.accessible===null&&(this.accessible=!1,this.doAccessibleCheck==!0&&(this.accessible=this.testAccessible()));return this.accessible},testAccessible:function(){this.accessible=!1;if(dojo.render.html.ie||dojo.render.html.mozilla){var a=document.createElement("div");a.style.backgroundImage='url("'+this.imgPath+'/tab_close.gif")',dojo.body().appendChild(a);var b=null;if(window.getComputedStyle){var c=getComputedStyle(a,"");b=c.getPropertyValue("background-image")}else b=a.currentStyle.backgroundImage;var d=!1;b!=null&&(b=="none"||b=="url(invalid-url:)")&&(this.accessible=!0),dojo.body().removeChild(a)}return this.accessible},setCheckAccessible:function(a){this.doAccessibleCheck=a},setAccessibleMode:function(){this.accessible===null&&this.checkAccessible()&&dojo.render.html.prefixes.unshift("a11y");return this.accessible}}