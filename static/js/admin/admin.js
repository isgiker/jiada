
/**
 * 后台切换行业
 */
$("#industry_modules").change(function() {
    var industry_modules = $(this).val();
    $.cookie('industry_modules', industry_modules, { expires:0, path:'/'});
    location.reload();
});

/*添加商品，根据分类获取品牌 begin======================================================================*/
$("#cateId").change(function() {
    var cateId = $(this).val();
    $.getJSON('/Chaoshi/Goodsbrand/getCateBrand/cateId/'+cateId, function(data) {
        var items = '<option value="" selected>--选择商品品牌--</option>';
        $.each(data, function(key, val) {
            items+=('<option value="'+val.brandId+'">'+val.brandName+'</option>');            
        });
        $('#brandId').html(items);
    });
});

//自动计算价格
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

/*复选框全选和取消全部 begin======================================================================*/
function   checkAll(e, itemName)
{
    var checkbox = document.getElementsByName(itemName);
    for (var i = 0; i < checkbox.length; i++)
        checkbox[i].checked = e.checked;
} 

/*添加仓库，地区联动功能 begin======================================================================*/
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
                //根据data-display属性判断是否显示input
                if(nextNextObj.data('display')==1){
                    nextNextObj.show();
                }else{
                    nextNextObj.hide();
                }
                
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


/*广告位置 begin======================================================================*/
$("#admId").change(function() {
    var admId = $(this).val();
    var size;
    $.getJSON('/Default/Admodule/ajax_adminfo/admId/'+admId, function(data) {
        var sizeLong,sizeWidth,html;
        if(data.sizeLong){
            sizeLong = data.sizeLong;
        }else{
            sizeLong = '';
        }
        if(data.sizeWidth){
            sizeWidth = data.sizeWidth;
        }else{
            sizeWidth = '';
        }

        html=' ';
        if(sizeLong && sizeWidth){
            size=sizeLong+'X'+sizeWidth; 
            html='尺寸'+size;            
        }
        $('#msize').html(html);
        
    });
});