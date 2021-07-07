<?php require_once('Controleur/controleurStatic.php');?>
<?=
	'<header class="header">;
			<div class="logo"> <a href="index.php"><img src="DATA/pion_black.jpg"></a></div>
			<div class="titre"> <h1>Xfiles</h1><p>L affaire de Mister X</p>
			<marquee bgcolor="orange">'; 
			?>
			<?php afficheStatBase($connexion);?>
			<?='</marquee></div>
	</header>';
?>


