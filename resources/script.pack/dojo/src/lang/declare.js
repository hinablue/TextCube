/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/dojo.provide("dojo.lang.declare"),dojo.require("dojo.lang.common"),dojo.require("dojo.lang.extras"),dojo.lang.declare=function(a,b,c,d){if(dojo.lang.isFunction(d)||!d&&!dojo.lang.isFunction(c)){var e=d;d=c,c=e}var f=[];dojo.lang.isArray(b)&&(f=b,b=f.shift()),c||(c=dojo.evalObjPath(a,!1),c&&!dojo.lang.isFunction(c)&&(c=null));var g=dojo.lang.declare._makeConstructor(),h=b?b.prototype:null;h&&(h.prototyping=!0,g.prototype=new b,h.prototyping=!1),g.superclass=h,g.mixins=f;for(var i=0,j=f.length;i<j;i++)dojo.lang.extend(g,f[i].prototype);g.prototype.initializer=null,g.prototype.declaredClass=a,dojo.lang.isArray(d)?dojo.lang.extend.apply(dojo.lang,[g].concat(d)):dojo.lang.extend(g,d||{}),dojo.lang.extend(g,dojo.lang.declare._common),g.prototype.constructor=g,g.prototype.initializer=g.prototype.initializer||c||function(){};var k=dojo.parseObjPath(a,null,!0);k.obj[k.prop]=g;return g},dojo.lang.declare._makeConstructor=function(){return function(){var a=this._getPropContext(),b=a.constructor.superclass;b&&b.constructor&&(b.constructor==arguments.callee?this._inherited("constructor",arguments):this._contextMethod(b,"constructor",arguments));var c=a.constructor.mixins||[];for(var d=0,e;e=c[d];d++)(e.prototype&&e.prototype.initializer||e).apply(this,arguments);!this.prototyping&&a.initializer&&a.initializer.apply(this,arguments)}},dojo.lang.declare._common={_getPropContext:function(){return this.___proto||this},_contextMethod:function(a,b,c){var d,e=this.___proto;this.___proto=a;try{d=a[b].apply(this,c||[])}catch(f){throw f}finally{this.___proto=e}return d},_inherited:function(a,b){var c=this._getPropContext();do{if(!c.constructor||!c.constructor.superclass)return;c=c.constructor.superclass}while(!(a in c));return dojo.lang.isFunction(c[a])?this._contextMethod(c,a,b):c[a]},inherited:function(a,b){dojo.deprecated("'inherited' method is dangerous, do not up-call! 'inherited' is slated for removal in 0.5; name your super class (or use superclass property) instead.","0.5"),this._inherited(a,b)}},dojo.declare=dojo.lang.declare