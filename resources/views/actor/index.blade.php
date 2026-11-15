@extends('app')

@section('content')

{{--{!! Html::script('scripts/freezeHeader/js/jquery.freezeheader.js') !!}--}}

<a href="#insert_actor" data-rel="popup" data-position-to="window" class="ui-btn ui-btn-inline ui-icon-plus ui-btn-icon-left ui-btn-a" data-transition="pop">Adicionar actor</a>
<div data-role="popup" id="insert_actor" data-history="false" data-theme="a">
	<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Fechar</a>
	<div style="padding:10px 20px;">
		{!! Form::open(array('route' => 'actor.store', 'method' => 'post', 'class' => 'form', 'data-ajax' => 'false')) !!}
		   <label>Nome:{!! Form::text('name', null, array('required', 'size'=>'40x5', 'class'=>'form-control', 'placeholder'=>'Nome do ator ou atriz')) !!}</label>
			<fieldset>
				<legend>Género: </legend>
				<label for="radio-insertM">Masculino</label>
				<input required type="radio" name="gender" id="radio-insertM" value="M" >
				<label for="radio-insertF">Feminino</label>
				<input required type="radio" name="gender" id="radio-insertF" value="F">
			</fieldset>
			<label>Correio electrónico: {!!Form::text('email', null, array('class'=>'form-control', 'placeholder'=>'Correio electrónico do ator ou atriz'))!!}</label>
			<label>Contacto telefónico: {!!Form::text('phone', null, array('class'=>'form-control', 'placeholder'=>'Contacto telefónico do ator ou atriz'))!!}</label>
			<label>Nota: {!!Form::text('note', null, array('class'=>'form-control', 'placeholder'=>'Notas gerais'))!!}</label>
			{!! Form::submit('Adicionar', array('class'=>'btn btn-primary')) !!}
		{!! Form::close() !!}
	</div>
</div>

<div data-role="fieldcontain">
	<label for="filterTable-input">Filtrar por:</label>
	<input id="filterTable-input" data-type="search" data-inline="true">
</div>

<table id="actor_table" data-role="table" data-mode="reflow" data-filter="true" data-input="#filterTable-input" class="ui-body-d ui-shadow table-stripe ui-responsive" style="align:center; text-align:center; vertical-align:middle;">
	<thead>
		<tr>
			<th style="text-align:center; vertical-align:middle;">Nome</th>
			<th style="text-align:center; vertical-align:middle;">Género</th>
			<th style="text-align:center; vertical-align:middle;">E-mail</th>
			<th style="text-align:center; vertical-align:middle;">Telefone</th>
			<th style="text-align:center; vertical-align:middle;">Nota</th>
			<th style="text-align:center; vertical-align:middle;">Opções</th>
		</tr>
	</thead>
	<tbody>
		@foreach( $actors as $actor )
			<tr>
				<td style="text-align:center; vertical-align:middle;">
					{{$actor->name}}
				</td>
				<td style="text-align:center; vertical-align:middle;">
					{{$actor->gender}}
				</td>
				<td style="text-align:center; vertical-align:middle;">
					{{$actor->email}}
				</td>
				<td style="text-align:center; vertical-align:middle;">
					{{$actor->phone}}
				</td>
				<td style="text-align:center; vertical-align:middle;">
                                        {{$actor->note}}
                                </td>
				<td style="text-align:center; align:center; vertical-align:middle;">
					<div class="ui-grid-b ui-responsive" style="text-align:center; vertical-align:middle;">
						<div class="ui-block-a" style="text-align:center; vertical-align:middle;">
							<a href="#" class="ui-btn ui-mini ui-corner-all ui-shadow ui-btn-a ui-icon-arrow-d ui-btn-icon-left ui-btn-inline" onclick="actor_history('{{$actor->id}}');">Histórico</a>
						</div>
						<div class="ui-block-b" style="text-align:center; vertical-align:middle;">
							<a href="#edit_actor-{{$actor->id}}" data-rel="popup" data-position-to="window" class="ui-btn ui-mini ui-shadow ui-corner-all ui-icon-edit ui-btn-icon-left ui-btn-inline" data-transition="pop">Editar</a>
							<div data-role="popup" id="edit_actor-{{$actor->id}}" data-history="false" data-theme="a">
								<a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Fechar</a>
								<div style="padding:10px 20px;">
									{!! Form::open(array('route' => ['actor.update', $actor->id], 'method' => 'put', 'class' => 'form', 'data-ajax' => 'false')) !!}
										<label>Nome:{!! Form::text('name', $actor->name, array('required', 'size'=>'40x5', 'class'=>'form-control', 'placeholder'=>'Nome do ator ou atriz')) !!}</label>
										
											@if($actor->gender === "M")
					
                                        							<fieldset>
                                									<legend>Género: </legend>
                                									<label for="radio-updateM_{{$actor->id}}">Masculino</label>
                                									<input required type="radio" name="gender" id="radio-updateM_{{$actor->id}}" value="M" checked>
                                									<label for="radio-updateF_{{$actor->id}}">Feminino</label>
                                									<input required type="radio" name="gender" id="radio-updateF_{{$actor->id}}" value="F">
                        									</fieldset>
												
											@else

												<fieldset>
                                                                                                        <legend>Género: </legend>
                                                                                                        <label for="radio-updateM_{{$actor->id}}">Masculino</label>
                                                                                                        <input required type="radio" name="gender" id="radio-updateM_{{$actor->id}}" value="M">
                                                                                                        <label for="radio-updateF_{{$actor->id}}">Feminino</label>
                                                                                                        <input required type="radio" name="gender" id="radio-updateF_{{$actor->id}}" value="F" checked>
                                                                                                </fieldset>

                               		
											@endif
										<label>Correio electrónico: {!!Form::text('email', $actor->email, array('class'=>'form-control', 'placeholder'=>'Correio electrónico do ator ou atriz'))!!}</label>
										<label>Contacto telefónico: {!!Form::text('phone', $actor->phone, array('class'=>'form-control', 'placeholder'=>'Contacto telefónico do ator ou atriz'))!!}</label>
										<label>Nota: {!!Form::text('note', $actor->note, array('class'=>'form-control', 'placeholder'=>'Notas gerais'))!!}</label>
										{!! Form::submit('Actualizar', array('class'=>'btn btn-primary')) !!}
									{!! Form::close() !!}
								</div>
							</div>
						</div>
						<div class="ui-block-c" style="text-align:center; vertical-align:middle;">
							<a href="#" class="ui-btn ui-mini ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-left ui-btn-inline" onclick="actor_delete('{{$actor->id}}', '{{$actor->name}}');">Remover</a>
						</div>
					</div>
				</td>
			</tr>
		@endforeach
	</tbody>
</table>

<script id="source" language="javascript" type="text/javascript">

//$(document).ready(function() {
//	$('#actor_table').freezeHeader({'offset': '45px'});
//});

function actor_history(actor_id){
	var download_dialog = $.dialog({
		icon: 'fa fa-spinner fa-spin',
		useBootstrap: false,
		boxWidth: '30%',
		type: 'green',
		backgroundDismiss: false,
		autoClose: false,
		title: 'Gerar ficheiro',
		content: 'Por favor aguarde...',
	});
	
	var form = document.createElement("form");
    form.setAttribute("method", "POST");
    form.setAttribute("action", "{{ URL::action('ActorController@printHistory') }}");
	var params = {_token : "{{ csrf_token() }}",
					actor_id : actor_id};
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

function actor_delete(actor_id, actor_name){
	$.confirm({
		useBootstrap: false,
		boxWidth: '50%',
		type: 'orange',
		title: 'Atenção!',
		content: 'Ao apagar o ator <b><i>' + actor_name + '</i></b>, da base de dados, irá apagar este ator de todas as séries na qual ele entrou.<br>Pretende realmente fazer isso?',
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
						url: "{{ URL::action('ActorController@delete') }}",
						data: {
							actor_id : actor_id
							},
						success: function (data) {
							location.reload();
						},
						error: function (data){
							toastr["error"]('Actor: erro ao remover o actor pretendido.');
						}
					});
				}
			 }
		}
	});
}

</script>

@endsection

