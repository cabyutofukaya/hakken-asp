// JavaScript Document
$(function(){
	$('.toggleBtn').on('click', function() {
		$(this).next().slideToggle();
		$(this).toggleClass('open');
	});
});