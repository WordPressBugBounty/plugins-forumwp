(()=>{"use strict";var e={n:o=>{var t=o&&o.__esModule?()=>o.default:()=>o;return e.d(t,{a:t}),t},d:(o,t)=>{for(var r in t)e.o(t,r)&&!e.o(o,r)&&Object.defineProperty(o,r,{enumerable:!0,get:t[r]})},o:(e,o)=>Object.prototype.hasOwnProperty.call(e,o)};const o=window.wp.blocks,t=window.wp.components,r=window.wp.serverSideRender;var l=e.n(r);const a=window.wp.blockEditor,n=window.wp.element,p=window.wp.data,s=window.ReactJSXRuntime;(0,o.registerBlockType)("fmwp-block/fmwp-forum",{edit:function(e){const o=(0,a.useBlockProps)(),{attributes:r,setAttributes:i}=e,{show_header:d,order:w,id:u}=r,c=(0,p.useSelect)((e=>{const{getEntityRecords:o}=e("core");return o("postType","fmwp_forum",{per_page:100})||[]}),[]),_=((0,n.useMemo)((()=>{let e="[fmwp_forum";return e+=` show_header="${d?1:0}"`,e+=` order="${w||FMWP().options().get("default_topics_order")}"`,u&&(e+=` id="${u}"`),e+="]",e}),[d,w,u]),(0,n.useCallback)((e=>i({show_header:e})),[i])),f=(0,n.useCallback)((e=>i({order:e})),[i]),m=(0,n.useCallback)((e=>i({id:e})),[i]);return(0,s.jsxs)("div",{...o,children:[(0,s.jsx)(l(),{block:"fmwp-block/fmwp-forum",attributes:r}),(0,s.jsx)(a.InspectorControls,{children:(0,s.jsxs)(t.PanelBody,{title:wp.i18n.__("Forum Settings","forumwp"),children:[(0,s.jsx)(t.ToggleControl,{label:wp.i18n.__("Show Header?","forumwp"),checked:d,onChange:_}),(0,s.jsx)(t.SelectControl,{label:wp.i18n.__("Order","forumwp"),value:w||FMWP().options().get("default_topics_order"),options:[{value:"date_desc",label:wp.i18n.__("Newest to Oldest","forumwp")},{value:"date_asc",label:wp.i18n.__("Oldest to Newest","forumwp")},{value:"update_desc",label:wp.i18n.__("Recently updated","forumwp")},{value:"views_desc",label:wp.i18n.__("Most views","forumwp")},{value:"replies_desc",label:wp.i18n.__("Most replies","forumwp")},{value:"likes_desc",label:wp.i18n.__("Most likes (Likes module)","forumwp")},{value:"votes_desc",label:wp.i18n.__("Most votes (Votes module)","forumwp")}],onChange:f}),(0,s.jsx)(t.SelectControl,{label:wp.i18n.__("Forum ID","forumwp"),value:u,options:[{label:wp.i18n.__("Select a Forum","forumwp"),value:""},...c.map((e=>({label:e.title.rendered,value:e.id})))],onChange:m})]})})]})},save:()=>null}),jQuery(window).on("load",(function(){new MutationObserver((e=>{e.forEach((e=>{jQuery(e.addedNodes).find(".fmwp-topic-main-wrapper").each((function(){jQuery(".fmwp-topic-main-wrapper").each((function(){fmwp_get_replies(jQuery(this).find(".fmwp-topic-wrapper"),{page:1,order:jQuery(this).find(".fmwp-topic-wrapper").data("order")})}))}))}))})).observe(document,{attributes:!1,childList:!0,characterData:!1,subtree:!0})})),wp.hooks.addAction("fmwp_replies_load_finish","fmwp_replies_load_finish_block",(()=>{const e=document.querySelector(".fmwp-topic-main-wrapper");e&&e.addEventListener("click",(o=>{o.target!==e&&(o.preventDefault(),o.stopPropagation())}))}))})();