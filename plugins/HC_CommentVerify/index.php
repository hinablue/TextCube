<?php

function reCAPTCHAScript($target) {
	global $suri, $pluginURL;
    
    $directive = array('archive','category','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $target;

    if (!doesHaveOwnership() && !doesHaveMembership())
    {
        $target .= '<script type="text/javascript" src="http://api.recaptcha.net/js/recaptcha_ajax.js"></script>
<script type="text/javascript">
//<![CDDA[
    var reCAPTCHAentryIdWriteComment = 0;
    if (typeof loadCommentCallback === "undefined") {
	    var loadCommentCallback = [];
    }
    if (typeof addCommentCallback === "undefined") {
        var addCommentCallback = [];
    }
    var reCAPTCHAloadCommentCallback = function() {
        reCAPTCHACommentVerify(arguments[0]);
	    return false;
    };
    var reCAPTCHAaddCommentCallback = function() {
        reCAPTCHACommentVerify(arguments[1]);
	    return false;
    };

    var reCAPTCHACommentVerify = function(entryId) {
        var entryId = entryId;
    	(function($) {
            var options = {
                beforeSubmit: showRecaptchaRequest,
                success: showRecaptchaResponse,
                url: "'.$pluginURL.'/verifycomment.php",
                data: { \'entryId\': entryId },
                forceSync: true
            };
            var form = $("#entry"+entryId+"WriteComment");
            var commentType = $("input.commentTypeCheckbox:checked", form);
            if (typeof commentType === "object")
            {
                $(\'<div id="recaptcha_entry\'+entryId+\'WriteComment"></div>\').insertAfter($("textarea", form));
	            Recaptcha.create("6LfJ7roSAAAAAO-z_EUw2kBOmm7Yyan-Qso5O8Q-", "recaptcha_entry"+entryId+"WriteComment", {
                	theme: "red",
                	tabindex: 0
                });
                $("input[type=submit]", form)
                .attr("onclick","return false;")
                .attr("id", "commentrebuildSubmit"+entryId.toString())
                .click(function() { 
                    reCAPTCHAentryIdWriteComment = entryId;
                    form.ajaxSubmit(options); 
                    return false; 
                });
            }
    	})(jQuery);
	    return false;
    };

/*!
  * $script.js v1.3
  * https://github.com/ded/script.js
  * Copyright: @ded & @fat - Dustin Diaz, Jacob Thornton 2011
  * Follow our software http://twitter.com/dedfat
  * License: MIT
  */
!function(a,b,c){function s(a,c){var e=b.createElement("script"),f=j;e.onload=e.onerror=e[o]=function(){e[m]&&!/^c|loade/.test(e[m])||f||(e.onload=e[o]=null,f=1,h[a]=2,c())},e.async=1,e.src=a,d.insertBefore(e,d.firstChild)}function q(a,b){p(a,function(a){return!b(a)})}var d=b.getElementsByTagName("head")[0],e={},f={},g={},h={},i="string",j=!1,k="push",l="DOMContentLoaded",m="readyState",n="addEventListener",o="onreadystatechange",p=function(a,b){for(var c=0,d=a.length;c<d;++c)if(!b(a[c]))return j;return 1};!b[m]&&b[n]&&(b[n](l,function u(){b.removeEventListener(l,u,j),b[m]="complete"},j),b[m]="loading");var r=function(a,b,d){function o(){if(!--m){e[l]=1,j&&j();for(var a in g)p(a.split("|"),n)&&!q(g[a],n)&&(g[a]=[])}}function n(a){return a.call?a():e[a]}a=a[k]?a:[a];var i=b&&b.call,j=i?b:d,l=i?a.join(""):b,m=a.length;c(function(){q(a,function(a){h[a]?(l&&(f[l]=1),h[a]==2&&o()):(h[a]=1,l&&(f[l]=1),s(r.path?r.path+a+".js":a,o))})},0);return r};r.get=s,r.ready=function(a,b,c){a=a[k]?a:[a];var d=[];!q(a,function(a){e[a]||d[k](a)})&&p(a,function(a){return e[a]})?b():!function(a){g[a]=g[a]||[],g[a][k](b),c&&c(d)}(a.join("|"));return r};var t=a.$script;r.noConflict=function(){a.$script=t;return this},typeof module!="undefined"&&module.exports?module.exports=r:a.$script=r}(this,document,setTimeout);

    if(typeof jQuery === "undefined") {
        $script("http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js", function() {
            jQuery.noConflict();
            finalreCAPTCHA();
        });
    } else {
        finalreCAPTCHA();
    }

    function finalreCAPTCHA() {
        $script(["'.$pluginURL.'/jquery.form.plugin.min.js","'.$pluginURL.'/jquery.expr.regex.js"], function() {
            (function($) {
                $(document).ready(function() {
                    var options = {
                        beforeSubmit: showRecaptchaRequest,
                        success: showRecaptchaResponse,
                        url: "'.$pluginURL.'/verifycomment.php",
                        forceSync: true
                    };
                    loadCommentCallback.push(reCAPTCHAloadCommentCallback);
                    addCommentCallback.push(reCAPTCHAaddCommentCallback);

                    if (typeof $("form:regex(id, entry[0-9]+WriteComment)") === "object") {
                        $("form:regex(id, entry[0-9]+WriteComment)").each(function(index, elem) {
                            var elem = $(elem), index = index;
                            var commentType = $("input.commentTypeCheckbox:checked", elem);
                            var entryId = parseInt(elem.attr("id").replace(/entry/,"").replace(/WriteComment/,""));
                            var options = {
                                beforeSubmit: showRecaptchaRequest,
                                success: showRecaptchaResponse,
                                url: "'.$pluginURL.'/verifycomment.php",
                                data: { \'entryId\': entryId },
                                forceSync: true
                            };
                            if (typeof commentType === "object")
                            {
                                $(\'<div id="recaptcha\'+entryId.toString()+\'"></div>\').insertAfter($("textarea", elem));
                                Recaptcha.create("6LfJ7roSAAAAAO-z_EUw2kBOmm7Yyan-Qso5O8Q-", "recaptcha"+entryId.toString(), {
                                    theme: "red",
                                    tabindex: 0
                                });

                                $("input[type=submit]", elem)
                                .attr("onclick","return false;")
                                .attr("id", "commentrebuildSubmit"+entryId.toString())
                                .click(function() { 
                                    elem.ajaxSubmit(options); 
                                    return false; 
                                });
                            }
                        });
                    }
                });
            })(jQuery);
        });
    }
    function showRecaptchaRequest(formData, jqForm, options)
    {
        (function($) {
            var entryId = parseInt(options.data.entryId);
            var form = $("form#entry"+entryId+"WriteComment");
            var resp_field = $("input[name=recaptcha_response_field]", form).fieldValue();
            if (!resp_field[0])
            { 
                alert("'._t("You need to enter the validation string.").'"); 
       	    	return false; 
            }
        })(jQuery);
    }
    function showRecaptchaResponse(responseText, statusText)
    {
        if (statusText == "success" && responseText != "")
        {
            (function($) {
                var entryId = parseInt(responseText);
                addComment(document.getElementById("commentrebuildSubmit"+entryId.toString()), entryId);
                return true;
            })(jQuery);
        }
    }
//]]>
</script>';
    }

	return $target;	
}
?>
