@extends('app')

@section('content')

{{--{!! Html::script('scripts/freezeHeader/js/jquery.freezeheader.js') !!}--}}
{!! Html::script('scripts/fileDownload/jquery.fileDownload.js') !!}

<h3>Episódios</h3>
<select id="series_options" name="series_options" onFocus="this.selectedIndex = -1;" onchange="episode_get_episodes();" required>
	<option selected disabled value="-1">Escolha uma série</option>
		@foreach($series as $serie)
			<option value="{{ $serie->id }}">{{ $serie->original_name }}</option>
		@endforeach
</select>

<div data-role="fieldcontain">
	<label for="filterTable-input">Filtrar por:</label>
	<input id="filterTable-input" data-type="search" data-inline="true">
</div>

<table id="episode_table" data-role="table" data-mode="reflow" data-filter="true" data-input="#filterTable-input" class="ui-body-d ui-shadow table-stripe ui-responsive" style="align:center; text-align:center; vertical-align:middle;">
	<thead>
		<tr>
			<th style="text-align:center; vertical-align:middle;">N.º</th>
			<th style="text-align:center; vertical-align:middle;">Episódio</th>
			<th style="text-align:center; vertical-align:middle;">Estado</th>
			<th style="text-align:center; vertical-align:middle;">Opções</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<script id="source" language="javascript" type="text/javascript">

//$(document).ready(function() {
//	$('#episode_table').freezeHeader({'offset': '45px'});
//});

function episode_get_episodes(){
	var serie_id = $( "#series_options option:selected" ).val();
	
	if(serie_id == -1){
		return;
	}
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});

	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('EpisodeController@getEpisodes') }}",
		data: {
			serie_id : serie_id
			},
		success: function (data) {
			var episode_table = $("#episode_table tbody");
			if(data.length == 0){
				toastr["error"]('Episódio: não foram encontrados episódios.');
				episode_table.empty().change().trigger("create");
			}
			else 
			{
				episode_table.empty().change().trigger("create");
				$.each(data, function(i,item){
					if(item.current_state == 0){
						var episode_slider = "<div class='episode_current_state_slider'> <select name='episode_current_state_"+item.id+"' id='episode_current_state_"+item.id+"' data-role='slider' onChange=\"episode_current_state_change('"+item.id+"');\"> <option value='0' selected>Falta</option> <option value='1'>Feito</option> </select> </div>";
						var newRowContent = "<tr style='background-color:#ffdfdf;'><td style='text-align:center; vertical-align:middle;'>"+item.number+"</td><td style='text-align:center; vertical-align:middle;'>"+item.original_name+"</td><td style='text-align:center; vertical-align:middle;'>"+episode_slider+"</td><td style='text-align:center; vertical-align:middle;'><fieldset data-role='controlgroup' data-type='horizontal' data-mini='true'><button data-mini='true' data-icon='arrow-d' onclick='episode_print(\""+item.id+"\");'>Guião</button><button data-mini='true' data-icon='delete' onclick='episode_delete(\""+item.id+"\",\""+item.original_name.replace(/["']/g, "")+"\",\""+item.number+"\");'>Remover</button></fieldset></td></tr>";
					} else {
						var episode_slider = "<div class='episode_current_state_slider'> <select name='episode_current_state_"+item.id+"' id='episode_current_state_"+item.id+"' data-role='slider' onChange=\"episode_current_state_change('"+item.id+"');\"> <option value='0'>Falta</option> <option value='1' selected>Feito</option> </select> </div>";
						var newRowContent = "<tr style='background-color:#dfffdf;'><td style='text-align:center; vertical-align:middle;'>"+item.number+"</td><td style='text-align:center; vertical-align:middle;'>"+item.original_name+"</td><td style='text-align:center; vertical-align:middle;'>"+episode_slider+"</td><td style='text-align:center; vertical-align:middle;'><fieldset data-role='controlgroup' data-type='horizontal' data-mini='true'><button data-mini='true' data-icon='arrow-d' onclick='episode_print(\""+item.id+"\");'>Guião</button><button data-mini='true' data-icon='delete' onclick='episode_delete(\""+item.id+"\",\""+item.original_name.replace(/["']/g, "")+"\",\""+item.number+"\");'>Remover</button></fieldset></td></tr>";
					}
					episode_table.append(newRowContent);
				});
				episode_table.change().trigger("create");
			}
		},
		error: function (data){
			toastr["error"]('Episódio: erro ao obter episódios.');
		}
	});
}

function episode_current_state_change(episode_id){
	var episode_current_state = $("#episode_current_state_"+episode_id+" :selected").val();
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});
	
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('EpisodeController@changeState') }}",
		data: {
			episode_id : episode_id,
			current_state : episode_current_state
			},
		success: function (data) {
			if(data.length == 0){
				toastr["error"]('Episódio: não foram encontrados episódios.');
			}
			else 
			{
				toastr["success"]('Episódio: alteração de estado bem sucedida.');
				episode_get_episodes();
			}
		},
		error: function (data){
			toastr["error"]('Episódio: erro ao mudar estado do episódio.');
		}
	});
}

function episode_delete(episode_id, episode_original_name, episode_number){
	$.confirm({
		useBootstrap: false,
		boxWidth: '50%',
		type: 'orange',
		title: 'Atenção!',
		content: 'Ao apagar o episódio <b><i>' + episode_number + ' - ' + episode_original_name + '</i></b>, da base de dados, irá apagar todas as entradas relacionadas a mesma.<br>Pretende realmente fazer isso?',
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
						url: "{{ URL::action('EpisodeController@delete') }}",
						data: {
							episode_id : episode_id
							},
						success: function (data) {
							episode_get_episodes();
						},
						error: function (data){
							toastr["error"]('Episódio: erro ao remover o episódio pretendido.');
						}
					});
				}
			 }
		}
	});
}

function episode_print(episode_id){
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
	
	var form = document.createElement("form");
    form.setAttribute("method", "POST");
    form.setAttribute("action", "{{ URL::action('EpisodeController@printEpisode') }}");
	var params = {_token : "{{ csrf_token() }}",
					episode_id : episode_id};
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

</script>

@endsection
