@extends('app')

@section('content')

{!! Html::style('scripts/qTip/jquery.qtip.min.css') !!}
{!! Html::script('scripts/qTip/jquery.qtip.min.js') !!}
{!! Html::script('scripts/jquery.mark/jquery.mark.min.js') !!}

{!! Html::style('scripts/trumbowyg/ui/trumbowyg.min.css') !!}
{!! Html::script('scripts/trumbowyg/trumbowyg.min.js') !!}

{!! Html::style('scripts/trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css') !!}
{!! Html::script('scripts/trumbowyg/plugins/colors/trumbowyg.colors.min.js') !!}

<div class="script_slider">
	<select name="series_current_state" id="series_current_state" data-role="slider" onChange="script_get_series();">
		<option value="0">Séries em falta</option>
		<option value="1">Séries concluídas</option>
	</select>
</div>
	
<select id="series_options" onFocus="this.selectedIndex = -1;" onChange="script_get_episodes();">
	<option selected disabled value="-1">Escolha uma série</option>
	@if(isset($series))
		@foreach($series as $serie)
			<option value="{{ $serie->id }}">{{ $serie->original_name }}</option>
		@endforeach
	@endif
</select>

<div class="script_slider">
	<select name="episodes_current_state" id="episodes_current_state" data-role="slider" onChange="script_get_episodes();">
    	<option value="0">Episódios em falta</option>
    	<option value="1">Episódios concluídos</option>
    </select>
</div>

<select id="episodes_options" onChange="script_get_actors();" onFocus="this.selectedIndex = -1;">
	<option selected disabled value="-1">Escolha um episódio</option>
</select>

<div id="script_full_button"></div>

<div id="songs_button"></div>

<div id="actors"></div>

<div id="view_options_content"></div>

<div id="script" class="script"></div>

<div id="popupDialog_speechEdit" data-history="false" data-role="popup" data-overlay-theme="a" data-theme="a" data-dismissible="false" >
	<a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
	<div data-role="header" data-theme="a">
		<h1>Editar</h1>
	</div>
	<div role="main" class="ui-content" >
		<select id="characters_options" onFocus="this.selectedIndex = -1;">
			<option selected disabled value="-1">Escolha uma personagem</option>
		</select>
		<textarea name="popupDialog_scriptEdit_speech" id="popupDialog_scriptEdit_speech" cols="40" rows="10">
		</textarea>
		<div id="popupDialog_scriptEdit_buttons" style="text-align:center;">
		</div>
	</div>
</div>


<script id="source" language="javascript" type="text/javascript">

function script_get_corrections_series(){
	var serie_id = $( "#series_options option:selected" ).val();
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});

	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('CorrectionController@getCorrectionsSeries') }}",
		data: {
			serie_id : serie_id,
			current_state : false
			},
		success: function (data) {
			if(data.correctionsTable.length == 0){
				toastr["success"]('Correção: não foram encontradas correcções para a série.');
			}
			else 
			{
				toastr["warning"]('Correção: foram encontradas correcções para a série.');			
				$("#corrections_table").empty().append(data.correctionsTable).change().trigger("create");
				jQuery.mobile.changePage( $("#corrections"), {
					transition: "none"
				});
			}
		},
		error: function (data){
			toastr["error"]('Correção: erro ao obter correcções.');
		}
	});
}

function script_get_corrections_actors(actors_selected){
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});

	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('CorrectionController@getCorrectionsActors') }}",
		data: {
			actors_selected : actors_selected,
			current_state : false
			},
		success: function (data) {
			if(data.correctionsTable.length == 0){
				toastr["success"]('Correcção: não foram encontradas correcções para os actores.');
			}
			else 
			{
				toastr["warning"]('Correcção: foram encontradas correcções para os actores.');
				$("#corrections_table").empty().append(data.correctionsTable).change().trigger("create");
				jQuery.mobile.changePage( $("#corrections"), {
					transition: "none"
				});
			}
		},
		error: function (data){
			toastr["error"]('Correcção: erro ao obter correcções.');
		}
	});
}

function script_get_series(){
	$('#episodes_options').empty().append('<option selected disabled value="-1">Escolha um episódio</option>').find('option:first').attr("selected","selected").change();
	$("#script").empty().change().trigger("create");
	$("#view_options_content").empty().change().trigger("create");
	$("#actors").empty().change().trigger("create");
	$('#script_full_button').empty().change().trigger("create");
	var series_current_state = $('#series_current_state :selected').val();
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});

	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('SerieController@getSeries') }}",
		data: {
			current_state : series_current_state
			},
		success: function (data) {
			var seriesoptions = $('#series_options');
			if(data.length == 0){
				toastr["error"]('Série: não foram encontradas séries.');
				seriesoptions.empty().append('<option selected disabled value="-1">Não foram encontradas séries</option>').find('option:first').attr("selected","selected").change();
			}
			else 
			{
				seriesoptions.empty().append('<option selected disabled value="-1">Escolha uma série</option>').find('option:first').attr("selected","selected").change();
				$.each(data, function(i,item){
					seriesoptions.append(new Option(item.original_name, item.id));
				});
				seriesoptions.trigger("create");
			}
		},
		error: function (data){
			toastr["error"]('Série: erro ao obter séries.');
		}
	});
}

function script_get_episodes(){
	$("#script").empty().change().trigger("create");
	$("#view_options_content").empty().change().trigger("create");
	$('#script_full_button').empty().change().trigger("create");
	$("#actors").empty().change().trigger("create");
	var episodes_current_state = $('#episodes_current_state :selected').val();
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
			serie_id : serie_id,
			current_state : episodes_current_state
			},
		success: function (data) {
			var episodesoptions = $('#episodes_options');
			if(data.length == 0){
				toastr["error"]('Episódio: não foram encontrados episódios.');
				episodesoptions.empty().append('<option selected disabled value="-1">Não foram encontrados episódios</option>').find('option:first').attr("selected","selected").change();
			}
			else 
			{
				episodesoptions.empty().append('<option selected disabled value="-1">Escolha um episódio</option>').find('option:first').attr("selected","selected").change();
				$.each(data, function(i,item){
					episodesoptions.append(new Option(item.number + ' - ' + item.original_name, item.id));
				});
				episodesoptions.trigger("create");
			}
		},
		error: function (data){
			toastr["error"]('Episódio: erro ao obter episódios.');
		}
	});
	script_get_corrections_series();
}

function script_get_actors(){
	var serie_id = $( "#series_options option:selected" ).val();
	var episode_id = $( "#episodes_options option:selected" ).val();

	if(serie_id == -1){
		return;
	}
	
	if(episode_id == -1) {
		return;	
	}
	
	$('#script_full_button').empty().append("<a href='' class='ui-btn' onclick=\"script_get_full_script();\">Guião integral</a>").trigger("create");
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});

	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('SongController@songsExists') }}",
		data: {
			episode_id : episode_id
			},
		success: function (data) {
			if(data.songsExists.length == 0){
				$("#songs_button").empty().change().trigger("create");
			}
			else 
			{
				if(data.songsExists == 1){
					toastr["warning"]('Música: foram encontradas músicas para este episódio.');	
					$('#songs_button').empty().append("<a href='' class='ui-btn' onclick=\"song_get_songs();\">Músicas</a>").trigger("create");
				} else {
					$("#songs_button").empty().change().trigger("create");
				}
			}
		},
		error: function (data){
			toastr["error"]('Música: erro ao obter as músicas para este episódio.');	
		}
	});
	
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('ActorController@getActors') }}",
		data: {
			serie_id : serie_id,
			episode_id : episode_id
			},
		success: function (data) {
			$('#script').empty().change().trigger("create");
			$('#actors div').empty().change().trigger("create");
			var actors = $("#actors");
			if(data.length == 0){
				toastr["error"]('Actor: não foram encontradas distribuições para as personagens.');
				actors.empty().change().trigger("create");
			}
			else 
			{
				$.each(data, function(i,item){
					actors.append("<label><input type=\"checkbox\" class=\"actor\" name=\"actor\" onChange=\"script_get_scripts('1');\" value=\"" + item.actor_id + "\"> " + item.actor_name + "<label class=\"contagem\">" + item.speech_count + "</label></label>");
				});
				actors.trigger("create");
				$("#view_options_content").empty().append("<fieldset data-role='controlgroup' data-type='horizontal' id='view_options' onChange=\"script_get_scripts('0');\"> <input name='view_option' id='view_options_1' value='0' checked='checked' type='radio'> <label for='view_options_1'>Ordenar por fala</label> <input name='view_option' id='view_options_2' value='1' type='radio'> <label for='view_options_2'>Ordenar por personagem</label> </fieldset>").change().trigger("create");
			}
		},
		error: function (data){
			toastr["error"]('Actor: erro ao obter a lista de actores.');
		}
	});
}

function script_get_scripts(getCorrections){
	if($("#view_options").length > 0){
		
	} else {
		$("#view_options_content").empty().append("<fieldset data-role='controlgroup' data-type='horizontal' id='view_options' onChange=\"script_get_scripts('0');\"> <input name='view_option' id='view_options_1' value='0' checked='checked' type='radio'> <label for='view_options_1'>Ordenar por fala</label> <input name='view_option' id='view_options_2' value='1' type='radio'> <label for='view_options_2'>Ordenar por personagem</label> </fieldset>").change().trigger("create");
	}
	
	var view_option = $("#view_options :radio:checked").val();
	var actors_selected = [];
	$(":checkbox:checked").each(function(i){
		actors_selected[i] = $(this).val();
	});

	if(actors_selected.length > 0){
		if(getCorrections != 0){
			script_get_corrections_actors(actors_selected);
		}
		var serie_id = $( "#series_options option:selected" ).val();
		var episode_id = $( "#episodes_options option:selected" ).val();

		if(serie_id == -1){
			return;
		}
		
		if(episode_id == -1) {
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
			url: "{{ URL::action('ScriptController@getScripts') }}",
			data: {
				serie_id : serie_id,
				episode_id : episode_id,
				actors_selected : actors_selected,
				view_option : view_option
				},
			success: function (data) {
				if(data.scriptData.length == 0){
					toastr["error"]('Guião: não foram encontradas falas.');
					$('#script').empty().change().trigger("create");
				} else {
					$('#script').empty().append(data.scriptData).change().trigger("create");
					script_highlight_words(serie_id);
				}
			},
			error: function (data){
				toastr["error"]('Guião: erro ao obter guião.');
			}
		});
	} else {
		$("#script").empty().change().trigger("create");
	}
}

function jump_to_previous_id(current_id){
	var arrayOfIds = $.map($(".fala"), function(n, i){
		return n.id;
	});
	for(var i = 0; i < arrayOfIds.length; i++){
		if(arrayOfIds[i] == current_id){
			var value = arrayOfIds[i-1];
			$('html, body').animate({scrollTop: $("#" + value).offset().top}, 500);
			break;
		}
	}
}

function jump_to_next_id(current_id){
	var arrayOfIds = $.map($(".fala"), function(n, i){
		return n.id;
	});
	for(var i = 0; i < arrayOfIds.length; i++){
		if(arrayOfIds[i] == current_id){
			var value = arrayOfIds[i+1];
			$('html, body').animate({scrollTop: $("#" + value).offset().top}, 500);
			break;
		}
	}
}

function isInArray(value, array) {
	return array.indexOf(value) > -1;
}

function script_update_speech_div(script_id){
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
		url: "{{ URL::action('ScriptController@getScriptById') }}",
		data: {
			script_id : script_id
			},
		success: function (data) {
			var script = $('#' + script_id + ' .texto_fala');
			if(data.speech.length == 0){
				toastr["error"]('Guião: não foi encontrada fala.');
				script.empty().change().trigger("create");
			} else {
				//script.empty().append(data.speech.replace(/\n/g, '<br />')).change().trigger("create");
				script.empty().append(data.speech).change().trigger("create");
				script_highlight_words(serie_id);
			}
		},
		error: function (data){
			toastr["error"]('Guião: erro ao obter fala.');
		}
	});
}

function script_get_full_script(){
	var serie_id = $( "#series_options option:selected" ).val();
	var episode_id = $( "#episodes_options option:selected" ).val();

	$("#actors .actor").each(function() {
		$(this).prop("checked", false).checkboxradio("refresh");
	});
	
	if(serie_id == -1){
		return;
	}
	
	if(episode_id == -1) {
		return;	
	}
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});

	$("#view_options_content").empty().change().trigger("create");
	
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "{{ URL::action('ScriptController@get_script_full') }}",
		data: {
			serie_id : serie_id,
			episode_id : episode_id
			},
		success: function (data) {
			var script = $('#script');
			if(data.scriptData.length == 0){
				toastr["error"]('Guião: não foram encontradas falas.');
				script.empty().change().trigger("create");
			} else {
				script.empty().append(data.scriptData).change().trigger("create");
				script_highlight_words(serie_id);
			}
		},
		error: function (data){
			toastr["error"]('Guião: erro ao obter guião.');
		}
	});
}

function script_highlight_words(serie_id){
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
			if(data.length == 0){
				
			} else {
				toastr["warning"]('Glossário: palavras detectadas para esta série.');
				$.each(data, function(i,item){
					$('div .texto_fala').mark(item.word, {"separateWordSearch" : true});
					$("div .texto_fala :contains('"+item.word+"')").attr('title', item.pronounce);
				});
				$('[title!=""]').qtip({
					content: { title: 'Pronunciar' }
				});
			}
		},
		error: function (data){
			toastr["error"]('Glossário: erro ao obter entradas do glossário.');
		}
	});
}

function script_show_popupdialog_scriptEdit(serie_id, character_id, scriptid){
	//$("#popupDialog_scriptEdit_speech").trumbowyg('destroy');
	//$("#popupDialog_scriptEdit_speech").empty().change().trigger("create");
	
	script_getCharacters(serie_id, character_id);
	var script_speech = strip_mark_tag($('#' + scriptid).find('.texto_fala').html()/*.replace(/<br\s*[\/]?>/gi, "\n")*/);
	//var script_speech = $('#' + scriptid).find('.texto_fala').html();
	
	//$("#popupDialog_scriptEdit_speech").html(script_speech).change().trigger("create");
	
	$("#popupDialog_scriptEdit_speech").trumbowyg({
		btns: [['bold', 'italic', 'underline', 'strikethrough', 'foreColor', 'backColor'],['removeformat']],
		removeformatPasted: true,
		autogrow: false,
		resetCss: false
	}).change().trigger("create");
	
	$("#popupDialog_scriptEdit_speech").trumbowyg('html', script_speech);
	
	$("#popupDialog_scriptEdit_buttons").html("<a href='#' data-rel='back' data-role='button' data-theme='a' data-inline='true' data-mini='true' data-icon='delete' class='ui-btn-inline'>Cancelar</a><a href='' class='ui-btn-inline' data-role='button' data-inline='true' data-mini='true' data-icon='check' onclick=\"script_update_script_speech('" + scriptid + "');\">Aceitar</a>").trigger("create");
	$("#popupDialog_speechEdit").popup("open");
}

function strip_tags( str ) {
	str=str.toString();
	return str.replace(/<\/?[^>]+>/gi, '');
}

function strip_mark_tag(str){
	str=str.toString();
	return str.replace(/<[\/]{0,1}(mark|MARK)[^><]*>/g,"");
}

function script_getCharacters(serie_id, character_id){
	if(serie_id == -1 || character_id == -1){
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
		url: "{{ URL::action('CharacterController@getCharacters') }}",
		data: {
			serie_id : serie_id
			},
		success: function (data) {
			var charactersoptions = $('#characters_options');
			if(data.length == 0){
				toastr["error"]('Personagem: não foram encontradas personagens.');
				charactersoptions.empty().append('<option selected disabled value="-1">Escolha uma personagem</option>').find('option:first').attr("selected","selected").change();
			}
			else 
			{
				charactersoptions.empty().append('<option selected disabled value="-1">Escolha uma personagem</option>');
				$.each(data, function(i,item){
					if(character_id == item.id){
						charactersoptions.append('<option value="'+item.id+'" selected>' + item.name + ' (' + item.gender + ')</option>').change();
					} else {
						charactersoptions.append('<option value="'+item.id+'">' + item.name + ' (' + item.gender + ')</option>');
					}
				});
				charactersoptions.trigger("create");
			}
		},
		error: function (data){
			toastr["error"]('Personagem: erro ao obter personagens.');
		}
	});
}

function script_update_script_speech(scriptid){
	var script_speech = $("#popupDialog_scriptEdit_speech").trumbowyg('html');
	//var script_speech = $("#popupDialog_scriptEdit_speech").val();
	var character_id = $("#characters_options option:selected").val();

	if(character_id == -1){
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
		url: "{{ URL::action('ScriptController@update_script_speech') }}",
		data: {
			script_id : scriptid,
			script_speech : script_speech,
			character_id : character_id
			},
		success: function (data) {
			toastr["success"]('Guião: fala alterada com sucesso.');
			script_update_speech_div(scriptid);
		},
		error: function (data){
			toastr["error"]('Guião: erro ao alterar a fala.');
		}
	});
	$( "#popupDialog_speechEdit" ).popup( "close" );
}

//SONG
function song_get_songs(){
	var episode_id = $( "#episodes_options option:selected" ).val();
	
	if(episode_id == -1) {
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
		url: "{{ URL::action('SongController@getSongs') }}",
		data: {
			episode_id : episode_id
			},
		success: function (data) {
			if(data.songs.length == 0){
				toastr["error"]('Música: não foram encontradas músicas para este episódio.');
				$("#songs_content").empty().change().trigger("create");
			}
			else 
			{
				toastr["warning"]('Música: foram encontradas músicas para este episódio.');			
				$("#songs_content").empty().append("<h3>" + data.episode.original_name + " - " + data.episode.number + "</h3>").change().trigger("create");
				$.each(data.songs, function(i,item){
					
					$("#songs_content").append("<div class='song_info'>Timecode: " + item.timecode + " - Nome da música: " + item.name + "</div><div id=" + item.id + " class='song_lyrics' style='width: 100%;height:auto; min-height:100%' ></div><a href='' class='ui-btn-inline' data-role='button' data-inline='true' data-mini='true' data-icon='refresh' onclick=\"song_update_song('" + item.id + "');\">Actualizar música</a>");

					$("#" + item.id).trumbowyg({
						btns: [['bold', 'italic', 'underline', 'strikethrough', 'foreColor', 'backColor'],['removeformat']],
						removeformatPasted: true,
						autogrow: true,
						resetCss: true
					}).change().trigger("create");
					
					$("#" + item.id).trumbowyg('html', item.lyrics);
					$("#" + item.id).change().trigger("create");
				});

				jQuery.mobile.changePage( $("#songs"), {
					transition: "none"
				});
			}
		},
		error: function (data){
			toastr["error"]('Música: erro ao obter músicas.');
			$("#songs_content").empty().change().trigger("create");
		}
	});
}

function song_update_song(song_id){
	var song_lyric_textarea = $("#"+song_id).html();
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});

	$.ajax({
		type: "PUT",
		dataType: "json",
		url: "{{ URL::action('SongController@update') }}",
		data: {
			song_id : song_id,
			song_lyric : song_lyric_textarea
			},
		success: function (data) {
			if(data.song_lyric.length == 0){
				toastr["error"]('Música: não foram encontradas músicas para este episódio.');
			}
			else 
			{
				toastr["success"]('Música: actualização efectuada com sucesso.');
				$("#"+song_id).html(data.song_lyric);
			}
		},
		error: function (data){
			toastr["error"]('Música: erro ao actualizar a música.');
		}
	});
}

</script>

@endsection
