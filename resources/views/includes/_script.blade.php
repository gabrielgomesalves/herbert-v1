<script id="source" language="javascript" type="text/javascript">

$(document).ready(function() {
	$('#gototop').click(function(){
		$('html, body').animate({scrollTop:0}, 'quick');
		return false;
	});

	$('#gotomiddle').click(function(){
		$('html, body').animate({scrollTop:$(document).height() / 2}, 'quick');
		return false;
	});

	$('#gotobottom').click(function(){
		$('html, body').animate({scrollTop:$(document).height()}, 'quick');
		return false;
	});
	
	toastr.options = {
	  "closeButton": false,
	  "debug": false,
	  "newestOnTop": false,
	  "progressBar": true,
	  "positionClass": "toast-top-center",
	  "preventDuplicates": false,
	  "onclick": null,
	  "showDuration": "300",
	  "hideDuration": "1000",
	  "timeOut": "4000",
	  "extendedTimeOut": "1000",
	  "showEasing": "swing",
	  "hideEasing": "linear",
	  "showMethod": "fadeIn",
	  "hideMethod": "fadeOut"
	}
	
	$.expr[":"].contains = $.expr.createPseudo(function(arg) {
		return function( elem ) {
			return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
		};
	});
});

</script>
