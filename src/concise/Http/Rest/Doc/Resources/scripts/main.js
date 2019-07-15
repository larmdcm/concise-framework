"use strict";



mce.create({
	use: ['router','dialog','storage'],
	loaded: function () {
		var router = this.router;
		window.dialog  = this.dialog;
		window.storage = this.storage; 
		new Vue({
			el: '#navbar',
			data: {
				name: apiConfig.name,
				lists: apiConfig.navs
			},
			components: {
				"navbar": components.navbar
			},
			mounted: function () {
				router.bindRouterChange(function (url) {
					$('.navbar-nav').find('.active').removeClass('active') && 
					$('.navbar-nav').find('li').each(function () {
						url == $(this).attr('data-url') && $(this).addClass('active');
					}); 
				}).config({
					defaultExt: ''
				}).dispatch({
					index: {
						el: '#container',
						view: '/doc/index'
					},
					edit: {
						el: '#container',
						view: 'view/edit'
					}
				});
			}
		});
	}
});

