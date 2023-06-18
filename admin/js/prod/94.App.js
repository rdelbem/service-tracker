"use strict";(self.webpackChunkservice_tracker=self.webpackChunkservice_tracker||[]).push([[94],{94:(e,t,n)=>{n.r(t),n.d(t,{default:()=>E});var a=n(294),r=n(848),l=n(27),c=n(395),o=n(135),i=n(920),s=n(750),u=n(893),m=n(434);function p(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var a,r,l,c,o=[],i=!0,s=!1;try{if(l=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;i=!1}else for(;!(i=(a=l.call(n)).done)&&(o.push(a.value),o.length!==t);i=!0);}catch(e){s=!0,r=e}finally{try{if(!i&&null!=n.return&&(c=n.return(),Object(c)!==c))return}finally{if(s)throw r}}return o}}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return d(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return d(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function d(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}function f(e){var t=e.id,n=e.id_user,d=e.status,f=e.created_at,v=e.title,w=(0,a.useContext)(l.Z),y=w.deleteCase,b=w.toggleCase,E=w.editCase,g=(0,a.useContext)(c.Z),h=g.updateIdView,C=(0,a.useContext)(o.Z).getStatus,_=p((0,a.useState)(!1),2),k=_[0],N=_[1],x=p((0,a.useState)(""),2),S=x[0],A=x[1],Z={};return"open"===d&&(Z={borderLeft:"4px solid green"}),"close"===d&&(Z={borderLeft:"4px solid blue"}),wp.element.createElement(a.Fragment,null,wp.element.createElement("div",{className:"case-title",style:Z},wp.element.createElement("small",null,(0,i.ZP)(f,"dd/mm/yyyy, HH:MM")),wp.element.createElement("h3",null,wp.element.createElement("span",{className:"the-title",onClick:function(){h(n,t,"progress",g.state.name),C(t,!1,v)}},v),wp.element.createElement(m.FH3,{onClick:function(){return y(t,v)},"data-tooltip-id":"service-tracker","data-tooltip-content":data.tip_delete_case,className:"case-icon"}),"open"===d&&wp.element.createElement(s.XGl,{onClick:function(){return b(t)},"data-tooltip-id":"service-tracker","data-tooltip-content":data.tip_toggle_case_open,className:"case-icon"}),"close"===d&&wp.element.createElement(s.YXN,{onClick:function(){return b(t)},"data-tooltip-id":"service-tracker","data-tooltip-content":data.tip_toggle_case_close,className:"case-icon"}),wp.element.createElement(u.vPQ,{onClick:function(){return N(!k)},"data-tooltip-id":"service-tracker","data-tooltip-content":data.tip_edit_case,className:"case-icon"}))),wp.element.createElement(r.Z,{in:k,timeout:400,classNames:"editing",unmountOnExit:!0},wp.element.createElement("div",{className:"editing-title"},wp.element.createElement("form",null,wp.element.createElement("input",{onChange:function(e){var t=e.target.value;A(t)},className:"edit-input",type:"text"}),wp.element.createElement("button",{onClick:function(e){e.preventDefault(),E(t,n,S)},className:"btn btn-save"},data.btn_save_case),wp.element.createElement("button",{onClick:function(e){e.preventDefault(),N(!k)},className:"btn btn-dismiss"},data.btn_dismiss_edit)))))}var v=n(120),w=n(265);function y(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var a,r,l,c,o=[],i=!0,s=!1;try{if(l=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;i=!1}else for(;!(i=(a=l.call(n)).done)&&(o.push(a.value),o.length!==t);i=!0);}catch(e){s=!0,r=e}finally{try{if(!i&&null!=n.return&&(c=n.return(),Object(c)!==c))return}finally{if(s)throw r}}return o}}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return b(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return b(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function b(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}function E(){var e=(0,a.useContext)(c.Z),t=(0,a.useContext)(l.Z),n=t.state,r=t.postCase,o=t.currentUserInDisplay,i=y((0,a.useState)(""),2),s=i[0],u=i[1];return"cases"!==e.state.view?wp.element.createElement(a.Fragment,null):n.loadingCases?wp.element.createElement(w.Z,null):0===n.cases.length&&!n.loadingCases&&o?wp.element.createElement(a.Fragment,null,wp.element.createElement("form",null,wp.element.createElement("input",{className:"case-input",placeholder:data.case_name,onChange:function(e){var t=e.target.value;u(t)},type:"text",value:s}),wp.element.createElement("button",{onClick:function(e){e.preventDefault(),""!==s&&r(o,s),u("")},className:"add-case"},data.btn_add_case)),wp.element.createElement("div",null,wp.element.createElement("center",null,wp.element.createElement("h3",null,data.no_cases_yet)))):wp.element.createElement(a.Fragment,null,wp.element.createElement("form",null,wp.element.createElement("input",{className:"case-input",placeholder:data.case_name,onChange:function(e){var t=e.target.value;u(t)},type:"text",value:s}),wp.element.createElement("button",{onClick:function(e){e.preventDefault(),""!==s&&r(o,s),u("")},className:"add-case"},data.btn_add_case)),n.cases.map((function(e){return wp.element.createElement(f,e)})),wp.element.createElement(v.u,{id:"service-tracker",place:"left",type:"dark",effect:"solid","data-delay-show":"1000"}))}},265:(e,t,n)=>{n.d(t,{Z:()=>a});n(294);function a(){return wp.element.createElement("div",{className:"spinner-container"},wp.element.createElement("div",{className:"lds-ellipsis"},wp.element.createElement("div",null),wp.element.createElement("div",null),wp.element.createElement("div",null),wp.element.createElement("div",null)))}},27:(e,t,n)=>{n.d(t,{Z:()=>a});const a=(0,n(294).createContext)()},395:(e,t,n)=>{n.d(t,{Z:()=>a});const a=(0,n(294).createContext)()},135:(e,t,n)=>{n.d(t,{Z:()=>a});const a=(0,n(294).createContext)()}}]);