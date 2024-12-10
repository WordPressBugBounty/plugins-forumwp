(()=>{"use strict";var e={n:r=>{var o=r&&r.__esModule?()=>r.default:()=>r;return e.d(o,{a:o}),o},d:(r,o)=>{for(var t in o)e.o(o,t)&&!e.o(r,t)&&Object.defineProperty(r,t,{enumerable:!0,get:o[t]})},o:(e,r)=>Object.prototype.hasOwnProperty.call(e,r)};const r=window.wp.blocks,o=window.wp.components,t=window.wp.serverSideRender;var a=e.n(t);const n=window.wp.blockEditor,s=window.wp.element,l=window.wp.data,i=window.ReactJSXRuntime;(0,r.registerBlockType)("fmwp-block/fmwp-forums-list",{edit:function(e){const r=(0,n.useBlockProps)(),{attributes:t,setAttributes:u}=e,{search:c,with_sub:w,category:p,order:d}=t,f=(0,l.useSelect)((e=>{const{getEntityRecords:r}=e("core");return r("taxonomy","fmwp_forum_category")||[]}),[]),m=((0,s.useMemo)((()=>{let e="[fmwp_forums";return e+=` search="${c?1:0}"`,e+=` with_sub="${w?1:0}"`,p&&(e+=` category="${p}"`),e+=` order="${d||FMWP().options().get("default_forums_order")}"`,e+="]",e}),[c,w,p,d]),(0,s.useCallback)((e=>u({search:e})),[u])),_=(0,s.useCallback)((e=>u({with_sub:e})),[u]),b=(0,s.useCallback)((e=>u({category:e})),[u]),h=(0,s.useCallback)((e=>u({order:e})),[u]);return(0,i.jsxs)("div",{...r,children:[(0,i.jsx)(a(),{block:"fmwp-block/fmwp-forums-list",attributes:t}),(0,i.jsx)(n.InspectorControls,{children:(0,i.jsxs)(o.PanelBody,{title:wp.i18n.__("Forum Filter Settings","forumwp"),children:[(0,i.jsx)(o.ToggleControl,{label:wp.i18n.__("Enable Search?","forumwp"),checked:c,onChange:m}),(0,i.jsx)(o.ToggleControl,{label:wp.i18n.__("Include Subcategories?","forumwp"),checked:w,onChange:_}),(0,i.jsx)(o.SelectControl,{label:wp.i18n.__("Category","forumwp"),value:p,options:[{label:wp.i18n.__("Select a Category","forumwp"),value:""},...f.map((e=>({label:e.name,value:e.id})))],onChange:b}),(0,i.jsx)(o.SelectControl,{label:wp.i18n.__("Order","forumwp"),value:d||FMWP().options().get("default_forums_order"),options:[{value:"date_desc",label:wp.i18n.__("Newest to Oldest","forumwp")},{value:"date_asc",label:wp.i18n.__("Oldest to Newest","forumwp")},{value:"order_desc",label:wp.i18n.__("Most Priority","forumwp")},{value:"order_asc",label:wp.i18n.__("Lower Priority","forumwp")}],onChange:h})]})})]})},save:()=>null}),jQuery(window).on("load",(function(){new MutationObserver((e=>{e.forEach((e=>{jQuery(e.addedNodes).find(".fmwp-archive-forums-wrapper").each((function(){jQuery(".fmwp-archive-forums-wrapper").each((function(){var e=jQuery(this).data("order");fmwp_get_forums(jQuery(this).find(".fmwp-forums-wrapper"),{page:1,order:e})}))}))}))})).observe(document,{attributes:!1,childList:!0,characterData:!1,subtree:!0})})),wp.hooks.addAction("fmwp_forums_load_finish","fmwp_forums_load_finish_block",(()=>{const e=document.querySelector(".fmwp-archive-forums-wrapper");e&&e.addEventListener("click",(r=>{r.target!==e&&(r.preventDefault(),r.stopPropagation())}))}))})();