/*mini购物车*/
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
    
//商品分类
$('#_ALLSORT').hide();
$('#categorys').hover(
        function() {
            $('#_ALLSORT').show();
        },
        function() {
            $('#_ALLSORT').hide()
        }
);
//Tabs内容切换 
$(".category .item").click(function() {
    var $this = $(this);

    $this.prevAll().removeClass('current');
    $this.nextAll().removeClass('current');
    
    $this.prevAll().children('h3').attr('class',"cBlock");
    $this.nextAll().children('h3').attr('class',"cBlock");
    
    //为当前元素添加class
    $this.addClass("current");
    $this.children('h3').attr('class',"cOpen");
});
