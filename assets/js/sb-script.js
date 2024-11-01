(function( $ ) {
var showing = 1;
var limit = $('.banner_info').length;
var duration = $(".sliding-banner").attr("duration");
if(duration=="") duration = 20;
var durationms = duration*1000;
$(".banner_info").hide();
$(".banner_info_"+showing).show();
setInterval(function() {
showing++;
if(showing > limit){
	showing = 1;
}
$(".banner_info").hide();
$(".banner_info_"+showing).show();
}, durationms);
})( jQuery );
