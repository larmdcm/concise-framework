

mce.define('validator',function (mce) {
	 "use strict";
	 var $ = this.jQuery
	 	, dialog = this.dialog
	 	, Validator  = function () {
	 	     this.v  = "1.0";
	 	     this.el = {}; 
	 	     this.validFn  = {};
	 	     this.validErrorFn = {};
	 	     this.ruleName = 'data-valid';
	 	     this.rules = {
	    	      required: '该字段必须填写',
	    	      between: '该字段值不在指定范围内',
	    	      length: '该字段值长度不在指定范围内',
	    	      max: '该字段值超出范围',
	    	      min: '该字段值小于范围内',
	    	      email: '邮箱格式错误',
	    	      phone: '电话号码格式错误',
	    	      idNumber: '身份证号格式错误',
	         };
	         this.tool = mce.toolFn;
	     };

	 // 自动验证
	 Validator.prototype.valid = function (el) {
	 	 var self = this
	 	   , el   = self.tool.getElement( el || $('form') )
 	 	   , elemet = el.get(0);
	 	 for (var i = 0; i < elemet.length; i++) {
	 	 	  var field  = $(elemet[i])
	 	 	  	 , rule  = field.attr( self.ruleName );
	 	 	  if (!rule) continue;
	 	 	  var rules = rule.split('|')
 	 	  	 for (var v = 0; v < rules.length; v++) {
 	 	  	 	  var value   = field.val().trim()
 	 	  	 	     , params = rules[v].split(' ')
 	 	  	 	     , method = self.__getMethod(params[0])
 	 	  	 	     , args   = [];
 	 	  	 	  args = params.length > 1 ? params[1].indexOf(',') ? params[1].split(',') : params[1].split('') : [];
 	 	  	 	  args.splice(0,0,field) && args.splice(1,0,value);
 	 	  	 	  // 先寻找内部验证方法
 	 	  	 	  if (self.tool.isFunction( self[method] )) {
 	 	  	 	  		if ( !self[method].apply(self,args) ) return this.validError(field,params[0]);
 	 	  	 	  } else if (self.tool.isFunction( self.validFn[method] )) { // 寻找自定义验证方法
 	 	  	 	  		if (!self.validFn[method].apply(self,args)) {
 	 	  	 	  			self.validErrorFn[ params[0] ] && self.tool.isFunction(self.validErrorFn[ params[0] ]) 
 	 	  	 	  			? self.validErrorFn[ params[0] ].apply(self,args)
 	 	  	 	  			: self.validError(field,params[0]);
 	 	  	 	  			return false;
 	 	  	 	  		}
 	 	  	 	  }
 	 	  	 	  continue;
 	 	  	 }
	 	 }
	 	 return true;
	 };
	 // 自定义验证
	 Validator.prototype.registerValid = function (rule,message,callback,errorHndle) {
	 		var errorHndle = errorHndle || null , self = this;
	 		self.rules[rule] && self.tool.error('registerValid','rule is exists!');
	 		self.rules[rule] = message;
	 		self.validFn[ self.__getMethod(rule) ] = callback;
	 		errorHndle != null ? self.validErrorFn[rule] = errorHndle : '';
	 };
	 // 获取方法名称
	 Validator.prototype.__getMethod = function (name) {
	 	 return 'valid' + this.tool.firstToUpper(name);
	 };
	 // 验证错误提示
	 Validator.prototype.validError = function (el,rule) {
	 	 var message = el.attr('data-' + rule) || this.rules[rule];
	 	 dialog.tips(el,message);
 		 el.focus();
 		 return false;
	 };
	 // 验证是否有值
	 Validator.prototype.validRequired = function (el,value) {
	 	  return this.tool.isEmpty(value) ? false : true;
	 };
	 // 验证值是否在范围内
	 Validator.prototype.validBetween = function (el,value,min,max) {
	 	  return value.length >= min && value.length <= max ? true : false;
	 };
	 // 验证长度
	 Validator.prototype.validLength = function (el,value,length) {
	 	 return value.length != length ? false : true;
	 };
	 // 验证最大值
	 Validator.prototype.validMax = function (el,value,max) {
	 	return value.length <= max ? true : false;
	 };
	 // 验证最小值
	 Validator.prototype.validMin = function (el,value,min) {
	 	return value.length >= min ? true : false;
	 };
	 // 验证邮箱
	 Validator.prototype.validEmail = function (el,value) {
	    return /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/
	    .test(value) ? true : false;
	 };
	 // 验证电话号码
	 Validator.prototype.validPhone = function (el,value) {
	 	return /^1[3|4|5|8][0-9]\d{4,8}$/.test(value) ? true : false;
	 };
	 // 验证身份证号码
	 Validator.prototype.validIdNumber = function (el,value) {
	 	 return /^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/.test(value) ? true : false; 
	 };
	 return new Validator;
},['dialog']);