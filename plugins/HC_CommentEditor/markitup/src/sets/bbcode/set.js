// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// BBCode tags example
// http://en.wikipedia.org/wiki/Bbcode
// ----------------------------------------------------------------------------
// Feel free to add more tags
// ----------------------------------------------------------------------------
var mySettings = {
	markupSet: [
		{name:'Bold', openWith:'[b]', closeWith:'[/b]', className:"fonts-bold" },
		{name:'Italic', openWith:'[i]', closeWith:'[/i]', className:"fonts-italic" },
		{name:'Underline', openWith:'[u]', closeWith:'[/u]', className:"fonts-underline" },
		{separator:'---------------' },
		{name:'Size', openWith:'[size=[![文字大小 (單位: %)]!]]', closeWith:'[/size]', className:"fonts-size",
		dropMenu :[
			{name:'Big', openWith:'[size=200]', closeWith:'[/size]' },
			{name:'Normal', openWith:'[size=100]', closeWith:'[/size]' },
			{name:'Small', openWith:'[size=75]', closeWith:'[/size]' }
		]},
		{name:'Colors', openWith:'[color=[![顏色, #000000 or black]!]]', closeWith:'[/color]', className:"fonts-colors",
		dropMenu: [
			{name:'Yellow', openWith:'[color=yellow]', closeWith:'[/color]', className:"col1-1" },
			{name:'Orange', openWith:'[color=orange]', closeWith:'[/color]', className:"col1-2" },
			{name:'Red', openWith:'[color=red]', closeWith:'[/color]', className:"col1-3" },
			{name:'Blue', openWith:'[color=blue]', closeWith:'[/color]', className:"col2-1" },
			{name:'Purple', openWith:'[color=purple]', closeWith:'[/color]', className:"col2-2" },
			{name:'Green', openWith:'[color=green]', closeWith:'[/color]', className:"col2-3" },
			{name:'White', openWith:'[color=white]', closeWith:'[/color]', className:"col3-1" },
			{name:'Gray', openWith:'[color=gray]', closeWith:'[/color]', className:"col3-2" },
			{name:'Black', openWith:'[color=black]', closeWith:'[/color]', className:"col3-3" }
		]},
		{separator:'---------------' },		
		{name:'Picture', replaceWith:'[img][![輸入圖片網址]!][/img]', className:"picture" },
		{name:'Link', openWith:'[url=[![輸入網址]!]]', closeWith:'[/url]', placeHolder:'輸入要鏈結的文字...', className:"link" },
		{separator:'---------------' },
		{name:'Smile', openWith:'[smile=[![表情編號 01~25 號，請由下拉清單選擇。]!]]', closeWith:'[/smile]', placeHolder:'輸入表情描述文字...', className:"content-smile",
		dropMenu: [
			{name:'放光芒', openWith:'[smile=01]放光芒', closeWith:'[/smile]', className:"col1-s1" },
			{name:'冷靜', openWith:'[smile=02]冷靜', closeWith:'[/smile]', className:"col1-s2" },
			{name:'得意', openWith:'[smile=03]得意', closeWith:'[/smile]', className:"col1-s3" },
			{name:'冷汗', openWith:'[smile=04]冷汗', closeWith:'[/smile]', className:"col2-s1" },
			{name:'HAPPY', openWith:'[smile=05]HAPPY', closeWith:'[/smile]', className:"col2-s2" },
			{name:'攤手', openWith:'[smile=06]攤手', closeWith:'[/smile]', className:"col2-s3" },
			{name:'震驚', openWith:'[smile=07]震驚', closeWith:'[/smile]', className:"col3-s1" },
			{name:'抖胸', openWith:'[smile=08]抖胸', closeWith:'[/smile]', className:"col3-s2" },
			{name:'再見', openWith:'[smile=09]再見', closeWith:'[/smile]', className:"col3-s3" },
			{name:'音樂', openWith:'[smile=10]音樂', closeWith:'[/smile]', className:"col4-s1" },
			{name:'GO', openWith:'[smile=11]GO', closeWith:'[/smile]', className:"col4-s2" },
			{name:'YEAH', openWith:'[smile=12]YEAH', closeWith:'[/smile]', className:"col4-s3" },
			{name:'LOVE', openWith:'[smile=13]LOVE', closeWith:'[/smile]', className:"col5-s1" },
			{name:'撓牆', openWith:'[smile=14]撓牆', closeWith:'[/smile]', className:"col5-s2" },
			{name:'愛睏', openWith:'[smile=15]愛睏', closeWith:'[/smile]', className:"col5-s3" },
			{name:'閃光', openWith:'[smile=16]閃光', closeWith:'[/smile]', className:"col6-s1" },
			{name:'翻桌', openWith:'[smile=17]翻桌', closeWith:'[/smile]', className:"col6-s2" },
			{name:'竊笑', openWith:'[smile=18]竊笑', closeWith:'[/smile]', className:"col6-s3" },
			{name:'催眠', openWith:'[smile=19]催眠', closeWith:'[/smile]', className:"col7-s1" },
			{name:'開心', openWith:'[smile=20]開心', closeWith:'[/smile]', className:"col7-s2" },
			{name:'揉臉', openWith:'[smile=21]揉臉', closeWith:'[/smile]', className:"col7-s3" },
			{name:'哼', openWith:'[smile=22]哼', closeWith:'[/smile]', className:"col8-s1" },
			{name:'烙餅', openWith:'[smile=23]烙餅', closeWith:'[/smile]', className:"col8-s2" },
			{name:'監獄', openWith:'[smile=24]監獄', closeWith:'[/smile]', className:"col8-s3" },
			{name:'疑問', openWith:'[smile=25]疑問', closeWith:'[/smile]', className:"col9-s1" }
		]},
		{name:'Quotes', openWith:'[quote]', closeWith:'[/quote]', className:"content-quotes" },
		{name:'Code', openWith:'[code]', closeWith:'[/code]', className:"source-code" }, 
		{separator:'---------------' },
		{name:'Clean', className:"clean", replaceWith:function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, "") } }
	]
};