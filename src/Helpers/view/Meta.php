<?php

/**
 * Nip Framework
 *
 * @category   Nip
 * @copyright  2009 Nip Framework
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version    SVN: $Id: Meta.php 60 2009-04-28 14:50:04Z victor.stanciu $
 */

class Nip_Helper_View_Meta extends Nip_Helper_View_Abstract {

    public $charset     = 'utf-8';
    public $language    = 'en';

    public $authors     = array();
    public $publisher;
    public $copyright;

    public $robots      = 'index,follow';

    public $title       = false;
    public $titleComponents = array(
        'base' => false,
        'elements' => array(),
        'separator' => ' - ',

    );

    public $keywords    = array();
    public $description = false;
    public $descriptionComponents = array();
    public $verify_v1   = false;
    public $feeds   = array();


    public function setTitleBase($base)
    {
        $this->titleComponents['base'] = $base;
        $this->generateTitle();
        return $this;
    }

    public function appendTitle($title)
    {
        $this->titleComponents['elements'][] = $title;
        $this->generateTitle();
        return $this;
    }

    public function prependTitle($title)
    {
        array_unshift($this->titleComponents['elements'], $title);
        $this->generateTitle();
        return $this;
    }

    public function generateTitle()
    {
        $components = $this->titleComponents['elements'];
        if ($this->titleComponents['base']) {
            $components[] = $this->titleComponents['base'];
        }
        $this->title = implode($this->titleComponents['separator'], $components);
    }

    public function getFirstTitle()
    {
        $components = $this->titleComponents['elements'];
        return end($components);
    }


    public function addKeywords($keywords) {
        if (!is_array($keywords)) {
            $keywords = array($keywords);
        }
        foreach ($keywords as $keyword) {
            if ($keyword) {
                array_unshift($this->keywords, $keyword);
            }
        }

        return $this;
    }

    public function addDescription($description) {
        array_unshift($this->descriptionComponents, $description);
        $this->generateDescription();
    }

    public function setDescription($description) {
        if ($description) {
            $this->description = $description;
        }

        return $this;
    }

    public function generateDescription() {
        $this->description = implode('. ', $this->descriptionComponents);
    }

    public function addFeed($url, $title = 'Rss') {
        if (is_object($url)) {
            $feed = $url;
        } else {
            $feed = new stdClass();
            $feed->title = $title;
            $feed->url = $url;
        }

        $this->feeds[$feed->url] = $feed;

        return $this;
    }

    public function addFeeds(array $feeds) {
        foreach ($feeds as $feed) {
            $this->addFeed($feed);
        }

        return $this;
    }

    public function __toString() {
    	if ($this->title) {
            $return[] = '<title>'. $this->title .'</title>';
    	}

    	$return[] = '<meta http-equiv="Content-Type" content="text/html;" />';
    	$return[] = '<meta charset="' . $this->charset . '">';
    	$return[] = '<meta http-equiv="content-language" content="' . $this->language . '" />';

    	$return[] = '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>';
        
    	if ($this->authors) {
            $return[] = '<meta name="author" content="' . implode(", ", $this->authors) . '" />';
    	}
    	if ($this->publisher) {
            $return[] = '<meta name="publisher" content="' . $this->publisher . '" />';
    	}
    	if ($this->copyright) {
            $return[] = '<meta name="copyright" content="' . $this->copyright . '" />';
    	}

    	$return[] = '<meta name="robots" content="' . $this->robots . '" />';

    	if ($this->keywords) {
            $return[] = '<meta name="keywords" content="' . implode(",", $this->keywords) . '" />';
    	}

    	if (!empty($this->description)) {
    	   $return[] = '<meta name="description" content="' . $this->description . '" />';
    	}

        if (!empty($this->verify_v1)) {
            $return[] = '<meta name="verify-v1" content="'. $this->verify_v1 .'" />';
        }

        foreach ($this->feeds as $feed) {
            $return[] = '<link rel="alternate" type="application/rss+xml" title="'.$feed->title.'" href="'.$feed->url.'" />';
        }

//        $return[] = '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />';

    	return implode("\n", $return);
    }

	/**
	 * Singleton
	 *
	 * @return Nip_Helper_View_Meta
	 */
	static public function instance() {
		static $instance;
		if (!($instance instanceof self)) {
			$instance = new self();
		}
		return $instance;
	}
}