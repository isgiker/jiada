$(function() {
	document.getElementsByTagName("head")[0].appendChild($('<script src="http://api.map.baidu.com/api?v=2.0&ak=7463442f78f85ee9bc9e7b3b0ff60e6d"></script>')[0]);
	var marker, map;
	$('#lnglat').bind('click', function() {
		var title = "";
		var city = $('#node2')[0],
			counties = $('#node3')[0],
			address = $('#address').val();
                city = city.options[city.selectedIndex].text;
		counties = counties.options[counties.selectedIndex].text;
		if (!city || !counties || !address) {
			art.dialog.alert('请先填写所在地区和详细地址位置信息！');
			return;
		}
		
		title = city + counties + address;
		if (!map) {
			map = art.dialog({
				width: 500,
				height: 400,
				fixed: true,
				content: '<div id="mapContainer" style="width:500px;height:400px"></div>',
				padding: 0,
				lock: true,
				close: function() {
					this.hide();
					return false;
				},
				button: [{
					name: '确定',
					focus: true,
					callback: function() {
						var position = marker.getPosition();
						//var a = position.lng;
						//var b = position.lat;
						//var c = a +','+ b;
						//alert(c);
						$('#lng').val(position.lng);
						$('#lat').val(position.lat);
						$('#lnglat').val(position.lng +','+position.lat);
					}
				}],
				init: function() {
					var map = new BMap.Map("mapContainer");
					var lng = $('#lng').val(),
						lat = $('#lat').val();
					map.addControl(new BMap.NavigationControl({
							anchor: BMAP_ANCHOR_TOP_RIGHT,
							type: BMAP_NAVIGATION_CONTROL_SMALL
						})); //右上角，仅包含平移和缩放按钮
					if (lng == "" || lat == "") {
						map.centerAndZoom(new BMap.Point(116.404, 39.915), 11);					

						// 创建地址解析器实例 
						var myGeo = new BMap.Geocoder();
						// 将地址解析结果显示在地图上，并调整地图视野
						myGeo.getPoint(title, function(point) {
							if (point) {
								var opts = {
									width: 200, // 信息窗口宽度
									height: 60, // 信息窗口高度
									enableMessage: false,
									title: "帮助"
								}
								map.enableDragging();//启用地图拖拽事件，默认启用(可不写)
						        map.enableScrollWheelZoom();//启用地图滚轮放大缩小
						        map.enableDoubleClickZoom();//启用鼠标双击放大，默认启用(可不写)
						        map.enableKeyboard();//启用键盘上下左右键移动地图
								var infoWindow = new BMap.InfoWindow("拖动该红色标记到相应的地理位置", opts);
								map.openInfoWindow(infoWindow, point);
								map.centerAndZoom(point, 16);
								marker = new BMap.Marker(point);
								map.addOverlay(marker);
								marker.enableDragging();
							}
						}, "北京市");
					} else {
						//右上角，仅包含平移和缩放按钮
						var point =new BMap.Point(lng, lat);
						var opts = {
							width: 200, // 信息窗口宽度
							height: 60, // 信息窗口高度
							enableMessage: false,
							title: "帮助"
						}
						var infoWindow = new BMap.InfoWindow("拖动该红色标记到相应的地理位置", opts);
						map.openInfoWindow(infoWindow, point);
						map.centerAndZoom(point, 16);
						marker = new BMap.Marker(point);
						map.enableDragging();//启用地图拖拽事件，默认启用(可不写)
				        map.enableScrollWheelZoom();//启用地图滚轮放大缩小
				        map.enableDoubleClickZoom();//启用鼠标双击放大，默认启用(可不写)
				        map.enableKeyboard();//启用键盘上下左右键移动地图
						map.addOverlay(marker);
						marker.enableDragging();
					}
				}
			});
		} else {
			map.show();
		}

	});
})