<?php

    /**
     * main
     * 
     * Fonction principale permettant d'importer les données XML depuis le document source
     * puis de les transformer afin de les afficher proprement dans le nouveau format voulu
     *
     * @return void
     */
    function main() {

        $dom = new DomDocument();
        $dom->preserveWhiteSpace = false;
        $dom->load('../entree/assemblee1920.xml');

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('ns','http://schemas.assemblee-nationale.fr/referentiel');
        $xpath->registerNamespace('xsi','http://www.w3.org/2001/XMLSchema-instance');
        
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<!DOCTYPE information\n";
        echo " SYSTEM \"info.dtd\">\n";
        echo "<information>\n";

        $acteurs = $xpath->evaluate("/assemblée/liste-acteurs/ns:acteur[ns:uid/text() = /assemblée/liste-scrutins/ns:scrutin[ns:titre[contains(.,\"l'information\")]]/ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef/text()]");
        // pour chaque acteur ($acteur->item($i)) on récupère les informations dont nous avons besoin qu'on stocke dans un tableau $acteur_array
        for ($i=0;$acteurs->length > $i;$i++) {
            $acteur = $acteurs->item($i);
            // on fait une requête xpath et on récupère le noeud associé à cette requête puis le texte associé à ce noeud
            $act_nom = $xpath->evaluate("./ns:etatCivil/ns:ident/ns:nom", $acteur)->item(0)->nodeValue;
            $act_prenom = $xpath->evaluate("./ns:etatCivil/ns:ident/ns:prenom", $acteur)->item(0)->nodeValue;
            $act_uid = $xpath->evaluate("./ns:uid", $acteur)->item(0)->nodeValue;
            // on fait une requête xpath et on récupère le/les noeud(s) associé(s) à cette requête
            $scrutins = $xpath->evaluate("/assemblée/liste-scrutins/ns:scrutin[ns:titre[contains(.,\"l'information\")] and ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef/text() = \"$act_uid\"]");
            $acteur_array[$act_nom." ".$act_prenom] = [
                "nom"=>$act_nom,
                "prenom"=>$act_prenom,
                "sc"=>[]
            ];

            // pour chaque scrutin de cet acteur on récupère les informations dont nous avons besoin qu'on stocke, dans un tableau $sc lui même dans le tableau $acteur_array
            for($j=0; $scrutins->length > $j; $j++) {
                $scrutin = $scrutins->item($j);
                $sc_mandatRef = $xpath->evaluate("ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant[ns:acteurRef/text() = \"$act_uid\"]/ns:mandatRef/text()",$scrutin)->item(0)->nodeValue;
                $sc_organeRef = $xpath->evaluate("ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe[ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef/text() = \"$act_uid\"]/ns:organeRef/text()", $scrutin)->item(0)->nodeValue;
                $sc_titre = $xpath->evaluate("ns:titre",$scrutin)->item(0)->nodeValue;
                $sc_sort = $xpath->evaluate("ns:sort/ns:code",$scrutin)->item(0)->nodeValue;
                $sc_date = $xpath->evaluate("ns:dateScrutin",$scrutin)->item(0)->nodeValue;
                $sc_grp = $xpath->evaluate("/assemblée/liste-organes/ns:organe[ns:uid/text() = \"$sc_organeRef\"]/ns:libelle")->item(0)->nodeValue;
                
                $mandat_query = "ns:mandats/ns:mandat[ns:uid/text() = \"$sc_mandatRef\"]";
                $sc_mandat = $xpath->evaluate("$mandat_query/ns:infosQualite/ns:libQualite",$acteur)->item(0)->nodeValue;               
                $sc_legislature = $xpath->evaluate("$mandat_query/ns:legislature/@xsi:nil = 'true'",$acteur) ? $xpath->evaluate("$mandat_query/ns:legislature",$acteur)->item(0)->nodeValue : "15";
                
                $parDelegation_query = "ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant[ns:acteurRef/text() = \"$act_uid\"]/ns:parDelegation/text()";
                $sc_present = $xpath->evaluate("$parDelegation_query = 'false'",$scrutin) ? "Oui" : "Non";

                $sc_mandat .= " Assemblée nationale de la ".$sc_legislature."ème legislature";

                $scrutin_array = [
                    "sc_titre"=>$sc_titre,
                    "sc_sort"=>$sc_sort,
                    "sc_date"=>$sc_date,
                    "sc_mandat"=>$sc_mandat,
                    "sc_grp"=>$sc_grp,
                    "sc_present"=>$sc_present,
                ];
                array_push($acteur_array[$act_nom." ".$act_prenom]["sc"], $scrutin_array);
            }

        }
        // ce tri n'est possible qu'une fois toutes les données récupérées
        ksort($acteur_array);
        // cette boucle est nécessaire afin de traiter les arrays triés précédemment
        foreach($acteur_array as $acteur) {
            echo "\t<act nom=\"".$acteur["prenom"]." ".$acteur["nom"]."\">\n";
            foreach($acteur["sc"] as $scrutin) {
                echo "\t\t<sc nom=\"".$scrutin["sc_titre"]."\"\n";
                echo "\t\t\tsort=\"".$scrutin["sc_sort"]."\"\n";
                echo "\t\t\tdate=\"".$scrutin["sc_date"]."\"\n";
                echo "\t\t\tmandat=\"".$scrutin["sc_mandat"]."\"\n";
                echo "\t\t\tgrp=\"".$scrutin["sc_grp"]."\"\n";
                echo "\t\t\tprésent=\"".$scrutin["sc_present"]."\"/>\n";
            }
            echo "\t</act>\n";
        }
        echo "</information>";
    }

    main();
?>