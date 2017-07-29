
//  var counter = 3; //变量声明的同时最好初始化
//  var options = [{
//          selector: '#card-first',
//          offset: 400,
//          callback: function (el) {
//              var result = appendNew();
//              if (result == "append failed!") {
//                  $('#footer-top-id').text('没有更多内容了...');
//              }
//          }
//      },
//      {
//          selector: '#card-first',
//          offset: 800,
//          callback: function (el) {
//              appendNew()
//          }
//      },
//      {
//          selector: '#card-first',
//          offset: 1200,
//          callback: function (el) {
//              appendNew()
//          }
//      },
//      {
//          selector: '#card-first',
//          offset: 1600,
//          callback: function (el) {
//              appendNew()
//          }
//      },
//      {
//          selector: '#card-first',
//          offset: 2000,
//          callback: function (el) {
//              appendNew()
//          }
//      },
//      {
//          selector: '#card-first',
//          offset: 2400,
//          callback: function (el) {
//              appendNew()
//          }
//      },
//      {
//          selector: '#card-first',
//          offset: 2800,
//          callback: function (el) {
//              appendNew()
//          }
//      },
//      {
//          selector: '#card-first',
//          offset: 3200,
//          callback: function (el) {
//              var result = appendNew();
//              if (result == "append failed!") {
//                  $('#footer-top-id').text('没有更多内容了...');
//              }
//          }
//      },
//      //上面是研究项目，下面是研究成果的内容
//      {
//          selector: '#project-gain-card',
//          offset: 800,
//          callback: function (el) {
//             projectGain() ;
//          }
//      },
//      {
//          selector: '#project-gain-card',
//          offset: 1200,
//          callback: function (el) {
//              projectGain()
//          }
//      },
//      {
//          selector: '#project-gain-card',
//          offset: 1500,
//          callback: function (el) {
//              var result = projectGain()
//              if (result == "append failed!") {
//                  $('#footer-top-id').text('没有更多内容了...');
//              }
//          }
//      },
//         {
//             selector: '#project-gain-card',
//          offset: 1800,
//          callback: function (el) {
//              var result = projectGain()
//              if (result == "append failed!") {
//                  $('#footer-top-id').text('没有更多内容了...');
//              }
//          }
//         }
//  ];
//  Materialize.scrollFire(options);

//  function appendNew() {
//      var num=$('#current-research-project-id').attr('style');
//      if (counter < num) {
//          $childCard = '<div class="current-research-project-card" id="current-research-project-card-id-' + counter + '">' +
//              '<div class="example-img"></div>' +
//              '<div class="card-introduction">' +
//              '<h1>人工智能技术落地核心驱动力</h1><span>2017-07-22</span>' +
//              '<p>4月24日，搜狗公布了2017年第一季度财报，其中搜狗搜索流量及' +
//              '份额稳固增长，到3月底，整体搜索流量较一年前增长26%.</p>' +
//              '</div>' +
//              '</div>';
//          // Materialize.showStaggeredList($(el));
//          //if(ajax请求失败returnfalse)
//          $('#current-research-project-id').append($childCard);
//          counter++;
//          $childCard = '<div class="current-research-project-card" id="current-research-project-card-id-' + counter + '">' +
//              '<div class="example-img"></div>' +
//              '<div class="card-introduction">' +
//              '<h1>人工智能技术落地核心驱动力</h1><span>2017-07-22</span>' +
//              '<p>4月24日，搜狗公布了2017年第一季度财报，其中搜狗搜索流量及' +
//              '份额稳固增长，到3月底，整体搜索流量较一年前增长26%.</p>' +
//              '</div>' +
//              '</div>';
//          //发ajax请求if(ajax请求失败returnfalse)
//          $('#current-research-project-id').append($childCard);
//          counter++;
//          for (var i = counter - 2; i < counter; i++) {
//              Materialize.fadeInImage($('#current-research-project-card-id-' + i));
//          }
//          return "append success!";
//      } else {
//          return "append failed!";
//      }
//  }

//  var counterProjectGain = 3; // 一开始就有四个卡片了
//  function projectGain() {
//        var num=$('#research-project-gain-id').attr('style');
//      if (counterProjectGain < num) {
//          $childCard = '<div class="research-project-gain-card" id="research-project-gain-card-id-' + counterProjectGain + '">' +
//              '<div class="example-img"></div>' +
//              '<div class="card-introduction">' +
//              '<h1>这是研究成果啦</h1><span>2017-07-22</span>' +
//              '<p>我是SCUT设计学院人机交互设计中心的设计成果啦</p>' +
//              '</div>' +
//              '</div>';
//          // Materialize.showStaggeredList($(el));
//          //if(ajax请求失败returnfalse)
//          $('#research-project-gain-id').append($childCard);
//          counterProjectGain++;
//          $childCard = '<div class="research-project-gain-card" id="research-project-gain-card-id-' +counterProjectGain + '">' +
//              '<div class="example-img"></div>' +
//              '<div class="card-introduction">' +
//              '<h1>这是研究成果啦</h1><span>2017-07-22</span>' +
//              '<p>我是SCUT设计学院人机交互设计中心的设计成果啦</p>' +
//              '</div>' +
//              '</div>';
//          //发ajax请求if(ajax请求失败returnfalse)
//          $('#research-project-gain-id').append($childCard);
        
//          counterProjectGain++;
//          for (var i = counterProjectGain - 2; i < counterProjectGain; i++) {
//              Materialize.fadeInImage($('#research-project-gain-card-id-' + i));
//          }
//          return "append success!";
//      } else {
//          return "append failed!";
//      }

//  }
//  //研究项目滚动监视效果结束