@extends('app')

@section('content')

<h3>Distribuição Herbert 1.0</h3>

<div class="container">
	<div class="dropzone" id="DropzoneFileUpload">
	<script id="source" language="javascript" type="text/javascript">
		var baseUrl = "{{ route('assignment.automaton_insert', []) }}";
		var token = "{{ csrf_token() }}";
		Dropzone.autoDiscover = false;
		var myDropzone = new Dropzone("div#dropzoneFileUpload", { 
			url: baseUrl,
			paramName: "file", // The name that will be used to transfer the file
			maxFilesize: 5, // MB
			addRemoveLinks: true,
			uploadMultiple: false,
			parallelUploads: 1,
			dictDefaultMessage: "Clique dentro desta caixa para escolher os ficheiros de distribuição a ser utilizado ou arraste-os para dentro desta caixa.",
			params: {
				_token: token
			},
			init: function() {
				
			},
			success: function(file, done) {
				toastr["success"]("Distribuição bem sucedida.");
			},
			error: function(file, done) {
				toastr["error"]("Erro: " + done.error_msg);
			}
		});
	</script>
	</div>
</div>

@endsection