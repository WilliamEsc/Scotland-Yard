<?php
function getConnexion()
{
    $connexion=mysqli_connect(host,nametag,password,base);
    if(mysqli_connect_error())
    {
        printf("Echec de la connexion : %s\n",mysqli_connect_error());
        exit;
    }
    mysqli_set_charset($connexion,'utf8');
    return $connexion;
}


function integrationDonnees($connexion)
{
    $req='SELECT DISTINCT q.idQ as idQ, q.typeQ as typeQ, q.nomQ as nomQ,q.coords as coords, r.isQuartierDepart as ptsD, q.cpCommune as cpC, q.nomCommune as nomC,q.departement as idD FROM dataset.Quartiers q JOIN dataset.Routes r ON q.idQ=r.idQuartierDepart;';
    $rep=mysqli_query($connexion,$req);
    //printf("Select a retourné %d lignes.\n", mysqli_num_rows($rep));
    $j=0;
    foreach( $rep as $val)
    {
        $nomQ=mysqli_real_escape_string($connexion,$val['nomQ']);
        $idQ=mysqli_real_escape_string($connexion,$val['idQ']);
        $typeQ=mysqli_real_escape_string($connexion,$val['typeQ']);
        $ptsD=mysqli_real_escape_string($connexion,$val['ptsD']);
        $cpC=mysqli_real_escape_string($connexion,$val['cpC']);
        $nomC=mysqli_real_escape_string($connexion,$val['nomC']);
        $idD=mysqli_real_escape_string($connexion,$val['idD']);
        $req="INSERT INTO p1702268.projet__Communes (NomCo,CodePostal,idD)VALUES ('$nomC','$cpC','$idD');";
        mysqli_query($connexion,$req);
        $req="INSERT INTO p1702268.projet__Departement (idD) VALUES ('$idD')";
        mysqli_query($connexion,$req);
        $temp=array("[","]");
        $coord=explode(",",str_replace(" ", "",str_replace($temp, "", $val['coords'])));
        $longitude=0;
        $latitude=0;
        $i=0;
        foreach($coord as $coor)
        {
            if($i%2==0)
            {
                $longitude+=$coor;
            }
            else
            {
                $latitude+=$coor;
            }
            $i++;
        }
        $longitude/=$i/2;
        $latitude/=$i/2;
        //longitude et latitude sont les moyennes des coordonnées
        if (isset($val['ptsD']))//si le quartier est un pts de départ
        {
            $req="INSERT INTO p1702268.projet__Quartier (idQ, nomQ, typeQ, ptsDepart, longitude, latitude, NomCo) VALUES ('$idQ','$nomQ','$typeQ','$ptsD', '$longitude', '$latitude','$nomC')";
            mysqli_query($connexion,$req);
        }
        else
        {
            $req="INSERT INTO p1702268.projet__Quartier (idQ, nomQ, typeQ, longitude, latitude,NomCo) VALUES ('$idQ','$nomQ','$typeQ', '$longitude', '$latitude','$nomC')";
            mysqli_query($connexion,$req);
        }
    }
    $req='SELECT idQuartierDepart as idQ1, idQuartierArrivee as idQ2, transport FROM dataset.Routes;';
    $rep=mysqli_query($connexion,$req);
    foreach( $rep as  $val)
    {
        $idQ1=mysqli_real_escape_string($connexion,$val['idQ1']);
        $idQ2=mysqli_real_escape_string($connexion,$val['idQ2']);
        $transport=mysqli_real_escape_string($connexion,$val['transport']);
        $req2="INSERT INTO p1702268.projet__Route (TypeTransport, idQ1, idQ2) VALUES ('$transport','$idQ1','$idQ2')";
        mysqli_query($connexion,$req2);
    }
}

function GetTabQuartier($connexion)
{
    $req="SELECT idQ, nomQ, typeQ FROM p1702268.projet__Quartier;";
    $rep=mysqli_query($connexion, $req);
    //printf("Select a retourné %d lignes.\n", mysqli_num_rows($rep));
    return $rep;
}

function initNewGame($joueuse, $nbBot, $config,$connexion)
{
    $joueuse=mysqli_real_escape_string($connexion,$joueuse);
    $nbBot=mysqli_real_escape_string($connexion,$nbBot);
    $config=mysqli_real_escape_string($connexion,$config);
    $req="SELECT nomJ FROM p1702268.projet__Joueuse WHERE nomJ LIKE '$joueuse';";
    $rep=mysqli_query($connexion,$req);
    if(mysqli_num_rows($rep)>0)//si la joueuse existe deja
    {
        $_SESSION['date']=date("Y-m-d h:i:s");
        $date=$_SESSION['date'];
        $nbBot++;
        $req="INSERT INTO p1702268.projet__Partie VALUES ('$date','$nbBot','$joueuse','basique');";
        $res=mysqli_query($connexion,$req);//creation de la partie
        return TRUE;
    }
    else
    {
        $req="INSERT INTO p1702268.projet__Joueuse VALUES ('$joueuse','email', '0');";
        $res=mysqli_query($connexion,$req);//creation de la joueuse
        if ($res)
        {
            $_SESSION['date']=date("Y-m-d h:i:s");
            $date=$_SESSION['date'];
            $nbBot++;
            $req="INSERT INTO p1702268.projet__Partie VALUES ('$date','$nbBot','$joueuse','basique');";
            $res=mysqli_query($connexion,$req);//creation de la partie
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}

function QuartierSuivant ($connexion,$idQ1)//retourne les quartier accessible et par quel moyen de transport
{
    $idQ1=mysqli_real_escape_string($connexion,$idQ1);
    $req="SELECT idQ2,TypeTransport FROM p1702268.projet__Route WHERE idQ1='$idQ1';";
    $rep=mysqli_query($connexion, $req);
    return $rep;
}

function convertObjectToArray($resultat)
{
    $tab = array();
    while($row = mysqli_fetch_array($resultat))
    {
        $tab[] = $row ;
    }
    return $tab ;
}

function getquartierdepart($connexion)//retourne les quartiers de depart possible
{
    $req="select idQ FROM p1702268.projet__Quartier WHERE ptsDepart=1;";
    $rep=mysqli_query($connexion, $req);
    return $rep;
}

function insertMisterX($connexion,$numTour,$idQ1,$idQ2,$typeTransport)
{
    //aucune des valeurs ci dessus n'est rentré par l'utilisateur pas besoin d'echappement
    $date=$_SESSION['date'];
    $req="INSERT INTO p1702268.projet__MisterX VALUES ('$date','$numTour', '$idQ1', '$idQ2', '$typeTransport');";
    $rep=mysqli_query($connexion,$req);
    return $rep; //pour tester le fonctionnement
}

function getMisterX($connexion)//retourne la derniere position de Mister X et le numero du tour d'une partie donnée
{
    $date=$_SESSION['date'];
    $req = "Select idQ2,NumTour FROM p1702268.projet__MisterX where DateDemarrage ='$date' AND NumTour = (Select MAX(NumTour) FROM p1702268.projet__MisterX WHERE DateDemarrage ='$date')" ;
    $posX = mysqli_query($connexion,$req);
    $res=mysqli_fetch_assoc($posX);
    return $res;
}

function getTicketMisterX($connexion)//retourne toute les positions et les type de transport de Mister X d'une partie donnée
{
    $date=$_SESSION['date'];
    $req = "Select idQ2,TypeTransport,NumTour FROM p1702268.projet__MisterX where DateDemarrage ='$date'";
    $rep = mysqli_query($connexion,$req);
    return $rep;
}

function getAllImage($connexion){//prend toute les images de la base de donnée
	$req = "Select nomI,cheminI FROM p1702268.projet__Image";
	$rep = mysqli_query($connexion,$req);
	return $rep ;
}

function getImage($connexion,$img){//retourne les image avec un nom donnée
	$img=trim($img);
	$req = "Select cheminI FROM p1702268.projet__Image where nomI='$img'";
	$rep = mysqli_query($connexion,$req);
	$rep=mysqli_fetch_assoc($rep);
	return $rep ;
}

function incrementVictoireBD($connexion){//incremente le nombre de victoire d'un joueur donnée
	$player=$_SESSION['player'];
	$req="SELECT nbV FROM p1702268.projet__Joueuse WHERE nomJ='$player'";
	$rep = mysqli_query($connexion,$req);
	$rep=mysqli_fetch_assoc($rep);
	$nbV=$rep['nbV']+1;
	$req="UPDATE p1702268.projet__Joueuse SET nbV='$nbV' WHERE nomJ='$player'";
	mysqli_query($connexion,$req);
}

function getNomJoueur($connexion){//retourne tous les joueurs de la base de données associé a leur nombre de victoire et le nombre de partie jouée
	$req = "Select j.nomJ, j.nbV, count(*) as nbP FROM p1702268.projet__Joueuse j NATURAL JOIN p1702268.projet__Partie p GROUP BY nomJ";
	$rep = mysqli_query($connexion,$req);
	$rep=convertObjectToArray($rep);
	return $rep ;
}

/*//boc inutile, était pour la strategie de pistage en comparer les normes suivant latitude et longitude
function getPosQuartierSuivant ($connexion,$idQ1)
{
    $idQ1=mysqli_real_escape_string($connexion,$idQ1);
    $req="SELECT r.idQ2 as idQ,longitude,latitude FROM p1702268.projet__Route r JOIN p1702268.projet__Quartier q WHERE r.idQ1='$idQ1' AND r.idQ2=q.idQ;";
    $rep=mysqli_query($connexion, $req);
    return $rep;
}

function getPosQuartier ($connexion,$idQ1)
{
    $idQ1=mysqli_real_escape_string($connexion,$idQ1);
    $req="SELECT idQ,longitude,latitude FROM p1702268.projet__Quartier q WHERE idQ='$idQ1';";
    $rep=mysqli_query($connexion, $req);
    $rep=mysqli_fetch_assoc($rep);
    return $rep;
}*/

function getStatDB($connexion){//affiche le nombre de tuple de chaque table
	$tab=array('Route'=>0,'Quartier'=>0,'MisterX'=>0,'Partie'=>0,'Joueuse'=>0);
	$req="SELECT * FROM p1702268.projet__Route;";
	$rep=mysqli_query($connexion, $req);
	$tab['Route']=mysqli_num_rows($rep);
	$req="SELECT * FROM p1702268.projet__Quartier;";
	$rep=mysqli_query($connexion, $req);
	$tab['Quartier']=mysqli_num_rows($rep);
	$req="SELECT * FROM p1702268.projet__MisterX;";
	$rep=mysqli_query($connexion, $req);
	$tab['MisterX']=mysqli_num_rows($rep);
	$req="SELECT * FROM p1702268.projet__Partie;";
	$rep=mysqli_query($connexion, $req);
	$tab['Partie']=mysqli_num_rows($rep);
	$req="SELECT * FROM p1702268.projet__Joueuse;";
	$rep=mysqli_query($connexion, $req);
	$tab['Joueuse']=mysqli_num_rows($rep);
	return $tab;
}
?>
