<?php
/**
 * Este arquivo é parte do código fonte Uloc
 *
 * (c) Tiago Felipe <tiago@tiagofelipe.com>
 *
 * Para informações completas dos direitos autorais, por favor veja o arquivo LICENSE
 * distribuído junto com o código fonte.
 */

namespace Fnbassets\WebsiteApi\Utils;


trait Utils
{

    /**
     * Em alguns casos, atrributos de objetos em respostas da API precisam ser modificadas de forma global.
     * Este método estático resolve este problema. Por exemplo, em muitas respostas da API precisamos enviar uma data,
     * que normalmente será uma intância \DateTime. Supomos que precisamos enviar na resposta da API o timestamp desta
     * data, porém seja necessário adicionar três zeros ao método getTimestamp. Basta adicionar uma escuta à este método
     * e uma função anônima com a modificação necessára.
     * @param $valor
     * @param $campo
     * @return mixed
     */
    static function transformObjResponse($valor, $campo)
    {
        $listener = array(
            "getTimestamp" => function ($v) {
                return intval($v . '000'); //timestamp com milisegundos
            }
        );
        if (isset($listener[$campo])) {
            $fcn = $listener[$campo];
            return $fcn($valor);
        } else {
            return $valor;
        }
    }

    /**
     * Get a users first name from the full name
     * or return the full name if first name cannot be found
     * e.g.
     * James Smith        -> James
     * James C. Smith   -> James
     * Mr James Smith   -> James
     * Mr Smith        -> Mr Smith
     * Mr J Smith        -> Mr J Smith
     * Mr J. Smith        -> Mr J. Smith
     *
     * @param string $fullName
     * @param bool $checkFirstNameLength Should we make sure it doesn't just return "J" as a name? Defaults to TRUE.
     *
     * @return string
     */
    public static function fullNameToFirstName($fullName, $checkFirstNameLength = true)
    {
        // Split out name so we can quickly grab the first name part
        $nameParts = explode(' ', $fullName);
        $firstName = str_replace('.', '', $nameParts[0]);
        // If the first part of the name is a prefix, then find the name differently
        if (in_array(strtolower($firstName), array('mr', 'ms', 'mrs', 'miss', 'dr', 'sr', 'sra'))) {
            if ($nameParts[2] != '') {
                // E.g. Mr James Smith -> James
                $firstName = $nameParts[1];
            } else {
                // e.g. Mr Smith (no first name given)
                $firstName = $fullName;
            }
        }
        // make sure the first name is not just "J", e.g. "J Smith" or "Mr J Smith" or even "Mr J. Smith"
        if ($checkFirstNameLength && strlen($firstName) < 3) {
            $firstName = $fullName;
        }
        return $firstName;
    }

    // Function to get the client ip address
    public static function get_client_ip_env()
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // se usar Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED', // padrão RFC 7239, raro
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0]; // pega o primeiro IP real
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'UNKNOWN';
    }

    // Function to detect mobile
    public static function detectPlatform()
    {

        $tablet_browser = 0;
        $mobile_browser = 0;

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower(@$_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower(@$_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
        }

        if ((strpos(strtolower(@$_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
        }

        $mobile_ua = strtolower(substr(@$_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-');

        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }

        if (strpos(strtolower(@$_SERVER['HTTP_USER_AGENT']), 'opera mini') > 0) {
            $mobile_browser++;
            //Check for tablets on opera mini alternative headers
            $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : (isset($_SERVER['HTTP_DEVICE_STOCK_UA']) ? $_SERVER['HTTP_DEVICE_STOCK_UA'] : ''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
                $tablet_browser++;
            }
        }

        if ($tablet_browser > 0) {
            // do something for tablet devices
            return 'tablet';
        } else if ($mobile_browser > 0) {
            // do something for mobile devices
            return 'mobile';
        } else {
            // do something for everything else
            return 'desktop';
        }

    }

    public static function getBrowser()
    {
        $u_agent = @$_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } else {
            $ub = 'N/d';
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    public static function camelCase($string)
    {

    }

    public static function hideCenterContentTest($string)
    {
        return preg_replace("/(?!^).(?!$)/", "*", $string);
    }

    public static function xml2array($string)
    {
        $sxi = new \SimpleXmlIterator($string, null);
        return [$sxi->getName() => static::sxiToArray($sxi)];
    }

    public static function sxiToArray(\SimpleXMLIterator $sxi)
    {
        $a = array();
        for ($sxi->rewind(); $sxi->valid(); $sxi->next()) {
//            if(!array_key_exists($sxi->key(), $a)){
//                $a[$sxi->key()] = array();
//            }
            if ($sxi->hasChildren()) {
                $a[$sxi->key()][] = static::sxiToArray($sxi->current());
            } else {
                if (isset($a[$sxi->key()])) {
                    if (!is_array($a[$sxi->key()])) {
                        $a[$sxi->key()] = [$a[$sxi->key()]];
                    }
                    $a[$sxi->key()][] = strval($sxi->current());
                } else {
                    $a[$sxi->key()] = strval($sxi->current());
                }
            }
        }
        return $a;
    }

    public static function diaPtBr($d)
    {
        $dias = [
            'Domingo',
            'Segunda-feira',
            'Terça-feira',
            'Quarta-feira',
            'Quinta-feira',
            'Sexta-feira',
            'Sábado'
        ];
        return $dias[$d];
    }

    public static function mesPtBr($d)
    {
        $mes = [
            'Janeiro',
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro'
        ];
        return $mes[$d];
    }

    // source: https://stackoverflow.com/questions/7549669/php-validate-latitude-longitude-strings-in-decimal-format
    public static function validaCoordenadas(string $latitude, string $longitude)
    {
        $isLatitudeValid = preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $latitude);
        $isLongitudeValid = preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $longitude);

        return $isLatitudeValid && $isLongitudeValid;
    }
}