

mce.define('storage',function () {
	"use strict";
	var $ = this.jQuery
	,  config = this.config()
	, tool    = this.toolFn
	, Storage = function () {
		this.v = '1.0';
		this.storage = {};
		this.driver  = config.driver.storage;
		this.prefix  = 'mce_';
		!tool.isUndefined(window[this.driver]) ? this.storage = window[this.driver] 
											   : tool.error("Storage","你的浏览器不支持" + this.driver);
	};

	// 设置存储值
	Storage.prototype.set = function (key,val) {
		 if (arguments.length == 1) {
		 	for (var i in key) {
		 		tool.isObject(key) ? this.storage.setItem(this.prefix + i,key[i]) 
		 						   : tool.error('Storage.set key is not object');
		 	}
		 	return true;
		 }
		 return this.storage.setItem(this.prefix + key,val);
	};
	// 获取存储值
	Storage.prototype.get = function (key) {
		return this.storage.getItem(this.prefix + key) || ''; 
	};
	// 删除存储值
	Storage.prototype.remove = function (key) {
		return this.storage.removeItem(this.prefix + key) ? true : false;
	};
	// 清除全部存储值
	Storage.prototype.clear  = function () {
		return this.storage.clear();	
	};
	// 设置前缀
	Storage.prototype.setPreFix = function (prefix) {
		this.prefix = prefix;
	};
	return new Storage();
});
