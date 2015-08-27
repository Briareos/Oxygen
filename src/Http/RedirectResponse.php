<?php

class Oxygen_Http_RedirectResponse extends Oxygen_Http_Response
{
    public function __construct($url, $statusCode = 302, array $headers = array())
    {
        $headers['location'] = $url;
        parent::__construct($url, $statusCode, $headers);
    }

    public function getContentAsString()
    {
        return sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8'));
    }
}
