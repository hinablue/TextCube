/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/dojo.provide("dojo.lfx.toggle"),dojo.require("dojo.lfx.*"),dojo.lfx.toggle.plain={show:function(a,b,c,d){dojo.html.show(a),dojo.lang.isFunction(d)&&d()},hide:function(a,b,c,d){dojo.html.hide(a),dojo.lang.isFunction(d)&&d()}},dojo.lfx.toggle.fade={show:function(a,b,c,d){dojo.lfx.fadeShow(a,b,c,d).play()},hide:function(a,b,c,d){dojo.lfx.fadeHide(a,b,c,d).play()}},dojo.lfx.toggle.wipe={show:function(a,b,c,d){dojo.lfx.wipeIn(a,b,c,d).play()},hide:function(a,b,c,d){dojo.lfx.wipeOut(a,b,c,d).play()}},dojo.lfx.toggle.explode={show:function(a,b,c,d,e){dojo.lfx.explode(e||{x:0,y:0,width:0,height:0},a,b,c,d).play()},hide:function(a,b,c,d,e){dojo.lfx.implode(a,e||{x:0,y:0,width:0,height:0},b,c,d).play()}}