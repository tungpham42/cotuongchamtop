<?php
function checkPhpExtensionVersion($extensionName, $version, $compare = '>=')
{
    if (!extension_loaded($extensionName)) {
        return false;
    }
    $extensionVersion = phpversion($extensionName);
    if (empty($extensionVersion)) {
        return false;
    }
    if (strncasecmp($extensionVersion, 'PECL-', 5) === 0) {
        $extensionVersion = substr($extensionVersion, 5);
    }

    return version_compare($extensionVersion, $version, $compare);
}

function checkPhpIniOn($name)
{
    $value = ini_get($name);
    if (empty($value)) {
        return false;
    }

    return ((int) $value === 1 || strtolower($value) === 'on');
}

function checkPhpIniOff($name)
{
    $value = ini_get($name);
    if (empty($value)) {
        return true;
    }

    return (strtolower($value) === 'off');
}

function getServerInfo()
{
    $info[]= isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
    $info[]=@strftime('%Y-%m-%d %H:%M',time());
    return implode(' ',$info);
}

function checkPhpFunctions(array $functions)
{
    $not_available = [];
    foreach ($functions as $function) {
        if(!function_exists($function)) {
            $not_available[] = sprintf("<strong>%s</strong> is not available", $function);
        }
    }
    return !empty($not_available) ? join(", ", $not_available) : "";
}

function checkGd()
{
    if(!extension_loaded('gd')) {
        return 'GD extension is not loaded';
    }
    $gd_info = gd_info();
    if(!$gd_info['FreeType Support']) {
        return 'GD extension has no GD support';
    }
    return '';
}

function checkIcu()
{
    if(!extension_loaded('intl')) {
        return 'Intl extension is not loaded';
    }
    if(!defined('INTL_ICU_VERSION'))  {
        return 'ICU library is not loaded';
    }

    if(version_compare(PHP_VERSION,'7.4.0', '<')) {
        if(version_compare(INTL_ICU_VERSION, '4.0.0', '<=')) {
            return 'ICU library version must be greater than 4.0.0';
        }
    } else {
        if(version_compare(INTL_ICU_VERSION, '50.1', '<=')) {
            return 'ICU library version must be greater than 50.1';
        }
    }
    return '';
}

$serverInfo = getServerInfo();


$requirements = array(
    array(
        "rule"=>"PHP version (5.6.0 or higher)",
        "result"=>version_compare(PHP_VERSION, "5.6.0",">="),
        "explanation"=>"PHP 5.6.0 or higher is required.",
        "mandatory"=>true,
    ),
    array (
        "rule"=>'PCRE extension',
        "result"=>extension_loaded('pcre'),
        "explanation"=>"PCRE extension is not loaded",
        "mandatory"=>true,
    ),
    array (
        "rule"=>'SPL extension',
        "result"=>extension_loaded('SPL'),
        "explanation"=>"SPL extension is not loaded",
        "mandatory"=>true,
    ),
    array (
        "rule"=>'Gd extension',
        "result"=>'' === $gd = checkGd(),
        "explanation"=>$gd,
        "mandatory"=>true,
    ),
    array(
        "rule"=>'Intl extension && ICU library',
        "result"=>'' === $messageIcu = checkIcu(),
        "explanation"=>$messageIcu,
        "mandatory"=>true,
    ),
    array(
        "rule"=>'Mysqli extension',
        "result"=>extension_loaded('mysqli'),
        "explanation"=>'Mysqli extension is not loaded',
        "mandatory"=>true,
    ),
    array(
        "rule"=>'MySQL Native Driver',
        "result"=>function_exists('mysqli_fetch_all'),
        "explanation"=>'MySQL Native Driver (<a href="https://www.php.net/manual/en/book.mysqlnd.php">mysqlnd</a>) is not loaded.',
        "mandatory"=>true,
    ),
    array(
        "rule"=>'MBString extension',
        "result"=>extension_loaded("mbstring"),
        "explanation"=>'MBString extension is not loaded. Required for multibyte encoding string processing.',
        "mandatory"=>true,
    ),
    array(
        "rule"=>'OpenSSL extension',
        "result"=>extension_loaded('openssl'),
        "explanation"=>'OpenSSL extension is not loaded. Required by encrypt and decrypt methods.',
        "mandatory"=>true,
    ),
    array(
        "rule"=>'cURL extension',
        "result"=>function_exists('curl_version'),
        "explanation"=>'cURL is not enabled',
        "mandatory"=>true,
    ),
    array(
        "rule"=>"fsockopen function",
        "result"=>function_exists('fsockopen'),
        "explanation"=>"fsockopen must be available in order to pull data via WHOIS protocol",
        "mandatory"=>true,
    ),
    array(
        "rule"=>'short_open_tag (php.ini directive)',
        "result"=>checkPhpIniOn('short_open_tag'),
        "explanation"=>'You need to enable <strong>short_open_tag</strong> in <strong>php.ini</strong>.',
        "mandatory"=>true,
    ),
);

$result=1;  // 1: all pass, 0: fail
foreach($requirements as $i=>$requirement) {
    if(!$requirement['result'] && $requirement['mandatory']) {
        $result = 0;
    }
    $requirements[$i]['cell_class'] = $requirement['result'] ? "passed" : ($requirement['mandatory'] ? "failed" : "warning");

    if($requirement['explanation'] === '') {
        $requirements[$i]['explanation']='&nbsp;';
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="content-language" content="en"/>
    <title>Webmaster Tools | Requirements</title>
    <style type="text/css">
        body
        {
            background: white;
            font-family:'Lucida Grande',Verdana,Geneva,Lucida,Helvetica,Arial,sans-serif;
            font-size:10pt;
            font-weight:normal;
        }

        #page
        {
            width: 800px;
            margin: 0 auto;
        }

        #header
        {
        }

        #content
        {
        }

        #footer
        {
            color: gray;
            font-size:8pt;
            border-top:1px solid #aaa;
            margin-top:10px;
        }

        h1
        {
            color:black;
            font-size:1.6em;
            font-weight:bold;
            margin:0.5em 0;
        }

        h2
        {
            color:black;
            font-size:1.25em;
            font-weight:bold;
            margin:0.3em 0;
        }

        h3
        {
            color:black;
            font-size:1.1em;
            font-weight:bold;
            margin:0.2em 0;
        }

        table.result
        {
            background:#E6ECFF none repeat scroll 0 0;
            border-collapse:collapse;
            width:100%;
        }

        table.result th
        {
            background:#CCD9FF none repeat scroll 0 0;
            text-align:left;
        }

        table.result th, table.result td
        {
            border:1px solid #BFCFFF;
            padding:0.2em;
        }

        td.passed
        {
            background-color: #60BF60;
            border: 1px solid silver;
            padding: 2px;
        }

        td.warning
        {
            background-color: #efeb5b;
            border: 1px solid silver;
            padding: 2px;
        }

        td.failed
        {
            background-color: #FF8080;
            border: 1px solid silver;
            padding: 2px;
        }
    </style>
</head>
<body>
<div id="page">

    <div id="header">
        <h1>Webmaster Tools</h1>
    </div><!-- header-->

    <div id="content">
        <h2>Description</h2>
        <p>
            This script checks if your server configuration meets the requirements
            for running <a href="http://webmaster-tools.php8developer.com" target="_blank">Webmaster Tools</a>.
            It checks if the server is running the right version of PHP,
            if appropriate PHP extensions have been loaded, and if php.ini file settings are correct.
        </p>
        <h2>Conclusion</h2>
        <p>
            <?php if($result>0): ?>
                Congratulations! Your server configuration satisfies minimum requirements by Webmaster Tools.
            <?php else: ?>
                Unfortunately your server configuration does not satisfy the requirements by Webmaster Tools.
            <?php endif; ?>
        </p>

        <h2>Details</h2>

        <table class="result">
            <tr><th>Name</th><th>Result</th><th>Memo</th></tr>
            <?php foreach($requirements as $requirement): ?>
                <tr>
                    <td>
                        <?php echo $requirement['rule']; ?>
                    </td>
                    <td class="<?php echo $requirement['cell_class']; ?>">
                        <?php echo ucfirst($requirement['cell_class']) ?>
                    </td>
                    <td>
                        <?php if(!$requirement['result']): ?>
                            <?php echo $requirement['explanation']; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <table>
            <tr>
                <td class="passed">&nbsp;</td><td>passed</td>
                <td class="failed">&nbsp;</td><td>failed</td>
                <td class="warning">&nbsp;</td><td>warning</td>
            </tr>
        </table>

    </div><!-- content -->

    <div id="footer">
        <?php echo $serverInfo; ?>
    </div><!-- footer -->

</div><!-- page -->
</body>
</html>