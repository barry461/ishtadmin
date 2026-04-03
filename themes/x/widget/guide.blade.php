<style>
    .addbox{position:fixed;width:100%;background:rgba(255,255,255,0.8);bottom:0;left:0;z-index:999;box-shadow:rgba(0,0,0,0.56) 0 -3px 18px 4px;display:none;}.addbox > div{background:#2c2a2a;padding:.6rem 2rem .6rem .6rem;color:white;display:flex;flex-direction:row;justify-content:space-between;align-items:center;float:left;width:100%;box-sizing:border-box;}.addbox > div .logo{height:1.5rem;margin-left:.4rem;border-radius:7px;}.addbox > div .showSwipe{height:1.5rem;}.addbox > div .closeX{position:absolute;top:.5rem;right:.9rem;font-size:.7rem;}.closebox .closeDilog .closeG{width:1rem;height:1rem;position:relative;}.addbox > div .closeX:before,.addbox > div .closeX:after{position:absolute;top:1px;right:1px;content:' ';height:15px;width:2px;background-color:white;}.closebox .closeDilog .closeG:before,.closebox .closeDilog .closeG:after{position:absolute;top:0;content:' ';height:17px;width:1px;background-color:white;}.addbox > div .closeX:before,.closebox .closeDilog .closeG:before{transform:rotate(45deg);}.addbox > div .closeX:after,.closebox .closeDilog .closeG:after{transform:rotate(-45deg);}.van-overlay{position:fixed;display:none;left:0;top:0;z-index:999;width:100%;height:100%;background-color:rgba(0,0,0,.7);align-items:center;justify-content:center;}.bg-layout{position:relative;width:90%;height:60%;display:block;max-width:420px;margin-left:auto;margin-right:auto;}.box{width:100%;height:48vh;border-radius:1.2rem;background-color:rgb(43,42,42);}.box2{margin-left:10%;width:80%;height:100%;}.box2 img{width:100%;height:auto;margin-top:2rem;}.closebox{display:flex;flex-direction:row;width:100%;height:20%;justify-content:center;align-items:center;text-align:center;}.closeDilog{width:2.4rem;height:2.4rem;text-align:center;background-color:rgba(43,42,42,1);border-radius:1.2rem;color:white;display:flex;align-items:center;justify-content:center;}.swiper-pagination{display:flex;align-items:center;justify-content:center;margin-top:0.8rem;}.swiper-pagination-bullet{display:block !important;box-sizing:border-box;}.swiper-pagination-bullet-active{background-color:rgb(255,35,116) !important;background:rgb(255,35,116);}.swiper-slide p{text-align:center;margin:10px 0;}.swiper-slide span{display:block;font-size:12px;color:grey;text-align:center;}.swiper-pagination{position:initial !important;padding:5px 0;}
</style>
<div class="addbox">
    <div>
        {!! theme()->importImg(options('logo_url'), register('site.app_name'), ['class'=>"logo"]) !!}
        {!! html()->emmet("span{添加%s到主屏幕}>strong{【{$config['app_name']}】}") !!}
        {!! theme()->importImg('/usr/themes/Mirages/images/addbtn.png', "添加到主屏幕 - 快速打开".register('site.app_name'), ['class'=>"showSwipe"]) !!}
        <i class="closeX"></i>
    </div>
</div>
<div class="van-overlay">
    <div class="bg-layout">
        <div class="box">
            <div class="box2">
                <div class="swiper swiperios">
                    <div class="swiper-wrapper">
                        {!! $swiperImagesIos !!}
                    </div>
                    <!-- 如果需要分页器 -->
                    <div class="swiper-pagination"></div>
                    <!-- 如果需要滚动条 -->
                </div>
                <div class="swiper swiperand">
                    <div class="swiper-wrapper">
                        {!! $swiperImagesAnd !!}
                    </div>
                    <!-- 如果需要分页器 -->
                    <div class="swiper-pagination"></div>
                    <!-- 如果需要滚动条 -->
                </div>
            </div>
        </div>
        <div class="closebox">
            <div class="closeDilog"><i class="closeG"></i></div>
        </div>
    </div>
</div>
{!! theme()->importCss('/usr/themes/Mirages/css/7.10.0/swiper-bundle.min.css' , ['v'=>1]) !!}
{!! theme()->importJs('/usr/themes/Mirages/js/7.10.0/swiper-bundle.min.js' , ['v'=>1]) !!}
<script>
    $(document).ready(function () {

        let today = (new Date()).toLocaleDateString();
        let status_day = sessionStorage.getItem("51cg_guide_guide");
        console.log(`today ${today}  status_day ${status_day}`)
        if (today !== status_day) {
            $('.addbox').show();
        }
        $(".closeX").click(function () {
            $(".addbox").hide();
            console.log(`set 51cg_guide_guide : ${today}`)
            sessionStorage.setItem("51cg_guide_guide", today);
        });
        $(".showSwipe").click(function () {
            $(".van-overlay").css('display', 'flex');
        });
        $(".van-overlay").on("click", function (e) {
            if ($(e.target).closest(".van-overlay .box").length > 0) {
            } else {
                $(".van-overlay").hide();
            }
        });
        if (judgeClient() == "IOS") {
            $(".swiperand").hide();
        } else if (judgeClient() == "Android") {
            $(".swiperios").hide();
        } else {
            $(".addbox").hide();
        }
        var mySwiper = new Swiper('.swiper', {
            direction: 'horizontal', // 垂直切换选项
            loop: true, // 循环模式选项
            // 如果需要分页器
            pagination: {
                el: '.swiper-pagination',
            },
            // 如果需要前进后退按钮
            navigation: {},
            // 如果需要滚动条
            scrollbar: {
                el: '.swiper-scrollbar',
            },
        })

        function judgeClient() {
            let u = navigator.userAgent;
            let isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1;   //判断是否是 android终端
            let isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);     //判断是否是 iOS终端
            if (isAndroid) {
                return 'Android';
            } else if (isIOS) {
                return 'IOS';
            } else {
                return 'PC';
            }
        }
    })
</script>