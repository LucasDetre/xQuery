xquery version "1.0";
declare namespace ns = "http://schemas.assemblee-nationale.fr/referentiel" ;
declare namespace output = "http://www.w3.org/2010/xslt-xquery-serialization";
declare option output:method "xml";
declare option output:media-type "text/xml";
declare option output:omit-xml-declaration "no";
declare option output:indent "yes";
declare option output:doctype-system "info.dtd";

<information> {
    for $acteur in /assemblée/liste-acteurs/ns:acteur
    let $prenomnom := (concat($acteur/ns:etatCivil/ns:ident/ns:prenom, ' ', $acteur/ns:etatCivil/ns:ident/ns:nom))
    let $nomprenom := (concat(lower-case($acteur/ns:etatCivil/ns:ident/ns:nom), ';', $acteur/ns:etatCivil/ns:ident/ns:prenom))
    let $idActeur := $acteur/ns:uid
    order by $nomprenom
    return
        if (count(/assemblée/liste-scrutins/ns:scrutin[contains(ns:titre, "l&apos;information") and ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef = $idActeur]) > 0) then 
            <ac nom="{ $prenomnom }">
            {
                for $scrutin in /assemblée/liste-scrutins/ns:scrutin[contains(ns:titre, "l&apos;information") and ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef = $idActeur]
                let $titre := $scrutin/ns:titre
                let $votantActeurScrutin := $scrutin/ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant[ns:acteurRef = $idActeur]
                let $mandatActeur := /assemblée/liste-acteurs/ns:acteur/ns:mandats/ns:mandat[ns:uid eq $votantActeurScrutin/ns:mandatRef]

                order by $titre
                return
                    <sc nom="{$titre}"
                        sort="{$scrutin/ns:sort/ns:code}"
                        date="{$scrutin/ns:dateScrutin}"
                        mandat="{$mandatActeur/ns:infosQualite/ns:libQualite} Assemblée nationale de la {$mandatActeur/ns:legislature} ème législature"
                        grp="{/assemblée/liste-organes/ns:organe[ns:uid eq $votantActeurScrutin/../../../../ns:organeRef]/ns:libelle}"
                        présent="{if ($votantActeurScrutin/ns:parDelegation eq 'false') then "Oui" else "Non"}"/>
            }
            </ac>
        else ()
} </information>
