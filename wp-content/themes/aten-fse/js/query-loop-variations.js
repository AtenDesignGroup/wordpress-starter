(()=>{var r="loop-patterns/news-query";wp.domReady(function(){wp.blocks.registerBlockVariation("core/query",{name:r,title:"News Query",description:"Displays a list of News posts",isActive:function(e){var t=e.namespace,e=e.query;return t===r&&"news"===e.postType},icon:"list-view",attributes:{namespace:r,query:{perPage:12,pages:0,offset:0,postType:"news",order:"desc",orderBy:"date",author:"",search:"",exclude:[],sticky:"",inherit:!0,filterByDate:!0}},scope:["inserter"],innerBlocks:[["core/post-template",{},[["acf/news-meta-fields"],["core/post-title"]]],["core/query-pagination"],["core/query-no-results"]]})})})();