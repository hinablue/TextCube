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

    if(typeof document.getElementsByTagName === "undefined") {
        // I don\'t care.
    } else {
        if(typeof jQuery === "undefined") {
            var reCAPTCHAjQscript = document.createElement("script");
            reCAPTCHAjQscript.src = "http://www.google.com/jsapi";
            reCAPTCHAjQscript.type = "text/javascript";
            document.getElementsByTagName("head")[0].appendChild(reCAPTCHAjQscript);

            if (reCAPTCHAjQscript.readyState) {
                reCAPTCHAjQscript.onreadystatechange = function () {
                    if (reCAPTCHAjQscript.readyState == "loaded" || reCAPTCHAjQscript.readyState == "complete") {
                        finalInitjQuery();
                    }
                    return;
                };
            } else {
                reCAPTCHAjQscript.onload = function() {
                    finalInitjQuery();
                    return;
                };
            }
        } else {
            finalreCAPTCHA();
        }
    }

    function finalInitjQuery() {
        google.load("jquery", "1.4.2");
        google.setOnLoadCallback(function() {
            jQuery.noConflict();
            finalreCAPTCHA();
        });
    }

    function finalreCAPTCHA() {
        (function($) {
            $(\'<script />\')
            .attr(\'type\',\'text/javascript\')
            .attr(\'src\', \''.$pluginURL.'/jquery.expr.regex.js\')
            .appendTo($(\'head\'));
            $(\'<script />\')
            .attr(\'type\',\'text/javascript\')
            .attr(\'src\', \''.$pluginURL.'/jquery.form.plugin.min.js\')
            .appendTo($(\'head\'));

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
