/**
 * Author: Mdcm
 * Data: 2017.12.16
 * Remark: To write JS code More Succinctly and Efficiently
 */

;(function (window,document) {
	 "use strict"; 
	  var VERSION   = '1.0' , config = {
	  	  // 默认脚本后缀
	  	  defaultScriptExt: '.js',
	  	  // 默认样式后缀
	  	  defaultCssExt: '.css',
	  	  // 驱动组
	  	  driver: {
	  	  	 storage: 'localStorage',
	  	  }
	  }
	  // Mce内置模块
	  , modules  = {
	  	 // 简单模板引擎
	  	 tpl: 'modules/tpl',
	  	 // web存储
	  	 storage: 'modules/storage',
	  	 // 弹出层
	  	 dialog: 'modules/dialog',
	  	 // 文件上传
	  	 upload: 'modules/upload',
	  	 // 表单验证
	  	 validator: 'modules/validator',
	  	 // 路由调度
	  	 router: 'modules/router'
	  }
	  // 第三方插件库
	  // path: 加载路径
	  // themes: 样式
	  // deps: 所依赖其他插件
	  // as: 别名
	  // mobile: 移动端使用
	  , plugins  = {
	  	 jQuery: {
	  	 	path: 'plugins/jQuery/jQuery'
	  	 },
	  	 md5: {
	  	 	path: 'plugins/encrypt/md5'
	  	 },
	  	 layer: {
	  	 	path: 'plugins/layer/layer',
	  	 	themes: 'plugins/layer/theme/default/layer',
	  	 	deps: ['jQuery'],
	  	 	mobile: {
	  	 		path: 'plugins/layer/mobile/layer',
	  	 		themes: 'plugins/layer/mobile/need/layer',
	  	 	}
	  	 },
	  	 Handlebars: {
	  	 	path: 'plugins/tpl/handlebars',
	  	 	as: 'handlebars'
	  	 }
	  }
	  // 缓存已加载列表
	  , cache = {
	  	 plugins: {},
	  	 themes: {}
	  }
	  // 是否为undefined
	  , isUndefined = function (ufe) {
	  	  return typeof ufe == 'undefined' || ufe === undefined;
	  }
	  // 是否为数组
	  , isArray  = function (array) {
	  	 return array  instanceof Array;
	  }
	  // 是否为字符串
	  , isString = function (string) {
	  	 return typeof string == 'string';
	  }
	  // 是否为函数
	  , isFunction = function (fn) {
		 return (
	  		  !!fn && !fn.nodename 
	  		  && fn.constructor != String 
	  		  && fn.constructor != RegExp 
	  		  && fn.constructor != Array 
	  		  && /function/i.test(fn + "")
	  	);
	  }
	  // 是否为对象
	  , isObject = function (object) {
	  	  return object != null && typeof object == 'object';
	  }
	  // 是否为空
	  , isEmpty = function (instance) {
		  return isString(instance) ? instance.replace(/(^s*)|(s*$)/g, "").length == 0 
		  		 : isUndefined(instance) || instance == null;
	  }
	  // 是否为整数
	  , isInteger = function (integer) {
	  	  return /^[0-9]*[1-9][0-9]*$/.test(integer);
	  }
	  // 是否为null
	  , isNull  = function (val) {
	  	 return val === null;
	  }
	  // 是否为布尔值
	  , isBoolean = function (bool) {
	  	 return typeof bool == 'boolean';
	  }
	  // 是否在pc端
	  , isPc = function () {
  	     var userAgentInfo = navigator.userAgent
	       , Agents = ["Android", "iPhone",
	                "SymbianOS", "Windows Phone",
	                "iPad", "iPod"]
	       , is = true;
	     for (var v = 0; v < Agents.length; v++) {
	        if (userAgentInfo.indexOf(Agents[v]) > 0) {
	            is = false;
	            break;
	        }
	     }
	     return is;
	  }
	  // 是否在微信端
	  , isWeChat = function () {
	  	  var ua = navigator.userAgent.toLowerCase();
          return ua.match(/MicroMessenger/i) == "micromessenger";
	  }
	  , getBasePath = function () {
	  	     var js       = doc.scripts
	  	     	, jsPath  = js[js.length - 1].src;
         	return jsPath.substring(0, jsPath.lastIndexOf('/') + 1);
	  }
	  , getFileName  = function (path,prefix) {
	  	  var path   = path.split('/')
	  	  	, prefix = isUndefined(prefix) ? true : prefix;
	  	  return prefix ? path[path.length - 1] : path[path.length - 1].split('.')[0];
	  }
	  , getSrc = function (path) {
	  	    var src = path.replace('http://','');
            src = src.split('/');
            src.shift();
            src = src.join('/');
            return src.substr(0,1) == '/' ? src : '/' + src; 
	  }
	  // 获取文件后缀
	  , getPrefix = function (path) {
	  	 var file = path.split('.');
	     if (file.length <= 1) return '';
	     return file[file.length - 1];
	  }
	  , getElement = function (el) {
	  	  return el instanceof jQuery ? el : jQuery(el);
	  }
	  , getGuid = function () {
	  	 var S4 = function () {
           return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
         };
         return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
	  }
	  , error = function (method,message) {
	  	 var error = "Mce: " + "[" + method +"] to " + message;
	  	 throw new Error(error);
	  }
	  , addEvent = function (el,type,handle) {
		  	try {  // Chrome、FireFox、Opera、Safari、IE9.0及其以上版本
	        	el.addEventListener(type,handle,false);
		    } catch(e) {
		        try {  // IE8.0及其以下版本
		            el.attachEvent('on' + type,handle);
		        } catch (e) {  // 早期浏览器
		            el['on' + type] = handle;
		        }
		    }
	  }
	  // 首字母转大写
	  , firstToUpper = function (str) {
		var first = '';
        str   = str.split('');
        first = str.shift().toLocaleUpperCase();
        return first + str.join('');
	  }
	   // 首字母小写
	  , firstToLower = function (str) {
	  	var first = '';
        str   = str.split('');
        first = str.shift().toLocaleLowerCase();
        return first + str.join('');
	  }
	    //浏览器检测
	  , getBrowser = function () {	  	
	 		var browser = {}
			  , ua  = navigator.userAgent.toLowerCase()
			  , s;		
			(s = ua.match(/msie ([\d.]+)/)) ? browser.ie = s[1] :
			(s = ua.match(/firefox\/([\d.]+)/)) ? browser.firefox = s[1] :
			(s = ua.match(/chrome\/([\d.]+)/)) ? browser.chrome = s[1] : 
			(s = ua.match(/opera\/.*version\/([\d.]+)/)) ? browser.opera = s[1] : 
			(s = ua.match(/version\/([\d.]+).*safari/)) ? browser.safari = s[1] : 0;
			
			if (/webkit/.test(ua)) browser.webkit = ua.match(/webkit\/([\d.]+)/)[1];
			return browser;
	  }
	  , doc = document
	  , win = window
	  , Mce = function () {
	  	(function () {
	  		return /^http(s*):\/\//.test(location.href) || error("server","Please deploy the project to the server to run!");
	  	})();
		this.v = VERSION;
		this.basePath = getBasePath();
		this.rootPath = getSrc(getBasePath());
		this.autoloadJQuery = true;
		// 提供方便处理数组和对象的方法
		this.struct = {
			is: function (instance) {
				return isArray(instance) || isObject(instance);
			},
			in: function (instance,instances) {
				for (var i in instances) {
					if (instances[i] == instance) {
						return true;
					}
				}
				return false;
			},
			inNo: function (instance,instances) {
				for (var i in instances) {
					if (instances[i] !== instances) {
						return false;
					}
				}
				return true;
			},
			has: function (instance,key) {
				return arguments.length == 1 ? !isEmpty(instance) : !isEmpty(instance[key]);
			},
			merger: function (instance,newInstance) {
				for (var i in newInstance) {
					isArray(instance) ? instance.push(newInstance[i]) : instance[i] = newInstance[i];
				}
				return instance;
			},
			insert: function (instance,newInstance,index) {
				var index = isArray(instance) ? index || 0 : index || 'newKey';
				isArray(instance) ? instance.splice(index,0,newInstance) : instance[index] = newInstance;
				return instance;
			},
			remove: function (instance,key) {
				if (isArray(instance) && (isInteger(key) || key == 0)) {
					return instance.splice(key,1);
				}
				for (var i in instance) {
					if (isArray(instance)) {
						instance[i] == key && instance.splice(i,1);
					} else {
						if (key == i) {
						   delete instance[key];
						} else {
							instance[i] == key ? delete instance[i] : '';
						}
					}
				}
				return instance;
			},
			max: function (instance) {
				var maxVal = this.first(instance);
				for (var i in instance) {
					if (instance[i] > maxVal) {
						maxVal = instance[i];
					}
				}
				return maxVal;
			},
			min: function (instance) {
				var minVal = this.first(instance);
				for (var i in instance) {
					if (instance[i] < minVal) {
						minVal = instance[i];
					}
				}
				return minVal;
			},
			first: function (instance) {
				for (var i in instance) {
					return instance[i];
				}
			},
			last: function (instance) {
				var index = 0;
				for (var i in instance) {
					index++;
					if (index == instance.length) {
						return instance[i];
					}
				}
			},
			each: function (instance,callback) {
				isArray(instance) || isObject(instance) 
								  || error('each(instance,callback)','instance is neither an object not an array!');
		        var result;
		        for (var i in instance) {
		        	 result = callback.call(instance[i],i,instance[i]);
		             if (result === false) break; 
		        }
		        return isUndefined(result) ? true : result;
			},
			clear: function (instance) {
				instance.length = 0;
				return instance;
			}
		};
		// 工具函数组
		this.toolFn = {
			isString: isString,
			isArray: isArray,
			isFunction: isFunction,
			isObject: isObject,
			isUndefined: isUndefined,
			isNull: isNull,
			isInteger: isInteger,
			isEmpty: isEmpty,
			isBoolean: isBoolean,
			error: error,
			getElement: getElement,
			getPrefix: getPrefix,
			firstToUpper: firstToUpper,
			firstToLower: firstToLower,
			addEvent: addEvent,
		};
		// 浏览器检测
		this.browser = getBrowser();
		// 是否在pc端
		this.isPc = isPc(); 
		// 是否在移动端
		this.isMobile = (!isPc());
		// 是否在微信端
		this.isWeChat = isWeChat();
		// 这边为了方便pc,mobile端测试做窗口大小改变监听
		var self = this
		  , checkTerminal = function () {
			this.isPc = isPc(); 
			this.isMobile = (!isPc());
		};
		this.jQuery ? jQuery(win).on('resize',function () {
			checkTerminal.call(self);
		}) : addEvent(win,'resize',function () {
			checkTerminal.call(self);
		});
		var deps  = ['jQuery'];
		// 如果jQuery已经引入,则jQuery不需要继续加载
		if (win['jQuery']) {
			this.struct.remove(deps,'jQuery');
			this.jQuery = win['jQuery'];
			this.autoloadJQuery = false;
		}
		// 预加载第三方插件
		this.plugin(deps);
	};
	// 创建
	Mce.prototype.create = function (options) {
		var self    = this
		  , options = options || {}
		  , cfg     = options.cfg || {}
		  , use     = options.use || []
		  , fns     = options.fns || []
		  , plugins = options.plugins || []
		  , modules = options.modules || []
		  , init    = options.init || null
		  , loaded  = options.loaded || null
		  , immed   = isUndefined(immed) ? false : immed; // 是否等文档加载完毕后执行
		return self.bindGlobalFunction(fns).init(init,cfg).modules(modules).plugin(plugins,function () {
			  self.use(use,function () {
			 	   loaded && isFunction(loaded) && (
			 	   	  immed ? self['jQuery'] ? self['jQuery'](function () {
			 	   	  	loaded.call(self,self)
			 	   	  }) : addEvent(win,'load',function () {
			 	   	  	loaded.call(self,self)
			 	   	  }) : loaded.call(self,self)
			 	   ); 	 
			  })
		});
	}
	// 初始化操作
	Mce.prototype.init   = function (fn,config) {
		 var self = this
		   , cfg  = config || []
		   , fn   = fn || null;
		 self.config(cfg);
		 fn && isFunction(fn) && fn.call(self,self);
		 return self
	};
	// 绑定全局使用函数
	Mce.prototype.bindGlobalFunction = function (name,fn) {
		if (arguments.length == 1) {
			for (var i in name) {
				!isUndefined(name[i]) && isFunction(name[i]) ? win[i] = name[i].bind(this) : '';
			}
		} else
			win[name] = fn.bind(this);
		return this;
	};
	// 配置处理
	Mce.prototype.config = function (options) {
		if (arguments.length == 0) return config;
		if (isObject(options)) {
			for (var key in options) {
				if (isObject(options[key])) {
					if (key == 'plugins') {
						for (var i in options[key]) {
							plugins[key][i] = options[key][i];
						}
					} else if (key == 'modules') {
						for (var i in options[key]) {
							modules[key][i] = options[key][i];
						}
					} else {
						for (var i in options[key]) {
							config[key][i] = options[key][i];
						}
					}
				} else
					config[key] = options[key];
			}
		} else
			return options.indexOf('.') >= 0 ? config[ options.split('.')[0] ][ options.split('.')[1] ]
											 : config[options];
	}
	// 定义模块
	Mce.prototype.define = function (module,callback,depenModule,depenPlugin) {
		var self = this
		   , callback    = callback    || null
		   , depenModule = depenModule || []
		   , depenPlugin = depenPlugin || [];
		if (isNull(depenModule) && isNull(depenPlugin)) {
			if (!self[module]) {
				var result = callback.call(self,self);
				isObject(result) ? self[module] = result : '';
			}
			return self;
		}
		this.plugin(depenPlugin,function () {
			this.use(depenModule,function () {
				if (!self[module]) {
					var result = callback.call(self,self);
					isObject(result) ? self[module] = result : '';
				}
			});	
		});
		return self;
	};
	// 加载模块
	Mce.prototype.use = function (module,callback) {
		var self      = this
		   , callback = callback || null
		   , items    = []
		   , callback = callback || null
		   , total    = 0;
		isArray(module) ? items = module : items.push(module);
		
		for (var i in items) {
			 var item = items[i]
			    , isLoadScript = self.isLoadScripts(item);
			 if ( modules[item] && !win[item] && !self[item] && isLoadScript) {
			 	 var src = self.rootPath + modules[item] + config['defaultScriptExt'];
			 	 self.loadFile(src);
			 }
		}
		function isLoad (apps) {
			// 依赖于jQuery 所以jQuery是必须加载的
			if (!self['jQuery']) return false;
			return self.struct.each(apps,function (index,item) {
				 if (!self[item]) {
				 	return false;
				 }
			}) ? true : false;
		}
		;(function poll(apps,callback,mce) {
			total++
			if (total >= 1000) {
				total = 0;
				return error("use",'[' + apps + ']' + " in module is load error!"); 
			}
			!isLoad(apps) ? setTimeout(function () {
				return poll(apps,callback,mce);
			},3) : callback && callback.call(mce,mce);
		}(items,callback,self));
		return self;
	};
	// 加载第三方插件
	Mce.prototype.plugin = function (plugin,callback,fullPath) {
		var self      = this
		   , callback = callback || null
		   , items    = []
		   , fullPath = isUndefined(fullPath) ? false : fullPath
		   , callback = callback || null
		   , total    = 0;
		isArray(plugin) ? items = plugin : items.push(plugin);
		for (var i = 0; i < items.length; i++) {
			 var item = items[i]
			 	, isLoadScript   = self.isLoadScripts(item)
			 	, isThisLoad     = plugins[item]['as'] ? self[plugins[item]['as']] : self[item]
			 	, pluginItemPath = plugins[item]['mobile'] && self.isMobile ? plugins[item]['mobile']['path'] 
			 																 : plugins[item]['path'];
			 if ( plugins[item] && pluginItemPath && !win[item] 
			 	 				&& !isThisLoad && !cache['plugins'][item] 
			 	 				&& isLoadScript
			 	) {
			 	 var src  = fullPath ? item : self.rootPath + pluginItemPath + config['defaultScriptExt']
			 	   , load = function (src,item) {
			 	   		 var pluginItemThemes = plugins[item]['mobile'] && self.isMobile 
			 	   		 												? plugins[item]['mobile']['themes'] 
			 	   		 												: plugins[item]['themes'];
					 	 self.loadFile(src,function (src) {
					 	     var  itemSrc   = !isUndefined(this.src) && !(this === window) ? this.src : src
					 	     	, itemName  = getFileName(itemSrc,false)
					 	     	, pluginCfg = plugins[itemName];
					 	     	cache['plugins'][itemName] = plugins[itemName]['path'];
						 	 	win[itemName] ? pluginCfg['as'] ? self[pluginCfg['as']] = win[itemName] 
						 	 				  : self[itemName] = win[itemName] : '';
					 	 });
					 	if (pluginItemThemes) {
				 	 		if (!cache['themes'][item]) {
				 	 			var themes = [];
				 	 			isArray(pluginItemThemes) ? themes = pluginItemThemes 
				 	 											 : themes.push(pluginItemThemes);
				 	 		    for (var i in themes) {
				 	 		    	var src = self.rootPath + themes[i] + config['defaultCssExt']; 
				 	 		    	self.loadFile(src);
				 	 		    }
				 	 		}
						 }
			 	   };
			 	   isEmpty(plugins[item]['deps']) ? load(src,item) : self.plugin(plugins[item]['deps'],function () {
			 	   		load(src,item);
			 	   });
			 } 
		}
		function isLoad (apps) {
			return self.struct.each(apps,function (index,item) {
				 if (!win[item] && !self[item] && !cache['plugins'][item]) {
				 	return false;
				 }
			}) ? true : false;
		}
		;(function poll(apps,callback,mce) {
			total++;
			if (total >= 1000) {
				return error('plugin','[' + apps + ']' + ' in plugin is load error!');
			}
			!isLoad(apps) ? setTimeout(function () {
				return poll(apps,callback,mce);
			},3) : callback && callback.call(mce,mce);
		}(items,callback,self));
		return self;
	};
	// 更新模块
	Mce.prototype.modules  = function (options) {
		if (arguments.length == 0) return modules;
        if ( isObject(options) ) {
            for (var i in options) {
                modules[i] = options[i];
            }
        }
        return this;
	};
	// 更新注册插件
	Mce.prototype.registerPlugin = function (options) {
		if ( isObject(options) ) {
			for (var i in options) {
				plugins[i] = options[i];
			}
		}
		return this;
	};
	// 获取已注册插件
	Mce.prototype.getPlugins = function () {
		return plugins;
	};
	// 加载文件
	Mce.prototype.loadFile = function (src,callback) {
		var appendDom = doc.getElementsByTagName('head')[0] 
					 || doc.getElementsByTagName('body')[0]
		  , srcs     = []
		  , callback = callback || null
		  isArray(src) ? srcs = src : srcs.push(src);
		  for (var i in srcs) {
		  	  var type = getPrefix(srcs[i]);
		  	  !this.jQuery ? srcs[i] += '?_' + (new Date()).getTime() : '';
		  	  switch (type) {
			  	 case 'js':
		  	  	 	var file   = doc.createElement('script');
		  	  	 	file.src   = srcs[i];
		  	  	 	file.async = true;
		  	  	 	break;
		  	  	 case 'css':
		  	  	 	file = doc.createElement('link');
		            file.href = srcs[i];
		            file.type = "text/css";
		            file.rel  = "stylesheet";
		  	  	 	break;
		  	  	 case 'less':
		  	  	 	file = doc.createElement('link');
		            file.href = srcs[i];
		            file.type = "text/css";
		            file.rel  = "stylesheet";
		  	  	 	break;
		  	  	 default:
		  	  	 	continue;
		  	  	 	break;
		  	  }
		  	 file.onload = file.onreadystatechange = function () {
		  	  	if (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete') {
		  	  		!isNull(callback) && callback.call(this,this);
		  	  	}
			 }
			 this.jQuery ? this.jQuery(appendDom).append( this.jQuery(file) ) && isFunction(callback) 
			 																  && callback.call(window,srcs[i]) 
			 			 : appendDom.appendChild(file);
		  } 
	};
	// 检测脚本是否已经加载
	Mce.prototype.isLoadScripts = function (script) {
		var self = this;
	    return (function (script) {
	 		return self.struct.each(doc.scripts,function (index,item) {
	 			if (item.src) {
	 				var src = item.src;
	 				if (getFileName(src,false) == script) {
	 					return false;
	 				}
	 			}
	 		});
	 	})(script)
	};
	win.mce = new Mce;
})(window,document);

