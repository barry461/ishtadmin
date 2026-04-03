// =========================== sa对象封装一系列工具方法 ===========================  
var sa = {
	version: '2.4.3',
	update_time: '2020-10-2',
	info: '新增双击layer标题处全屏',
	app_name: "吃瓜后台",
	app_logo: 'sa-frame/admin-logo.png',
	debug: true
};

const isSelf = window.parent === window;
const self = window.parent || window;
// ===========================  当前环境配置  ======================================= 
(function () {
	// 公司开发环境
	var cfg_dev = {
		api_url: 'https://api-yaf2.yvyjdhr.com/adminv2.php/adminv2',	// 所有ajax请求接口父地址
		// api_url: 'http://911cgw-adminv2.local/adminv2.php/adminv2',	// 所有ajax请求接口父地址
		web_url: 'http://www.baidu.com'		// 此项目前台地址 (此配置项非必须)
	}
	// 服务器测试环境
	var cfg_test = {
		api_url: 'http://www.baidu.com',
		web_url: 'http://www.baidu.com'
	}
	// 正式生产环境
	var cfg_prod = {
		api_url: 'http://www.baidu.com',
		web_url: 'http://www.baidu.com'
	}
	sa.cfg = cfg_dev; // 最终环境 , 上线前请选择正确的环境 
})();


// ===========================  ajax的封装  ======================================= 
(function () {
	const originalConsole = isSelf ? window.console : window.parent.console
	const log = originalConsole.log
	const error = originalConsole.error
	sa.log = {
		info: log,
		error
	}

	// 模拟一个ajax 
	// 请注意: 本模板中所有ajax请求调用的均为此模拟函数 
	sa.ajax2 = function (url, data, success200, cfg) {
		// 如果是简写模式(省略了data参数)
		if (typeof data === 'function') {
			cfg = success200;
			success200 = data;
			data = {};
		}
		// 几个默认配置 
		cfg = cfg || {};
		cfg.baseUrl = (url.indexOf('http') === 0 ? '' : sa.cfg.api_url);	// 父url，拼接在url前面
		// 设定一个默认的提示文字 
		if (cfg.msg == undefined || cfg.msg == null || cfg.msg == '') {
			cfg.msg = '正在努力加载...';
		}
		// 默认延时函数 
		if (cfg.sleep == undefined || cfg.sleep == null || cfg.sleep == '' || cfg.sleep == 0) {
			cfg.sleep = 600;
		}
		// 默认的模拟数据
		cfg.res = cfg.res || {
			code: 200,
			msg: 'ok',
			data: []
		}
		// 开始loding 
		sa.loading(cfg.msg);

		// 打印请求地址和参数, 以便调试 
		console.log("======= 模拟ajax =======");
		console.log("请求地址：" + cfg.baseUrl + url);
		console.log("请求参数：" + JSON.stringify(data));

		// 模拟ajax的延时 
		setTimeout(function () {
			sa.hideLoading();	// 隐藏掉转圈圈 
			console.log('返回数据：', cfg.res);
			success200(cfg.res);
		}, cfg.sleep)
	};

	if (window.axios && window.CryptoJS) {

		const appkey = "5589d41f92a597d016b037ac37db243d";
		const key = CryptoJS.enc.Utf8.parse("2acf7e91e9864673");
		const iv = CryptoJS.enc.Utf8.parse("1c29882d3ddfcfd6");
		const sha256 = (data) => CryptoJS.SHA256(data).toString();
		const md5 = (data) => CryptoJS.MD5(data).toString();

		const token_key = "access_token"
		sa.set_token = (value) => {
			localStorage.setItem(token_key, value)
		}

		sa.get_token = () => {
			return localStorage.getItem(token_key) || ''
		}

		sa.remove_token = () => {
			localStorage.removeItem(token_key)
		}

		const serialization = (params) => {
			const keyValues = [`data=${params["data"]}`, `timestamp=${params["timestamp"]}`];
			const spliceString = `${keyValues.join("&")}${appkey}`;
			const digest = sha256(spliceString);
			return md5(digest);
		};

		// 加密
		const encrypts = (data, key, iv) => {
			const preprocessing = CryptoJS.AES.encrypt(data, key, {
				iv,
				mode: CryptoJS.mode.CBC,
				padding: CryptoJS.pad.Pkcs7,
			}).toString();

			const timestamp = Math.floor(Date.now() / 1000);
			const sign = serialization({ data: preprocessing, timestamp });

			return `timestamp=${timestamp}&data=${preprocessing}&sign=${sign}`;
		};

		// 解密
		const decrypts = (
			ciphertext,
			key,
			iv,
			padding = CryptoJS.pad.Pkcs7,
			enc = CryptoJS.enc.Utf8
		) => {
			return CryptoJS.AES.decrypt(ciphertext, key, {
				iv,
				mode: CryptoJS.mode.CBC,
				padding,
			}).toString(enc);
		};

		const encryptData = (query) => encrypts(query, key, iv);
		const decryptData = (query) => decrypts(query, key, iv);

		const http = axios.create({
			baseURL: sa.cfg.api_url,
			timeout: 30000,  // 增加到30秒
			headers: {
				"Content-Type": "application/x-www-form-urlencoded",
			},
			method: "post"
		});

		/** 统一把 axios error 归一化成你想要的结构 */
		function normalizeAxiosError(error) {
			// 请求发出但服务端有响应（4xx/5xx）
			if (error.response) {
				const { status, data, config } = error.response;
				return {
					type: "HTTP_ERROR",
					status,
					message: data?.message || data?.msg || error.message || "Request failed",
					data,
					url: config?.url,
					method: config?.method,
				};
			}

			// 请求超时或网络错误（没有响应）
			if (error.config) {
				const { config } = error;
				return {
					type: "HTTP_ERROR",
					status: undefined,
					message: error.message || "Request failed",
					data: undefined,
					url: config?.url,
					method: config?.method,
				};
			}

			return {
				type: "HTTP_ERROR",
				status: undefined,
				message: error.message || "Unknown error",
				data: undefined,
				url: undefined,
				method: undefined,
			}
		}
		http.interceptors.request.use(
			(config) => {

				config.data = config.data || {}
				const token = sa.get_token();
				// 所有请求（含 GET）都带上 Token 头，否则 GET 接口会因无 token 被后端判 401 导致退登
				if (token) {
					config.headers['Token'] = token;
				}
				if (config.data && !(config.data instanceof FormData)) {

					if (config.method === "post") {

						const baseRequestParams = {
							token,
						};

						const requestData = config.data
							? { ...baseRequestParams, ...config.data }
							: baseRequestParams;

						const _data = JSON.stringify(requestData)
						if (sa.debug) {
							config.__dev__log__data__ = _data
						}
						config.data = encryptData(_data);


					}
				} else {
					if (config.data && config.data.append) {
						config.data.append('token', token)
					}
				}


				return config;
			},
			(error) => Promise.reject(normalizeAxiosError(error))
		);

		// 响应拦截器
		http.interceptors.response.use(
			(response) => {
				const { config, data } = response

				const { showError = true, showSuccess = false, throwError = true, successCode = 1 } = config

				// 判断是否需要解密：errcode === 0 或者 crypt === true
				const needDecrypt = (data != null && data.data != null && data.errcode === 0) ||
					(data != null && data.crypt === true);

				if (needDecrypt) {
					let decryptedData;

					// 如果有 crypt 标记但数据未加密（已经是明文对象），直接使用
					if (data.crypt === true && typeof data.data === 'object' && data.status !== undefined) {
						// 数据已经是明文格式，不需要解密
						decryptedData = data;
					} else if (typeof data.data === 'string') {
						// 数据是加密字符串，需要解密
						decryptedData = JSON.parse(decryptData(data.data));
					} else {
						// 其他情况，直接使用原始数据
						decryptedData = data;
					}

					response.data = decryptedData;

					if (sa.debug) {
						log(
							// @ts-ignore
							`%curl：[${config.url}]\n%cparams before crypto：${config.__dev__log__data__}\n`,
							'color: #1677ff; font-size: 14px;',
							'color: #000; font-size: 12px; ',
						)
						log(
							`%cresponse：[${config.url}]`,
							`color: ${successCode === decryptedData.status ? '#07c160' : '#ee0a24'};font-size: 15px;`,
						)
						log(decryptedData)
					}
					if (successCode === decryptedData.status) {
						if (showSuccess) {
							const message = decryptedData?.data?.msg ?? decryptedData?.msg ?? decryptedData?.data
							message && typeof ELEMENT !== 'undefined' && ELEMENT.Message({
								message,
								type: 'success'
							})
						}
						return Promise.resolve(decryptedData)
					}




					if (showError && typeof ELEMENT !== 'undefined') {
						ELEMENT.Message({
							showClose: true,
							message: decryptedData.msg,
							type: 'error'
						});
					}

					console.log('decryptedData: ', decryptedData);
					if (decryptedData.status === 401) {
						localStorage.removeItem("access_token");
						ELEMENT.Message.closeAll()
						ELEMENT.Message({
							showClose: true,
							message: decryptedData.msg,
							type: 'error'
						});

						setTimeout(() => {
							self.location.href = '/login.html'
						}, 2000);

						// return
					}

					return throwError ? Promise.reject(decryptedData) : Promise.resolve(decryptedData)

				} else {
					if (showError && typeof ELEMENT !== 'undefined') {
						ELEMENT.Message({
							showClose: true,
							message: data,
							type: 'error'
						});
					}

					log(
						// @ts-ignore
						`%curl：[${config.url}]\n%cparams before crypto：${config.__dev__log__data__}\n`,
						'color: #1677ff; font-size: 14px;',
						'color: #000; font-size: 12px; ',
					)
					log(
						`%cresponse：[${config.url}]`,
						`color: ${'#ee0a24'};font-size: 15px;`,
					)
					log(data)

					if (throwError) {
						return Promise.reject(data)
					}

					return data
				}


			},
			(error) => {
				const normalized = normalizeAxiosError(error);

				// 401 统一处理示例
				if (normalized.type === "HTTP_ERROR" && normalized.status === 401) {
					let msg = normalized.message
					if (normalized.data?.data) {
						const res = JSON.parse(decryptData(normalized.data.data));
						console.log('res: ', res);
						msg = res.msg
					}

					ELEMENT.Message.closeAll()
					ELEMENT.Message({
						showClose: true,
						message: msg,
						type: 'error'
					});
					localStorage.removeItem("access_token");

					setTimeout(() => {
						self.location.href = '/login.html'
					}, 2000);
				}

				return Promise.reject(normalized);
			}
		);
		sa.http = http
	} else {
		sa.http = () => {
			console.warn(
				`请用script标签引入axios: 
				<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js"></script>
				<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.9.0/axios.min.js"></script>
				`
			)
		}
	}


})();


// ===========================  封装弹窗相关函数   ======================================= 
(function () {

	var me = sa;
	if (window.layer) {
		layer.ready(function () { });
	}



	// tips提示文字  
	me.msg = function (msg, cfg) {
		msg = msg || '操作成功';
		layer.msg(msg, cfg);
	};

	// 操作成功的提示  
	me.ok = function (msg) {
		msg = msg || '操作成功';
		layer.msg(msg, { anim: 0, icon: 1 });
	}
	me.ok2 = function (msg) {
		msg = msg || '操作成功';
		layer.msg(msg, { anim: 0, icon: 6 });
	}

	// 操作失败的提示  
	me.error = function (msg) {
		msg = msg || '操作失败';
		layer.msg(msg, { anim: 6, icon: 2 });
	}
	me.error2 = function (msg) {
		msg = msg || '操作失败';
		layer.msg(msg, { anim: 6, icon: 5 });
	}

	// alert弹窗 [text=提示文字, okFn=点击确定之后的回调函数]
	me.alert = function (text, okFn) {
		// 开始弹窗 
		layer.alert(text, function (index) {
			layer.close(index);
			if (okFn) {
				okFn();
			}
		});
	};

	// 询问框 [text=提示文字, okFn=点击确定之后的回调函数]
	me.confirm = function (text, okFn) {
		layer.confirm(text, {}, function (index) {
			layer.close(index);
			if (okFn) {
				okFn();
			}
		}.bind(this));
	};

	// 输入框 [title=提示文字, okFn=点击确定后的回调函数, formType=输入框类型(0=文本,1=密码,2=多行文本域) 可省略, value=默认值 可省略 ]  
	me.prompt = function (title, okFn, formType, value) {
		layer.prompt({
			title: title,
			formType: formType,
			value: value
		}, function (pass, index) {
			layer.close(index);
			if (okFn) {
				okFn(pass);
			}
		});
	}

	// 打开loading
	me.loading = function (msg) {
		layer.closeAll();	// 开始前先把所有弹窗关了
		return layer.msg(msg, { icon: 16, shade: 0.3, time: 1000 * 20, skin: 'ajax-layer-load' });
	};

	// 隐藏loading
	me.hideLoading = function () {
		layer.closeAll();
	};

	// ============== 一些常用弹窗 ===================== 

	// 大窗显示一个图片 
	// 参数: src=地址、w=宽度(默认80%)、h=高度(默认80%)
	me.showImage = function (src, w, h) {
		w = w || '80%';
		h = h || '80%';
		var content = '<div style="height: 100%; overflow: hidden !important;">' +
			'<img src="' + src + ' " style="width: 100%; height: 100%;" />' +
			'</div>';
		layer.open({
			type: 1,
			title: false,
			shadeClose: true,
			closeBtn: 0,
			area: [w, h], //宽高
			content: content
		});
	}

	// 预览一组图片 
	// srcList=图片路径数组(可以是json样，也可以是逗号切割式), index=打开立即显示哪张(可填下标, 也可填写src路径)
	me.showImageList = function (srcList, index) {
		// 如果填的是个string 
		srcList = srcList || [];
		if (typeof srcList === 'string') {
			try {
				srcList = JSON.parse(srcList);
			} catch (e) {
				try {
					srcList = srcList.split(',');	// 尝试字符串切割
				} catch (e) {
					srcList = [];
				}
			}
		}
		// 如果填的是路径 
		index = index || 0;
		if (typeof index === 'string') {
			index = srcList.indexOf(index);
			index = (index == -1 ? 0 : index);
		}

		// 开始展示 
		var arr_list = [];
		srcList.forEach(function (item) {
			arr_list.push({
				alt: '左右键切换',
				pid: 1,
				src: item,
				thumb: item
			})
		})
		layer.photos({
			photos: {
				title: '',
				id: new Date().getTime(),
				start: index,
				data: arr_list
			}
			, anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
		});
	}

	// 显示一个iframe 
	// 参数: 标题，地址，宽，高 , 点击遮罩是否关闭, 默认false 
	me.showIframe = function (title, url, w, h, shadeClose) {
		// 参数修正
		w = w || '95%';
		h = h || '95%';
		shadeClose = (shadeClose === undefined ? false : shadeClose);
		// 弹出面板 
		var index = layer.open({
			type: 2,
			title: title,	// 标题 
			shadeClose: shadeClose,	// 是否点击遮罩关闭
			maxmin: true, // 显示最大化按钮
			shade: 0.8,		// 遮罩透明度 
			scrollbar: false,	// 屏蔽掉外层的滚动条
			moveOut: true,		// 是否可拖动到外面
			area: [w, h],	// 大小 
			content: url,	// 传值 
			// 解决拉伸或者最大化的时候，iframe高度不能自适应的问题
			resizing: function (layero) {
				solveLayerBug(index);
			}
		});
		// 解决拉伸或者最大化的时候，iframe高度不能自适应的问题
		if (window.$) {
			$('#layui-layer' + index + ' .layui-layer-max').click(function () {
				setTimeout(function () {
					solveLayerBug(index);
				}, 200)
			})
		}
	}
	me.showView = me.showIframe;

	// 显示一个iframe, 底部按钮方式
	// 参数: 标题，地址，点击确定按钮执行的代码(在子窗口执行)，宽，高 
	me.showIframe2 = function (title, url, evalStr, w, h) {
		// 参数修正
		w = w || '95%';
		h = h || '95%';
		// 弹出面板 
		var index = layer.open({
			type: 2,
			title: title,	// 标题 
			closeBtn: (title ? 1 : 0),	// 是否显示关闭按钮
			btn: ['确定', '取消'],
			shadeClose: false,	// 是否点击遮罩关闭
			maxmin: true, // 显示最大化按钮
			shade: 0.8,		// 遮罩透明度 
			scrollbar: false,	// 屏蔽掉外层的滚动条
			moveOut: true,		// 是否可拖动到外面
			area: [w, h],	// 大小 
			content: url,	// 传值 
			// 解决拉伸或者最大化的时候，iframe高度不能自适应的问题
			resizing: function (layero) {

			},
			yes: function (index, layero) {
				var iframe = document.getElementById('layui-layer-iframe' + index);
				var iframeWindow = iframe.contentWindow;
				iframeWindow.eval(evalStr);
			}
		});
	}


	// 当前iframe关闭自身  (在iframe中调用)
	me.closeCurrIframe = function () {
		try {
			var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
			parent.layer.close(index); //再执行关闭   
		} catch (e) {
			//TODO handle the exception
		}
	}
	me.closeCurrView = me.closeCurrIframe;


	//执行一个函数, 解决layer拉伸或者最大化的时候，iframe高度不能自适应的问题
	function solveLayerBug(index) {
		if (!window.$) {
			return;
		}
		var selected = '#layui-layer' + index;
		var height = $(selected).height();
		var title_height = $(selected).find('.layui-layer-title').height();
		$(selected).find('iframe').css('height', (height - title_height) + 'px');
	}


	// 监听回车事件，达到回车关闭弹窗的效果 
	if (window.$) {
		$(document).on('keydown', function () {
			if (event.keyCode === 13 && $(".layui-layer-btn0").length == 1 && !window.is_not_watch_enter && $(this).find('.layui-layer-input').length == 0) {
				$(".layui-layer-btn0").click();
				return false;
			}
		});
	}



})();


// ===========================  常用util函数封装   ======================================= 
(function () {

	// 超级对象
	var me = sa;

	// ===========================  常用util函数封装   ======================================= 
	if (true) {

		// 从url中查询到指定参数值 
		me.p = function (name, defaultValue) {
			var query = window.location.search.substring(1);
			var vars = query.split("&");
			for (var i = 0; i < vars.length; i++) {
				var pair = vars[i].split("=");
				if (pair[0] == name) { return pair[1]; }
			}
			return (defaultValue == undefined ? null : defaultValue);
		}
		me.q = function (name, defaultValue) {
			var query = window.location.search.substring(1);
			var vars = query.split("&");
			for (var i = 0; i < vars.length; i++) {
				var pair = vars[i].split("=");
				if (pair[0] == name) { return pair[1]; }
			}
			return (defaultValue == undefined ? null : defaultValue);
		}

		// 判断一个变量是否为null
		// 返回true或false，如果return_obj有值，则在true的情况下返回return_obj
		me.isNull = function (obj, return_obj) {
			var flag = [null, undefined, '', 'null', 'undefined'].indexOf(obj) != -1;
			if (return_obj === undefined) {
				return flag;
			} else {
				if (flag) {
					return return_obj;
				} else {
					return obj;
				}
			}
		}

		// 将时间戳转化为指定时间
		// way：方式（1=年月日，2=年月日时分秒）默认1,  也可以指定格式：yyyy-MM-dd HH:mm:ss  
		me.forDate = function (inputTime, way) {
			if (me.isNull(inputTime) == true) {
				return "";
			}
			var date = new Date(inputTime);
			var y = date.getFullYear();
			var m = date.getMonth() + 1;
			m = m < 10 ? ('0' + m) : m;
			var d = date.getDate();
			d = d < 10 ? ('0' + d) : d;
			var h = date.getHours();
			h = h < 10 ? ('0' + h) : h;
			var minute = date.getMinutes();
			var second = date.getSeconds();
			minute = minute < 10 ? ('0' + minute) : minute;
			second = second < 10 ? ('0' + second) : second;
			var ms = date.getMilliseconds();

			way = way || 1;
			// way == 1  年月日
			if (way === 1) {
				return y + '-' + m + '-' + d;
			}
			// way == 1  年月日时分秒 
			if (way === 2) {
				return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;
			}
			// way == 具体格式   标准格式: yyyy-MM-dd HH:mm:ss
			if (typeof way == 'string') {
				return way.replace("yyyy", y).replace("MM", m).replace("dd", d).replace("HH", h).replace("mm", minute).replace("ss", second).replace("ms", ms);
			}
			return y + '-' + m + '-' + d;
		};
		// 时间日期 
		me.forDatetime = function (inputTime) {
			return me.forDate(inputTime, 2);
		}

		// 将时间转化为 个性化 如：3小时前, 
		// d1 之于 d2 ，d2不填则默认取当前时间 
		me.forDate2 = function (d, d2) {

			var hou = "前";

			if (d == null || d == '') {
				return '';
			}
			if (d2 == null || d2 == '') {
				d2 = new Date();
			}
			d2 = new Date(d2).getTime();

			var timestamp = new Date(d).getTime() - 1000;
			var mistiming = Math.round((d2 - timestamp) / 1000);
			if (mistiming < 0) {
				mistiming = 0 - mistiming;
				hou = '后'
			}
			var arrr = ['年', '月', '周', '天', '小时', '分钟', '秒'];
			var arrn = [31536000, 2592000, 604800, 86400, 3600, 60, 1];
			for (var i = 0; i < arrn.length; i++) {
				var inm = Math.floor(mistiming / arrn[i]);
				if (inm != 0) {
					return inm + arrr[i] + hou;
				}
			}
		}

		// 综合以上两种方式，进行格式化
		// 小于24小时的走forDate2，否则forDat 
		me.forDate3 = function (d, way) {
			if (d == null || d == '') {
				return '';
			}
			var cha = new Date().getTime() - new Date(d).getTime();
			cha = (cha > 0 ? cha : 0 - cha);
			if (cha < (86400 * 1000)) {
				return me.forDate2(d);
			}
			return me.forDate(d, way);
		}

		// 返回时间差, 此格式数组：[x, x, x, 天, 时, 分, 秒]
		me.getSJC = function (small_time, big_time) {
			var date1 = new Date(small_time); //开始时间
			var date2 = new Date(big_time); //结束时间
			var date3 = date2.getTime() - date1.getTime(); //时间差秒
			//计算出相差天数
			var days = Math.floor(date3 / (24 * 3600 * 1000));

			//计算出小时数
			var leave1 = date3 % (24 * 3600 * 1000); //计算天数后剩余的毫秒数
			var hours = Math.floor(leave1 / (3600 * 1000));

			//计算相差分钟数
			var leave2 = leave1 % (3600 * 1000); //计算小时数后剩余的毫秒数
			var minutes = Math.floor(leave2 / (60 * 1000));

			//计算相差秒数
			var leave3 = leave2 % (60 * 1000); //计算分钟数后剩余的毫秒数
			var seconds = Math.round(leave3 / 1000);

			// 返回数组
			return [0, 0, 0, days, hours, minutes, seconds];
		}

		// 将日期，加上指定天数
		me.dateAdd = function (d, n) {
			var s = new Date(d).getTime();
			s += 86400000 * n;
			return new Date(s);
		}

		// 转化json，出错返回默认值
		me.JSONParse = function (obj, default_obj) {
			try {
				return JSON.parse(obj) || default_obj;
			} catch (e) {
				return default_obj || {};
			}
		}

		// 截取指定长度字符，默认50
		me.maxLength = function (str, length) {
			length = length || 50;
			if (!str) {
				return "";
			}
			return (str.length > length) ? str.substr(0, length) + ' ...' : str;
		},

			// 过滤掉标签
			me.text = function (str) {
				if (!str) {
					return "";
				}
				return str.replace(/<[^>]+>/g, "");
			}

		// 为指定集合的每一项元素添加上is_update属性 
		me.listAU = function (list) {
			list.forEach(function (ts) {
				ts.is_update = false;
			})
			return list;
		}

		// 获得一段文字中所有图片的路径
		me.getSrcList = function (str) {
			try {
				var imgReg = /<img.*?(?:>|\/>)/gi;	//匹配图片（g表示匹配所有结果i表示区分大小写）
				var srcReg = /src=[\'\"]?([^\'\"]*)[\'\"]?/i;	//匹配src属性
				var arr = str.match(imgReg);	// 图片数组
				var srcList = [];
				for (var i = 0; i < arr.length; i++) {
					var src = arr[i].match(srcReg);
					srcList.push(src[1]);
				}
				return srcList;
			} catch (e) {
				return [];
			}
		}

		// 无精度损失的乘法
		me.accMul = function (arg1, arg2) {
			var m = 0,
				s1 = arg1.toString(),
				s2 = arg2.toString(),
				t;

			t = s1.split(".");
			// 判断有没有小数位，避免出错
			if (t[1]) {
				m += t[1].length
			}

			t = s2.split(".");
			if (t[1]) {
				m += t[1].length
			}

			return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m)
		}

		// 正则验证是否为手机号
		me.isPhone = function (str) {
			str = str + '';
			if ((/^1[34578]\d{9}$/.test(str))) {
				return true;
			}
			return false;
		}

		// 产生随机字符串
		me.randomString = function (len) {
			len = len || 32;
			var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
			var maxPos = $chars.length;
			var str = '';
			for (i = 0; i < len; i++) {
				str += $chars.charAt(Math.floor(Math.random() * maxPos));
			}
			return str;
		}

		// 刷新页面
		me.f5 = function () {
			location.reload();
		}

		// 动态加载js 
		me.loadJS = function (src, onload) {
			var script = document.createElement("script");
			script.setAttribute("type", "text/javascript");
			script.src = src;
			script.onload = onload;
			document.body.appendChild(script);
		}

		// 产生随机数字 
		me.randomNum = function (min, max) {
			return parseInt(Math.random() * (max - min + 1) + min, 10);
		}

		// 打开页面
		me.open = function (url) {
			window.open(url);
		}



		// == if 结束
	}

	// ===========================  数组操作   ======================================= 
	if (true) {

		// 从数组里获取数据,根据指定数据
		me.getArrayField = function (arr, prop) {
			var propArr = [];
			for (var i = 0; i < arr.length; i++) {
				propArr.push(arr[i][prop]);
			}
			return propArr;
		}

		// 从数组里获取数据,根据指定数据
		me.arrayGet = function (arr, prop, value) {
			for (var i = 0; i < arr.length; i++) {
				if (arr[i][prop] == value) {
					return arr[i];
				}
			}
			return null;
		}

		// 从数组删除指定记录
		me.arrayDelete = function (arr, item) {
			if (item instanceof Array) {
				for (let i = 0; i < item.length; i++) {
					let ite = item[i];
					let index = arr.indexOf(ite);
					if (index > -1) {
						arr.splice(index, 1);
					}
				}
			} else {
				var index = arr.indexOf(item);
				if (index > -1) {
					arr.splice(index, 1);
				}
			}
		}

		// 从数组删除指定id的记录
		me.arrayDeleteById = function (arr, id) {
			var item = me.arrayGet(arr, 'id', id);
			me.arrayDelete(arr, item);
		}

		// 将数组B添加到数组A的开头
		me.unshiftArray = function (arrA, arrB) {
			if (arrB) {
				arrB.reverse().forEach(function (ts) {
					arrA.unshift(ts);
				})
			}
			return arrA;
		}

		// 将数组B添加到数组A的末尾
		me.pushArray = function (arrA, arrB) {
			if (arrB) {
				arrB.forEach(function (ts) {
					arrA.push(ts);
				})
			}
			return arrA;
		}

		// == if 结束
	}

	// ===========================  浏览器相关   ======================================= 
	if (true) {

		// set cookie 值 
		me.setCookie = function setCookie(cname, cvalue, exdays) {
			exdays = exdays || 30;
			var d = new Date();
			d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
			var expires = "expires=" + d.toGMTString();
			document.cookie = cname + "=" + escape(cvalue) + "; " + expires + "; path=/";
		}

		// get cookie 值
		me.getCookie = function (objName) {
			var arrStr = document.cookie.split("; ");
			for (var i = 0; i < arrStr.length; i++) {
				var temp = arrStr[i].split("=");
				if (temp[0] == objName) {
					return unescape(temp[1])
				};
			}
			return "";
		}

		// 复制指定文本
		me.copyText = function (str) {
			var oInput = document.createElement('input');
			oInput.value = str;
			document.body.appendChild(oInput);
			oInput.select(); // 选择对象
			document.execCommand("Copy"); // 执行浏览器复制命令
			oInput.className = 'oInput';
			oInput.style.display = 'none';
		}

		// jquery序列化表单增强版： 排除空值
		me.serializeNotNull = function (selected) {
			if (!window.$) {
				return '';
			}
			var serStr = $(selected).serialize();
			return serStr.split("&").filter(function (str) { return !str.endsWith("=") }).join("&");
		}

		// 将cookie序列化为k=v形式
		me.strCookie = function () {
			return document.cookie.replace(/; /g, "&");
		}

		// 回到顶部
		me.goTop = function () {
			function smoothscroll() {
				var currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
				if (currentScroll > 0) {
					window.requestAnimationFrame(smoothscroll);
					window.scrollTo(0, currentScroll - (currentScroll / 5));
				}
			};
			smoothscroll();
		}



		// == if 结束
	}

	// =========================== javascript对象操作   ======================================= 
	if (true) {
		// 去除json对象中的空值 
		me.removeNull = function (obj) {
			var newObj = {};
			if (obj != undefined && obj != null) {
				for (var key in obj) {
					if (obj[key] === undefined || obj[key] === null || obj[key] === '') {
						// 
					} else {
						newObj[key] = obj[key];
					}
				}
			}
			return newObj;
		}

		// JSON 浅拷贝, 返回拷贝后的obj
		me.copyJSON = function (obj) {
			if (obj === null || obj === undefined) {
				return obj;
			};
			var new_obj = {};
			for (var key in obj) {
				new_obj[key] = obj[key];
			}
			return new_obj;
		}

		// json合并, 将 defaulet配置项 转移到 user配置项里 并返回 user配置项
		me.extendJson = function (userOption, defaultOption) {
			if (!userOption) {
				return defaultOption;
			};
			for (var key in defaultOption) {
				if (userOption[key] === undefined) {
					userOption[key] = defaultOption[key];
				} else if (userOption[key] == null) {

				} else if (typeof userOption[key] == "object") {
					me.extendJson(userOption[key], defaultOption[key]); //深度匹配
				}
			}
			return userOption;
		}

		// == if 结束
	}

	// ===========================  本地集合存储   ======================================= 
	if (true) {

		// 获取指定key的list
		me.keyListGet = function (key) {
			try {
				var str = localStorage.getItem('LIST_' + key);
				if (str == undefined || str == null || str == '' || str == 'undefined' || typeof (JSON.parse(str)) == 'string') {
					//alert('key' + str);
					str = '[]';
				}
				return JSON.parse(str);
			} catch (e) {
				return [];
			}
		},

			me.keyListSet = function (key, list) {
				localStorage.setItem('LIST_' + key, JSON.stringify(list));
			},

			me.keyListHas = function (key, item) {
				var arr2 = me.keyListGet(key);
				return arr2.indexOf(item) != -1;
			},

			me.keyListAdd = function (key, item) {
				var arr = me.keyListGet(key);
				arr.push(item);
				me.keyListSet(key, arr);
			},

			me.keyListRemove = function (key, item) {
				var arr = me.keyListGet(key);
				var index = arr.indexOf(item);
				if (index > -1) {
					arr.splice(index, 1);
				}
				me.keyListSet(key, arr);
			}

		// == if 结束
	}


	// ===========================  对sa-admin的优化   ======================================= 
	if (true) {

		// 封装element-ui的导出表格
		// 参数：选择器（默认.data-count），fileName=导出的文件名称
		me.exportExcel = function (select, fileName) {

			// 声明函数 
			let exportExcel_fn = function (select, fileName) {
				// 赋默认值
				select = select || '.data-table';
				fileName = fileName || 'table.xlsx';
				// 开始导出
				let wb = XLSX.utils.table_to_book(document.querySelector(select));   // 这里就是表格
				let sheet = wb.Sheets.Sheet1;	// 单元表 
				try {
					// 强改宽度 
					sheet['!cols'] = sheet['!cols'] || [];
					let thList = document.querySelector(select).querySelectorAll('.el-table__header-wrapper tr th');
					for (var i = 0; i < thList.length; i++) {
						// 如果是多选框
						if (thList[i].querySelector('.el-checkbox')) {
							sheet['!cols'].push({ wch: 5 });	// 强改宽度
							continue;
						}
						sheet['!cols'].push({ wch: 15 });	// 强改宽度
					}
					// 强改高度 
					sheet['!rows'] = sheet['!rows'] || [];
					let trList = document.querySelector(select).querySelectorAll('.el-table__body-wrapper tbody tr');
					for (var i = 0; i < trList.length + 1; i++) {
						sheet['!rows'].push({ hpx: 20 });	// 强改高度 
					}
				} catch (e) {
					console.err(e);
				}
				// 开始制作并输出
				let wbout = XLSX.write(wb, { bookType: 'xlsx', bookSST: true, type: 'array' });
				// 点击 
				let blob = new Blob([wbout], { type: 'application/octet-stream' });
				const a = document.createElement("a")
				a.href = URL.createObjectURL(blob)
				a.download = fileName // 这里填保存成的文件名
				a.click()
				URL.revokeObjectURL(a.href)
				a.remove();
				sa.hideLoading();
			}

			sa.loading('正在导出...');
			// 判断是否首次加载 
			if (window.XLSX) {
				return exportExcel_fn(select, fileName);
			} else {
				me.loadJS('https://unpkg.com/xlsx@0.16.6/dist/xlsx.core.min.js', function () {
					return exportExcel_fn(select, fileName);
				});
			}

		}

		// 刷新表格高度, 请务必在所有表格高度发生变化的地方调用此方法
		me.f5TableHeight = function () {
			if (!window.$) {
				return;
			}
			Vue.nextTick(function () {
				if ($('.el-table.data-table .el-table__body-wrapper table').length == 0) {
					return;
				}
				var _f5Height = function () {
					var height = $('.el-table .el-table__body-wrapper table').height();
					height = height == 0 ? 60 : height;
					// 判断是否有滚动条
					var tw = $('.el-table .el-table__body-wrapper').get(0);
					if (tw.scrollWidth > tw.clientWidth) {
						height = height + 16;
					}
					if ($('.el-table .el-table__body-wrapper table td').width() == 0) {
						return;
					}
					// 设置高度
					$('.el-table .el-table__body-wrapper').css('min-height', height);
					$('.el-table .el-table__body-wrapper').css('max-height', height);
				};

				setTimeout(_f5Height, 0)
				setTimeout(_f5Height, 200)
			})
		}

		// 在表格查询的页面，监听input回车事件，提交查询
		me.onInputEnter = function (app) {
			Vue.nextTick(function () {
				app = app || window.app;
				// document.querySelectorAll('.el-form input').forEach(function(item) {
				// 	item.onkeydown = function(e) {
				// 		var theEvent = e || window.event;
				// 		var code = theEvent.keyCode || theEvent.which || theEvent.charCode;
				// 		if (code == 13) {
				// 			app.p.pageNo = 1;
				// 			app.f5();
				// 		}    
				// 	}
				// })
				document.querySelectorAll('.el-form').forEach(function (item) {
					item.onkeydown = function (e) {
						var theEvent = e || window.event;
						var code = theEvent.keyCode || theEvent.which || theEvent.charCode;
						if (code == 13) {
							var target = e.target || e.srcElement;
							if (target.tagName.toLowerCase() == "input") {
								app.p.pageNo = 1;
								app.f5();
							}
						}
					}
				})
			})
		}

		// 如果value为true，则抛出异常 
		me.check = function (value, errorMsg) {
			if (value === true) {
				throw { type: 'sa-error', msg: errorMsg };
			}
		}

		// 如果value为null，则抛出异常 
		me.checkNull = function (value, errorMsg) {
			if (me.isNull(value)) {
				throw { type: 'sa-error', msg: errorMsg };
			}
		}

		// 监听窗口变动
		if (!window.onresize) {
			window.onresize = function () {
				try {
					me.f5TableHeight();
				} catch (e) {
					// console.log(e);
				}
			}
		}

		// 双击layer标题处全屏
		if (window.$) {
			$(document).on('mousedown', '.layui-layer-title', function (e) {
				// console.log('单击中');
				if (window.layer_title_last_click_time) {
					var cz = new Date().getTime() - window.layer_title_last_click_time;
					if (cz < 250) {
						console.log('双击');
						$(this).parent().find('.layui-layer-max').click();
					}
				}
				window.layer_title_last_click_time = new Date().getTime();
			})
		}

		// == if 结束
	}




})();


// ===========================  $sys 有关当前系统的方法  一般不能复制到别的项目中用  ======================================= 
(function () {

	// 超级对象
	var me = {};
	sa.$sys = me;

	// ======================= 登录相关 ============================
	// 写入当前已登陆用户信息
	me.setCurrUser = function (currUser) {
		localStorage.setItem('currUser', JSON.stringify(currUser));
	}

	// 获得当前已登陆用户信息
	me.getCurrUser = function () {
		var user = localStorage.getItem("currUser");
		if (user == undefined || user == null || user == 'null' || user == '' || user == '{}' || user.length < 10) {
			user = {
				id: '0',
				username: '未登录'
			}
		} else {
			user = JSON.parse(user);
		}
		return user;
	}

	// 如果未登录，则强制跳转到登录 
	me.checkLogin = function (not_login_url) {
		console.log(me.getCurrUser());
		if (me.getCurrUser().id == 0) {
			location.href = not_login_url || '../../login.html';
			throw '未登录，请先登录';
		}
	}

	// 同上, 只不过是以弹窗的形式显示未登录
	me.checkLoginTs = function (not_login_url) {
		if (me.getCurrUser().id == 0) {
			sa.$page.openLogin(not_login_url || '../../login.html');
			throw '未登录，请先登录';
		}
	}


	// ========================= 权限验证 ========================= 

	// 定义key
	var pcode_key = 'permission_code';

	// 写入当前会话的权限码集合
	sa.setAuth = function (codeList) {
		sa.keyListSet(pcode_key, codeList);
	}

	// 清除当前会话的权限码集合 
	sa.clearAuth = function () {
		sa.keyListSet(pcode_key, []);
	}

	// 检查当前会话是否拥有一个权限码, 返回true和false 
	sa.isAuth = function (pcode) {
		return sa.keyListHas(pcode_key, pcode);
	}

	// 检查当前会话是否拥有一个权限码, 如果没有, 则跳转到无权限页面 
	// 注意: 非二级目录页面请注意调整路径问题 
	sa.checkAuth = function (pcode, not_pcode_url) {
		var is_have = sa.keyListHas(pcode_key, pcode);
		if (is_have == false) {
			location.href = not_pcode_url || '../../sa-view/error-page/403.html';
			throw '暂无权限: ' + pcode;
		}
	}
	// 同上, 只不过是以弹窗的形式显示出来无权限来 
	sa.checkAuthTs = function (pcode, not_pcode_url) {
		var is_have = sa.keyListHas(pcode_key, pcode);
		if (is_have == false) {
			var url = not_pcode_url || '../../sa-view/error-page/403.html';
			layer.open({
				type: 2,
				title: false,	// 标题 
				shadeClose: true,	// 是否点击遮罩关闭
				shade: 0.8,		// 遮罩透明度 
				scrollbar: false,	// 屏蔽掉外层的滚动条 
				closeBtn: false,
				area: ['700px', '600px'],	// 大小  
				content: url	// 传值 
			});
			throw '暂无权限: ' + pcode;
		}
	}



	// ======================= 配置相关 ============================
	// 写入配置信息
	me.setAppCfg = function (cfg) {
		if (typeof cfg != 'string') {
			cfg = JSON.stringify(cfg);
		}
		localStorage.setItem('app_cfg', cfg);
	}

	// 获取配置信息
	me.getAppCfg = function () {
		var app_cfg = sa.JSONParse(localStorage.getItem('app_cfg'), {}) || {};
		return app_cfg;
	}




})();


// ===========================  $page 跳页面相关 避免一次变动，到处乱改 ======================================= 
(function () {

	// 超级对象
	var me = {};
	sa.$page = me;

	// 打开登录页面
	me.openLogin = function (login_url) {
		layer.open({
			type: 2,
			// title: '登录',
			title: false,
			closeBtn: false,
			shadeClose: true,
			shade: 0.8,
			// area: ['90%', '100%'],
			area: ['70%', '80%'],
			// area: ['450px', '360px'],
			resize: false,
			content: login_url || '../../login.html'
		});
	}


})();


// 如果是sa_admin环境 
window.sa_admin = window.sa_admin || parent.sa_admin || top.sa_admin;
window.saAdmin = window.sa_admin;

// 如果当前是Vue环境, 则挂在到 Vue 示例
if (window.Vue) {
	// 全局的 sa 对象
	Vue.prototype.sa = window.sa;
	Vue.prototype.sa_admin = window.sa_admin;
	Vue.prototype.saAdmin = window.saAdmin;

	// 表单校验异常捕获 
	Vue.config.errorHandler = function (err, vm) {
		if (err.type == 'sa-error') {
			return sa.error(err.msg);
		}
		throw err;
	}

	// Element-UI 全局组件样式  
	Vue.prototype.$ELEMENT = { size: 'mini', zIndex: 3000 };

	if (typeof ELEMENT !== 'undefined') {
		sa.message = ELEMENT.Message
	}


	Vue.mixin({

		data() {
			return {
				pagination: {
					page: 1,
					limit: 15
				},
				dataList: [],
				dataCount: 0,
				fetching: false,
				getImaging: false,
				images: [],
				videos: [],
				getVideoing: false,

			}
		},
		methods: {
			async fetchTableData() {
				if (this.fetchList) {
					try {
						this.fetching = true
						const res = await this.fetchList()
						this.dataList = [...res.data.list]

						this.pagination.page = res.data.page
						this.dataCount = res.data.total
						this.$nextTick(() => {
							sa.f5TableHeight()
						})
					} catch (error) {
						console.log('error: ', error);
					} finally {
						this.fetching = false
					}
				}
			},
			resetTableData() {
				this.pagination = {
					page: 1,
					limit: 15
				}
				this.resetTableParams?.()

				this.fetchTableData()

			},

			async customUploadImage(file, onUploadProgress) {
				try {
					const data = new FormData()
					data.append('file', file)
					const res = await sa.http({
						url: 'upload/uploadRemote',
						data,
						onUploadProgress
					})

					return res
				} catch (error) {
					return Promise.reject(error)
				}
			},
			// 图片上传相关
			async customUpload(e) {
				try {
					const data = new FormData()
					data.append('file', e.file)
					this.fileList = this.fileList.map(item => {
						if (item.raw === e.file) {
							return {
								...item,
								status: 'uploading',
							}
						}
						return item
					})
					const res = await sa.http({
						url: 'upload/uploadRemote',
						data
					})

					await sa.http({
						url: 'attachment/uploadImage',
						data: {
							cid: 0,
							name: e.file.name,
							image_url: res.data.url,
							image_src: res.data.src
						}
					})
					this.fileList = this.fileList.map(item => {
						if (item.raw === e.file) {
							return {
								...item,
								status: 'success',
								url: res.data.url,
								image_url: res.data.url,
								image_src: res.data.src,
								percentage: 100
							}
						}
						return item
					})
					this.$message({
						type: 'success',
						message: '上传成功'
					});

					this.getImages()

				} catch (error) {
					this.fileList = this.fileList.map(item => {
						if (item.raw === e.file) {
							return {
								...item,
								status: 'fail',
							}
						}
						return item
					})
				}
			},
			async getImages() {

				this.getImaging = true
				try {
					const res = await sa.http({
						url: 'contents/images'
					})
					this.images = res.data.list
				} catch (error) {

				} finally {
					this.getImaging = false
				}

			},
			async getR2uploadUrl() {
				try {
					const res = await sa.http('attachment/r2UploadUrl');
					return res
				} catch (e) {
					return { status: 0, msg: '接口请求失败' };
				}
			},


			async customUploadVideo(file, onProgress) {
				let retryCount = 0;
				const maxRetries = 3;
				let res;

				while (retryCount < maxRetries) {
					res = await this.getR2uploadUrl();
					if (res && res.status === 1) break;
					retryCount++;
					await new Promise(resolve => setTimeout(resolve, 1000));
				}

				if (!res || res.status !== 1) {
					return { status: -1, msg: '获取上传链接失败' };
				}

				const { uploadUrl, UploadName, publicUrl } = res.data;
				const formData = new FormData();
				formData.append('video', file, UploadName);

				try {
					const response = await axios.put(uploadUrl, formData.get('video'), {
						headers: { 'Content-Type': 'video/mp4' },
						onUploadProgress: onProgress ? function (progressEvent) {
							const progress = Math.round((progressEvent.loaded * 100) / (progressEvent.total || 1));
							onProgress(progress);
						} : undefined
					});

					return response.status === 200
						? { status: 1, msg: publicUrl }
						: { status: -1, msg: '上传失败' };
				} catch (e) {
					return { status: -1, msg: e.message || '上传异常' };
				}
			},

			async getVideos() {
				try {
					this.getVideoing = true
					const res = await sa.http({
						url: 'attachment/list'
					})
					this.videos = res.data.list
				} catch (error) {

				} finally {
					this.getVideoing = false
				}
			},
			submitMv(mvInfo) {
				console.log('mvInfo: ', mvInfo);
				return sa.http({
					url: 'attachment/uploadVideo',
					data: {
						...mvInfo,
						cid: this.cid || 0
					}
				})

			}

		},
		created() {
			this.fetchTableData()
		},

	})


	// v-only-en-num：只允许英文 + 数字
	Vue.directive('only-en-num', {
		inserted(el) {
			// el-input 的真实输入框在里面
			const input =
				el.querySelector('input') ||
				el.querySelector('textarea') ||
				el.querySelector('.el-input__inner')

			if (!input) return

			const handler = () => {
				const next = input.value.replace(/[^a-zA-Z0-9]/g, '')
				if (next === input.value) return

				// 尽量保持光标位置
				const start = input.selectionStart
				const end = input.selectionEnd
				input.value = next

				input.setSelectionRange?.(start, end)

				// 触发 v-model 更新（Element UI / Vue2 依赖 input 事件）
				input.dispatchEvent(new Event('input', { bubbles: true }))
			}

			// 保存引用，便于解绑（可选）
			el.__onlyEnNumHandler__ = handler

			input.addEventListener('input', handler)
			input.addEventListener('blur', handler) // 防止某些场景漏掉
		},

		unbind(el) {
			const input =
				el.querySelector('input') ||
				el.querySelector('textarea') ||
				el.querySelector('.el-input__inner')

			if (input && el.__onlyEnNumHandler__) {
				input.removeEventListener('input', el.__onlyEnNumHandler__)
				input.removeEventListener('blur', el.__onlyEnNumHandler__)
			}
			delete el.__onlyEnNumHandler__
		}
	})


	// 加载全局组件 (注意路径问题)
	// if(window.httpVueLoader && window.loadComponent !== false) {
	// 	Vue.component("sa-item", httpVueLoader('../../sa-frame/com/sa-item.vue'));
	// 	Vue.component("sa-td", httpVueLoader('../../sa-frame/com/sa-td.vue'));
	// }

}

// 对外开放, 在模块化时解开此注释 
// export default sa;

(function () {
	if (!sa.get_token() && typeof isLogin === 'undefined') {
		self.location.href = '/login.html'
	}
})();

