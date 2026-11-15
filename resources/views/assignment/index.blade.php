@extends('app')

@section('content')

{{--{!! Html::script('scripts/freezeHeader/js/jquery.freezeheader.js') !!}--}}
{!! Html::script('scripts/fileDownload/jquery.fileDownload.js') !!}

@if(isset($assigned))

<h3>Distribuição de personagens</h3>

{!! Form::open(array('route' => 'assignment.store', 'class' => 'form', 'data-ajax' => 'false')) !!}
	{!! Form::hidden('serie_id', "$serie_id") !!}
	<div data-role="fieldcontain">
		<label for="filterTable-input">Filtrar por:</label>
		<input id="filterTable-input" data-type="search" data-inline="true">
	</div>
	
	<table id="assignment_table" data-role="table" data-mode="reflow" data-filter="true" data-input="#filterTable-input" class="ui-body-d ui-shadow table-stripe ui-responsive" style="align:center; text-align:center; vertical-align:middle;">
		<thead>
			<tr>
				<th style="text-align:center; vertical-align:middle;"></th>
				<th style="text-align:center; vertical-align:middle;">Personagem</th>
				<th style="text-align:center; vertical-align:middle;">Total Episódios</th>
				<th style="text-align:center; vertical-align:middle;">Total Falas</th>
				<th style="text-align:center; vertical-align:middle;">Actor/Actriz</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $assigned as $assign )
				<tr>
					<td style="text-align:center; vertical-align:middle;">
						<a href="#" class="ui-btn ui-icon-info ui-btn-icon-notext ui-corner-all" onClick="$('.assignment_character_info#{{$assign['character_id']}}').toggle();">No text</a>
					</td>
					<td style="text-align:center; vertical-align:middle;">
						{{$assign['character_name']}} ({{$assign['character_gender']}})
					</td>
					<td style="text-align:center; vertical-align:middle;">
						<?php $i = 0; ?>
						@foreach( $speeches_count as $speech_count )
							@if($speech_count->characters_id === $assign['character_id'])
								<?php $i = $i + 1; ?>
							@endif
						@endforeach
						{{ $i }} / {{ $total_episodes_count }}			
					</td style="text-align:center; vertical-align:middle;">
					<td style="text-align:center; vertical-align:middle;">
						@foreach($speeches_total_count as $speech_total_count)
							@if($speech_total_count->characters_id === $assign['character_id'])
								{{$speech_total_count->scripts_speech_count}}
							@endif
						@endforeach
					</td style="text-align:center; vertical-align:middle;">
					@if($assign['actor_id'] === null)
						<td style="text-align:center; vertical-align:middle;">
							<select name="{{ $assign['character_id'] }}" onFocus="this.selectedIndex = -1;">
								<option selected disabled value="-1">Escolha um actor ou atriz</option>
									@foreach($actors as $actor)
										<option value="{{ $actor->id }}">{{ $actor->name }}</option>
									@endforeach
							</select>
						</td>
					@else
						<td style="text-align:center; vertical-align:middle;">
							<select name="{{ $assign['character_id'] }}">
								<option disabled value="-1">Escolha um actor ou atriz</option>
									@foreach($actors as $actor)
										@if($actor->id === $assign['actor_id'])
											<option selected value="{{ $actor->id }}">{{ $actor->name }}</option>
										@else
											<option value="{{ $actor->id }}">{{ $actor->name }}</option>
										@endif
									@endforeach
							</select>
						</td>
					@endif
				</tr>
				<tr id="{{$assign['character_id']}}" class="assignment_character_info">
					<td style="text-align:justify; vertical-align:middle;" colspan="5">		
					@foreach( $speeches_count as $speech_count )
						@if($speech_count->characters_id === $assign['character_id'])
							[Ep: {{$speech_count->episodes_number}} - Falas: {{$speech_count->scripts_speech_count}}] 
						@endif
					@endforeach
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	{!! Form::submit('Fazer distribuição', array('class'=>'btn btn-primary')) !!}
{!! Form::close() !!}

@endif

@endsection

