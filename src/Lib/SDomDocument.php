<?php

namespace Silentx\Lib;

use DOMDocument;
use tidy;

class SDomDocument
{
	public function getByUrl(string $url)
	{
		$dom = new DOMDocument();
		$tidy = new tidy();

		$content = file_get_contents($url);
		$content = $tidy->repairString($content, [
			'clean' => true,
			'output-xhtml' => true,
			'show-body-only' => true,
			'wrap' => 0,
		], 'utf8');

		libxml_use_internal_errors(true);
		$dom->loadHTML('<?xml encoding="utf-8" ?>'.$content);
		libxml_use_internal_errors(false);

		return $dom;
	}
}