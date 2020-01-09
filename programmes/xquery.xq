xquery version "1.0";
declare namespace ns = "http://schemas.assemblee-nationale.fr/referentiel" ;
declare namespace output = "http://www.w3.org/2010/xslt-xquery-serialization";
declare option output:method "xml";
declare option output:media-type "text/xml";
declare option output:omit-xml-declaration "no";
declare option output:indent "yes";
declare option output:doctype-system "info.dtd";

<information> 
{
    (: Pour chaque acteur :)
    for $acteur in /assemblée/liste-acteurs/ns:acteur
    (: Trié alphabétiquement sur son nom de famille et son prénom:)
    order by (concat(lower-case($acteur/ns:etatCivil/ns:ident/ns:nom), ';', $acteur/ns:etatCivil/ns:ident/ns:prenom))
    return
        (: Si cet acteur a voté "pour" à un scrutin dont le titre contient "l'information" :)
        if (count(/assemblée/liste-scrutins/ns:scrutin[contains(ns:titre, "l&apos;information") and ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef = $acteur/ns:uid]) > 0) then 
            (: On affiche son Prénom et Nom :)
            <ac nom="{(concat($acteur/ns:etatCivil/ns:ident/ns:prenom, ' ', $acteur/ns:etatCivil/ns:ident/ns:nom))}">
            {
                (: Pour chaque scrutin concerné (pour lequel l'acteur a voté "pour" contenant "l'information" dans son titre) :)
                for $scrutin in /assemblée/liste-scrutins/ns:scrutin[contains(ns:titre, "l&apos;information") and ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef = $acteur/ns:uid]
                (: On récupère le votant associé à ce scrutin :)
                let $votantActeurScrutin := $scrutin/ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant[ns:acteurRef = $acteur/ns:uid]
                (: On récupère le mandat associé à ce votant :)
                let $mandatActeur := /assemblée/liste-acteurs/ns:acteur/ns:mandats/ns:mandat[ns:uid eq $votantActeurScrutin/ns:mandatRef]
                (: On affiche les informations voulues :)
                return
                    <sc nom="{$scrutin/ns:titre}"
                        sort="{$scrutin/ns:sort/ns:code}"
                        date="{$scrutin/ns:dateScrutin}"
                        mandat="{$mandatActeur/ns:infosQualite/ns:libQualite} Assemblée nationale de la {$mandatActeur/ns:legislature} ème législature"
                        grp="{/assemblée/liste-organes/ns:organe[ns:uid eq $votantActeurScrutin/../../../../ns:organeRef]/ns:libelle}"
                        présent="{if ($votantActeurScrutin/ns:parDelegation eq 'false') then "Oui" else "Non"}"/>
            }
            </ac>
        else ()
} 
</information>
