@extends('app')

@section('content')

{{--{!! Html::script('scripts/freezeHeader/js/jquery.freezeheader.js') !!}--}}

<a href="#insert_correction" data-rel="popup" data-position-to="window" class="ui-btn ui-btn-inline ui-icon-plus ui-btn-icon-left ui-btn-a" data-transition="pop">Adicionar correcção</a>
<div data-role="popup" id="insert_correction" data-history="false" data-theme="a">
	<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Fechar</a>
	<div style="padding:10px 20px;">
		{!! Form::open(array('route' => 'correction.store', 'method' => 'post', 'class' => 'form', 'data-ajax' => 'false')) !!}
			<label>Série:
				<select id="series_options" name="series_options" onFocus="this.selectedIndex = -1;" onChange="correction_get_episodes();" required>
					<option selected disabled value="-1">Escolha uma série</option>
						@foreach($series as $serie)
							<option value="{{ $serie->id }}">{{ $serie->original_name }}</option>
						@endforeach
				</select>
			</label>
			<label>Episódio:
				<select id="episodes_options" name="episodes_options" onFocus="this.selectedIndex = -1;">
					<option selected value="-1">Escolha um episódio</option>
				</select>
			</label>
			<label>Actor:
				<select id="actor" name='actor' onFocus="this.selectedIndex = -1;">
					<option selected value="-1">Escolha um actor</option>
						@foreach($actors as $actor)
							<option value="{{ $actor->id }}">{{ $actor->name }}</option>
						@endforeach
				</select>
			</label>
			<label>Timecode (HH:MM:SS):{!! Form::text('timecode', null, array('class'=>'form-control', 'placeholder'=>'Timecode da correcção (HH:MM:SS)')) !!}</label>
			<label>Correcção:{!! Form::textarea('correction_text', null, array('required', 'class'=>'form-control', 'size' => '50x20', 'placeholder'=>'Correcção pretendida')) !!}</label>
			{!! Form::submit('Adicionar correcção', array('class'=>'btn btn-primary')) !!}
		{!! Form::close() !!}
	</div>
</div>

{!! Form::open(array('route' => 'correction.index', 'method' => 'get', 'class' => 'form', 'data-ajax' => 'false')) !!}
<div class="correction_slider">
	<select name="corrections_current_state" id="corrections_current_state" class="corrections_current_state" data-role="slider" onChange="this.form.submit();">
		@if($current_state == 0)
			<option value="0" selected>Correcções em falta</option>
			<option value="1">Correcções concluídas</option>
		@else
			<option value="0" >Correcções em falta</option>
			<option value="1" selected>Correcções concluídas</option>
		@endif
	</select>
</div>
{!! Form::close() !!}

<div data-role="fieldcontain">
	<label for="filterTable-input">Filtrar por:</label>
	<input id="filterTable-input" data-type="search" data-inline="true">
</div>

<table id="corrections_table" data-role="table" data-mode="reflow" data-filter="true" data-input="#filterTable-input" class="ui-body-d ui-shadow table-stripe ui-responsive" style="align:center; text-align:center; vertical-align:middle;">
	<thead>
		<tr>
			<th style="text-align:center; vertical-align:middle;">Série</th>
			<th style="text-align:center; vertical-align:middle;">Nº Episódio</th>
			<th style="text-align:center; vertical-align:middle;">Actor</th>
			<th style="text-align:center; vertical-align:middle;">Timecode</th>
			<th style="text-align:center; vertical-align:middle;">Correcção</th>
			<th style="text-align:center; vertical-align:middle;">Estado</th>
			<th style="text-align:center; vertical-align:middle;">Opções</th>
		</tr>
	</thead>

	<tbody>
		@foreach( $corrections as $correction )
			@if($correction->correction_current_state === 0 )
				<tr style="background-color:#ffdfdf;">
			@else
				<tr style="background-color:#dfffdf;">
			@endif
					<td style="text-align:center; vertical-align:middle;">
						{{$correction->serie_original_name}}
					</td>
					<td style="text-align:center; vertical-align:middle;">
						{{$correction->episode_number}}
					</td>
					<td style="text-align:center; vertical-align:middle;">
						{{$correction->actor_name}}
					</td>
					<td style="text-align:center; vertical-align:middle;">
						{{$correction->correction_timecode}}
					</td>
					<td style="text-align:center; vertical-align:middle;">
						{{$correction->correction_correction}}
					</td>
					<td style="text-align:center; vertical-align:middle;">
						<div class="correction_current_state_slider">
							<select name="correction_current_state_{{$correction->correction_id}}" id="correction_current_state_{{$correction->correction_id}}" data-role="slider" onChange="correction_current_state_change({{$correction->correction_id}});">
								@if($correction->correction_current_state === 0 )
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
						<a href="#edit_correction-{{$correction->correction_id}}" data-rel="popup" data-position-to="window" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-edit ui-btn-icon-left" data-transition="pop" onClick="correction_edit_get_episodes('{{$correction->correction_id}}', '{{$correction->serie_id}}', '{{$correction->episode_id}}');">Editar</a>
						<div data-role="popup" id="edit_correction-{{$correction->correction_id}}" data-history="false" data-theme="a">
							<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Fechar</a>
							<div style="padding:10px 20px;">
								{!! Form::open(array('route' => ['correction.update', $correction->correction_id], 'method' => 'put', 'class' => 'form', 'data-ajax' => 'false')) !!}
									<label>Série:
										<select id="series_edit_options" name="series_edit_options" onFocus="this.selectedIndex = -1;" onChange="correction_edit_get_episodes('{{$correction->correction_id}}');" required >
											<option disabled value="-1">Escolha uma série</option>
											@foreach($series as $serie)
												@if($correction->serie_id === $serie->id)
													<option selected value="{{ $serie->id }}">{{ $serie->original_name }}</option>
												@else
													<option value="{{ $serie->id }}">{{ $serie->original_name }}</option>
												@endif
											@endforeach
										</select>
									</label>
									<label>Episódio:
										<select id="episodes_edit_options" name="episodes_edit_options" onFocus="this.selectedIndex = -1;">
											<option value="-1">Escolha um episódio</option>
										</select>
									</label>
									<label>Actor:
										<select id="actor" name='actor' onFocus="this.selectedIndex = -1;">
											@if($correction->actor_id === -1 || $correction->actor_id === null)
												<option selected value="-1">Escolha um actor</option>
											@else
												<option value="-1">Escolha um actor</option>
											@endif
											@foreach($actors as $actor)
												@if($correction->actor_id === $actor->id)
													<option selected value="{{ $actor->id }}">{{ $actor->name }}</option>
												@else
													<option value="{{ $actor->id }}">{{ $actor->name }}</option>
												@endif
											@endforeach
										</select>
									</label>
									<label>Timecode (HH:MM:SS):{!! Form::text('timecode', $correction->correction_timecode, array('class'=>'form-control', 'placeholder'=>'Timecode da correcção (HH:MM:SS)')) !!}</label>
									<label>Correcção:{!! Form::textarea('correction_text', $correction->correction_correction, array('required', 'class'=>'form-control', 'size' => '50x20', 'placeholder'=>'Correcção pretendida')) !!}</label>
									{!! Form::submit('Actualizar', array('class'=>'btn btn-primary')) !!}
								{!! Form::close() !!}
							</div>
						</div>
						<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-left ui-btn-inline" onclick="correction_delete('{{$correction->correction_id}}');">Remover</a>
					</fieldset>
					</td>
			</tr>
		@endforeach
	</tbody>
</table>

<script id="source" language="javascript" type="text/javascript">

//$(document).ready(function() {
//	$('#corrections_table').freezeHeader({'offset': '45px'});
//});

function correction_get_episodes(){
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
			var episodesoptions = $('#episodes_options');
			if(data.length == 0){
				toastr["error"]('Correcção: não foram encontrados episódios.');
				episodesoptions.empty().append('<option selected disabled value="-1">Não foram encontrados episódios</option>').find('option:first').attr("selected","selected").change();
			}
			else 
			{
				episodesoptions.empty().append('<option selected value="-1">Escolha um episódio</option>').find('option:first').attr("selected","selected").change();
				$.each(data, function(i,item){
					episodesoptions.append(new Option(item.number + ' - ' + item.original_name, item.id));
				});
				episodesoptions.trigger("create");
			}
		},
		error: function (data){
			toastr["error"]('Correcção: erro ao obter episódios.');
		}
	});
}

function correction_edit_get_episodes(correction_id, correction_serie_id, correction_episode_id){
	if(correction_serie_id === undefined ||  correction_serie_id === null ||  correction_serie_id === ''){
		var serie_id = $('#edit_correction-'+correction_id).find('select[name="series_edit_options"]').val();
	} else {
		var serie_id = correction_serie_id;
	}
	
	if(correction_episode_id === undefined ||  correction_episode_id === null ||  correction_episode_id === ''){
		var episode_id = -1;
	} else {
		var episode_id = correction_episode_id;
	}

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
			serie_id : serie_id,
			current_state : false
			},
		success: function (data) {
			var episodesoptions = $('#edit_correction-'+correction_id).find('select[name="episodes_edit_options"]');
			if(data.length == 0){
				toastr["error"]('Correcção: não foram encontrados episódios.');
				episodesoptions.empty().append('<option selected disabled value="-1">Não foram encontrados episódios</option>').find('option:first').attr("selected","selected").change();
			}
			else 
			{
				if(episode_id == -1){
					episodesoptions.empty().append('<option selected value="-1">Escolha um episódio</option>');
				} else {
					episodesoptions.empty().append('<option value="-1">Escolha um episódio</option>');
				}
				$.each(data, function(i,item){
					if(episode_id != -1 && item.id == episode_id){
						episodesoptions.append('<option selected value="'+item.id+'">'+item.number + ' - ' + item.original_name+'</option>').change();
					} else {
						episodesoptions.append('<option value="'+item.id+'">'+item.number + ' - ' + item.original_name+'</option>').change();
					}
				});
				episodesoptions.trigger("create");
			}
		},
		error: function (data){
			toastr["error"]('Correcção: erro ao obter episódios.');
		}
	});
}

function correction_current_state_change(correction_id){
	var correction_current_state = $("#correction_current_state_"+correction_id+" :selected").val();
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});
	
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('CorrectionController@changeState') }}",
		data: {
			correction_id : correction_id,
			current_state : correction_current_state
			},
		success: function (data) {
			if(data.length == 0){
				toastr["error"]('Correcção: erro ao mudar estado da correcção.');
			}
			else 
			{
				toastr["success"]('Correcção: alteração bem sucedida.');
				location.reload();
			}
		},
		error: function (data){
			toastr["error"]('Correcção: erro ao mudar estado da correcção.');
		}
	});
}

function correction_delete(correction_id){
	$.confirm({
		useBootstrap: false,
		boxWidth: '30%',
		type: 'orange',
		title: 'Atenção!',
		content: 'Pretende realmente fazer isso?',
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
						url: "{{ URL::action('CorrectionController@delete') }}",
						data: {
							correction_id : correction_id
							},
						success: function (data) {
							location.reload();
						},
						error: function (data){
							toastr["error"]('Correction: erro ao remover a correcção pretendida.');
						}
					});
				}
			 }
		}
	});
}

</script>

@endsection
