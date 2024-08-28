var rml_gutenberg;(()=>{"use strict";var e={};(e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})})(e);const t=ReactJSXRuntime,s=(React,wp),i="real-media-library/gallery",{registerBlockType:r}=s.blocks,{G:n,SVG:o,Path:l,ServerSideRender:a,PanelBody:d,RangeControl:p,ToggleControl:h,SelectControl:u,TreeSelect:c,Notice:g,Spinner:m,Button:b,withNotices:y}=s.components,{Component:x,Fragment:C}=s.element,{InspectorControls:j,ServerSideRender:v}=s.editor,{__:f}=s.i18n,S=a||v,k=[{value:"attachment",label:f("Attachment Page")},{value:"media",label:f("Media File")},{value:"none",label:f("None")}];class T extends x{constructor(){super(...arguments),this.state={$busy:!0,tree:[]}}async componentDidMount(){const{tree:e}=await window.rml.request({location:{path:"/tree"}});e.unshift({id:-1,name:rmlOpts.others.lang.unorganized}),e.unshift({id:void 0,name:"-"}),this.setState({tree:e,$busy:!1})}render(){const{...e}=this.props,{$busy:s,tree:i}=this.state;return s?(0,t.jsx)(m,{}):(0,t.jsx)(c,{label:rmlOpts.others.lang.folder,...e,tree:i})}}r(i,{title:"Real Media Library Gallery",description:"Display folder images in a rich gallery.",icon:(0,t.jsxs)(o,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg",children:[(0,t.jsx)(l,{fill:"none",d:"M0 0h24v24H0V0z"}),(0,t.jsxs)(n,{children:[(0,t.jsx)(l,{d:"M20 4v12H8V4h12m0-2H8L6 4v12l2 2h12l2-2V4l-2-2z"}),(0,t.jsx)(l,{d:"M12 12l1 2 3-3 3 4H9z"}),(0,t.jsx)(l,{d:"M2 6v14l2 2h14v-2H4V6H2z"})]})]}),category:"common",supports:{align:!0},attributes:{fid:{type:"number",default:0},columns:{type:"number",default:3},imageCrop:{type:"boolean",default:!0},captions:{type:"boolean",default:!0},linkTo:{type:"string",default:"none"},lastEditReload:{type:"number",default:0}},edit:y(class extends x{constructor(){super(...arguments),this.setFid=e=>this.props.setAttributes({fid:+e}),this.setLinkTo=e=>this.props.setAttributes({linkTo:e}),this.setColumnsNumber=e=>this.props.setAttributes({columns:e}),this.toggleImageCrop=()=>this.props.setAttributes({imageCrop:!this.props.attributes.imageCrop}),this.toggleCaptions=()=>this.props.setAttributes({captions:!this.props.attributes.captions}),this.handleReload=()=>this.props.setAttributes({lastEditReload:(new Date).getTime()}),this.render=()=>{const{attributes:e}=this.props,{fid:s,columns:r=3,imageCrop:n,captions:o,linkTo:l}=e;return(0,t.jsxs)(C,{children:[(0,t.jsx)(j,{children:(0,t.jsxs)(d,{title:f("Gallery Settings"),children:[(0,t.jsx)(T,{value:s,onChange:this.setFid}),(0,t.jsx)(p,{label:f("Columns"),value:r,onChange:this.setColumnsNumber,min:"1",max:"8"}),(0,t.jsx)(h,{label:f("Crop Images"),checked:!!n,onChange:this.toggleImageCrop}),(0,t.jsx)(h,{label:f("Caption"),checked:!!o,onChange:this.toggleCaptions}),(0,t.jsx)(u,{label:f("Link To"),value:l,onChange:this.setLinkTo,options:k}),(0,t.jsx)(b,{isPrimary:!0,onClick:this.handleReload,children:rmlOpts.others.lang.reloadContent})]})}),(0,t.jsx)(S,{block:i,attributes:e}),!s&&(0,t.jsx)(g,{status:"error",isDismissible:!1,children:(0,t.jsx)("p",{children:rmlOpts.others.lang.gutenBergBlockSelect})})]})},this.state={refresh:(new Date).getTime()}}}),save:()=>null}),rml_gutenberg=e})();
//# sourceMappingURL=https://sourcemap.devowl.io/real-media-library/4.22.22/4265f79674b5b51c24e2ea0728178c30/rml_gutenberg.lite.js.map
