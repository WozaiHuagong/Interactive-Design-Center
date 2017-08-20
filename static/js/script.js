$(function(){
	// 固定导航
	$(window).scroll(function(){
		if ($(window).scrollTop()>150) {
			$('.fixed-nav').fadeIn()
		}else{
			$('.fixed-nav').fadeOut()
		}
	})

	$('#major-comment').click(function(){
		$('.question-box .add-reply-box').fadeToggle()
	})

	$('.share').click(function(){
		$(this).children('.share-dropdown').toggle()
	})


	// 评论
	$('.comment').click(function(){
		$('.comment2').removeClass('active')
		$('.commentFrame').fadeOut(10)
		$(this).toggleClass('active')
		$(this).parent().parent().children('.add-reply-box').fadeToggle()
	})

	$('.comment2').click(function(){
		$('.comment').removeClass('active')
		$('.add-reply-box').fadeOut(100)
		$(this).toggleClass('active')
		$(this).parent().parent().children('.commentFrame').fadeToggle()
	})

	$('.commentFrame textarea').focus(function(){
		$(this).parent().parent().children('.bt-wrap').fadeIn()
	}).blur(function(){
		$(this).parent().parent().children('.bt-wrap').fadeOut()
	})


	// 追问
	$('.ask').click(function(){
		$(this).toggleClass('active')
		$('.askFrame').fadeToggle()
	})

	$('.askFrame textarea').focus(function(){
		$(this).parent().parent().children('.bt-wrap').fadeIn()
	}).blur(function(){
		$(this).parent().parent().children('.bt-wrap').fadeOut()
	})

	// 用户信息框弹出效果
	$('#userinfo-ajax-box').hover(function(){
		$(this).addClass('hover')
		$(this).fadeIn()
	},function(){
		$(this).removeClass('hover')
		$(this).fadeOut(10)
	})

	$('.user-head').each(function(){
		$(this).hover(function(){
			var _newTop = $(this).offset().top + $(this).height() + 5
			var _newLeft = $(this).offset().left
			$('#userinfo-ajax-box').css({
				'top' : _newTop,
				'left' : _newLeft
			})
			$('#userinfo-ajax-box').fadeIn()

		},function(){
			var _ajaxBox = $('#userinfo-ajax-box')
			setTimeout(function(){
				if(!_ajaxBox.hasClass('hover')){
					_ajaxBox.fadeOut(10)
				}
			},10)
		})
	})

	// 关注切换
	$('.focus').each(function(){
		$(this).click(function(){
			if($(this).hasClass('active')){
				var _html = "关注<i class=\"icon-plus\"></i>"
				$(this).removeClass('active')
				$(this).html(_html)
			}else{
				var _html = "取消关注<i class=\"icon-followed\"></i>"
				$(this).addClass('active')
				$(this).html(_html)
			}
		})	
	})

	$('.add-reply-box textarea').focus(function(){
		$(this).parent().parent().children('.bt-wrap').fadeIn()
	}).blur(function(){
		$(this).parent().parent().children('.bt-wrap').fadeOut()
	})
	
	$('.agree_effect').click(function(){
		var _num = parseInt($(this).children('b').text())
		if($(this).hasClass('active')){
			_num -= 1
			$(this).removeClass('active')
			$(this).children('b').html(_num)
		}else{
			_num += 1
			$(this).addClass('active')
			$(this).children('b').html(_num)
		}
	})

	$('.add-reply-box-sm .info>span:last-child').each(function(){
		$(this).click(function(){
			var _username = $(this).prev().children('a').html()
			var _html = '@'+_username+': '
			var _textobj = $(this).parent().parent().parent().parent().next().find('textarea')
			_textobj.focus().val(_textobj.val()+_html)
		})
	})

	$('.commentFrame .info>span:last-child').each(function(){
		$(this).click(function(){
			var _username = $(this).prev().children('a').html()
			var _html = '@'+_username+': '
			var _textobj = $(this).parent().parent().parent().parent().next().find('textarea')
			_textobj.focus().val(_textobj.val()+_html)
		})
	})

	$('.report').click(function(){
		$('.backdrop').css('background','rgba(0,0,0,.2)').fadeIn(150)
		$('.report-box').fadeIn()
	})

	$('.close-bt').click(function(){
		$('.backdrop').fadeOut(150)
		$('.report-box').fadeOut()
	})

	$('.report-box .cancel-bt').click(function(){
		$('.backdrop').fadeOut(150)
		$('.report-box').fadeOut()
	})

	$('.thank').click(function(){
		$('.backdrop').css('background','rgba(255,255,255,.8)').fadeIn(150).addClass('thank-bg')
	//	changeThkWPos()
		$('.thank-way').fadeIn()
	})

	$('.b-thank').click(function(){
		$('.backdrop').css('background','rgba(255,255,255,.8)').fadeIn(150).addClass('thank-bg')
	//	changeThkWPos()
		$('.thank-way').fadeIn()
	})

	$('.support').click(function(){
		$('.backdrop').css('background','rgba(255,255,255,.8)').fadeIn(150).addClass('thank-bg')
		$('.thank-way2').fadeIn()
	})

	$('.thank-way .cancel,.thank-way .cancel-bt,.thank-modal .cancel,.thank-modal .cancel-bt').click(function(){
		$('.backdrop').fadeOut(150)
		$('.thank-way').fadeOut()
		$('.thank-modal').fadeOut()
	})

	$('.thank-way2 .cancel-bt').click(function(){
		$('.backdrop').fadeOut(150)
		$('.thank-way2').fadeOut()
	})
	$('.thank-modal .cancel-bt').click(function(){
		$('.backdrop').fadeOut(150);
		$('.thank-modal').fadeOut();
	});

	/*$('.thank-way2 .submit-bt').click(function(){
		if($(this).prev().val()==""){
			alert("您没有输入赞助金额！")
		}
	})*/

	$('.close-bt').click(function(){
		$('.backdrop').fadeOut(150)
		$('.collect-box').fadeOut()
	})

	$('.bt-close').click(function(){
		$('.backdrop').fadeOut(150)
		$('.collect-box').fadeOut()
	})

	$('.collect').click(function(){
		//alert("收藏成功！")
	})

	// 打赏金额判定
	/*$('.thank-way .confirm').click(function(){
		var sum = 0
		var total = parseInt($('#totalMon i').text())
		$('.thank-list li input').each(function(){
			if($(this).val()!=""){
				sum += parseInt($(this).val());
			}
		})
		if(sum>total){
			alert("您输入的金额超过了打赏的总金额")
		}else if(sum == 0){
			alert("您没有输入打赏金额！")
		}

	})*/

	// 控制加奖弹出框的位置
	function changeThkWPos(ele){
		var clientT = $(window).scrollTop()
		var newTop = ($(window).height()-$('.thank-way').height())/2 + clientT
		$('.thank-way').css('top',newTop)
	}

	// 发文页
	// 加奖阅读全文选项
	$('#readAll').next('b').css('color','#999')
	$('#original').click(function(){
		if($(this).is(':checked')){
			$('#readAll').attr('disabled',false)
			$('#readAll').next('b').css('color','#666')
		}else if(!$(this).is(':checked')){
			//alert('sss');
			$('#readAll').attr('checked',false)
			$('#readAll').attr('onclick','')
			$('.summary_reay').attr('id','hideSection')
			$('#hideSection').fadeOut()
			$('#readAll').attr('disabled',true)
			$('#readAll').next('b').css('color','#999')
		}	
	})

	$('#readAll').click(function(){
		if($(this).is(':checked')){
			$('#hideSection').fadeIn()
			$('.summary_reay').fadeIn()
		}else if(!$(this).is(':checked')){
			$('#hideSection').fadeOut()
			$('.summary_reay').fadeOut()
		}
	})

	// 分类
	$('#classify').change(function(){
		if ($(this).val()==0) {
			$('#elseClassify').fadeIn()
		}else{
			$('#elseClassify').fadeOut()
		}
	})

	// 评论框自动延长
	$('textarea').autosize()

	// 头像切换
	$(".per-info .content-wrap .btn-upload input[type=file]").on('change',function(e){
      	$(this).parent().prev().prev().find("img").attr('src',URL.createObjectURL($(this)[0].files[0]))
    })

    // 用户协议
    $('#bt-protocol').click(function(){
    	$('#protocol').fadeToggle()
    })


});	


