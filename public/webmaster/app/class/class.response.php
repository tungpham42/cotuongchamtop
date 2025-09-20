<?php
class Response
{
	private static $methods = array("JSON", "XML", "ARRAY");
	const METHOD_PREFIX = "response";

	private static function getAvailableResponse($response)
	{
		return in_array(strtoupper($response), self::$methods) ? $response : "JSON";
	}

	public static function run(array $response, $method = "JSON")
	{
        $method = self::METHOD_PREFIX.self::getAvailableResponse($method);
		return self::$method($response);
	}

	private static function responseJSON($response)
	{
		header('Content-Type: application/json');
		return json_encode($response);
	}

	private static function responseXML($response)
	{
		header('Content-type: text/xml');
		return self::array2xml($response);
	}

	private static function responseARRAY($response)
	{
		header('Content-type: plain/text');
		return serialize($response);
	}

	public static function array2xml($array, $wrap='response')
	{
        $xml = "<$wrap>\n";
		foreach ($array as $key=>$value)
		{
			if(is_array($value))
				$xml .= self::array2xml($value, $key);
			else
				$xml .= "<$key>".htmlspecialchars(trim($value))."</$key>";
		}
		$xml .= "\n</$wrap>\n";
		return $xml;
	}
}