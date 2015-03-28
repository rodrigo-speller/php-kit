<?php
/*
Mail Parse Lib para PHP
Copyright (c) 2015 Primos Tecnologia da Informação Ltda.

É permitida a distribuição irrestrita desta obra, livre de taxas e encargos,
incluindo, e sem limitar-se ao uso, cópia, modificação, combinação e/ou
publicação, bem como a aplicação em outros trabalhos derivados deste.

A mensagem de direito autoral:
    Mail Parse Lib para PHP
    Copyright (c) 2015 Primos Tecnologia da Informação Ltda.
deverá ser incluída em todas as cópias ou partes do obra derivado que permitam a
inclusão desta informação.

O autor desta obra poderá alterar as condições de licenciamento em versões
futuras, porém, tais alterações serão vigentes somente a partir da versão
alterada.

O LICENCIANTE OFERECE A OBRA “NO ESTADO EM QUE SE ENCONTRA” (AS IS) E NÃO PRESTA
QUAISQUER GARANTIAS OU DECLARAÇÕES DE QUALQUER ESPÉCIE RELATIVAS À ESTA OBRA,
SEJAM ELAS EXPRESSAS OU IMPLÍCITAS, DECORRENTES DA LEI OU QUAISQUER OUTRAS,
INCLUINDO, SEM LIMITAÇÃO, QUAISQUER GARANTIAS SOBRE A TITULARIDADE, ADEQUAÇÃO
PARA QUAISQUER PROPÓSITOS, NÃO-VIOLAÇÃO DE DIREITOS, OU INEXISTÊNCIA DE
QUAISQUER DEFEITOS LATENTES, ACURACIDADE, PRESENÇA OU AUSÊNCIA DE ERROS, SEJAM
ELES APARENTES OU OCULTOS. REVOGAM-SE AS PERMISSÕES DESTA LICENÇA EM JURISDIÇÕES
QUE NÃO ACEITEM A EXCLUSÃO DE GARANTIAS IMPLÍCITAS.

EM NENHUMA CIRCUNSTÂNCIA O LICENCIANTE SERÁ RESPONSÁVEL PARA COM VOCÊ POR
QUAISQUER DANOS, ESPECIAIS, INCIDENTAIS, CONSEQÜENCIAIS, PUNITIVOS OU
EXEMPLARES, ORIUNDOS DESTA LICENÇA OU DO USO DESTA OBRA, MESMO QUE O LICENCIANTE
TENHA SIDO AVISADO SOBRE A POSSIBILIDADE DE TAIS DANOS.

[PT]
*** AVISO LEGAL ***
Antes de usar esta obra, você deve entender e concordar com os termos acima.

[ES]
*** AVISO LEGAL ***
Antes de usar este trabajo, debe entender y aceptar las condiciones anteriores.

[EN]
*** LEGAL NOTICE ***
You must understand and agree to the above terms before using this work.

[CH]
*** 法律聲明 ***
使用這項工作之前，了解並同意本許可。

[JP]
*** 法律上の注意事項 ***
この作品を使用する前に、理解し、このライセンスに同意する。

*/

/*
    =====================================
    Mail Parse Lib para PHP
    =====================================
    Versão:      0.0.1
    Criação:     2015-03-26
    Alteração:   2015-03-26
    
    Escrito por: Rodrigo Speller
    E-mail:      rspeller@primosti.com.br
    -------------------------------------

"Mail Parse Lib" é uma biblioteca para a análise, tratamento e manipulação de
dados para uso em serviços de e-mail baseados na [RFC 5321].

Alterações
----------

» 0.0.1

- Lançamento para testes.

Alreações futuras
-----------------

- Suporte aos endereços IDN.

Referências
-----------

[RFC 1035] P. Mockapetris, "Domain names - Implementation and Specification",
           RFC 1035, Novembro/1987, <http://tools.ietf.org/html/rfc1035>.

[RFC 3986] Berners-Lee, T., Fielding, R. e L. Masinter, "Uniform Resource
           Identifier (URI): Generic Syntax",
           RFC 3986, Janeiro/2005, <http://tools.ietf.org/html/rfc3986>.

[RFC 4291] Hinden, R. e S. Deering, "IP Version 6 Addressing Architecture",
           RFC 4291, Fevereiro/2006, <http://tools.ietf.org/html/rfc4291>.

[RFC 5234] Crocker, D. e P. Overell, "Augmented BNF for Syntax Specifications:
           ABNF", STD 68, RFC 5234, Janeiro/2008,
           <http://tools.ietf.org/html/rfc5234>.
         
[RFC 5321] Klensin, J., "Simple Mail Transfer Protocol", RFC 5321, Outubro/2008,
           <http://tools.ietf.org/html/rfc5321>.
         
[RFC 5952] Kawamura, W. e W. Kawashima,
           "A Recommendation for IPv6 Address Text Representation", RFC 5321,
           Agosto/2010, <http://tools.ietf.org/html/rfc5952>.

*/

const MAIL_ADDR_TYPE_HOSTNAME = 0x01;
const MAIL_ADDR_TYPE_IPV4     = 0x02;
const MAIL_ADDR_TYPE_IPV6     = 0x03;
const MAIL_ADDR_TYPE_GENERIC  = 0x04;
const MAIL_ADDR_TYPE_IDN      = 0xFFFFFFFF; /* Uso futuro */

/**
 * Analisa um endereço conforme o formato definido pela [RFC 5321].
 * 
 * Algumas alterações foram feitas no formato em relação ao padrão definido na
 * [RFC 5321].
 * 
 * No lugar de:
 *   Snum        = 1*3DIGIT
 *               ; "representing a decimal integer value in the range 0 through
 *               ; 255" (sic)
 * 
 * Foi usado:
 *   Snum        = "25" %x30-35 | "2" %x30-34 DIGIT | "1" 2DIGIT
 *               | *1(%x31-39) DIGIT
 *               ; A regra "Snum" é utilizada nos valores decimais do IPv4.
 *               ; O padrão original da [RFC 5321] permitia o intervalo de
 *               ; valores de 0 a 999, inclusive a presença de zeros à esquerda.
 * 
 * No lugar de:
 *  IPv6-addr    = IPv6-full / IPv6-comp / IPv6v4-full / IPv6v4-comp
 * 
 * Foi usado:
 *   IPv6-addr   = (                                    6(IPv6-hex ":")
 *                   |                             "::" 5(IPv6-hex ":")
 *                   |                   IPv6-hex] "::" 4(IPv6-hex ":")
 *                   | [*1(IPv6-hex ":") IPv6-hex] "::" 3(IPv6-hex ":")
 *                   | [*2(IPv6-hex ":") IPv6-hex] "::" 2(IPv6-hex ":")
 *                   | [*3(IPv6-hex ":") IPv6-hex] "::"   IPv6-hex ":"
 *                   | [*4(IPv6-hex ":") IPv6-hex] "::"
 *               ) (IPv6-hex ":" IPv6-hex | IPv4_address_literal)
 *               |     [*5(IPv6-hex ":") IPv6-hex] "::"   IPv6-hex
 *               |     [*6(IPv6-hex ":") IPv6-hex] "::"
 *               ; Regra baseada na [RFC 3986], mais simples e eficiente
 * 
 * Para os endereços do tipo MAIL_ADDR_TYPE_HOSTNAME são considerados os limites
 * definidos na Seção 2.3.4 da [RFC 1035].
 * 
 * @since 0.0.1
 *
 * @param string $address A string contendo o endereço de e-mail para análise.
 * @return array|false Retorna uma array associativa contendo as
 * seguintes chaves:
 *
 *     » local-part: A parte local do endereço (antes do sinal "@"), sem
 *       qualquer tratamento.
 *
 *     » tag-part: A segunda parte do endereço (após o sinal "@"), sem qualquer
 *       tratamento.
 *
 *     » local: A parte local interpretada do endereço.
 *
 *     » tag: A parte relevante da tag do endereço, conforme o tipo do
 *       endereço.
 *
 *     » type: O tipo do endereço, conforme as constantes MAIL_ADDR_TYPE_*.
 *
 *     » ipv4-mapped: Presente apenas se o tipo do endereço for
 *       MAIL_ADDR_TYPE_IPV6, indica se a notação do IPv6 embute a notação do
 *       IPv4.
 */
function mail_parse_address($address) {
    /**
     * Padrão para análise do endereço.
     * O padrão foi simplificado e os caracteres reduzidos para melhorar o
     * desempenho da pesquisa.
     */
    $pattern = '/(?(DEFINE)(?<a>(?:[0-9]|[A-Fa-f]){1,4})(?<b>(?&f)(?:\.(?&f)){3'
        .'})(?<c>(?=.{0,63}(?!(?&d)))(?:[A-Za-z]|[0-9])(?&d)?)(?<d>(?:[A-Za-z]|'
        .'[0-9]|-)*(?:[A-Za-z]|[0-9]))(?<e>(?:[A-Za-z]|[0-9]|[!#\$%&\'*+\-\/=?^'
        .'_`{|}~])+)(?<f>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9]))^(?<_localp'
        .'art>(?:(?&e)(?:\.(?&e))*)|(?:"(?:(?:[\x20-\x21]|[\x23-\x5b]|[\x5d-\x7'
        .'e])|(?:\\\\[\x20-\x7e]))*"))\@(?<_tag>(?<_hostname>(?&c)(?:\.(?&c))*)'
        .'|\[(?:(?<_ipv4>(?&b))|IPv6:(?<_ipv6>(?:(?:(?&a):){6}|::(?:(?&a):){5}|'
        .'(?&a)?::(?:(?&a):){4}|(?:(?:(?&a):){0,1}(?&a))?::(?:(?&a):){3}|(?:(?:'
        .'(?&a):){0,2}(?&a))?::(?:(?&a):){2}|(?:(?:(?&a):){0,3}(?&a))?::(?&a):|'
        .'(?:(?:(?&a):){0,4}(?&a))?::)(?:(?&a):(?&a)|(?<_ipv6v4>(?&b)))|(?:(?:('
        .'?&a):){0,5}(?&a))?::(?&a)|(?:(?:(?&a):){0,6}(?&a))?::)|(?<_generic>(?'
        .'&d):(?:[\x21-\x5a]|[\x5e-\x7f])+))\])$/';    

    $match;
    if(!preg_match($pattern, $address, $match))
        return false;

    $ret = array(
        'local-part'    => $match['_localpart'],
        'tag-part'    => $match['_tag']
    );
    
    $tmp = $ret['local-part'];
    if($tmp[0] == '"')
        $tmp = stripslashes(substr($tmp, 1, -1));
    $ret['local'] = $tmp;

    $tag_keys = array(
        '_hostname' => MAIL_ADDR_TYPE_HOSTNAME,
        '_ipv4'     => MAIL_ADDR_TYPE_IPV4,
        '_ipv6'     => MAIL_ADDR_TYPE_IPV6,
        '_generic'  => MAIL_ADDR_TYPE_GENERIC
    );
    
    foreach($tag_keys as $key => $type) {
        if($match[$key]) {
            $ret['tag'] = $match[$key];
            $ret['type'] = $type;
            
            switch($key) {
            case '_hostname':
                if(strlen($ret['tag']) > 255)
                    return false;
                break;
            case '_ipv6':
                $ret['ipv4-mapped'] = !empty($match['_ipv6v4']);
                break;
            }
            return $ret;
        }
    }
    
    return false;
}
?>
