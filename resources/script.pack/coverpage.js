/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
function errorWaitServerResponse(){dlg.setContent('<p class="error-string">'+commonString_error+"</p>");var a=document.createElement("input");a.type="button",a.value=commonString_close,a.className="input-button",a.onclick=function(){window.location.reload();return!1};var b=document.createElement("div");b.className="button-box",b.appendChild(a);var c=dlg.domNode;c.appendChild(b),dlg.setCloseControl(a),dlg.show()}function clearWaitServerResponse(){dlg.hide()}function waitServerResponse(){dlg!=null&&(dlg.setContent('<p class="waiting-string">'+commonString_saving+"</p>"),dlg.show())}function editCoverpagePlugin(a,b){var c=blogURL+"/owner/skin/coverpage/edit?coverpageNumber="+a+"&modulePos="+b+"&ajaxcall=submitCoverpagePlugin("+a+","+b+")"+viewMode,d=new HTTPRequest("GET",c);d.onSuccess=function(){if(dlg!=null){dlg.setContent(this._request.responseText);var a=document.createElement("input");a.type="button",a.value=commonString_cancel,a.className="input-button";var b=dlg.domNode.firstChild;while(b!=null){if(b.tagName!=null&&b.tagName.toLowerCase()=="form"){b=b.firstChild;break}b=b.nextSibling}while(b!=null){if(b.className!=null&&b.className.toLowerCase()=="button-box"){b.appendChild(a);break}b=b.nextSibling}dlg.setCloseControl(a),dlg.show()}},d.onError=function(){globalChker=!1},d.onVerify=function(){return!0},d.send()}function decorateDragPanel(a){var b=a.coverpageNumber,c=a.modulePos,d=a.firstChild;while(d!=null){if(d.tagName!=null&&d.tagName.toLowerCase()=="h4")break;d=d.nextSibling}if(d!=null){var e=document.createElement("a");e.className="module-close",e.href=blogURL+"/owner/skin/coverpage/delete/?coverpageNumber="+b+"&modulePos="+c+viewMode,e.title=decorateDragPanelString_deleteTitle,e.innerHTML='<img src="'+servicePath+adminSkin+'/image/img_delete_module.gif" border="0" alt="'+commonString_delete+'" />',d.nextSibling!=null?a.insertBefore(e,d.nextSibling):a.appendChild(e)}var d=a.firstChild;while(d!=null)d.tagName!=null&&d.tagName.toLowerCase()=="div"&&(d.style.clear="both"),d=d.nextSibling}function previewPlugin(a,b){var c=blogURL+"/owner/skin/coverpage/preview?coverpageNumber="+a+"&modulePos="+b+previewMode,d=new HTTPRequest("GET",c);d.coverpage=a,d.modulepos=b,d.onSuccess=function(){var a=document.getElementById("coverpage-ul-"+this.coverpage);a!=null&&(a=a.firstChild);while(a!=null){if(a.tagName!=null&&a.tagName.toLowerCase()=="li"){if(this.modulepos<=0)break;this.modulepos--}a=a.nextSibling}a!=null&&(a=a.lastChild);while(a!=null){if(a.tagName!=null&&a.tagName.toLowerCase()=="div")break;a=a.previousSibling}a!=null&&(a.innerHTML=this._request.responseText)},d.onError=function(){globalChker=!1},d.onVerify=function(){return!0},d.send()}function submitCoverpagePlugin(a,b){var c=dlg.domNode.firstChild;while(c!=null){if(c.tagName!=null&&c.tagName.toLowerCase()=="form")break;c=c.nextSibling}if(c!=null){var d=blogURL+"/owner/skin/coverpage/setPlugin?coverpageNumber="+a+"&modulePos="+b+"&ajaxcall=true"+viewMode,e="";c=c.firstChild;while(c!=null){if(c.className!=null&&c.className.toLowerCase()=="field-box"){c=c.firstChild;break}c=c.nextSibling}while(c!=null){if(c.tagName!=null&&c.tagName.toLowerCase()=="div"){var f=c.firstChild;while(f!=null)f.tagName!=null&&f.tagName.toLowerCase()=="input"&&f.type.toLowerCase()=="text"?d+="&"+encodeURIComponent(f.name)+"="+encodeURIComponent(f.value):f.tagName!=null&&f.tagName.toLowerCase()=="textarea"&&(e.length>0&&(e+="&"),e+=f.name+"="+encodeURIComponent(f.value)),f=f.nextSibling}c=c.nextSibling}var g=new HTTPRequest("POST",d);g.coverpage=a,g.modulepos=b,g.onSuccess=function(){previewPlugin(this.coverpage,this.modulepos);return!0},g.onError=function(){errorWaitServerResponse(),globalChker=!1},g.onVerify=function(){return!0},g.send(e)}dlg.hide()}djConfig.parseWidgets=!1,dojo.require("dojo.dnd.HtmlDragAndDrop"),dojo.require("dojo.widget.Parse"),dojo.require("dojo.widget.Dialog"),DragPanel=function(a,b){dojo.dnd.HtmlDragSource.call(this,a,b),this.dragClass="ajax-floating-panel",this.opacity=.9,this.domNode.coverpageNumber!=null&&decorateDragPanel(this.domNode)},dojo.inherits(DragPanel,dojo.dnd.HtmlDragSource),DragPanelAdd=function(a,b){dojo.dnd.HtmlDragSource.call(this,a,b),this.dragClass="ajax-floating-panel",this.opacity=.9},dojo.inherits(DragPanelAdd,dojo.dnd.HtmlDragSource),DropPanel=function(a,b){dojo.dnd.HtmlDropTarget.call(this,a,b)},dojo.inherits(DropPanel,dojo.dnd.HtmlDropTarget),DropDeletePanel=function(a,b){dojo.dnd.HtmlDropTarget.call(this,a,b)},dojo.inherits(DropDeletePanel,dojo.dnd.HtmlDropTarget);var globalChker=!0,globalNewNodeCounter=0;dojo.lang.extend(DropPanel,{onDrop:function(a){if(a.dragObject.domNode.ajaxtype=="register"&&a.dragObject.domNode.moduleCategory=="plugin"){var b=document.createElement(a.dragObject.domNode.tagName);b.id="newDragPanel_"+globalNewNodeCounter++,b.className="coverpage-module coverpage-plugin-module",b.ajaxtype="register",b.moduleCategory=a.dragObject.domNode.moduleCategory,b.identifier=a.dragObject.domNode.identifier,b.innerHTML=a.dragObject.domNode.innerHTML,b.hasPropertyEdit=a.dragObject.domNode.hasPropertyEdit,a.dragObject.domNode=b,new DragPanel(b,["coverpage"])}a.dragObject.domNode.ajaxtype!="register"||a.dragObject.domNode.moduleCategory!="coverpage_element",this.parentMethod=DropPanel.superclass.onDrop;var c=this.parentMethod(a);delete this.parentMethod;if(c==!0&&globalChker==!0){var d=this.domNode.coverpage,e=0,f=a.dragObject.domNode.previousSibling;while(f!=null){if(f.nodeType!=3&&f.className.indexOf("coverpage-module")!=-1)break;f=f.previousSibling}f!=null&&(e=f.modulePos+1);if(a.dragObject.domNode.ajaxtype=="reorder"){var g=a.dragObject.domNode.coverpageNumber,h=a.dragObject.domNode.modulePos;a.dragObject.domNode.coverpageNumber=d;var i=blogURL+"/owner/skin/coverpage/order?coverpageNumber="+g+"&targetCoverpageNumber="+d+"&modulePos="+h+"&targetPos="+e+viewMode,j=new HTTPRequest("POST",i);j.onSuccess=function(){clearWaitServerResponse()},j.onError=function(){globalChker=!1,errorWaitServerResponse()},j.onVerify=function(){return!0},j.send(),waitServerResponse()}else if(a.dragObject.domNode.ajaxtype=="register"){a.dragObject.domNode.coverpageNumber=d,a.dragObject.domNode.ajaxtype="reorder";var i=blogURL+"/owner/skin/coverpage/register?coverpageNumber="+d+"&modulePos="+e+"&moduleId="+a.dragObject.domNode.identifier+viewMode,j=new HTTPRequest("POST",i);j.coverpage=d,j.modulepos=e,j.moduleCategory=a.dragObject.domNode.moduleCategory,j.onSuccess=function(){clearWaitServerResponse(),this.moduleCategory=="plugin"&&previewPlugin(this.coverpage,this.modulepos),decorateDragPanel(a.dragObject.domNode)},j.onError=function(){globalChker=!1,errorWaitServerResponse()},j.onVerify=function(){return!0},j.send(),waitServerResponse()}else alert(a.dragObject.domNode.ajaxtype);reordering()}return c},createDropIndicator:function(){this.parentMethod=DropPanel.superclass.createDropIndicator;var retVal=this.parentMethod();delete this.parentMethod;with(this.dropIndicator.style)borderTopWidth="5px",borderTopColor="silver",borderTopStyle="solid";return retVal}}),dojo.lang.extend(DropDeletePanel,{onDrop:function(a){if(a.dragObject.domNode.ajaxtype=="register"){this.dropIndicator&&(dojo.html.removeNode(this.dropIndicator),delete this.dropIndicator);return!1}var b=a.dragObject.domNode.coverpageNumber,c=a.dragObject.domNode.modulePos;this.parentMethod=DropPanel.superclass.onDrop;var d=this.parentMethod(a);delete this.parentMethod,window.location.href=blogURL+"/owner/skin/coverpage/delete?coverpageNumber="+b+"&modulePos="+c+viewMode;return d},createDropIndicator:function(){this.parentMethod=DropPanel.superclass.createDropIndicator;var retVal=this.parentMethod();delete this.parentMethod;with(this.dropIndicator.style)borderTopWidth="5px",borderTopColor="silver",borderTopStyle="solid";return retVal}});var dlg;dojo.widget.defineWidget("dojo.widget.popupWindow",dojo.widget.Dialog,{templatePath:"",loadContents:function(){this.containerNode=this.domNode;return},setContent:function(a){this.domNode.innerHTML=a},placeModalDialog:function(){var scroll_offset=dojo.html.getScroll().offset,viewport_size=dojo.html.getViewport(),mb=dojo.html.getMarginBox(this.containerNode);mb.width<200&&(mb.width=200),mb.height<200&&(mb.height=200);var x=scroll_offset.x+(viewport_size.width-mb.width)/2,y=scroll_offset.y+(viewport_size.height-mb.height)/2;with(this.domNode.style)left=x+"px",top=y+"px"}})