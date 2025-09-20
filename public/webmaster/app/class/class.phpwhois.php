<?php
class PHPWhois {
    protected $domain,
        $tld,
        $parent_domain,
        $server_list = array(),
        $server_params = array(),
        $server_charset = array();

    public function __construct($domain, $server_list_path, $server_charset_path, $server_params_path) {
        $domain = trim($domain); //remove space from start and end of domain
        if(substr(strtolower($domain), 0, 7) == "http://")
            $domain = substr($domain, 7); // remove http:// if included
        if(substr(strtolower($domain), 0, 4) == "www.")
            $domain = substr($domain, 4); //remove www from domain
        $this -> domain = $domain;
        $this -> server_list = include_once $server_list_path;
        $this -> server_charset = include_once $server_charset_path;
        $this -> server_params = include_once $server_params_path;
        $this -> tld = $this -> getTld();
        $this -> parent_domain = $this->getParentDomain($domain);
    }

    protected function getTld() {
        $domain_parts = explode(".", $this->domain);
        // domain level > 2 ?
        if(count($domain_parts) > 2) {
            $tld = implode(".", array_slice($domain_parts, -2));
            return isset($this->server_list[$tld]) ? $tld : array_pop($domain_parts);
        } else {
            return array_pop($domain_parts);
        }
    }

    protected function getParentDomain($domain) {
        $domain = preg_replace("#\.{$this->tld}$#i", "", $domain);
        $parts = explode(".", $domain);
        $parent = end($parts);
        return $parent.".".$this->tld;
    }

    protected function getServerParams() {
        if(isset($this->server_params[$this->tld])) {
            $params = strtr($this->server_params[$this->tld], array(
                '{Domain}'=>$this -> parent_domain,
            ));
        } else {
            $params = $this -> parent_domain;
        }
        return $params;
    }

    protected function getWhoisServerOnline() {
        $server = "whois.iana.org";
        return $this->socketQuery($server);
    }

    protected function getWhoisServer() {
        return isset($this->server_list[$this->tld]) ? $this->server_list[$this->tld] : false;
    }

    public function query() {
        $output = "No whois server is known for this kind of object.\n";
        if(!$server = $this->getWhoisServer()) {
            if(preg_match("#refer:(.*)#i", $this->getWhoisServerOnline(), $matches)) {
                $server = trim($matches[1]);
                $whois = $this->socketQuery($server);
                return !empty($whois) ? $whois : $output;
            }
            return $output;
        }
        if(count($server) > 1) {
            $type = $server[1]; // ex: ARPA, NONE, WEB
            switch($type) {
                case "NONE":
                    $output = "This TLD has no whois server.\n";
                    break;
                case "WEB":
                    $output = "This TLD has no whois server, but you can access the whois database at \n". $server[0]. "\n";
                    break;
                case "ARPA":
                default;
                    break;
            }
            return $output;
        } else {
            $res = $this->socketQuery($server[0]);
            /*while(preg_match_all("/Whois Server: (.*)/", $res, $matches)) {
                $server = array_pop($matches[1]);
                $res = $this->socketQuery($server, $this->domain);
            }*/
            return isset($this->server_charset[$server[0]]) ? @iconv($this->server_charset[$server[0]], "utf-8//IGNORE", $res) : $res;
        }
    }

    protected function socketQuery($server) {
        // Checking whois server for .com and .net
        // Getting whois information
        $fp = @fsockopen($server, 43, $errNo, $errStr, 10);
        if (!$fp) {
            return "Connection error!";
        }

        fputs($fp, $this->getServerParams()."\r\n");

        // Getting string
        $string = '';
        if ($this->tld == 'com' || $this->tld == 'net') {
            while (!feof($fp)) {
                $line = trim(fgets($fp, 128));

                $string .= $line;

                $lineArr = explode (":", $line);

                if (strtolower($lineArr[0]) == 'whois server') {
                    $server = trim($lineArr[1]);
                }
            }
            // Getting whois information
            $fp2 = @fsockopen($server, 43, $errNo, $errStr, 10);
            if (!$fp2) {
                return "Connection error!";
            }

            fputs($fp2, "{$this->parent_domain}\r\n");

            // Getting string
            $string = '';

            while (!feof($fp2)) {
                $string .= fgets($fp2, 128);
            }
            fclose($fp2);
            // Checking for other tld's
        } else {
            while (!feof($fp)) {
                $string .= fgets($fp, 128);
            }
        }
        fclose($fp);
        return $string;
    }
}