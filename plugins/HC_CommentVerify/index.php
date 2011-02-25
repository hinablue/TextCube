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

    // script.js
    (function(c,j){var h=j.getElementsByTagName("script")[0],e={},a={},d=false,k=function(){},l="string",b=function(){return Array.every||function(f,p,o){for(var n=0,m=f.length;n<m;++n){if(!p(f[n],n,f)){return d}}return 1}}(),i=function(f,n,m){b(f,function(q,p,o){n(q,p,o);return true},m)};if(j.readyState==null&&j.addEventListener){j.addEventListener("DOMContentLoaded",function g(){j.removeEventListener("DOMContentLoaded",g,d);j.readyState="complete"},d);j.readyState="loading"}c.$script=function(p,m,o){var n=typeof m=="function"?m:(o||k),p=typeof p==l?[p]:p,r=typeof m==l?m:p.join(""),f=p.length,q=function(){if(!--f){e[r]=1;n();for(dset in a){b(dset.split("|"),function(s){return(s in e)})&&b(a[dset],function(s){s();a[dset].shift()})}}};b(p,function(s){setTimeout(function(){var u=j.createElement("script"),t=d;u.onload=u.onreadystatechange=function(){if((u.readyState&&u.readyState!=="complete"&&u.readyState!=="loaded")||t){return d}u.onload=u.onreadystatechange=null;t=true;q()};u.async=true;u.src=s;h.insertBefore(u,h.firstChild)},0);return true});return c};$script.ready=function(o,m,n){n=n||k;o=(typeof o==l)?[o]:o;var f=[];i(o,function(p){(p in e)||f.push(p)})&&b(o,function(p){return(p in e)})?m():(function(p){a[p]=a[p]||[];a[p].push(m);n(f)}(o.join("|")));return $script}}(window,document));

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
