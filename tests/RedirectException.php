<?php
namespace Tests;

class RedirectException extends \Exception
{
    protected $url;

    public function __construct($url)
    {
        parent::__construct("Redirect to: " . $url);
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
