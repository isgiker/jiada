/**
 * 配置公共参数
 * @returns {OrderAppConfig}
 */
function OrderAppConfig() {
}
OrderAppConfig.Domain = "http://trade.jd.com";
OrderAppConfig.ChaoshiDomain = "http://chaoshi.jiada.local";
OrderAppConfig.AsyncDomain = "http://trade.jd.com/async";
OrderAppConfig.LoginUrl = "http://passport.jd.com/new/login.aspx?ReturnUrl=" + OrderAppConfig.Domain + "/order/getOrderInfo.action";
OrderAppConfig.LoginLocUrl = "http://passport.jd.com/new/login.aspx?ReturnUrl=" + OrderAppConfig.Domain + "/order/getLocOrderInfo.action";
OrderAppConfig.Module_Consignee = "consignee";
OrderAppConfig.Module_PayAndShip = "payment-ship";
OrderAppConfig.Module_Shipment = "shipment";
OrderAppConfig.Module_Coupon = "coupons";
OrderAppConfig.Module_GiftCard = "gift";
OrderAppConfig.Module_Invoice = "part-invoice";
OrderAppConfig.Module_SkuList = "span-skulist";


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
    $("#consignee_radio_" + id).parents(".item").find(".item-action").show()
            .removeClass("hide");
}

/**
 * 编辑收货人地址
 * @param consigneeId
 */
function editConsignee() {

    var actionUrl = OrderAppConfig.ChaoshiDomain + "/order/userAddress";

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
            // 没有登录跳登录
            $("#step-1").replaceWith(dataResult);
        },
        error: function(XMLHttpResponse) {
            alert("系统繁忙，请稍后再试！");
            location.reload();
        }
    });
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
        actionUrl = OrderAppConfig.ChaoshiDomain + "/order/addAddress";
        var param = "addressId=" + addressId + "&contact=" + consignee_name
			+ "&provinceId=" + consignee_provinceId
			+ "&cityId=" + consignee_cityId
			+ "&districtId=" + consignee_countyId
			+ "&communityId=" + consignee_communityId
			+ "&address=" + consignee_address
			+ "&mobile=" + consignee_mobile
			+ "&email=" + consignee_email
			+ "&tel=" + consignee_phone;
        alert("新增!");
    } else if (consignee_radio_id && !useNewConsigee && !isHidden && addressId) {
        //编辑事件
        actionUrl = OrderAppConfig.ChaoshiDomain + "/order/editAddress";
        param = "addressId=" + addressId;
        alert("编辑事件!");
    } else if (consignee_radio_id && isHidden && !useNewConsigee) {
        //切换事件
        actionUrl = OrderAppConfig.ChaoshiDomain + "/order/selectedAddress";
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