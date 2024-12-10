(()=>{"use strict";var e={n:t=>{var r=t&&t.__esModule?()=>t.default:()=>t;return e.d(r,{a:r}),r},d:(t,r)=>{for(var o in r)e.o(r,o)&&!e.o(t,o)&&Object.defineProperty(t,o,{enumerable:!0,get:r[o]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.blocks,r=window.wp.components,o=window.wp.serverSideRender;var n=e.n(o);const i=window.wp.blockEditor,s=window.wp.element,l=window.ReactJSXRuntime;(0,t.registerBlockType)("fmwp-block/fmwp-registration-form",{edit:function(e){const t=(0,i.useBlockProps)(),{attributes:o,setAttributes:a}=e,{first_name:c,last_name:w,redirect:d=""}=o,m=((0,s.useMemo)((()=>{let e="[fmwp_registration_form";return e+=` first_name="${c?1:0}"`,e+=` last_name="${w?1:0}"`,d&&(e+=` redirect="${d}"`),e+="]",e}),[c,w,d]),(0,s.useCallback)((e=>a({first_name:e})),[a])),p=(0,s.useCallback)((e=>a({last_name:e})),[a]),u=(0,s.useCallback)((e=>a({redirect:e})),[a]);return(0,l.jsxs)("div",{...t,children:[(0,l.jsx)(n(),{block:"fmwp-block/fmwp-registration-form",attributes:o}),(0,l.jsx)(i.InspectorControls,{children:(0,l.jsxs)(r.PanelBody,{title:wp.i18n.__("Registation form","forumwp"),children:[(0,l.jsx)(r.ToggleControl,{label:wp.i18n.__("Hide first name?","forumwp"),checked:c,onChange:m}),(0,l.jsx)(r.ToggleControl,{label:wp.i18n.__("Hide last name?","forumwp"),checked:w,onChange:p}),(0,l.jsx)(r.TextControl,{label:wp.i18n.__("Redirect","forumwp"),value:d,onChange:u})]})})]})},save:()=>null})})();