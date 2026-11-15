<html lang="pt">
	<head>
		<title>Herbert</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="_token" content="{!! csrf_token() !!}" />
		@include('includes/_header')
		@include('includes/_style')
	</head>
<body>
<div data-role="page" id="page">
	<div data-role="header" data-position="fixed" id="header">
		<h1>Herbert</h1>
		<div class="ui-btn-right" data-role="controlgroup" data-type="horizontal" id="header_right">	
			<a href="#" id="gototop" data-role="button" data-icon="arrow-u">Topo</a>
			<a href="#" id="gotomiddle" data-role="button" data-icon="minus">Meio</a>
			<a href="#" id="gotobottom" data-role="button" data-icon="arrow-d">Base</a>
			<!-- <a href="#cancao" data-role="button" data-icon="star" onClick="cancaoSelecionada();">Canções</a> -->
			<a href="#nav-panel-right" data-role="button" data-icon="gear" >Menu</a>
		</div>
	</div>
	<div id="main_page" data-role="main" class="ui-content jqm-content jqm-fullwidth">
		@yield('content')
	</div>
	<div data-role="panel" data-position="right" data-display="overlay" data-theme="a" id="nav-panel-right">
		<ul data-role="listview" data-ajax="false">
			<li data-icon="delete"><a href="#" data-rel="close">Fechar menu</a></li>
			<li><a href="{{ route('script.view') }}" data-ajax="false">Ver guião</a></li>
			<li data-role="list-divider">Gestão</li>
			<li><a href="{{ route('script.insert') }}" data-ajax="false">Inserir guião</a></li>
			<li><a href="{{ route('serie.index') }}" data-ajax="false">Séries</a></li>
			<li><a href="{{ route('episode.index') }}" data-ajax="false">Episódios</a></li>
			<li><a href="{{ route('correction.index') }}" data-ajax="false">Correcções</a></li>
			<li><a href="{{ route('glossary.index') }}" data-ajax="false">Glossário</a></li>
			<!-- <li><a href="{{ route('assignment.index') }}" data-ajax="false">Distribuir personagens</a></li> -->
			<li><a href="{{ route('actor.index') }}" data-ajax="false">Actores</a></li>
			<li><a href="{{ route('character.index') }}" data-ajax="false">Conflito personagens</a></li>
		</ul>
	</div><!-- /panel -->
</div>
<div data-role="page" id="corrections">
  <div data-role="header" data-position="fixed">
	<a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="arrow-l" class="ui-btn-left">Voltar</a>
    <h1>Correcções</h1>
  </div>
  <div data-role="content">
  	<div id="corrections_content">
		<table data-role="table" data-mode="reflow" id="corrections_table" class="ui-body-d ui-shadow table-stripe ui-responsive" style="align:center; text-align:center; vertical-align:middle; overflow:scroll;">
		</table>
	</div>
  </div>
</div>
<div data-role="page" id="songs">
  <div data-role="header" data-position="fixed">
	<a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="arrow-l" class="ui-btn-left">Voltar</a>
    <h1>Músicas</h1>
  </div>
  <div data-role="content">
  	<div id="songs_content"></div>
  </div>
</div>
@include('includes/_script')
@yield('footer_script')
</body>
</html>