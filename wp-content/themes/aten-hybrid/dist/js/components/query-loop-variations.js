(()=>{let s="loop-patterns/news-query";wp.domReady(function(){wp.blocks.registerBlockVariation("core/query",{name:s,title:"News Query",description:"Displays a list of News posts",isActive:({namespace:e,query:t})=>e===s&&"news"===t.postType,icon:"list-view",attributes:{namespace:s,query:{perPage:12,pages:0,offset:0,postType:"news",order:"desc",orderBy:"date",author:"",search:"",exclude:[],sticky:"",inherit:!0,filterByDate:!0}},scope:["inserter"],innerBlocks:[["core/post-template",{},[["core/post-title"]]],["core/query-pagination"],["core/query-no-results"]]})})})();