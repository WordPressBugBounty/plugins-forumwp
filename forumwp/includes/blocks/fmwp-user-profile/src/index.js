(()=>{"use strict";var e={n:t=>{var r=t&&t.__esModule?()=>t.default:()=>t;return e.d(r,{a:r}),r},d:(t,r)=>{for(var o in r)e.o(r,o)&&!e.o(t,o)&&Object.defineProperty(t,o,{enumerable:!0,get:r[o]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.blocks,r=window.wp.serverSideRender;var o=e.n(r);const i=window.wp.blockEditor,p=window.wp.element,a=window.ReactJSXRuntime;(0,t.registerBlockType)("fmwp-block/fmwp-user-profile",{edit:function(e){const t=(0,i.useBlockProps)(),r=(0,p.useMemo)((()=>(0,a.jsx)(o(),{block:"fmwp-block/fmwp-user-profile",attributes:e.attributes})),[e.attributes]);return(0,a.jsx)("div",{...t,children:r})},save:function(e){return null}}),jQuery(window).on("load",(function(){new MutationObserver((e=>{e.forEach((e=>{jQuery(e.addedNodes).find(".fmwp-profile-wrapper").each((function(){const e=jQuery(".fmwp-profile-menu").find("li.fmwp-active-tab > a").data("tab"),t=jQuery(`.fmwp-profile-tab-content[data-tab="${e}"]`).find("li.fmwp-active-tab > a").data("tab"),r=jQuery(".fmwp-profile-wrapper").data("user_id");"topics"===e?fmwp_profile_topics(jQuery(`.fmwp-profile-${e}-content`),{page:1,user_id:r}):"replies"===e?fmwp_profile_replies(jQuery(`.fmwp-profile-${e}-content`),{page:1,user_id:r}):wp.hooks.doAction("fmwp_user_profile_tab_loading",e,t,r)}))}))})).observe(document,{attributes:!1,childList:!0,characterData:!1,subtree:!0})})),wp.hooks.addAction("fmwp_profile_load_finish","fmwp_profile_load_finish_block",(()=>{const e=document.querySelector(".fmwp-profile-wrapper");e&&e.addEventListener("click",(t=>{t.target!==e&&(t.preventDefault(),t.stopPropagation())}))}))})();