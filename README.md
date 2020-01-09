# Distanciel xQuery - Données sur le web

## A propos 

Ce projet a pour but de mettre en application xQuery permettant la transformation de document XML.
On a également ici utiliser d'autres technologies permettant de mettre en concurrence plusieurs solutions afin de les comparer.
Ces programmes sont issus du projet disponible à l'adresse [https://github.com/MaximeHenaff/TP_Donnees_Web](https://github.com/MaximeHenaff/TP_Donnees_Web).
On a utilisé ici Xquery, XSLT, Sax ainsi que DOM.

L'utilisation de ces technologies sur un document de taille conséquente nous permet d'observer ces différentes technologies en action et d'analyser leur performances.

## Pré-requis et installation

#### Prérequis :
  - [hyperfine](https://github.com/sharkdp/hyperfine) version 1.9

#### Installation :
  - Si vous utilisez Git : ``` git clone https://github.com/LucasDetre/xQuery ``` puis déplacez vous à la racine du projet.
  - Sinon déplacez vous seulement dans le dossier racine du projet.

## Utilisation 

  - Pour utiliser l'application en mode assisté, lancez en bash ``` ./launch ``` et suivez les indications. (attention pour lancer le monitoring ``hyperfine`` doit être installé).
  - Sinon :
    - ___xQuery___ : 
    ``` 
        cd programmes 
        time java -cp saxon9he.jar net.sf.saxon.Query -q:xquery.xq -s:../entree/assemblee1920.xml -o:../sortie/sortie.xml
    ```
    - ___XSLT___ : 
    ``` 
        cd programmes 
        time java -jar saxon9he.jar -xsl:XSLT.xsl -s:../entree/assemblee1920.xml -o:../sortie/sortie.xml
    ```
    - ___SAX___ :
    ``` 
        cd programmes 
        time php sax.php > ../sortie/sortie.xml
    ```
    - ___DOM SANS XPATH___ : 
    ``` 
        cd programmes 
        time php dom_sans_xpath.php > ../sortie/sortie.xml
    ```
    - ___DOM AVEC XPATH___ :
    ``` 
        cd programmes 
        time php dom_avec_xpath.php > ../sortie/sortie.xml
    ```
    - ___MONITORING___ :
    ``` 
        cd programmes
        hyperfine 'php dom_avec_xpath.php > ../sortie/sortie.xml' 'php dom_sans_xpath.php > ../sortie/sortie.xml' 'php sax.php > ../sortie/sortie.xml' 'java -jar saxon9he.jar -xsl:XSLT.xsl -s:../entree/assemblee1920.xml -o:../sortie/sortie.xml' --export-markdown ../reporting/REPORTING.md --export-json ../reporting/REPORTING.json --export-csv ../reporting/REPORTING.csv
    ```
        
