/**
 * 编辑收货人地址
 * @param consigneeId
 */
function editConsignee() {
    var actionUrl = OrderAppConfig.ChaoshiDomain + "/Order/userAddress";
    var param = '';
    jQuery.ajax({
        type: "GET",
        dataType: "text",
        url: actionUrl,
        data: param,
        cache: false,
        success: function(dataResult, textStatus) {
            if (!dataResult) {
                alert("系统繁忙，请稍后再试！");
                location.reload();
            }

            $("#step-1").replaceWith(dataResult);
            $("#submit_check_info_message").html("<span>您需先保存<a style='color:#005EA7;' href='#consigneeFocus'>收货人信息</a>，再提交订单 <input type='hidden' id='anchor_info' value='consigneeFocus'></span>").show();
            $("#order-submit").attr("class", "checkout-submit-disabled");
            itemListOver.init("#consignee-list");
            var isNewUser = $("#consignee_radio_new").attr("checked");
            if (isNewUser) {
                use_NewConsignee();
            }
        },
        error: function(XMLHttpResponse) {
            alert("系统繁忙，请稍后再试！");
            location.reload();
        }
    });
}

/**
 * 配置公共参数
 * @returns {OrderAppConfig}
 */
function OrderAppConfig() {
}
OrderAppConfig.Domain = "http://trade.jd.com";
OrderAppConfig.ChaoshiDomain = "http://chaoshi.jiada.local";
OrderAppConfig.AsyncDomain = "";
OrderAppConfig.LoginUrl = "";
OrderAppConfig.LoginLocUrl = "";
OrderAppConfig.Module_Consignee = "consignee";
OrderAppConfig.Module_PayAndShip = "payment-ship";
OrderAppConfig.Module_Shipment = "shipment";
OrderAppConfig.Module_Coupon = "coupons";
OrderAppConfig.Module_GiftCard = "gift";
OrderAppConfig.Module_Invoice = "part-invoice";
OrderAppConfig.Module_SkuList = "span-skulist";

//*************************公共方法和变量*************************
var errorUrl ="";
var cartUrl = "";
var lipinkaPhysicalUrl = "";
var orderUrl = "";
var stepLoading = '<div class="step-loading"><div class="loading-style1"><b></b>正在加载中，请稍候...</div></div>';

/**
 * 选择常用收货人地址
 * @param id
 */
function chose_Consignee(id) {
    $("#consignee-form").hide();
    $("#use-new-address").attr("class", "item");
    $("#consignee_radio_" + id).attr("checked", "checked");
    $("#addNumLimitNote").css("display", "none");
    set_CurrentConsignee(id);

}

/**
 * 设置当前常用收货地址变高亮，其他不亮
 * 
 * @param id
 */
function set_CurrentConsignee(id) {
    var parentDiv = $("#consignee_radio_" + id).parent();
    var indexNumCurrent = parentDiv.attr("index").split("_")[2];
    var consigneeList = $("#consignee-list");
    consigneeList.find(".item").each(function() {
        if ($(this).attr("index") == null) {
            $(this).attr("class", "item");
        } else {
            var indexNum = $(this).attr("index").split("_")[2];
            if (indexNumCurrent == indexNum) {
                $(this).attr("class", "item item-selected");
            } else {
                if (parseInt(indexNum) > 5) {
                    $(this).attr("class", "item item-fore hide");
                } else {
                    $(this).attr("class", "item");
                }
                try {
                    $(this).find("span").eq(0).hide();
                } catch (e) {
                }
            }
        }
    });
    $("#consignee_radio_" + id).attr("checked", "checked");
    $("#consignee_radio_" + id).parents(".item").find(".item-action").show().removeClass("hide");
}


/**
 * 保存收货人地址
 * @param consigneeId
 */
function saveConsignee() {
    var useNewConsigee = $("#consignee_radio_new").attr("checked");//判断新增
    var isHidden = $("#consignee-form").is(":hidden");//是否隐藏，判断新增或编辑
    var consignee_radio_id = $("input[name='consignee_radio']:checked").val();//获取收货人id(切换地址或编辑)
    var actionUrl = null;
    var param = '';
    //判断是新增还是编辑地址还是切换地址
    if (!isHidden) {
        var checkConsignee = true;
        // 验证收货人信息是否正确
        if (!check_Consignee("name_div")) {
            checkConsignee = false;
        }
        // 验证地区是否正确
        if (!check_Consignee("area_div")) {
            checkConsignee = false;
        }
        // 验证收货人地址是否正确
        if (!check_Consignee("address_div")) {
            checkConsignee = false;
        }
        // 验证手机号码是否正确
        if (!check_Consignee("call_mobile_div")) {
            checkConsignee = false;
        }
        // 验证电话是否正确
        if (!check_Consignee("call_phone_div")) {
            checkConsignee = false;
        }
        // 验证邮箱是否正确
        if (!check_Consignee("email_div")) {
            checkConsignee = false;
        }
        if (!checkConsignee) {
            return;
        }

        var addressId = $("#addressId").val();//编辑地址时这个值不为空
        consignee_provinceId = $(".consignee_province").find("option:selected").val();
        consignee_cityId = $(".consignee_city").find("option:selected").val();
        consignee_countyId = $(".consignee_county").find("option:selected").val();
        consignee_communityId = $(".consignee_community").find("option:selected").val();
        consignee_name = $("#consignee_name").val();
        consignee_address = $("#consignee_address").val();
        consignee_mobile = $("#consignee_mobile").val();
        consignee_phone = $("#consignee_phone").val();
        consignee_email = $("#consignee_email").val();
        if (consignee_email == null || consignee_email == "undefined") {
            consignee_email = "";
        }
        if (consignee_phone == null || consignee_phone == "undefined") {
            consignee_phone = "";
        }
    }
    if (useNewConsigee && !isHidden) {
        //新增事件        
        actionUrl = OrderAppConfig.ChaoshiDomain + "/Order/addAddress";
        var param = "addressId=" + addressId + "&contact=" + consignee_name
                + "&provinceId=" + consignee_provinceId
                + "&cityId=" + consignee_cityId
                + "&districtId=" + consignee_countyId
                + "&communityId=" + consignee_communityId
                + "&address=" + consignee_address
                + "&mobile=" + consignee_mobile
                + "&email=" + consignee_email
                + "&tel=" + consignee_phone;
    } else if (consignee_radio_id && !useNewConsigee && !isHidden && addressId) {
        //编辑事件
        actionUrl = OrderAppConfig.ChaoshiDomain + "/Order/editAddress";
        var param = "addressId=" + addressId + "&contact=" + consignee_name
                + "&provinceId=" + consignee_provinceId
                + "&cityId=" + consignee_cityId
                + "&districtId=" + consignee_countyId
                + "&communityId=" + consignee_communityId
                + "&address=" + consignee_address
                + "&mobile=" + consignee_mobile
                + "&email=" + consignee_email
                + "&tel=" + consignee_phone;
    } else if (consignee_radio_id && isHidden && !useNewConsigee) {
        //切换事件
        actionUrl = OrderAppConfig.ChaoshiDomain + "/Order/selectedAddress";
        param = "addressId=" + consignee_radio_id;

    } else {
        if (consignee_radio_id == undefined) {
            alert("请选择收货人地址!");
            return;
        }
        if (!actionUrl) {
            return;
        }
    }
    $(".loading").css("display", "block");
    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: actionUrl,
        data: param,
        cache: false,
        success: function(dataResult, textStatus) {
            $(".loading").css("display", "none");
            if (dataResult.result == 'err') {
                if (dataResult.msg) {
                    alert(dataResult.msg);
                }
                return false;
            }
            location.reload();
        },
        error: function(XMLHttpResponse) {
            $(".loading").css("display", "none");
            alert("系统繁忙，请稍后再试！");
            return false;
        }
    });
}


/**
 * 使用新收获人地址
 */
function use_NewConsignee() {
    removeConsingeeMessage();
    $("#use-new-address").attr("class", "item");
    $("#addNumLimitNote").css("display", "none");
    $("#consignee_radio_new").attr("checked", "checked");
    $("#consignee-form").show();
    $('#consignee_form_reset').click();
    $(".consignee_province").empty();
    $(".consignee_city").empty();
    $(".consignee_county").empty();
    $(".consignee_community").empty();
    $(".consignee_province").append("<option value='' selected>请选择：</option>");
    $(".consignee_city").append("<option value=''    selected>请选择：</option>");
    $(".consignee_county").append("<option value=''  selected>请选择：</option>");
    $(".consignee_community").append("<option value=''  selected>请选择：</option>");

    $("#areaNameTxt").text("");
    // #高亮选中
    $("#use-new-address").attr("class", "item item-selected");
    var consigneeList = $("#consignee-list");
    consigneeList.find(".item").each(function() {
        var indexNum = $(this).attr("index").split("_")[2];
        if (parseInt(indexNum) > 5) {
            $(this).attr("class", "item item-fore hide");
        } else {
            $(this).attr("class", "item");
        }
    });
    // 加载省份
    loadProvinces();
}

/**
 * 删除收货人验证提示信息
 */
function removeConsingeeMessage() {
    $("#name_div").removeClass("message");
    $("#area_div").removeClass("message");
    $("#address_div").removeClass("message");
    $("#call_div").removeClass("message");
    $("#email_div").removeClass("message");
    $("#name_div_error").html("");
    $("#area_div_error").html("");
    $("#address_div_error").html("");
    $("#call_div_error").html("");
    $("#email_div_error").html("");
}


/**
 * 验证收货地址消息提示
 * 
 * @param divId
 * @param value
 */
function check_Consignee(divId) {
    var errorFlag = false;
    var errorMessage = null;
    var value = null;
    // 验证收货人名称
    if (divId == "name_div") {
        value = $("#consignee_name").val();
        if (isEmpty(value)) {
            errorFlag = true;
            errorMessage = "请您填写收货人姓名";
        }
        if (value.length > 25) {
            errorFlag = true;
            errorMessage = "收货人姓名不能大于25位";
        }
        if (!is_forbid(value)) {
            errorFlag = true;
            errorMessage = "收货人姓名中含有非法字符";
        }
    }
    // 验证邮箱格式
    else if (divId == "email_div") {
        value = $("#consignee_email").val();
        if (!isEmpty(value)) {
            if (!check_email(value)) {
                errorFlag = true;
                errorMessage = "邮箱格式不正确";
            }
        } else {
            if (value.length > 50) {
                errorFlag = true;
                errorMessage = "邮箱长度不能大于50位";
            }
        }
    }
    // 验证地区是否完整
    else if (divId == "area_div") {
        var provinceId = $(".consignee_province").find("option:selected").val();
        var cityId = $(".consignee_city").find("option:selected").val();
        var countyId = $(".consignee_county").find("option:selected").val();
        var townId = $(".consignee_community").find("option:selected").val();
        // 验证地区是否正确
        if (isEmpty(provinceId) || isEmpty(cityId) || isEmpty(countyId) || isEmpty(townId)) {
            errorFlag = true;
            errorMessage = "请您填写完整的地区信息";
        }
    }
    // 验证收货人地址
    else if (divId == "address_div") {
        value = $("#consignee_address").val();
        if (isEmpty(value)) {
            errorFlag = true;
            errorMessage = "请您填写收货人详细地址";
        }
        if (!is_forbid(value)) {
            errorFlag = true;
            errorMessage = "收货人详细地址中含有非法字符";
        }
        if (value.length > 50) {
            errorFlag = true;
            errorMessage = "收货人详细地址过长";
        }
    }
    // 验证手机号码
    else if (divId == "call_mobile_div") {
        value = $("#consignee_mobile").val();
        divId = "call_div";
        if (isEmpty(value)) {
            errorFlag = true;
            errorMessage = "请您填写收货人手机号码";
        } else {
            if (!check_mobile(value)) {
                errorFlag = true;
                errorMessage = "手机号码格式不正确";
            }
        }
        if (!errorFlag) {
            value = $("#consignee_phone").val();
            if (!isEmpty(value)) {
                if (!is_forbid(value)) {
                    errorFlag = true;
                    errorMessage = "固定电话号码中含有非法字符";
                }
                if (!checkPhone(value)) {
                    errorFlag = true;
                    errorMessage = "固定电话号码格式不正确";
                }
            }
        }
    }
    // 验证电话号码
    else if (divId == "call_phone_div") {
        value = $("#consignee_phone").val();
        divId = "call_div";
        if (!isEmpty(value)) {
            if (!is_forbid(value)) {
                errorFlag = true;
                errorMessage = "固定电话号码中含有非法字符";
            }
            if (!checkPhone(value)) {
                errorFlag = true;
                errorMessage = "固定电话号码格式不正确";
            }
        }
        if (true) {
            value = $("#consignee_mobile").val();
            if (isEmpty(value)) {
                errorFlag = true;
                errorMessage = "请您填写收货人手机号码";
            } else {
                if (!check_mobile(value)) {
                    errorFlag = true;
                    errorMessage = "手机号码格式不正确";
                }
            }
        }
    }
    if (errorFlag) {
        $("#" + divId + "_error").html(errorMessage);
        $("#" + divId).addClass("message");
        return false;
    } else {
        $("#" + divId).removeClass("message");
        $("#" + divId + "_error").html("");
    }
    return true;
}



/**
 * 获取省份列表
 */
function loadProvinces() {

    //接口地址的areaId是parentId
    var actionUrl = OrderAppConfig.ChaoshiDomain + "/Area/nodeArea/areaId/0";
    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: actionUrl,
        data: null,
        cache: false,
        success: function(dataResult, textStatus) {
//                    if (dataResult.result == 'err') {
//                        if (dataResult.msg) {
//                            alert(dataResult.msg);
//                        }
//                    }
            var items = '';
            $.each(dataResult, function(key, row) {
                items += ('<option value="' + row.id + '">' + row.name + '</option>');
            });
            $("#node1").append(items);
        },
        error: function(XMLHttpResponse) {
            alert("系统繁忙，请稍后再试！");
            return false;
        }
    });
}


/**
 * 获取区域列表
 * @param {int} parentId 地区父级id
 * @param {int} id 地区父级id
 * @param {string} areaType 省|市|区|社区
 */
function loadAreas(parentId, id, areaType) {
    var selected;
    //接口地址的areaId是parentId
    var actionUrl = OrderAppConfig.ChaoshiDomain + "/Area/nodeArea/areaId/" + parentId;
    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: actionUrl,
        data: null,
        cache: false,
        success: function(dataResult, textStatus) {
//                    if (dataResult.result == 'err') {
//                        if (dataResult.msg) {
//                            alert(dataResult.msg);
//                        }
//                    }
            var items = '';
            $.each(dataResult, function(key, row) {
                if (id == row.id) {
                    selected = ' selected="selected"';
                } else {
                    selected = '';
                }
                items += ('<option value="' + row.id + '" ' + selected + '>' + row.name + '</option>');
            });
            $("#" + areaType).html(items);
        },
        error: function(XMLHttpResponse) {
            alert("系统繁忙，请稍后再试！");
            return false;
        }
    });
}




/**
 * 编辑常用收货地址,展开对应信息
 */
function editConsigneeDetail(id) {
    // 隐藏20个数量的限制的提示
    $("#addNumLimitNote").css("display", "none");

    $("#consignee-form").show();
    // 设置收货地址详细值
    $("#consignee_radio_" + id).attr("checked", "checked");
    var consignee_id = $("#hidden_consignee_id_" + id).val();
    var consignee_type = $("#hidden_consignee_type_" + id).val();
    var consignee_name = $("#hidden_consignee_name_" + id).val();
    var consignee_provinceName = $("#hidden_consignee_provinceName_" + id).val();
    var consignee_cityName = $("#hidden_consignee_cityName_" + id).val();
    var consignee_countyName = $("#hidden_consignee_countyName_" + id).val();
    var consignee_communityName = $("#hidden_consignee_communityName_" + id).val();
    var consignee_provinceId = $("#hidden_consignee_provinceId_" + id).val();
    var consignee_cityId = $("#hidden_consignee_cityId_" + id).val();
    var consignee_countyId = $("#hidden_consignee_countyId_" + id).val();
    var consignee_communityId = $("#hidden_consignee_communityId_" + id).val();
    var consignee_email = $("#hidden_consignee_email_" + id).val();
    var consignee_mobile = $("#hidden_consignee_mobile_" + id).val();
    var consignee_address = $("#hidden_consignee_address_" + id).val();
    var consignee_phone = $("#hidden_consignee_phone_" + id).val();
    $("#addressId").val(id);
    $("#consignee_type").val(consignee_type);
    $("#consignee_name").val(consignee_name);
    $("#consignee_email").val(consignee_email);
    $("#consignee_phone").val(consignee_phone);
    $("#consignee_mobile").val(consignee_mobile);
    $("#consignee_address").val(consignee_address);
    $("#consignee-form").show();
    // 展开三级地址
    $(".consignee_province").empty();
    $(".consignee_city").empty();
    $(".consignee_county").empty();
    $(".consignee_community").empty();
    $(".consignee_province").append(
            "<option value='" + consignee_provinceId + "' selected >"
            + consignee_provinceName + "</option>");
    $(".consignee_city").append(
            "<option value='" + consignee_cityId + "' selected >"
            + consignee_cityName + "</option>");
    $(".consignee_county").append(
            "<option value='" + consignee_countyId + "' selected >"
            + consignee_countyName + "</option>");
    $(".consignee_community").append(
            "<option value='" + consignee_communityId + "' selected >"
            + consignee_communityName + "</option>");
    $("#use-new-address").attr("class", "item");
    set_CurrentConsignee(id);
    removeConsingeeMessage();
    loadAreas(0, consignee_provinceId, 'node1');
    loadAreas(consignee_provinceId, consignee_cityId, 'node2');
    loadAreas(consignee_cityId, consignee_countyId, 'node3');
    loadAreas(consignee_countyId, consignee_communityId, 'node4');

}


var itemListOver = {
    init: function(selector) {
        this.dom = $(selector);
        this.timeout = null;

        this.bindEvents();
    },
    bindEvents: function() {
        var self = this;

        if (!this.dom.find(".item").length) {
            return;
        }

        this.dom.find(".item").each(function() {
            var $this = $(this);

            $this.find(".hookbox").each(function() {
                $(this).bind("click", function() {
                    $this.find(".hookbox").attr("checked", false);

                    self.dom.find(".item").removeClass("item-selected");
                    self.dom.find(".item .item-action").addClass("hide").hide();

                    $(this).attr("checked", "checked");

                    $this.addClass("item-selected");
                    $this.find(".item-action").removeClass("hide").show();
                });
            });

            if (!!$this.find(".hookbox").attr("checked")) {
                $this.addClass("item-selected");
                $this.find(".item-action").show().removeClass("hide");
            }

            $this.bind("mouseenter", function() {
                if (!$this.find(".hookbox").attr("checked")) {
                    $this.addClass("item-selected");
                    self.timeout = setTimeout(function() {
                        $this.find(".item-action").show().removeClass("hide");
                    }, 50);
                }
            }).bind("mouseleave", function() {
                if (!$this.find(".hookbox").attr("checked")) {
                    clearTimeout(self.timeout);
                    $this.removeClass("item-selected");
                    $this.find(".item-action").hide().addClass("hide");
                }
            });
        });
    }
};



// *************************************************支付和配送方式开始***************************************************************
/**
 * 编辑支付方式
 */
function edit_Payment(flag) {
	$("#payment-ship").css({
		position:"static"
	});
	var actionUrl = OrderAppConfig.ChaoshiDomain+"/Order/payShip";
	var param = '';
	jQuery.ajax( {
		type : "GET",
		dataType : "text",
		url : actionUrl,
		data : param,
		cache : false,
		success: function(dataResult, textStatus) {
                    if (!dataResult) {
                        alert("系统繁忙，请稍后再试！");
                        location.reload();
                    }

                    $("#step-2").replaceWith(dataResult);
                    $("#submit_check_info_message").html("<span>您需先保存<a style='color:#005EA7;' href='#payAndShipFocus'>支付及配送方式</a>，再提交订单  <input type='hidden' id='anchor_info' value='payAndShipFocus'></span>").show();
                    $("#order-submit").attr("class", "checkout-submit-disabled");
                },
                error: function(XMLHttpResponse) {
                    alert("系统繁忙，请稍后再试！");
                    location.reload();
                }
	});
}


/**
 * 用户选中支付方式radio弹出层显示支持与不支持的商品列表
 * @param obj
 */
function highlight(obj) {
    $(obj).parents().children("input").attr('checked',false);
    $(obj).parents().children(".item").removeClass('item-selected');
    $(obj).attr('checked',true);
    $(obj).parents(".item").addClass('item-selected');
}


/**
 * 保存支付与配送方式
 */
function savePayAndShip() {
    $("#payment-ship").css({
        position: "relative"
    });
    var param = "";
    //支付方式
    var payMode = $('input:radio[name="payMode"]:checked').val();
    //配送时间
    var deliveryTimeOption = $('input:radio[name="deliveryTimeOption"]:checked').val();
    //电话确认
    var callToConfirm = $('input:radio[name="callToConfirm"]:checked').val();
    
    var param = "payMode=" + payMode + "&deliveryTimeOption=" + deliveryTimeOption
                + "&callToConfirm=" + callToConfirm;
    
    actionUrl = OrderAppConfig.ChaoshiDomain + "/Order/payShip";
    
    $(".loading").css("display", "block");
    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: actionUrl,
        data: param,
        cache: false,
        success: function(dataResult, textStatus) {
            $(".loading").css("display", "none");
            if (dataResult.result == 'err') {
                if (dataResult.msg) {
                    alert(dataResult.msg);
                }
                return false;
            }
            location.reload();
        },
        error: function(XMLHttpResponse) {
            $(".loading").css("display", "none");
            alert("系统繁忙，请稍后再试！");
            return false;
        }
    });
    
}



/**
 * 提交订单方法
 */
function submitOrder() {
    var actionUrl = OrderAppConfig.ChaoshiDomain + "/Order/submitOrder";
    var param = "";

    // 检查如果存在没保存，则直接弹到锚点
    if (!$("#submit_check_info_message").is(":hidden")) {
        var anchor = $("#anchor_info").val();
        window.location.hash = anchor;
        return;
    }
    
    //检验提交参数
//    var goodsCn=$("#hide_goodsCn").val();
//    var originalPriceTotal=$("#hide_originalPriceTotal").val();
//    var currentPriceTotal=$("#hide_currentPriceTotal").val();
//    var orderPriceTotal=$("#hide_orderPriceTotal").val();
//    var payPriceTotal=$("#hide_payPriceTotal").val();
//    var deliveryFee=$("#hide_deliveryFee").val();
//    var actLower=$("#hide_lower").val();
//    
//    
//    var payMode=$("#hide_payMode").val();
//    var deliveryTime=$("#hide_deliveryTimeOption").val();
//    var callToConfirm=$("#hide_callToConfirm").val();
//    var order_payship_sing=$("#hide_order_payship_sing").val();
//    
//    var contact=$("#hide_contact").val();
//    var mobile=$("#hide_mobile").val();
//    var email=$("#hide_email").val();
//    var province=$("#hide_province").val();
//    var city=$("#hide_city").val();
//    var district=$("#hide_district").val();
//    var community=$("#hide_community").val();
//    var address=$("#hide_address").val();
    

    $(".loading").css("display", "block");
    var originSubmit = $("#order-submit").clone(true);
//    $("#order-submit").replaceWith('');

    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: actionUrl,
        data: param,
        cache: false,
        success: function(dataResult, textStatus) {
            $(".loading").css("display", "none");
            if (dataResult.result == 'err') {
                if (dataResult.msg) {
                    alert(dataResult.msg);
                }
                return false;
            }else if(dataResult.result == 'ok'){
                //如果是在线支付，跳转到支付页面
                if(dataResult.data.payMode == 2){
                    successUrl="/Success/index";
                    window.location.href = successUrl + "?orderNo=" + dataResult.data.orderNo + "&rid=" + Math.random();
                    return;
                }else{
                    successUrl="/Success/index";
                    window.location.href = successUrl + "?orderNo=" + dataResult.data.orderNo + "&rid=" + Math.random();
                    return;
                }
            }
            location.reload();
        },
        error: function(XMLHttpResponse) {
            $(".loading").css("display", "none");
            alert("系统繁忙，请稍后再试！");
            return false;
        }
//        
//        success: function(dataResult) {
//
//            if (dataResult.result.success) {
//                //跳订单中心列表
//                if (result.goJumpOrderCenter) {
//                    successUrl = "http://order.jd.com/center/list.action";
//                    //等待拆单，定时450毫秒
//                    window.setTimeout('window.location.href=successUrl+"?rd="+Math.random();', 450);
//                    return;
//
//                } else {
//                    successUrl = "http://s.trade.jd.com/success/success.action";
//                    window.location.href = successUrl + "?orderId=" + result.orderId + "&rid=" + Math.random();
//                    return;
//                }
//
//            } else {
//                if (result.message != null) {
//                    if (result.message.indexOf("商品无货") != -1 && !isLocBuy()) {
//                        var a = result.message.indexOf("编号为");
//                        var b = result.message.indexOf("的商品无货");
//                        var outSkus = result.message.substring(a + 3, b);
//                        // 对无货商品的处理
//                        $("#order-loading").replaceWith(originSubmit);
//                        $("#submit_message").html(result.message);
//                        $("#submit_message").show();
//                        if (!isEmpty(outSkus)) {
//                            if (isLipinkaPhysical()) {
//                                // window.location.href = lipinkaPhysicalUrl;
//                                return;
//                            }
//                            window.location.href = cartUrl + '?outSkus='
//                                    + outSkus + '&rid=' + Math.random();
//                            return;
//                        }
//                    } else if (result.message.indexOf("收货人信息中的省市县选择有误") != -1) {
//                        edit_Consignee();
//                    } else if (result.message.indexOf("由于订单金额较大") != -1) {
//                        $("#order-loading").replaceWith(originSubmit);
//                        $("#submit_message").html(result.message);
//                        $("#submit_message").show();
//                        return;
//                    }
//                    else if (result.message.indexOf("验证码不正确") != -1) {
//                        $("#order-loading").replaceWith(originSubmit);
//                        $("#submit_message").html(result.message);
//                        $("#submit_message").show();
//                        getNextCheckCode();// 刷新验证码
//                        return;
//                    } else if (result.message.indexOf("正在参与预售活动") != -1) {
//                        var a = result.message.indexOf("您购买的商品");
//                        var b = result.message.indexOf("正在参与预售活动");
//                        var outSkus = result.message.substring(a + 6, b);
//                        if (!isEmpty(outSkus)) {
//                            var tmpHtml = "";
//                            var skuList = outSkus.split(",");
//                            for (var i = 0; i < skuList.length; i++) {
//                                tmpHtml = tmpHtml + "<a target=\"_parent\" href=\"http://item.jd.com/" + skuList[i] + ".html\">" + skuList[i] + "</a>,";
//                            }
//                            tmpHtml = tmpHtml.substring(0, tmpHtml.length - 1);
//                            result.message = "您购买的商品" + tmpHtml + "正在参与预售活动,请进入商品详情页单独购买";
//                        }
//                        $("#order-loading").replaceWith(originSubmit);
//                        $("#submit_message").html(result.message);
//                        $("#submit_message").show();
//                    } else {
//                        $("#order-loading").replaceWith(originSubmit);
//                        $("#submit_message").html(result.message);
//                        $("#submit_message").show();
//                        return;
//                    }
//                } else {
//                    $("#order-loading").replaceWith(originSubmit);
//                    $("#submit_message").html("亲爱的用户请不要频繁点击, 请稍后重试...");
//                    $("#submit_message").show();
//                    return;
//                }
//            }
//        },
//        error: function(error) {
//            $("#order-loading").replaceWith(originSubmit);
//            $("#submit_message").html("亲爱的用户请不要频繁点击, 请稍后重试...");
//            $("#submit_message").show();
//        }
    });
}