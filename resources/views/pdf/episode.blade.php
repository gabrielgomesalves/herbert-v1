<html lang="pt">
	<head>
		<title>Herbert</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<style>
			@page { margin: 5.2cm 3.0cm 2.5cm 3.0cm; }
			.header { position: fixed; left: -25px; top: -125px; right: 0px; height: 150px; background-color: transparent; text-align: left; }
			.footer { position: fixed; left: 0px; bottom: -145px; right: 0px; height: 150px; background-color: transparent; text-align: center;}
			.footer .page:after { content: counter(page, decimal); }
			.fala {
				page-break-inside: avoid;
			}
			.info{
				font-family: Georgia;
				color: rgb(0,0,0);
				font-size: 0.85em;
				font-style: italic;
				letter-spacing: 0.25em;
				border-bottom: 1px solid rgba(69,54,37,0.2);
				padding-bottom: 0.5em;
				padding-top: 2em;
			}
			.texto_fala{
				font-family: Palatino, serif;
				font-size: 1em;
				text-align:justify;
				text-justify:inter-word;
				line-height: 2em;
				color: rgb(0,0,0);
			}
			.company_info { font-size:48%; text-align: center; }
		</style>
	</head>
	<body>
		<div class="header">
			<img src="./img/company_logo.png" alt="company logo">
		</div>
		<div class="footer">
			<p class="page">Pág. <?php $PAGE_NUM ?></p>
			<p class="company_info">112 Studios, Lda – Praceta Luís de Freitas Branco, nº 6 – cave – 2770-113 Paço de Arcos – Oeiras – Capital social: €5.000,00 – CRC Lisboa 4ª Sec. – Contribuinte IVA PT508032296<br>www.112studios.pt &nbsp;&nbsp; - &nbsp;&nbsp; geral@112studios.pt &nbsp;&nbsp; - &nbsp;&nbsp; Tel.: (+351) 213 163 580 &nbsp;&nbsp; - &nbsp;&nbsp; Fax: (+351) 213 163 589</p>
		</div>
		<div class="content">
			<p><strong>Série:</strong> {{$serie_info->original_name}} <strong>- Episódio:</strong> {{$episode_info->number}} <strong>-</strong> {{$episode_info->original_name}}</p>
			@foreach($scripts as $script)
				<div class="fala">
					<div class="info">Personagem: {{$script->character_name}} ({{$script->character_gender}}) - N.º: {{$script->script_number}} - Timecode: {{$script->script_timecode}}</div>
					<div class="texto_fala">{{trim(strip_tags($script->script_speech))}}</div>
				</div>
			@endforeach
		</div>
	</body>
</html>
