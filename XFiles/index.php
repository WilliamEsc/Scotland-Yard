<! DOCTYpe html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="Css/style.css" >
		<title>
			XFiles
		</title>
	</head>
	<body>
	<?php
		session_start();
		require('Inc/include.php');
		require('Inc/constante.php');
		require('Modele/modele.php');
		require('Inc/route.php');
		$connexion=getConnexion();
		integrationDonnees($connexion);
	?>
	<div class="all">
		<?php include('Static/header.php'); ?>
		</br>
		<div class="content">
			<div class="menu-stat">
				<nav class="nav">
				<?php include('Static/menu.php'); ?>
				</nav>
				<aside class="statbase">
				<?php include('Static/stat.php'); ?>
				</aside>
			</div>
				<?php
				$controleur ='controleurAccueil';
				$vue ='vueAccueil';
		
				if(isset($_POST['PAGE'])){
					$nomPage = $_POST['PAGE'];
		
				if(isset ($route[$nomPage])){
					$controleur=$route[$nomPage]['controleur'];
					$vue=$route[$nomPage]['vue'];}
				}
				
				include('Controleur/'.$controleur.'.php');
				include('Vue/'.$vue.'.php');
				?>
			<aside class="aside">
			</aside>
		</div>
		<?php include('Static/footer.php'); ?>
	</div>
	</body>
