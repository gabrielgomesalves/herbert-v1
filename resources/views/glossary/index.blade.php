@extends('app')

@section('content')

{{--{!! Html::script('scripts/freezeHeader/js/jquery.freezeheader.js') !!}--}}

<a href="#insert_glossary" data-rel="popup" data-position-to="window" class="ui-btn ui-btn-inline ui-icon-plus ui-btn-icon-left ui-btn-a" data-transition="pop">Adicionar nova palavra ao glossário</a>
<div data-role="popup" id="insert_glossary" data-history="false" data-theme="a">
	<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Fechar</a>
	<div style="padding:10px 20px;">
		{!! Form::open(array('route' => 'glossary.store', 'method' => 'post', 'class' => 'form', 'data-ajax' => 'false')) !!}
			<label>Serie:
				<select name='serie_id' onFocus="this.selectedIndex = -1;" required>
					<option selected disabled value="-1">Escolha uma série</option>
						@foreach($series as $serie)
							<option value="{{ $serie->id }}">{{ $serie->original_name }}</option>
						@endforeach
				</select>
			</label>
			<label>Palavra:{!! Form::text('word', null, array('required', 'class'=>'form-control', 'placeholder'=>'Palavra a ser inserida no glossário')) !!}</label>
			<label>Como pronunciar: {!!Form::textarea('pronounce', null, array('required', 'class'=>'form-control', 'placeholder'=>'Como deve-se pronunciar a palavra'))!!}</label>
			{!! Form::submit('Adicionar', array('class'=>'btn btn-primary')) !!}
		{!! Form::close() !!}
	</div>
</div>

<select id="series_options" name="series_options" onFocus="this.selectedIndex = -1;" onchange="glossary_get_glossary();">
	<option selected disabled value="-1">Escolha uma série</option>
		@foreach($series as $serie)
			<option value="{{ $serie->id }}">{{ $serie->original_name }}</option>
		@endforeach
</select>

<div data-role="fieldcontain">
	<label for="filterTable-input">Filtrar por:</label>
	<input id="filterTable-input" data-type="search" data-inline="true">
</div>

<table id="glossary_table" data-role="table" data-mode="reflow" data-filter="true" data-input="#filterTable-input" class="ui-body-d ui-shadow table-stripe ui-responsive" style="align:center; text-align:center; vertical-align:middle;">
	<thead>
		<tr>
			<th style="text-align:center; vertical-align:middle;" width="25%">Palavra</th>
			<th style="text-align:center; vertical-align:middle;" width="65%">Como pronunciar</th>
			<th style="text-align:center; vertical-align:middle;" width="10%">Opções</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<script id="source" language="javascript" type="text/javascript">

//$(document).ready(function() {
//	$('#glossary_table').freezeHeader({'offset': '45px'});
//});

function glossary_get_glossary(){
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
		url: "{{ URL::action('GlossaryController@getGlossary') }}",
		data: {
			serie_id : serie_id
			},
		success: function (data) {
			var glossary_table = $("#glossary_table tbody");
			if(data.length == 0){
				toastr["success"]('Glossário: não foram encontradas palavras para esta série.');
				glossary_table.empty().change().trigger("create");
			}
			else 
			{
				toastr["warning"]('Glossário: palavras detectadas para esta série.');
				glossary_table.empty().change().trigger("create");
				$.each(data, function(i,item){
					var newRowContent = "<tr><td style='text-align:center; vertical-align:middle;'>"+item.word+"</td><td style='text-align:center; vertical-align:middle;'>"+item.pronounce+"</td><td style='text-align:center; vertical-align:middle;'><fieldset data-role='controlgroup' data-type='horizontal' data-mini='true'><button class='ui-btn ui-shadow ui-corner-all ui-icon-delete ui-btn-icon-left ui-btn-inline' onclick='glossary_delete(\""+item.id+"\");'>Remover</button></fieldset></td></tr>";
					glossary_table.append(newRowContent);
				});
				glossary_table.change().trigger("create");
			}
		},
		error: function (data){
			toastr["error"]('Glossário: erro ao obter entradas do glossário.');
		}
	});	
}

function glossary_delete(glossary_id){
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
						url: "{{ URL::action('GlossaryController@delete') }}",
						data: {
							glossary_id : glossary_id
							},
						success: function (data) {
							glossary_get_glossary();
						},
						error: function (data){
							toastr["error"]('Glossário: erro ao remover a palavra pretendida.');
						}
					});
				}
			 }
		}
	});
}

</script>


@endsection
