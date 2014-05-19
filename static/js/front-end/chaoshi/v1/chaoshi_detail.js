$(document).ready(function() {
    $('.jqzoom').jqzoom({
        zoomType: 'standard',
        lens: true,
        preloadImages: false,
        alwaysOn: false
    });

});

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

function resetTabs() {
    $("#pnav_content > div").hide(); //Hide all content
    $("#pnav_tabs a").attr("id", ""); //Reset id's      
}

(function() {
    $("#pnav_content > div").hide(); // Initially hide all content
    $("#pnav_tabs li:first a").attr("id", "current"); // Activate first tab
    $("#pnav_content > div:first").fadeIn(); // Show first tab content

    $("#pnav_tabs a").on("click", function(e) {
        e.preventDefault();
        if ($(this).attr("id") == "current") { //detection for current tab
            return
        }
        else {
            resetTabs();
            $(this).attr("id", "current"); // Activate this
            $($(this).attr('name')).fadeIn(); // Show content for current tab
        }
    });


})()