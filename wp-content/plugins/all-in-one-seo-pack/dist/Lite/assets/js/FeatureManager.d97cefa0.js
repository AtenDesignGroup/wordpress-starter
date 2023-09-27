import{c as y,a as M,p as x,u as T}from"./links.23796d97.js";import{a as U}from"./allowed.2026f6fd.js";import{n as I}from"./isArrayLikeObject.22a096cb.js";import{C as E}from"./index.0eabb636.js";import{C as O}from"./Index.f7bbb33f.js";import{o as c,c as f,a as n,r as d,d as u,w as r,b as _,f as g,g as h,t as l,F as P,h as D,D as V,i as v}from"./vue.runtime.esm-bundler.b39e1078.js";import{_ as k}from"./_plugin-vue_export-helper.b97bdf23.js";import"./default-i18n.3881921e.js";import"./constants.1758f66e.js";import{C as z}from"./Index.4ee78e0e.js";import{a as B}from"./Caret.164d8058.js";import{c as G}from"./news-sitemap.1ec2e03a.js";import{C as R,S as Y,a as q,b as H}from"./SitemapsPro.917c1aa5.js";import{G as K,a as W}from"./Row.5242dafa.js";import{S as j,a as X}from"./ImageSeo.bbde49ae.js";/* empty css                                              *//* empty css                                              */import"./addons.afbe11a7.js";import"./upperFirst.8df5cd57.js";import"./_stringToArray.4de3b1f3.js";import"./toString.0891eb3e.js";import"./params.f0608262.js";/* empty css                                            */import"./Url.1263ccb6.js";import"./Tooltip.6979830f.js";const J={computed:{yourLicenseIsText(){const t=y();let e=this.$t.__("You have not yet added a license key.",this.$td);return t.license.isExpired&&(e=this.$t.__("Your license has expired.",this.$td)),t.license.isDisabled&&(e=this.$t.__("Your license has been disabled.",this.$td)),t.license.isInvalid&&(e=this.$t.__("Your license key is invalid.",this.$td)),e}}},Q={},Z={xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",class:"aioseo-code"},ee=n("path",{d:"M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z",fill:"currentColor"},null,-1),te=[ee];function se(t,e){return c(),f("svg",Z,te)}const ie=k(Q,[["render",se]]);const oe={setup(){return{addonsStore:M(),licenseStore:y(),pluginsStore:x(),rootStore:T()}},components:{CoreAlert:E,CoreFeatureCard:R,CoreModal:z,Cta:O,GridColumn:K,GridRow:W,SvgClose:B,SvgCode:ie,SvgImageSeo:j,SvgLinkAssistant:Y,SvgLocalBusiness:X,SvgRedirect:q,SvgSitemapsPro:H},mixins:[J],data(){return{allowed:U,ctaImg:G,showNetworkModal:!1,maybeActivate:!1,maybeDeactivate:!1,search:null,loading:{activateAll:!1,deactivateAll:!1},strings:{videoNewsSitemaps:this.$t.__("Video and News Sitemaps",this.$td),imageSeoOptimization:this.$t.__("Image SEO Optimization",this.$td),localBusinessSeo:this.$t.__("Local Business SEO",this.$td),advancedWooCommerce:this.$t.__("Advanced WooCommerce",this.$td),customTaxonomies:this.$t.__("SEO for Categories, Tags and Custom Taxonomies",this.$td),andMore:this.$t.__("And many more...",this.$td),activateAllFeatures:this.$t.__("Activate All Features",this.$td),deactivateAllFeatures:this.$t.__("Deactivate All Features",this.$td),searchForFeatures:this.$t.__("Search for Features...",this.$td),ctaHeaderText:this.$t.sprintf(this.$t.__("Upgrade %1$s to Pro and Unlock all Features!",this.$td),"AIOSEO"),ctaButtonText:this.$t.__("Upgrade to Pro and Unlock All Features",this.$td),aValidLicenseIsRequired:this.$t.__("A valid license key is required in order to use our addons.",this.$td),enterLicenseKey:this.$t.__("Enter License Key",this.$td),purchaseLicense:this.$t.__("Purchase License",this.$td),areYouSureNetworkChange:this.$t.__("This is a network-wide change.",this.$td),yesProcessNetworkChange:this.$t.__("Yes, process this network change",this.$td),noChangedMind:this.$t.__("No, I changed my mind",this.$td)},descriptions:{aioseoImageSeo:{description:"<p>"+this.$t.__("Globally control the Title attribute and Alt text for images in your content. These attributes are essential for both accessibility and SEO.",this.$td)+"</p>",version:0},aioseoVideoSitemap:{description:"<p>"+this.$t.__("The Video Sitemap works in much the same way as the XML Sitemap module, it generates an XML Sitemap specifically for video content on your site. Search engines use this information to display rich snippet information in search results.",this.$td)+"</p>",version:0},aioseoNewsSitemap:{description:"<p>"+this.$t.__("Our Google News Sitemap lets you control which content you submit to Google News and only contains articles that were published in the last 48 hours. In order to submit a News Sitemap to Google, you must have added your site to Google’s Publisher Center and had it approved.",this.$td)+"</p>",version:0},aioseoLocalBusiness:{description:"<p>"+this.$t.__("Local Business schema markup enables you to tell Google about your business, including your business name, address and phone number, opening hours and price range. This information may be displayed as a Knowledge Graph card or business carousel.",this.$td)+"</p>",version:0}}}},computed:{upgradeToday(){return this.$t.sprintf(this.$t.__("%1$s %2$s comes with many additional features to help take your site's SEO to the next level!",this.$td),"AIOSEO","Pro")},getAddons(){return this.addonsStore.addons.filter(t=>!this.search||t.name.toLowerCase().includes(this.search.toLowerCase()))},networkChangeMessage(){return this.activated?this.$t.__("Are you sure you want to deactivate these addons across the network?",this.$td):this.$t.__("Are you sure you want to activate these addons across the network?",this.$td)}},methods:{getAssetUrl:I,closeNetworkModal(t=!1){if(this.showNetworkModal=!1,t){const e=this.maybeActivate?"actuallyActivateAllFeatures":"actuallyDeactivateAllFeatures";this.maybeActivate=!1,this.maybeDeactivate=!1,this[e]()}},getIconComponent(t){return t.startsWith("svg-")?t:"img"},getIconSrc(t,e){return typeof t=="string"&&t.startsWith("svg-")?null:typeof t=="string"?`data:image/svg+xml;base64,${t}`:e},getAddonDescription(t){const e=t.sku.replace(/-./g,m=>m.toUpperCase()[1]);return this.descriptions[e]&&this.descriptions[e].description&&t.descriptionVersion<=this.descriptions[e].version?this.descriptions[e].description:t.description},activateAllFeatures(){if(!this.$isPro||!this.licenseStore.license.isActive)return window.open(this.$links.utmUrl(this.rootStore.aioseo.data.isNetworkAdmin?"network-activate-all-features":"activate-all-features"));if(this.rootStore.aioseo.data.isNetworkAdmin){this.showNetworkModal=!0,this.maybeActivate=!0;return}this.actuallyActivateAllFeatures()},actuallyActivateAllFeatures(){this.loading.activateAll=!0;const t=this.addonsStore.addons.filter(e=>!e.requiresUpgrade).map(e=>({plugin:e.basename}));this.pluginsStore.installPlugins(t).then(e=>{const m=Object.keys(e.body.completed).map(o=>e.body.completed[o]);this.$refs.addons.forEach(o=>{m.includes(o.feature.basename)&&(o.activated=!0)}),this.loading.activateAll=!1})},deactivateAllFeatures(){if(this.rootStore.aioseo.data.isNetworkAdmin){this.showNetworkModal=!0,this.maybeDeactivate=!0;return}this.actuallyDeactivateAllFeatures()},actuallyDeactivateAllFeatures(){this.loading.deactivateAll=!0;const t=this.addonsStore.addons.filter(e=>!e.requiresUpgrade).filter(e=>e.installed).map(e=>({plugin:e.basename}));this.pluginsStore.deactivatePlugins(t).then(e=>{const m=Object.keys(e.body.completed).map(o=>e.body.completed[o]);this.$refs.addons.forEach(o=>{m.includes(o.feature.basename)&&(o.activated=!1)}),this.loading.deactivateAll=!1})}}},ae={class:"aioseo-feature-manager"},re={class:"aioseo-feature-manager-header"},ne={key:0,class:"buttons"},le={class:"button-content"},ce={class:"search"},de={class:"aioseo-feature-manager-addons"},ue={class:"buttons"},he=["innerHTML"],me={class:"large"},pe=["src"],_e={class:"aioseo-modal-body"},ge={class:"reset-description"};function fe(t,e,m,o,s,a){const p=d("base-button"),S=d("base-input"),w=d("core-alert"),A=d("core-feature-card"),b=d("grid-column"),$=d("grid-row"),C=d("cta"),L=d("svg-close"),N=d("core-modal");return c(),f("div",ae,[n("div",re,[a.getAddons.filter(i=>i.canActivate===!0).length>0?(c(),f("div",ne,[n("div",le,[u(p,{size:"medium",type:"blue",loading:s.loading.activateAll,onClick:a.activateAllFeatures},{default:r(()=>[h(l(s.strings.activateAllFeatures),1)]),_:1},8,["loading","onClick"]),o.licenseStore.isUnlicensed?g("",!0):(c(),_(p,{key:0,size:"medium",type:"gray",loading:s.loading.deactivateAll,onClick:a.deactivateAllFeatures},{default:r(()=>[h(l(s.strings.deactivateAllFeatures),1)]),_:1},8,["loading","onClick"]))])])):g("",!0),n("div",ce,[u(S,{modelValue:s.search,"onUpdate:modelValue":e[0]||(e[0]=i=>s.search=i),size:"medium",placeholder:s.strings.searchForFeatures,"prepend-icon":"search"},null,8,["modelValue","placeholder"])])]),n("div",de,[t.$isPro&&o.licenseStore.isUnlicensed?(c(),_(w,{key:0,type:"red"},{default:r(()=>[n("strong",null,l(t.yourLicenseIsText),1),h(" "+l(s.strings.aValidLicenseIsRequired)+" ",1),n("div",ue,[u(p,{type:"blue",size:"small",tag:"a",href:o.rootStore.aioseo.data.isNetworkAdmin?o.rootStore.aioseo.urls.aio.networkSettings:o.rootStore.aioseo.urls.aio.settings},{default:r(()=>[h(l(s.strings.enterLicenseKey),1)]),_:1},8,["href"]),u(p,{type:"green",size:"small",tag:"a",target:"_blank",href:t.$links.getUpsellUrl("feature-manager-upgrade","no-license-key","pricing")},{default:r(()=>[h(l(s.strings.purchaseLicense),1)]),_:1},8,["href"])])]),_:1})):g("",!0),u($,null,{default:r(()=>[(c(!0),f(P,null,D(a.getAddons,(i,F)=>(c(),_(b,{key:F,sm:"6",lg:"4"},{default:r(()=>[u(A,{ref_for:!0,ref:"addons","can-activate":i.canActivate,"can-manage":s.allowed(i.capability),feature:i},{title:r(()=>[(c(),_(V(a.getIconComponent(i.icon)),{src:a.getIconSrc(i.icon,i.image)},null,8,["src"])),h(" "+l(i.name),1)]),description:r(()=>[n("div",{innerHTML:a.getAddonDescription(i)},null,8,he)]),_:2},1032,["can-activate","can-manage","feature"])]),_:2},1024))),128))]),_:1})]),o.licenseStore.isUnlicensed?(c(),_(C,{key:0,class:"feature-manager-upsell",type:2,"button-text":s.strings.ctaButtonText,floating:!1,"cta-link":t.$links.utmUrl("feature-manager","main-cta"),"learn-more-link":t.$links.getUpsellUrl("feature-manager","main-cta","home"),"feature-list":t.$constants.UPSELL_FEATURE_LIST},{"header-text":r(()=>[n("span",me,l(s.strings.ctaHeaderText),1)]),description:r(()=>[h(l(a.upgradeToday),1)]),"featured-image":r(()=>[n("img",{alt:"Purchase AIOSEO Today!",src:a.getAssetUrl(s.ctaImg)},null,8,pe)]),_:1},8,["button-text","cta-link","learn-more-link","feature-list"])):g("",!0),s.showNetworkModal?(c(),_(N,{key:1,"no-header":"",onClose:e[5]||(e[5]=i=>a.closeNetworkModal(!1))},{body:r(()=>[n("div",_e,[n("button",{class:"close",onClick:e[2]||(e[2]=v(i=>a.closeNetworkModal(!1),["stop"]))},[u(L,{onClick:e[1]||(e[1]=v(i=>a.closeNetworkModal(!1),["stop"]))})]),n("h3",null,l(s.strings.areYouSureNetworkChange),1),n("div",ge,l(a.networkChangeMessage),1),u(p,{type:"blue",size:"medium",onClick:e[3]||(e[3]=i=>a.closeNetworkModal(!0))},{default:r(()=>[h(l(s.strings.yesProcessNetworkChange),1)]),_:1}),u(p,{type:"gray",size:"medium",onClick:e[4]||(e[4]=i=>a.closeNetworkModal(!1))},{default:r(()=>[h(l(s.strings.noChangedMind),1)]),_:1})])]),_:1})):g("",!0)])}const Re=k(oe,[["render",fe]]);export{Re as default};
