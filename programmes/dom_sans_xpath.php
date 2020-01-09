<?php
  
    /**
     * select_all_acteurs
     *
     * Fonction qui permet à partir du Node <liste-acteurs> de créer et retourner un tableau d'acteurs sous la forme :
     *      liste_acteurs_array : [
     *          acteurs[
     *              "act_uid",
     *              "prenom",
     *              "nom",
     *              "mandats" : [
     *                  "mandat_uid",
     *                  "legislature",
     *                  "libQualite",
     *                  "organeRef"                 
     *              ]
     *          ]
     *      ]
     * 
     * @param [Node] $liste_acteurs_node
     * @return [Array] $liste_acteurs_array
     */
    function select_all_acteurs($liste_acteurs_node) {
        $liste_acteurs_array = [];
        // /assemblée/liste-acteurs  
        foreach ($liste_acteurs_node->childNodes as $acteur) {
            $acteur_array = [];
            // /assemblée/liste-acteurs/acteur/uid
            $act_uid = $acteur->firstChild;
            // /assemblée/liste-acteurs/acteur/ident/prenom
            $prenom = $act_uid->nextSibling->firstChild->firstChild->nextSibling;
            // /assemblée/liste-acteurs/acteur/ident/nom
            $nom = $prenom->nextSibling;
            // /assemblée/liste-acteurs/acteur/mandats
            $mandats = $acteur->lastChild;
            $mandats_array = [];
            foreach ($mandats->childNodes as $mandat) {
                // /assemblée/liste-acteurs/acteur/mandats/mandat/uid
                $mandat_uid = $mandat->firstChild;
                // /assemblée/liste-acteurs/acteur/mandats/mandat/legislature
                $legislature = $mandat_uid->nextSibling->nextSibling;
                // /assemblée/liste-acteurs/acteur/mandats/mandat/infosQualite/libQualite
                $libQualite = $legislature->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->firstChild->nextSibling;
                // /assemblée/liste-acteurs/acteur/mandats/mandat/organes/organeRef
                $organeRef = $legislature->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->firstChild;
                $mandat_array = [
                    "mandat_uid" => $mandat_uid->nodeValue,
                    "legislature" => $legislature->nodeValue,
                    "libQualite" => $libQualite->nodeValue,
                    "organeRef" => $organeRef->nodeValue
                ];
                array_push($mandats_array,$mandat_array);
            }
            $acteur_array = [
                "act_uid" => $act_uid->nodeValue,
                "prenom" => $prenom->nodeValue,
                "nom" => $nom->nodeValue,
                "mandats" => $mandats_array
            ];
            $liste_acteurs_array["$nom->nodeValue $prenom->nodeValue"] = $acteur_array;
        }
        ksort($liste_acteurs_array);
        return $liste_acteurs_array;
    }

    /**
     * select_all_scrutins
     *
     * Fonction qui permet à partir du Node <liste-scrutins> de créer et retourner un tableau de scrutins sous la forme :
     *      liste_scrutins_array : [
     *          scrutins[
     *              "sc_uid",
     *              "titre",
     *              "date",
     *              "sorte",
     *              "groupes" : [
     *                  "organeRef",
     *                  "votants" : [
     *                      "act_uid",
     *                      "parDelegation"
     *                  ]                  
     *              ]
     *          ]
     *      ]
     * 
     * @param [Node] $liste_scrutins_node
     * @return [Array] $liste_scrutins_array
     */
    function select_all_scrutins($liste_scrutins_node) {
        $liste_scrutins_array = [];
        foreach($liste_scrutins_node->childNodes as $scrutin) {
            // /assemblée/liste-scrutins/scrutin/uid
            $sc_uid = $scrutin->firstChild;
            // /assemblée/liste-scrutins/scrutin/dateScrutin
            $date = $sc_uid->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling;
            // /assemblée/liste-scrutins/scrutin/sort/code
            $sort = $date->nextSibling->nextSibling->nextSibling->firstChild;
            // /assemblée/liste-scrutins/scrutin/titre
            $titre = $date->nextSibling->nextSibling->nextSibling->nextSibling;
            $scrutin_array = [];
            if ( strpos($titre->nodeValue, "l'information") !== false) {
                // /assemblée/liste-scrutins/scrutin/ventilationVotes
                $ventilationVote = $titre->nextSibling->nextSibling->nextSibling->nextSibling->nextSibling;
                // /assemblée/liste-scrutins/scrutin/ventilationVote/organe/groupes
                $groupes = $ventilationVote->firstChild->firstChild->nextSibling;
                $groupes_array = [];
                foreach ($groupes->childNodes as $groupe) {
                    // /assemblée/liste-scrutins/scrutin/ventilationVote/organe/groupes/groupe/organeRef
                    $organeRef = $groupe->firstChild;
                    // /assemblée/liste-scrutins/scrutin/ventilationVote/organe/groupes/groupe/vote/decompteNominatif/pours
                    $votants = $organeRef->nextSibling->nextSibling->firstChild->nextSibling->nextSibling->firstChild->nextSibling;
                    $votants_array = [];
                    foreach ($votants->childNodes as $votant) {
                        // /assemblée/liste-scrutins/scrutin/ventilationVote/organe/groupes/groupe/vote/decompteNominatif/pours/votant/acteurRef
                        $acteurRef = $votant->firstChild;
                        // /assemblée/liste-scrutins/scrutin/ventilationVote/organe/groupes/groupe/vote/decompteNominatif/pours/votant/parDelegation
                        $parDelegation = $votant->lastChild;
                        $votant_array = [
                            "act_uid" => $acteurRef->nodeValue,
                            "parDelegation" => $parDelegation->nodeValue
                        ];
                        array_push($votants_array,$votant_array);

                    }
                    $groupe_array = [
                        "organeRef" => $organeRef->nodeValue,
                        "votants" => $votants_array
                    ];
                    array_push($groupes_array,$groupe_array);
                }
                $scrutin_array = [
                    "sc_uid" => $sc_uid->nodeValue,
                    "titre" => $titre->nodeValue,
                    "date" => $date->nodeValue,
                    "sort" => $sort->nodeValue,
                    "groupes" => $groupes_array
                ];
                array_push($liste_scrutins_array, $scrutin_array);
            }
        }
        return $liste_scrutins_array;
    }

    /**
     * select_all_organes
     *
     * Fonction qui permet à partir du Node <liste-organes> de créer et retourner un tableau d'organes sous la forme :
     *      liste_organes_array[
     *          organes[
     *              "org_uid",
     *              "libelle"
     *          ]
     *      ]
     * 
     * @param [Node] $liste_organes_node
     * @return array $liste_organes_array
     */
    function select_all_organes($liste_organes_node) {
        $liste_organes_array = [];
        foreach($liste_organes_node->childNodes as $organe) {
            // /assemblée/liste-organes/organe/uid
            $org_uid = $organe->firstChild;
            foreach ($organe->childNodes as $organeNode) {
                // /assemblée/liste-organes/organe/libelle
                if ($organeNode->nodeName == "libelle") $libelle = $organeNode;        
            }
            $organe_array = [
                "org_uid" => $org_uid->nodeValue,
                "libelle" => $libelle->nodeValue
            ];
            array_push($liste_organes_array, $organe_array);
        }
        return $liste_organes_array;
    }

    /**
     * infos_necessaires_acteurs
     *
     * Fonction qui permet à partir des tableaux contenant les acteurs, les scrutins et les organes du document source
     * de créer un tableau d'acteurs sous la forme :
     *      return_acteurs : [
     *          acteurs[
     *              "prenom",
     *              "nom",
     *              "scrutins" : [
     *                  "titre",
     *                  "sort",
     *                  "date", 
     *                  "libQualite",
     *                  "legislature",
     *                  "organe_libelle",
     *                  "present",
     *              ]
     *          ]
     *      ]
     * 
     * @param [Array] $acteurs
     * @param [Array] $scrutins
     * @param [Array] $organes
     * @return [Array] $return_acteurs
     */
    function infos_necessaires_acteurs($acteurs, $scrutins,$organes) {
        $return_acteurs = [];
        // pour tous les acteurs du fichier d'origine
        foreach($acteurs as $acteur) {
            $acteurOK = false;
            $return_acteur = [];
            $scrutins_par_acteurs = [];
            // pour tous les scrutins dont le titre contient "l'information"
            foreach ($scrutins as $scrutin) {
                // pour tous les groupes de ce scrutin
                foreach ($scrutin["groupes"] as $groupe) {
                    // pour tous les votants de ce groupe
                    foreach ($groupe["votants"] as $votant) {
                        // si ce votant correspond à cet acteur
                        if ($acteur["act_uid"] == $votant["act_uid"]) {
                            $acteurOK = true;
                            // pour tous les mandats de cet acteur
                            foreach($acteur["mandats"] as $mandat) {
                                // pour tous les organes du fichier d'origine
                                foreach ($organes as $organe) {
                                    // si l'organe du groupe auquel appartient le votant correspond à cet organe
                                    if ($groupe["organeRef"] == $organe["org_uid"]) {
                                        // si le champ parDelegation vaut "true" alors le votant n'était pas présent à l'assemblée nationale au moment du vote.
                                        if ($votant["parDelegation"] == "true") $scrutins_par_acteurs[$scrutin["sc_uid"]] = [$scrutin["titre"], $scrutin["sort"], $scrutin["date"], $mandat["libQualite"], $mandat["legislature"], $organe["libelle"], "Non"];
                                        else $scrutins_par_acteurs[$scrutin["sc_uid"]] = [$scrutin["titre"], $scrutin["sort"], $scrutin["date"], $mandat["libQualite"], $mandat["legislature"], $organe["libelle"], "Oui"];
                                    }
                                }
                            }
                        }      
                    }
                }
            }
            if ($acteurOK) {
                $return_acteur = [
                    "prenom" => $acteur["prenom"],
                    "nom" => $acteur["nom"],
                    "scrutins" => $scrutins_par_acteurs
                ];
                array_push($return_acteurs,$return_acteur);
            }
        }
        return $return_acteurs;
    }

    /**
     * affichage
     *
     * Fonction qui permet à partir d'un tableau d'acteurs et des scrutins valables qui lui sont associés
     * de générer un document XML respectant le format attendu
     * 
     * @param [Array] $acteurs
     * @return void
     */
    function affichage($acteurs) {
        
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<!DOCTYPE information\n SYSTEM \"info.dtd\">\n";
        echo "<information>\n";
        foreach($acteurs as $acteur) {
            echo "\t<act nom=\"".$acteur["prenom"]." ".$acteur["nom"]."\">\n";
            foreach($acteur["scrutins"] as $scrutin) {
                echo "\t\t  <sc nom=\"".$scrutin[0]."\"\n";
                echo "\t\t\t    sort=\"".$scrutin[1]."\"\n";
                echo "\t\t\t    date=\"".$scrutin[2]."\"\n";
                echo "\t\t\t    mandat=\"".$scrutin[3]." Assemblée nationale de la ".$scrutin[4]."ème législature\"\n";
                echo "\t\t\t    grp=\"".$scrutin[5]."\"\n";
                echo "\t\t\t    présent=\"".$scrutin[6]."\"/>\n";
            }
            echo "\t</act>\n";
        }
    }

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
        $dom->validateOnParse = true;
        $dom->preserveWhiteSpace = false;
        $dom->load('../entree/assemblee1920.xml');

        
        $assemblee = $dom->firstChild;
        foreach($assemblee->childNodes as $node) {
            // /assemblée/liste-acteurs
            if ($node->nodeName == "liste-acteurs") $liste_acteurs_node = $node;
            // /assemblée/liste-scrutins
            if ($node->nodeName == "liste-scrutins") $liste_scrutins_node = $node;
            // /assemblée/liste-organes
            if ($node->nodeName == "liste-organes") $liste_organes_node = $node; 
            
        }

        $liste_acteurs = select_all_acteurs($liste_acteurs_node);
        $liste_scrutins = select_all_scrutins($liste_scrutins_node);
        $liste_organes = select_all_organes($liste_organes_node);

        $acteurs = infos_necessaires_acteurs($liste_acteurs, $liste_scrutins, $liste_organes);

        affichage($acteurs);
    }

    main();
?>