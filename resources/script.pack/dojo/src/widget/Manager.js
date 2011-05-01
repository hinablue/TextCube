/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/dojo.provide("dojo.widget.Manager"),dojo.require("dojo.lang.array"),dojo.require("dojo.lang.func"),dojo.require("dojo.event.*"),dojo.widget.manager=new function(){function f(){for(var a in dojo.render)if(dojo.render[a].capable===!0){var c=dojo.render[a].prefixes;for(var d=0;d<c.length;d++)b.push(c[d].toLowerCase())}}this.widgets=[],this.widgetIds=[],this.topWidgets={};var a={},b=[];this.getUniqueId=function(b){var c;do c=b+"_"+(a[b]!=undefined?++a[b]:a[b]=0);while(this.getWidgetById(c));return c},this.add=function(a){this.widgets.push(a),a.extraArgs.id||(a.extraArgs.id=a.extraArgs.ID),a.widgetId==""&&(a.id?a.widgetId=a.id:a.extraArgs.id?a.widgetId=a.extraArgs.id:a.widgetId=this.getUniqueId(a.ns+"_"+a.widgetType)),this.widgetIds[a.widgetId]&&dojo.debug("widget ID collision on ID: "+a.widgetId),this.widgetIds[a.widgetId]=a},this.destroyAll=function(){for(var a=this.widgets.length-1;a>=0;a--)try{this.widgets[a].destroy(!0),delete this.widgets[a]}catch(b){}},this.remove=function(a){if(dojo.lang.isNumber(a)){var b=this.widgets[a].widgetId;delete this.topWidgets[b],delete this.widgetIds[b],this.widgets.splice(a,1)}else this.removeById(a)},this.removeById=function(a){if(!dojo.lang.isString(a)){a=a.widgetId;if(!a){dojo.debug("invalid widget or id passed to removeById");return}}for(var b=0;b<this.widgets.length;b++)if(this.widgets[b].widgetId==a){this.remove(b);break}},this.getWidgetById=function(a){if(dojo.lang.isString(a))return this.widgetIds[a];return a},this.getWidgetsByType=function(a){var b=a.toLowerCase(),c=a.indexOf(":")<0?function(a){return a.widgetType.toLowerCase()}:function(a){return a.getNamespacedType()},d=[];dojo.lang.forEach(this.widgets,function(a){c(a)==b&&d.push(a)});return d},this.getWidgetsByFilter=function(a,b){var c=[];dojo.lang.every(this.widgets,function(d){if(a(d)){c.push(d);if(b)return!1}return!0});return b?c[0]:c},this.getAllWidgets=function(){return this.widgets.concat()},this.getWidgetByNode=function(a){var b=this.getAllWidgets();a=dojo.byId(a);for(var c=0;c<b.length;c++)if(b[c].domNode==a)return b[c];return null},this.byId=this.getWidgetById,this.byType=this.getWidgetsByType,this.byFilter=this.getWidgetsByFilter,this.byNode=this.getWidgetByNode;var c={},d=["dojo.widget"];for(var e=0;e<d.length;e++)d[d[e]]=!0;this.registerWidgetPackage=function(a){d[a]||(d[a]=!0,d.push(a))},this.getWidgetPackageList=function(){return dojo.lang.map(d,function(a){return a!==!0?a:undefined})},this.getImplementation=function(a,b,c,d){var e=this.getImplementationName(a,d);if(e){var f=b?new e(b):new e;return f}};var g=function(a,c){if(!c)return null;for(var d=0,e=b.length,f;d<=e;d++){f=d<e?c[b[d]]:c;if(!f)continue;for(var g in f)if(g.toLowerCase()==a)return f[g]}return null},h=function(a,b){var c=dojo.evalObjPath(b,!1);return c?g(a,c):null};this.getImplementationName=function(a,e){var g=a.toLowerCase();e=e||"dojo";var i=c[e]||(c[e]={}),j=i[g];if(j)return j;b.length||f();var k=dojo.ns.get(e);k||(dojo.ns.register(e,e+".widget"),k=dojo.ns.get(e)),k&&k.resolve(a),j=h(g,k.module);if(j)return i[g]=j;k=dojo.ns.require(e);if(k&&k.resolver){k.resolve(a),j=h(g,k.module);if(j)return i[g]=j}dojo.deprecated("dojo.widget.Manager.getImplementationName",'Could not locate widget implementation for "'+a+'" in "'+k.module+'" registered to namespace "'+k.name+'". '+"Developers must specify correct namespaces for all non-Dojo widgets","0.5");for(var l=0;l<d.length;l++){j=h(g,d[l]);if(j)return i[g]=j}throw new Error('Could not locate widget implementation for "'+a+'" in "'+k.module+'" registered to namespace "'+k.name+'"')},this.resizing=!1,this.onWindowResized=function(){if(!this.resizing)try{this.resizing=!0;for(var a in this.topWidgets){var b=this.topWidgets[a];b.checkSize&&b.checkSize()}}catch(c){}finally{this.resizing=!1}},typeof window!="undefined"&&(dojo.addOnLoad(this,"onWindowResized"),dojo.event.connect(window,"onresize",this,"onWindowResized"))},function(){var a=dojo.widget,b=a.manager,c=dojo.lang.curry(dojo.lang,"hitch",b),d=function(b,d){a[d||b]=c(b)};d("add","addWidget"),d("destroyAll","destroyAllWidgets"),d("remove","removeWidget"),d("removeById","removeWidgetById"),d("getWidgetById"),d("getWidgetById","byId"),d("getWidgetsByType"),d("getWidgetsByFilter"),d("getWidgetsByType","byType"),d("getWidgetsByFilter","byFilter"),d("getWidgetByNode","byNode"),a.all=function(a){var c=b.getAllWidgets.apply(b,arguments);if(arguments.length>0)return c[a];return c},d("registerWidgetPackage"),d("getImplementation","getWidgetImplementation"),d("getImplementationName","getWidgetImplementationName"),a.widgets=b.widgets,a.widgetIds=b.widgetIds,a.root=b.root}()