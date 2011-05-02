/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
//<![CDATA[
function onClipBoard(a){alert(a?messages.trackbackUrlCopied:messages.operationFailed)}function copyUrl(a,b){try{window.clipboardData.setData("Text",a),window.alert(messages.trackbackUrlCopied)}catch(c){s=window.getSelection();var d=document.createRange();d.setStartBefore(b),d.setEndAfter(b),s.addRange(d)}}function thisMovie(a){return navigator.appName.indexOf("Microsoft")!=-1?window[a]:document[a]}function reloadEntry(a){var b=document.getElementById("entry"+a+"password");b||(passwords=document.getElementsByName("entry"+a+"password"),passwords!=null&&passwords.Count>0&&(b=passwords));!b||(document.cookie="GUEST_PASSWORD="+escape(b.value)+";path="+servicePath,window.location.href=window.location.href)}function deleteEntry(a){if(!!doesHaveOwnership){if(!confirm(messages.confirmEntryDelete))return;var b=new HTTPRequest("GET",blogURL+"/owner/entry/delete/"+a),c=blogURL;if(blogURL==null||blogURL.length<=0)c="/";b.onSuccess=function(){window.location.href=c},b.send()}}function changeVisibility(a,b){if(!!doesHaveOwnership){var c=new HTTPRequest("GET",blogURL+"/owner/entry/visibility/"+a+"?visibility="+b);c.onSuccess=function(){window.location.reload()},c.send()}}function deleteTrackback(a,b){if(!doesHaveOwnership)alert(messages.operationFailed);else{if(!confirm(messages.confirmTrackbackDelete))return;var c=new HTTPRequest("GET",blogURL+"/trackback/delete/"+a);c.onSuccess=function(){document.getElementById("entry"+b+"Trackback").innerHTML=this.getText("/response/trackbackList"),document.getElementById("entry"+b+"Trackback").style.display="block";try{obj=document.getElementById("trackbackCount"+b),obj!=null&&(obj.innerHTML=this.getText("/response/trackbackCount"))}catch(a){}try{obj=document.getElementById("recentTrackbacks"),obj!=null&&(obj.innerHTML=this.getText("/response/recentTrackbacks"))}catch(a){}},c.onError=function(){alert(messages.operationFailed)},c.send()}}function sendTrackback(a){openCenteredWindow(blogURL+"/trackback/send/"+a,"tatter",580,400)}function guestbookComment(a){openCenteredWindow(blogURL+"/comment/comment/"+a,"tatter",460,360)}function editEntry(a,b){openCenteredWindow(blogURL+"/owner/entry/edit/"+a+"?popupEditor&returnURL="+b,"tatter",1020,550,!0)}function updateStream(a,b,c){Ocontent=document.getElementById("line-content"),Pcontent=document.getElementById("line-more-page"),c=="top"?Ocontent.innerHTML=a+Ocontent.innerHTML:Ocontent.innerHTML=Ocontent.innerHTML+a,Pcontent.innerHTML=b;return!0}function getMoreLineStream(a,b,c){var d=new HTTPRequest("POST",blogURL+"/stream/");d.onSuccess=function(){contentView=this.getText("/response/contentView"),buttonView=this.getText("/response/buttonView"),a==1&&b==1&&(buttonView=""),updateStream(contentView,buttonView,c)},d.onError=function(){},d.send("page="+a+"&lines="+b)}function commentComment(a){openCenteredWindow(blogURL+"/comment/comment/"+a,"tatter",460,550)}function modifyComment(a){openCenteredWindow(blogURL+"/comment/modify/"+a,"tatter",460,400)}function deleteComment(a){openCenteredWindow(blogURL+"/comment/delete/"+a,"tatter",460,400)}function openCenteredWindow(a,b,c,d,e){e=e||!1?1:0;try{openWindow!=""&&openWindow.close()}catch(f){}openWindow=window.open(a,b,"width="+c+",height="+d+",top="+(screen.height/2-d/2)+",left="+(screen.width/2-c/2)+",location=0,menubar=0,resizable=1,scrollbars="+e+",status=0,toolbar=0"),openWindow.focus();return openWindow}function loadComment(a,b,c,d){var e;if(d==!0){e=1;var f=document.getElementById("entry"+a+"CommentList")}else{e=0;var f=document.getElementById("entry"+a+"Comment")}var g=new HTTPRequest("POST",blogURL+"/comment/load/"+a);if(!c&&f.style.display=="none"||c)g.onSuccess=function(){PM.removeRequest(this),f.innerHTML=this.getText("/response/commentBlock");if(typeof loadCommentCallback=="object"||typeof loadCommentCallback=="array")for(var e=0;e<loadCommentCallback.length;e++)typeof loadCommentCallback[e]=="function"&&loadCommentCallback[e].call(this,a,b,c,d)},g.onError=function(){PM.removeRequest(this),PM.showErrorMessage("Loading Failed.","center","bottom")},PM.addRequest(g,"Loading Comments..."),g.send("&page="+b+"&listOnly="+e);c||(f.style.display=f.style.display=="none"?"block":"none")}function recallLastComment(a,b){alert("Not yet supported.");var c=findFormObject(a);if(!c)return!1;var d=c.action.split("/");d.pop(),d.pop(),d=d.join("/"),d+="/recall?__T__="+(new Date).getTime();var e=new HTTPRequest("POST",d);e.onSuccess=function(){},e.onError=function(){alert(this.getText("/response/description"))}}function addCommentWithOpenIDAuth(a,b){if(!a)return!1;form=document.createElement("form");var c=a.action.split("/");c.pop(),c.pop(),form.action=c.join("/"),form.action+="/addopenid/"+b+"?__T__="+(new Date).getTime(),form.method="post";var d;d=document.createElement("input"),d.type="hidden",d.name="key",d.value=commentKey,form.appendChild(d),d=document.createElement("input"),d.type="hidden",d.name="requestURI",d.value=document.location.href,form.appendChild(d),tempComment="comment_"+b,tempHomepage="homepage_"+b,tempSecret="secret_"+b;for(i=0;i<a.elements.length;i++){if(a.elements[i].disabled==!0)continue;var e="",f="";if(a.elements[i].tagName.toLowerCase()=="input"){switch(a.elements[i].type){case"checkbox":case"radio":a.elements[i].checked==!0&&(a.elements[i].name==tempSecret?e=a.elements[i].name:a.elements[i].id==tempSecret?e=a.elements[i].id:a.elements[i].name!=""?e=a.elements[i].name+"_"+b:a.elements[i].id!=""&&(e=a.elements[i].id));break;case"text":case"password":case"hidden":case"button":case"submit":a.elements[i].name==tempHomepage?e=a.elements[i].name:a.elements[i].id==tempHomepage?e=a.elements[i].id:a.elements[i].name!=""?e=a.elements[i].name+"_"+b:a.elements[i].id!=""&&(e=a.elements[i].id)}e&&(f=a.elements[i].value)}else a.elements[i].tagName.toLowerCase()=="select"?(num=a.elements[i].selectedIndex,a.elements[i].name!=""?(e=a.elements[i].name+"_"+b,f=a.elements[i].options[num].value):a.elements[i].id!=""&&(e=a.elements[i].id,f=a.elements[i].options[num].value)):a.elements[i].tagName.toLowerCase()=="textarea"&&(a.elements[i].name==tempComment?(e=a.elements[i].name,f=a.elements[i].value):a.elements[i].name!=""?(e=a.elements[i].name+"_"+b,f=a.elements[i].value):a.elements[i].id!=""&&(e=a.elements[i].id,f=a.elements[i].value));if(!e)continue;d=document.createElement("input"),d.type="hidden",d.name=e,d.value=f,form.appendChild(d)}document.body.appendChild(form),form.submit()}function addComment(a,b){if(commentSavingNow==!0){alert(messages.onSaving);return!1}var c=findFormObject(a);if(!c)return!1;if(c.comment_type!=undefined&&c.comment_type[0].checked&&c.comment_type[0].value=="openid")return addCommentWithOpenIDAuth(c,b);var d=new HTTPRequest("POST",c.action);d.onSuccess=function(){PM.removeRequest(this),commentSavingNow=!1,document.getElementById("entry"+b+"Comment").innerHTML=this.getText("/response/commentBlock"),getObject("recentComments")!=null&&(document.getElementById("recentComments").innerHTML=this.getText("/response/recentCommentBlock")),getObject("commentCount"+b)!=null&&(document.getElementById("commentCount"+b).innerHTML=this.getText("/response/commentView")),getObject("commentCountOnRecentEntries"+b)!=null&&(document.getElementById("commentCountOnRecentEntries"+b).innerHTML="("+this.getText("/response/commentCount")+")");if(typeof addCommentCallback=="object"||typeof addCommentCallback=="array")for(var c=0;c<addCommentCallback.length;c++)typeof addCommentCallback[c]=="function"&&addCommentCallback[c].call(this,a,b)},d.onError=function(){PM.removeRequest(this),commentSavingNow=!1,alert(this.getText("/response/description"))};var e="key="+commentKey;tempComment="comment_"+b,tempHomepage="homepage_"+b,tempName="name_"+b,tempPassword="password_"+b,tempSecret="secret_"+b;for(i=0;i<c.elements.length;i++){e!=""?linker="&":linker="";if(c.elements[i].disabled==!0)continue;if(c.elements[i].tagName.toLowerCase()=="input")switch(c.elements[i].type){case"checkbox":case"radio":c.elements[i].checked==!0&&(c.elements[i].name==tempSecret?e+=linker+c.elements[i].name+"="+encodeURIComponent(c.elements[i].value):c.elements[i].id==tempSecret?e+=linker+c.elements[i].id+"="+encodeURIComponent(c.elements[i].value):c.elements[i].name!=""?e+=linker+c.elements[i].name+"_"+b+"="+encodeURIComponent(c.elements[i].value):c.elements[i].id!=""&&(e+=linker+c.elements[i].id+"="+encodeURIComponent(c.elements[i].value)));break;case"text":case"password":case"hidden":case"button":case"submit":c.elements[i].name==tempName?e+=linker+c.elements[i].name+"="+encodeURIComponent(c.elements[i].value):c.elements[i].id==tempName?e+=linker+c.elements[i].id+"="+encodeURIComponent(c.elements[i].value):c.elements[i].name==tempPassword?e+=linker+c.elements[i].name+"="+encodeURIComponent(c.elements[i].value):c.elements[i].id==tempPassword?e+=linker+c.elements[i].id+"="+encodeURIComponent(c.elements[i].value):c.elements[i].name==tempHomepage?e+=linker+c.elements[i].name+"="+encodeURIComponent(c.elements[i].value):c.elements[i].id==tempHomepage?e+=linker+c.elements[i].id+"="+encodeURIComponent(c.elements[i].value):c.elements[i].name!=""?e+=linker+c.elements[i].name+"_"+b+"="+encodeURIComponent(c.elements[i].value):c.elements[i].id!=""&&(e+=linker+c.elements[i].id+"="+encodeURIComponent(c.elements[i].value))}else c.elements[i].tagName.toLowerCase()=="select"?(num=c.elements[i].selectedIndex,c.elements[i].name!=""?e+=linker+c.elements[i].name+"_"+b+"="+encodeURIComponent(c.elements[i].options[num].value):c.elements[i].id!=""&&(e+=linker+c.elements[i].id+"="+encodeURIComponent(c.elements[i].options[num].value))):c.elements[i].tagName.toLowerCase()=="textarea"&&(c.elements[i].name==tempComment?e+=linker+c.elements[i].name+"="+encodeURIComponent(c.elements[i].value):c.elements[i].name!=""?e+=linker+c.elements[i].name+"_"+b+"="+encodeURIComponent(c.elements[i].value):c.elements[i].id!=""&&(e+=linker+c.elements[i].id+"="+encodeURIComponent(c.elements[i].value)))}commentSavingNow=!0,PM.addRequest(d,"Saving Comments..."),d.send(e)}function processShortcut(a){a=STD.event(a);if(!(a.altKey||a.ctrlKey||a.metaKey)){switch(a.target.nodeName){case"INPUT":case"SELECT":case"TEXTAREA":return}switch(a.keyCode){case 81:window.location=blogURL+"/owner";break;case 82:isReaderEnabled&&(window.location=blogURL+"/owner/network/reader");break;case 84:isReaderEnabled&&(window.location=blogURL+"/owner/network/reader/?forceRefresh");break;case 65:case 72:case 80:prevURL&&(window.location=prevURL);break;case 83:case 76:case 78:nextURL&&(window.location=nextURL);break;case 74:window.scrollBy(0,100);break;case 75:window.scrollBy(0,-100);break;case 90:window.location="#recentEntries";break;case 88:window.location="#recentComments";break;case 67:window.location="#recentTrackbacks"}}}function searchBlog(){var a=document.getElementById("TTSearchForm");a&&a.search&&a.search.value.trim()!=""&&(window.location=blogURL+"/search/"+looseURIEncode(a.search.value));return!1}function looseURIEncode(a){a=a.replace(new RegExp("%","g"),"%25"),a=a.replace(new RegExp("\\?","g"),"%3F"),a=a.replace(new RegExp("#","g"),"%23");return a}function preventEnter(a){a||(a=window.event);if(a.keyCode==13){a.returnValue=!1,a.cancelBubble=!0;try{a.preventDefault()}catch(b){}return!1}return!0}function showMessage(a){PM.showMessage(""+a,"right","bottom")}function makeQueryStringByForm(a){queryString="",tempForm=document.getElementById(a);for(i=0;i<tempForm.elements.length;i++){queryString!=""?linker="&":linker="";if(tempForm.elements[i].disabled==!0)continue;if(tempForm.elements[i].tagName.toLowerCase()=="input")switch(tempForm.elements[i].type){case"checkbox":case"radio":tempForm.elements[i].checked==!0&&(queryString+=linker+tempForm.elements[i].name+"="+tempForm.elements[i].value);break;case"text":case"password":queryString+=linker+tempForm.elements[i].name+"="+tempForm.elements[i].value;break;case"file":tempForm.elements[i].value!=""&&(queryString+=linker+tempForm.elements[i].name+"="+tempForm.elements[i].value)}else tempForm.elements[i].tagName.toLowerCase()=="select"?(num=tempForm.elements[i].selectedIndex,queryString+=linker+tempForm.elements[i].name+"="+tempForm.elements[i].options[num].value):tempForm.elements[i].tagName.toLowerCase()=="textarea"&&(queryString+=linker+tempForm.elements[i].name+"="+tempForm.elements[i].value)}return queryString}function removeItselfById(a){document.getElementById(a).parentNode.removeChild(document.getElementById(a))}function getParentByTagName(a,b){while(b.tagName!=a.toUpperCase())b=b.parentNode;return b}function toggleMoreLess(a,b,c,d){oMore=document.getElementById("more"+b),oContent=document.getElementById("content"+b),c.Length==0&&(c="more..."),d.Length==0&&(d="less...");if(oContent.style.display=="none"){oContent.style.display="block",oMore.className="moreless_top",a.innerHTML=d,oLess=document.createElement("P"),oLess.id="less"+b,oLess.className="moreless_bottom";var e=c.replace(/&/g,"&amp;"),f=d.replace(/&/g,"&amp;");oLess.innerHTML='<span style="cursor: pointer;" onclick="toggleMoreLess(this, \''+b+"', '"+e+"', '"+f+"'); return false;\">"+d+"</span>",after=oContent.nextSibling,oContent.parentNode.insertBefore(oLess,after)}else oContent.style.display="none",oMore.className="moreless_fold",oMore.childNodes[0].innerHTML=c,oLess=document.getElementById("less"+b),oContent.parentNode.removeChild(oLess)}function getTagChunks(a,b,c){var d=[],e=pos2=0;while((e=a.indexOfCaseInsensitive(new RegExp("<"+b+"\\s","i"),pos2))>-1){var f="";do{if((pos2=a.indexOfCaseInsensitive(new RegExp("</"+b,"i"),Math.max(e,pos2)))==-1)return d;pos2+=b.length+3,f=a.substring(e,pos2)}while(f!=""&&f.count(new RegExp("<"+b+"\\s","gi"))!=f.count(new RegExp("</"+b,"gi")));typeof c=="function"&&(f=c(f)),d[d.length]=f}return d}function writeCode2(a,b){b==undefined?document.write(a):document.getElementById(b).innerHTML=a}function writeCode(a,b){a=a.replace('src="','src="http://'+document.domain+(document.location.port?":"+document.location.port:"")),b==undefined?document.write(a):document.getElementById(b).innerHTML=a}function getEmbedCode(a,b,c,d,e,f,g,h,i,j,k,l){try{if(a==undefined||b==undefined||c==undefined)return!1;if(f==undefined)var m="",n="";else var m='<param name="FlashVars" value="'+f+'" />',n=' FlashVars="'+f+'" ';if(g==undefined)var o="",p="";else var o='<param name="menu" value="'+g+'" />',p=' menu="'+g+'" ';if(h==undefined)var q="",r="";else var q='<param name="wmode" value="'+h+'" />',r=' wmode="'+h+'" ';if(i==undefined)var s="",t="";else var s='<param name="quality" value="'+i+'" />',t=' quality="'+i+'" ';if(j==undefined)var u="",v="";else var u='<param name="bgcolor" value="'+j+'" />',v=' bgcolor="'+j+'" ';if(k==undefined)var w="",x="";else var w='<param name="allowScriptAccess" value="'+k+'" />',x=' allowScriptAccess="'+k+'" ';if(d==undefined)var y="";else var y='id="'+d+'"';l==undefined&&(l="7,0,0,0");return STD.isIE?'<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version='+l+'" width="'+b+'" height="'+c+'" '+y+' align="middle"><param name="movie" value="'+a+'" />'+w+m+o+s+u+q+"</object>":"<embed "+y+' type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" src="'+a+'"'+' width="'+b+'"'+' height="'+c+'"'+x+n+p+t+v+r+"/>"}catch(z){return!1}}function searchChildNodes(a,b){var c=[];if(a.hasChildNodes())for(var d=0;d<a.childNodes.length;d++){var e=a.childNodes[d];if(e.nodeType!=1)continue;e.tagName.toUpperCase()==b.toUpperCase()&&(c[c.length]=e);var f=searchChildNodes(e,b);for(var g=0;g<f.length;g++)c[c.length]=f[g]}return c}function updateFeed(){var a=createHttp();a&&(a.open("GET",blogURL+"/feeder?"+(new Date).getTime(),!0),a.send(""))}function getOffsetLeft(a){return a?a.offsetLeft+getOffsetLeft(a.offsetParent):0}function getOffsetTop(a){return a?a.offsetTop+getOffsetTop(a.offsetParent):0}function getWindowCleintWidth(){return window.innerWidth!=null?window.innerWidth:document.documentElement.clientWidth}function getWindowCleintHeight(){return window.innerHeight!=null?window.innerHeight:document.documentElement.clientHeight}function setUserSetting(a,b){var c=new HTTPRequest("POST",blogURL+"/owner/setting/userSetting/set/");c.send("name="+encodeURIComponent(a)+"&value="+encodeURIComponent(b))}function showJukeboxList(a,b){target=document.getElementById("jukeBoxContainer"+a),divTarget=document.getElementById("jukeBox"+a+"Div"),flashTarget=document.getElementById("jukeBox"+a+"Flash"),target.style.height=flashTarget.style.height=divTarget.style.height=b+"px"}function eleganceScroll(a,b){b==undefined&&(b=8),scrollerId=window.setInterval("scroller('"+a+"',"+b+")",1e3/30)}function scroller(a,b){try{var a=document.getElementById(a),c=document.body.scrollTop;status=a.scrollTop+"  "+document.body.scrollTop+"  "+b+" = "+(a.offsetTop-document.body.scrollTop)/b,c+=(a.offsetTop-document.body.scrollTop)/b,document.body.scrollTop==c&&clearInterval(scrollerId),window.scroll(0,c)}catch(d){clearInterval(scrollerId),alert(d.message)}}function openFullScreen(a,b,c){try{}catch(d){}img_view=window.open(a,"img_popup","width="+screen.width+",height="+screen.height+",left=0,top=0,scrollbars=no,resizable=yes"),img_view.status=b;try{img_view.document.focus()}catch(d){}}function open_img(a){img_view=window.open("","TatterImagePopup","width=0, height=0, left=0, top=0, scrollbars=yes, resizable=yes"),img_view.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">\n<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">\n   <head>\n       <title> :: View :: </title>\n       <meta http-equiv="content-type" content="text/html; charset=utf-8" />\n       <script type="text/javascript">\n       //<![CDATA\n           function getWindowCleintHeight() {\n               return (window.innerHeight != null) ? window.innerHeight : document.documentElement.clientHeight;\n           }\n           function getWindowCleintWidth() {\n               return (window.innerWidth != null) ? window.innerWidth : document.documentElement.clientWidth;\n           }\n           function resize(img) {\n               var imageWidth = img.width+5;\n               var imageHeight = img.height+5;\n               var screenWidth = screen.availWidth;\n               var screenHeight = screen.availHeight;\n               var windowWidth = imageWidth;\n               var windowHeight = imageHeight;\n               var positionX = (screenWidth - imageWidth) / 2;\n               var positionY = (screenHeight - imageHeight) / 2;\n               if(imageWidth > screenWidth * 0.8) {\n                   windowWidth = screenWidth * 0.8;\n                   document.body.scroll ="yes";\n                   positionX = 0;\n               }\n               if(imageHeight > screenHeight * 0.8 ) {\n                   windowHeight = screenHeight * 0.8;\n                   document.body.scroll ="yes";\n                   positionY = 0;\n               }\n               iWidth = windowWidth - getWindowCleintWidth();\n               iHeight = windowHeight - getWindowCleintHeight();\n               window.resizeBy(iWidth, iHeight);\n               window.moveTo(positionX, positionY);\n           }\n       //]]>\n       </script>\n   </head>\n   <body style="margin: 0px; padding: 0;">\n       <a href="javascript:window.close()"><img src="'+a+'" style="border: 0px; padding: 0; margin:0;" onload="resize(this)" /></a>\n'+"   </body>\n"+"</html>");try{img_view.document.close()}catch(b){}try{img_view.document.focus()}catch(b){}}function isNull(a,b){if(a.value.length==0){alert(b+"\t"),a.focus();return!0}return!1}function setRequestBody(a,b,c){var d="";d+="--"+c+"\r\n",d+='Content-Disposition: form-data; name="'+a+'"'+"\r\n\r\n",d+=b+"\r\n";return d}function requestPostText(a,b){var c=createHttp();c.open("POST",a+"&time="+(new Date).getTime(),!1),c.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),c.send(b);var d=c.responseText;delete c;return d}function requestPost(a,b){var c=createHttp();c.open("POST",a+"&time="+(new Date).getTime(),!1),c.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),c.send(b+"&time="+(new Date).getTime());var d=c.responseXML.selectSingleNode("/response/error").text;delete c;return d}function requestHttpXml(a){var b=createHttp();b.open("GET",a+"&time="+(new Date).getTime(),!1),b.send("");var c=b.responseXML;delete b;return c}function requestHttpText(a){var b=createHttp();b.open("GET",a+"&time="+(new Date).getTime(),!1),b.send("");var c=b.responseText;delete b;return c}function requestHttp(a){try{var b=createHttp();b.open("GET",a+"&time="+(new Date).getTime(),!1),b.send("");if(isSafari||isOpera){var c=b.responseXML.firstChild.firstChild.nextSibling.firstChild.nodeValue;delete b;return c}var c=b.responseXML.selectSingleNode("/response/error").text;delete b;return c}catch(d){window.status=d.messge}}function getResponse(a,b){try{loading=!0;var c=document.body,d=createHttp();a.indexOf("?")==-1?aux="?":aux="&",d.open("POST",a+aux+"time="+(new Date).getTime(),!1),b==undefined?b="":d.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),d.send(b),result=[];if(isSafari||isOpera){resultNodes=d.responseXML.firstChild.childNodes;for(var e=0;e<resultNodes.length;e++)resultNodes.item(e).firstChild!=null&&(result[resultNodes.item(e).nodeName]=resultNodes.item(e).firstChild.nodeValue);loading=!1,delete d;return result}if(isIE){resultNodes=d.responseXML.documentElement.childNodes,result=[];for(var e=0;e<resultNodes.length;e++)result[resultNodes[e].nodeName]=resultNodes[e].text;loading=!1,delete d;return result}loading=!1;var f=d.responseXML.selectNodes("/response/descendant::*");delete d;return f}catch(g){alert("exception"),loading=!1;var h=document.getElementsByName("body"),i=document.createElement("div");document.body.appendChild(i),i.innerHTML='<iframe src="'+a+'"style="display:none" onload="location.href=location.href"></iframe>';return!1}}function createHttp(){try{return new XMLHttpRequest}catch(a){var b=["MSXML2.XMLHTTP.5.0","MSXML2.XMLHTTP.4.0","MSXML2.XMLHTTP.3.0","MSXML2.XMLHTTP","Microsoft.XMLHTTP"];for(var c=0;c<b.length;c++)try{return new ActiveXObject(b[c])}catch(a){}return null}}function endProgress(){oProgress&&(document.body.removeChild(oProgress),oProgress=null)}function beginProgress(){endProgress(),oProgress=document.createElement("span"),oProgress.style.position="absolute",oProgress.style.left="0px",oProgress.style.top="0px",oProgress.style.backgroundColor="#FFFF99",oProgress.innerText="???..",document.body.appendChild(oProgress)}function openKeyword(a){window.open(a,"keyword","width=570,height=650,location=0,menubar=0,resizable=1,scrollbars=1,status=0,toolbar=0")}function trimAll(a){try{for(var b=0;b<a.elements.length;b++)var c=a.elements[b].tagName.toLowerCase(),d=a.elements[b].type;return!0}catch(e){alert(e.message)}}function checkValue(a,b){try{if(a.value.length==0){alert(b),a.focus();return!1}return!0}catch(c){return!1}}function trim(a){var b=0,c=a.length;for(var d=0;d<a.length;d++)if(a.charAt(d)!=" "){b=d;break}for(var d=a.length-1;d>=0;d--)if(a.charAt(d)!=" "){c=d+1;break}return a.substring(b,c)}function findFormObject(a){for(var b=a;b;b=b.parentNode)if(b.nodeName=="FORM")return b;return null}function hideLayer(a){document.getElementById(a).style.display="none";return!0}function showLayer(a){document.getElementById(a).style.display="block";return!0}function focusLayer(a,b){try{var c=document.getElementById(a);c.style.display="block";for(x in b)if(b[x]!=a){var c=document.getElementById(b[x]);c.style.display="none"}}catch(d){}return!0}function toggleLayer(a){try{var b=document.getElementById(a);b.style.display=b.style.display=="none"?"block":"none"}catch(c){}return!0}function openLinkInNewWindow(a){if(a){var b=a.getAttribute("href");if(b){window.open(b);return!1}}return!0}function trace(a,b){result=analysis(a,b);if(b==undefined)alert(result);else if(b="w"){var c=window.open("","traceWin");c.document.write(result)}}function analysis(a,b){try{if(b==undefined){var c="";for(var d in a)c+=d+"\t\t:"+a[d]+"\n";return c}if(b="w"){var c='<table  cellspacing="0">';for(var d in a)c+="<tr>",c+="<td>"+d+"</td><td>",c+=a[d],c+="</td>",c+="</tr>";c+="</table>";return c}}catch(e){}}function compareVersions(a,b){var c=a.split("."),d=b.split(".");if(c.length>d.length)for(var e=0;e<c.length-d.length;e++)d.push("0");else if(c.length<d.length)for(var e=0;e<d.length-c.length;e++)c.push("0");for(var e=0;e<c.length;e++){if(c[e]<d[e])return-1;if(c[e]>d[e])return 1}return 0}var sUserAgent=navigator.userAgent,fAppVersion=parseFloat(navigator.appVersion),isOpera=sUserAgent.indexOf("Opera")>-1,isMinOpera4=isMinOpera5=isMinOpera6=isMinOpera7=isMinOpera7_5=!1;if(isOpera){var fOperaVersion;if(navigator.appName=="Opera")fOperaVersion=fAppVersion;else{var reOperaVersion=new RegExp("Opera (\\d+\\.\\d+)");reOperaVersion.test(sUserAgent),fOperaVersion=parseFloat(RegExp.$1)}isMinOpera4=fOperaVersion>=4,isMinOpera5=fOperaVersion>=5,isMinOpera6=fOperaVersion>=6,isMinOpera7=fOperaVersion>=7,isMinOpera7_5=fOperaVersion>=7.5}var isKHTML=sUserAgent.indexOf("KHTML")>-1||sUserAgent.indexOf("Konqueror")>-1||sUserAgent.indexOf("AppleWebKit")>-1,isMinSafari1=isMinSafari1_2=!1,isMinKonq2_2=isMinKonq3=isMinKonq3_1=isMinKonq3_2=!1,isSafari=!1;if(isKHTML){isSafari=sUserAgent.indexOf("AppleWebKit")>-1,isKonq=sUserAgent.indexOf("Konqueror")>-1;if(isSafari){var reAppleWebKit=new RegExp("AppleWebKit\\/(\\d+(?:\\.\\d*)?)");reAppleWebKit.test(sUserAgent);var fAppleWebKitVersion=parseFloat(RegExp.$1);isMinSafari1=fAppleWebKitVersion>=85,isMinSafari1_2=fAppleWebKitVersion>=124,isMinSafari3=fAppleWebKitVersion>=510}else if(isKonq){var reKonq=new RegExp("Konqueror\\/(\\d+(?:\\.\\d+(?:\\.\\d)?)?)");reKonq.test(sUserAgent),isMinKonq2_2=compareVersions(RegExp.$1,"2.2")>=0,isMinKonq3=compareVersions(RegExp.$1,"3.0")>=0,isMinKonq3_1=compareVersions(RegExp.$1,"3.1")>=0,isMinKonq3_2=compareVersions(RegExp.$1,"3.2")>=0}}var isIE=sUserAgent.indexOf("compatible")>-1&&sUserAgent.indexOf("MSIE")>-1&&!isOpera,isMinIE4=isMinIE5=isMinIE5_5=isMinIE6=!1;if(isIE){var reIE=new RegExp("MSIE (\\d+\\.\\d+);");reIE.test(sUserAgent);var fIEVersion=parseFloat(RegExp.$1);isMinIE4=fIEVersion>=4,isMinIE5=fIEVersion>=5,isMinIE5_5=fIEVersion>=5.5,isMinIE6=fIEVersion>=6}var isMoz=sUserAgent.indexOf("Gecko")>-1&&!isKHTML,isMinMoz1=sMinMoz1_4=isMinMoz1_5=!1;if(isMoz){var reMoz=new RegExp("rv:(\\d+\\.\\d+(?:\\.\\d+)?)");reMoz.test(sUserAgent),isMinMoz1=compareVersions(RegExp.$1,"1.0")>=0,isMinMoz1_4=compareVersions(RegExp.$1,"1.4")>=0,isMinMoz1_5=compareVersions(RegExp.$1,"1.5")>=0}var isNS4=!isIE&&!isOpera&&!isMoz&&!isKHTML&&sUserAgent.indexOf("Mozilla")==0&&navigator.appName=="Netscape"&&fAppVersion>=4&&fAppVersion<5,isMinNS4=isMinNS4_5=isMinNS4_7=isMinNS4_8=!1;isNS4&&(isMinNS4=!0,isMinNS4_5=fAppVersion>=4.5,isMinNS4_7=fAppVersion>=4.7,isMinNS4_8=fAppVersion>=4.8);var isWin=navigator.platform=="Win32"||navigator.platform=="Windows",isMac=navigator.platform=="Mac68K"||navigator.platform=="MacPPC"||navigator.platform=="Macintosh",isUnix=navigator.platform=="X11"&&!isWin&&!isMac,isWin95=isWin98=isWinNT4=isWin2K=isWinME=isWinXP=!1,isMac68K=isMacPPC=!1,isSunOS=isMinSunOS4=isMinSunOS5=isMinSunOS5_5=!1;isWin&&(isWin95=sUserAgent.indexOf("Win95")>-1||sUserAgent.indexOf("Windows 95")>-1,isWin98=sUserAgent.indexOf("Win98")>-1||sUserAgent.indexOf("Windows 98")>-1,isWinME=sUserAgent.indexOf("Win 9x 4.90")>-1||sUserAgent.indexOf("Windows ME")>-1,isWin2K=sUserAgent.indexOf("Windows NT 5.0")>-1||sUserAgent.indexOf("Windows 2000")>-1,isWinXP=sUserAgent.indexOf("Windows NT 5.1")>-1||sUserAgent.indexOf("Windows XP")>-1,isWinNT4=sUserAgent.indexOf("WinNT")>-1||sUserAgent.indexOf("Windows NT")>-1||sUserAgent.indexOf("WinNT4.0")>-1||sUserAgent.indexOf("Windows NT 4.0")>-1&&!isWinME&&!isWin2K&&!isWinXP),isMac&&(isMac68K=sUserAgent.indexOf("Mac_68000")>-1||sUserAgent.indexOf("68K")>-1,isMacPPC=sUserAgent.indexOf("Mac_PowerPC")>-1||sUserAgent.indexOf("PPC")>-1);if(isUnix){isSunOS=sUserAgent.indexOf("SunOS")>-1;if(isSunOS){var reSunOS=new RegExp("SunOS (\\d+\\.\\d+(?:\\.\\d+)?)");reSunOS.test(sUserAgent),isMinSunOS4=compareVersions(RegExp.$1,"4.0")>=0,isMinSunOS5=compareVersions(RegExp.$1,"5.0")>=0,isMinSunOS5_5=compareVersions(RegExp.$1,"5.5")>=0}}var oProgress=null;isMoz&&(XMLDocument.prototype.selectNodes=function(a){var b=new XPathEvaluator,c=b.evaluate(a,this,null,XPathResult.ORDERER_NODE_ITERATOR_TYPE,null),d=[],e=c.iterateNext();while(e)d[e.nodeName]=e.firstChild.nodeValue,e=c.iterateNext();return d},XMLDocument.prototype.selectSingleNode=function(a){var b=new XPathEvaluator,c=b.evaluate(a,this,null,XPathResult.FIRST_ORDERED_NODE_TYPE,null);return c.singleNodeValue},Node.prototype.__defineGetter__("xml",function(){var a=new XMLSerializer;return a.serializeToString(this,"text/xml")}));var loading=!1,StringBuffer=function(){this.buffer=[]};StringBuffer.prototype.append=function(a){this.buffer[this.buffer.length]=a},StringBuffer.prototype.toString=function(){return this.buffer.join("")},Array.prototype.push||(Array.prototype.push=function(){var a=this.length;for(var b=0;b<arguments.length;b++)this[a+b]=arguments[b];return this.length}),String.prototype.trim||(String.prototype.trim=function(){return this.replace(new RegExp("(^\\s*)|(\\s*$)","g"),"")}),String.prototype.replaceAll||(String.prototype.replaceAll=function(a,b){a=a.replace(new RegExp("(\\W)","g"),"\\$1"),b=b.replace(new RegExp("\\$","g"),"$$$$");return this.replace(new RegExp(a,"gm"),b)}),String.prototype.count||(String.prototype.count=function(a){if(typeof a=="string")var b=this.match(new RegExp(a.replace(new RegExp("(\\W)","g"),"\\$1"),"g"));else var b=this.match(a);return b?b.length:0}),String.prototype.indexOfCaseInsensitive||(String.prototype.indexOfCaseInsensitive=function(a,b){var c=typeof b=="undefined"?this:this.substring(b,this.length),d=typeof a=="string"?(new RegExp(a.replace(new RegExp("(\\W)","g"),"\\$1"),"i")).exec(c):a.exec(c);return d?d.index+(typeof b=="number"?b:0):-1});var commentSavingNow=!1,openWindow=""