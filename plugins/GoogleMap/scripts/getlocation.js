// Google Map Plugin UI Helper
// - depends on jQuery 1.3.2, jQuery UI plugin 1.7.2, and Google Maps API

var mode = 'started';
var position;

$(document).ready(function() {
	if (navigator.geolocation) {
		$('#availability').html(_t['현재 웹브라우저는 Geolocation 기능을 지원합니다.']+' <button id="getLocation">'+_t['위치 가져오기']+'</button>');
		$('#getLocation').click(function() {
			if (mode == 'started') {
				$('#status').html('<img src="'+pluginURL+'/images/icon_loading.gif" style="vertical-align:middle" width="16" height="16" alt="가져오는 중..." />');
				navigator.geolocation.getCurrentPosition(function(pos) {
					map.setCenter(new GLatLng(pos.coords.latitude, pos.coords.longitude), 10);
					position = pos;
					$('#status').html(_t['가져오기 성공.']);
					$('#getLocation').text(_t['좌표 설정하기']);
					mode = 'set';
				}, function(error) {
					switch (error.code) {
					case 1:
						msg = _t['권한 없음'];
						break;
					case 2:
						msg = _t['위치정보 없음'];
						break;
					case 3:
						msg = _t['시간 제한 초과'];
						break;
					default:
						msg = _t['알 수 없는 오류'];
					}
					$('#status').html(_t['실패']+' ('+msg+')');
				});
			} else if (mode == 'set') {
				if (confirm('('+position.coords.latitude+', '+position.coords.longitude+') '+_t['이 좌표를 현재 작성 중인 포스트의 좌표로 설정하시겠습니까?'])) {
					var opener = window.opener;
					if (!opener) {
						alert(_t['Error: The editor is not accessible.']);
						return;
					}
					if (opener.jQuery('input[name=latitude]').length == 0) {
						opener.jQuery('#editor-form').append('<input type="hidden" name="latitude" value="" /><input type="hidden" name="longitude" value="" />');	
					}
					opener.jQuery('input[name=latitude]').val(position.coords.latitude);
					opener.jQuery('input[name=longitude]').val(position.coords.longitude);
					window.close();
				}
			}
		});
	} else {
		$('#availability').html(_t['현재 웹브라우저는 Geolocation 기능을 지원하지 않습니다.'])
	}
});
