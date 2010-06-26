<?php
/* Creative Commons License marker plugin for Textcube 1.6
   ----------------------------------
   Version 1.6
   Tatter Network Foundation development team / Needlworks. 

   Creator          : inureyes
   Maintainer       : inureyes

   Created at       : 2006.10.29
   Last modified at : 2008.02.25
 
 This plugin shows Creative commons License banner on the sidebar.
 For the detail, visit http://forest.nubimaru.com


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 change log
 sihwpg: 주성애비님의 post별 CCL 기능 추가 , 잔버그 수정

*/

function CCLmarker_insertCCL($target,$mother)
{
	global $configVal, $suri, $entryContentViewMode;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	$data['postUse'] = !isset($data['postUse']) ? '01' : $data['postUse']; 
	$data['profit'] = !isset($data['profit']) ? 'no' : $data['profit'];
	$data['changeable'] = !isset($data['changeable']) ? 'no' : $data['changeable'];
	$data['titleCaption'] = !isset($data['titleCaption']) ? '01' : $data['titleCaption'];
	$data['Position'] = !isset($data['Position']) ? '02':$data['Position'];
	$data['showRSS'] = !isset($data['showRSS']) ? '01' :$data['showRSS'];
	
	if( strcmp($data['postUse'],'02') == 0 ) return $target;
	
	if(!is_null($data)){
		$profit = $data['profit']=='yes' ? "" : "nc";
		if($data['changeable']=='yes')
			$changeable = 'fr';
		else if($data['changeable']=='some')
			$changeable = "sa";
		else if($data['changeable']=='no')
			$changeable = "nd";

		if($data['titleCaption'] == '01')
			$titleCaption ='<legend><span><strong>'._t('크리에이티브 커먼즈 라이센스').'</strong></span></legend>';
		else if($data['titleCaption'] == '02')
			$titleCaption ='<legend><span><strong>Creative Commons License</strong></span></legend>';
		else if($data['titleCaption'] == '03')
			$titleCaption ='';
		else if($data['titleCaption'] == '04')
			$titleCaption ='<legend><span><strong>'.$data['titleCaptionText'].'</strong></span></legend>';
	} else {
		$data['Position'] = '02';
		$data['showRSS'] = '01';
		$profit = 'nc';
		$changeable = 'nd';
		$titleCaption ='<legend><span><strong>'._t('크리에이티브 커먼즈 라이센스').'</strong></span></legend>';
	}

	$buf = '';
	if( strcmp( $suri['directive'] , '/sync') == 0 || strcmp( $suri['directive'] , '/m') == 0 ) 
		return $target;
	if($data['showRSS']=='01' || strcmp($suri['directive'],"/rss")!= 0){
		$buf .= '<fieldset style="margin:20px 0px 20px 0px;padding:5px;">';
		$buf .= $titleCaption;
		$buf .='<!--Creative Commons License-->';
		$buf .='<div style="float: left; width: 88px; margin-top: 3px;"><a rel="license" href="http://creativecommons.org/licenses/by';
		$buf .= CCLmarker_setTail($profit, $changeable);
		$buf .='/'._t('version').'/'._t('kr').'/" target=_blank><img alt="Creative Commons License" style="border-width: 0" src="http://i.creativecommons.org/l/by';
		$buf .= CCLmarker_setTail($profit, $changeable);
		$buf .='/'._t('version').'/'._t('kr').'/88x31.png"/></a></div>';
		$buf .='<div style="margin-left: 92px; margin-top: 3px; text-align: justify;">'._t('이 저작물은').' <a rel="license" href="http://creativecommons.org/licenses/by';
		$buf .= CCLmarker_setTail($profit, $changeable);
		$buf .='/'._t('version').'/'._t('kr').'/" target=_blank>'._t('크리에이티브 커먼즈 코리아 저작자표시');
		$buf .= CCLmarker_setDesc($profit, $changeable);
		$buf .= _t('2.0 대한민국 라이센스').'</a>'._t('에 따라 이용하실 수 있습니다.').'
			<!-- Creative Commons License-->
			<!-- <rdf:RDF xmlns="http://web.resource.org/cc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
			<Work rdf:about="">
			<license rdf:resource="http://creativecommons.org/licenses/by';
		$buf .= CCLmarker_setTail($profit, $changeable);
		$buf .= '/'._t('version').'/'._t('kr').'/" />
			</Work>
			<License rdf:about="http://creativecommons.org/licenses/by';
		$buf .= CCLmarker_setTail($profit, $changeable);
		$buf .= '/">';
		$buf .= '
			<permits rdf:resource="http://web.resource.org/cc/Reproduction"/>
			<permits rdf:resource="http://web.resource.org/cc/Distribution"/>
			<requires rdf:resource="http://web.resource.org/cc/Notice"/>
			<requires rdf:resource="http://web.resource.org/cc/Attribution"/>';
		if($changeable == 'sa') 
			$buf .= '
			<permits rdf:resource="http://web.resource.org/cc/DerivativeWorks"/>
			<requires rdf:resource="http://web.resource.org/cc/ShareAlike"/>';
		else if($changeable == 'fr') 
			$buf .= '
			<permits rdf:resource="http://web.resource.org/cc/DerivativeWorks"/>';
		if($profit == 'nc') $buf .= '<prohibits rdf:resource="http://web.resource.org/cc/CommercialUse"/>';

		$buf .= '</License></rdf:RDF> -->';

		$buf .= '</div></fieldset>';
	}
	if ($data['Position'] == '01')
		return $buf.$target;
	else if ($data['Position'] == '02')
		return $target.$buf;
}

function CCLmarker($parameter)
{
	return CCLmaker_getView();
}

function CCLmarker_replacer($target)
{
	return CCLmaker_getView();
}

function CCLmaker_getView()
{
	global $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);

	if(!is_null($data)){
		$data['profit'] = isset($data['profit'])? $data['profit'] : "nc";
		$data['changeable'] = isset($data['changeable'])? $data['changeable'] : "no";
		$profit = $data['profit']=='yes' ? "" : "nc";
		if($data['changeable']=='yes')
			$changeable = 'fr';
		else if($data['changeable']=='some')
			$changeable = "sa";
		else if($data['changeable']=='no')
			$changeable = "nd";
	} else {
		$profit = 'nc';
		$changeable = 'nd';
	}

	$buf = '';
	$buf .='<!--Creative Commons License-->';
	$buf .='<center>';
	$buf .='<a rel="license" href="http://creativecommons.org/licenses/by';
	$buf .= CCLmarker_setTail($profit, $changeable);
	$buf .='/'._t('version').'/'._t('kr').'/"><img alt="Creative Commons License" style="border-width: 0" src="http://i.creativecommons.org/l/by';
	$buf .= CCLmarker_setTail($profit, $changeable);
	$buf .='/'._t('version').'/'._t('kr').'/88x31.png"/></a>';
	$buf .='</center>';
	$buf .='<br/>'._t('이 저작물은').' <a rel="license" href="http://creativecommons.org/licenses/by';
	$buf .= CCLmarker_setTail($profit, $changeable);
	$buf .='/'._t('version').'/'._t('kr').'/">'._t('크리에이티브 커먼즈 코리아 저작자표시');
	$buf .= CCLmarker_setDesc($profit, $changeable);
	$buf .= _t('2.0 대한민국 라이센스').'</a>'._t('에 따라 이용하실 수 있습니다.').'
		<!-- Creative Commons License-->
		<!-- <rdf:RDF xmlns="http://web.resource.org/cc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
		<Work rdf:about="">
		<license rdf:resource="http://creativecommons.org/licenses/by';
	$buf .= CCLmarker_setTail($profit, $changeable);
	$buf .= '/'._t('version').'/'._t('kr').'/" />
		</Work>
		<License rdf:about="http://creativecommons.org/licenses/by';
	$buf .= CCLmarker_setTail($profit, $changeable);
	$buf .= '/">';
	$buf .= '
		<permits rdf:resource="http://web.resource.org/cc/Reproduction"/>
		<permits rdf:resource="http://web.resource.org/cc/Distribution"/>
		<requires rdf:resource="http://web.resource.org/cc/Notice"/>
		<requires rdf:resource="http://web.resource.org/cc/Attribution"/>';
	if($changeable == 'sa') 
		$buf .= '
		<permits rdf:resource="http://web.resource.org/cc/DerivativeWorks"/>
		<requires rdf:resource="http://web.resource.org/cc/ShareAlike"/>';
	else if($changeable == 'fr') 
		$buf .= '
		<permits rdf:resource="http://web.resource.org/cc/DerivativeWorks"/>';
	if($profit == 'nc') $buf .= '<prohibits rdf:resource="http://web.resource.org/cc/CommercialUse"/>';

	$buf .= '</License></rdf:RDF> -->';
	return $buf;
}

function CCLmarker_setTail($profit, $changeable) {
	$buf = '';
	if($profit == 'nc') $buf.='-nc';
	if($changeable == 'sa') $buf.='-sa';
	else if($changeable == 'nd') $buf.='-nd';
	return $buf;
}

function CCLmarker_setDesc($profit, $changeable) {
	$buf = '';
	if($profit == 'nc') $buf.= _t('-비영리');
	if($changeable == 'sa') $buf.= _t('-동일조건변경허락');
	else if($changeable == 'nd') $buf.= _t('-변경금지');
	return $buf;
}

function CCLmarker_DataSet($DATA){
	requireComponent('Textcube.Function.misc');
	$cfg = Misc::fetchConfigVal($DATA);
	//fetchConfigVal($DATA);
	return true;
}
?>
