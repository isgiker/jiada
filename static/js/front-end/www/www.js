/**
 *发布商品：分类节点联动
 *这是本人写的一个通用的无限极联动菜单
 */
/**
 *发布商品：分类节点联动
 *这是本人写的一个通用的无限极联动菜单
 */
$("select.linkage").change(function() {
    var
            cateId = $(this).val(),
            next = $(this).data('next'),
            nextObj = next ? $(next) : null;
    if (next.length == 0) {
        return false;
    }
    var nextNext = nextObj.data('next'),
            nextNextObj = nextNext ? $(nextNext) : null;
    while (nextNextObj && nextNextObj.length) {
        if (nextNextObj && nextNextObj.length) {
            //根据data-display属性判断是否显示input
            if (nextNextObj.data('display') == 1) {
                nextNextObj.show();
            } else {
                nextNextObj.hide();
            }

            nextNext = nextNextObj.data('next');
            nextNextObj = nextNext ? $(nextNext) : null;
        }
    }
    //先清空历史条目再追加
    nextObj.find('option[value!=""]').remove();

    $.getJSON($(this).data('url') + '/' + cateId, function(data) {

        var items = '';
        $.each(data, function(key, row) {
            items += ('<option value="' + row.id + '">' + row.name + '</option>');
        });
        nextObj.show();
        nextObj.append(items);
//        $(next).trigger('change');
    });
});

/**注册页面
 * ==============================================================================
 */
//添加社区
$(function() {
    $('.add_community').on('click', function() {
        var districtName = $("#node3").find("option:selected").text();
        $("#ptext").val(districtName);
        $("#pid").val($("#node3").val());
        if (!$("#pid").val()) {
            alert('请选择所属区域');
            return false;
        }
        $.layer({
            shade: false,
            type: 1,
            fix: false,
            offset: ['75', ''],
            area: ['400px', '220px', '75px'],
            title: '添加小区',
            border: [5, 0.5, '#666', true],
            page: {dom: '#community_html'},
            success: function() {
                layer.shift('top', 500);

            },
            close: function(index) {
                layer.close(index);
                $('#community_html').hide();
            }

        });
    });
});

$('#addcommunity').submit(function(e) {
    form=$(this);
    $.ajax({
        url: form.attr('action'),
        data: form.serialize(),
        type: form.attr('method'),
        dataType: 'json',
        success: function(data) {
            if (data.result == "ok") {
                var win;
                if (window.location == top.window.location) {
                    win = window;
                } else {
                    win = top.window;
                }
                //
                alert(data.data['areaName']);
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
                ac.showError(str);
//        alert(str);
            }
        }
    })
    return false;
});