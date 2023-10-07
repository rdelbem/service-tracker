/*! For license information please see 71.App.js.LICENSE.txt */
(self.webpackChunkservice_tracker=self.webpackChunkservice_tracker||[]).push([[71],{184:(t,e)=>{var n;!function(){"use strict";var r={}.hasOwnProperty;function o(){for(var t=[],e=0;e<arguments.length;e++){var n=arguments[e];if(n){var i=typeof n;if("string"===i||"number"===i)t.push(n);else if(Array.isArray(n)){if(n.length){var l=o.apply(null,n);l&&t.push(l)}}else if("object"===i){if(n.toString!==Object.prototype.toString&&!n.toString.toString().includes("[native code]")){t.push(n.toString());continue}for(var a in n)r.call(n,a)&&n[a]&&t.push(a)}}}return t.join(" ")}t.exports?(o.default=o,t.exports=o):void 0===(n=function(){return o}.apply(e,[]))||(t.exports=n)}()},893:(t,e,n)=>{"use strict";n.d(e,{vPQ:()=>o});var r=n(405);function o(t){return(0,r.w_)({tag:"svg",attr:{viewBox:"0 0 24 24",fill:"none",stroke:"currentColor",strokeWidth:"2",strokeLinecap:"round",strokeLinejoin:"round"},child:[{tag:"path",attr:{d:"M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"}},{tag:"path",attr:{d:"M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"}}]})(t)}},434:(t,e,n)=>{"use strict";n.d(e,{FH3:()=>o});var r=n(405);function o(t){return(0,r.w_)({tag:"svg",attr:{viewBox:"0 0 24 24"},child:[{tag:"path",attr:{fill:"none",d:"M0 0h24v24H0z"}},{tag:"path",attr:{fill:"none",d:"M0 0h24v24H0V0z"}},{tag:"path",attr:{d:"M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zm2.46-7.12l1.41-1.41L12 12.59l2.12-2.12 1.41 1.41L13.41 14l2.12 2.12-1.41 1.41L12 15.41l-2.12 2.12-1.41-1.41L10.59 14l-2.13-2.12zM15.5 4l-1-1h-5l-1 1H5v2h14V4z"}}]})(t)}},920:(t,e,n)=>{"use strict";n.d(e,{ZP:()=>l});var r=/d{1,4}|D{3,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|W{1,2}|[LlopSZN]|"[^"]*"|'[^']*'/g,o=/\b(?:[A-Z]{1,3}[A-Z][TC])(?:[-+]\d{4})?|((?:Australian )?(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time)\b/g,i=/[^-+\dA-Z]/g;function l(t,e,n,o){if(1!==arguments.length||"string"!=typeof t||/\d/.test(t)||(e=t,t=void 0),(t=t||0===t?t:new Date)instanceof Date||(t=new Date(t)),isNaN(t))throw TypeError("Invalid date");var i=(e=String(a[e]||e||a.default)).slice(0,4);"UTC:"!==i&&"GMT:"!==i||(e=e.slice(4),n=!0,"GMT:"===i&&(o=!0));var l=function(){return n?"getUTC":"get"},p=function(){return t[l()+"Date"]()},y=function(){return t[l()+"Day"]()},h=function(){return t[l()+"Month"]()},v=function(){return t[l()+"FullYear"]()},g=function(){return t[l()+"Hours"]()},w=function(){return t[l()+"Minutes"]()},b=function(){return t[l()+"Seconds"]()},x=function(){return t[l()+"Milliseconds"]()},S=function(){return n?0:t.getTimezoneOffset()},T=function(){return f(t)},_={d:function(){return p()},dd:function(){return s(p())},ddd:function(){return c.dayNames[y()]},DDD:function(){return u({y:v(),m:h(),d:p(),_:l(),dayName:c.dayNames[y()],short:!0})},dddd:function(){return c.dayNames[y()+7]},DDDD:function(){return u({y:v(),m:h(),d:p(),_:l(),dayName:c.dayNames[y()+7]})},m:function(){return h()+1},mm:function(){return s(h()+1)},mmm:function(){return c.monthNames[h()]},mmmm:function(){return c.monthNames[h()+12]},yy:function(){return String(v()).slice(2)},yyyy:function(){return s(v(),4)},h:function(){return g()%12||12},hh:function(){return s(g()%12||12)},H:function(){return g()},HH:function(){return s(g())},M:function(){return w()},MM:function(){return s(w())},s:function(){return b()},ss:function(){return s(b())},l:function(){return s(x(),3)},L:function(){return s(Math.floor(x()/10))},t:function(){return g()<12?c.timeNames[0]:c.timeNames[1]},tt:function(){return g()<12?c.timeNames[2]:c.timeNames[3]},T:function(){return g()<12?c.timeNames[4]:c.timeNames[5]},TT:function(){return g()<12?c.timeNames[6]:c.timeNames[7]},Z:function(){return o?"GMT":n?"UTC":m(t)},o:function(){return(S()>0?"-":"+")+s(100*Math.floor(Math.abs(S())/60)+Math.abs(S())%60,4)},p:function(){return(S()>0?"-":"+")+s(Math.floor(Math.abs(S())/60),2)+":"+s(Math.floor(Math.abs(S())%60),2)},S:function(){return["th","st","nd","rd"][p()%10>3?0:(p()%100-p()%10!=10)*p()%10]},W:function(){return T()},WW:function(){return s(T())},N:function(){return d(t)}};return e.replace(r,(function(t){return t in _?_[t]():t.slice(1,t.length-1)}))}var a={default:"ddd mmm dd yyyy HH:MM:ss",shortDate:"m/d/yy",paddedShortDate:"mm/dd/yyyy",mediumDate:"mmm d, yyyy",longDate:"mmmm d, yyyy",fullDate:"dddd, mmmm d, yyyy",shortTime:"h:MM TT",mediumTime:"h:MM:ss TT",longTime:"h:MM:ss TT Z",isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:sso",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'",expiresHeaderFormat:"ddd, dd mmm yyyy HH:MM:ss Z"},c={dayNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"],timeNames:["a","p","am","pm","A","P","AM","PM"]},s=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:2;return String(t).padStart(e,"0")},u=function(t){var e=t.y,n=t.m,r=t.d,o=t._,i=t.dayName,l=t.short,a=void 0!==l&&l,c=new Date,s=new Date;s.setDate(s[o+"Date"]()-1);var u=new Date;u.setDate(u[o+"Date"]()+1);return c[o+"FullYear"]()===e&&c[o+"Month"]()===n&&c[o+"Date"]()===r?a?"Tdy":"Today":s[o+"FullYear"]()===e&&s[o+"Month"]()===n&&s[o+"Date"]()===r?a?"Ysd":"Yesterday":u[o+"FullYear"]()===e&&u[o+"Month"]()===n&&u[o+"Date"]()===r?a?"Tmw":"Tomorrow":i},f=function(t){var e=new Date(t.getFullYear(),t.getMonth(),t.getDate());e.setDate(e.getDate()-(e.getDay()+6)%7+3);var n=new Date(e.getFullYear(),0,4);n.setDate(n.getDate()-(n.getDay()+6)%7+3);var r=e.getTimezoneOffset()-n.getTimezoneOffset();e.setHours(e.getHours()-r);var o=(e-n)/6048e5;return 1+Math.floor(o)},d=function(t){var e=t.getDay();return 0===e&&(e=7),e},m=function(t){return(String(t).match(o)||[""]).pop().replace(i,"").replace(/GMT\+0000/g,"UTC")}},884:(t,e,n)=>{"use strict";n.d(e,{u:()=>yt});var r=n(294),o=n(184);const i=Math.min,l=Math.max,a=Math.round,c=(Math.floor,t=>({x:t,y:t})),s={left:"right",right:"left",bottom:"top",top:"bottom"},u={start:"end",end:"start"};function f(t,e,n){return l(t,i(e,n))}function d(t,e){return"function"==typeof t?t(e):t}function m(t){return t.split("-")[0]}function p(t){return t.split("-")[1]}function y(t){return"x"===t?"y":"x"}function h(t){return"y"===t?"height":"width"}function v(t){return["top","bottom"].includes(m(t))?"y":"x"}function g(t){return y(v(t))}function w(t){return t.replace(/start|end/g,(t=>u[t]))}function b(t){return t.replace(/left|right|bottom|top/g,(t=>s[t]))}function x(t){return"number"!=typeof t?function(t){return{top:0,right:0,bottom:0,left:0,...t}}(t):{top:t,right:t,bottom:t,left:t}}function S(t){return{...t,top:t.y,left:t.x,right:t.x+t.width,bottom:t.y+t.height}}function T(t,e,n){let{reference:r,floating:o}=t;const i=v(e),l=g(e),a=h(l),c=m(e),s="y"===i,u=r.x+r.width/2-o.width/2,f=r.y+r.height/2-o.height/2,d=r[a]/2-o[a]/2;let y;switch(c){case"top":y={x:u,y:r.y-o.height};break;case"bottom":y={x:u,y:r.y+r.height};break;case"right":y={x:r.x+r.width,y:f};break;case"left":y={x:r.x-o.width,y:f};break;default:y={x:r.x,y:r.y}}switch(p(e)){case"start":y[l]-=d*(n&&s?-1:1);break;case"end":y[l]+=d*(n&&s?-1:1)}return y}async function _(t,e){var n;void 0===e&&(e={});const{x:r,y:o,platform:i,rects:l,elements:a,strategy:c}=t,{boundary:s="clippingAncestors",rootBoundary:u="viewport",elementContext:f="floating",altBoundary:m=!1,padding:p=0}=d(e,t),y=x(p),h=a[m?"floating"===f?"reference":"floating":f],v=S(await i.getClippingRect({element:null==(n=await(null==i.isElement?void 0:i.isElement(h)))||n?h:h.contextElement||await(null==i.getDocumentElement?void 0:i.getDocumentElement(a.floating)),boundary:s,rootBoundary:u,strategy:c})),g="floating"===f?{...l.floating,x:r,y:o}:l.reference,w=await(null==i.getOffsetParent?void 0:i.getOffsetParent(a.floating)),b=await(null==i.isElement?void 0:i.isElement(w))&&await(null==i.getScale?void 0:i.getScale(w))||{x:1,y:1},T=S(i.convertOffsetParentRelativeRectToViewportRelativeRect?await i.convertOffsetParentRelativeRectToViewportRelativeRect({rect:g,offsetParent:w,strategy:c}):g);return{top:(v.top-T.top+y.top)/b.y,bottom:(T.bottom-v.bottom+y.bottom)/b.y,left:(v.left-T.left+y.left)/b.x,right:(T.right-v.right+y.right)/b.x}}const E=function(t){return void 0===t&&(t={}),{name:"flip",options:t,async fn(e){var n,r;const{placement:o,middlewareData:i,rects:l,initialPlacement:a,platform:c,elements:s}=e,{mainAxis:u=!0,crossAxis:f=!0,fallbackPlacements:y,fallbackStrategy:v="bestFit",fallbackAxisSideDirection:x="none",flipAlignment:S=!0,...T}=d(t,e);if(null!=(n=i.arrow)&&n.alignmentOffset)return{};const E=m(o),A=m(a)===a,D=await(null==c.isRTL?void 0:c.isRTL(s.floating)),M=y||(A||!S?[b(a)]:function(t){const e=b(t);return[w(t),e,w(e)]}(a));y||"none"===x||M.push(...function(t,e,n,r){const o=p(t);let i=function(t,e,n){const r=["left","right"],o=["right","left"],i=["top","bottom"],l=["bottom","top"];switch(t){case"top":case"bottom":return n?e?o:r:e?r:o;case"left":case"right":return e?i:l;default:return[]}}(m(t),"start"===n,r);return o&&(i=i.map((t=>t+"-"+o)),e&&(i=i.concat(i.map(w)))),i}(a,S,x,D));const k=[a,...M],L=await _(e,T),R=[];let N=(null==(r=i.flip)?void 0:r.overflows)||[];if(u&&R.push(L[E]),f){const t=function(t,e,n){void 0===n&&(n=!1);const r=p(t),o=g(t),i=h(o);let l="x"===o?r===(n?"end":"start")?"right":"left":"start"===r?"bottom":"top";return e.reference[i]>e.floating[i]&&(l=b(l)),[l,b(l)]}(o,l,D);R.push(L[t[0]],L[t[1]])}if(N=[...N,{placement:o,overflows:R}],!R.every((t=>t<=0))){var O,H;const t=((null==(O=i.flip)?void 0:O.index)||0)+1,e=k[t];if(e)return{data:{index:t,overflows:N},reset:{placement:e}};let n=null==(H=N.filter((t=>t.overflows[0]<=0)).sort(((t,e)=>t.overflows[1]-e.overflows[1]))[0])?void 0:H.placement;if(!n)switch(v){case"bestFit":{var C;const t=null==(C=N.map((t=>[t.placement,t.overflows.filter((t=>t>0)).reduce(((t,e)=>t+e),0)])).sort(((t,e)=>t[1]-e[1]))[0])?void 0:C[0];t&&(n=t);break}case"initialPlacement":n=a}if(o!==n)return{reset:{placement:n}}}return{}}}};const A=function(t){return void 0===t&&(t=0),{name:"offset",options:t,async fn(e){const{x:n,y:r}=e,o=await async function(t,e){const{placement:n,platform:r,elements:o}=t,i=await(null==r.isRTL?void 0:r.isRTL(o.floating)),l=m(n),a=p(n),c="y"===v(n),s=["left","top"].includes(l)?-1:1,u=i&&c?-1:1,f=d(e,t);let{mainAxis:y,crossAxis:h,alignmentAxis:g}="number"==typeof f?{mainAxis:f,crossAxis:0,alignmentAxis:null}:{mainAxis:0,crossAxis:0,alignmentAxis:null,...f};return a&&"number"==typeof g&&(h="end"===a?-1*g:g),c?{x:h*u,y:y*s}:{x:y*s,y:h*u}}(e,t);return{x:n+o.x,y:r+o.y,data:o}}}},D=function(t){return void 0===t&&(t={}),{name:"shift",options:t,async fn(e){const{x:n,y:r,placement:o}=e,{mainAxis:i=!0,crossAxis:l=!1,limiter:a={fn:t=>{let{x:e,y:n}=t;return{x:e,y:n}}},...c}=d(t,e),s={x:n,y:r},u=await _(e,c),p=v(m(o)),h=y(p);let g=s[h],w=s[p];if(i){const t="y"===h?"bottom":"right";g=f(g+u["y"===h?"top":"left"],g,g-u[t])}if(l){const t="y"===p?"bottom":"right";w=f(w+u["y"===p?"top":"left"],w,w-u[t])}const b=a.fn({...e,[h]:g,[p]:w});return{...b,data:{x:b.x-n,y:b.y-r}}}}};function M(t){return R(t)?(t.nodeName||"").toLowerCase():"#document"}function k(t){var e;return(null==t||null==(e=t.ownerDocument)?void 0:e.defaultView)||window}function L(t){var e;return null==(e=(R(t)?t.ownerDocument:t.document)||window.document)?void 0:e.documentElement}function R(t){return t instanceof Node||t instanceof k(t).Node}function N(t){return t instanceof Element||t instanceof k(t).Element}function O(t){return t instanceof HTMLElement||t instanceof k(t).HTMLElement}function H(t){return"undefined"!=typeof ShadowRoot&&(t instanceof ShadowRoot||t instanceof k(t).ShadowRoot)}function C(t){const{overflow:e,overflowX:n,overflowY:r,display:o}=B(t);return/auto|scroll|overlay|hidden|clip/.test(e+r+n)&&!["inline","contents"].includes(o)}function W(t){return["table","td","th"].includes(M(t))}function F(t){const e=P(),n=B(t);return"none"!==n.transform||"none"!==n.perspective||!!n.containerType&&"normal"!==n.containerType||!e&&!!n.backdropFilter&&"none"!==n.backdropFilter||!e&&!!n.filter&&"none"!==n.filter||["transform","perspective","filter"].some((t=>(n.willChange||"").includes(t)))||["paint","layout","strict","content"].some((t=>(n.contain||"").includes(t)))}function P(){return!("undefined"==typeof CSS||!CSS.supports)&&CSS.supports("-webkit-backdrop-filter","none")}function z(t){return["html","body","#document"].includes(M(t))}function B(t){return k(t).getComputedStyle(t)}function $(t){return N(t)?{scrollLeft:t.scrollLeft,scrollTop:t.scrollTop}:{scrollLeft:t.pageXOffset,scrollTop:t.pageYOffset}}function j(t){if("html"===M(t))return t;const e=t.assignedSlot||t.parentNode||H(t)&&t.host||L(t);return H(e)?e.host:e}function V(t){const e=j(t);return z(e)?t.ownerDocument?t.ownerDocument.body:t.body:O(e)&&C(e)?e:V(e)}function Y(t,e,n){var r;void 0===e&&(e=[]),void 0===n&&(n=!0);const o=V(t),i=o===(null==(r=t.ownerDocument)?void 0:r.body),l=k(o);return i?e.concat(l,l.visualViewport||[],C(o)?o:[],l.frameElement&&n?Y(l.frameElement):[]):e.concat(o,Y(o,[],n))}function Z(t){const e=B(t);let n=parseFloat(e.width)||0,r=parseFloat(e.height)||0;const o=O(t),i=o?t.offsetWidth:n,l=o?t.offsetHeight:r,c=a(n)!==i||a(r)!==l;return c&&(n=i,r=l),{width:n,height:r,$:c}}function I(t){return N(t)?t:t.contextElement}function J(t){const e=I(t);if(!O(e))return c(1);const n=e.getBoundingClientRect(),{width:r,height:o,$:i}=Z(e);let l=(i?a(n.width):n.width)/r,s=(i?a(n.height):n.height)/o;return l&&Number.isFinite(l)||(l=1),s&&Number.isFinite(s)||(s=1),{x:l,y:s}}const q=c(0);function U(t){const e=k(t);return P()&&e.visualViewport?{x:e.visualViewport.offsetLeft,y:e.visualViewport.offsetTop}:q}function X(t,e,n,r){void 0===e&&(e=!1),void 0===n&&(n=!1);const o=t.getBoundingClientRect(),i=I(t);let l=c(1);e&&(r?N(r)&&(l=J(r)):l=J(t));const a=function(t,e,n){return void 0===e&&(e=!1),!(!n||e&&n!==k(t))&&e}(i,n,r)?U(i):c(0);let s=(o.left+a.x)/l.x,u=(o.top+a.y)/l.y,f=o.width/l.x,d=o.height/l.y;if(i){const t=k(i),e=r&&N(r)?k(r):r;let n=t.frameElement;for(;n&&r&&e!==t;){const t=J(n),e=n.getBoundingClientRect(),r=B(n),o=e.left+(n.clientLeft+parseFloat(r.paddingLeft))*t.x,i=e.top+(n.clientTop+parseFloat(r.paddingTop))*t.y;s*=t.x,u*=t.y,f*=t.x,d*=t.y,s+=o,u+=i,n=k(n).frameElement}}return S({width:f,height:d,x:s,y:u})}function K(t){return X(L(t)).left+$(t).scrollLeft}function G(t,e,n){let r;if("viewport"===e)r=function(t,e){const n=k(t),r=L(t),o=n.visualViewport;let i=r.clientWidth,l=r.clientHeight,a=0,c=0;if(o){i=o.width,l=o.height;const t=P();(!t||t&&"fixed"===e)&&(a=o.offsetLeft,c=o.offsetTop)}return{width:i,height:l,x:a,y:c}}(t,n);else if("document"===e)r=function(t){const e=L(t),n=$(t),r=t.ownerDocument.body,o=l(e.scrollWidth,e.clientWidth,r.scrollWidth,r.clientWidth),i=l(e.scrollHeight,e.clientHeight,r.scrollHeight,r.clientHeight);let a=-n.scrollLeft+K(t);const c=-n.scrollTop;return"rtl"===B(r).direction&&(a+=l(e.clientWidth,r.clientWidth)-o),{width:o,height:i,x:a,y:c}}(L(t));else if(N(e))r=function(t,e){const n=X(t,!0,"fixed"===e),r=n.top+t.clientTop,o=n.left+t.clientLeft,i=O(t)?J(t):c(1);return{width:t.clientWidth*i.x,height:t.clientHeight*i.y,x:o*i.x,y:r*i.y}}(e,n);else{const n=U(t);r={...e,x:e.x-n.x,y:e.y-n.y}}return S(r)}function Q(t,e){const n=j(t);return!(n===e||!N(n)||z(n))&&("fixed"===B(n).position||Q(n,e))}function tt(t,e,n){const r=O(e),o=L(e),i="fixed"===n,l=X(t,!0,i,e);let a={scrollLeft:0,scrollTop:0};const s=c(0);if(r||!r&&!i)if(("body"!==M(e)||C(o))&&(a=$(e)),r){const t=X(e,!0,i,e);s.x=t.x+e.clientLeft,s.y=t.y+e.clientTop}else o&&(s.x=K(o));return{x:l.left+a.scrollLeft-s.x,y:l.top+a.scrollTop-s.y,width:l.width,height:l.height}}function et(t,e){return O(t)&&"fixed"!==B(t).position?e?e(t):t.offsetParent:null}function nt(t,e){const n=k(t);if(!O(t))return n;let r=et(t,e);for(;r&&W(r)&&"static"===B(r).position;)r=et(r,e);return r&&("html"===M(r)||"body"===M(r)&&"static"===B(r).position&&!F(r))?n:r||function(t){let e=j(t);for(;O(e)&&!z(e);){if(F(e))return e;e=j(e)}return null}(t)||n}const rt={convertOffsetParentRelativeRectToViewportRelativeRect:function(t){let{rect:e,offsetParent:n,strategy:r}=t;const o=O(n),i=L(n);if(n===i)return e;let l={scrollLeft:0,scrollTop:0},a=c(1);const s=c(0);if((o||!o&&"fixed"!==r)&&(("body"!==M(n)||C(i))&&(l=$(n)),O(n))){const t=X(n);a=J(n),s.x=t.x+n.clientLeft,s.y=t.y+n.clientTop}return{width:e.width*a.x,height:e.height*a.y,x:e.x*a.x-l.scrollLeft*a.x+s.x,y:e.y*a.y-l.scrollTop*a.y+s.y}},getDocumentElement:L,getClippingRect:function(t){let{element:e,boundary:n,rootBoundary:r,strategy:o}=t;const a=[..."clippingAncestors"===n?function(t,e){const n=e.get(t);if(n)return n;let r=Y(t,[],!1).filter((t=>N(t)&&"body"!==M(t))),o=null;const i="fixed"===B(t).position;let l=i?j(t):t;for(;N(l)&&!z(l);){const e=B(l),n=F(l);n||"fixed"!==e.position||(o=null),(i?!n&&!o:!n&&"static"===e.position&&o&&["absolute","fixed"].includes(o.position)||C(l)&&!n&&Q(t,l))?r=r.filter((t=>t!==l)):o=e,l=j(l)}return e.set(t,r),r}(e,this._c):[].concat(n),r],c=a[0],s=a.reduce(((t,n)=>{const r=G(e,n,o);return t.top=l(r.top,t.top),t.right=i(r.right,t.right),t.bottom=i(r.bottom,t.bottom),t.left=l(r.left,t.left),t}),G(e,c,o));return{width:s.right-s.left,height:s.bottom-s.top,x:s.left,y:s.top}},getOffsetParent:nt,getElementRects:async function(t){let{reference:e,floating:n,strategy:r}=t;const o=this.getOffsetParent||nt,i=this.getDimensions;return{reference:tt(e,await o(n),r),floating:{x:0,y:0,...await i(n)}}},getClientRects:function(t){return Array.from(t.getClientRects())},getDimensions:function(t){return Z(t)},getScale:J,isElement:N,isRTL:function(t){return"rtl"===B(t).direction}};const ot=(t,e,n)=>{const r=new Map,o={platform:rt,...n},i={...o.platform,_c:r};return(async(t,e,n)=>{const{placement:r="bottom",strategy:o="absolute",middleware:i=[],platform:l}=n,a=i.filter(Boolean),c=await(null==l.isRTL?void 0:l.isRTL(e));let s=await l.getElementRects({reference:t,floating:e,strategy:o}),{x:u,y:f}=T(s,r,c),d=r,m={},p=0;for(let n=0;n<a.length;n++){const{name:i,fn:y}=a[n],{x:h,y:v,data:g,reset:w}=await y({x:u,y:f,initialPlacement:r,placement:d,strategy:o,middlewareData:m,rects:s,platform:l,elements:{reference:t,floating:e}});u=null!=h?h:u,f=null!=v?v:f,m={...m,[i]:{...m[i],...g}},w&&p<=50&&(p++,"object"==typeof w&&(w.placement&&(d=w.placement),w.rects&&(s=!0===w.rects?await l.getElementRects({reference:t,floating:e,strategy:o}):w.rects),({x:u,y:f}=T(s,d,c))),n=-1)}return{x:u,y:f,placement:d,strategy:o,middlewareData:m}})(t,e,{...o,platform:i})};function it(t,e){void 0===e&&(e={});var n=e.insertAt;if(t&&"undefined"!=typeof document){var r=document.head||document.getElementsByTagName("head")[0],o=document.createElement("style");o.type="text/css","top"===n&&r.firstChild?r.insertBefore(o,r.firstChild):r.appendChild(o),o.styleSheet?o.styleSheet.cssText=t:o.appendChild(document.createTextNode(t))}}it(":root{--rt-color-white:#fff;--rt-color-dark:#222;--rt-color-success:#8dc572;--rt-color-error:#be6464;--rt-color-warning:#f0ad4e;--rt-color-info:#337ab7;--rt-opacity:0.9}");const lt=(t,e,n)=>{let r=null;return function(...o){const i=()=>{r=null,n||t.apply(this,o)};n&&!r&&(t.apply(this,o),r=setTimeout(i,e)),n||(r&&clearTimeout(r),r=setTimeout(i,e))}},at={anchorRefs:new Set,activeAnchor:{current:null},attach:()=>{},detach:()=>{},setActiveAnchor:()=>{}},ct=(0,r.createContext)({getTooltipData:()=>at});function st(t="DEFAULT_TOOLTIP_ID"){return(0,r.useContext)(ct).getTooltipData(t)}const ut="undefined"!=typeof window?r.useLayoutEffect:r.useEffect,ft=async({elementReference:t=null,tooltipReference:e=null,tooltipArrowReference:n=null,place:r="top",offset:o=10,strategy:l="absolute",middlewares:a=[A(Number(o)),E(),D({padding:5})]})=>{if(!t)return{tooltipStyles:{},tooltipArrowStyles:{},place:r};if(null===e)return{tooltipStyles:{},tooltipArrowStyles:{},place:r};const c=a;return n?(c.push({name:"arrow",options:s={element:n,padding:5},async fn(t){const{x:e,y:n,placement:r,rects:o,platform:l,elements:a,middlewareData:c}=t,{element:u,padding:m=0}=d(s,t)||{};if(null==u)return{};const y=x(m),v={x:e,y:n},w=g(r),b=h(w),S=await l.getDimensions(u),T="y"===w,_=T?"top":"left",E=T?"bottom":"right",A=T?"clientHeight":"clientWidth",D=o.reference[b]+o.reference[w]-v[w]-o.floating[b],M=v[w]-o.reference[w],k=await(null==l.getOffsetParent?void 0:l.getOffsetParent(u));let L=k?k[A]:0;L&&await(null==l.isElement?void 0:l.isElement(k))||(L=a.floating[A]||o.floating[b]);const R=D/2-M/2,N=L/2-S[b]/2-1,O=i(y[_],N),H=i(y[E],N),C=O,W=L-S[b]-H,F=L/2-S[b]/2+R,P=f(C,F,W),z=!c.arrow&&null!=p(r)&&F!=P&&o.reference[b]/2-(F<C?O:H)-S[b]/2<0,B=z?F<C?F-C:F-W:0;return{[w]:v[w]+B,data:{[w]:P,centerOffset:F-P-B,...z&&{alignmentOffset:B}},reset:z}}}),ot(t,e,{placement:r,strategy:l,middleware:c}).then((({x:t,y:e,placement:n,middlewareData:r})=>{var o,i;const l={left:`${t}px`,top:`${e}px`},{x:a,y:c}=null!==(o=r.arrow)&&void 0!==o?o:{x:0,y:0};return{tooltipStyles:l,tooltipArrowStyles:{left:null!=a?`${a}px`:"",top:null!=c?`${c}px`:"",right:"",bottom:"",[null!==(i={top:"bottom",right:"left",bottom:"top",left:"right"}[n.split("-")[0]])&&void 0!==i?i:"bottom"]:"-4px"},place:n}}))):ot(t,e,{placement:"bottom",strategy:l,middleware:c}).then((({x:t,y:e,placement:n})=>({tooltipStyles:{left:`${t}px`,top:`${e}px`},tooltipArrowStyles:{},place:n})));var s};var dt={tooltip:"styles-module_tooltip__mnnfp",fixed:"styles-module_fixed__7ciUi",arrow:"styles-module_arrow__K0L3T",noArrow:"styles-module_noArrow__T8y2L",clickable:"styles-module_clickable__Bv9o7",show:"styles-module_show__2NboJ",dark:"styles-module_dark__xNqje",light:"styles-module_light__Z6W-X",success:"styles-module_success__A2AKt",warning:"styles-module_warning__SCK0X",error:"styles-module_error__JvumD",info:"styles-module_info__BWdHW"};it(".styles-module_tooltip__mnnfp{border-radius:3px;font-size:90%;left:0;opacity:0;padding:8px 16px;pointer-events:none;position:absolute;top:0;transition:opacity .3s ease-out;visibility:hidden;width:max-content;will-change:opacity,visibility}.styles-module_fixed__7ciUi{position:fixed}.styles-module_arrow__K0L3T{background:inherit;height:8px;position:absolute;transform:rotate(45deg);width:8px}.styles-module_noArrow__T8y2L{display:none}.styles-module_clickable__Bv9o7{pointer-events:auto}.styles-module_show__2NboJ{opacity:var(--rt-opacity);visibility:visible}.styles-module_dark__xNqje{background:var(--rt-color-dark);color:var(--rt-color-white)}.styles-module_light__Z6W-X{background-color:var(--rt-color-white);color:var(--rt-color-dark)}.styles-module_success__A2AKt{background-color:var(--rt-color-success);color:var(--rt-color-white)}.styles-module_warning__SCK0X{background-color:var(--rt-color-warning);color:var(--rt-color-white)}.styles-module_error__JvumD{background-color:var(--rt-color-error);color:var(--rt-color-white)}.styles-module_info__BWdHW{background-color:var(--rt-color-info);color:var(--rt-color-white)}");const mt=({id:t,className:e,classNameArrow:n,variant:i="dark",anchorId:l,anchorSelect:a,place:c="top",offset:s=10,events:u=["hover"],openOnClick:f=!1,positionStrategy:d="absolute",middlewares:m,wrapper:p,delayShow:y=0,delayHide:h=0,float:v=!1,hidden:g=!1,noArrow:w=!1,clickable:b=!1,closeOnEsc:x=!1,closeOnScroll:S=!1,closeOnResize:T=!1,style:_,position:E,afterShow:A,afterHide:D,content:M,contentWrapperRef:k,isOpen:L,setIsOpen:R,activeAnchor:N,setActiveAnchor:O})=>{const H=(0,r.useRef)(null),C=(0,r.useRef)(null),W=(0,r.useRef)(null),F=(0,r.useRef)(null),[P,z]=(0,r.useState)(c),[B,$]=(0,r.useState)({}),[j,V]=(0,r.useState)({}),[Y,Z]=(0,r.useState)(!1),[I,J]=(0,r.useState)(!1),q=(0,r.useRef)(!1),U=(0,r.useRef)(null),{anchorRefs:X,setActiveAnchor:K}=st(t),G=(0,r.useRef)(!1),[Q,tt]=(0,r.useState)([]),et=(0,r.useRef)(!1),nt=f||u.includes("click");ut((()=>(et.current=!0,()=>{et.current=!1})),[]),(0,r.useEffect)((()=>{if(!Y){const t=setTimeout((()=>{J(!1)}),150);return()=>{clearTimeout(t)}}return()=>null}),[Y]);const rt=t=>{et.current&&(t&&J(!0),setTimeout((()=>{et.current&&(null==R||R(t),void 0===L&&Z(t))}),10))};(0,r.useEffect)((()=>{if(void 0===L)return()=>null;L&&J(!0);const t=setTimeout((()=>{Z(L)}),10);return()=>{clearTimeout(t)}}),[L]),(0,r.useEffect)((()=>{Y!==q.current&&(q.current=Y,Y?null==A||A():null==D||D())}),[Y]);const ot=(t=h)=>{F.current&&clearTimeout(F.current),F.current=setTimeout((()=>{G.current||rt(!1)}),t)},it=t=>{var e;if(!t)return;const n=null!==(e=t.currentTarget)&&void 0!==e?e:t.target;if(!(null==n?void 0:n.isConnected))return O(null),void K({current:null});y?(W.current&&clearTimeout(W.current),W.current=setTimeout((()=>{rt(!0)}),y)):rt(!0),O(n),K({current:n}),F.current&&clearTimeout(F.current)},at=()=>{b?ot(h||100):h?ot():rt(!1),W.current&&clearTimeout(W.current)},ct=({x:t,y:e})=>{ft({place:c,offset:s,elementReference:{getBoundingClientRect:()=>({x:t,y:e,width:0,height:0,top:e,left:t,right:t,bottom:e})},tooltipReference:H.current,tooltipArrowReference:C.current,strategy:d,middlewares:m}).then((t=>{Object.keys(t.tooltipStyles).length&&$(t.tooltipStyles),Object.keys(t.tooltipArrowStyles).length&&V(t.tooltipArrowStyles),z(t.place)}))},mt=t=>{if(!t)return;const e=t,n={x:e.clientX,y:e.clientY};ct(n),U.current=n},pt=t=>{it(t),h&&ot()},yt=t=>{var e;[document.querySelector(`[id='${l}']`),...Q].some((e=>null==e?void 0:e.contains(t.target)))||(null===(e=H.current)||void 0===e?void 0:e.contains(t.target))||rt(!1)},ht=t=>{"Escape"===t.key&&rt(!1)},vt=lt(it,50,!0),gt=lt(at,50,!0);(0,r.useEffect)((()=>{var t,e;const n=new Set(X);Q.forEach((t=>{n.add({current:t})}));const r=document.querySelector(`[id='${l}']`);r&&n.add({current:r}),S&&window.addEventListener("scroll",gt),T&&window.addEventListener("resize",gt),x&&window.addEventListener("keydown",ht);const o=[];nt?(window.addEventListener("click",yt),o.push({event:"click",listener:pt})):(o.push({event:"mouseenter",listener:vt},{event:"mouseleave",listener:gt},{event:"focus",listener:vt},{event:"blur",listener:gt}),v&&o.push({event:"mousemove",listener:mt}));const i=()=>{G.current=!0},a=()=>{G.current=!1,at()};return b&&!nt&&(null===(t=H.current)||void 0===t||t.addEventListener("mouseenter",i),null===(e=H.current)||void 0===e||e.addEventListener("mouseleave",a)),o.forEach((({event:t,listener:e})=>{n.forEach((n=>{var r;null===(r=n.current)||void 0===r||r.addEventListener(t,e)}))})),()=>{var t,e;S&&window.removeEventListener("scroll",gt),T&&window.removeEventListener("resize",gt),nt&&window.removeEventListener("click",yt),x&&window.removeEventListener("keydown",ht),b&&!nt&&(null===(t=H.current)||void 0===t||t.removeEventListener("mouseenter",i),null===(e=H.current)||void 0===e||e.removeEventListener("mouseleave",a)),o.forEach((({event:t,listener:e})=>{n.forEach((n=>{var r;null===(r=n.current)||void 0===r||r.removeEventListener(t,e)}))}))}}),[I,X,Q,x,u]),(0,r.useEffect)((()=>{let e=null!=a?a:"";!e&&t&&(e=`[data-tooltip-id='${t}']`);const n=new MutationObserver((n=>{const r=[];n.forEach((n=>{if("attributes"===n.type&&"data-tooltip-id"===n.attributeName&&n.target.getAttribute("data-tooltip-id")===t&&r.push(n.target),"childList"===n.type&&(N&&[...n.removedNodes].some((t=>{var e;return!!(null===(e=null==t?void 0:t.contains)||void 0===e?void 0:e.call(t,N))&&(J(!1),rt(!1),O(null),!0)})),e))try{const t=[...n.addedNodes].filter((t=>1===t.nodeType));r.push(...t.filter((t=>t.matches(e)))),r.push(...t.flatMap((t=>[...t.querySelectorAll(e)])))}catch(t){}})),r.length&&tt((t=>[...t,...r]))}));return n.observe(document.body,{childList:!0,subtree:!0,attributes:!0,attributeFilter:["data-tooltip-id"]}),()=>{n.disconnect()}}),[t,a,N]);const wt=()=>{E?ct(E):v?U.current&&ct(U.current):ft({place:c,offset:s,elementReference:N,tooltipReference:H.current,tooltipArrowReference:C.current,strategy:d,middlewares:m}).then((t=>{et.current&&(Object.keys(t.tooltipStyles).length&&$(t.tooltipStyles),Object.keys(t.tooltipArrowStyles).length&&V(t.tooltipArrowStyles),z(t.place))}))};(0,r.useEffect)((()=>{wt()}),[Y,N,M,_,c,s,d,E]),(0,r.useEffect)((()=>{if(!(null==k?void 0:k.current))return()=>null;const t=new ResizeObserver((()=>{wt()}));return t.observe(k.current),()=>{t.disconnect()}}),[M,null==k?void 0:k.current]),(0,r.useEffect)((()=>{var t;const e=document.querySelector(`[id='${l}']`),n=[...Q,e];N&&n.includes(N)||O(null!==(t=Q[0])&&void 0!==t?t:e)}),[l,Q,N]),(0,r.useEffect)((()=>()=>{W.current&&clearTimeout(W.current),F.current&&clearTimeout(F.current)}),[]),(0,r.useEffect)((()=>{let e=a;if(!e&&t&&(e=`[data-tooltip-id='${t}']`),e)try{const t=Array.from(document.querySelectorAll(e));tt(t)}catch(e){tt([])}}),[t,a]);const bt=!g&&M&&Y&&Object.keys(B).length>0;return I?r.createElement(p,{id:t,role:"tooltip",className:o("react-tooltip",dt.tooltip,dt[i],e,`react-tooltip__place-${P}`,{[dt.show]:bt,[dt.fixed]:"fixed"===d,[dt.clickable]:b}),style:{..._,...B},ref:H},M,r.createElement(p,{className:o("react-tooltip-arrow",dt.arrow,n,{[dt.noArrow]:w}),style:j,ref:C})):null},pt=({content:t})=>r.createElement("span",{dangerouslySetInnerHTML:{__html:t}}),yt=({id:t,anchorId:e,anchorSelect:n,content:o,html:i,render:l,className:a,classNameArrow:c,variant:s="dark",place:u="top",offset:f=10,wrapper:d="div",children:m=null,events:p=["hover"],openOnClick:y=!1,positionStrategy:h="absolute",middlewares:v,delayShow:g=0,delayHide:w=0,float:b=!1,hidden:x=!1,noArrow:S=!1,clickable:T=!1,closeOnEsc:_=!1,closeOnScroll:E=!1,closeOnResize:A=!1,style:D,position:M,isOpen:k,setIsOpen:L,afterShow:R,afterHide:N})=>{const[O,H]=(0,r.useState)(o),[C,W]=(0,r.useState)(i),[F,P]=(0,r.useState)(u),[z,B]=(0,r.useState)(s),[$,j]=(0,r.useState)(f),[V,Y]=(0,r.useState)(g),[Z,I]=(0,r.useState)(w),[J,q]=(0,r.useState)(b),[U,X]=(0,r.useState)(x),[K,G]=(0,r.useState)(d),[Q,tt]=(0,r.useState)(p),[et,nt]=(0,r.useState)(h),[rt,ot]=(0,r.useState)(null),{anchorRefs:it,activeAnchor:lt}=st(t),at=t=>null==t?void 0:t.getAttributeNames().reduce(((e,n)=>{var r;return n.startsWith("data-tooltip-")&&(e[n.replace(/^data-tooltip-/,"")]=null!==(r=null==t?void 0:t.getAttribute(n))&&void 0!==r?r:null),e}),{}),ct=t=>{const e={place:t=>{var e;P(null!==(e=t)&&void 0!==e?e:u)},content:t=>{H(null!=t?t:o)},html:t=>{W(null!=t?t:i)},variant:t=>{var e;B(null!==(e=t)&&void 0!==e?e:s)},offset:t=>{j(null===t?f:Number(t))},wrapper:t=>{var e;G(null!==(e=t)&&void 0!==e?e:d)},events:t=>{const e=null==t?void 0:t.split(" ");tt(null!=e?e:p)},"position-strategy":t=>{var e;nt(null!==(e=t)&&void 0!==e?e:h)},"delay-show":t=>{Y(null===t?g:Number(t))},"delay-hide":t=>{I(null===t?w:Number(t))},float:t=>{q(null===t?b:"true"===t)},hidden:t=>{X(null===t?x:"true"===t)}};Object.values(e).forEach((t=>t(null))),Object.entries(t).forEach((([t,n])=>{var r;null===(r=e[t])||void 0===r||r.call(e,n)}))};(0,r.useEffect)((()=>{H(o)}),[o]),(0,r.useEffect)((()=>{W(i)}),[i]),(0,r.useEffect)((()=>{P(u)}),[u]),(0,r.useEffect)((()=>{B(s)}),[s]),(0,r.useEffect)((()=>{j(f)}),[f]),(0,r.useEffect)((()=>{Y(g)}),[g]),(0,r.useEffect)((()=>{I(w)}),[w]),(0,r.useEffect)((()=>{q(b)}),[b]),(0,r.useEffect)((()=>{X(x)}),[x]),(0,r.useEffect)((()=>{nt(h)}),[h]),(0,r.useEffect)((()=>{var r;const o=new Set(it);let i=n;if(!i&&t&&(i=`[data-tooltip-id='${t}']`),i)try{document.querySelectorAll(i).forEach((t=>{o.add({current:t})}))}catch(r){console.warn(`[react-tooltip] "${i}" is not a valid CSS selector`)}const l=document.querySelector(`[id='${e}']`);if(l&&o.add({current:l}),!o.size)return()=>null;const a=null!==(r=null!=rt?rt:l)&&void 0!==r?r:lt.current,c=new MutationObserver((t=>{t.forEach((t=>{var e;if(!a||"attributes"!==t.type||!(null===(e=t.attributeName)||void 0===e?void 0:e.startsWith("data-tooltip-")))return;const n=at(a);ct(n)}))})),s={attributes:!0,childList:!1,subtree:!1};if(a){const t=at(a);ct(t),c.observe(a,s)}return()=>{c.disconnect()}}),[it,lt,rt,e,n]);let ut=m;const ft=(0,r.useRef)(null);if(l){const t=l({content:null!=O?O:null,activeAnchor:rt});ut=t?r.createElement("div",{ref:ft,className:"react-tooltip-content-wrapper"},t):null}else O&&(ut=O);C&&(ut=r.createElement(pt,{content:C}));const dt={id:t,anchorId:e,anchorSelect:n,className:a,classNameArrow:c,content:ut,contentWrapperRef:ft,place:F,variant:z,offset:$,wrapper:K,events:Q,openOnClick:y,positionStrategy:et,middlewares:v,delayShow:V,delayHide:Z,float:J,hidden:U,noArrow:S,clickable:T,closeOnEsc:_,closeOnScroll:E,closeOnResize:A,style:D,position:M,isOpen:k,setIsOpen:L,afterShow:R,afterHide:N,activeAnchor:rt,setActiveAnchor:t=>ot(t)};return r.createElement(mt,{...dt})}}}]);