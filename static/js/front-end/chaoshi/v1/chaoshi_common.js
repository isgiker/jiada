
/**
 * 返回text文本数据
 * @param url
 * @param param
 * @param successDivId
 * @param messageDivId
 * @return
 */
function callText(url, param, successDivId, messageDivId) {
    return call("text", url, param, successDivId, messageDivId);
}

/**
 * 返回json数据
 * @param url
 * @param param
 * @param successDivId
 * @param messageDivId
 * @return
 */
function callJson(url, param, successDivId, messageDivId) {
    return call("json", url, param, successDivId, messageDivId);
}
/**
 * 订单异步操作请求服务器Call
 * @param actionType 请求参数类型
 * @param url
 * @param param
 * @param successDivId
 * @param messageDivId
 */
function call(callType, url, param, successDivId, messageDivId) {
    var isReturn = true;
    var isSuccess = false;
    var result = null;
    if (url.indexOf("?") == -1) {
        url = url + "?rid=" + Math.random();
    }
    else {
        url = url + "&rid=" + Math.random();
    }
    jQuery.ajax({
        type: "POST",
        dataType: callType,
        url: url,
        data: param,
        async: false,
        success: function(dataResult, textStatus) {
            //服务器返回异常处理,如果有消息div则放入,没有则弹出
            if (textStatus == 5000) {
                if (messageDivId != null) {
                    isReturn = false;
                    $("#" + messageDivId).html(dataResult);
                } else {
                    alert(dataResult);
                    isReturn = false;
                }
            }
            //成功后如果有divID直接放入div，没有则返回结果
            else {
                if (successDivId != null) {
                    isReturn = false;
                    $("#" + successDivId).html(dataResult);
                    isSuccess = true;
                }
                else {
                    result = dataResult;
                }
            }
        },
        error: function(XMLHttpResponse) {
            alert("系统繁忙，请稍后再试！");
            return false;
        }
    });
    //如果没有处理结果则返回
    if (isReturn) {
        return result;
    }
    //如果div成功赋值后则返回ture
    if (isSuccess) {
        return true;
    }
}

/**
 * 判断是否是空
 * @param value
 */
function isEmpty(value) {
    if (value == null || value == "" || value == "undefined" || value == undefined || value == "null") {
        return true;
    }
    else {
        value = value.replace(/\s/g, "");
        if (value == "") {
            return true;
        }
        return false;
    }
}

/**
 * 判断是否是数字
 */
function isNumber(value) {
    if (isNaN(value)) {
        return false;
    }
    else {
        return true;
    }
}

/**
 * 只包含中文和英文
 * @param cs
 * @returns {Boolean}
 */
function isGbOrEn(value) {
    var regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
    var re = new RegExp(regu);
    if (value.search(re) != -1) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检查邮箱格式
 * @param email
 * @returns {Boolean}
 */
function check_email(email) {
    if (email) {
        var myReg = /(^\s*)\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*(\s*$)/;
        if (!myReg.test(email)) {
            return false;
        }
        return true;
    }
    return false;
}

/**
 * 检查手机号码
 * @param mobile
 * @returns {Boolean}
 */
function check_mobile(mobile) {
    var regu = /^\d{11}$/;
    var re = new RegExp(regu);
    if (!re.test(mobile)) {
        return  false;
    }
    return true;
}

/**
 * 验证电话号码，带"(,),-"字符和数字其他不通过
 * @param str
 * @returns {Boolean}
 */
function checkPhone(str) {
    if (str.length > 20) {
        return false;
    }
    var patternStr = "(0123456789-)";
    var strlength = str.length;
    for (var i = 0; i < strlength; i++) {
        var tempchar = str.substring(i, i + 1);
        if (patternStr.indexOf(tempchar) < 0) {
            return false;
        }
    }
    return true;
}


/**
 * 验证银行账户，带"(, ),-"字符和数字其他不通过
 * @param str
 * @returns {Boolean}
 */
function checkBankCount(str) {
    if (str.length > 50) {
        return false;
    }
    var patternStr = "(0123456789- )";
    var strlength = str.length;
    for (var i = 0; i < strlength; i++) {
        var tempchar = str.substring(i, i + 1);
        if (patternStr.indexOf(tempchar) < 0) {
            return false;
        }
    }
    return true;
}

//正则
function trimTxt(txt) {
    return txt.replace(/(^\s*)|(\s*$)/g, "");
}
/**
 * 检查是否含有非法字符
 * @param temp_str
 * @returns {Boolean}
 */
function is_forbid(temp_str) {
    temp_str = trimTxt(temp_str);
    temp_str = temp_str.replace('*', "@");
    temp_str = temp_str.replace('--', "@");
    temp_str = temp_str.replace('/', "@");
    temp_str = temp_str.replace('+', "@");
    temp_str = temp_str.replace('\'', "@");
    temp_str = temp_str.replace('\\', "@");
    temp_str = temp_str.replace('$', "@");
    temp_str = temp_str.replace('^', "@");
    temp_str = temp_str.replace('.', "@");
    temp_str = temp_str.replace(';', "@");
    temp_str = temp_str.replace('<', "@");
    temp_str = temp_str.replace('>', "@");
    temp_str = temp_str.replace('"', "@");
    temp_str = temp_str.replace('=', "@");
    temp_str = temp_str.replace('{', "@");
    temp_str = temp_str.replace('}', "@");
    var forbid_str = new String('@,%,~,&');
    var forbid_array = new Array();
    forbid_array = forbid_str.split(',');
    for (i = 0; i < forbid_array.length; i++) {
        if (temp_str.search(new RegExp(forbid_array[i])) != -1)
            return false;
    }
    return true;
}

/**
 * 检查数量
 * @param txtObj
 * @returns {Number}
 */
function checkLength(txtObj) {
    var val = txtObj;
    var valLength = 0;
    for (var ii = 0; ii < val.length; ii++) {
        var word = val.substring(ii, 1);
        if (/[^\x00-\xff]/g.test(word)) {
            valLength += 2;
        } else {
            valLength++;
        }
    }
    return valLength;
}