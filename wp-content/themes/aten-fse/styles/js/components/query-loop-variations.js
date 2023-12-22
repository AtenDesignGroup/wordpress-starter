const NEWS_QUERY = 'loop-patterns/news-query';

wp.domReady( function() {
	wp.blocks.registerBlockVariation( 'core/query', {
		name: NEWS_QUERY,
		title: 'News Query',
		description: 'Displays a list of News posts',
		isActive: ( { namespace, query } ) => {
			return (
				namespace === NEWS_QUERY
				&& query.postType === 'news'
			);
		},
		icon: 'list-view',
		attributes: {
			namespace: NEWS_QUERY,
			query: {
				perPage: 12,
				pages: 0,
				offset: 0,
				postType: 'news',
				order: 'desc',
				orderBy: 'date',
				author: '',
				search: '',
				exclude: [],
				sticky: '',
				inherit: true,
				filterByDate: true
			},
		},
		scope: [ 'inserter' ],
		innerBlocks: [
			[
				'core/post-template',
				{},
				[   
					[ 'acf/news-meta-fields' ], 
                    [ 'core/post-title' ],
                ],
			],
			[ 'core/query-pagination' ],
			[ 'core/query-no-results' ],
		],
		}
	)
} );