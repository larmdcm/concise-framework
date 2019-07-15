"use strict";
// 缓存类
class Cache
{
  constructor() 
  {
     this.data = {};
  }
  get ()
  {
     return this.data;
  }
  set (key,val)
  {
     if (key.includes('.')) {
        let keys = key.split('.');
        keys.length > 1 ? this.data[keys[0]] 
        ? this.data[keys[0]][keys[1]] = val : this.__setData(keys,val)
        : this.data[keys[0]] = val;
     } else {
        this.data[key] = val;
     }
      return this;
     
  }
  __setData(keys,val)
  {
    this.data[keys[0]] = {};
    this.data[keys[0]][keys[1]] = val;
  }
  read (key)
  {
      if (key.includes('.')) {
          let keys = key.split('.');
          return keys.length > 1 
                ? this.exists(this.data[keys[0]][keys[1]]) ? this.data[keys[0]][keys[1]] : ''
                : this.exists(this.data[keys[0]]) ? this.data[keys[0]] : '';
      }
      return this.exists(this.data[key]) ? this.data[key] : '';
  }
  clear()
  {
      this.data = {};
  }
  count()
  {
      return this.data.lenght;
  }
  has(key)
  { 
      if (key.includes('.')) {
          let keys = key.split('.')
            , val  = keys.length > 1 
                      ? this.data[keys[0]] ? this.data[keys[0]][keys[1]] : this.__hasData(keys) : this.data[keys[0]];
          return !mce.toolFn.isEmpty(val) ? true : false;
      } 
      return !mce.toolFn.isEmpty(this.data[key]) ? true : false;
  }
  __hasData(keys)
  {
     this.data[keys[0]] = {};
     return this.data[keys[0]][keys[1]];
  }
  exists (value)
  {
     return !mce.toolFn.isEmpty(value) ? true : false;
  }
  remove (key)
  {
     if (key.includes('.')) {
        let keys = key.split('.');
        keys.length > 1 ? delete this.data[keys[0]][keys[1]] : delete this.data[keys[0]];
     } else {
        delete this.data[key];
     }
     return this;
  }
}

let $class;
for ($class of ['Cache']) {
  window[ mce.toolFn.firstToLower($class) ] = createClass($class);
}

// create Class
function createClass ($class) {
   return window[mce.toolFn.firstToLower($class)] 
        ? window[mce.toolFn.firstToLower($class)]
        : eval('new ' + $class + '()');
}


// 获取参数
function getParams (el) {
    let params    = []
      , paramOnce = {}
      , element = mce.toolFn.isString(el) ? $(el) : mce.toolFn.getElement(el);
    element.find('tbody').eq(0).find('tr').each(function () {
        $(this).find('td').each(function () {
            let once = $(this).children().eq(0);
            once.attr('name') ? paramOnce[once.attr('name')] = (once.val() || once.text()) : '';
        });
        params.push(paramOnce);
        paramOnce = {};
    });
    return params;
}
// 获取请求url
function getRequstUrl (name) {
    // return apiConfig['requestUrl'] + apiConfig['requestUrlList'][name];
    return apiConfig['requestUrlList'][name];
}
// 获取状态码
function getStatus (status)
{
    return apiConfig['status'][status];
}
function inObjectVal (object,val,key = 'name') {
    for (v of object) {
        if (v[key] == val) return true;
    }
    return false;
}
// 格式化JSON源码(对象转换为JSON文本)
function jsonFormat(txt, compress) {
    var indentChar = '    ';
    if (/^\s*$/.test(txt)) {
        alert('返回数据为空,无法格式化! ');
        return "";
    }
    try {
        var data = eval('(' + txt + ')');
    } catch(e) {
        alert('数据源语法错误,格式化失败! 错误信息: ' + e.description, 'err');
        return;
    };
    var draw = [],
    last = false,
    This = this,
    line = compress ? '': '\n',
    nodeCount = 0,
    maxDepth = 0;

    var notify = function(name, value, isLast, indent
    /*缩进*/
    , formObj) {
        nodeCount++;
        /*节点计数*/
        for (var i = 0,
        tab = ''; i < indent; i++) tab += indentChar;
        /* 缩进HTML */
        tab = compress ? '': tab;
        /*压缩模式忽略缩进*/
        maxDepth = ++indent;
        /*缩进递增并记录*/
        if (value && value.constructor == Array) {
            /*处理数组*/
            draw.push(tab + (formObj ? ('"' + name + '":') : '') + '[' + line);
            /*缩进'[' 然后换行*/
            for (var i = 0; i < value.length; i++) notify(i, value[i], i == value.length - 1, indent, false);
            draw.push(tab + ']' + (isLast ? line: (',' + line)));
            /*缩进']'换行,若非尾元素则添加逗号*/
        } else if (value && typeof value == 'object') {
            /*处理对象*/
            draw.push(tab + (formObj ? ('"' + name + '":') : '') + '{' + line);
            /*缩进'{' 然后换行*/
            var len = 0,
            i = 0;
            for (var key in value) len++;
            for (var key in value) notify(key, value[key], ++i == len, indent, true);
            draw.push(tab + '}' + (isLast ? line: (',' + line)));
            /*缩进'}'换行,若非尾元素则添加逗号*/
        } else {
            if (typeof value == 'string') value = '"' + value + '"';
            draw.push(tab + (formObj ? ('"' + name + '":') : '') + value + (isLast ? '': ',') + line);
        };
    };
    var isLast = true,
    indent = 0;
    notify('', data, isLast, indent, false);
    return draw.join('');
}

if (typeof Vue != 'undefined') {
    Vue.$http = {
        post(url,data,callback = (response) => {
             response.code == getStatus('success') 
             ? dialog.success(response.msg,apiConfig['name']) 
             : dialog.error(response.msg,apiConfig['name']);
        },dataType) {
            return this.send(url,callback,data,'post',true,dataType);
        },
        getJSON(...params) {
            return this.send(...params);
        },
        send(url,callback = null,data = {},type = 'post',async = true,dataType = 'json') {
             $.ajax({
                url: url,
                type: type,
                data: data,
                dataType: dataType,
                async: async,
                success: function (response) {
                    if (callback != null) return callback(response);
                    console.log(response);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                   layer.closeAll();
                   var message = 'Status:' + XMLHttpRequest.status + ' readyState:' 
                   						   + XMLHttpRequest.readyState + ' error:' + textStatus;
                   throw new Error(message);
                }
             });
        },
    };
}