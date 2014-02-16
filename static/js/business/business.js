/**
 *发布商品：分类节点联动
 *这是本人写的一个通用的无限极联动菜单
 */
$("select.linkage").change(function() {
    var 
        cateId = $(this).val(),
        next = $(this).data('next'),
        nextObj = next ? $(next) : null;
    if(next.length==0){
        return false;
    }
    var nextNext = nextObj.data('next'),
        nextNextObj = nextNext ? $(nextNext) : null;
        while(nextNextObj && nextNextObj.length){
            if (nextNextObj && nextNextObj.length) {
                nextNextObj.hide();
                nextNext = nextNextObj.data('next');
                nextNextObj = nextNext ? $(nextNext) : null;
            }
        }
        //先清空历史条目再追加
        nextObj.find('option[value!=""]').remove();
        
    $.getJSON($(this).data('url')+'/' + cateId, function(data) {

        var items = '';
        $.each(data, function(key, row) {
            items += ('<option value="' + row.id + '">' + row.name + '</option>');
        });
        nextObj.show();
        nextObj.append(items);
//        $(next).trigger('change');
    });
});


//自定义confrim信息
//配置说明
//<a href="提交路径" class="confirm"  data-method="post|get" data-data="{id:12,a:b}" data-msg="您确定要执行该操作吗？" ></a>

function custConfrim(obj) {
    obj.bind('click', function() {
        var $this = $(this),
                data = $this.data();
        var msg = data['msg'] || '您确定要执行该操作吗？',
                url = $this.data('url'),
                method = data['method'] || 'post',
                data = data['data'] || {};
        var html = '' +
                '<div class="confirmDialog">' +
                '    <div>' +
                '        <div class="desc"><span>' + msg + '</span></div>' +
                '    </div>' +
                '<div>' +
                '    <div class="btnBlack">' +
                '        <input name="ok"  type="button" value="确 定">' +
                '    </div>' +
                '    <div class="btnBlack">' +
                '        <input name="cancel" type="button" value="取 消">' +
                '    </div>' +
                '</div>';
        $html = $(html);
        $html.find('input').bind('click', function() {
            if (this.name == "ok") {
                window.location = url;
            } else {
                $html.remove();
            }
        })
        $('#mainPage').append($html);
        return false;
    })
}

//商品发布，自动计算价格
$(document).ready(function() {
	$('#originalPrice,#discount,#currentPrice').bind('change',function(){
	var id=this.id;
	var originalPrice=parseFloat($('#originalPrice').val()),currentPrice=parseFloat($('#currentPrice').val()),discount=parseFloat($('#discount').val());
	if (!isNaN(originalPrice)){
		if (isNaN(currentPrice)){
			$('#currentPrice').val(originalPrice);
			currentPrice=parseFloat($('#currentPrice').val())			
		}
		if(id=="discount"){
			$('#currentPrice').val(Math.round(originalPrice*discount*100)/100);
		}
		else{
			$('#discount').val(Math.round(currentPrice*100/originalPrice)/100);
		}
	}
	})
});

/*
 打开弹层窗口
 使用方法<a href="打开页面地址（不能是跨域）" class="dialog" data-title="优先标题">标题</a>
 */
function openDialog(dialogs) {
    dialogs.bind('click', function() {
        var $this = $(this),
                title = $this.data('title') || $this.text(),
                url = $this.attr('href');
        if (url.indexOf('?') > 0) {
            url += "&mini=true";
        } else {
            url += '?mini=true';
        }
        art.dialog.open(url, {
            title: title,
            fixed: true,
            padding: 0,
            width: 780,
            lock: true
        });
        return false;
    })
}


$(function() {
    var
            linkage = $('select._node'),
            linkDialog = $('.dialog'),
            confirmInfo = $('a.confirm,input.confirm');

    if (linkage.length > 1) {
        LinkageInput(linkage);
    }
    if (confirmInfo.length) {
        custConfrim(confirmInfo);
    }
    if (linkDialog.length) {
        openDialog(linkDialog);
    }

})
























/*添加仓库，地区联动功能 begin======================================================================*/
$("#provinceId").change(function() {
    var areaId = $(this).val();
    $.getJSON('/Default/Area/ajaxArea/areaId/' + areaId, function(data) {
        var items = '<option value="0" selected>市</option>';
        $.each(data, function(key, val) {
            items += ('<option value="' + val.areaId + '">' + val.areaName + '</option>');
        });
        $('#cityId').html(items);
    });
});
$("#cityId").change(function() {
    var areaId = $(this).val();
    $.getJSON('/Default/Area/ajaxArea/areaId/' + areaId, function(data) {
        var items = '<option value="0" selected>区县</option>';
        $.each(data, function(key, val) {
            items += ('<option value="' + val.areaId + '">' + val.areaName + '</option>');
        });
        $('#districtId').html(items);
    });
});
