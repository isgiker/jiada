var win = window,
        dom = document,
        cs = {
    //全屏
    fullscreen: function() {
        win.addEventListener('load', function() {
            win.setTimeout(function() {
                win.scrollTo(0, 1);
            })
        })
    },
    //数据处理中心
    dataCenter: {
        getData: function(url, data, cb) {
            $.getJSON(url, data, function(data) {
                if (data.result == "ok") {
                    var win;
                    if (window.location == top.window.location) {
                        win = window;
                    } else {
                        win = top.window;
                    }
                    if ('function' == typeof(cb)) {
                        cb(data.data);
                    }
                    if (data.msg) {
                        cs.showMsg(msg);
                    } else {
                        if (data.url) {
                            win.location = data.url;
                        }
                    }
                } else {
                    var msg = data.msg,
                            str = '';
                    if (typeof(msg) == 'object') {
                        for (var v in msg) {
                            str += msg[v] + '<br>';
                        }
                    } else {
                        str = msg;
                    }
                    cs.showError(msg);
                    //alert(str);
                }
            })
        }
    },
    showMsg: function(msg, url) {
        var html = '<div class="m10 relative"><div class="border "><div class="alertM"><div class="flex"><div class="success"></div><div class="cell cell1">' + msg + '</div></div><div><a href="javascript:window.location=\'' + url + '\';" class="btnOrg bigBtn">确定</a></div></div></div></div>' +
                '</div><div class="mask"></div>';
        $('article').append(html);
    },
    showError: function(msgInfo) {
        var msg = $('#errorMsg');
        if (!msg.length) {
            $('body').append('<div id="errorMsg" />');
            msg = $('#errorMsg');
        }
        msg.html(msgInfo).show();
        win.setTimeout(function() {
            msg.remove()
        }, 3000)
    },
    upCart: function(url, data, fn) {
        cs.dataCenter.getData(url, null, function(data) {
            
            if ('function' == typeof(fn)) {
                fn(data);
            }

        });
    },
    buycart: function() {
        $('#buyThis').bind('click change', function(event) {
            var $this = $(this);
            var pid=$this.attr("product");
            var val=$("#product_amount").val();
            var shopid=$this.attr("shopid");
            var buyCallback = function(data) {
                var r = confirm("商品已成功加入购物车！去购物车结算？")
                if (r == true)
                {
                    location.href="/Cart/Index";
                }
                else
                {
                    return false;
                }
            };
            cs.upCart('/cart/add/cartItem/' + shopid + '-' + pid + '-' + val, null, buyCallback);
            
            
        });
        $('.btnMinus,.btnPlus,.cart-remove,.quantity-text,.quantity-text').bind('click change', function(event) {
            var $this = $(this),
                    btnNum = $this.parents('.quantity-form').find('.quantity-text'),
                    val = parseInt(btnNum.val()),
                    pitem = $this.parents().parent('.item'),
                    cont = $this.parents('.item');

            var goodsCn, originalPriceTotal, currentPriceTotal, orderPriceTotal, payPriceTotal, deliveryFee, jiesheng, actLower;
            var callback2 = function(data) {
                if (typeof data.statistics.orderPriceTotal != 'undefined') {
                    //商品总数量统计
                    goodsCn=data.statistics.goodsCn;
                    //统计
                    originalPriceTotal = data.statistics.originalPriceTotal;
                    currentPriceTotal = data.statistics.currentPriceTotal;
                    //合计
                    orderPriceTotal = data.statistics.orderPriceTotal;
                    //节省
                    jiesheng = data.statistics.jieSheng;
                    //配送费
                    deliveryFee = data.statistics.deliveryFee;
                    
                    //活动返现金额
                    actLower=data.statistics.actLower;

                    //更新应付总金额
                    $('#finalPrice').html('￥'+orderPriceTotal);
                    //更新商品现价合计
                    $('#totalSkuPrice').html('￥'+currentPriceTotal);
                    //更新活动返现金额
                    $('#totalRePrice').html('￥'+actLower);
                    
                    $('#selectedCount').text(goodsCn);
                }
            };
            var callback = function(data) {
                //如果购物车内无数据
                if (!data.goodsCn) {
                    $('.cart-content').remove();
                    $('.cart-empty').show();
                }
                if (val <= 0) {                    
                    if($("div[data-shopid='"+ pitem.data('shopid') +"']").length <=1 ){
                        //删除当前条目
                        cont.remove();
                        //删除店铺条目
                        cont.prev().remove();                        
                        
                    }else{
                        //仅删除当前条目
                        cont.remove();
                    }
//                    var buyItemType = cont.parents('.listInfo'),
//                            parents = cont.parents('.groupInfo'),
//                            itemNum = buyItemType.find('.listItem').length - 1;
//                    if (itemNum == 0) {
//                        buyItemType.prev().remove();
//                        buyItemType.remove();
//                        if (parents.find('.groupType').length == 0) {
//                            win.location = win.location;
//                        }
//                    } else {
//                        var titleBar = buyItemType.prev().find('.break'),
//                                title = $.trim(titleBar.text());
//                        titleBar.text(title.replace(/\(\d\)$/, '(' + itemNum + ')'));
//                    }
//                    cont.remove();
                } else {
                    btnNum.val(val);
                }

                //重新统计
                cs.upCart('/cart/recount', null, callback2);
            };
            //修改个数
            if ($this.hasClass('btnMinus')) {
                //减
                val--;
                cs.upCart('/cart/edit/cartItem/' + pitem.data('shopid') + '-' + pitem.data('pid') + '-' + val, null, callback);

            } else if ($this.hasClass('btnPlus')) {
                //加
                val++;
                cs.upCart('/cart/edit/cartItem/' + pitem.data('shopid') + '-' + pitem.data('pid') + '-' + val, null, callback);

            }else if($this.hasClass('cart-remove')){
                //删除
                val=0;
                cs.upCart('/cart/edit/cartItem/' + pitem.data('shopid') + '-' + pitem.data('pid') + '-' + val, null, callback);
            }else if($this.hasClass('quantity-text') && event.type=='change'){
                //修改input数值
                cs.upCart('/cart/edit/cartItem/' + pitem.data('shopid') + '-' + pitem.data('pid') + '-' + val, null, callback);
            }


        });
        //单选
        $('.checkbox').bind('click', function(event) {
            var $this = $(this),
            pitem = $this.parents().parent('.item');
            if($this.attr( "checked" )){
                pitem.addClass("item_selected");
            }else{
                pitem.removeClass("item_selected");
            }
            
//            alert(pitem.data('pid'));
        });
        //全选|取消
        $('#toggle-checkboxes').bind('click',function(){
            if (this.checked) {
                $("input[name='checkItem']").each(function() {
                    this.checked = true;
                    $(this).parents().parent('.item').addClass("item_selected");
                });
            } else {
                $("input[name='checkItem']").each(function() {
                    this.checked = false;
                    $(this).parents().parent('.item').removeClass("item_selected");
                });
            }

        })
    },
    init: function() {
//        this.getPosition();
        if ($('.btnMinus,.btnPlus,#buyThis').length) {
            this.buycart();
        }
    }
};
win.cs = cs;
win.onload = function() {
    cs.init();
};


jQuery(document).ready(function() {
    var setAmount = {
        min: 1,
        max: 999,
        reg: function(x) {
            return new RegExp("^[1-9]\\d*$").test(x);
        },
        amount: function(obj, mode) {
            var x = $(obj).val();
            if (this.reg(x)) {
                if (mode) {
                    x++;
                } else {
                    x--;
                }
            } else {
//                alert("请输入正确的数量！");
                $(obj).val(1);
                $(obj).focus();
            }
            return x;
        },
        reduce: function(obj) {
            var x = this.amount(obj, false);
            if (x >= this.min) {
                $(obj).val(x);
            } else {
//                alert("商品数量最少为" + this.min);
                $(obj).val(1);
                $(obj).focus();
            }
        },
        add: function(obj) {
            var x = this.amount(obj, true);
            if (x <= this.max) {
                $(obj).val(x);
            } else {
//                alert("商品数量最多为" + this.max);
                $(obj).val(9);
                $(obj).focus();
            }
        },
        modify: function(obj) {
            var x = $(obj).val();
            if (x < this.min || x > this.max || !this.reg(x)) {
//                alert("请输入正确的数量！");
                $(obj).val(1);
                $(obj).focus();
            }
        }
    };
    $("#add").click(function() {
        setAmount.add('#product_amount');
    });
    $("#reduce").click(function() {
        setAmount.reduce('#product_amount');
    });
    $("#product_amount").keyup(function() {
        setAmount.modify('#product_amount');
    });

});