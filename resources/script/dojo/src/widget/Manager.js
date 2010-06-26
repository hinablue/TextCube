dojo.provide("dojo.widget.Manager");dojo.require("dojo.lang.array");dojo.require("dojo.lang.func");dojo.require("dojo.event.*");
dojo.widget.manager=new (function(){this.widgets=[];this.widgetIds=[];this.topWidgets={};var i={},h=[];this.getUniqueId=function(a){var b;do b=a+"_"+(i[a]!=undefined?++i[a]:(i[a]=0));while(this.getWidgetById(b));return b};this.add=function(a){this.widgets.push(a);a.extraArgs.id||(a.extraArgs.id=a.extraArgs.ID);if(a.widgetId=="")a.widgetId=a.id?a.id:a.extraArgs.id?a.extraArgs.id:this.getUniqueId(a.ns+"_"+a.widgetType);this.widgetIds[a.widgetId]&&dojo.debug("widget ID collision on ID: "+a.widgetId);
this.widgetIds[a.widgetId]=a};this.destroyAll=function(){for(var a=this.widgets.length-1;a>=0;a--)try{this.widgets[a].destroy(true);delete this.widgets[a]}catch(b){}};this.remove=function(a){if(dojo.lang.isNumber(a)){var b=this.widgets[a].widgetId;delete this.topWidgets[b];delete this.widgetIds[b];this.widgets.splice(a,1)}else this.removeById(a)};this.removeById=function(a){if(!dojo.lang.isString(a)){a=a.widgetId;if(!a){dojo.debug("invalid widget or id passed to removeById");return}}for(var b=0;b<
this.widgets.length;b++)if(this.widgets[b].widgetId==a){this.remove(b);break}};this.getWidgetById=function(a){if(dojo.lang.isString(a))return this.widgetIds[a];return a};this.getWidgetsByType=function(a){var b=a.toLowerCase(),c=a.indexOf(":")<0?function(e){return e.widgetType.toLowerCase()}:function(e){return e.getNamespacedType()},g=[];dojo.lang.forEach(this.widgets,function(e){c(e)==b&&g.push(e)});return g};this.getWidgetsByFilter=function(a,b){var c=[];dojo.lang.every(this.widgets,function(g){if(a(g)){c.push(g);
if(b)return false}return true});return b?c[0]:c};this.getAllWidgets=function(){return this.widgets.concat()};this.getWidgetByNode=function(a){var b=this.getAllWidgets();a=dojo.byId(a);for(var c=0;c<b.length;c++)if(b[c].domNode==a)return b[c];return null};this.byId=this.getWidgetById;this.byType=this.getWidgetsByType;this.byFilter=this.getWidgetsByFilter;this.byNode=this.getWidgetByNode;for(var m={},d=["dojo.widget"],k=0;k<d.length;k++)d[d[k]]=true;this.registerWidgetPackage=function(a){if(!d[a]){d[a]=
true;d.push(a)}};this.getWidgetPackageList=function(){return dojo.lang.map(d,function(a){return a!==true?a:undefined})};this.getImplementation=function(a,b,c,g){if(a=this.getImplementationName(a,g))return b?new a(b):new a};var l=function(a,b){var c=dojo.evalObjPath(b,false);if(c)a:{if(c)for(var g=0,e=h.length,f;g<=e;g++)if(f=g<e?c[h[g]]:c)for(var j in f)if(j.toLowerCase()==a){c=f[j];break a}c=null}else c=null;return c};this.getImplementationName=function(a,b){var c=a.toLowerCase();b=b||"dojo";var g=
m[b]||(m[b]={}),e=g[c];if(e)return e;if(!h.length)for(var f in dojo.render)if(dojo.render[f].capable===true){e=dojo.render[f].prefixes;for(var j=0;j<e.length;j++)h.push(e[j].toLowerCase())}f=dojo.ns.get(b);if(!f){dojo.ns.register(b,b+".widget");f=dojo.ns.get(b)}f&&f.resolve(a);if(e=l(c,f.module))return g[c]=e;if((f=dojo.ns.require(b))&&f.resolver){f.resolve(a);if(e=l(c,f.module))return g[c]=e}dojo.deprecated("dojo.widget.Manager.getImplementationName",'Could not locate widget implementation for "'+
a+'" in "'+f.module+'" registered to namespace "'+f.name+'". Developers must specify correct namespaces for all non-Dojo widgets',"0.5");for(j=0;j<d.length;j++)if(e=l(c,d[j]))return g[c]=e;throw new Error('Could not locate widget implementation for "'+a+'" in "'+f.module+'" registered to namespace "'+f.name+'"');};this.resizing=false;this.onWindowResized=function(){if(!this.resizing)try{this.resizing=true;for(var a in this.topWidgets){var b=this.topWidgets[a];b.checkSize&&b.checkSize()}}catch(c){}finally{this.resizing=
false}};if(typeof window!="undefined"){dojo.addOnLoad(this,"onWindowResized");dojo.event.connect(window,"onresize",this,"onWindowResized")}});
(function(){var i=dojo.widget,h=i.manager,m=dojo.lang.curry(dojo.lang,"hitch",h),d=function(k,l){i[l||k]=m(k)};d("add","addWidget");d("destroyAll","destroyAllWidgets");d("remove","removeWidget");d("removeById","removeWidgetById");d("getWidgetById");d("getWidgetById","byId");d("getWidgetsByType");d("getWidgetsByFilter");d("getWidgetsByType","byType");d("getWidgetsByFilter","byFilter");d("getWidgetByNode","byNode");i.all=function(k){var l=h.getAllWidgets.apply(h,arguments);if(arguments.length>0)return l[k];
return l};d("registerWidgetPackage");d("getImplementation","getWidgetImplementation");d("getImplementationName","getWidgetImplementationName");i.widgets=h.widgets;i.widgetIds=h.widgetIds;i.root=h.root})();
