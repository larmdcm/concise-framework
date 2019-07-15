"use strict";
(function () {
	// 导航栏组件
	let navbar = {
		template: `
				<div>
					<div class="navbar-header">
		      			<a class="navbar-brand" href="javascript:;">{{ name }}</a>
		      			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
							<span class="sr-only">切换导航</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
				    </div>
				    <div class="collapse navbar-collapse navbar-let" id="navbar-collapse">
					    <ul class="nav navbar-nav">
					    	 <li v-for="item in listData" :data-url="item.dataUrl">
						    	 <a :href="item.url">{{ item.name }}</a>
					    	 </li>
					    </ul>
				    </div>
				</div>
		`,
		props: ['name','lists'],
		computed: {
			listData () {
				for (let i in this.lists) {
					this.lists[i].dataUrl = this.lists[i].url.substr(2);
				}
				return this.lists;
			}
		}
	}
	// table创建组件
	, tableCreate = {
		template: `<div class="col-sm-12">
			<div class="content">
				<label class="control-label">{{ title }}: 
					<a href="javascript:;" @click="create()" v-if="type == 'params' || type == 'return'">+新增参数</a>
					<a href="javascript:;" @click="showback()" v-else>显示隐藏</a>
				</label>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="table-responsive content">
						<table class="table table-striped table-bordered table-hover table-condensed" v-show="isShow">
							<thead>
								<tr>
									<th v-for="item in thead">{{ item }}</th>
								</tr>
							</thead>
							<template v-if="type == 'params'">
								<tr-params :tbody="tbody"></tr-params>
							</template>
							<template v-if="type == 'return'">
								<tr-return :tbody="tbody"></tr-return>
							</template>
							<template v-if="type == 'readParams'">
								<tr-read-params :tbody="tbody"></tr-read-params>
							</template>
							<template v-if="type == 'readReturn'">
								<tr-read-return :tbody="tbody"></tr-read-return>
							</template>
					</table>
					</div>
				</div>
			</div>
		</div>
		`,
		props: ['thead','tbody','type'],
		data() {
			return {
				isShow: true
			}
		},
		methods: {
			create () {
				let createType = this.type == 'params' ? '#postparam' : '#returnparam'
				   , params = getParams(createType)
				   , k
				   , v;
				for ([k,v] of params.entries()) {
					this.tbody[k] = params[k];
				}
				this.tbody.push({
					name: '',
					type: '',
					is_need: 1,
					memo: '',
					default: ''
				})
			},
			remove (index) {
				let createType = this.type == 'params' ? '#postparam' : '#returnparam'
				   , params = getParams(createType)
				   , k
				   , v;
				for ([k,v] of params.entries()) {
					this.tbody[k] = params[k];
				}
				this.tbody.splice(index,1);
			},
			showback() {
				this.isShow = !this.isShow;
			},
			changeParams() {
				app.$emit("changeParams",getParams('#postparam'));
			}
		},
		computed: {
			title () {
				let title = '';
				switch (this.type) {
					case 'params':
						title = '请求';
						break;
					case 'return':
						title = '返回';
						break;
					case 'readParams':
						title = '参数说明';
						break;
					case 'readReturn':
						title = '返回说明';
				}
				return title;
			}
		},
		components: {
			"tr-params": {
				template: `
				<tbody>
					<tr v-for="(item,index) in tbody">
						<td><input type="text" :value="item.name" class="form-control" name="name"/></td>
						<td><input type="text" :value="item.type" class="form-control" name="type"/></td>
						<td>
							<select class="form-control" name="is_need">
								<template v-for="(v,k) in ['否','是']">
									<option :value="k" v-if="k == item.is_need" selected="selected">{{ v }}</option>
									<option :value="k" v-else>{{ v }}</option>
								</template>
							</select>
						</td>
						<td><input type="text" :value="item.memo" class="form-control" name="memo"/></td>
						<td>
							<input type="text" :value="item.default" class="form-control" name="default"/>
						</td>
						<td class="text-center">
							<input @click="$parent.remove(index)" type="button" value="删除" class="btn btn-danger btn-xs"/>
						</td>
					</tr>
				</tbody>
				`,
				props: ['tbody']
			},
		  "tr-return": {
		  	template: `
				<tbody>
					<tr v-for="(item,index) in tbody">
						<td><input type="text" :value="item.name" class="form-control" name="name"/></td>
						<td><input type="text" :value="item.type" class="form-control" name="type"/></td>
						<td><input type="text" :value="item.memo" class="form-control" name="memo"/></td>
						<td class="text-center">
							<input @click="$parent.remove(index)" type="button" value="删除" class="btn btn-danger btn-xs"/>
						</td>
					</tr>
				</tbody>
		  	`,
		  	props: ['tbody']
		  },
		  "tr-read-params": {
		  	 template: `
				<tbody>
						<tr v-for="item in tbody">
							<td><span name="name">{{ item.name }}</span></td>
							<td>{{ item.type }}</td>
							<td>{{ item.is_need }}</td>
							<td>{{ item.memo }}</td>
							<td>
								<input type="text" :value="item.default" name="default" class="form-control" @keyup="$parent.changeParams"/>
							</td>
						</tr>
				</tbody>
		  	 `,
		  	 props: ['tbody']
		  },
		  "tr-read-return": {
		  	template: `
					<tbody>
						<tr v-for="item in tbody">
							<td>{{ item.name }}</td>
							<td>{{ item.type }}</td>
							<td>{{ item.memo }}</td>
						</tr>
					</tbody>
		  	`,
		  	props: ['tbody']
		  }
		}
	} ,
	// 接口select选择组件
	interfaceSelectList = {
		template: `
				<select v-model="selected" @change="selectChange" name="iterface" class="form-control selectpicker show-tick" id="interfaceSelect" data-live-search="true">
					 <template v-if="type == 'add'">
						<option value="add" selected="selected">新增接口</option>
					 </template>
					 <template v-for="(item,index) in iters">
						 <option v-if="type != 'add' && index == 0" :value="item.url" selected="selected">{{index + 1}}-{{ item.name }}</option>
						 <option :value="item.url" v-else>{{index + 1}}-{{ item.name }}</option>
					 </template>
				</select>
		`,
		props: ['type'] ,
		data() {
			return {
				iters: [],
				selected: this.type
			};
		},
		created() {
			let vm = this
			  , callback = (response) => {
				 if (response.code == getStatus('success')) {
				 	cache.set("readIterList",response);
				 	let k , v;
				 	for ([k,v] of response.data.entries()) {
				 		vm.$set(vm.iters,k,v);
				 	}
				 } else {
				 	dialog.error(response.msg,apiConfig['name']);
				 }
				 setTimeout(function () {
					 $('#interfaceSelect').selectpicker('refresh');
					 app.$emit("changeUrl",vm.selected == 'read' ? response.data[0] ? response.data[0].url : '' : vm.selected);
				 },1);
			  };
			 cache.has("readIterList") 
			 			? callback(cache.read("readIterList")) 
			 			: Vue.$http.post(getRequstUrl('read'),{},callback);
		},
		methods: {
			selectChange() {
				app.$emit("changeUrl",this.selected);
			}
		},
		mounted () {
			var vm = this;
			setTimeout(function () {
				app.$on("changeIterUrl",(k,iterData,keyName) => {
					 let key = "iterList." + k
					   , iterDatas = cache.read("readIterList");
					 if (k == 'add') {
					 	cache.set("iterList." + keyName,{data:iterData});
					 	vm.iters.push({url: keyName,name: iterData.name});
					 	iterDatas.data.push({url: keyName,name: iterData.name});
					 } else {
						 cache.set(key,{data:iterData});
						 vm.iters.forEach((v,index) => {
						 	 if (v.url == k) {
						 	 	vm.iters[index]['name'] = iterData.name;
						 	 	iterDatas.data[index]['name'] = iterData.name;
						 	 }
						 });
					 }
					 cache.set("readIterList",iterDatas);
					 setTimeout(function () {
					 	$('#interfaceSelect').selectpicker('refresh');
				 	 },1);
				});
			},1);
		}
	};
	window.components = {
		navbar: navbar,
		tableCreate: tableCreate,
		interfaceSelectList: interfaceSelectList
	};
})();