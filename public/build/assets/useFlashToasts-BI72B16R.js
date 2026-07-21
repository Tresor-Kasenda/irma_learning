import{d as x,e as m,k as y,Y as w,w as b,o as a,F as _,J as T,n as z,c as p,a as u,u as n,t as C,g as M,p as $,f as B,j as N,i as v,x as E}from"./app-5tx2LE_f.js";import{c}from"./createLucideIcon-CtL1gPI5.js";/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const F=[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]],L=c("circle-check",F);/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const A=[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"M12 16v-4",key:"1dtifu"}],["path",{d:"M12 8h.01",key:"e9boi3"}]],I=c("info",A);/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const j=[["path",{d:"m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3",key:"wmoenq"}],["path",{d:"M12 9v4",key:"juzpu7"}],["path",{d:"M12 17h.01",key:"p32p05"}]],V=c("triangle-alert",j);/**
 * @license @lucide/vue v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const q=[["path",{d:"M18 6 6 18",key:"1bl5f8"}],["path",{d:"m6 6 12 12",key:"d8bk6v"}]],D=c("x",q),G={class:"pointer-events-none fixed right-4 top-20 z-[80] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3 sm:right-6 sm:w-full","aria-live":"polite"},J={class:"min-w-0 flex-1 text-sm font-medium leading-5"},P=["aria-label","onClick"],X=x({__name:"AdminToasts",props:{toasts:{}},emits:["dismiss"],setup(r){const d={success:"border-emerald-400/30 bg-emerald-50 text-emerald-950 dark:bg-[#0d2926] dark:text-emerald-100",error:"border-rose-400/30 bg-rose-50 text-rose-950 dark:bg-[#321725] dark:text-rose-100",info:"border-sky-400/30 bg-sky-50 text-sky-950 dark:bg-[#10283b] dark:text-sky-100"};return(l,o)=>(a(),m("div",G,[y(w,{"enter-active-class":"transition duration-200 ease-out","enter-from-class":"translate-x-4 opacity-0","leave-active-class":"transition duration-150 ease-in","leave-to-class":"translate-x-4 opacity-0"},{default:b(()=>[(a(!0),m(_,null,T(r.toasts,s=>(a(),m("div",{key:s.id,class:z([d[s.type],"pointer-events-auto flex items-start gap-3 border px-4 py-3 shadow-2xl shadow-slate-950/15"]),role:"status"},[s.type==="success"?(a(),p(n(L),{key:0,class:"mt-0.5 size-5 shrink-0 text-emerald-500","stroke-width":1.8})):s.type==="error"?(a(),p(n(V),{key:1,class:"mt-0.5 size-5 shrink-0 text-rose-500","stroke-width":1.8})):(a(),p(n(I),{key:2,class:"mt-0.5 size-5 shrink-0 text-sky-500","stroke-width":1.8})),u("p",J,C(s.message),1),u("button",{"aria-label":`Fermer la notification : ${s.message}`,class:"grid size-6 shrink-0 place-items-center opacity-60 transition hover:bg-black/5 hover:opacity-100 dark:hover:bg-white/10",type:"button",onClick:k=>l.$emit("dismiss",s.id)},[y(n(D),{class:"size-4"})],8,P)],2))),128))]),_:1})]))}});function Y(r="app:toast"){const d=E(),l=$(()=>d.props.flash??{}),o=v([]),s=new Map;let k=1;function h(e){const t=s.get(e);t&&(window.clearTimeout(t),s.delete(e)),o.value=o.value.filter(g=>g.id!==e)}function i(e){const t=k++;o.value.push({...e,id:t}),s.set(t,window.setTimeout(()=>h(t),5e3))}function f(e){i(e.detail)}return M(l,e=>{e!=null&&e.success?i({type:"success",message:e.success}):e!=null&&e.error?i({type:"error",message:e.error}):e!=null&&e.info&&i({type:"info",message:e.info})},{immediate:!0,deep:!0}),B(()=>window.addEventListener(r,f)),N(()=>{window.removeEventListener(r,f),s.forEach(e=>window.clearTimeout(e)),s.clear()}),{dismissToast:h,toasts:o}}export{L as C,V as T,D as X,X as _,Y as u};
