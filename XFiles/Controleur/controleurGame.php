<?php
//$taxi = 10 ;
//$bus = 8 ;
//$metro = 4 ;
if(isset($_POST['NewGame']) && $_POST['NewGame']=="init")  //c'est l'initialisation
{

    $_SESSION['player']=$_POST['Player'];
    $_SESSION['nbBot']=$_POST['nbBot'];
    $_SESSION['nomCf']=trim($_POST['nomCf']);
    $res=initNewGame($_SESSION['player'],$_SESSION['nbBot'],$_SESSION['nomCf'],$connexion);
    $_POST['NewGame']="done";
    $qdep=getquartierdepart($connexion);
    $tab=array();
    foreach($qdep as $q)
    {
        $tab[] = $q['idQ'] ;
    }
    $tabpos=array();
    $i=0;
    while($i<$_SESSION['nbBot']+2)
    {
        $pos=$tab[rand(0,17)];
        if(!in_array($pos,$tabpos))
        {
            $tabpos[] = $pos;
            $i++;
        }
    }
    $_SESSION['Joueur']['pos']=$tabpos[0];
    if($_SESSION['nomCf']=="eco" || $_SESSION['nomCf']=="pis")  // si la partie est en strategie econome ou pistage, on initialise le nombre de tickets pour le joueurs
    {
        $_SESSION['Joueur']['Taxi']=10;
        $_SESSION['Joueur']['Bus']=8;
        $_SESSION['Joueur']['Métro/tramway']=4;
        $_SESSION['Joueur']['Bateau']=0;
    }
    for ($i=0; $i<$_SESSION['nbBot']; $i++)
    {
        $_SESSION['Bot'][$i]['pos']=$tabpos[$i+1];
        if($_SESSION['nomCf']=="eco" || $_SESSION['nomCf']=="pis")  // meme chose pour les bots
        {
            $_SESSION['Bot'][$i]['Taxi'] = 10 ;
            $_SESSION['Bot'][$i]['Bus'] = 8 ;
            $_SESSION['Bot'][$i]['Métro/tramway'] = 4 ;
            $_SESSION['Bot'][$i]['Bateau'] = 0 ;
        }
    }
    $_SESSION['MisterX']['pos']=$tabpos[$_SESSION['nbBot']+1];
    insertMisterX($connexion,0,0,$_SESSION['MisterX']['pos'],"parachute");//au commencement MisterX est parachuté dans son quartier de départ
    $_SESSION['Victoire']=false;
    $_SESSION['Defaite']=false;
}
if(isset($_POST['ts']))  //tour suivant
{
///////////MisterX
    $posX = getMisterX($connexion) ; // on recupere le quartier dans lequel se trouve MisterX
    $qSuivMX = QuartierSuivant($connexion,$posX['idQ2']); // on recupere les quartier dans lesquels peut se rendre MisterX
    $tabQ = array();
    $tabT = array();
    foreach ($qSuivMX as $q)  // on met respectivement dans $tabQ et $TabT les quartiers d'arrives et les types de transport
    {
        $tabQ[] = $q['idQ2'] ;
        $tabT[] = $q['TypeTransport'] ;
    }
    /*$tailleMX = mysqli_num_rows($qSuivMX) ; // nb de quartier dispo
    $max = $tailleMX-1 ;*/
    $max = mysqli_num_rows($qSuivMX)-1 ;
    $x=0;
    do  ///test pour la prochaine position de MisterX
    {
        $num=rand(0,$max); // on tire au hasard le prochain quartier
        $posMX=$tabQ[$num];
        $Ttransport=$tabT[$num];
        $testPos=encercleMisterX($posMX);
        $x++;
    }
    while($testPos && $x<=$max);  // on cherche un nouveau quartier suivant pour MisterX tant que celui qui a ete choisi est pris et qu'il reste un quartier dispo
    if($x>$max)  //quand $x>$max MisterX est encerclé
    {
        $_SESSION['Victoire']=true;
    }
    if(!$_SESSION['Victoire'])  // Si MisterX a perdu inutile de faire le reste
    {
        $numtour=$posX['NumTour']+1; // on incremente le nombre de tout joue
        if($numtour>20) // au bout de 20 tours si MisterX n'est pas trouve le joueur perd la partie
        {
            $_SESSION['Defaite']=true;
        }
        insertMisterX($connexion,$numtour,$posX['idQ2'],$posMX,$Ttransport); // on insert dans la base de donnees les infos concernant la nouvelle position de MisterX
        $_SESSION['MisterX']['pos']=$posMX;
        if(!$_SESSION['Defaite'])
        {
//////////les bots
            for ($i = 0 ; $i < $_SESSION['nbBot'] ; $i++)
            {
                //echo $_SESSION['Bot'][$i]['pos']."<br>";//pour voir les anciennes positions des bots
                $qSuiv=QuartierSuivant($connexion,$_SESSION['Bot'][$i]['pos']); // $qSuiv contient les quartiers suivants dans lesquels le bot $i peut aller
                $tabQB=array();
                $tabTB=array();
                foreach($qSuiv as $q2)
                {
                    $tabQB[] = $q2['idQ2']; //on met dans $tabQB les quartiers d'arrives
                    $tabTB[] = $q2['TypeTransport'];
                    //pour voir les chemin possible des bots
                    /*echo $q2['idQ2'] ;
                    echo $q2['TypeTransport'];
                    echo "<br>";*/
                }

                $tailleB = mysqli_num_rows($qSuiv) ; // nb de quartier suivant dispo
                /*echo "Liste des idQ dispo : "."<br>"; //verification visuel des choix des bots
                for ($j = 0 ; $j < $taille ; $j++) {
                	echo $tab[$j]." ";
                }
                echo "<br>";*/
                $posMX=$_SESSION['MisterX']['pos'];
                $testposMX = false ;
                $j = 0 ;
                $ind=0;
                foreach ($tabQB as $t)
                {
                    if($t == $posMX)
                    {
                        $testposMX = true ;
                        $ind = $j ;
                    }
                    $j ++ ;
                }
                if ($_SESSION['nomCf'] == "eco")
                {
                    $ind=BotDeplacementEconomique($i,$testposMX,$posMX,$tailleB,$ind,$tabQB,$tabTB);
                }
                elseif ($_SESSION['nomCf'] == "pis")
                {
                    $ind=BotDeplacementEconomique($i,$testposMX,$posMX,$tailleB,$ind,$tabQB,$tabTB);
                    /*
                    $deja=array();
                    $tabRes=array();
                    cheminpluscourt($connexion,$tabRes,$_SESSION['Bot'][$i]['pos'],$posMX);*/
                }
                else
                {
                    BotDeplacementBasique($i,$testposMX,$posMX,$tailleB,$tabQB);
                }
                //pour voir les nouvelles positions et tickets des bots
                /*echo "Quartier actuel : ".$_SESSION['Bot'][$i]['pos']."<br>";
                if ($_SESSION['nomCf'] == "eco")
                {
                    echo $tabTB[$ind];
                    echo "taxi".$_SESSION['Bot'][$i]['Taxi'];
                    echo "bus".$_SESSION['Bot'][$i]['Bus'];
                    echo "metro".$_SESSION['Bot'][$i]['Métro/tramway'];
                }
                echo "<br>";*/
            }
///////////le joueur
            //echo $_POST['direction']."<br>";//pour voir le chemin utiliser par le joueur
            $dir=explode(" ",$_POST['direction']);
            $_SESSION['Joueur']['pos']=$dir[0];
            if($_SESSION['nomCf']=="eco" || $_SESSION['nomCf']=="pis")  // si on est en strategie economique ou pistage, on utilise le systeme de ticket
            {
                if(isset($_SESSION['Joueur'][$dir[2]]))
                    $_SESSION['Joueur'][$dir[2]]--;
            }
            for ($i = 0 ; $i < $_SESSION['nbBot'] ; $i++)
            {
                if($_SESSION['Bot'][$i]['pos']==$posMX)
                {
                    $_SESSION['Victoire']=true;
                }
            }
            if($_SESSION['Joueur']['pos']==$posMX)
            {
                $_SESSION['Victoire']=true;
            }
        }

    }
    //echo "Quartier MisterX: ".$_SESSION['MisterX']['pos']."<br>";//pour voir la position de misterX
}

function couleurCase ($posJ,$case)
{
if ($posJ == $case+1)
        {
            return "joueur" ;
        }
    else
    {
        return "default";
    }
}

function AfficherTabQuartier($connexion)
{
    $tab=GetTabQuartier($connexion);
    $i=0;
    foreach($tab as $val)
    {
        $t=QuartierSuivant($connexion,$val['idQ']);
        if(($i%10) == 0)
        {
            echo "<tr>";
            echo "<td class=".couleurCase($_SESSION['Joueur']['pos'], $i).couleurCase($_SESSION['MisterX']['pos'], $i).">";
            foreach($val as $donnee)
            {
                echo "<p>".$donnee."</p>";
            }
            echo "<p> quartier accessible </p>";
            echo "<p>";
            foreach($t as $t1)
            {
                echo "<p>\"".$t1['idQ2']." en ".$t1['TypeTransport']."\"</p>";
            }
            echo "</p>";
            echo "</td>";
        }
        elseif(($i%10) == 9)
        {
            echo "<td class=".couleurCase($_SESSION['Joueur']['pos'], $i).couleurCase($_SESSION['MisterX']['pos'], $i).">";
            foreach($val as $donnee)
            {
                echo "<p>".$donnee."</p>";
            }
            echo "<p> quartier accessible </p>";
            echo "<p>";
            foreach($t as $t1)
            {
                echo "<p>\"".$t1['idQ2']." en ".$t1['TypeTransport']."\"</p>";
            }
            echo "</td>";
            echo "</tr>";
        }
        else
        {
            echo "<td class=".couleurCase($_SESSION['Joueur']['pos'], $i).couleurCase($_SESSION['MisterX']['pos'], $i).">";
            foreach($val as $donnee)
            {
                echo "<p>".$donnee."</p>";
            }
            echo "<p> quartier accessible </p>";
            echo "<p>";
            foreach($t as $t1)
            {
                echo "<p>\"".$t1['idQ2']." en ".$t1['TypeTransport']."\"</p>";
            }
            echo "</td>";
        }
        $i++;
    }
}

function getChoixQuartierJoueur($connexion)
{
    //la fonction renvoie une liste de quartier accessible par le joueur
    $choix=QuartierSuivant($connexion,$_SESSION['Joueur']['pos']);
    $i=0;
    echo "<SELECT name="."direction"." size="."1".">";
    foreach($choix as $pos)
    {
        if($_SESSION['nomCf']=="eco" || $_SESSION['nomCf']=="pis")
        {
            if($_SESSION['Joueur'][$pos['TypeTransport']]>0)
            {
                echo "<OPTION>".$pos['idQ2']." en ".$pos['TypeTransport'];
                $i++;
            }// on propose au joueur les quartiers dans lesquels il peut aller a l'aide d'un menu deroulant
        }
        else
        {
            echo "<OPTION>".$pos['idQ2']." en ".$pos['TypeTransport'];
        }
    }
    if($_SESSION['nomCf']=="eco" || $_SESSION['nomCf']=="pis")
    {
        if($i==0)
        {
            echo "<OPTION> Maison a pieds";
            $_SESSION['Defaite']=true;
        }
    }
    /*$cheat=QuartierSuivant($connexion,$_SESSION['MisterX']['pos']); // pour tester la condition de victoire
    foreach($cheat as $pos)
    {
        echo "<OPTION>".$pos['idQ2']." en "."cheat";//probabilite de 1/n (n=nombre de quartier accessible par MisterX) de gagner
    }*/
}

function encercleMisterX($posMX)
{
    //la fonction test les quartiers ou il n'y as ni bots ni joueurs
    $testPos=false;
    for ($i = 0 ; $i < $_SESSION['nbBot'] ; $i++)   // on regarde si dans le prochain quartier de MisterX il y a un bot
    {
        if($_SESSION['Bot'][$i]['pos']==$posMX)
        {
            $testPos=true;
        }
    }
    if($_SESSION['Joueur']['pos']==$posMX)  // meme chose avec le joueur
    {
        $testPos=true;
    }
    return $testPos;
}

function BotDeplacementBasique($i,$testposMX,$posMX,$tailleB,$tabQB)
{
    //deplace les bots de facon aleatoire et sur MisterX si possible
    if ($testposMX == true)
    {
        $_SESSION['Bot'][$i]['pos'] = $posMX ;
    }
    else
    {
        $ind = rand(0,$tailleB-1);
        $posB=$tabQB[$ind]; // on tire au hasard le prochain quartier du bot $i
        $_SESSION['Bot'][$i]['pos'] = $posB ;
    }
}

function BotDeplacementEconomique($i,$testposMX,$posMX,$tailleB,$ind,$tabQB,$tabTB)
{
    //deplace les bbots de facon aleatoire et en fonction des tickets restant ainsi qu sur MisterX si possible
    if ($testposMX == true && $_SESSION['Bot'][$i][$tabTB[$ind]]>0)
    {
        $_SESSION['Bot'][$i]['pos'] = $posMX ;
        $_SESSION['Bot'][$i][$tabTB[$ind]]-- ;
    }
    else
    {
        $tabind=array();
        $x=0;
        while($x<$tailleB)
        {
            $num=rand(0,$tailleB-1);
            if(!in_array($num,$tabind))
            {
                $tabind[] = $num;
                $x++;
            }
        }
        $t=0;
        do
        {
            $ind = $tabind[$t];
            $t++;
        }
        while($_SESSION['Bot'][$i][$tabTB[$ind]]<1 && $t<$tailleB);
        if($t<$tailleB)
        {
            $posB=$tabQB[$ind]; // on tire au hasard le prochain quartier du bot $i
            $Tptrans=$tabTB[$ind];
            $_SESSION['Bot'][$i]['pos'] = $posB ;
            $_SESSION['Bot'][$i][$Tptrans]-- ;
        }
    }
    return $ind;
}

function imageCaseTicket($connexion,$tabT,$t)
{
    if(isset ($tabT[$t]))
    {
        switch($tabT[$t])
        {
        case "Taxi":
            $img=getImage($connexion,"ticketTaxi");
            return $img['cheminI'];
        case "Bus":
            $img=getImage($connexion,"ticketBus");
            return $img['cheminI'];
        case "Métro/tramway":
            $img=getImage($connexion,"ticketMetro");
            return $img['cheminI'];
        case "bateau":
            $img=getImage($connexion,"ticketNoir");
            return $img['cheminI'];
        default:
            return "./DATA/empty.jpg";
        }
    }
}

function afficheQuartierMisterX($connexion,$tabQ,$tabN,$t)
{
    if(isset ($tabN[$t]))
    {
        switch($t)
        {
        case 3:
            return $tabQ[$t];
        case 8:
            return $tabQ[$t];
        case 13:
            return $tabQ[$t];
        case 18:
            return $tabQ[$t];
        default:
            break;
        }
    }
}

function getTableauTicketMisterXPistage($connexion)
{
    $rep=getTicketMisterX($connexion);
    $tabT=array();//ticket
    $tabN=array();//numtour
    $tabQ=array();//Quartier
    foreach($rep as $r)
    {
        $tabT[]=$r['TypeTransport'];
        $tabN[]=$r['NumTour'];
        $tabQ[]=$r['idQ2'];
    }
    for($t=1; $t<21; $t++)
    {
        if(($t%10) == 1)
        {
            echo "<tr>";
            echo "<td class=cellticket background=".imageCaseTicket($connexion,$tabT,$t).">";
            echo "</td>";
        }
        elseif(($t%10) == 0)
        {
            echo "<td class=cellticket background=".imageCaseTicket($connexion,$tabT,$t).">";
            echo "</td>";
            echo "<tr>";
        }
        else
        {
            echo "<td class=cellticket  background=".imageCaseTicket($connexion,$tabT,$t).">";
            echo "<p><mark>".afficheQuartierMisterX($connexion,$tabQ,$tabN,$t)."</mark></p>";
            echo "</td>";
        }
    }
}

function getTableauTicketMisterXEconome($connexion)
{
    $rep=getTicketMisterX($connexion);
    $tabT=array();//ticket
    $tabN=array();//numtour
    $tabQ=array();//Quartier
    foreach($rep as $r)
    {
        $tabT[]=$r['TypeTransport'];
        $tabN[]=$r['NumTour'];
        $tabQ[]=$r['idQ2'];
    }
    for($t=1; $t<21; $t++)
    {
        if(($t%10) == 1)
        {
            echo "<tr>";
            echo "<td class=cellticket background=".imageCaseTicket($connexion,$tabT,$t).">";
            echo "</td>";
        }
        elseif(($t%10) == 0)
        {
            echo "<td class=cellticket background=".imageCaseTicket($connexion,$tabT,$t).">";
            echo "</td>";
            echo "<tr>";
        }
        else
        {
            echo "<td class=cellticket  background=".imageCaseTicket($connexion,$tabT,$t).">";
            echo "</td>";
        }
    }
}

function touteimage($connexion)
{
    $image = getAllImage($connexion);
    $tabI=array();
    $tabN=array();
    $t=0;
    foreach ($image as $i)
    {
        $tabI[]=$i['cheminI'];
        $tabN[]=$i['nomI'];
        $t++;
    }
    for($i=0; $i<$t; $i++)
    {
        echo '<img class='.$i.' src='.$tabI[$i].' alt="" height="50px" width="50px" />' ;
        if(strstr($tabN[$i],"Taxi"))
            echo $_SESSION['Joueur']['Taxi'];
        if(strstr($tabN[$i],"Bus"))
            echo $_SESSION['Joueur']['Bus'];
        if(strstr($tabN[$i],"Metro"))
            echo $_SESSION['Joueur']['Métro/tramway'];
        if(strstr($tabN[$i],"Noir"))
            echo $_SESSION['Joueur']['Bateau'];
    }
    /* $j = 0 ;
     foreach($tabI as $i)
     {
         echo '<img class='.$j.' src='.$i.' alt="" height="50px" width="50px" />' ;
         $j++;
     }*/
}

function incrementVictoire($connexion)
{
    incrementVictoireBD($connexion);
}

function norme($posQ)
{
    return sqrt( $posQ['longitude']*$posQ['longitude'] + $posQ['latitude']*$posQ['latitude']);
}

function absolutenorme($idQ2,$QA)
{
    $ans=norme($idQ2)-norme($QA);
    return sqrt($ans*$ans);
}
?>

<?php function menuDeplacementJoueurBasique($connexion)
{
    //affiche un menu de deplacement pour la strategie basique?>
    <?=
        '<div class="formJ">
        <div class=menuJ>
        <h4>Menu de déplacement</h4>
        <p>Position actuel:';
    ?>
    <?php echo $_SESSION['Joueur']['pos'];
    ?>
    <?=
        '</p>
        <form method = "POST" action = "#" >
        <label for="Direction">Direction quartier</label>';
    ?>
    <?php getChoixQuartierJoueur($connexion) ?>
    <?=			'<input type="hidden" name="PAGE" value="Game" />
                <input type="submit" name="ts" value="Tour Suivante" />
                </form>
                </div>
                </div>';
}?>

<?php function menuDeplacementJoueurEconome($connexion)
{
    //affiche un menu de deplacement pour la strategie econome?>
    <?=
        '<div class="formJ">
        <div class=menuJ>
        <h4>Menu de déplacement</h4>
        <p>Position actuel:';
    ?>
    <?php echo $_SESSION['Joueur']['pos'];
    ?>
    <?=
        '</p>
        <form method = "POST" action = "#" >
        <label for="Direction">Direction quartier</label>';
    ?>
    <?php getChoixQuartierJoueur($connexion) ?>
    <?=			'<input type="hidden" name="PAGE" value="Game" />
                <input type="submit" name="ts" value="Tour Suivante" />
                </form>
                </div>';
    ?>
    <?php
    echo '<div class=ticketJoueur>';
    touteimage($connexion);
    echo '</div>';
    echo '</div>';
    echo '<p>Deplacement de MisterX:</p>';
    echo  '<div class="tableTicket">';
    echo '<table>';
    getTableauTicketMisterXEconome($connexion);
    echo '</table>';
    echo '</div>';
}?>

<?php function menuDeplacementJoueurPistage($connexion)
{
    //affiche un menu de deplacement pour la strategie econome?>
    <?=
        '<div class="formJ">
        <div class=menuJ>
        <h4>Menu de déplacement</h4>
        <p>Position actuel:';
    ?>
    <?php echo $_SESSION['Joueur']['pos'];
    ?>
    <?=
        '</p>
        <form method = "POST" action = "#" >
        <label for="Direction">Direction quartier</label>';
    ?>
    <?php getChoixQuartierJoueur($connexion) ?>
    <?=			'<input type="hidden" name="PAGE" value="Game" />
                <input type="submit" name="ts" value="Tour Suivante" />
                </form>
                </div>';
    ?>
    <?php
    echo '<div class=ticketJoueur>';
    touteimage($connexion);
    echo '</div>';
    echo '</div>';
    echo '<p>Deplacement de MisterX:</p>';
    echo  '<div class="tableTicket">';
    echo '<table>';
    getTableauTicketMisterXPistage($connexion);
    echo '</table>';
    echo '</div>';
}?>

