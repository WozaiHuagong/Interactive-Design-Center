$(document).ready(function () {
    var globalMainPageInfo = -1; //未点击主页面的资讯中心的内容

    //首页轮播图启动
    $('.carousel.carousel-slider').carousel({
        full_width: true
    });
    //轮播图启动结束
    //右边的滚动监视条触发
    $('.scrollspy').scrollSpy();
    $('.scroll-spy').pushpin({
        top: 0, //滚动多少页面后，就是隐藏的页面有多高后自动固定
        bottom: 2000,
        offset: 200 //距离页面顶部多少距离时固定
    });
    //右边滚动监视条结束

    function getText(htmlContent) {
        var re = new RegExp(/(<("[^"]*"|'[^']*'|[^'">])*>|&nbsp;)/, 'g'); //匹配出所有标签和内容
        var result = htmlContent.replace(re, "");
        return result;
    }
    //html标签转义
    function HTMLDecode(text) {
        var temp = document.createElement("div");
        temp.innerHTML = text;
        var output = temp.innerText || temp.textContent; //如果能顺利转就取标签加内容，不能就直接取内容
        temp = null;
        output = output.replace(/&quot;/g, '"');
        output = output.replace(/&ldquo;/g, '');
        output = output.replace(/&rdquo;/g, '');
        return output;
    }
    //各个页面右上角蓝色的功能切换
    var infoCenterBodyHeaderChoices = function (eventNode) {
        $(eventNode).attr('style', 'color:rgb(8,147,214);')
    }
    var infoCenterBodyHeaderRemoveChoices = function (eventNode) {
        $(eventNode).siblings().removeAttr('style');
    }
    //获取当前页面的URL，用正则表达式匹配，决定那个导航的地方有下边的蓝线
    var determineTheBlueBorder = function () {
        $url = window.location.href;
        $mainPage = $url.match('index');
        if ($mainPage != null) {
            //轮播图函数
            var sliderCounter = 0;
            // $(function(){
            // });
            function sliderChange() {
                var leftIn = -sliderCounter * 956;
                var styleLeft = leftIn + "px";
                $('#main-page-id .personal-slider .slider-wrap').animate({
                    left: styleLeft
                }, 500);
                $('#main-page-id .personal-slider .slider-dots ul li i').attr('class', '');
                $('#main-page-id .personal-slider .slider-dots ul li').eq(sliderCounter).find('i').attr('class', 'active');

                $('#carousel-content-text-title-id').text(ajaxResponseOfCarousel.data[sliderCounter].title);
                $('#carousel-content-text-content-id').text(ajaxResponseOfCarousel.data[sliderCounter].content);
            }
            $('#main-page-id .personal-slider  .slider-direction-next').click(function () {
                sliderCounter++;
                sliderCounter %= 4;
                sliderChange();
            });
            $('#main-page-id .personal-slider  .slider-direction-prev').click(function () {
                if (sliderCounter >= 1) {
                    sliderCounter--;
                    sliderCounter %= 4;
                    sliderChange();
                } else {
                    sliderCounter = 3;
                    sliderChange();
                }

            });
            $('#main-page-id .personal-slider .slider-dots ul li').click(function () {
                var index = $('#main-page-id .personal-slider .slider-dots ul li').index($(this));
                sliderCounter = index;
                sliderChange();
            });
            $('#main-page-id .slider-wrap').click(function () {
                sliderCounter++;
                sliderCounter %= 4;
                sliderChange();
            })


            //轮播图结束
            $('#common-nav-mainpage').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
            //根据当前URL做出相关的ajax请求与更新，更新轮播图
            var ajaxResponseOfCarousel = "";
            //$('.carousel-item').click(function(){});//点击事
            //    $('#main-page-right-nav-id li a').click(function(){

            $.ajax({
                url: "http://39.108.87.54/api/carousel/list/",
                method: 'GET',
                dataType: 'JSON',
                contentType: 'application/x-www-form-urlencoded',
                //data追加到URL
                success: function (response) {
                    //console.log(response);
                    ajaxResponseOfCarousel = response;
                    var data = response.data; //轮播图内容
                    var arrLength = data.length;
                    //更新轮播图
                    for (var i = 0; i < 4; i++) {
                        $('.slider-block').eq(i).attr('style', 'background-image: url("http://39.108.87.54' + data[i].url + '");');
                        $('.slider-block').eq(i).attr('id', 'carousel-id' + i);
                        if (i == 0) {
                            $('#carousel-content-text-title-id').text(data[0].title);
                            $('#carousel-content-text-content-id').text(data[0].content);
                        }
                    }
                    //轮播图更新完毕
                },
                error: function () {
                    console.log('carousel get fail!');
                    //alert('it me')
                }
            }); //轮播图更新完毕

            //更新主页资讯中心内容
            var infoajax = "";

            $.ajax({
                url: "http://39.108.87.54/api/advisory/libInfo/number",
                method: 'GET',
                //data:{number:0},
                // data:'3',
                contentType: 'application/x-www-form-urlencoded',
                dataType: 'JSON',
                success: function (response) {
                    response = response.data.sort(function (a, b) {
                        return (b.id - a.id);
                    })
                    var data = response;
                    infoajax = response;
                    //  console.log(infoajax[0]);
                    $.cookie('globalMainPageInfo', -1);
                    $('#info-center-id .main-page-info-center-card .row a').click(function () {
                        var index = $('#info-center-id .main-page-info-center-card .row a').index($(this));
                        //alert(index);
                        switch (index) {
                            case 0:
                                //alert(infoajax[0]);
                                //console.log(infoajax[0])
                                globalMainPageInfo = 0;
                                // var st=JSON.stringify(infoajax[0]);
                                //console.log(st);
                                $.cookie('globalMainPageInfo', '0', {
                                    path: '/'
                                });
                                window.location.href = "../html/infomationCenter/infomationCenterDetail.html";
                                // $.cookie('content',st);
                                break;
                            case 1:
                                globalMainPageInfo = 1;
                                $.cookie('globalMainPageInfo', '1', {
                                    path: '/'
                                });
                                window.location.href = "../html/infomationCenter/infomationCenterDetail.html";
                                break;
                                // 
                            case 2:
                                globalMainPageInfo = 2;
                                $.cookie('globalMainPageInfo', '2', {
                                    path: '/'
                                });
                                window.location.href = "../html/infomationCenter/infomationCenterDetail.html";
                                break;
                                //
                        }
                    })
                    var dataLength = data.length;
                    var $title = $('.main-page-info-center-card .row h2');
                    var $p = $('.main-page-info-center-card .row p');
                    var $spanPublishTime = $('.main-page-info-center-card .row span');
                    for (var i = 0; i < (dataLength >= 3 ? 3 : dataLength); i++) {
                        if (data[i].title.length > 27) {
                            $title.eq(i).text(data[i].title.substr(0, 26) + "...");

                        } else {
                            $title.eq(i).text(data[i].title);
                        }

                        if (data[i].summary.length > 70) {
                            $p.eq(i).text(data[i].summary.substring(0, 70) + "...");
                        } else {
                            $p.eq(i).text(data[i].summary);
                        } //出现省略号的判断
                        $spanPublishTime.eq(i).text(data[i].publish_time.substr(0, 10));
                    }

                },
                error: function () {
                    console.log('main-page-info-center-infomation-get-fail!');
                }

            });
            //主页资讯中心内容更新完毕
            //研究项目具体内容更换开始；
            var researchajax = "";
            $.ajax({
                url: "http://39.108.87.54/api/project/working/",
                method: 'GET',
                contentType: 'application/x-www-form-urlencoded',
                dataType: 'JSON',
                success: function (response) {
                    response = response.data.sort(function (a, b) {
                        return b.id - a.id;
                    });
                    researchajax = response;
                    var data = response;
                    $.cookie('globalMainPageResearch', '-1', {
                        path: '/'
                    });
                    $('#research-project-id .main-page-research-project-card .row').click(function () {
                        var index = parseInt($('#research-project-id .main-page-research-project-card .row').index($(this)));
                        switch (index) {
                            case 0:
                                $.cookie('globalMainPageResearch', '0', {
                                    path: '/'
                                });
                                window.location.href = "../html/researchProject/researchProjectDetail.html";
                                break;
                            case 1:
                                $.cookie('globalMainPageResearch', '1', {
                                    path: '/'
                                });
                                window.location.href = "../html/researchProject/researchProjectDetail.html";
                                break;
                            default:
                                console.log("the switch in the reseach main page is wrong!!!")
                        }

                    })
                    // var attach = response.attachs; //附件图片内容
                    var dataLength = data.length;
                    var $image = $('.main-page-research-project-card .row .col .card .card-image');
                    var $title = $('.main-page-research-project-card .row .col .card .card-content .card-article-title');
                    var $summary = $('.main-page-research-project-card .row .col .card .card-content p'); //图片内容简介
                    var $publish_time = $('.main-page-research-project-card .row .col .card .card-content .publish-time');
                    for (var i = 0; i < (dataLength > 2 ? 2 : dataLength); i++) {

                        $image.eq(i).attr('style', 'background-image:url("' + data[i].attach[0] + '");') //更新项目图片
                        $title.eq(i).text(data[i].title);
                        if (getText(HTMLDecode(data[i].message)).length > 57) {
                            $summary.eq(i).text(getText(HTMLDecode(data[i].message)).substring(0, 57) + "...");
                        } else {
                            $summary.eq(i).text(getText(HTMLDecode(data[i].message))); //短就直接显示，太长就直接截断
                        }

                        $publish_time.eq(i).text(data[i].publish_time.substr(0, 10));
                    }
                },
                error: function () {
                    console.log('get-research-project-fail!')
                }
            });
            //资讯中心内容更换结束
            //行业精英资讯内容更换开始
            var eliteajax = "";
            $.ajax({
                url: "http://39.108.87.54/api/elite/list/",
                method: 'GET',
                contentType: 'application/x-www-form-urlencoded',
                dataType: 'JSON',
                success: function (response) {
                    response = response.data.sort(function (a, b) {
                        return b.id - a.id
                    });
                    eliteajax = response;
                    var data = response;
                    $.cookie('globalMainPageElite', '-1', {
                        path: '/'
                    });
                    $('#industry-elite-id .main-page-industry-elite-card .row').click(function () {
                        var index = parseInt($('#industry-elite-id .main-page-industry-elite-card .row').index($(this)));
                        switch (index) {
                            case 0:
                                $.cookie('globalMainPageElite', '0', {
                                    path: '/'
                                });
                                window.location.href = "../html/industryElite/industryEliteDetail.html";
                                break;
                            case 1:
                                $.cookie('globalMainPageElite', '1', {
                                    path: '/'
                                });
                                window.location.href = "../html/industryElite/industryEliteDetail.html";
                                break;
                            case 2:
                                $.cookie('globalMainPageElite', '2', {
                                    path: '/'
                                });
                                window.location.href = "../html/industryElite/industryEliteDetail.html";
                                break;
                            case 3:
                                $.cookie('globalMainPageElite', '3', {
                                    path: '/'
                                });
                                window.location.href = "../html/industryElite/industryEliteDetail.html";
                                break;
                            default:
                                console.log('error in the main page Elite switch');
                        }
                    })
                    var dataLength = data.length;
                    var $image = $('.main-page-industry-elite-card .row .col .card .card-image');
                    var $name = $('.main-page-industry-elite-card .row .col .card .card-stacked .card-content .name');

                    var $position = $('.main-page-industry-elite-card .row .col .card .card-stacked .card-content .position');
                    var $introduction = $('.main-page-industry-elite-card .row .col .card .card-stacked .card-content p'); //这是行业精英的描述
                    for (var i = 0; i < (dataLength > 4 ? 4 : dataLength); i++) {
                        $image.eq(i).attr('style', 'background-image: url("' + data[i].avatar + '")'); //更改行业精英的头像
                        $name.eq(i).text(data[i].title); //获取行业精英的名字
                        $position.eq(i).text(data[i].honor); //获取头衔
                        if (getText(HTMLDecode(data[i].message)).length > 48) {
                            $introduction.eq(i).text(getText(HTMLDecode(data[i].message)).substring(0, 48) + "...");
                        } else {
                            $introduction.eq(i).text(getText(HTMLDecode(data[i].message)));
                        }
                        //缺个人介绍
                        //正则表达式匹配html标签
                    }
                },
                error: function () {
                    console.log('get industry elite fail!');
                }
            })
            //行业精英内容更换结束
            //首页查看详情页==========================================================================
            $('#main-page-look-in-detail-id').click(function () {
                var title = $('#carousel-content-text-title-id').text();
                var flag = false;
                if (!flag) {
                    for (var i = 0; i < infoajax.length; i++) {
                        if (infoajax[i].title == title) {
                            $.cookie('globalMainPageInfo', i, {
                                path: '/'
                            });
                            flag = true;
                            window.location.href = "../html/infomationCenter/infomationCenterDetail.html";
                            break;
                        }
                    }
                }
                if (!flag) {
                    for (var i = 0; i < researchajax.length; i++) {
                        if (researchajax[i].title == title) {
                            $.cookie('globalMainPageResearch', i, {
                                path: '/'
                            });
                            window.location.href = "../html/researchProject/researchProjectDetail.html";
                            flag = true;
                            break;
                        }
                    }
                }
                if (!flag) {
                    for (var i = 0; i < eliteajax.length; i++) {
                        if (eliteajax[i].title == title) {
                            $.cookie('globalMainPageElite', i, {
                                path: '/'
                            });
                            window.location.href = "../html/industryElite/industryEliteDetail.html";
                            flag = true;
                            break;
                        }
                    }
                }

            })
        }
        /*===================================================================================================================*/
        /*======资讯中心模块==================================================================================================*/
        /*===================================================================================================================*/
        /*===================================================================================================================*/
        $infoCenter = $url.match('infomationCenterDetail')
        if ($infoCenter != null) {
            //alert(window.location.href)
            //alert(window.location.search);
            //alert($.cookie('globalMainPageInfo'));
            //alert('do it')
            $('#common-nav-info-center').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
            var ajaxResponse = "";
            $.ajax({
                url: 'http://39.108.87.54/api/advisory/libInfo/number',
                method: 'GET',
                //async:false,
                dataType: 'JSON',
                contentType: 'application/x-www-form-urlencoded',
                //data追加到URL
                success: function (response) {
                    response = response.data.sort(function (a, b) {
                        return b.id - a.id;
                    });
                    ajaxResponse = response; //提取所有返回的数据
                    var data = response;
                    var attach = response.attachs;
                    var dataLength = data.length;
                    //==========================================================================
                    if (parseInt($.cookie('globalMainPageInfo')) != -1) {
                        console.log($.cookie('globalMainPageInfo'))
                        // alert(JSON.parse($.cookie('content')));
                        var indexOfArticle = parseInt($.cookie('globalMainPageInfo'));
                        $.cookie('globalMainPageInfo', "-1", {
                            path: '/'
                        });
                        //alert(indexOfArticle);
                        $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[indexOfArticle].title);
                        $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[indexOfArticle].publish_time);
                        $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[indexOfArticle].view_num + "次阅读");
                        // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                        $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[indexOfArticle].summary);
                        $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                        //文章内容已经更新完毕
                        for (var i = 0; i < 5; i++) {
                            $('#info-center-detail-page-id .right-section ul li').eq(i).text(ajaxResponse[i].title);
                        };
                        //右方最新资讯内容更新
                        $('#info-center-detail-page-id .right-section ul li').click(function () {

                            for (var i = 0; i < ajaxResponse.length; i++) {
                                if (ajaxResponse[i].title == $(this).text()) {
                                    $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[i].title);
                                    $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[i].publish_time);
                                    $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[i].view_num + "次阅读");
                                    // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                                    $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[i].summary);
                                    $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[i].message));
                                    break;
                                }
                            }
                        });
                        //更新下方的上一篇与下一篇
                        if (indexOfArticle > 0 && indexOfArticle < ajaxResponse.length - 1) {
                            $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                            $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                        } else if (indexOfArticle == 0) {
                            $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                            $('#info-center-detail-page-id .left-section .pre-article').text('');
                        } else {
                            $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                            $('#info-center-detail-page-id .left-section .next-article').text('');
                        } //上下篇的问题解决
                        $('#info-center-detail-page-id').show()
                        $('#info-center-article-content-research-room').hide(); //研究中心资讯隐藏
                        $('#info-center-article-content-industry').hide(); //行业资讯隐藏
                        $('#info-center-pagination-id').hide(); //分页条隐藏
                        //点击下方的上一篇与下一篇的事件
                        $('#info-center-detail-page-id .left-section .pre-article').click(function () {
                            indexOfArticle -= 1;
                            $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[indexOfArticle].title);
                            $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[indexOfArticle].publish_time);
                            $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[indexOfArticle].view_num + "次阅读");
                            // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                            $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[indexOfArticle].summary);
                            $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                            //文章内容已经更新完毕 
                            if (indexOfArticle > 0 && indexOfArticle < ajaxResponse.length - 1) {
                                $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                                $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                            } else if (indexOfArticle == 0) {
                                $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                                $('#info-center-detail-page-id .left-section .pre-article').text('');
                            } else {
                                $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                                $('#info-center-detail-page-id .left-section .next-article').text('');
                            } //上下篇的问题解决
                        });
                        //点击下方下一篇的事件
                        $('#info-center-detail-page-id .left-section .next-article').click(function () {
                            indexOfArticle += 1;
                            $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[indexOfArticle].title);
                            $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[indexOfArticle].publish_time);
                            $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[indexOfArticle].view_num + "次阅读");
                            // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                            $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[indexOfArticle].summary);
                            $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                            //文章内容已经更新完毕 
                            if (indexOfArticle > 0 && indexOfArticle < ajaxResponse.length - 1) {
                                $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                                $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                            } else if (indexOfArticle == 0) {
                                $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                                $('#info-center-detail-page-id .left-section .pre-article').text('');
                            } else {
                                $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                                $('#info-center-detail-page-id .left-section .next-article').text('');
                            } //上下篇的问题解决
                        });


                    }
                    //===============================================================
                    var $libInfoImg = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-img');
                    var $libInfoTitle = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-abstract h1');
                    var $libInfoTime = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-abstract .info-center-article-share-time');
                    var $libInfoDetail = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-abstract p');
                    var $lookInDetail = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-abstract .look-in-detail')
                    for (var i = 0; i < (dataLength > 5 ? 5 : dataLength); i++) {
                        $libInfoImg.eq(i).attr('style', 'background-image:url("' + data[i].attach[0] + '");');
                        $lookInDetail.eq(i).attr('id', 'id-' + i);
                        $libInfoTitle.eq(i).text(data[i].title);
                        $libInfoTime.eq(i).text(data[i].publish_time.substr(0, 10));
                        if (data[i].summary.length > 128) {
                            $libInfoDetail.eq(i).text(data[i].summary.substring(0, 128) + '...');
                        } else {
                            $libInfoDetail.eq(i).text(data[i].summary);
                        }
                    }

                },
                error: function () {
                    console.log('infomation-page-ajax fail!!!');
                }
            }); //请求研究中心资讯内容
            $('.look-in-detail').click(function () {
                var indexOfArticle = parseInt($(this).attr('id').substr(3));
                //  alert(indexOfArticle);
                $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[indexOfArticle].title);
                $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[indexOfArticle].publish_time);
                $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[indexOfArticle].view_num + "次阅读");
                // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[indexOfArticle].summary);
                $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                //文章内容已经更新完毕
                for (var i = 0; i < 5; i++) {
                    $('#info-center-detail-page-id .right-section ul li').eq(i).text(ajaxResponse[i].title);
                };
                //右方最新资讯内容更新
                $('#info-center-detail-page-id .right-section ul li').click(function () {

                    for (var i = 0; i < ajaxResponse.length; i++) {
                        if (ajaxResponse[i].title == $(this).text()) {
                            $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[i].title);
                            $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[i].publish_time);
                            $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[i].view_num + "次阅读");
                            // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                            $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[i].summary);
                            $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[i].message));
                            break;
                        }
                    }
                });
                //更新下方的上一篇与下一篇
                if (indexOfArticle > 0 && indexOfArticle < ajaxResponse.length - 1) {
                    $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                    $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                } else if (indexOfArticle == 0) {
                    $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                    $('#info-center-detail-page-id .left-section .pre-article').text('');
                } else {
                    $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                    $('#info-center-detail-page-id .left-section .next-article').text('');
                } //上下篇的问题解决
                $('#info-center-detail-page-id').show()
                $('#info-center-article-content-research-room').hide(); //研究中心资讯隐藏
                $('#info-center-article-content-industry').hide(); //行业资讯隐藏
                $('#info-center-pagination-id').hide(); //分页条隐藏
                //点击下方的上一篇与下一篇的事件
                $('#info-center-detail-page-id .left-section .pre-article').click(function () {
                    indexOfArticle -= 1;
                    $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[indexOfArticle].title);
                    $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[indexOfArticle].publish_time);
                    $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[indexOfArticle].view_num + "次阅读");
                    // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                    $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[indexOfArticle].summary);
                    $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                    //文章内容已经更新完毕 
                    if (indexOfArticle > 0 && indexOfArticle < ajaxResponse.length - 1) {
                        $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                        $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                    } else if (indexOfArticle == 0) {
                        $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                        $('#info-center-detail-page-id .left-section .pre-article').text('');
                    } else {
                        $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                        $('#info-center-detail-page-id .left-section .next-article').text('');
                    } //上下篇的问题解决
                });
                //点击下方下一篇的事件
                $('#info-center-detail-page-id .left-section .next-article').click(function () {
                    indexOfArticle += 1;
                    $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[indexOfArticle].title);
                    $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[indexOfArticle].publish_time);
                    $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[indexOfArticle].view_num + "次阅读");
                    // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                    $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[indexOfArticle].summary);
                    $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                    //文章内容已经更新完毕 
                    if (indexOfArticle > 0 && indexOfArticle < ajaxResponse.length - 1) {
                        $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                        $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                    } else if (indexOfArticle == 0) {
                        $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                        $('#info-center-detail-page-id .left-section .pre-article').text('');
                    } else {
                        $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                        $('#info-center-detail-page-id .left-section .next-article').text('');
                    } //上下篇的问题解决
                });


            });
            //分页功能的实现
            $('#info-center-pagination-id ul li').click(function () {
                var index = $('#info-center-pagination-id ul li').index(this);
                var maxPageNum = Math.ceil(ajaxResponse.length / 5); //向上取整获取最大页面数
                var pageNum = parseInt($(this).text());
                // alert(pageNum);
                if (index == 6) //用户点击了下一页的功能
                {
                    for (var i = 0; i < 7; i++) {
                        if ($('#info-center-pagination-id ul li').eq(i).attr('class') == 'active') {
                            pageNum = parseInt($('#info-center-pagination-id ul li').eq(i).text());
                            if (pageNum === maxPageNum) {
                                break;
                            }
                            pageNum++;
                            for (var i = 0; i < 6; i++) {
                                $('#info-center-pagination-id ul li').eq(i).attr('class', 'waves-effect');
                            }
                            $('#info-center-pagination-id ul li').eq(pageNum).attr('class', 'active')
                            break;
                        }

                    }

                }
                if (index == 0) {

                    for (var i = 0; i < 7; i++) {
                        if ($('#info-center-pagination-id ul li').eq(i).attr('class') == 'active') {
                            pageNum = parseInt($('#info-center-pagination-id ul li').eq(i).text());
                            if (pageNum === 1) {
                                break;
                            }
                            pageNum--;
                            for (var i = 1; i < 7; i++) {
                                $('#info-center-pagination-id ul li').eq(i).attr('class', 'waves-effect');
                            }
                            $('#info-center-pagination-id ul li').eq(pageNum).attr('class', 'active')

                            break;
                        }

                    }
                }
                if (pageNum === 1) {
                    for (var i = 0; i < 7; i++) {
                        $('#info-center-pagination-id ul li').eq(i).attr('class', 'waves-effect');
                    }
                    $('#info-center-pagination-id ul li').eq(0).attr('class', 'disabled');
                    $('#info-center-pagination-id ul li').eq(1).attr('class', 'active');
                } else if (pageNum === maxPageNum) {
                    for (var i = 0; i < 7; i++) {
                        $('#info-center-pagination-id ul li').attr('class', 'waves-effect');
                    }
                    $('#info-center-pagination-id ul li').eq(6).attr('class', 'disabled');
                    $('#info-center-pagination-id ul li').eq(5).attr('class', 'active');
                } else if (!isNaN(parseInt($(this).text()))) { //证明此时是页码//当点击到最大页码时需要让下一页功能失效
                    for (var i = 0; i < 7; i++) {
                        $('#info-center-pagination-id ul li').eq(i).attr('class', 'waves-effect');
                    }
                    $('#info-center-pagination-id ul li').eq(pageNum).attr('class', 'active')
                }
                //判断分页部分是否应该向前推进还是向后推进
                var isMaxPage = parseInt($('#info-center-pagination-id ul li').eq(5).text());
                var isMinPage = parseInt($('#info-center-pagination-id ul li').eq(1).text());
                if (!(isMaxPage === maxPageNum)) {
                    //向后推进
                    if (index == 5) {
                        for (var i = 1; i < 6; i++) {
                            $('#info-center-pagination-id ul li').eq(i).children('a').text(parseInt(pageNum - 3 + i));
                        }
                        for (var i = 1; i < 6; i++) {
                            $('#info-center-pagination-id ul li').attr('class', 'waves-effect');
                        }
                        $('#info-center-pagination-id ul li').eq(3).attr('class', 'active');
                    }
                }
                if (!(isMinPage === 1)) {
                    //向前推进
                    if (index == 1) {
                        for (var i = 1; i < 6; i++) {
                            $('#info-center-pagination-id ul li').eq(i).children('a').text(parseInt(pageNum - 3 + i));
                        }
                        for (var i = 1; i < 6; i++) {
                            $('#info-center-pagination-id ul li').attr('class', 'waves-effect');
                        }
                        $('#info-center-pagination-id ul li').eq(3).attr('class', 'active');
                    }
                }
                //推进判断结束
                // for (var i = (pageNum - 1) * 5; i < pageNum * 5; i++) {
                var data = ajaxResponse;
                //  var attach = response.attachs;
                var dataLength = data.length;
                var $libInfoImg = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-img');
                var $libInfoTitle = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-abstract h1');
                var $libInfoTime = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-abstract .info-center-article-share-time');
                var $libInfoDetail = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-abstract p');
                var $lookInDetail = $('#info-center-article-content-research-room .info-center-article-list .info-center-article-abstract .look-in-detail')
                for (var i = 0; i < 5; i++) {
                    $libInfoImg.eq(i).attr('style', 'background-image:url("' + data[(pageNum - 1) * 5 + i].attach[0] + '");');
                    $lookInDetail.eq(i).attr('id', 'id-' + ((pageNum - 1) * 5 + i)); //注意字符串拼接问题，在这里因为前面有字符串，直接想加会使得成为字符串拼接问题
                    $libInfoTitle.eq(i).text(data[(pageNum - 1) * 5 + i].title);
                    $libInfoTime.eq(i).text(data[(pageNum - 1) * 5 + i].publish_time.substr(0, 10));
                    if (data[(pageNum - 1) * 5 + i].summary.length > 128) {
                        $libInfoDetail.eq(i).text(data[(pageNum - 1) * 5 + i].summary.substring(0, 128) + '...');
                    } else {
                        $libInfoDetail.eq(i).text(data[(pageNum - 1) * 5 + i].summary);
                    }
                }
                //}
            })
            //这个是资讯中心右上角“研究中心资讯 | 行业资讯”点击时的JS代码切换
            $('#info-center-detail-body-header-right-first').click(function (event) {
                infoCenterBodyHeaderChoices(event.target);
                infoCenterBodyHeaderRemoveChoices(event.target);
                $('#info-center-article-list-research-info-id').show(); //研究中心变蓝
                $('#info-center-article-list-industry-info-id').hide(); //行业资讯变灰
                $('#info-center-article-content-research-room').show(); //研究中心资讯
                $('#info-center-article-content-industry').hide(); //行业资讯
                $('#info-center-detail-page-id').hide(); //详情页
                $('#info-center-pagination-id').show(); //分页条
            });
            var ajaxResponseOfIndustryInfo = "";
            $('#info-center-detail-body-header-right-second').click(function (event) {
                //分页栏初始化
                for (var i = 0; i < 7; i++) {
                    $('#info-center-pagination-id ul li').attr('class', 'waves-effect');
                    $('#info-center-pagination-id ul li').eq(0).attr('class', 'disabled');
                    $('#info-center-pagination-id ul li').eq(1).attr('class', 'active');
                }
                //点击时再进行ajx请求，加快网页加载速度提高用户体验
                infoCenterBodyHeaderChoices(event.target);
                infoCenterBodyHeaderRemoveChoices(event.target);
                $.ajax({
                    url: 'http://39.108.87.54/api/advisory/industryInfo/number',
                    method: 'GET',
                    dataType: 'JSON',
                    contentType: 'application/x-www-form-urlencoded',
                    success: function (response) {
                        response = response.data.sort(function (a, b) {
                            return b.id - a.id;
                        });
                        // ajaxResponse = response; //提取所有返回的数据
                        ajaxResponseOfIndustryInfo = response;
                        var data = response;
                        var $image = $('#info-center-article-content-industry .info-center-article-list .info-center-article-img');
                        var $title = $('#info-center-article-content-industry .info-center-article-list .info-center-article-abstract h1');
                        var $time = $('#info-center-article-content-industry .info-center-article-list .info-center-article-abstract .info-center-article-share-time');
                        var $summary = $('#info-center-article-content-industry .info-center-article-list .info-center-article-abstract p');
                        var $lookInDetail = $('#info-center-article-content-industry .info-center-article-list .info-center-article-abstract .look-in-detail-industry');
                        for (var i = 0; i < (data.length > 5 ? 5 : data.length); i++) {
                            $image.eq(i).attr('style', 'background-image:url("' + data[i].attach[0] + '")'); //图片内容未定
                            $title.eq(i).text(data[i].title);
                            $time.eq(i).text(data[i].publish_time.substr(0, 10));
                            $summary.eq(i).text(data[i].summary);
                            $lookInDetail.eq(i).attr('id', 'id-' + i);
                        }

                    },
                    error: function () {
                        console.log('industry info get fail!!!');
                    }
                });
                //分页功能实现--现在在行业资讯页面中=============================================================
                $('#info-center-pagination-id ul li').click(function () {
                    var index = $('#info-center-pagination-id ul li').index(this);
                    var maxPageNum = Math.ceil(ajaxResponseOfIndustryInfo.length / 5); //向上取整获取最大页面数
                    var pageNum = parseInt($(this).text());
                    if (pageNum === 1) {
                        for (var i = 0; i < 7; i++) {
                            $('#info-center-pagination-id ul li').eq(i).attr('class', 'waves-effect');
                        }
                        $('#info-center-pagination-id ul li').eq(0).attr('class', 'disabled');
                        $('#info-center-pagination-id ul li').eq(1).attr('class', 'active');
                    } else if (pageNum === maxPageNum) {
                        for (var i = 0; i < 7; i++) {
                            $('#info-center-pagination-id ul li').attr('class', 'waves-effect');
                        }
                        $('#info-center-pagination-id ul li').eq(6).attr('class', 'disabled');
                        $('#info-center-pagination-id ul li').eq(5).attr('class', 'active');
                    } else if (!isNaN(pageNum)) { //证明此时是页码//当点击到最大页码时需要让下一页功能失效
                        for (var i = 1; i < 6; i++) {
                            $('#info-center-pagination-id ul li').attr('class', 'waves-effect');
                        }
                        $(this).attr('class', 'active')
                    }
                    //判断分页部分是否应该向前推进还是向后推进
                    var isMaxPage = parseInt($('#info-center-pagination-id ul li').eq(5).text());
                    var isMinPage = parseInt($('#info-center-pagination-id ul li').eq(1).text());
                    if (!(isMaxPage === maxPageNum)) {
                        //向后推进
                        if (index == 5) {
                            for (var i = 1; i < 6; i++) {
                                $('#info-center-pagination-id ul li').eq(i).children('a').text(parseInt(pageNum - 3 + i));
                            }
                            for (var i = 1; i < 6; i++) {
                                $('#info-center-pagination-id ul li').attr('class', 'waves-effect');
                            }
                            $('#info-center-pagination-id ul li').eq(3).attr('class', 'active');
                        }
                    }
                    if (!(isMinPage === 1)) {
                        //向前推进
                        if (index == 1) {
                            for (var i = 1; i < 6; i++) {
                                $('#info-center-pagination-id ul li').eq(i).children('a').text(parseInt(pageNum - 3 + i));
                            }
                            for (var i = 1; i < 6; i++) {
                                $('#info-center-pagination-id ul li').attr('class', 'waves-effect');
                            }
                            $('#info-center-pagination-id ul li').eq(3).attr('class', 'active');
                        }
                    }
                    //推进判断结束
                    // for (var i = (pageNum - 1) * 5; i < pageNum * 5; i++) {
                    var data = ajaxResponseOfIndustryInfo;
                    //  var attach = response.attachs;
                    var dataLength = data.length;
                    var $image = $('#info-center-article-content-industry .info-center-article-list .info-center-article-img');
                    var $title = $('#info-center-article-content-industry .info-center-article-list .info-center-article-abstract h1');
                    var $time = $('#info-center-article-content-industry .info-center-article-list .info-center-article-abstract .info-center-article-share-time');
                    var $summary = $('#info-center-article-content-industry .info-center-article-list .info-center-article-abstract p');
                    var $lookInDetail = $('#info-center-article-content-industry .info-center-article-list .info-center-article-abstract .look-in-detail-industry');
                    for (var i = 0; i < 5; i++) {
                        $image.eq(i).attr('style', 'background-image:url("' + data[(pageNum - 1) * 5 + i].attach[0] + '");');
                        $lookInDetail.eq(i).attr('id', 'id-' + ((pageNum - 1) * 5 + i)); //注意字符串拼接问题，在这里因为前面有字符串，直接想加会使得成为字符串拼接问题
                        $title.eq(i).text(data[(pageNum - 1) * 5 + i].title);
                        $time.eq(i).text(data[(pageNum - 1) * 5 + i].publish_time.substr(0, 10));
                        if (data[(pageNum - 1) * 5 + i].summary.length > 128) {
                            $summary.eq(i).text(data[(pageNum - 1) * 5 + i].summary.substring(0, 128) + '...');
                        } else {
                            $summary.eq(i).text(data[(pageNum - 1) * 5 + i].summary);
                        }
                    }
                    //}
                })
                $('#info-center-article-list-research-info-id').hide(); //研究中心变灰
                $('#info-center-article-list-industry-info-id').show(); //行业资讯变蓝
                $('#info-center-article-content-research-room').hide(); //研究中心资讯
                $('#info-center-article-content-industry').show(); //行业资讯
                $('#info-center-detail-page-id').hide(); //详情页
                $('#info-center-pagination-id').show(); //分页条



            });
            $('.look-in-detail-industry').click(function () {
                var indexOfArticle = parseInt($(this).attr('id').substr(3));
                $('#info-center-detail-page-id .left-section h1').text(ajaxResponseOfIndustryInfo[indexOfArticle].title);
                $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponseOfIndustryInfo[indexOfArticle].publish_time);
                $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponseOfIndustryInfo[indexOfArticle].view_num + "次阅读");
                // $('#info-center-detail-page-id .left-section .article-source').text( ajaxResponseOfIndustryInfo[indexOfArticle].source ?  ajaxResponseOfIndustryInfo[indexOfArticle].source : ""); //文章来源
                $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponseOfIndustryInfo[indexOfArticle].summary);
                $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponseOfIndustryInfo[indexOfArticle].message));
                //文章内容已经更新完毕
                //右方最新资讯更新
                for (var i = 0; i < 5; i++) {
                    $('#info-center-detail-page-id .right-section ul li').eq(i).text(ajaxResponseOfIndustryInfo[i].title);
                };
                $('#info-center-detail-page-id .right-section ul li').click(function () {
                    for (var i = 0; i < ajaxResponseOfIndustryInfo.length; i++) {
                        if (ajaxResponseOfIndustryInfo[i].title == $(this).text()) {
                            $('#info-center-detail-page-id .left-section h1').text(ajaxResponseOfIndustryInfo[i].title);
                            $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponseOfIndustryInfo[i].publish_time);
                            $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponseOfIndustryInfo[i].view_num + "次阅读");
                            // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                            $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponseOfIndustryInfo[i].summary);
                            $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponseOfIndustryInfo[i].message));
                            break;
                        }
                    }
                })
                //更新下方的上一篇与下一篇

                //更新下方的上一篇与下一篇
                if (indexOfArticle > 0 && indexOfArticle < ajaxResponse.length - 1) {
                    $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                    $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                } else if (indexOfArticle == 0) {
                    $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                    $('#info-center-detail-page-id .left-section .pre-article').text('');
                } else {
                    $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                    $('#info-center-detail-page-id .left-section .next-article').text('');
                } //上下篇的问题解决
                $('#info-center-detail-page-id').show()
                $('#info-center-article-content-research-room').hide(); //研究中心资讯隐藏
                $('#info-center-article-content-industry').hide(); //行业资讯隐藏
                $('#info-center-pagination-id').hide(); //分页条隐藏
                //点击下方的上一篇与下一篇的事件
                $('#info-center-detail-page-id .left-section .pre-article').click(function () {
                    indexOfArticle -= 1;
                    $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[indexOfArticle].title);
                    $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[indexOfArticle].publish_time);
                    $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[indexOfArticle].view_num + "次阅读");
                    // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                    $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[indexOfArticle].summary);
                    $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                    //文章内容已经更新完毕 
                    if (indexOfArticle > 0 && indexOfArticle < ajaxResponse.length - 1) {
                        $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                        $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                    } else if (indexOfArticle == 0) {
                        $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                        $('#info-center-detail-page-id .left-section .pre-article').text('');
                    } else {
                        $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                        $('#info-center-detail-page-id .left-section .next-article').text('');
                    } //上下篇的问题解决
                });
                //点击下方下一篇的事件
                $('#info-center-detail-page-id .left-section .next-article').click(function () {
                    indexOfArticle += 1;
                    $('#info-center-detail-page-id .left-section h1').text(ajaxResponse[indexOfArticle].title);
                    $('#info-center-detail-page-id .left-section .share-time').text("发表于 " + ajaxResponse[indexOfArticle].publish_time);
                    $('#info-center-detail-page-id .left-section .read-times').text(ajaxResponse[indexOfArticle].view_num + "次阅读");
                    // $('#info-center-detail-page-id .left-section .article-source').text(ajaxResponse[indexOfArticle].source ? ajaxResponse[indexOfArticle].source : ""); //文章来源
                    $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>" + ajaxResponse[indexOfArticle].summary);
                    $('#info-center-detail-page-id .left-section article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                    //文章内容已经更新完毕 
                    if (indexOfArticle > 0 && indexOfArticle < ajaxResponse.length - 1) {
                        $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                        $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                    } else if (indexOfArticle == 0) {
                        $('#info-center-detail-page-id .left-section .next-article').text('下一篇：' + ajaxResponse[indexOfArticle + 1].title);
                        $('#info-center-detail-page-id .left-section .pre-article').text('');
                    } else {
                        $('#info-center-detail-page-id .left-section .pre-article').text('上一篇：' + ajaxResponse[indexOfArticle - 1].title);
                        $('#info-center-detail-page-id .left-section .next-article').text('');
                    }
                }); //上下篇的问题解决
            });
            //资讯中心右上角“研究中心资讯 | 行业资讯”点击时的JS代码切换结
            $('.info-center-detail-body .info-center-detail-body-header .info-center-detail-body-header-content .info-center-detail-body-header-left').click(function () {
                infoCenterBodyHeaderChoices($('#info-center-detail-body-header-right-first'));
                infoCenterBodyHeaderRemoveChoices($('#info-center-detail-body-header-right-first'));
                $('#info-center-article-content-industry').hide();
                $('#info-center-detail-page-id').hide();
                $('#info-center-article-content-research-room').show();
                $('#info-center-pagination-id').show(); //分页条
            });
            //资讯中心搜索
            // $('#search').keydown(function (e) {
            //     if (e.keyCode == 13) {
            //         window.location.href = '../searchResult/searchResult.html';

            //         alert('success');
            //         var searchValue = $('#search').val();
            //         return false;
            //         // $.ajax({

            //         // })
            //     }
            // });
        }
        //匹配资讯中心
        /*===================================================================================================================*/
        /*======研究项目模块==================================================================================================*/
        /*===================================================================================================================*/
        /*===================================================================================================================*/

        $project = $url.match('researchProject');
        if ($project != null) {
            //ajax请求
            $.cookie('reaschProject', 0);
            $.cookie('reaschGain', 0);
            $('#common-nav-project').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
            var researchProjectNum = 0;
            var ajaxResponseResearchProject = "";
            var ajaxResponseFinshed = "";
            var projectFinshNum = 0;
            $.ajax({
                url: 'http://39.108.87.54/api/project/working/',
                method: 'GET',
                dataType: 'JSON',
                // async: false ,
                contentType: 'application/x-www-form-urlencoded',
                success: function (response) {
                    response = response.data.sort(function (a, b) {
                        return b.id - a.id
                    });
                    ajaxResponseResearchProject = response;
                    var data = response;
                    //===================================================================================
                    if (parseInt($.cookie('globalMainPageResearch')) != -1) {
                        var indexOfArticle = parseInt($.cookie('globalMainPageResearch'));
                        //alert(indexOfArticle+'research')
                        $.cookie('globalMainPageResearch', '-1', {
                            path: '/'
                        });
                        $('#research-project-detail-page-id h1').text(ajaxResponseResearchProject[indexOfArticle].title);
                        $('#research-project-detail-page-id .share-time').text(ajaxResponseResearchProject[indexOfArticle].publish_time);
                        $('#research-project-detail-page-id .article-section article').html(HTMLDecode(ajaxResponseResearchProject[indexOfArticle].message));
                        var imglist = "";
                        for (var i = 0; i < ajaxResponseResearchProject[indexOfArticle].attach.length; i++) {
                            console.log(ajaxResponseResearchProject[indexOfArticle].attach[i]);
                            imglist += '<div class="img-list">' + '</div>';
                            //' + 'style="background-image: url("' + ajaxResponseResearchProject[indexOfArticle].attach[i] +'")"
                        }
                        $('#research-project-detail-page-id .article-section .right-image').html(imglist);
                        for (var i = 0; i < ajaxResponseResearchProject[indexOfArticle].attach.length; i++) {
                            // console.log( ajaxResponseResearchProject[indexOfArticle].attach[i] );
                            // imglist += '<div class="img-list">' + '</div>';
                            //' + 'style="background-image: url("' + ajaxResponseResearchProject[indexOfArticle].attach[i] +'")"
                            $('#research-project-detail-page-id .article-section .right-image .img-list').eq(i).attr('style', 'background-image: url("' + ajaxResponseResearchProject[indexOfArticle].attach[i] + '")')
                        }
                        $('#footer-top-id').text("");
                        $('#current-research-project-id').hide();
                        $('#research-project-gain-id').hide();
                        $('#research-project-detail-page-id').show();
                    }
                    //===========================================================================
                    researchProjectNum = data.length; //获取全部共有几条数据
                    var attach = data.attach;
                    var $img = $('#current-research-project-id .current-research-project-card .example-img');
                    var $title = $('#current-research-project-id .current-research-project-card .card-introduction h1');
                    var $time = $('#current-research-project-id .current-research-project-card .card-introduction span');
                    var $summary = $('#current-research-project-id .current-research-project-card .card-introduction p');
                    var $card = $('#current-research-project-id .current-research-project-card');
                    for (var i = 0; i < (data.length > 4 ? 4 : data.length); i++) {
                        $card.eq(i).attr('id', 'current-research-project-card-id-' + i);
                        $img.eq(i).attr('style', 'background-image: url("' + data[i].attach[0] + '");');
                        $title.eq(i).text(data[i].title);
                        $time.eq(i).text(data[i].publish_time.substr(0, 10));
                        var text = getText(HTMLDecode(data[i].message));
                        if (text.length > 63) {
                            $summary.eq(i).text(text.substring(0, 60) + "...");
                        } else {
                            $summary.eq(i).text(text);
                        }
                    }
                },
                error: function () {
                    console.log('get project working fail!!!');
                }
            })
            //研究项目右上角切换功能

            $('#research-project-detail-body-header-right-first').click(function (event) {
                infoCenterBodyHeaderChoices(event.target);
                infoCenterBodyHeaderRemoveChoices(event.target);
                $('#current-research-project-id').show();
                $('#research-project-gain-id').hide();
                $('#research-project-detail-page-id').hide();
                $('#footer-top-id').text('');
                if ($.cookie('reaschProject') == 1) {
                    $('#footer-top-id').text('没有更多内容了……');
                } else {
                    $('#footer-top-id').text('');
                }
            });
            $('#research-project-detail-body-header-right-second').click(function (event) {

                infoCenterBodyHeaderChoices(event.target);
                infoCenterBodyHeaderRemoveChoices(event.target);
                $.ajax({
                    url: 'http://39.108.87.54/api/project/finished/',
                    method: 'GET',
                    dataType: 'JSON',
                    contentType: 'application/x-www-form-urlencoded',
                    success: function (response) {
                        response = response.data.sort(function (a, b) {
                            return b.id - a.id
                        });
                        ajaxResponseFinshed = response;
                        projectFinshNum = response.length;
                        var data = response;
                        var attach = response.attach;
                        var $img = $('#research-project-gain-id .research-project-gain-card .example-img');
                        var $title = $('#research-project-gain-id .research-project-gain-card .card-introduction h1');
                        var $time = $('#research-project-gain-id .research-project-gain-card .card-introduction span');
                        var $summary = $('#research-project-gain-id .research-project-gain-card .card-introduction p');
                        var $card = $('#research-project-gain-id  .research-project-gain-card');
                        for (var i = 0; i < (data.length > 4 ? 4 : data.length); i++) {
                            $card.eq(i).attr('id', 'id-' + i);
                            $img.eq(i).attr('style', 'background-image: url("' + data[i].attach[0] + '");');
                            $title.eq(i).text(data[i].title);
                            $time.eq(i).text(data[i].publish_time.substr(0, 10));
                            var text = getText(HTMLDecode(data[i].message));
                            if (text.length > 63) {
                                $summary.eq(i).text(text.substring(0, 60) + "...");
                            } else {
                                $summary.eq(i).text(text);
                            }
                        }
                    },
                    error: function () {
                        console.log('get project finshed fail!!!');
                    }
                });
                if ($.cookie('reaschGain') == 1) {
                    $('#footer-top-id').text('没有更多内容了……');
                } else {
                    $('#footer-top-id').text('');
                }
                $('#current-research-project-id').hide();
                $('#research-project-detail-page-id').hide();
                $('#research-project-gain-id').show();
                // $('#footer-top-id').text('');
            });
            //研究中心点击事件查看详情方法
            $('#current-research-project-id').on('click', '.current-research-project-card', function () {
                var indexOfArticle = parseInt($(this).attr('id').substr(33));
                $('#research-project-detail-page-id h1').text(ajaxResponseResearchProject[indexOfArticle].title);
                $('#research-project-detail-page-id .share-time').text(ajaxResponseResearchProject[indexOfArticle].publish_time);
                $('#research-project-detail-page-id .article-section article').html(HTMLDecode(ajaxResponseResearchProject[indexOfArticle].message));
                var imglist = "";
                for (var i = 0; i < ajaxResponseResearchProject[indexOfArticle].attach.length; i++) {

                    imglist += '<div class="img-list">' + '</div>';

                }
                $('#research-project-detail-page-id .article-section .right-image').html(imglist);
                for (var i = 0; i < ajaxResponseResearchProject[indexOfArticle].attach.length; i++) {

                    $('#research-project-detail-page-id .article-section .right-image .img-list').eq(i).attr('style', 'background-image: url("' + ajaxResponseResearchProject[indexOfArticle].attach[i] + '")')
                }
                $('#footer-top-id').text("");
                $('#current-research-project-id').hide();
                $('#research-project-gain-id').hide();
                $('#research-project-detail-page-id').show();
            });
            $('#research-project-gain-id').on('click', '.research-project-gain-card', function () {
                var indexOfArticle = parseInt($(this).attr('id').substr(3));
                $('#research-project-detail-page-id h1').text(ajaxResponseFinshed[indexOfArticle].title);
                $('#research-project-detail-page-id .share-time').text(ajaxResponseFinshed[indexOfArticle].publish_time);
                $('#research-project-detail-page-id .article-section article').html(HTMLDecode(ajaxResponseFinshed[indexOfArticle].message));
                var imglist = "";
                for (var i = 0; i < ajaxResponseFinshed[indexOfArticle].attach.length; i++) {
                    imglist += '<div class="img-list">' + '</div>';
                }
                $('#research-project-detail-page-id .article-section .right-image').html(imglist);
                for (var i = 0; i < ajaxResponseFinshed[indexOfArticle].attach.length; i++) {
                    $('#research-project-detail-page-id .article-section .right-image .img-list').eq(i).attr('style', 'background-image: url("' + ajaxResponseFinshed[indexOfArticle].attach[i] + '")')
                }
                $('#footer-top-id').text("");
                $('#current-research-project-id').hide();
                $('#research-project-gain-id').hide();
                $('#research-project-detail-page-id').show();
            })
            //研究中心点击时间结束
            //研究项目右上角切换功能结束
            /*======滚动显示效果==========================================================*/
            var counterOfReaschProject = 3; //变量声明的同时最好初始化
            function appendReasch() {
                counterOfReaschProject++;
                var text = getText(HTMLDecode(ajaxResponseResearchProject[counterOfReaschProject].message));
                if (text.length > 63) {
                    var $reaschList = '<div class="current-research-project-card">' +
                        '<div class="example-img"></div>' +
                        '<div class="card-introduction">' +
                        '<h1>' + ajaxResponseResearchProject[counterOfReaschProject].title + '</h1>' +
                        '<p>' + text.substr(0, 63) + "..." + '</p>' +
                        '<span class="share-time">' + ajaxResponseResearchProject[counterOfReaschProject].publish_time.substr(0, 10) + '</span>' + '</div>' + '</div>';
                } else {
                    var $reaschList = '<div class="current-research-project-card">' +
                        '<div class="example-img"></div>' +
                        '<div class="card-introduction">' +
                        '<h1>' + ajaxResponseResearchProject[counterOfReaschProject].title + '</h1>' +
                        '<p>' + text + '</p>' +
                        '<span class="share-time">' + ajaxResponseResearchProject[counterOfReaschProject].publish_time.substr(0, 10) + '</span>' + '</div>' + '</div>';
                }
                $('#current-research-project-id').append($reaschList);
                $('#current-research-project-id .current-research-project-card .example-img').eq(counterOfReaschProject).attr('style', 'background-image: url("' + ajaxResponseResearchProject[counterOfReaschProject].attach[0] + '");');
                $('#current-research-project-id .current-research-project-card').eq(counterOfReaschProject).attr('id', 'current-research-project-card-id-' + counterOfReaschProject);
                Materialize.fadeInImage($('.current-research-project-card').eq(counterOfReaschProject));

            }
            $(window).scroll(function () {
                if ($('#current-research-project-id').attr('style') != "") {
                    return;
                }
                var restHeight = $(document).height() - $(document).scrollTop(); //文档高度减去被卷进去的文档高度
                var flagOfDisplay = restHeight - $(window).height(); //窗口可视高度
                if (flagOfDisplay < 200) {
                    // alert('doit');
                    for (var i = 0; i < 2; i++) {
                        if (counterOfReaschProject < ajaxResponseResearchProject.length - 2) {
                            appendReasch();
                        } else if (counterOfReaschProject == ajaxResponseResearchProject.length - 2) {
                            appendReasch();
                            $.cookie('reaschProject', 1);
                            $('#footer-top-id').text('没有更多内容了……');
                        }
                    }
                }
            });

            var counterOfProjectGain = 3;

            function appendProjectGain() {
                counterOfProjectGain++;
                var text = getText(HTMLDecode(ajaxResponseFinshed[counterOfProjectGain].message));
                if (text.length > 63) {
                    var $reaschList = '<div class="research-project-gain-card">' +
                        '<div class="example-img"></div>' +
                        '<div class="card-introduction">' +
                        '<h1>' + ajaxResponseFinshed[counterOfProjectGain].title + '</h1>' +
                        '<p>' + text.substr(0, 63) + "..." + '</p>' + '<span  class="share-time">' + ajaxResponseFinshed[counterOfProjectGain].publish_time.substr(0, 10) + '</span>' + '</div>' + '</div>';
                } else {
                    var $reaschList = '<div class="current-research-project-card">' +
                        '<div class="example-img"></div>' +
                        '<div class="card-introduction">' +
                        '<h1>' + ajaxResponseFinshed[counterOfProjectGain].title + '</h1>' +
                        '<p>' + text + '</p>' + '<span class="share-time">' + ajaxResponseFinshed[counterOfProjectGain].publish_time.substr(0, 10) + '</span>' + '</div>' + '</div>';
                }
                $('#research-project-gain-id').append($reaschList);
                $('#research-project-gain-id .research-project-gain-card .example-img').eq(counterOfProjectGain).attr('style', 'background-image: url("' + ajaxResponseFinshed[counterOfProjectGain].attach[0] + '");');
                $('#research-project-gain-id  .research-project-gain-card').eq(counterOfProjectGain).attr('id', 'id-' + counterOfProjectGain);
                Materialize.fadeInImage($('.research-project-gain-card').eq(counterOfProjectGain));

            }
            $(window).scroll(function () {
                if ($('#research-project-gain-id').attr('style') != "") {
                    console.log('12345')
                    console.log($('#research-project-gain-id').attr('style') + " 1223")
                    return;
                }
                var restHeight = $(document).height() - $(document).scrollTop(); //文档高度减去被卷进去的文档高度
                var flagOfDisplay = restHeight - $(window).height(); //窗口可视高度
                if (flagOfDisplay < 200) {
                    // alert('doit');
                    for (var i = 0; i < 2; i++) {
                        if (counterOfProjectGain < ajaxResponseFinshed.length - 2) {
                            appendProjectGain();
                        } else if (counterOfProjectGain == ajaxResponseFinshed.length - 2) {
                            appendProjectGain();
                            $.cookie('reaschGain', 1);
                            $('#footer-top-id').text('没有更多内容了……');
                        }
                    }
                }
            });
            //研究项目滚动监视效果结束=============================================================
            $('.info-center-detail-body .info-center-detail-body-header .info-center-detail-body-header-content .info-center-detail-body-header-left').click(function () {
                infoCenterBodyHeaderChoices($('#research-project-detail-body-header-right-first'));
                infoCenterBodyHeaderRemoveChoices($('#research-project-detail-body-header-right-first'));
                $('#research-project-gain-id').hide();
                $('#research-project-detail-page-id').hide();
                $('#current-research-project-id').show();
            });
            //返回正常页面
        }

        /*===================================================================================================================*/
        /*======行业精英模块==================================================================================================*/
        /*===================================================================================================================*/
        /*===================================================================================================================*/

        $industryElite = $url.match('industryElite');
        if ($industryElite != null) {
            $('#common-nav-industry-elite').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
            var ajaxResponse = "";
            var personNum = 0;
            $.ajax({
                url: 'http://39.108.87.54/api/elite/list/',
                method: 'GET',
                dataType: 'JSON',
                contentType: 'application/x-www-form-urlencoded',
                success: function (response) {
                    //alert('success');
                    response = response.data.sort(function (a, b) {
                        return b.id - a.id;
                    });
                    ajaxResponse = response;
                    if (parseInt($.cookie('globalMainPageElite')) != -1) {
                        var indexOfArticle = parseInt($.cookie('globalMainPageElite'));
                        $.cookie('globalMainPageElite', '-1', {
                            path: '/'
                        });
                        $('#industry-elite-personal-detail-page-id .industry-elite-personal-detail-img').attr('style', 'background-image:url("' + ajaxResponse[indexOfArticle].avatar + '")');
                        $('#industry-elite-personal-detail-page-id .industry-elite-personal-detail-article .industry-elite-personal-detail-article-name').text(ajaxResponse[indexOfArticle].title);
                        $('#industry-elite-personal-detail-page-id .industry-elite-personal-detail-article .industry-elite-personal-detail-article-position').text(ajaxResponse[indexOfArticle].honor);
                        $('#industry-elite-personal-detail-page-id .industry-elite-personal-detail-article article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                        $('#industry-elite-list-body-article-id').hide();
                        $('#industry-elite-personal-detail-page-id').show();
                    }

                    personNum = response.length;
                    var data = response;
                    var $card = $('.industry-elite-list-body-article-list');
                    var $image = $('.industry-elite-list-body-article-list .industry-elite-list-body-article-list-left');
                    var $name = $('.industry-elite-list-body-article-list .industry-elite-list-body-article-list-right .industry-elite-introduction .industry-elite-name');
                    var $honor = $('.industry-elite-list-body-article-list .industry-elite-list-body-article-list-right .industry-elite-introduction .industry-elite-honor');
                    var $introduction = $('.industry-elite-list-body-article-list .industry-elite-list-body-article-list-right .industry-elite-introduction .industry-elite-personal-introduction');
                    for (var i = 0; i < (data.length > 6 ? 6 : data.length); i++) {
                        $card.eq(i).attr('id', 'industry-elite-id-' + i);
                        $image.eq(i).attr('style', 'background-image:url("' + data[i].avatar + '")');
                        $image.eq(i).html('');
                        $name.eq(i).text(data[i].title);
                        $honor.eq(i).text(data[i].honor);
                        var text = getText(HTMLDecode(data[i].message));
                        $introduction.eq(i).text(text);
                        console.log(text);
                    }
                },
                error: function () {
                    console.log('get elite fail!!!');
                }
            });
            //滚动显示内容，动态刷新
            var counterOfElite = 5;
            // window.scr
            function appendChild() {
                counterOfElite++;
                var text = getText(HTMLDecode(ajaxResponse[counterOfElite].message));
                // alert(text);
                var urlPng = ajaxResponse[counterOfElite].avatar;
                if (text.length > 60) {
                    $childHtml = '<div class="industry-elite-list-body-article-list">' +
                        '<div class="industry-elite-list-body-article-list-left"><i class=" material-icons">polymer</i></div>' +
                        '<div class="industry-elite-list-body-article-list-right">' +
                        '<div class="industry-elite-introduction" id="industry-elite-personal-detail-introduction-second">' +
                        '<span class="industry-elite-name">' + ajaxResponse[counterOfElite].title + '</span>' +
                        '<span class="industry-elite-honor">' + ajaxResponse[counterOfElite].honor + '</span>' +
                        ' <span class="industry-elite-personal-introduction">' + text.substr(0, 60) + "..." + '</span>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                } else {
                    $childHtml = '<div class="industry-elite-list-body-article-list">' +
                        '<div class="industry-elite-list-body-article-list-left"><i class=" material-icons">polymer</i></div>' +
                        '<div class="industry-elite-list-body-article-list-right">' +
                        '<div class="industry-elite-introduction" id="industry-elite-personal-detail-introduction-second">' +
                        '<span class="industry-elite-name">' + ajaxResponse[counterOfElite].title + '</span>' +
                        '<span class="industry-elite-honor">' + ajaxResponse[counterOfElite].honor + '</span>' +
                        ' <span class="industry-elite-personal-introduction">' + text + '</span>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                }
                $('#industry-elite-list-body-article-id').append($childHtml);
                $('.industry-elite-list-body-article-list .industry-elite-list-body-article-list-left').eq(counterOfElite).attr('style', 'background-image:url("' + urlPng + '")');
                $('.industry-elite-list-body-article-list').eq(counterOfElite).attr('id', 'industry-elite-id-' + counterOfElite)
                $('.industry-elite-list-body-article-list .industry-elite-list-body-article-list-left').eq(counterOfElite).html('');
                Materialize.fadeInImage($('.industry-elite-list-body-article-list').eq(counterOfElite));
            }
            $(window).scroll(function () {
                var restHeight = $(document).height() - $(document).scrollTop(); //文档高度减去被卷进去的文档高度
                var flagOfDisplay = restHeight - $(window).height(); //窗口可视高度
                if (flagOfDisplay < 200) {
                    // alert('doit');
                    for (var i = 0; i < 2; i++) {
                        if (counterOfElite < ajaxResponse.length - 2) {
                            appendChild();
                        } else if (counterOfElite == ajaxResponse.length - 2) {
                            appendChild();
                            $('#footer-top-id').text('没有更多内容了……');
                        }
                    }
                }
            });
            //行业精英获取信息，点击刷新的行业精英的内容
            $('#industry-elite-list-body-article-id').on('click', '.industry-elite-list-body-article-list', function () {
                var indexOfArticle = parseInt($(this).attr('id').substr(18));
                // alert(indexOfArticle);
                $('#industry-elite-personal-detail-page-id .industry-elite-personal-detail-img').attr('style', 'background-image:url("' + ajaxResponse[indexOfArticle].avatar + '")');
                $('#industry-elite-personal-detail-page-id .industry-elite-personal-detail-article .industry-elite-personal-detail-article-name').text(ajaxResponse[indexOfArticle].title);
                $('#industry-elite-personal-detail-page-id .industry-elite-personal-detail-article .industry-elite-personal-detail-article-position').text(ajaxResponse[indexOfArticle].honor);
                $('#industry-elite-personal-detail-page-id .industry-elite-personal-detail-article article').html(HTMLDecode(ajaxResponse[indexOfArticle].message));
                $('#industry-elite-list-body-article-id').hide();
                $('#industry-elite-personal-detail-page-id').show();
                $('#footer-top-id').text('');
                //调试代码结束
            })
            //第一个人结束
            $('.industry-elite-list-body .industry-elite-list-body-header .industry-elite-list-body-header-content .industry-elite-list-body-header-left').click(function () {
                $('#industry-elite-personal-detail-page-id').hide();
                $('#industry-elite-list-body-article-id').show();

            });
        }
        $forum = $url.match('forum');
        if ($forum != null) {
            $('#common-nav-forum').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
        }

        $aboutUs = $url.match('aboutUs');
        if ($aboutUs != null) {
            $('#common-nav-about-us').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
            $('.info-center-detail-body .info-center-detail-body-header .info-center-detail-body-header-content .info-center-detail-body-header-left').click(function () {
                $('#about-us-member-id').hide();
                $('#about-us-organization-id').show();
            });

        };
        $joinUs = $url.match('joinUs');
        if ($joinUs != null) {}
        var $searchResult = $url.match('searchResult.html');
        if ($searchResult != null) {
            var searchAjax="";
            var search="";
            $.ajax({
                url: 'http://39.108.87.54/api/search/type-articles__q-' + $.cookie('globalSearch'),
                method: 'GET',
                dataType: 'JSON',
                //   type:'articles',
                //  data:,
                contentType: 'application/x-www-form-urlencoded',
                success: function (response) {
                    searchAjax=response;
                    var length = response.length;
                    if (length) {
                       
                        search=$.cookie('globalSearch');
                        $('#search-data-length').text(length + "条结果");
                        var target=new RegExp($.cookie('globalSearch'),'g');
                        var changeTarget='<span style="color:#c00">'+$.cookie('globalSearch')+'</span>'
                        for (var i = 0; i < length; i++) {
                            var text= getText(HTMLDecode(response[i].content)).replace(target, changeTarget)
                            var $childResult = '<div class="search-data-list-one"><div class="title">'+response[i].name+'</div>'+'<span class="share-time">' + response[i].add_time.substr(0, 10) + '</span><p>' + text+'</p>' +
                                '<div class="btn look-in-detail-search">查看详情</div>' +
                                '</div>';
                            $('#search-data-list-id').append($childResult);
                        }
                        $.cookie('globalSearch','');
                        $('body').trigger('click');
                    } else {
                        $('#search-data-length').text("0" + "条结果");
                        var stylecontent=$('#footer-top-id').attr('style');
                        $('#footer-top-id').attr('style',stylecontent+'position:fixed;bottom:260px;');
                    }



                },
                error: function () {
                    alert('get search information fail!!!');
                }
            });
            $('#search-data-list-id').on('click','.look-in-detail-search',function(){
                var index=$('.look-in-detail-search').index($(this));
                var target=new RegExp(search,'g');
                var changeTarget='<span style="color:#c00">'+search+'</span>';
                var text= HTMLDecode(searchAjax[index].content).replace(target, changeTarget);
                $('#info-center-detail-page-id .left-section h1').html(searchAjax[index].name);
                $('#info-center-detail-page-id .left-section .share-time').text("发表于 "+searchAjax[index].add_time.substr(0,10));
                $('#info-center-detail-page-id .left-section .read-times').text(searchAjax[index].detail.views+"次阅读");
                $('#info-center-detail-page-id .left-section .info-center-detail-page-abstract').html("<span></span>"+searchAjax[index].summary);
                $('#info-center-detail-page-id .left-section article').html(text);
                $('.search-result-content').hide();
                $('#info-center-detail-page-id').show();
            })
        }
    }
    determineTheBlueBorder();
    //URL处理结束

    //处理尾部，如果页面内容少，让尾部固定在底部，多则不用理
    var determineTheFooterPosition = function () {
        $('#common-footer-id').removeAttr('style');
        var contentHeight = document.body.scrollHeight, //网页正文全文高度
            winHeight = window.innerHeight; //可视窗口高度，不包括浏览器顶部工具栏
        if (!(contentHeight > winHeight)) {
            //当网页正文高度小于可视窗口高度时，为footer添加类fixed-bottom
            //$("#footer").style.cssText="position:fixed; bottom:0px;";
            $('#common-footer-id').attr('style', 'position:fixed; bottom:0px;');
        } else {
            // $("#footer").style.cssText="";
            $('#common-footer-id').removeAttr('style');
        }
    }
    determineTheFooterPosition();
    $('body').click(function () {
        determineTheFooterPosition();
    })
    //尾部位置处理结束
    //关于我们上方的点击切换页面功能
    $('#about-us-detail-body-header-right-first').click(function (event) {
        infoCenterBodyHeaderChoices(event.target);
        infoCenterBodyHeaderRemoveChoices(event.target);
        $('#about-us-organization-id').show();
        $('#about-us-member-id').hide();

    });
    $('#about-us-detail-body-header-right-second').click(function (event) {
        infoCenterBodyHeaderChoices(event.target);
        infoCenterBodyHeaderRemoveChoices(event.target);
        $('#about-us-organization-id').hide();
        $('#about-us-member-id').show();
    });
    //关于我们上方切换结束
    //行业精英获取信息结束

    $('#search').focus(function () {
        $('.common-header .common-header-content nav').attr('style', 'width:300px');
    });
    $('#search').blur(function () {
        $('.common-header .common-header-content nav').removeAttr('style');
    });
    $('#search').keydown(function (event) {
        if (event.keyCode == 13) {
            $.cookie('globalSearch', $('#search').val());
            var url = window.location.href;
            if (url.match('index.html') != null) {
                window.location.href = "./html/searchResult/searchResult.html";
            } else {
                window.location.href = "../searchResult/searchResult.html";
            }
            event.preventDefault();
        }
    })
})