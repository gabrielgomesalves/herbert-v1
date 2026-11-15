@extends('app')

@section('content')

{!! Html::style('scripts/dropzone/dropzone.min.css') !!}
{!! Html::script('scripts/dropzone/dropzone.min.js') !!}

<h3>Inserir guião</h3>

<div class="container">
	<div class="dropzone" id="DropzoneFileUpload">
	<script id="source" language="javascript" type="text/javascript">
		var baseUrl = "{{ route('script.store', []) }}";
		var token = "{{ csrf_token() }}";
		Dropzone.autoDiscover = false;
		var myDropzone = new Dropzone("div#dropzoneFileUpload", { 
			url: baseUrl,
			paramName: "file", // The name that will be used to transfer the file
			maxFilesize: 5, // MB
			addRemoveLinks: true,
			uploadMultiple: false,
			parallelUploads: 1,
			dictDefaultMessage: "Clique dentro desta caixa para escolher os ficheiros dos guiões a inserir ou arraste-os para dentro desta caixa.",
			params: {
				_token: token
			},
			init: function() {
				//toastr.info('comecou');
			},
			success: function(file, done) {
				file.previewElement.classList.add("dz-success");
				var log = $('#script_log_textarea').val();
				var msg = "Ficheiro: " + done.filename + " - Série: " + done.serie_original_name + " - Episódio: " + done.episode_number + " - " + done.episode_original_name;
				toastr["success"](msg);
				log = log + "[Sucesso]: " + msg + ".\n";
				if($('#script_log_textarea').css("background-color") == "rgb(255, 255, 255)" || $('#script_log_textarea').css("background-color") == "white"){
					$('#script_log_textarea').val(log).css('background-color','#dfffdf').change();
				} else {
					$('#script_log_textarea').val(log).change();
				}
			},
			error: function(file, done) {
				file.previewElement.classList.add("dz-error");
				var log = $('#script_log_textarea').val();
				var msg = "Ficheiro: " + done.filename + " - Série: " + done.serie_original_name + " - Episódio: " + done.episode_number + " - " + done.episode_original_name;
				log = log + "[Erro]: " + msg + " => Linha: " + done.row + " - " + done.error_msg + ".\n";
				toastr["error"](msg);
				$('#script_log_textarea').val(log).css('background-color','#ffdfdf').change();
			}
		});
	</script>
	</div>
</div>

<label>
	<h4>Registro de inserção:</h4>
	<div class="script_log">
		<textarea id="script_log_textarea" class="script_log_textarea" rows="10" cols="20" data-role="none" ></textarea> 
	</div>
<label>

@endsection
