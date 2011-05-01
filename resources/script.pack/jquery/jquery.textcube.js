/// Copyright (c) 2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
// Textcube-specific jQuery Plugin
// TODO: set resourceURL - local or remote resource storage URL (depending on settings)
// TODO: plugin version check?
// 이 로더는 중요 jQuery 플러그인들(예: json, ui, easing)은 함께 포함되어 배포되며, 이들을 여러
// 스크립트에서 중복해서 불러오지 않도록 하는 역할을 한다.
// 하지만 그렇지 않은 플러그인들은 사용하고자 하는 주체(예: 플러그인 제작자)가 자체적으로
// 포함시키는 것을 원칙으로 한다.
// 사용법 : $.plugin('ui.essentials');
(function(a){a.path={},a.path.separator=a.path.default_separator="/",a.path.join=function(){var b=a.path.join.arguments,c=b[0];for(var d=1;d<b.length;++d){var e=c.charAt(c.length-1)==a.path.separator,f=b[d].charAt(0)==a.path.separator;e&&f?c+=b[d].slice(1):e||f?c+=b[d]:c+=(c?a.path.separator:"")+b[d]}return c},a.plugin=function(b,c){for(var d=0;d<a.plugin.loaded_ones.length;++d)if(a.plugin.loaded_ones[d].name==b)return!0;for(var d=0;d<a.plugin.locals.length;++d)if(a.plugin.locals[d].name==b){a.path.separator=".";var e=a.path.join("jquery",b,a.plugin.locals[d].version,"js");a.path.separator=a.path.default_separator;var f=a.path.join(serviceURL,resoucreURL||a.plugin.defaultResourceURL,e);a("<script>").attr("type","text/javascript").attr("src",f).appendTo(a("head")),a.plugins.loaded_ones.append({name:b,version:a.plugin.locals[d].version});return!0}return!1},a.plugin.locals=[{name:"json",version:""},{name:"easing",version:"1.3"},{name:"ui.essentials",version:"1.6"},{name:"ui.effects",version:"1.6"}],a.plugin.defaultResourceURL="/resources/script",a.plugin.loaded_ones=[]})(jQuery)