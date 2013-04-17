<?php
declare(encoding = "UTF8");
namespace DHP\component;

use DHP\blueprint\Component;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-04-02 22:39
 */
class Uuid extends Component
{

    /**
     * ID of the server that generated this unique id
     * @var int
     */
    private $serverId = null;
    /**
     * The actual uuId value
     * @var null
     */
    private $uuId = null;

    /**
     * Sets up the Uuid-component.
     *
     * @param Int $serverId the id of the server that generated the uuid value
     */
    public function __construct($serverId = 1)
    {
        $this->serverId = $serverId;
        $this->generate();
    }

    /**
     * Generates a new uuid
     */
    private function generate()
    {
        $time       = explode(" ", microtime());
        $this->uuId = sprintf(
            '%04x-%08s-%08s-%04s-%04x%04x',
            $this->serverId,
            $this->clientIPToHex(),
            // get 8HEX of unixtime
            substr("00000000" . dechex($time[1]), -8),
            // get 4HEX of microtime
            substr("0000" . dechex(round($time[0] * 65536)), -4),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Converts an ip-adress to hexadecimal value
     *
     * @param string $ip
     * @return string
     */
    private function clientIPToHex($ip = '')
    {
        $hex = "";
        if ($ip == "") {
            $ip = getEnv("REMOTE_ADDR");
        }
        $part = explode('.', $ip);
        # fix for when we have ipv6... and like to have ipv4 IP's....
        if (count($part) < 4) {
            if (!isset($_SERVER['REMOTE_ADDR'])) {
                $ip = gethostbyname(gethostbyaddr('127.0.0.1'));
            } else {
                $ip = gethostbyname(gethostbyaddr($_SERVER['REMOTE_ADDR']));
            }

            $part = explode('.', $ip);
        }
        $part = explode('[^0-9]+', $ip);
        for ($i = 0; $i <= count($part) - 1; $i++) {
            $hex .= substr("0" . dechex($part[$i]), -2);
        }
        return $hex;
    }

    public function __toString()
    {
        return sprintf('%s', $this->uuId);
    }

    public function __invoke()
    {
        return __toString();
    }

    /**
     * Sets the unique id
     * @param $id
     */
    public function setId($id)
    {
        $this->uuId = $id;
    }

    /**
     * Decodes the uuid into its separate values
     *
     * @return array
     */
    private function decode()
    {
        $uuid = $this->uuId;
        $rez  = Array();
        $u    = explode("-", $uuid);
        if (is_array($u) == YES && count($u) == 5) {
            $rez = Array(
                'serverID' => $u[0],
                'ip'       => $this->clientIPFromHex($u[1]),
                'unixtime' => hexdec($u[2]),
                'micro'    => (hexdec($u[3]) / 65536)
            );
        }
        return $rez;
    }

    /**
     * Converts a hex representation of an IP back into its original value
     *
     * @param $hex
     * @return string
     */
    private function clientIPFromHex($hex)
    {
        $ip = "";
        if (strlen($hex) == 8) {
            $ip .= hexdec(substr($hex, 0, 2)) . ".";
            $ip .= hexdec(substr($hex, 2, 2)) . ".";
            $ip .= hexdec(substr($hex, 4, 2)) . ".";
            $ip .= hexdec(substr($hex, 6, 2));
        }
        return $ip;
    }
}