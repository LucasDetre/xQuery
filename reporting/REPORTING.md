| Command | Mean [s] | Min [s] | Max [s] | Relative |
|:---|---:|---:|---:|---:|
| `java -cp saxon9he.jar net.sf.saxon.Query -q:xquery.xq -s:../entree/assemblee1920.xml -o:../sortie/sortie.xml` | 16.250 ± 0.788 | 15.266 | 17.746 | 5.23 ± 0.29 |
| `php dom_avec_xpath.php > ../sortie/sortie.xml` | 15.007 ± 0.322 | 14.612 | 15.372 | 4.83 ± 0.16 |
| `php dom_sans_xpath.php > ../sortie/sortie.xml` | 3.108 ± 0.079 | 3.043 | 3.309 | 1.00 |
| `php sax.php > ../sortie/sortie.xml` | 8.608 ± 0.108 | 8.463 | 8.836 | 2.77 ± 0.08 |
| `java -jar saxon9he.jar -xsl:XSLT.xsl -s:../entree/assemblee1920.xml -o:../sortie/sortie.xml` | 44.666 ± 2.987 | 40.870 | 49.702 | 14.37 ± 1.03 |
