"use strict";(self.webpackChunkservice_tracker=self.webpackChunkservice_tracker||[]).push([[280],{420:(e,r,t)=>{t.d(r,{Z:()=>n});var a=t(444);function n(e,r){switch(r.type){case a.fo:return{users:r.payload.users,loadingUsers:r.payload.loadingUsers};case a.gv:return{user:r.payload.user,cases:r.payload.cases,loadingCases:r.payload.loadingCases};case a.Mp:return{view:r.payload.view,userId:r.payload.userId,caseId:r.payload.caseId,name:r.payload.name};case a.fw:return{status:r.payload.status,caseTitle:r.payload.caseTitle,loadingStatus:r.payload.loadingStatus};default:return e}}},280:(e,r,t)=>{t.r(r),t.d(r,{default:()=>i});var a=t(294),n=t(420),u=t(395),s=t(444);function o(e,r){return function(e){if(Array.isArray(e))return e}(e)||function(e,r){var t=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=t){var a,n,u,s,o=[],l=!0,i=!1;try{if(u=(t=t.call(e)).next,0===r){if(Object(t)!==t)return;l=!1}else for(;!(l=(a=u.call(t)).done)&&(o.push(a.value),o.length!==r);l=!0);}catch(e){i=!0,n=e}finally{try{if(!l&&null!=t.return&&(s=t.return(),Object(s)!==s))return}finally{if(i)throw n}}return o}}(e,r)||function(e,r){if(!e)return;if("string"==typeof e)return l(e,r);var t=Object.prototype.toString.call(e).slice(8,-1);"Object"===t&&e.constructor&&(t=e.constructor.name);if("Map"===t||"Set"===t)return Array.from(e);if("Arguments"===t||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t))return l(e,r)}(e,r)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function l(e,r){(null==r||r>e.length)&&(r=e.length);for(var t=0,a=new Array(r);t<r;t++)a[t]=e[t];return a}function i(e){var r=o((0,a.useReducer)(n.Z,{view:"",userId:"",caseId:"",name:""}),2),t=r[0],l=r[1],i=function(e,r,t,a){l({type:s.Mp,payload:{view:t,userId:e,caseId:r,name:a}})};return(0,a.useEffect)((function(){i(t.userId,t.caseId,"init",t.name)}),[]),wp.element.createElement(u.Z.Provider,{value:{state:t,updateIdView:i}},e.children)}},395:(e,r,t)=>{t.d(r,{Z:()=>a});const a=(0,t(294).createContext)()},444:(e,r,t)=>{t.d(r,{Mp:()=>u,fo:()=>a,fw:()=>s,gv:()=>n});var a="GET_USERS",n="GET_CASES",u="IN_VIEW",s="GET_STATUS"}}]);