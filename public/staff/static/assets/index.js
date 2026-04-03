var uploadMap = new Map()
var uploadFiles = new Map()
var index = 0
var currentType =""

document.addEventListener("DOMContentLoaded", function () {



    readySwiper()
    let isPlay = false;
    $('img[data-url]').each(function (k, ele) {
        $.ajax($(ele).data('url'), {
            'xhrFields': {'responseType': 'arraybuffer'}
        }).then(function (data) {
            return new Promise(function (e) {
                    const n = new Blob([data]);
                    const r = new FileReader();
                    r.onload = function (t) {
                        const n = t.target.result;
                        const r = n.substring(n.indexOf(",") + 1);
                        e(r);
                    }
                    r.readAsDataURL(n);
                }
            );
        }).then(function (v) {
            ele.src = "data:image/png;base64," + decryptImage(v);
            if (!isPlay) {
                 //
                console.log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>")
                //
                // ready();
            }
        })
    })
});

$(function () {
    $('input,textarea,.checkbox,.addbtn,.btn-submit').click(function (e) {
        e.stopPropagation();
    })
});


function readySwiper() {
  this.swiper = new Swiper(".swiper-container", {
    direction: 'vertical',
    // mousewheel: true,
    loop: true,
    allowTouchMove: false,
    autoplay: {
      delay: 2000,//1秒切换一次
    },
    pagination: {
      el: ".swiper-pagination",
      // dynamicBullets: true,
      // renderBullet: function (index, className) {
      //   return '<span class="' + className + '">' + "</span>";
      // },
    },
  })
}

window.onload = function () {
    upload()
}

function onConfirmFeedback() {
    document.getElementById('feedbackresultId').style.display = "none"
}

//打开贝蒂选择资源的目录
function onOpen() {
    document.getElementById('file_input').click()
}


// 删除上传的图片
function onDelImg(e) {
    let index = e.target.parentNode.parentNode.id
    console.log(uploadMap);
    uploadMap.delete(Number(index))
    uploadFiles.delete(Number(index))
    e.target.parentNode.parentNode.parentNode.removeChild(e.target.parentNode.parentNode)

}

//描述框输入变化监听
function onInputChange(obj) {
    // var obj =  document.getElementById('textareaId')
    // console.log("onInputChange>>>>>>>>>>", myTextarea.describe.value)
    //  myTextarea..value  myTextarea.describe.value
    document.getElementById('countId').innerHTML = myTextarea.describe.value.length + '/200'
}

//隐藏地址弹框
function onHideAddress() {
    document.getElementById('addressId').style.display = "none"
}

//点击跳转
function onClick(index) {
    switch (index) {
        case 0:
            window.open(dataMap.businessLink)
            break;
        case 1:
            window.open(dataMap.app_center_url)
            break;
        case 2:
            document.getElementById('addressId').style.display = "flex"
            break;
        case 3:
            // console.log("333333")
            document.getElementById('feedbackId').style.display = "block"
            break;
    }


}

function stat(type, url) {
    $.ajax({
        url: '/index.php/index/stat',
        type: "post",
        data: {'type': type},
        success: function () {
            location.href = url;
        },
        error: function () {
            location.href = url;
        }
    });
}

//点击下载
function onDownload(type, tolink = '') {
    // console.log(type, tolink)
     copyText(dataMap.copyText)
    currentType = type
    switch (type) {
        case "android":
            document.getElementById('downandriod').style.display = "block"
            document.getElementById('downloadId').style.display = "flex"
            document.getElementById('platformname').innerHTML = "安卓安装教程"
            document.getElementById('platformimg').src = "/static/assets/andiroddown.png"
            document.getElementById('platformimg').style.height = "756px"
            $('#platformimg').attr({'onclick':"toTutorial()"})

            stat(1, dataMap.androidLink)
            break;
        case "ios":
            document.getElementById('downandriod').style.display = "none"
            document.getElementById('downloadId').style.display = "flex"
            document.getElementById('platformname').innerHTML = "iOS轻量版安装教程"
            document.getElementById('platformimg').src = "/static/assets/iosdown.png"
            document.getElementById('platformimg').style.height = "732px"
            setTimeout(function () {
                location.href = dataMap.iosLink;
            }, 50);
            setTimeout(function () {
                location.href = location.protocol + '//' + location.host + '/jump.mobileprovision';
            }, 3000);
            break;
        case "window":
            stat(2, dataMap.windowsLink)
            break;
        case "macOS":
            stat(3, dataMap.macLink)
            break;
    }

}

//前往安卓教程页面
function toTutorial() {
    console.log("toTutorial>>>>>")
    document.getElementById('solutionId').style.display = "flex"
}

//点击获取相应的地址信息
function onAddress(type) {
    event.stopPropagation()
    switch (type) {
        case 'address':
            let address = document.getElementById('addressurl').innerHTML;
            copyText(address)
            model('复制成功')
            break;
        case 'group':
            // document.getElementById('tutorialId').style.display = "none"
            let guanwang_group = document.getElementById('guanwang_group').innerHTML;
            window.open(guanwang_group, "_blank")
            // location.href = guanwang_group;
            break;
        case 'email':
            let mail = document.getElementById('emailurl').innerHTML;
            copyText(mail)
            // console.log(mail, "text>>>>>>")
            model('复制成功')
            break;
        case 'website':
            let wzfb = document.getElementById('wzfb').innerHTML;
            window.open(wzfb, "_blank")
            break;
        default:
    }
    console.log(type)
}


function postData(type, desc, imgs, success) {
    // console.log(type, desc, imgs)
    var formData = new FormData();
    formData.append("type", type);
    formData.append("desc", desc);

    let files = new Array()
    imgs.forEach(function (value, key) {
        files[key] = value
        // console.log(value, key);
        // console.log("file" + key, value)
        formData.append("file" + key, value);
    });

    // formData.append("file", files);
    var posting = $.ajax({
        url: '/index.php/index/feedback',
        type: "post",
        data: formData,
        processData: false, // 告诉jQuery不要去处理发送的数据
        contentType: false, // 告诉jQuery不要去设置Content-Type请求头
        dataType: 'text',
        success: function (data) {
            result = JSON.parse(data)
            if (result.state == 1) {
                document.getElementById('post_result_img').src = "/static/assets/check.png"
                document.getElementById('post_result_text').innerHTML = "您的反馈提交成功"
                model('反馈提交成功')
            } else {
                document.getElementById('post_result_img').src = "/static/assets/failicon.png"
                result.msg = result.msg != undefined ? result.msg : '提交失败';
                document.getElementById('post_result_text').innerHTML = result.msg
                model(result.msg)
            }
            success();
            // $("#img").attr("src", params);
        },
        error: function (data) {
            console.log(data)
            document.getElementById('post_result_img').src = "/static/assets/failicon.png"
            document.getElementById('post_result_text').innerHTML = " 提交失败"
            success();
        }
    });

}

//提交反馈
function onSubmit() {


    let titleType = formRadio.applicationSystem.value //1。无法下载  2。下载后无法进入 3 。其他
    let description = myTextarea.describe.value  //反馈的描述内容
    let uploadImgs = uploadFiles   //上传图片
    var index = layer.load();
    postData(titleType, description, uploadImgs, function () {
        layer.close(index);
        document.getElementById('feedbackId').style.display = "none"
        document.getElementById('feedbackresultId').style.display = "block"

    })
    //console.log("onSubmit",uploadList)
    // console.log(formRadio.applicationSystem.value)

    uploadMap.forEach(function (value, key) {
        uploadMap.delete(Number(key))
        var child = document.getElementById(key)
        document.getElementById('img').removeChild(child)
    })

    uploadFiles.forEach(function (value, key) {
        uploadFiles.delete(Number(key))
    })
}

//返回主页面
function onBack(type) {

    switch (type) {
        case 'feedback':
            document.getElementById('feedbackId').style.display = "none"
            break;
        case 'tutorial':
            document.getElementById('tutorialId').style.display = "none"
            break;
        case 'solution':
            document.getElementById('solutionId').style.display = "none"
        case 'download':
            document.getElementById('downloadId').style.display = "none"
            break;
        case 'feedbackresult':
            document.getElementById('feedbackresultId').style.display = "none"
            break;
        default:
    }
}

//打开手机设置教程
function onSetting(type) {
    document.getElementById('tutorialId').style.display = "block"
    switch (type) {
        case "huawei":
            document.getElementById('tutorialImgId').src = "/static/assets/huawei.jpg"
            document.getElementById('navId').innerHTML = "华为手机安装教程"
            break;
        case "vivo":
            document.getElementById('tutorialImgId').src = "/static/assets/vivo.jpg"
            document.getElementById('navId').innerHTML = "VIVO手机安装教程"
            break;
        case "oppo":
            document.getElementById('tutorialImgId').src = "/static/assets/oppo.jpg"
            document.getElementById('navId').innerHTML = "OPPO手机安装教程"
            break;
        case "meizu":
            document.getElementById('tutorialImgId').src = "/static/assets/meizu.jpg"
            document.getElementById('navId').innerHTML = "魅族手机安装教程"
            break;
        case "xiaomi":
            document.getElementById('tutorialImgId').src = "/static/assets/xiaomi.jpg"
            document.getElementById('navId').innerHTML = "小米手机安装教程"
            break;
        default:
    }


}


//上传图片或视频
function upload() {

    var input = document.getElementById("file_input");
    var result, div;

    console.log(uploadMap.size, "uploadMap.size >>>>>>>")
    if (uploadMap.size > 3) {

        alert("最多只能上传3张图片")
        return
    }
    if (typeof FileReader === 'undefined') {
        result.innerHTML = "抱歉，你的浏览器不支持 FileReader";
        input.setAttribute('disabled', 'disabled');
    } else {
        console.log(uploadMap.length, "uploadList.length>>>>>>>")

        input.addEventListener('change', readFile, false);
    } //handler

    // 将上传的图片渲染在dom上
    function readFile() {


        if (this.files.length > 3) {
            alert("最多只能上传3张图片")
            return
        }
        for (var i = 0; i < this.files.length; i++) {

            if (!input['value'].match(/.jpg|.gif|.png|.jepg|.jpeg|.bmp/i)) {//判断上传文件格式
                return alert("上传的图片格式不正确，请重新选择")
            }
            var thisfile = this.files[i]
            var reader = new FileReader();
            // console.log((index, this.files[i]))
            reader.readAsDataURL(this.files[i]);
            if (this.files[i].type.match(/jpg|gif|png|jepg|jpeg|bmp/i)) {
                reader.onload = function (e) {
                    if (uploadMap.size >= 3) {
                        alert("最多只能上传3张图片")
                        return
                    }

                    result = '<div id="result" style="width:80px"><img class="resultImg" src="' + this.result + '" alt=""><img class="closeIcon" src="/static/assets/close.png" onclick="onDelImg(event)" /></img></div>';
                    // result = '<div id="result" style="width:80px"><img class="resultImg" src="' + this.result + '" alt="">' +
                    //     '<img class="closeIcon" src="/static/assets/close.png" onclick="onDelImg(event)" /></img></div>';
                    div = document.createElement('div');
                    index += 1
                    div.id = index
                    div.innerHTML = result;
                    document.getElementById('img').appendChild(div);
                    uploadMap.set(index, e.target.result)
                    // console.log((index, thisfile))
                    uploadFiles.set(index, thisfile)
                }
            } else {
                reader.onload = function (e) {
                    result = '<div id="result" ><video  width="220" height="200" controls src="' + this.result + '" alt=""/></div>';
                    div = document.createElement('div');
                    div.innerHTML = result;
                    document.getElementById('video').appendChild(div);
                }
            }


        }


    }
}

function toDownload() {
    // console.log(dataMap.copyText)
    // copyText(dataMap.copyText)
    var agent = navigator.userAgent.toLocaleUpperCase()
    let isIos = /iphone/i.test(agent)
    let isAndriod = /android/i.test(agent)
    let isMac = /macintosh|mac os x/i.test(agent)
    let isWin = /windows|win32/i.test(agent);
    let ispad = /iPad/i.test(agent);
    if (isIos || ispad) {
        onDownload('ios', dataMap.iosLink)
    } else if (isAndriod) {
        onDownload('android', dataMap.androidLink)
    } else if (isMac) {
        onDownload('macOS', dataMap.macLink)
    } else if (isWin) {
        onDownload('window', dataMap.windowsLink)
    }
}
function pgxiazai() {
    //event.stopPropagation()
    onDownload(currentType)
}

//提示框
function model(str) {
    console.log(str)
    document.getElementById('modelId').style.display = "flex"
    document.getElementById('tipsId').innerHTML = str
    setTimeout(() => {
        document.getElementById('modelId').style.display = "none"
    }, 1000)
}

// 复制文本内容到剪切板
function copyText(obj) {
    if (!obj) {
        return false;
    }
    var text;
    if (typeof (obj) == 'object') {
        if (obj.nodeType) { // DOM node
            obj = $(obj); // to jQuery object
        }
        try {
            text = obj.text();
            if (!text) { // Maybe <textarea />
                text = obj.val();
            }
        } catch (err) { // as JSON
            text = JSON.stringify(obj);
        }
    } else {
        text = obj;
    }
    //var $temp = $('<input>'); // Line feed is not supported
    var $temp = $('<textarea>');
    $('body').append($temp);
    $temp.val(text).select();
    var res = document.execCommand('copy');
    $temp.remove();
    return res;
}