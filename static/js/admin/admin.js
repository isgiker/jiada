
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

/*添加仓库，地区联动功能 begin======================================================================*/
$("#provinceId").change(function() {
    var areaId = $(this).val();
    $.getJSON('/Default/Area/ajaxArea/areaId/'+areaId, function(data) {
        var items = '<option value="0" selected>市</option>';
        $.each(data, function(key, val) {
            items+=('<option value="'+val.areaId+'">'+val.areaName+'</option>');            
        });
        $('#cityId').html(items);
    });
});
$("#cityId").change(function() {
    var areaId = $(this).val();
    $.getJSON('/Default/Area/ajaxArea/areaId/'+areaId, function(data) {
        var items = '<option value="0" selected>区县</option>';
        $.each(data, function(key, val) {
            items+=('<option value="'+val.areaId+'">'+val.areaName+'</option>');            
        });
        $('#districtId').html(items);
    });
});
