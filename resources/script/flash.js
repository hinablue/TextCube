var sUserAgent=navigator.userAgent,fAppVersion=parseFloat(navigator.appVersion),isOpera=sUserAgent.indexOf("Opera")>-1,isIE=sUserAgent.indexOf("compatible")>-1&&sUserAgent.indexOf("MSIE")>-1&&!isOpera;function isExplore(){return sUserAgent.indexOf("compatible")>-1&&sUserAgent.indexOf("MSIE")>-1&&!isOpera}function AC_AddExtension(a,d){return a.indexOf("?")!=-1?a.replace(/\?/,d+"?"):a+d}
function AC_Generateobj(a,d,g){var b="";if(isExplore()){b+="<object ";for(var c in a)b+=c+'="'+a[c]+'" ';b+=">";for(c in d)b+='<param name="'+c+'" value="'+d[c]+'" /> '}b+="<embed ";for(c in g)b+=c+'="'+g[c]+'" ';b+=" ></embed>";if(isIE)b+="</object>";document.write(b)}
function AC_GenerateobjNotWriteGetString(a,d,g){var b="";if(isExplore()){b+="<object ";for(var c in a)b+=c+'="'+a[c]+'" ';b+=">";for(c in d)b+='<param name="'+c+'" value="'+d[c]+'" /> '}b+="<embed ";for(c in g)b+=c+'="'+g[c]+'" ';b+=" ></embed>";if(isIE)b+="</object>";return b}function AC_FL_RunContent(){var a=AC_GetArgs(arguments,".swf","movie","clsid:d27cdb6e-ae6d-11cf-96b8-444553540000","application/x-shockwave-flash");AC_Generateobj(a.objAttrs,a.params,a.embedAttrs)}
function AC_FL_RunContentNotWriteGetString(){var a=AC_GetArgs(arguments,".swf","movie","clsid:d27cdb6e-ae6d-11cf-96b8-444553540000","application/x-shockwave-flash");return AC_GenerateobjNotWriteGetString(a.objAttrs,a.params,a.embedAttrs)}function insertObject(a,d){document.getElementById(a).innerHTML=d}
function AC_GetArgs(a,d,g,b,c){var f={};f.embedAttrs={};f.params={};f.objAttrs={};for(var e=0;e<a.length;e+=2)switch(a[e].toLowerCase()){case "classid":break;case "pluginspage":f.embedAttrs[a[e]]=a[e+1];break;case "src":case "movie":a[e+1]=AC_AddExtension(a[e+1],d);f.embedAttrs.src=a[e+1];f.params[g]=a[e+1];break;case "onafterupdate":case "onbeforeupdate":case "onblur":case "oncellchange":case "onclick":case "ondblClick":case "ondrag":case "ondragend":case "ondragenter":case "ondragleave":case "ondragover":case "ondrop":case "onfinish":case "onfocus":case "onhelp":case "onmousedown":case "onmouseup":case "onmouseover":case "onmousemove":case "onmouseout":case "onkeypress":case "onkeydown":case "onkeyup":case "onload":case "onlosecapture":case "onpropertychange":case "onreadystatechange":case "onrowsdelete":case "onrowenter":case "onrowexit":case "onrowsinserted":case "onstart":case "onscroll":case "onbeforeeditfocus":case "onactivate":case "onbeforedeactivate":case "ondeactivate":case "type":case "codebase":f.objAttrs[a[e]]=
a[e+1];break;case "width":case "height":case "align":case "vspace":case "hspace":case "class":case "title":case "accesskey":case "name":case "id":case "tabindex":f.embedAttrs[a[e]]=f.objAttrs[a[e]]=a[e+1];break;default:f.embedAttrs[a[e]]=f.params[a[e]]=a[e+1]}f.objAttrs.classid=b;if(c)f.embedAttrs.type=c;return f}
function ExternalInterfaceManager(){this.registerMovie=function(a){if(!window.fakeMovies)window.fakeMovies=[];window.fakeMovies[window.fakeMovies.length]=a};this.initialize=function(){if(document.all)if(window.fakeMovies){for(i=0;i<window.fakeMovies.length;i++)window[window.fakeMovies[i]]={};STD.addEventListener(window);window.addEventListener("load",initializeExternalInterface,false)}}}
function initializeExternalInterface(){for(i=0;i<window.fakeMovies.length;i++){var a=window.fakeMovies[i],d=window[a],g=document.getElementById(a);for(var b in d)g[b]=function(){flashFunction='<invoke name="'+b.toString()+'" returntype="javascript">'+__flash__argumentsToXML(arguments,0)+"</invoke>";this.CallFunction(flashFunction)};window[a]=g}}function getVariableFromFlash(a,d){var g="";return g=document.all?document.all[a].getVariable(d):document[a].GetVariable(d)};
