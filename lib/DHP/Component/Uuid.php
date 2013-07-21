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
    private $serverId = NULL;
    /**
     * The actual uuId value
     * @var null
     */
    private $uuId = NULL;

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
     * @param string $ipAddress
     * @return string
     */
    private function clientIPToHex($ipAddress = '')
    {
        $hex = "";
        if ($ipAddress == "") {
            $ipAddress = getEnv("REMOTE_ADDR");
        }
        $part = explode('.', $ipAddress);
        # fix for when we have ipv6... and like to have ipv4 IP's....
        if (count($part) < 4) {
            if (!isset($_SERVER['REMOTE_ADDR'])) {
                $ipAddress = gethostbyname(gethostbyaddr('127.0.0.1'));
            } else {
                $ipAddress = gethostbyname(gethostbyaddr($_SERVER['REMOTE_ADDR']));
            }
            $part = explode('.', $ipAddress);
        }
        foreach ($part as $ipPart) {
            $hex .= substr("0" . dechex($ipPart), -2);
        }
        return $hex;
    }

    public function __toString()
    {
        return sprintf('%s', $this->uuId);
    }

    public function __invoke()
    {
        return $this->__toString();
    }

    /**
     * Sets the unique id
     * @param $uuId
     */
    public function setId($uuId)
    {
        $this->uuId = $uuId;
    }

    /**
     * Decodes the uuid into its separate values
     *
     * @return array
     */
    public function decode()
    {
        $uuid = $this->uuId;
        $rez  = Array();
        $uPart    = explode("-", $uuid);
        if (is_array($uPart) == TRUE && count($uPart) == 5) {
            $rez = Array(
                'serverID' => $uPart[0],
                'ip'       => $this->clientIPFromHex($uPart[1]),
                'unixtime' => hexdec($uPart[2]),
                'micro'    => (hexdec($uPart[3]) / 65536)
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
        $ipAddress = "";
        if (strlen($hex) == 8) {
            $ipAddress .= hexdec(substr($hex, 0, 2)) . ".";
            $ipAddress .= hexdec(substr($hex, 2, 2)) . ".";
            $ipAddress .= hexdec(substr($hex, 4, 2)) . ".";
            $ipAddress .= hexdec(substr($hex, 6, 2));
        }
        return $ipAddress;
    }
}