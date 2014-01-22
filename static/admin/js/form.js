/*
 form 验证以及异步提交
 */

/**
 异步提交	
 */

function ajaxSubmit(form) {
    $.ajax({
        url: form.attr('action'),
        data: form.serialize(),
        type: form.attr('method'),
        dataType: 'json',
        success: function(data) {
            ajax_Callback(data);
        }
    })
}

function ajax_Callback(data) {
    if (data.result == "ok") {
        var win;
        if (window.location == top.window.location) {
            win = window;
        } else {
            win = top.window;
        }
        if (!data.url) {
            data.url = win.location;
        }
        if (data.msg) {
            ac.showMsg(data.msg, data.url);
        } else {
            win.location = data.url;
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
        ac.showError(msg);
        //alert(str);
    }
}

//按照配置信息自动获取数据

function autoValidate(form) {
    $form = $(form);
    var reg = $form.find('input[data-rule-regex]');
    if (reg.length) {
        $.validator.addMethod("regex", function(value, input) {
            var re = eval($(input).data('rule-regex'));
            return re.test(value);
        }, '数据格式错误');
    }
    var valObj = {
        errorElement: "div",
        onkeyup: false,
        onclick: false,
        onsubmit: true,
        onfocusout: false,
        focusCleanup: true,
        showErrors: function(errorMap, errorList) {
            var msg = '<ol>';
            for (var err in errorList) {
                msg += '<li>' + errorList[err].message + '</li>'
            }
            msg += '</ol>';
            if (errorList.length) {
                ac.showError(msg);
            }
        }
    }

    if (!$form.hasClass('noajax')) {
        valObj['submitHandler'] = function(form) {
            ajaxSubmit($form);
        }
    }
    $form.validate(valObj);
}

//联动菜单

function LinkageInput(inputs) {
    inputs.bind('change', function() {
        var $this = $(this),
                next = $this.data('next'),
                nextObj = next ? $(next) : null;
        if ($this.val() && nextObj && nextObj.length) {
            inputGetData(nextObj, $this.val());
        } else if ($this.val() == "" && nextObj && nextObj.length) {
            nextObj.find('option[value!=""]').remove();
            nextObj.hide();
            nextObj.trigger('change');
        }
    });
    if ($(inputs[0]).find('ption[value!=""]').length == 0) {
        inputGetData($(inputs[0]), 0);
        //$(inputs[0]).trigger('change');
    }
}

function inputGetData(nextObj, val) {
    if (nextObj.data('url')) {
        $.getJSON(nextObj.data('url'), {
            id: val
        },
        function(data) {
            nextObj.find('option[value!=""]').remove();
            if (data.result == "ok" && data.data) {
                var back = data.data,
                        len = back.length,
                        option = '';
                for (var i = 0; i < len; i++) {
                    option += '<option value="' + back[i].value + '">' + back[i].name + '</option>'
                }
                nextObj.append(option);
                var nextNext = nextObj.data('next'),
                        nextNextObj = nextNext ? $(nextNext) : null;
                //选中默认值
                if (nextObj.data('value')) {
                    nextObj.find('option[value="' + nextObj.data('value') + '"]').attr('selected', true);
                    nextObj.trigger('change');
                } else {
                    //否则隐藏下一个select						
                    if (nextNextObj && nextNextObj.length) {
                        nextNextObj.hide();
                    }
                }
                if (nextNextObj && nextNextObj.length) {
                    nextNextObj.trigger('change');
                }
            }
            nextObj.show();
        }
        );
    }
}
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

$(document).ready(function() {
    var form = $('form.validate');
    if (form.length) {
        form.each(function() {
            //弹层显示错误
            autoValidate(this);
        });
    }
//    页面内显示错误
//        $("#form").validate({
//            debug: true,
//            errorElement: "div",
//            errorPlacement: function(error, element) {
//                $(".showmsg").html(error);
//            }
//        });

});