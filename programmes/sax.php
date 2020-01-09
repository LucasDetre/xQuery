<?php
  header('Content-type: text/xml; Encoding: utf-8');
  include('./Sax4PHP/Sax4PHP.php');

  class information extends DefaultHandler {

	/***
		Variables permettant la bonne utilisation de sax et en particulier la gestion des noeuds textes 
	*/
    public $textNode;
    public $multipleText;

    /***
		Arrays permettant de stocker les informations depuis le document initial
    */
    public $acteurs;
    public $acteur; // ["act_uid", "prenom", "nom", "mandats"]
    public $mandat; // ["mandat_uid","legislature","libQualite","organeRef"]

    public $scrutins;
    public $scrutin; // ["sc_uid", "titre", "date", "sort", "groupes"]
    public $groupe; // ["organeRef", "votants"]
    public $votant; // ["act_uid", "parDelegation"]

    public $organes;
    public $organe; // ["org_uid", "libelle"]

    /*** Flags permettant de nous situer dans la structure du document
      isXXXX signifie que l'on se situe à l'intérieur de la balise XXXX
    */
    public $isActeur;
    public $isIdent;
    public $isMandat;

    public $isScrutin;
    public $isGroupe;
    public $isPours;
    public $isVotant;

    public $isOrgane;

    public $titreIsOK;

    function __construct()
    {
		/** Initialisations des tableaux globaux */
		$this->acteurs = [];
		$this->scrutins = [];
		$this->organes = [];
    }

    function startDocument()
    {
	}	

    function endDocument()
    {
		$acteurs = $this->infos_necessaires_acteurs($this->acteurs, $this->scrutins, $this->organes);
		$this->affichage($acteurs);
	}
	
    function characters($txt) {
		$txt = trim($txt);
		if ($this->multipleText) {
			$this->textNode .= $txt;
		} else {
			$this->multipleText = true;
			$this->textNode = $txt;
		}
    }

    function startElement($name, $att)
    {
		switch($name) {

			/** Gestion des acteurs */

			case "acteur":
				$this->acteur = [];
				$this->isActeur = true;
			break;
			case "ident" :
				$this->isIdent = true;
			break;
			case "mandats":
				$this->acteur["mandats"] = [];
			break;
			case "mandat":
				$this->isMandat = true;
			break;

			/** Gestion des scrutins */
			case "scrutin":
				$this->scrutin = [];
				$this->scrutin["sc_uid"] = "";
				$this->scrutin["titre"] = "";
				$this->scrutin["date"] = "";
				$this->scrutin["sort"] = "";
				$this->scrutin["groupes"] = [];
				$this->isScrutin = true;
				$this->titreIsOK=false;
			break;
			case "groupe":
				$this->groupe["organeRef"] = "";
				$this->groupe["votants"] = [];
				if ($this->titreIsOK) $this->isGroupe = true;
			break;
			case "pours":
				if($this->titreIsOK) {
					$this->isPours=true;
					$this->scrutin["votants"] = [];
				}
			break;
			case "votant":
				$this->votant["act_uid"] = "";
				$this->votant["parDelegation"] = "";
				if ($this->isPours) $this->isVotant = true;
			break;

			/** Gestion des organes */
			case "organe":
				$this->organe["org_uid"] = "";
				$this->organe["libelle"] = "";
				$this->isOrgane = true;
			break;

			default:;
		}
    }
    function endElement($name)
    {
		if ($this->multipleText) {
			$this->multipleText = false;
		}
		switch($name) {

			/** Gestion des acteurs */
			case "liste-acteurs": 
				ksort($this->acteurs);
			break;
			case "acteur":
				$this->acteurs[$this->acteur['nom'] . " " . $this->acteur['prenom']] = $this->acteur;
				$this->isActeur = false;
			break;
			case "uid":
				if ($this->isMandat) $this->mandat["mandat_uid"] = $this->textNode;
				elseif ($this->isScrutin) $this->scrutin["sc_uid"] = $this->textNode;
				elseif ($this->isOrgane) $this->organe["org_uid"] = $this->textNode;
				elseif ($this->isActeur) $this->acteur["act_uid"] = $this->textNode; $this->isActeur=false;
			break;
			case "ident" :
				$this->isIdent = false;
			break;
			case "prenom":
				if ($this->isIdent)$this->acteur["prenom"] = $this->textNode;
			break;
			case "nom":
				if ($this->isIdent) $this->acteur["nom"] = $this->textNode;
			break;
			case "mandat":
				array_push($this->acteur["mandats"], $this->mandat);
				$this->isMandat = false;
			break;
			case "legislature":
				if($this->isMandat) $this->mandat["legislature"] = $this->textNode;
			break;
			case "libQualite":
				if($this->isMandat) $this->mandat["libQualite"] = $this->textNode;
			break;
			case "organeRef":
				if($this->isMandat) $this->mandat["organeRef"] = $this->textNode;
				elseif ($this->isGroupe) $this->groupe["organeRef"] = $this->textNode;
			break;

			/** Gestion des scrutins */
			case "scrutin":
				if ($this->titreIsOK) array_push($this->scrutins, $this->scrutin);
				$this->isScrutin= false;
			break;
			case "titre":
				if(strpos($this->textNode, "l'information") !== false) {
					$this->scrutin["titre"] = $this->textNode;
					$this->textNode = "";
					$this->titreIsOK=true;
				}
			break;
			case "dateScrutin":
				$this->scrutin["date"] = $this->textNode;
			break;
			case "code":
				$this->scrutin["sort"] = $this->textNode;
			break;
			case "groupe":
				if($this->isGroupe) array_push($this->scrutin["groupes"], $this->groupe);
				$this->isGroupe = false;
			break;
			case "pours":
				$this->isPours = false;
			break;
			case "votant":
				if($this->isVotant) array_push($this->groupe["votants"], $this->votant);
				$this->isVotant = false;
			break;
			case "acteurRef":
				if($this->isVotant) $this->votant["act_uid"] = $this->textNode;
			break;
			case "parDelegation":
				if($this->isVotant) $this->votant["parDelegation"] = $this->textNode;
			break;

			/** Gestion des organes */
			case "organe":
				if ($this->isOrgane) array_push($this->organes, $this->organe);
				$this->isOrgane = false;
			break;
			case "libelle":
				if ($this->isOrgane) $this->organe["libelle"] = $this->textNode;
			break;
			default:;
		}
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
}

try {
	$sax = new SaxParser(new information());
	$sax->parse('../entree/assemblee1920.xml');  
} 
catch(SAXException $e) { echo "\n",$e; } 
catch(Exception $e) { echo "Capture de l'exception par défaut\n", $e; }

