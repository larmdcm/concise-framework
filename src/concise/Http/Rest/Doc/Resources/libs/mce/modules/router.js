
mce.define('router',function (base) {
	var $ = this.jQuery
	   , Router = function () {
	   	 	this.v       = '1.0';
	   	 	this.params  = {};
	   	 	this.hashMap = {};
	   	 	this.defaultPath  = '/index';
	   	 	this.routerChange = []; 
	   }
	   , tool   = this.toolFn
	   , cache  = {}
	   , config = {
	   	  defaultExt: '.html',
	   	  basePath: ''
	   };

	// 初始化操作
	Router.prototype.init = function (isCache) {
		var initEvent = ['load','hashchange'];
		for (var i in initEvent) {
			$(window).on(initEvent[i],this.refersh.bind(this));
		}
		base.autoloadJQuery === true && this.refersh.call(this);

		return this;
	};
	// 注册路由
	Router.prototype.map = function (path,fn) {
		 var path = arguments.length == 1 ? path || [] : path || self.defaultPath
		   , self = this;

		 if (arguments.length == 1) 
		 	base.struct.merger(self.hashMap,path);
		 else
		    tool.isFunction(fn) ? self.hashMap[path] = fn : tool.error('Router.map()','fn is not function!')
		 return self;
	};
	// 页面切换
	Router.prototype.refersh  = function () {
		var self   = this
		   , param = self.getParamsUrl();

		self.params = param.params;
		base.struct.each(self.hashMap,function (key,item) {
			var itemPath = key.substr(0,1) == '/' ? key : '/' + key;
			if (itemPath != param.path) return;
			self.__execRouterChange(key);
			if (tool.isFunction(item)) {
				return item.call(self,param.params); 
			}
			if (!tool.isUndefined(item['fn']) && tool.isFunction(item['fn'])) {
				return item['fn'].call(self,param.params);
			}
			if (!tool.isUndefined(item['view'])) {
				var basePath = config['basePath'].substr(0,-1) != '/' && config['basePath'].split(' ').length > 1 
							   ? '/' + config['basePath']
							   : config['basePath']
				  , path = tool.isBoolean(item['view']) ? basePath + key : basePath + item['view']
				  , view = tool.getPrefix(path) == '' ? path + config['defaultExt'] : path
				  , layouts = item.layouts || []
				  , el = tool.isString(item.el) ? $(item.el) : tool.getElement(item.el);
				  cache[itemPath] ? el.get(0) && el.html(cache[itemPath]) : self.request(view,function (response) {
				  		cache[itemPath] = response;
				  		el.get(0) && el.html(response);
				  });
			}
		});
	};
	// 路由调度
	Router.prototype.dispatch = function (path,isCache) {
		 var path    = path || null
		   , isCache = tool.isUndefined(isCache) ? false : true;
		 path && this.map(path);
		 return this.init(isCache);
	};
	// 绑定路由改变监听事件
	Router.prototype.bindRouterChange = function (fn) {
		this.routerChange.push(fn);
		return this;
	};
	// 执行路由监听事件
	Router.prototype.__execRouterChange = function (url) {
		var self = this 
		  , fns  = self.routerChange;
		base.struct.each(fns,function (index,item) {
			tool.isFunction(item) && item.call(self,url);
		});
	};
	// 获取路由参数
	Router.prototype.getParamsUrl = function () {
		var hashUrl = location.hash.split('#')[1]
		  , path    = !tool.isUndefined(hashUrl) ? hashUrl.split('?')[0] : this.defaultPath
		  , params  = this.__parseParams(!tool.isUndefined(hashUrl) ? hashUrl.split('?').length > 1 
		  								 ? hashUrl.split('?')[1] : '' : '');
		return {path: path,params: params};
	};
	// 解析路由参数
	Router.prototype.__parseParams = function (param) {
		 if (tool.isEmpty(param)) return [];
		 var params = param.split('&')
		   , paramsContainer = [];
		 for (var i in params) {
		 	 var item  = params[i]
		 	   , items = item.split('=');
		 	 items.length > 1 ? paramsContainer[items[0]] = decodeURI(items[1]) : '';
		 }
		 return paramsContainer;
	};
	// get请求
	Router.prototype.request = function (url,callback) {
		 var self = this;
		 $.get(url,{_: (new Date()).getTime()}, function (response) {
		 	 callback && callback.call(self,response);
		 });
	};
	// 配置
	Router.prototype.config = function (options) {
		if (arguments.length == 0) return config;
		base.struct.merger(config,options);
		return this;
	};
	// 获取路由参数
	Router.prototype.get = function (key) {
		return !tool.isEmpty(this.params[key]) ? this.params[key] : '';
	};
	return new Router;
});

