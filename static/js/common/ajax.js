//添加商品
$("#cateId").change(function() {
    var cateId = $(this).val();
    $.getJSON('/Admin/Goodsbrand/getCateBrand/cateId/'+cateId, function(data) {
        var items = '<option value="" selected>--选择商品品牌--</option>';
        $.each(data, function(key, val) {
            items+=('<option value="'+val.brandId+'">'+val.brandName+'</option>');            
        });
        $('#brandId').html(items);
    });
});
function ajaxForm(form, url) {
    var form = $('#' + form);
    if(!url){
        url = form.attr('action');
    }
    $.ajax({
        cache: false,
        type: form.attr('method'),
        url: url,
        data: form.serialize(), // 你的formid
        async: true,
        dataType: 'json',
        error: function(request) {
            alert("Connection Error" + request);
        },
        success: function(data) {
            if (data.result == "ok") {
                var win;
                if (window.location == top.window.location) {
                    win = window;
                } else {
                    win = top.window;
                }
                if (data.msg) {
                    ac.showMsg(data.msg,data.url);
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

                ac.showError(str);
            }


        }
    });
}

//ac = ajax class
var win = window, ac = {
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
                    ac.showMsg(data.msg,data.url);
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
                ac.showError(msg);
            }
        })
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
        }, 1000)

    },
    showMsg: function(msg, url) {
        var html = '<div id="okInfo" class="border alertInfo">' + '<div class="box">' +
                '<div class="alert">' +
                '<div class="ft18">' + msg + '</div>' +
                '</div>' +
                '<div class="flex">' +
                '<div><input class="inputbox btnorg" onclick="window.location=\'' + url + '\'" type="button" value="确定"></div>' +
                '</div>' +
                '</div>' +
                '</div><div class="mask"></div>';
        $('body').append(html);
        $cen = $('#okInfo');
        win.setTimeout(function() {
            $cen.remove();
            location.href =url;
        }, 1500)
    }
}