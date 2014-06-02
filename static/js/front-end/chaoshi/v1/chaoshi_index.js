$('#mini_cart dl').hover(
        function() {
            $('.cart_open').show();
            $('.cart_close').hide();
        },
        function() {
            $('.cart_close').show();
            $('.cart_open').hide();
        }
);
$(".index_nav a").mouseenter(function() {
    var thisFloor = $(this).parents('.mt').parents('.m2').parent();
    var floorId = thisFloor.data('floor');
    var navid = $(this).data('id');
    var navwidth = parseInt(navid) * 199;
    thisFloor.find('.tab-arrow').clearQueue().animate({left: navwidth + 'px'}, 420);

    //隐藏当前元素同级的所有元素内容
    var curNavContent = thisFloor.find('#nav_content_' + floorId + '_' + navid);
    curNavContent.prevAll().hide();
    curNavContent.nextAll().hide();
    //显示当前元素下的内容
    if (curNavContent.length > 0) {
        curNavContent.show();
    }

    //当前a标签颜色
    $(this).parents().find('a').removeClass('hover');
    //为当前元素添加class
    $(this).addClass("hover");

});
//图片轮播
$('#flash1').slideBox({
    duration: 0.3, //滚动持续时间，单位：秒
    easing: 'linear', //swing,linear//滚动特效
    delay: 3, //滚动延迟时间，单位：秒
    hideClickBar: false, //不自动隐藏点选按键
    clickBarRadius: 10
});

//Tabs内容切换 
$(".marketRight_mod_news_tab").mouseover(function() {
    var $this = $(this), tbcontentid = $this.data('tbcontentid');
    $this.parent().parent().find(".content").hide();
    $('#marketRight_mod_news_tab_content_' + tbcontentid).show();
    $this.prevAll().removeClass('cur');
    $this.nextAll().removeClass('cur');
    //为当前元素添加class
    $this.addClass("cur");
});