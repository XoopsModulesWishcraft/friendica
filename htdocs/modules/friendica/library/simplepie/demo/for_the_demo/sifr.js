/*=:project
    scalable Inman Flash Replacement (sIFR) version 3, revision 245

  =:file
    Copyright: 2006 Mark Wubben.
    Author: Mark Wubben, <http://novemberborn.net/>

  =:history
    * IFR: Shaun Inman
    * sIFR 1: Mike Davidson, Shaun Inman AND Tomas Jogin
    * sIFR 2: Mike Davidson, Shaun Inman, Tomas Jogin AND Mark Wubben

  =:license
    This software is licensed AND provided under the CC-GNU LGPL.
    See <http://creativecommons.org/licenses/LGPL/2.1/>    
*/

var parseSelector=(function(){var _1=/\s*,\s*/;var _2=/\s*([\s>+~(),]|^|$)\s*/g;var _3=/([\s>+~,]|[^(]\+|^)([#.:@])/g;var _4=/^[^\s>+~]/;var _5=/[\s#.:>+~()@]|[^\s#.:>+~()@]+/g;function parseSelector(_6,_7){_7=_7||document.documentElement;var _8=_6.split(_1),_9=[];for(var i=0;i<_8.length;i++){var _b=[_7],_c=toStream(_8[i]);for(var j=0;j<_c.length;){var _e=_c[j++],_f=_c[j++],_10="";if(_c[j]=="("){while(_c[j++]!=")" && j<_c.length){_10+=_c[j]}_10=_10.slice(0,-1)}_b=select(_b,_e,_f,_10)}_9=_9.concat(_b)}return _9}function toStream(_11){var _12=_11.replace(_2,"$1").replace(_3,"$1*$2");if(_4.test(_12)){_12=" "+_12}return _12.match(_5)||[]}function select(_13,_14,_15,_16){return (_17[_14])?_17[_14](_13,_15,_16):[]}var _18={toArray:function(_19){var a=[];for(var i=0;i<_19.length;i++){a.push(_19[i])}return a}};var dom={isTag:function(_1d,tag){return (tag=="*")||(tag.toLowerCase()==_1d.nodeName.toLowerCase())},previousSiblingElement:function(_1f){do{_1f=_1f.previousSibling}while(_1f && _1f.nodeType!=1);return _1f},nextSiblingElement:function(_20){do{_20=_20.nextSibling}while(_20 && _20.nodeType!=1);return _20},hasClass:function(_21,_22){return (_22.className||"").match("(^|\\s)"+_21+"(\\s|$)")},getByTag:function(tag,_24){return _24.getElementsByTagName(tag)}};var _17={"#":function(_25,_26){for(var i=0;i<_25.length;i++){if(_25[i].getAttribute("id")==_26){return [_25[i]]}}return []}," ":function(_28,_29){var _2a=[];for(var i=0;i<_28.length;i++){_2a=_2a.concat(_18.toArray(dom.getByTag(_29,_28[i])))}return _2a},">":function(_2c,_2d){var _2e=[];for(var i=0,_30;i<_2c.length;i++){_30=_2c[i];for(var j=0,_32;j<_30.childNodes.length;j++){_32=_30.childNodes[j];if(_32.nodeType==1 && dom.isTag(_32,_2d)){_2e.push(_32)}}}return _2e},".":function(_33,_34){var _35=[];for(var i=0,_37;i<_33.length;i++){_37=_33[i];if(dom.hasClass([_34],_37)){_35.push(_37)}}return _35},":":function(_38,_39,_3a){return (pseudoClasses[_39])?pseudoClasses[_39](_38,_3a):[]}};parseSelector.selectors=_17;parseSelector.pseudoClasses={};parseSelector.util=_18;parseSelector.dom=dom;return parseSelector})();
var sIFR=new function(){var _3b=this;var _3c="sIFR-active";var _3d="sIFR-replaced";var _3e="sIFR-replacing";var _3f="sIFR-flash";var _40="sIFR-ignore";var _41="sIFR-alternate";var _42="sIFR-class";var _43="sIFR-layout";var _44=6;var _45=126;var _46=8;var _47="SIFR-PREFETCHED";var _48=[10,1.55,19,1.45,32,1.35,71,1.3,1.25];var _49=5;this.isActive=false;this.isEnabled=true;this.hideElements=true;this.preserveSingleWhitespace=false;this.fixWrap=true;this.fixHover=true;this.registerEvents=true;this.setPrefetchCookie=true;this.cookiePath="/";this.domains=[];this.fromLocal=true;this.forceClear=false;this.forceWidth=false;this.fitExactly=false;this.forceTextTransform=true;this.useDomContentLoaded=true;this.debugMode=false;this.hasFlashClassSet=false;this.delayCss=false;this.callbacks=[];var _4a=0;var _4b=false,_4c=false;var dom=new function(){var _4e="http://www.w3.org/1999/xhtml";this.getBody=function(){var _4f=document.getElementsByTagName("body");if(_4f.length==1){return _4f[0]}return null};this.addClass=function(_50,_51){if(_51){_51.className=((_51.className||"")==""?"":_51.className+" ")+_50}};this.removeClass=function(_52,_53){if(_53){_53.className=_53.className.replace(new RegExp("(^|\\s)"+_52+"(\\s|$)"),"").replace(/^\s+|(\s)\s+/g,"$1")}};this.hasClass=function(_54,_55){return new RegExp("(^|\\s)"+_54+"(\\s|$)").test(_55.className)};this.hasOneOfClassses=function(_56,_57){for(var i=0;i<_56.length;i++){if(this.hasClass(_56[i],_57)){return true}}return false};this.create=function(_59){if(document.createElementNS){return document.createElementNS(_4e,_59)}return document.createElement(_59)};this.setInnerHtml=function(_5a,_5b){if(ua.innerHtmlSupport){_5a.innerHTML=_5b}else{if(ua.xhtmlSupport){_5b=["<root xmlns=\"",_4e,"\">",_5b,"</root>"].join("");var xml=(new DOMParser()).parseFromString(_5b,"text/xml");xml=document.importNode(xml.documentElement,true);while(_5a.firstChild){_5a.removeChild(_5a.firstChild)}while(xml.firstChild){_5a.appendChild(xml.firstChild)}}}};this.nodeFromHtml=function(_5d){var _5e=this.create("div");_5e.innerHTML=_5d;return _5e.firstChild};this.getComputedStyle=function(_5f,_60){var _61;if(document.defaultView && document.defaultView.getComputedStyle){_61=document.defaultView.getComputedStyle(_5f,null)[_60]}else{if(_5f.currentStyle){_61=_5f.currentStyle[_60]}}return _61||""};this.getStyleAsInt=function(_62,_63,_64){var _65=this.getComputedStyle(_62,_63);if(_64 && !/px$/.test(_65)){return 0}_65=parseInt(_65);return isNaN(_65)?0:_65};this.getZoom=function(){return _66.zoom.getLatest()}};this.dom=dom;var ua=new function(){var ua=navigator.userAgent.toLowerCase();var _69=(navigator.product||"").toLowerCase();this.macintosh=ua.indexOf("mac")>-1;this.windows=ua.indexOf("windows")>-1;this.quicktime=false;this.opera=ua.indexOf("opera")>-1;this.konqueror=_69.indexOf("konqueror")>-1;this.ie=false/*@cc_on || true @*/;this.ieSupported=this.ie && !/ppc|smartphone|iemobile|msie\s5\.5/.test(ua)/*@cc_on  &&  @_jscript_version >= 5.5 @*/;this.ieWin=this.ie && this.windows/*@cc_on  &&  @_jscript_version >= 5.1 @*/;this.windows=this.windows && (!this.ie||this.ieWin);this.ieMac=this.ie && this.macintosh/*@cc_on  &&  @_jscript_version < 5.1 @*/;this.macintosh=this.macintosh && (!this.ie||this.ieMac);this.safari=ua.indexOf("safari")>-1;this.webkit=ua.indexOf("applewebkit")>-1 && !this.konqueror;this.khtml=this.webkit||this.konqueror;this.gecko=!this.webkit && _69=="gecko";this.operaVersion=this.opera && /.*opera(\s|\/)(\d+\.\d+)/.exec(ua)?parseInt(RegExp.$2):0;this.webkitVersion=this.webkit && /.*applewebkit\/(\d+).*/.exec(ua)?parseInt(RegExp.$1):0;this.geckoBuildDate=this.gecko && /.*gecko\/(\d{8}).*/.exec(ua)?parseInt(RegExp.$1):0;this.konquerorVersion=this.konqueror && /.*konqueror\/(\d\.\d).*/.exec(ua)?parseInt(RegExp.$1):0;this.flashVersion=0;if(this.ieWin){var axo;var _6b=false;try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7")}catch(e){try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");this.flashVersion=6;axo.AllowScriptAccess="always"}catch(e){_6b=this.flashVersion==6}if(!_6b){try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash")}catch(e){}}}if(!_6b && axo){this.flashVersion=parseFloat(/([\d,?]+)/.exec(axo.GetVariable("$version"))[1].replace(/,/g,"."))}}else{if(navigator.plugins && navigator.plugins["Shockwave Flash"]){var _6c=navigator.plugins["Shockwave Flash"];this.flashVersion=parseFloat(/(\d+\.?\d*)/.exec(_6c.description)[1]);var i=0;while(this.flashVersion>=_46 && i<navigator.mimeTypes.length){var _6e=navigator.mimeTypes[i];if(_6e.type=="application/x-shockwave-flash" && _6e.enabledPlugin.description.toLowerCase().indexOf("quicktime")>-1){this.flashVersion=0;this.quicktime=true}i++}}}this.flash=this.flashVersion>=_46;this.transparencySupport=this.macintosh||this.windows;this.computedStyleSupport=this.ie||document.defaultView && document.defaultView.getComputedStyle && (!this.gecko||this.geckoBuildDate>=20030624);this.css=true;if(this.computedStyleSupport){try{var _6f=document.getElementsByTagName("head")[0];_6f.style.backgroundColor="#FF0000";var _70=dom.getComputedStyle(_6f,"backgroundColor");this.css=!_70||/\#F{2}0{4}|rgb\(255,\s?0,\s?0\)/i.test(_70);_6f.style.backgroundColor="";_6f=null}catch(e){}}this.xhtmlSupport=!!window.DOMParser && !!document.importNode;try{var n=dom.create("span");if(!this.ieMac){n.innerHTML="x"}this.innerHtmlSupport=n.innerHTML=="x"}catch(e){this.innerHtmlSupport=false}this.zoomSupport=!!(this.opera && document.documentElement);this.geckoXml=this.gecko && (document.contentType||"").indexOf("xml")>-1;this.requiresPrefetch=this.ieWin||this.khtml;this.verifiedKonqueror=false;this.supported=this.flash && this.css && (!this.ie||this.ieSupported) && (!this.opera||this.operaVersion>=8) && (!this.webkit||this.webkitVersion>=412) && (!this.konqueror||this.konquerorVersion>3.5) && this.computedStyleSupport && (this.innerHtmlSupport||!this.khtml && this.xhtmlSupport)};this.ua=ua;var _72=new function(){var _73={leading:true,"margin-left":true,"margin-right":true,"text-indent":true};var _74=" ";function capitalize($){return $.toUpperCase()}this.normalize=function(str){if(_3b.preserveSingleWhitespace){return str.replace(/\s/g,_74)}return str.replace(/(\s)\s+/g,"$1").replace(/\xA0/,_74)};this.textTransform=function(_77,str){switch(_77){case "uppercase":str=str.toUpperCase();break;case "lowercase":str=str.toLowerCase();break;case "capitalize":var _79=str;str=str.replace(/^\w|\s\w/g,capitalize);if(str.indexOf("function capitalize")!=-1){var _7a=_79.replace(/(^|\s)(\w)/g,"$1$1$2$2").split(/^\w|\s\w/g);str="";for(var i=0;i<_7a.length;i++){str+=_7a[i].charAt(0).toUpperCase()+_7a[i].substring(1)}}break}return str};this.toHexString=function(str){if(typeof (str)!="string"||!str.charAt(0)=="#"||str.length!=4 && str.length!=7){return str}str=str.replace(/#/,"");if(str.length==3){str=str.replace(/(.)(.)(.)/,"$1$1$2$2$3$3")}return "0x"+str};this.toJson=function(obj){var _7e="";switch(typeof (obj)){case "string":_7e="\""+obj+"\"";break;case "number":case "boolean":_7e=obj.toString();break;case "object":_7e=[];for(var _7f in obj){if(obj[_7f]==Object.prototype[_7f]){continue}_7e.push("\""+_7f+"\":"+_72.toJson(obj[_7f]))}_7e="{"+_7e.join(",")+"}";break}return _7e};this.convertCssArg=function(arg){if(!arg){return {}}if(typeof (arg)=="object"){if(arg.constructor==Array){arg=arg.join("")}else{return arg}}var obj={};var _82=arg.split("}");for(var i=0;i<_82.length;i++){var $=_82[i].match(/([^\s{]+)\s*\{(.+)\s*;?\s*/);if(!$||$.length!=3){continue}if(!obj[$[1]]){obj[$[1]]={}}var _85=$[2].split(";");for(var j=0;j<_85.length;j++){var $2=_85[j].match(/\s*([^:\s]+)\s*\:\s*([^\s;]+)/);if(!$2||$2.length!=3){continue}obj[$[1]][$2[1]]=$2[2]}}return obj};this.extractFromCss=function(css,_89,_8a,_8b){var _8c=null;if(css && css[_89] && css[_89][_8a]){_8c=css[_89][_8a];if(_8b){delete css[_89][_8a]}}return _8c};this.cssToString=function(arg){var css=[];for(var _8f in arg){var _90=arg[_8f];if(_90==Object.prototype[_8f]){continue}css.push(_8f,"{");for(var _91 in _90){if(_90[_91]==Object.prototype[_91]){continue}var _92=_90[_91];if(_73[_91]){_92=parseInt(_92,10)}css.push(_91,":",_92,";")}css.push("}")}return escape(css.join(""))};this.bind=function(_93,_94){return function(){_93[_94].apply(_93,arguments)}}};this.util=_72;var _66={};_66.fragmentIdentifier=new function(){this.fix=true;var _95;this.cache=function(){_95=document.title};function doFix(){document.title=_95}this.restore=function(){if(this.fix){setTimeout(doFix,0)}}};_66.synchronizer=new function(){this.isBlocked=false;this.block=function(){this.isBlocked=true};this.unblock=function(){this.isBlocked=false;_96.replaceAll()}};_66.zoom=new function(){var _97=100;this.getLatest=function(){return _97};if(ua.zoomSupport && ua.opera){var _98=document.createElement("div");_98.style.position="fixed";_98.style.left="-65536px";_98.style.top="0";_98.style.height="100%";_98.style.width="1px";_98.style.zIndex="-32";document.documentElement.appendChild(_98);function updateZoom(){if(!_98){return}var _99=window.innerHeight/_98.offsetHeight;var _9a=Math.round(_99*100)%10;if(_9a>5){_99=Math.round(_99*100)+10-_9a}else{_99=Math.round(_99*100)-_9a}_97=isNaN(_99)?100:_99;_66.synchronizer.unblock();document.documentElement.removeChild(_98);_98=null}_66.synchronizer.block();setTimeout(updateZoom,54)}};this.hacks=_66;var _9b={kwargs:[],replaceAll:function(){for(var i=0;i<this.kwargs.length;i++){_3b.replace(this.kwargs[i])}this.kwargs=[]}};var _96={kwargs:[],replaceAll:_9b.replaceAll};function isValidDomain(){if(_3b.domains.length==0){return true}var _9d="";try{_9d=document.domain}catch(e){}if(_3b.fromLocal && sIFR.domains[0]!="localhost"){sIFR.domains.unshift("localhost")}for(var i=0;i<_3b.domains.length;i++){var _9f=_3b.domains[i];if(_9f=="*"||_9f==_9d){return true}var _a0=_9f.lastIndexOf("*");if(_a0>-1){_9f=_9f.substr(_a0+1);var _a1=_9d.lastIndexOf(_9f);if(_a1>-1 && (_a1+_9f.length)==_9d.length){return true}}}return false}this.activate=function(){if(!ua.supported||!this.isEnabled||this.isActive||!isValidDomain()){return}if(arguments.length>0){this.prefetch.apply(this,arguments)}this.isActive=true;if(this.hideElements){this.setFlashClass()}if(ua.ieWin && _66.fragmentIdentifier.fix && window.location.hash!=""){_66.fragmentIdentifier.cache()}else{_66.fragmentIdentifier.fix=false}if(!this.registerEvents){return}function handler(evt){_3b.initialize();if(evt && evt.type=="load"){if(document.removeEventListener){document.removeEventListener("DOMContentLoaded",handler,false)}if(window.removeEventListener){window.removeEventListener("load",handler,false)}}}if(window.addEventListener){if(_3b.useDomContentLoaded && ua.gecko){document.addEventListener("DOMContentLoaded",handler,false)}window.addEventListener("load",handler,false)}else{if(ua.ieWin){if(_3b.useDomContentLoaded){document.write("<scr"+"ipt id=__sifr_ie_onload defer src=//:></script>");document.getElementById("__sifr_ie_onload").onreadystatechange=function(){if(this.readyState=="complete"){handler();this.removeNode()}}}window.attachEvent("onload",handler)}}};this.setFlashClass=function(){if(this.hasFlashClassSet){return}dom.addClass(_3c,dom.getBody()||document.documentElement);this.hasFlashClassSet=true};this.removeFlashClass=function(){if(!this.hasFlashClassSet){return}dom.removeClass(_3c,dom.getBody());dom.removeClass(_3c,document.documentElement);this.hasFlashClassSet=false};this.initialize=function(){if(_4c||!this.isActive||!this.isEnabled){return}_4c=true;_9b.replaceAll();clearPrefetch()};function getSource(src){if(typeof (src)!="string"){if(src.src){src=src.src}if(typeof (src)!="string"){var _a4=[];for(var _a5 in src){if(src[_a5]!=Object.prototype[_a5]){_a4.push(_a5)}}_a4.sort().reverse();var _a6="";var i=-1;while(!_a6 && ++i<_a4.length){if(parseFloat(_a4[i])<=ua.flashVersion){_a6=src[_a4[i]]}}src=_a6}}if(!src && _3b.debugMode){throw new Error("sIFR: Could not determine appropriate source")}if(ua.ie && src.charAt(0)=="/"){src=window.location.toString().replace(/([^:]+)(:\/?\/?)([^\/]+).*/,"$1$2$3")+src}return src}this.prefetch=function(){if(!ua.requiresPrefetch||!ua.supported||!this.isEnabled||!isValidDomain()){return}if(this.setPrefetchCookie && new RegExp(";?"+_47+"=true;?").test(document.cookie)){return}try{_4b=true;if(ua.ieWin){prefetchIexplore(arguments)}else{prefetchLight(arguments)}if(this.setPrefetchCookie){document.cookie=_47+"=true;path="+this.cookiePath}}catch(e){if(_3b.debugMode){throw e}}};function prefetchIexplore(_a8){for(var i=0;i<_a8.length;i++){document.write("<script defer type=\"sifr/prefetch\" src=\""+getSource(_a8[i])+"\"></script>")}}function prefetchLight(_aa){for(var i=0;i<_aa.length;i++){new Image().src=getSource(_aa[i])}}function clearPrefetch(){if(!ua.ieWin||!_4b){return}try{var _ac=document.getElementsByTagName("script");for(var i=_ac.length-1;i>=0;i--){var _ae=_ac[i];if(_ae.type=="sifr/prefetch"){_ae.parentNode.removeChild(_ae)}}}catch(e){}}function getRatio(_af,_b0){for(var i=0;i<_b0.length;i+=2){if(_af<=_b0[i]){return _b0[i+1]}}return _b0[_b0.length-1]}function getFilters(obj){var _b3=[];for(var _b4 in obj){if(obj[_b4]==Object.prototype[_b4]){continue}var _b5=obj[_b4];_b4=[_b4.replace(/filter/i,"")+"Filter"];for(var _b6 in _b5){if(_b5[_b6]==Object.prototype[_b6]){continue}_b4.push(_b6+":"+escape(_72.toJson(_72.toHexString(_b5[_b6]))))}_b3.push(_b4.join(","))}return _b3.join(";")}function calculate(_b7){var _b8,_b9;if(!ua.ie){_b8=dom.getStyleAsInt(_b7,"lineHeight");_b9=Math.floor(dom.getStyleAsInt(_b7,"height")/_b8)}else{if(ua.ie){var _ba=_b7.innerHTML;_b7.style.visibility="visible";_b7.style.overflow="visible";_b7.style.position="static";_b7.style.zoom="normal";_b7.style.writingMode="lr-tb";_b7.style.width=_b7.style.height="auto";_b7.style.maxWidth=_b7.style.maxHeight=_b7.style.styleFloat="none";var _bb=_b7;var _bc=_b7.currentStyle.hasLayout;if(_bc){dom.setInnerHtml(_b7,"<div class=\""+_43+"\">X<br />X<br />X</div>");_bb=_b7.firstChild}else{dom.setInnerHtml(_b7,"X<br />X<br />X")}var _bd=_bb.getClientRects();_b8=_bd[1].bottom-_bd[1].top;_b8=Math.ceil(_b8*0.8);if(_bc){dom.setInnerHtml(_b7,"<div class=\""+_43+"\">"+_ba+"</div>");_bb=_b7.firstChild}else{dom.setInnerHtml(_b7,_ba)}_bd=_bb.getClientRects();_b9=_bd.length;if(_bc){dom.setInnerHtml(_b7,_ba)}_b7.style.visibility=_b7.style.width=_b7.style.height=_b7.style.maxWidth=_b7.style.maxHeight=_b7.style.overflow=_b7.style.styleFloat=_b7.style.position=_b7.style.zoom=_b7.style.writingMode=""}}return {lineHeight:_b8,lines:_b9}}this.replace=function(_be,_bf){if(!ua.supported){return}if(_bf){for(var _c0 in _be){if(typeof (_bf[_c0])=="undefined"){_bf[_c0]=_be[_c0]}}_be=_bf}if(!_4c){return _9b.kwargs.push(_be)}if(_66.synchronizer.isBlocked){return _96.kwargs.push(_be)}var _c1=_be.elements;if(!_c1 && parseSelector){_c1=parseSelector(_be.selector)}if(_c1.length==0){return}this.setFlashClass();var src=getSource(_be.src);var css=_72.convertCssArg(_be.css);var _c4=getFilters(_be.filters);var _c5=(_be.forceClear==null)?_3b.forceClear:_be.forceClear;var _c6=(_be.fitExactly==null)?_3b.fitExactly:_be.fitExactly;var _c7=_c6||(_be.forceWidth==null?_3b.forceWidth:_be.forceWidth);var _c8=parseInt(_72.extractFromCss(css,".sIFR-root","leading"))||0;var _c9=_72.extractFromCss(css,".sIFR-root","font-size",true)||0;var _ca=_72.extractFromCss(css,".sIFR-root","background-color",true)||"#FFFFFF";var _cb=_72.extractFromCss(css,".sIFR-root","kerning",true)||"";var _cc=_be.gridFitType||_72.extractFromCss(css,".sIFR-root","text-align")=="right"?"subpixel":"pixel";var _cd=_3b.forceTextTransform?_72.extractFromCss(css,".sIFR-root","text-transform",true)||"none":"none";var _ce=_72.extractFromCss(css,".sIFR-root","opacity",true)||"100";var _cf=_be.pixelFont||false;var _d0=_be.ratios||_48;if(parseInt(_c9).toString()!=_c9 && _c9.indexOf("px")==-1){_c9=0}else{_c9=parseInt(_c9)}if(parseFloat(_ce)<1){_ce=100*parseFloat(_ce)}var _d1=null;var _d2="";if(_c6){_72.extractFromCss(css,".sIFR-root","text-align",true)}if(!_be.modifyCss){_d2=_72.cssToString(css);_d1=_3b.fixHover && _d2.indexOf("%3Ahover")>-1}var _d3=!ua.opera && _3b.delayCss;var _d4=_be.wmode||"";if(!_d4){if(_be.transparent){_d4="transparent"}else{if(_be.opaque){_d4="opaque"}}}if(_d4=="transparent"){if(!ua.transparencySupport){_d4="opaque"}else{_ca="transparent"}}for(var i=0;i<_c1.length;i++){var _d6=_c1[i];if(!ua.verifiedKonqueror){if(dom.getComputedStyle(_d6,"lineHeight").match(/e\+08px/)){ua.supported=_3b.isEnabled=false;this.removeFlashClass();return}ua.verifiedKonqueror=true}if(dom.hasOneOfClassses([_3d,_3e,_40,_41],_d6)){continue}var _d7=_d6.offsetHeight;var _d8=_d6.offsetWidth;var _d9=dom.getComputedStyle(_d6,"display");if(!_d7||!_d8||_d9==null||_d9=="none"){continue}if(_c5 && ua.gecko){_d6.style.clear="both"}var _da=null;if(_3b.fixWrap && ua.ie && _d9=="block"){_da=_d6.innerHTML;dom.setInnerHtml(_d6,"X")}_d8=dom.getStyleAsInt(_d6,"width",ua.ie);if(_d8==0){var _db=dom.getStyleAsInt(_d6,"paddingRight",true);var _dc=dom.getStyleAsInt(_d6,"paddingLeft",true);var _dd=dom.getStyleAsInt(_d6,"borderRightWidth",true);var _de=dom.getStyleAsInt(_d6,"borderLeftWidth",true);_d8=_d6.offsetWidth-_dc-_db-_de-_dd}if(_da && _3b.fixWrap && ua.ie){dom.setInnerHtml(_d6,_da)}var _df,_e0;if(!_c9){var _e1=calculate(_d6);_df=Math.min(_45,Math.max(_44,_e1.lineHeight));if(_cf){_df=Math.max(8,8*Math.round(_df/8))}_e0=_e1.lines;if(isNaN(_e0)||!isFinite(_e0)||_e0==0){_e0=1}if(_e0>1 && _c8){_d7+=Math.round((_e0-1)*_c8)}}else{_df=_c9;_e0=1}_d7=Math.round(_e0*_df);if(_c5 && ua.gecko){_d6.style.clear=""}var _e2=dom.create("span");_e2.className=_41;var _e3=_d6.cloneNode(true);for(var j=0,l=_e3.childNodes.length;j<l;j++){_e2.appendChild(_e3.childNodes[j].cloneNode(true))}if(_be.modifyContent){_be.modifyContent(_e3,_be.selector)}if(_be.modifyCss){_d2=_be.modifyCss(css,_e3,_be.selector)}if(_d1==null){_d1=_3b.fixHover && _d2.indexOf("%3Ahover")>-1}var _e6=handleContent(_e3,_cd);if(_be.modifyContentString){_e6=_be.modifyContentString(_e6,_be.selector)}if(_e6==""){continue}var _e7=["content="+_e6,"width="+_d8,"height="+_d7,"fitexactly="+(_c6?"true":""),"tunewidth="+(_be.tuneWidth||""),"tuneheight="+(_be.tuneHeight||""),"offsetleft="+(_be.offsetLeft||""),"offsettop="+(_be.offsetTop||""),"thickness="+(_be.thickness||""),"sharpness="+(_be.sharpness||""),"kerning="+_cb,"gridfittype="+_cc,"zoomsupport="+ua.zoomSupport,"flashfilters="+_c4,"opacity="+_ce,"blendmode="+(_be.blendMode||""),"size="+_df,"zoom="+dom.getZoom(),"css="+_d2,"selectable="+(_be.selectable==null?"true":_be.selectable),"lines="+_e0];var _e8=encodeURI(_e7.join("&amp;"));var _e9="sIFR_callback_"+_4a++;var _ea=new CallbackInfo(_e9,_e7,_be.onReplacement,_d1);window[_e9+"_DoFSCommand"]=(function(_eb){return function(_ec,arg){_eb.handle(_ec,arg)}})(_ea);_d7=Math.round(_e0*getRatio(_df,_d0)*_df)+_49;var _ee=_c7?_d8:"100%";var _ef;if(ua.ie){_ef=["<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" id=\"",_e9,"\" sifr=\"true\" width=\"",_ee,"\" height=\"",_d7,"\" class=\"",_3f,"\">","<param name=\"movie\" value=\"",src,"\"></param>","<param name=\"flashvars\" value=\"",_e8,"\"></param>","<param name=\"allowScriptAccess\" value=\"always\"></param>","<param name=\"quality\" value=\"best\"></param>","<param name=\"wmode\" value=\"",_d4,"\"></param>","<param name=\"bgcolor\" value=\"",_ca,"\"></param>","<param name=\"name\" value=\"",_e9,"\"></param>","</object>","<scr","ipt event=FSCommand(info,args) for=",_e9,">",_e9,"_DoFSCommand(info, args);","</","script>"].join("")}else{_ef=["<embed type=\"application/x-shockwave-flash\"",(_d3?" class=\""+_3f+"\"":"")," src=\"",src,"\" quality=\"best\" flashvars=\"",_e8,"\" width=\"",_ee,"\" height=\"",_d7,"\" wmode=\"",_d4,"\" bgcolor=\"",_ca,"\" name=\"",_e9,"\" id=\"",_e9,"\" allowScriptAccess=\"always\" sifr=\"true\"></embed>"].join("")}dom.setInnerHtml(_d6,_ef);_ea.flashNode=_d6.firstChild;_ea.html=_ef;_3b.callbacks.push(_ea);if(_be.selector){if(!_3b.callbacks[_be.selector]){_3b.callbacks[_be.selector]=[_ea]}else{_3b.callbacks[_be.selector].push(_ea)}}_d6.appendChild(_e2);dom.addClass(_d3?_3e:_3d,_d6);_ea.setupFixHover()}_66.fragmentIdentifier.restore()};this.getCallbackByFlashElement=function(_f0){for(var i=0;i<_3b.callbacks.length;i++){if(_3b.callbacks[i].id==_f0.getAttribute("id")){return _3b.callbacks[i]}}};function handleContent(_f2,_f3){var _f4=[],_f5=[];var _f6=_f2.childNodes;var i=0;while(i<_f6.length){var _f8=_f6[i];if(_f8.nodeType==3){var _f9=_72.normalize(_f8.nodeValue);_f9=_72.textTransform(_f3,_f9);_f5.push(_f9.replace(/\%/g,"%25").replace(/\&/g,"%26").replace(/\,/g,"%2C").replace(/\+/g,"%2B"))}if(_f8.nodeType==1){var _fa=[];var _fb=_f8.nodeName.toLowerCase();var _fc=_f8.className||"";if(/\s+/.test(_fc)){if(_fc.indexOf(_42)>-1){_fc=_fc.match("(\\s|^)"+_42+"-([^\\s$]*)(\\s|$)")[2]}else{_fc=_fc.match(/^([^\s]+)/)[1]}}if(_fc!=""){_fa.push("class=\""+_fc+"\"")}if(_fb=="a"){var _fd=_f8.getAttribute("href")||"";var _fe=_f8.getAttribute("target")||"";_fa.push("href=\""+_fd+"\"","target=\""+_fe+"\"")}_f5.push("<"+_fb+(_fa.length>0?" ":"")+escape(_fa.join(" "))+">");if(_f8.hasChildNodes()){_f4.push(i);i=0;_f6=_f8.childNodes;continue}else{if(!/^(br|img)$/i.test(_f8.nodeName)){_f5.push("</",_f8.nodeName.toLowerCase(),">")}}}if(_f4.length>0 && !_f8.nextSibling){do{i=_f4.pop();_f6=_f8.parentNode.parentNode.childNodes;_f8=_f6[i];if(_f8){_f5.push("</",_f8.nodeName.toLowerCase(),">")}}while(i==_f6.length-1 && _f4.length>0)}i++}return _f5.join("").replace(/\n|\r/g,"")}function CallbackInfo(id,vars,_101,_102){this.id=id;this.vars=vars;this._replacementHandler=_101;this._firedReplacementEvent=!(this._replacementHandler!=null);this._fixHover=_102;this._setClasses=!_3b.delayCss;this.html="";this._pings=0}CallbackInfo.prototype.getFlashElement=function(){return document.getElementById(this.id)};CallbackInfo.prototype.handle=function(info,arg){if(/(FSCommand\:)?resize/.test(info)){var _105=this.getFlashElement();var $=arg.split(/\:|,/);_105.setAttribute($[0],$[1]);if($.length>2){_105.setAttribute($[2],$[3])}if(!this._setClasses){if(!ua.ie && !ua.opera){dom.addClass(_3f,_105)}dom.removeClass(_3e,_105.parentNode);dom.addClass(_3d,_105.parentNode);this._setClasses=true}if(ua.khtml){var _107=_105.offsetHeight}if(!this._firedReplacementEvent){this._replacementHandler(this);this._firedReplacementEvent=true}}else{if(/(FSCommand\:)?resetmovie/.test(info)){this.resetMovie()}else{if(/(FSCommand\:)?ping/.test(info)){if(this._pings>0){this.setupFixHover()}this._pings++}else{if(this.debugHandler && /(FSCommand\:)?debug/.test(info)){this.debugHandler(info,arg)}}}}};CallbackInfo.prototype.call=function(type,_109){var _10a=this.getFlashElement();if(!_10a){return}_10a.SetVariable("callbackType",type);_10a.SetVariable("callbackValue",_109);_10a.SetVariable("callbackTrigger",true)};CallbackInfo.prototype.replaceText=function(_10b){_10b=escape(_10b);this.call("replacetext",_10b);this.vars[0]="content="+_10b;this.html=this.html.replace(/(flashvars(=|\"\svalue=)\")[^\"]+/,"$1"+encodeURI(this.vars.join("&amp;")))};CallbackInfo.prototype.resetMovie=function(){var _10c=this.getFlashElement();var node=_10c.parentNode;node.replaceChild(dom.nodeFromHtml(this.html),_10c);this.setupFixHover()};CallbackInfo.prototype.setupFixHover=function(){var _10e=this.getFlashElement();if(!this._fixHover||!_10e){return}var node=_10e.parentNode;if(node.addEventListener){node.addEventListener("mouseout",_72.bind(this,"fixHover"),false)}else{if(node.attachEvent){node.attachEvent("onmouseout",_72.bind(this,"fixHover"))}}};CallbackInfo.prototype.fixHover=function(){this.call("resettext")}};