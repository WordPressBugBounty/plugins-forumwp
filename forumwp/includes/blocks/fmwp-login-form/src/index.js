(()=>{"use strict";var e={n:o=>{var r=o&&o.__esModule?()=>o.default:()=>o;return e.d(r,{a:r}),r},d:(o,r)=>{for(var t in r)e.o(r,t)&&!e.o(o,t)&&Object.defineProperty(o,t,{enumerable:!0,get:r[t]})},o:(e,o)=>Object.prototype.hasOwnProperty.call(e,o)};const o=window.wp.blocks,r=window.wp.components,t=window.wp.serverSideRender;var n=e.n(t);const i=window.wp.blockEditor,l=window.wp.element,s=window.ReactJSXRuntime;(0,o.registerBlockType)("fmwp-block/fmwp-login-form",{edit:function(e){const o=(0,i.useBlockProps)(),{attributes:t,setAttributes:w}=e,{redirect:c=""}=t,d=((0,l.useMemo)((()=>{let e="[fmwp_login_form";return c&&(e+=` redirect="${c}"`),e+="]",e}),[c]),(0,l.useCallback)((e=>w({redirect:e})),[w]));return(0,s.jsxs)("div",{...o,children:[(0,s.jsx)(n(),{block:"fmwp-block/fmwp-login-form",attributes:t}),(0,s.jsx)(i.InspectorControls,{children:(0,s.jsx)(r.PanelBody,{title:wp.i18n.__("Login form","forumwp"),children:(0,s.jsx)(r.TextControl,{label:wp.i18n.__("Redirect","forumwp"),value:c,onChange:d})})})]})},save:()=>null})})();