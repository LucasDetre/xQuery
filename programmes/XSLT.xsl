<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns="http://schemas.assemblee-nationale.fr/referentiel" xmlns:xalan="xml.apache.org/xalan"
    version="2.0">
    <xsl:strip-space elements="*"/>
    <xsl:output method="xml" indent="yes" encoding="UTF-8" doctype-system="info.dtd" xalan:indent-amount="4"/>
    <xsl:variable name="apos">'</xsl:variable>
    <xsl:template match="/">
        <xsl:element name="information">
            <xsl:apply-templates select="/assemblée/liste-acteurs/ns:acteur[
                    ns:uid/text() = /assemblée/liste-scrutins/ns:scrutin[
                        ns:titre[
                            contains(.,concat('l',$apos,'information'))
                        ]
                    ]
                    /ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef/text()
                ]">
                <xsl:sort data-type="text" select="ns:etatCivil/ns:ident/ns:nom"/>
                <xsl:sort data-type="text" select="ns:etatCivil/ns:ident/ns:prenom"/>
                
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>
    <xsl:template match="ns:acteur">
        <xsl:param name="act_id" select="ns:uid"/>
        <xsl:element name="act">
            <xsl:attribute name="nom">
                <xsl:value-of select="ns:etatCivil/ns:ident/ns:prenom"/>
                <xsl:text> </xsl:text>
                <xsl:value-of select="ns:etatCivil/ns:ident/ns:nom"/>
            </xsl:attribute>
            <xsl:apply-templates select="/assemblée/liste-scrutins/ns:scrutin[
                    ns:titre[
                        contains(.,concat('l',$apos,'information'))
                    ] and
                    ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef/text() = $act_id
                ]">
                <xsl:with-param name="act_id" select="$act_id"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>
    <xsl:template match="ns:scrutin">
        <xsl:param name="act_id"/>
        <xsl:param name="mandat_id" select="ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant[ns:acteurRef/text() = $act_id]/ns:mandatRef/text()"/>
        <xsl:param name="organe_id" select="ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe[ns:vote/ns:decompteNominatif/ns:pours/ns:votant/ns:acteurRef/text() = $act_id]/ns:organeRef/text()"/>
        <xsl:element name="sc">
            <xsl:attribute name="nom">
                <xsl:value-of select="ns:titre"/>             
            </xsl:attribute>
            <xsl:attribute name="sort">
                <xsl:value-of select="ns:sort/ns:code"/>                
            </xsl:attribute>
            <xsl:attribute name="date">
                <xsl:value-of select="ns:dateScrutin"/>                
            </xsl:attribute>
            <xsl:attribute name="grp">
                <xsl:value-of select="/assemblée/liste-organes/ns:organe[ns:uid/text() = $organe_id]/ns:libelle"/>             
            </xsl:attribute>         
            <xsl:attribute name="mandat">
                <xsl:value-of select="/assemblée/liste-acteurs/ns:acteur/ns:mandats/ns:mandat[ns:uid/text() = $mandat_id]/ns:infosQualite/ns:libQualite"/>
                <xsl:text> de la </xsl:text>
                <xsl:choose>
                    <xsl:when test="/assemblée/liste-acteurs/ns:acteur/ns:mandats/ns:mandat[ns:uid/text() = $mandat_id]/ns:legislature/@xsi:nil = 'true'">
                        <xsl:text>15</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="/assemblée/liste-acteurs/ns:acteur/ns:mandats/ns:mandat[ns:uid/text() = $mandat_id]/ns:legislature"/>
                    </xsl:otherwise>
                </xsl:choose>
                <xsl:text>ème législature</xsl:text>                
            </xsl:attribute>      
            <xsl:attribute name="présent">
                <xsl:choose>
                    <xsl:when test="ns:ventilationVotes/ns:organe/ns:groupes/ns:groupe/ns:vote/ns:decompteNominatif/ns:pours/ns:votant[ns:acteurRef/text() = $act_id]/ns:parDelegation/text() = 'false'">
                        <xsl:text>Oui</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>Non</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>  
</xsl:stylesheet>