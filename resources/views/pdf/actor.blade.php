<html lang="pt">
	<head>
		<title>Herbert</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<style>
			@page { margin: 5.2cm 3.0cm 2.5cm 3.0cm; }
			.header { position: fixed; left: -25px; top: -125px; right: 0px; height: 150px; background-color: transparent; text-align: left; }
			.footer { position: fixed; left: 0px; bottom: -145px; right: 0px; height: 150px; background-color: transparent; text-align: center;}
			.footer .page:after { content: counter(page, decimal); }
			.company_info { font-size:48%; text-align: center; }
		</style>
	</head>
	<body>
		<div  class="header">
			<img src="./img/company_logo.png" alt="company logo">
		</div>
		<div class="footer">
			<p class="page">Pág. <?php $PAGE_NUM ?></p>
			<p class="company_info">112 Studios, Lda – Praceta Luís de Freitas Branco, nº 6 – cave – 2770-113 Paço de Arcos – Oeiras – Capital social: €5.000,00 – CRC Lisboa 4ª Sec. – Contribuinte IVA PT508032296<br>www.112studios.pt &nbsp;&nbsp; - &nbsp;&nbsp; geral@112studios.pt &nbsp;&nbsp; - &nbsp;&nbsp; Tel.: (+351) 213 163 580 &nbsp;&nbsp; - &nbsp;&nbsp; Fax: (+351) 213 163 589</p>
		</div>
		<div class="content">
			<h3>Histórico de gravação de: {{$actor->name}}</h3>
			@foreach($history as $his)
				<p>Série: {{$his->series_original_name}} - Episódio: {{$his->episodes_number}} - Personagem: {{$his->characters_name}} ({{$his->characters_gender}}) - Total de falas: {{$his->count_scripts_id}}</p>
			@endforeach
		</div>
	</body>
</html>
