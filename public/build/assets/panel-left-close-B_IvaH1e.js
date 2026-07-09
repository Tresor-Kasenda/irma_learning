import{Y as y,Z as l,s as b}from"./app-C95G01Xu.js";/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const h=t=>t==="";/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const A=(...t)=>t.filter((e,r,o)=>!!e&&e.trim()!==""&&o.indexOf(e)===r).join(" ").trim();/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const k=t=>t.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const L=t=>t.replace(/^([A-Z])|[\s-_]+(\w)/g,(e,r,o)=>o?o.toUpperCase():r.toLowerCase());/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const S=t=>{const e=L(t);return e.charAt(0).toUpperCase()+e.slice(1)};/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var s={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":2,"stroke-linecap":"round","stroke-linejoin":"round"};/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const W=Symbol("lucide-icons");function P(){return y(W,{})}/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const _=({name:t,iconNode:e,absoluteStrokeWidth:r,"absolute-stroke-width":o,strokeWidth:c,"stroke-width":w,size:i,color:f,...p},{slots:d})=>{const{size:n,color:C,strokeWidth:m=2,absoluteStrokeWidth:g=!1,class:x=""}=P(),v=b(()=>{const a=h(r)||h(o)||r===!0||o===!0||g===!0,u=c||w||m||s["stroke-width"];return a?Number(u)*24/Number(i??n??s.width):u});return l("svg",{...s,...p,width:i??n??s.width,height:i??n??s.height,stroke:f??C??s.stroke,"stroke-width":v.value,class:A("lucide",x,...t?[`lucide-${k(S(t))}-icon`,`lucide-${k(t)}`]:["lucide-icon"])},[...e.map(a=>l(...a)),...d.default?[d.default()]:[]])};/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const $=(t,e)=>(r,{slots:o,attrs:c})=>l(_,{...c,...r,iconNode:e,name:t},o.default?{default:o.default}:void 0);/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const j=[["rect",{width:"18",height:"18",x:"3",y:"3",rx:"2",key:"afitv7"}],["path",{d:"M9 3v18",key:"fh3hqa"}],["path",{d:"m16 15-3-3 3-3",key:"14y99z"}]],I=$("panel-left-close",j);export{I as P,$ as c};
