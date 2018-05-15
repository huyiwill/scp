jQuery.extend({
	showMsg: function(msg){
		var html = '<div id="sysMsg" class="lui-modal-body z-99" style="display: block;"><div class="lui-modal-content"></div></div>';
		var modal = $(html);
		modal.find('.lui-modal-content').text(msg);
		$('body').append(modal);
		setTimeout(function(){
			modal.remove();
		},1500);
	},
	showLading: function(){
		var html = '<div id="lading" class="lui-modal-body z-99" style="display: block;"><div class="lui-modal-box"></div><div class="lui-spinner">'
				 + '<div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div>'
				 + '<div class="rect5"></div></div></div>';
		var modal = $(html);
		$('body').append(modal);
	},
	hideLading: function(){
		$('#lading').remove();
	},
	showConfirm: function(callback){
		var html = '<div id="confirm" class="lui-modal-body z-99" style="display: block;"><div class="lui-modal-box"></div>'
				 + '<div class="lui-modal-content"><div class="lui-confirm-title">提示</div><div class="lui-confirm-content">'
				 + '<p>您还没有做完题目，确定要提交吗？</p><p>（未作答的题目记为错题）</p></div><div class="lui-confirm-btn-main">'
				 + '<div class="lui-btn-confirm">确定</div><div class="lui-btn-cancel">取消</div></div></div></div>';
				 
		var result = {};
		var modal = $(html);
		$('body').append(modal);
		
		modal.find('.lui-btn-confirm').on('click',function(){
			result.type = "clickBtn";
			result.index = 1;
			callback(result);
			modal.remove();
		});
		modal.find('.lui-btn-cancel').on('click',function(){
			result.type = "clickBtn";
			result.index = 2;
			callback(result);
			modal.remove();
		});
	},
	showAlert: function(msg){
		var html = '<div id="alert" class="lui-modal-body z-99" style="display: block;"><div class="lui-modal-box"></div>'
				 + '<div class="lui-modal-content"><div class="lui-alert-title">提示</div><div class="lui-alert-content">'
				 + '</div><div class="lui-alert-btn-main"><div class="lui-btn-confirm">确定</div></div></div></div>';
				 
		var modal = $(html);
		modal.find('.lui-alert-content').html(msg);
		$('body').append(modal);
		modal.find('.lui-btn-confirm').on('click',function(){
			modal.remove();
		});
	},
	showForbid: function(msg){
		var html = '<div id="alert" class="lui-modal-body z-99" style="display: block;"><div class="lui-modal-box"></div>'
				 + '<div class="lui-modal-content"><div class="lui-alert-title">提示</div><div class="lui-alert-content">'
				 + '</div></div></div>';
				 
		var modal = $(html);
		modal.find('.lui-alert-content').html(msg);
		$('body').append(modal);
	},
	showInput: function(msg,callback){
		/*
		var html = '<div class="lui-modal-body z-99" style="display: block;"><div class="lui-modal-box"></div>'
				 + '<div class="lui-modal-content"><div class="lui-rel-title"></div>'
				 + '<div class="lui-input-main"><div class="lui-input-list"><div class="lui-input-list-left"></div>'
				 + '<div class="lui-input-list-right"><input style="text" name="name" class="lui-input" placeholder="请输入姓名"/>'
				 + '</div></div><div class="lui-input-list"><div class="lui-input-list-left">*</div><div class="lui-input-list-right">'
				 + '<input style="tel" name="mobile" class="lui-input" placeholder="请输入手机号码"/></div></div></div>'
				 + '<div class="lui-tips"></div><div class="lui-btn">开始评测</div></div></div>';
		*/
		var html = '<div id="relation" class="lui-modal-body z-99" style="display: block;"><div class="lui-modal-box"></div></div>';
		$('body').append(html);
		html = '<div id="relation"><div class="lui-modal-content"><div class="lui-rel-title"></div>'
			 + '<div class="lui-input-main"><div class="lui-input-list"><div class="lui-input-list-left"></div>'
			 + '<div class="lui-input-list-right"><input style="text" name="name" class="lui-input" placeholder="请输入姓名"/>'
			 + '</div></div><div class="lui-input-list"><div class="lui-input-list-left">*</div><div class="lui-input-list-right">'
			 + '<input style="tel" name="mobile" class="lui-input" placeholder="请输入手机号码"/></div></div></div>'
			 + '<div class="lui-tips"></div><div class="lui-btn">开始评测</div></div></div>';
				 
		var modal = $(html);
		modal.find('.lui-rel-title').text(msg.title);
		//modal.find('.lui-rel-text').text(msg.text);
		$('.lui-content').css({'position':'fixed','width':'100%'});
		modal.find('.lui-modal-content').css({'top':$(window).height()*0.5 - 134,'z-index': 100});
		$('body').append(modal).css('overflow-y','hidden');
		modal.find('input[name="name"]').focus();
		
		modal.find('.lui-btn').on('click',function(){
			var name = modal.find('input[name="name"]').val();
			var mobile = modal.find('input[name="mobile"]').val();
			var ismobile = /^1[34578]\d{9}$/;
			if($.trim(mobile).length == 0){
				modal.find('.lui-tips').text('请填写手机号码');
				return;
			}
			if(!ismobile.test(mobile)){
				modal.find('.lui-tips').text('请填写正确的手机号码');
				return;
			}
			$('.lui-content').css({'position':'relative','width':'100%'});
			$('body').css('overflow-y','auto');
			callback(name,mobile);
			modal.remove();
			$('#relation').remove();
		});
	},
	showInput2: function(msg,callback){
		var html = '<div id="relation" class="lui-modal-body z-99" style="display: block;"><div class="lui-modal-box"></div></div>';
		$('body').append(html);
		var html = '<div id="relation"><div class="lui-modal-content"><div class="lui-rel-text"></div><div class="lui-rel-text text2"></div>'
				 + '<div class="lui-input-main"><div class="lui-input-list"><div class="lui-input-list-left"></div>'
				 + '<div class="lui-input-list-right"><input style="text" name="name" class="lui-input" placeholder="请输入姓名"/>'
				 + '</div></div><div class="lui-input-list"><div class="lui-input-list-left">*</div><div class="lui-input-list-right">'
				 + '<input style="tel" name="mobile" class="lui-input" placeholder="请输入手机号码"/></div></div></div>'
				 + '<div class="lui-tips"></div><div class="lui-btn">查看评测</div></div></div>';
				 
		var modal = $(html);
		modal.find('.lui-rel-text').text(msg.title).css('padding','5px 0 0');
		modal.find('.text2').text(msg.title2).css('padding','2px 0 5px');
		$('.lui-content').css({'position':'fixed','width':'100%','overflow-y':'hidden','background-color':'#fff','-moz-filter':'blur(3px)','-webkit-filter':'blur(3px)','-o-filter':'blur(3px)','-ms-filter':'blur(3px)','filter':'blur(3px)'});
		modal.find('.lui-modal-content').css({'top':$(window).height()*0.5 - 134,'z-index': 100});
		$('body').append(modal).css('overflow-y','hidden');
		modal.find('input[name="name"]').focus();
		
		modal.find('.lui-btn').on('click',function(){
			var name = modal.find('input[name="name"]').val();
			var mobile = modal.find('input[name="mobile"]').val();
			var ismobile = /^1[34578]\d{9}$/;
			if($.trim(mobile).length == 0){
				modal.find('.lui-tips').text('请填写手机号码');
				return;
			}
			if(!ismobile.test(mobile)){
				modal.find('.lui-tips').text('请填写正确的手机号码');
				return;
			}
			$('.lui-content').css({'position':'relative','overflow-y':'auto','-moz-filter':'blur(0)','-webkit-filter':'blur(0)','-o-filter':'blur(0)','-ms-filter':'blur(0)','filter':'blur(0)'});
			$('body').css('overflow-y','auto');
			callback(name,mobile);
			modal.remove();
			$('#relation').remove();
		});
	},
	
	
	/*以下为老方法*/
	showBulletin: function(arg){
		var html = '<div id="bulletin" class="modal-body"><div class="modal-box"></div><div class="modal-content">'
				 + '<div class="title-box"><div class="title-text"></div><div class="close-btn-box"><img class="close-'
				 + 'btn" src="../addons/sz_yi/static/images/vote/close.png"/></div></div><div class="notice-content">'
				 + '</div></div></div>';
		var modal = $(html);
		modal.find('.title-text').text(arg.title);
		$.each(arg.content, function(i, v){
			var p = '<p>'+ v +'</p>';
			modal.find('.notice-content').append(p);
		})
		$('body').append(modal);
		modal.find('.close-btn').on('click',function(){
			modal.remove();
		});
	},
	showSheet: function(arg,callback){
		var html = '<div id="sheet" class="modal-body"><div class="modal-box"></div><div class="modal-content"></div></div>';
		var modal = $(html);
		var _html = '';
		$.each(arg, function(i, v){
			var index = i + 2;
			_html += '<div index="'+ index +'" class="option-list">'+ v +'</div>';
		});
		_html += '<div index="1" class="option-list cancel">取消</div>';
		modal.find('.modal-content').html(_html);
		$('body').append(modal);
		modal.find('.modal-content').animate({bottom: '15px'},200);
		
		modal.find('.option-list').on('click',function(){
			var index = $(this).attr('index');
			if(index == 1){
				modal.find('.modal-content').animate({bottom: '-120px'},200);
				setTimeout(function(){
					modal.remove();
				},200);
			}else{
				callback(index);
				modal.remove();
			}
		});
		modal.find('.modal-box').on('click',function(){
			modal.find('.modal-content').animate({bottom: '-120px'},200);
			setTimeout(function(){
				modal.remove();
			},200);
		});
	},
	showImg: function(imgurl){
		var html = '<div id="image" class="modal-body"><div class="modal-box"></div><div class="modal-content"><img class="bigimg" src="'+ imgurl +'"/></div></div>';
		var modal = $(html);
		$('body').append(modal);
		modal.on('click',function(){
			modal.remove();
		});
	},
});