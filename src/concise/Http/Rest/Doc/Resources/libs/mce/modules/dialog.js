
mce.define('dialog',function (base) {
	"use strict"
	var $ = this.jQuery
	   , tool   = this.toolFn
	   , layer  = window.layer
	   , Dialog = function () {
	   		this.v = '1.0';
	   }
	   , tool = base.toolFn;

	   // PC端基础弹出层
	   Dialog.prototype.__baseAlert = function (options) {
	   		 var self = this;
	   		 var options   = options || {}
	   		    , content  = options.content || '' 
	   		    , title    = options.title || ''
	   		    , callback = options.callback || null
	   		    , icon     = tool.isUndefined(options.icon) ? 1 : options.icon
	   		    , index    = layer.alert(content,{
	   		 	title: title,
	   		 	icon: icon,
	   		 	yes: function () {
	   		 		callback != null && callback.call(self);
	   		 		layer.close(index);
	   		 	}
	   		 });
	   		 return this;
	   };
	    // 移动端基础弹出层
	   Dialog.prototype.__baseMobileAlert = function (options) {
	   		var options = options || {}
	   		   , data   = {}
	   		   , callback   = options.callback || null;
	   		data['content'] = options.content || '';
	   		options.btn ? data['btn'] = options.btn : '';
	   		!tool.isUndefined(options.shadeClose) ? data['shadeClose'] = options.shadeClose : '';
	   		options.skin ? data['skin'] = options.skin : '';
	   		options.style ? data['style'] = options.style : '';
	   		options.type ? data['type'] = options.type : '';
	   		options.anim ? data['anim'] = options.anim : '';
	   		data.yes = function () {
	   			layer.close(index);
	   			callback && callback.call(this);
	   		};
	   		var index = layer.open(data);
	   		return this;
	   };
	   // 成功提示方法
	   Dialog.prototype.success = function (content,title,callback) {
	   		return this.__baseAlert({
	   			title: title,
	   			content: content,
	   			callback: callback
	   		});
	   };
	   // 失败提示方法
	   Dialog.prototype.error = function (content,title,callback) {
	   		return this.__baseAlert({
	   			title: title,
	   			content: content,
	   			callback: callback,
	   			icon: 2
	   		});
	   };
	   // 警告提示方法
	   Dialog.prototype.notice = function (content,title,callback) {
	   		return this.__baseAlert({
	   			title: title,
	   			content: content,
	   			callback: callback,
	   			icon: 0
	   		});
	   };
	   // 询问提示
	   Dialog.prototype.confirm = function (content,title,callback,options) {
	   		var self = this;
	   		var  content = content || ''
	   			 , options  = options || {} 
	   			 , title    = title   || ''
	   			 , callback = callback || null
	   			 , btn = options.btn || ['是','否']
	   			 , closeCallBack = options.callback || null; 
	   		if (base.isMobile) {
	   			return this.__baseMobileAlert({
	   				content: content,
	   				callback: tool.isFunction(title) ? title : callback,
	   				btn: btn,
	   				skin: 'footer'
	   			});
	   		} 
	   		var index = layer.confirm(content,{
	   			 		btn: btn,
	   			 		icon: 3
	   			 },function () {
	   			 	 callback != null && callback.call(self);
	   		 		 layer.close(index);
	   			 },function () {
	   			 	 closeCallBack != null && closeCallBack.call(self);
	   		 		 layer.close(index);	
	   			 });
	   		return this;
	   };

	   // 小tips
	   Dialog.prototype.tips = function (el,content,options) {
	   		if (base.isMobile) {
	   			var options = options || {}
	   			  , time    = options.time ? options.time / 1000 : 2;
	   			return layer.open({
	   				content: content,
	   				skin: 'msg',
	   				time: time
	   			});
	   		}
	   		var options = options || {}
	   		   , el = tool.isString(el) ? 
	   				 $(el) : tool.getElement(el)
	   		   , content = content || ''
	   		   , color = options.color || '#000'
	   		   , time  = options.time  || 2000
	   		   , position = options.position || 1;
	   		return layer.tips(content,el,{
	   			 tips: [1,color],
	   			 time: time,
	   			 tips: position
	   		});
	   };
	   // 底部弹出层(自己实现)
	   Dialog.prototype.bottomBox = function (el) {
	   		var el = tool.isString(el) ? el.substr(0,1) == '<' ? el : $(el).html()
	   								   : tool.getElement(el).html()
	   		   	, shade = $('<div class="mo-ui-shade" style="z-index: 99998;position: fixed;'+
	   					'top:0;left:0;width:100%;height:100%;background-color: rgba(0, 0, 0,.7);"></div>')
	   		   	, closeBox = function (html) {
		   			box.animate({
		   				bottom: '-180px'
		   			});
		   			$('.mo-ui-shade').remove();
		   			setTimeout(function () {
		   				box.is(":animated") && box.remove();
		   			},300);
		   			tool.isObject(el) && el.html(html);
	   		   	}, html = tool.isObject(el) ? el.html() ? el.html() : '' : el ? el : '';
	   		$('body').append(shade);
	   		$('body').append('<div class="mo-ui-bottom-box" style="width:100%;height:180px;position: fixed;'+
	   						 'bottom:-180px;right:0;background:#fff;z-index:99999;">'+ 
	   			html +
	   		'</div>');
	   		var box = $('.mo-ui-bottom-box');
	   		tool.isObject(el) && el.html('');
	   		box.animate({
				bottom: 0
			});
	   		$('.mo-ui-shade').on('click',function () {
	   			closeBox(html);
	   		});
	   		$('.mo-ui-box-close').on('click',function () {
	   			closeBox(html);
	   		});
	   		return this;
	   };
	   // 移动端信息框
	   Dialog.prototype.message = function (content,callback,btnName,shadeClose) {
	   		var shadeClose = tool.isUndefined(shadeClose) ? true : shadeClose;
	   		return this.__baseMobileAlert({
	   			content: content,
	   			btn: btnName ? btnName : '确定',
	   			shadeClose: shadeClose,
	   			callback: callback
	   		});
	   };
	   // 移动端提示框
	   Dialog.prototype.prompt = function (content,callback,btn) {
	   		return this.__baseMobileAlert({
	   			content: content,
	   			btn: btn ? btn : ['确定','取消'],
	   			callback: callback
	   		});
	   };
	   // 提示层
	   Dialog.prototype.loading = function (content,type) {
	   		if (base.isPc) {
	   			var typeTo = content || 1;
	   			return layer.load(parseInt(typeTo));
	   		}
	   		var options = {}
	   		  , type    = type || 2;
	   		content ? options['content'] = content : '';
	   		options['type'] = type;
	   		options['shadeClose'] = false;
	   		return layer.open(options);
	   };
	   // 底部弹出层(居于layer)
	   Dialog.prototype.bottomLayer = function (content) {
	   		var content = tool.isString(content) ? content.substr(0,1) == '<' ? content : $(content).html()
	   										: tool.getElement(content).html();
	   		return this.__baseMobileAlert({
	   			type: 1,
	   			content: content,
	   			anim: 'up',
	   			style: 'position:fixed; bottom:0; left:0; width: 100%; height: 200px; padding:10px 0; border:none;'
	   		});
	   };
	   // 关闭弹出层
	   Dialog.prototype.close = function (index) {
	   		var index = tool.isUndefined(index) ? false : index;
	   		index === false ? layer.closeAll() : layer.close(index);
	   		return this; 
	   };
	   // 返回layer对象
	   Dialog.prototype.get = function () {
	   	  return layer;
	   };
	   return new Dialog();
},[],['layer']);