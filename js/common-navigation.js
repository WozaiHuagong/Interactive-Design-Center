$(document).ready(function () {
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



    //获取当前页面的URL，用正则表达式匹配，决定那个导航的地方有下边的蓝线
    var determineTheBlueBorder = function () {
        $url = window.location.href;
        $mainPage = $url.match('index');
        if ($mainPage != null) {
            $('#common-nav-mainpage').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
            //根据当前URL做出相关的ajax请求与更新，更新轮播图
            $.ajax({
                url: "http://wecenter.shabby-wjt.cn:8081/api/carousel/list/",
                method: 'GET',
                dataType: 'JSON',
                contentType: 'application/x-www-form-urlencoded',
                //data追加到URL
                success: function (response) {
                    //console.log(response);
                    var data = response.data; //轮播图内容
                    var arrLength = data.length;
                    //更新轮播图
                    for (var i = 0; i < arrLength; i++) {
                        $('.carousel-item').eq(i).attr('style', 'background-image: url("' + data[i].url + '");');
                    }
                    //轮播图更新完毕
                },
                error: function () {
                    console.log('carousel get fail!');
                }
            }); //轮播图更新完毕

            //更新主页资讯中心内容
            $.ajax({
                url: "http://wecenter.shabby-wjt.cn:8081/api/advisory/industryInfo/number",
                method: 'GET',
                // data:'3',
                contentType: 'application/x-www-form-urlencoded',
                dataType: 'JSON',
                success: function (response) {
                    var data = response.data;
                    var dataLength = data.length;
                    var $title = $('.main-page-info-center-card .row h2');
                    var $p = $('.main-page-info-center-card .row p');
                    var $spanPublishTime = $('.main-page-info-center-card .row span');
                    for (var i = 0; i < (dataLength >= 3 ? 3 : dataLength); i++) {
                        $title.eq(i).text(data[i].title);
                        $p.eq(i).text(data[i].summary);
                        $spanPublishTime.eq(i).text(data[i].publish_time);
                    }

                },
                error: function () {
                    console.log('main-page-info-center-infomation-get-fail!');
                }

            });
            //主页资讯中心内容更新完毕
            //研究项目具体内容更换开始；
            $.ajax({
                url: "http://wecenter.shabby-wjt.cn:8081/api/project/working/",
                method: 'GET',
                contentType: 'application/x-www-form-urlencoded',
                dataType: 'JSON',
                success: function (response) {
                    var data = response.data;
                    var attach = response.attachs; //附件图片内容
                    var dataLength = data.length;
                    var $image = $('.main-page-research-project-card .row .col .card .card-image');
                    var $title = $('.main-page-research-project-card .row .col .card .card-content .card-article-title');
                    var $summary = $('.main-page-research-project-card .row .col .card .card-content p');
                    var $publish_time = $('.main-page-research-project-card .row .col .card .card-content .publish-time');
                    for (var i = 0; i < (dataLength > 2 ? 2 : dataLength); i++) {

                        $image.eq(i).attr('style', 'background-image:url("' + attach[i].url + '");') //更新项目图片
                        $title.eq(i).text(data[i].title);
                        $summary.eq(i).text(data[i].message);
                        $publish_time.eq(i).text(data[i].publish_time);
                    }
                },
                error: function () {
                    console.log('get-research-project-fail!')
                }
            });
            //资讯中心内容更换结束
            //行业精英资讯内容更换开始
            $.ajax({
                url: "http://wecenter.shabby-wjt.cn:8081/api/elite/list/",
                method: 'GET',
                contentType: 'application/x-www-form-urlencoded',
                dataType: 'JSON',
                success: function (response) {
                    var data = response.data;
                    var dataLength = data.length;
                    var $image = $('.main-page-industry-elite-card .row .col .card .card-image');
                    var $name = $('.main-page-industry-elite-card .row .col .card .card-stacked .card-content .name');

                    var $position = $('.main-page-industry-elite-card .row .col .card .card-stacked .card-content .position');
                    var $introduction = $('.main-page-industry-elite-card .row .col .card .card-stacked .card-content p'); //这是行业精英的描述
                    for (var i = 0; i < (dataLength > 4 ? 4 : dataLength); i++) {
                        $image.eq(i).attr('style', 'background-image: url("' + data[i].avatar + '")'); //更改行业精英的头像
                        $name.eq(i).text(data[i].title); //获取行业精英的名字
                        $position.eq(i).text(data[i].honor); //获取头衔
                        $introduction.eq(i).html(getText(HTMLDecode(data[i].message)));
                        //缺个人介绍
                        //正则表达式匹配html标签
                        function getText(htmlContent) {
                            var re = new RegExp(/(<("[^"]*"|'[^']*'|[^'">])*>|&nbsp;)/, 'g');//匹配出所有标签和内容
                            var result = htmlContent.replace(re, "");
                            return result;
                        }
                        //html标签转义
                        function HTMLDecode(text) {
                            var temp = document.createElement("div");
                            temp.innerHTML = text;
                            var output = temp.innerText || temp.textContent; //如果能顺利转就取标签加内容，不能就直接取内容
                            temp = null;
                            return output;
                        }
                    }
                },
                error: function () {
                    console.log('get industry elite fail!');
                }
            })
            //行业精英内容更换结束
        }

        $infoCenter = $url.match('infomationCenterDetail')
        if ($infoCenter != null) {
            $('#common-nav-info-center').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
            //资讯中心的内容ajax
        }

        $project = $url.match('researchProject');
        if ($project != null) {
            $('#common-nav-project').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
        }

        $industryElite = $url.match('industryElite');
        if ($industryElite != null) {
            $('#common-nav-industry-elite').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
        }

        $forum = $url.match('forum');
        if ($forum != null) {
            $('#common-nav-forum').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
        }

        $aboutUs = $url.match('aboutUs');
        if ($aboutUs != null) {
            $('#common-nav-about-us').attr('style', 'border-bottom:5px solid rgb(8,147,214)');
        }

    }
    determineTheBlueBorder();
    //URL处理结束

    //处理尾部，如果页面内容少，让尾部固定在底部，多则不用理
    var determineTheFooterPosition = function () {
        // $("#footer").style.cssText="";
        $.ajax({
            url: ' http://wecenter.shabby-wjt.cn:8081/api/advisory/libInfo/number',
            method: 'GET',
            contentType: "application/x-www-form-urlencoded",
            dataType: 'JSON',
            success: function (response) {
                //alert(response);
            },
            error: function () {
                // alert('fail!');
            }
        })
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



    // var getInfoCenterDetail=function(){
    //     alert('开始请求');
    //     $.ajax({
    //         type:"GET",
    //         url:"http://wecenter.shabby-wjt.cn:8133/api/advisory/industryInfo/",
    //         // dataType:"text/html",
    //         contentType:'application/x-www-form-urlencoded',
    //         success:function(response){
    //             alert('success'+ ": "+response);
    //         },
    //         error:function()
    //         {
    //             alert('fail');
    //         }
    //     })
    // }
    // function search(){
    // if(window.location.href.match('index')!=null)
    //     {
    //         getInfoCenterDetail();
    //     }
    // }
    // search();


    //这个是资讯中心右上角“研究中心资讯 | 行业资讯”点击时的JS代码切换
    var infoCenterBodyHeaderChoices = function (eventNode) {
        $(eventNode).attr('style', 'color:rgb(8,147,214);')
    }
    var infoCenterBodyHeaderRemoveChoices = function (eventNode) {
        $(eventNode).siblings().removeAttr('style');
    }
    $('#info-center-detail-body-header-right-first').click(function (event) {
        infoCenterBodyHeaderChoices(event.target);
        infoCenterBodyHeaderRemoveChoices(event.target);
        $('#info-center-article-list-research-info-id').show();
        $('#info-center-article-list-industry-info-id').hide();
        $('#info-center-article-content-research-room').show(); //研究中心资讯
        $('#info-center-article-content-industry').hide(); //行业资讯
        $('#info-center-detail-page-id').hide();
        $('#info-center-pagination-id').show(); //分页条
    });
    $('#info-center-detail-body-header-right-second').click(function (event) {
        infoCenterBodyHeaderChoices(event.target);
        infoCenterBodyHeaderRemoveChoices(event.target);
        $('#info-center-article-list-research-info-id').hide();
        $('#info-center-article-list-industry-info-id').show();
        $('#info-center-article-content-research-room').hide();
        $('#info-center-article-content-industry').show();
        $('#info-center-detail-page-id').hide()
        $('#info-center-pagination-id').show(); //分页条
    })

    //资讯中心右上角“研究中心资讯 | 行业资讯”点击时的JS代码切换结束

    //研究项目右上角切换功能
    //要结合滚动显示效果，所以放到另外一个文件researchProject.js里面

    $('#research-project-detail-body-header-right-first').click(function (event) {
        infoCenterBodyHeaderChoices(event.target);
        infoCenterBodyHeaderRemoveChoices(event.target);
        $('#current-research-project-id').show();
        $('#research-project-gain-id').hide();
        $('#research-project-detail-page-id').hide();
        $('#footer-top-id').text('');
        if ($('#footer-top-id').offset().top > 4000) //后期注意修改
        {
            $('#footer-top-id').text('没有更多内容了...');
        }

    });
    $('#research-project-detail-body-header-right-second').click(function (event) {
        infoCenterBodyHeaderChoices(event.target);
        infoCenterBodyHeaderRemoveChoices(event.target);
        $('#current-research-project-id').hide();
        $('#research-project-detail-page-id').hide();
        $('#research-project-gain-id').show();
        $('#footer-top-id').text('');
        if ($('#footer-top-id').offset().top > 2300) //后期注意修改
        {
            $('#footer-top-id').text('没有更多内容了...');
        }

    });


    //研究中心点击事件查看详情方法
    $('.current-research-project-card').click(function () {
        $('#current-research-project-id').hide();
        $('#research-project-gain-id').hide();
        $('#research-project-detail-page-id').show();
    });
    $(' .research-project-gain-card').click(function () {
        $('#current-research-project-id').hide();
        $('#research-project-gain-id').hide();
        $('#research-project-detail-page-id').show();
    })
    //研究中心点击时间结束
    //研究项目右上角切换功能结束





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












    //资讯中心查看详情按钮点击事件
    $('.look-in-detail').click(function () {
        $('#info-center-detail-page-id').show()
        $('#info-center-article-content-research-room').hide(); //研究中心资讯
        $('#info-center-article-content-industry').hide();
        $('#info-center-pagination-id').hide();
    });
    //资讯中心按钮点击事件结束



    //行业精英获取信息，点击刷新的行业精英的内容
    $('#industry-elite-personal-detail-introduction-first').click(function () {
        var $industryEliteFirstPageHtml = "";
        var industryElitePersonalDetailIntroduction = "";
        // $.ajax({
        //     url: "",
        //     method: "",
        //     data: "", //追加的参数
        //     dataType: "", //返回数据的格式
        //     contentType: "", //发送数据的格式
        //     success: function (response) {
        //         //带有$符号的变量只是用来识别这个是DOM对象
        //         //准备自动匹配去掉所有的HTML
        //         $industryEliteFirstPageHtml= $('#industry-elite-list-body-article-id').html(); //点击时获取全部的html，存储起来
        //         industryElitePersonalDetailIntroduction = '<img src="' + response.avatar+'" alt="something wrong"/>'+
        //                                                     '<div class="industry-elite-personal-detail-article>'+response.message+'</div>';
        //         $('#industry-elite-list-body-article-id').html(industryElitePersonalDetailIntroduction);//刷新页面
        //     },
        //     error: function () {}
        // });//ajax请求结束
        //下面代码仅做调试用
        var $test = '<div id="industry-elite-personal-detail-page"><div class="industry-elite-personal-detail-img"></div> <div class="industry-elite-personal-detail-article">' +
            '<span class="industry-elite-personal-detail-article-name">姜立军</span><span class="industry-elite-personal-detail-article-position">IDRC教授</span>' +
            '<span class="industry-elite-personal-detail-article-title">主要成就</span><p> 早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表</p>' +
            '<p>近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发</p>' +
            '<span class="industry-elite-personal-detail-article-title">获奖情况</span> ' +
            '<p>2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程</p>' +
            '</div></div>'
        $('#industry-elite-list-body-article-id').html($test);
        //调试代码结束
    })
    //第一个人结束
    //第二个人开始
    $('#industry-elite-personal-detail-introduction-second').click(function () {
        var $industryEliteFirstPageHtml = "";
        var industryElitePersonalDetailIntroduction = "";
        // $.ajax({
        //     url: "",
        //     method: "",
        //     data: "", //追加的参数
        //     dataType: "", //返回数据的格式
        //     contentType: "", //发送数据的格式
        //     success: function (response) {
        //         //带有$符号的变量只是用来识别这个是DOM对象
        //         //准备自动匹配去掉所有的HTML
        //         $industryEliteFirstPageHtml= $('#industry-elite-list-body-article-id').html(); //点击时获取全部的html，存储起来
        //         industryElitePersonalDetailIntroduction = '<img src="' + response.avatar+'" alt="something wrong"/>'+
        //                                                     '<div class="industry-elite-personal-detail-article>'+response.message+'</div>';
        //         $('#industry-elite-list-body-article-id').html(industryElitePersonalDetailIntroduction);//刷新页面
        //     },
        //     error: function () {}
        // });//ajax请求结束
        //下面代码仅做调试用
        var $test = '<div id="industry-elite-personal-detail-page"><div class="industry-elite-personal-detail-img"></div> <div class="industry-elite-personal-detail-article">' +
            '<span class="industry-elite-personal-detail-article-name">姜立军</span><span class="industry-elite-personal-detail-article-position">IDRC教授</span>' +
            '<span class="industry-elite-personal-detail-article-title">主要成就</span><p> 早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表</p>' +
            '<p>近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发</p>' +
            '<span class="industry-elite-personal-detail-article-title">获奖情况</span> ' +
            '<p>2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程</p>' +
            '</div></div>'
        $('#industry-elite-list-body-article-id').html($test);
        //调试代码结束
    })
    //第二个人结束

    //第三个人开始
    $('#industry-elite-personal-detail-introduction-third').click(function () {
        var $industryEliteFirstPageHtml = "";
        var industryElitePersonalDetailIntroduction = "";
        // $.ajax({
        //     url: "",
        //     method: "",
        //     data: "", //追加的参数
        //     dataType: "", //返回数据的格式
        //     contentType: "", //发送数据的格式
        //     success: function (response) {
        //         //带有$符号的变量只是用来识别这个是DOM对象
        //         //准备自动匹配去掉所有的HTML
        //         $industryEliteFirstPageHtml= $('#industry-elite-list-body-article-id').html(); //点击时获取全部的html，存储起来
        //         industryElitePersonalDetailIntroduction = '<img src="' + response.avatar+'" alt="something wrong"/>'+
        //                                                     '<div class="industry-elite-personal-detail-article>'+response.message+'</div>';
        //         $('#industry-elite-list-body-article-id').html(industryElitePersonalDetailIntroduction);//刷新页面
        //     },
        //     error: function () {}
        // });//ajax请求结束
        //下面代码仅做调试用
        var $test = '<div id="industry-elite-personal-detail-page"><div class="industry-elite-personal-detail-img"></div> <div class="industry-elite-personal-detail-article">' +
            '<span class="industry-elite-personal-detail-article-name">姜立军</span><span class="industry-elite-personal-detail-article-position">IDRC教授</span>' +
            '<span class="industry-elite-personal-detail-article-title">主要成就</span><p> 早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表</p>' +
            '<p>近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发</p>' +
            '<span class="industry-elite-personal-detail-article-title">获奖情况</span> ' +
            '<p>2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程</p>' +
            '</div></div>'
        $('#industry-elite-list-body-article-id').html($test);
        //调试代码结束
    })
    //第三个人结束

    //第四个人开始
    $('#industry-elite-personal-detail-introduction-forth').click(function () {
        var $industryEliteFirstPageHtml = "";
        var industryElitePersonalDetailIntroduction = "";
        // $.ajax({
        //     url: "",
        //     method: "",
        //     data: "", //追加的参数
        //     dataType: "", //返回数据的格式
        //     contentType: "", //发送数据的格式
        //     success: function (response) {
        //         //带有$符号的变量只是用来识别这个是DOM对象
        //         //准备自动匹配去掉所有的HTML
        //         $industryEliteFirstPageHtml= $('#industry-elite-list-body-article-id').html(); //点击时获取全部的html，存储起来
        //         industryElitePersonalDetailIntroduction = '<img src="' + response.avatar+'" alt="something wrong"/>'+
        //                                                     '<div class="industry-elite-personal-detail-article>'+response.message+'</div>';
        //         $('#industry-elite-list-body-article-id').html(industryElitePersonalDetailIntroduction);//刷新页面
        //     },
        //     error: function () {}
        // });//ajax请求结束
        //下面代码仅做调试用
        var $test = '<div id="industry-elite-personal-detail-page"><div class="industry-elite-personal-detail-img"></div> <div class="industry-elite-personal-detail-article">' +
            '<span class="industry-elite-personal-detail-article-name">姜立军</span><span class="industry-elite-personal-detail-article-position">IDRC教授</span>' +
            '<span class="industry-elite-personal-detail-article-title">主要成就</span><p> 早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表</p>' +
            '<p>近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发</p>' +
            '<span class="industry-elite-personal-detail-article-title">获奖情况</span> ' +
            '<p>2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程</p>' +
            '</div></div>'
        $('#industry-elite-list-body-article-id').html($test);
        //调试代码结束
    })
    //第四个人结束


    //第五个人开始
    $('#industry-elite-personal-detail-introduction-fifth').click(function () {
        var $industryEliteFirstPageHtml = "";
        var industryElitePersonalDetailIntroduction = "";
        // $.ajax({
        //     url: "",
        //     method: "",
        //     data: "", //追加的参数
        //     dataType: "", //返回数据的格式
        //     contentType: "", //发送数据的格式
        //     success: function (response) {
        //         //带有$符号的变量只是用来识别这个是DOM对象
        //         //准备自动匹配去掉所有的HTML
        //         $industryEliteFirstPageHtml= $('#industry-elite-list-body-article-id').html(); //点击时获取全部的html，存储起来
        //         industryElitePersonalDetailIntroduction = '<img src="' + response.avatar+'" alt="something wrong"/>'+
        //                                                     '<div class="industry-elite-personal-detail-article>'+response.message+'</div>';
        //         $('#industry-elite-list-body-article-id').html(industryElitePersonalDetailIntroduction);//刷新页面
        //     },
        //     error: function () {}
        // });//ajax请求结束
        //下面代码仅做调试用
        var $test = '<div id="industry-elite-personal-detail-page"><div class="industry-elite-personal-detail-img"></div> <div class="industry-elite-personal-detail-article">' +
            '<span class="industry-elite-personal-detail-article-name">姜立军</span><span class="industry-elite-personal-detail-article-position">IDRC教授</span>' +
            '<span class="industry-elite-personal-detail-article-title">主要成就</span><p> 早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表</p>' +
            '<p>近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发</p>' +
            '<span class="industry-elite-personal-detail-article-title">获奖情况</span> ' +
            '<p>2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程</p>' +
            '</div></div>'
        $('#industry-elite-list-body-article-id').html($test);
        //调试代码结束
    })
    //第五个人结束


    //第六个人开始
    $('#industry-elite-personal-detail-introduction-sixth').click(function () {
        var $industryEliteFirstPageHtml = "";
        var industryElitePersonalDetailIntroduction = "";
        // $.ajax({
        //     url: "",
        //     method: "",
        //     data: "", //追加的参数
        //     dataType: "", //返回数据的格式
        //     contentType: "", //发送数据的格式
        //     success: function (response) {
        //         //带有$符号的变量只是用来识别这个是DOM对象
        //         //准备自动匹配去掉所有的HTML
        //         $industryEliteFirstPageHtml= $('#industry-elite-list-body-article-id').html(); //点击时获取全部的html，存储起来
        //         industryElitePersonalDetailIntroduction = '<img src="' + response.avatar+'" alt="something wrong"/>'+
        //                                                     '<div class="industry-elite-personal-detail-article>'+response.message+'</div>';
        //         $('#industry-elite-list-body-article-id').html(industryElitePersonalDetailIntroduction);//刷新页面
        //     },
        //     error: function () {}
        // });//ajax请求结束
        //下面代码仅做调试用
        var $test = '<div id="industry-elite-personal-detail-page"><div class="industry-elite-personal-detail-img"></div> <div class="industry-elite-personal-detail-article">' +
            '<span class="industry-elite-personal-detail-article-name">姜立军</span><span class="industry-elite-personal-detail-article-position">IDRC教授</span>' +
            '<span class="industry-elite-personal-detail-article-title">主要成就</span><p> 早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表早期主要从事计算机辅助几何造型的研究，对于计算机三维造型、虚拟现实和 图像处理技术进行了长期的研究，尤其对于曲面表</p>' +
            '<p>近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发近年来，在过程质量监控方面，研究了离散型制造业的实时在线质量监控， “制造业生产现场质量管理支持平台的研究与开发</p>' +
            '<span class="industry-elite-personal-detail-article-title">获奖情况</span> ' +
            '<p>2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程2003-2008年，参与“建筑制图课程 多媒体教学的研究 与实践”教学项目 1 项，所在课程被评为国家精品课程</p>' +
            '</div></div>'
        $('#industry-elite-list-body-article-id').html($test);
        //调试代码结束
    })
    //第六个人结束

    //行业精英获取信息结束
})