<?php
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
            echo "<td>";
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
            echo "<td>";
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
            echo "<td>";
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
?>
