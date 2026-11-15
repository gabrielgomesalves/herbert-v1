@extends('app')

@section('content')

{{--{!! Html::script('scripts/freezeHeader/js/jquery.freezeheader.js') !!}--}}

<select id="series_options" name="series_options"  onFocus="this.selectedIndex = -1;" onchange="character_detect_conflicts();" required>
	<option selected disabled value="-1">Escolha uma série</option>
		@foreach($series as $serie)
			<option value="{{ $serie->id }}">{{ $serie->original_name }}</option>
		@endforeach
</select>
	
<label for="threshold">Valor limite:</label>
<input name="threshold" id="threshold" min="0" max="10" value="2" data-show-value="true" type="range" onchange="character_detect_conflicts();">

<table id="characters_conflicts" data-role="table" data-mode="reflow" class="ui-body-d ui-shadow table-stripe ui-responsive" style="align:center; text-align:center; vertical-align:middle;">
	<thead>
		<tr>
			<th style="text-align:center; vertical-align:middle;">Personagem</th>
			<th style="text-align:center; vertical-align:middle;">Total de falas</th>
			<th style="text-align:center; vertical-align:middle;">Alterar para</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<script id="source" language="javascript" type="text/javascript">

//$(document).ready(function() {
//	$('#characters_conflicts').freezeHeader({'offset': '45px'});
//});

function character_detect_conflicts(){
	var serie_id = $( "#series_options option:selected" ).val();
	var threshold_value = $("#threshold").val();
	
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
		url: "{{ URL::action('CharacterController@detect_conflicts') }}",
		data: {
			serie_id : serie_id,
			threshold : threshold_value
			},
		success: function (data) {
			if(data.conflictsData.length == 0){
				toastr["success"]('Personagem: não foram encontrados conflitos.');
				$("#characters_conflicts tbody").empty().change().trigger("create");
			}
			else 
			{
				toastr["warning"]('Personagem: foram detectados possiveis conflitos.');
				$("#characters_conflicts tbody").empty().append(data.conflictsData).change().trigger("create");
			}
		},
		error: function (data){
			toastr["error"]('Personagem: erro ao obter conflitos.');
			$("#characters_conflicts tbody").empty().change().trigger("create");
		}
	});	
}

function character_change_characters(character_old_id){
	var character_new_id = $( "#"+character_old_id+" option:selected" ).val();
	
	if(character_new_id == -1){
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
		url: "{{ URL::action('CharacterController@change_character') }}",
		data: {
			character_new_id : character_new_id,
			character_old_id : character_old_id
			},
		success: function (data) {
			toastr["success"]('Personagem: alteração bem sucedida.');
			character_detect_conflicts();
		},
		error: function (data){
			toastr["error"]('Personagem: erro ao efectuar alteração.');
		}
	});
}

</script>

@endsection
