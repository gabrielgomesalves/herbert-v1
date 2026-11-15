@extends('app')

@section('content')

{{--{!! Html::script('scripts/freezeHeader/js/jquery.freezeheader.js') !!}--}}
{!! Html::script('scripts/fileDownload/jquery.fileDownload.js') !!}

<h3>Séries</h3>

{!! Form::open(array('route' => 'serie.index', 'method' => 'get', 'class' => 'form', 'data-ajax' => 'false')) !!}
<div class="serie_slider">
	<select name="series_current_state" id="series_current_state" class="series_current_state" data-role="slider" onChange="this.form.submit();">
		@if($current_state == 0)
			<option value="0" selected>Séries em falta</option>
			<option value="1">Séries concluídas</option>
		@else
			<option value="0" >Séries em falta</option>
			<option value="1" selected>Séries concluídas</option>
		@endif
	</select>
</div>
{!! Form::close() !!}

<div data-role="fieldcontain">
	<label for="filterTable-input">Filtrar por:</label>
	<input id="filterTable-input" data-type="search" data-inline="true">
</div>

<table id="serie_table" data-role="table" data-mode="reflow" data-filter="true" data-input="#filterTable-input" class="ui-body-d ui-shadow table-stripe ui-responsive" style="align:center; text-align:center; vertical-align:middle;">
	<thead>
		<tr>
			<th style="text-align:center; vertical-align:middle;">Série</th>
			<th style="text-align:center; vertical-align:middle;">Total Episódios</th>
			<th style="text-align:center; vertical-align:middle;">Estado</th>
			<th style="text-align:center; vertical-align:middle;">Opções</th>
		</tr>
	</thead>
	<tbody>
		@foreach( $series as $serie )
			@if($serie->serie_current_state === 0 )
				<tr style="background-color:#ffdfdf;">
			@else
				<tr style="background-color:#dfffdf;">
			@endif
				<td style="text-align:center; vertical-align:middle;">
					{{$serie->serie_original_name}}
				</td>
				<td style="text-align:center; vertical-align:middle;">
					{{$serie->episodes_count}}
				</td>
				<td style="text-align:center; vertical-align:middle;">
					<div class="serie_current_state_slider">
						<select name="serie_current_state_{{$serie->serie_id}}" id="serie_current_state_{{$serie->serie_id}}" data-role="slider" onChange="serie_current_state_change({{$serie->serie_id}});">
							@if($serie->serie_current_state === 0 )
								<option value="0" selected>Falta</option>
								<option value="1">Feito</option>
							@else
								<option value="0">Falta</option>
								<option value="1" selected>Feito</option>
							@endif
						</select>
					</div>
				</td>
				<td style="text-align:center; vertical-align:middle;">
					<fieldset data-role="controlgroup" data-type="horizontal" data-mini="true">
						<button data-mini="true" data-icon="user" onclick="serie_assignment('{{$serie->serie_id}}');">Distribuir</a>
						<button data-mini="true" data-icon="arrow-d" onclick="serie_statistics('{{$serie->serie_id}}');">Estatísticas</button>
						<button data-mini="true" data-icon="arrow-d" onclick="serie_scripts('{{$serie->serie_id}}');">Guiões</button>
						<button data-mini="true" data-icon="delete" onclick="serie_delete('{{$serie->serie_id}}','{{$serie->serie_original_name}}');">Remover</button>
					</fieldset>
				</td>
			</tr>
		@endforeach
	</tbody>
</table>

<script id="source" language="javascript" type="text/javascript">

//$(document).ready(function() {
//	$('#serie_table').freezeHeader({'offset': '45px'});
//});

function serie_current_state_change(serie_id){
	var serie_current_state = $("#serie_current_state_"+serie_id+" :selected").val();
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});
	
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('SerieController@changeState') }}",
		data: {
			serie_id : serie_id,
			current_state : serie_current_state
			},
		success: function (data) {
			if(data.length == 0){
				toastr["error"]('Serie: erro ao mudar estado da serie.');
			}
			else 
			{
				toastr["success"]('Serie: alteração de estado bem sucedida.');
				location.reload();
			}
		},
		error: function (data){
			toastr["error"]('Serie: erro ao mudar estado da serie.');
		}
	});
}

function serie_assignment(serie_id){
	var form = document.createElement("form");
    form.setAttribute("method", "POST");
    form.setAttribute("action", "{{ URL::action('AssignmentController@listing') }}");
	var params = {_token : "{{ csrf_token() }}",
					serie_id : serie_id};
    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);
            form.appendChild(hiddenField);
         }
    }
    document.body.appendChild(form);
    form.submit();
}

function serie_statistics(serie_id){
	var download_dialog = $.dialog({
		icon: 'fa fa-spinner fa-spin',
		useBootstrap: false,
		boxWidth: '30%',
		backgroundDismiss: false,
		autoClose: false,
		type: 'green',
		title: 'Gerar ficheiro',
		content: 'Por favor aguarde...',
	});
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});
	
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('SerieController@create_statistics') }}",
		data: {
			serie_id : serie_id
			},
		success: function (data) {
			if(data.length == 0){
				toastr["error"]('Serie: erro ao gerar ficheiro de estatística.');
			}
			else 
			{
				var try_downloading = function (){
					$.ajax({
						type: 'HEAD',
						url: data.url,
						success: function() {
							//$.fileDownload(data.url);
							
							var iframe = document.createElement('iframe');
							iframe.id = "IFRAMEID";
							iframe.style.display = 'none';
							document.body.appendChild(iframe);
							iframe.src = data.url;
							
							
							download_dialog.close();
							clearTimeout(try_downloading);
							return;
						},
						error: function() {
							setTimeout(try_downloading, 3000);
						}
					});
				};
				try_downloading();
			}
		},
		error: function (data){
			download_dialog.close();
			toastr["error"]('Serie: erro ao gerar ficheiro de estatística.');
		}
	});
}

function serie_scripts(serie_id){
	var download_dialog = $.dialog({
		icon: 'fa fa-spinner fa-spin',
		useBootstrap: false,
		boxWidth: '30%',
		backgroundDismiss: false,
		autoClose: false,
		type: 'green',
		title: 'Gerar ficheiro',
		content: 'Por favor aguarde...',
	});
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});
	
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('SerieController@create_scripts') }}",
		data: {
			serie_id : serie_id
			},
		success: function (data) {
			if(data.length == 0){
				toastr["error"]('Serie: erro ao gerar o pacote com os guiões.');
			}
			else 
			{
				var try_downloading = function (){
					$.ajax({
						type: 'HEAD',
						url: data.url,
						success: function() {
							$.fileDownload(data.url);
							download_dialog.close();
							clearTimeout(try_downloading);
							return;
						},
						error: function() {
							setTimeout(try_downloading, 3000);
						}
					});
				};
				try_downloading();
			}
		},
		error: function (data){
			download_dialog.close();
			toastr["error"]('Serie: erro ao gerar o pacote com os guiões.');
		}
	});
}

function serie_delete(serie_id, serie_original_name){
	$.confirm({
		useBootstrap: false,
		boxWidth: '50%',
		type: 'orange',
		title: 'Atenção!',
		content: 'Ao apagar a série, <b><i>' + serie_original_name + '</i></b>, da base de dados, irá apagar todas as entradas relacionadas a mesma.<br>Pretende realmente fazer isso?',
		buttons: {
			buttonA: {
				text: 'Não',
				btnClass: 'btn-green',
				action: function () {
					
				}
			},
			buttonB: {
				text: 'Sim',
				btnClass: 'btn-red',
				action: function () {
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
						}
					});
					$.ajax({
						type: "POST",
						dataType: "json",
						url: "{{ URL::action('SerieController@delete') }}",
						data: {
							serie_id : serie_id
							},
						success: function (data) {
							location.reload();
						},
						error: function (data){
							toastr["error"]('Série: erro ao remover a série.');
						}
					});
				}
			 }
		}
	});
}

</script>

@endsection
