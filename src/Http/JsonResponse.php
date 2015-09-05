<?php

class Oxygen_Http_JsonResponse extends Oxygen_Http_Response
{
    public function __construct($content, $statusCode = 200, array $headers = array())
    {
        if (!is_array($content)) {
            throw new RuntimeException(sprintf('The %s only accepts arrays as content.', __CLASS__));
        }

        $headers['content-type'] = 'application/json';
        parent::__construct($content, $statusCode, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        if (!is_array($content)) {
            throw new RuntimeException(sprintf('The %s only accepts arrays as content.', __CLASS__));
        }

        return parent::setContent($content);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentAsString()
    {
        return "\n".json_encode($this->content)."\n";
    }
}
