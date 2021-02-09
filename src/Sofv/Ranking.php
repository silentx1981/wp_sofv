<?php

namespace Silentx\Sofv;

use DOMElement;
use Silentx\Lib\SDomDocument;

class Ranking
{
	private string $url;
	private $prototypeRank = [
		'rank'         => '',
		'team'         => '',
		'games'        => '',
		'wins'         => '',
		'ties'         => '',
		'losts'        => '',
		'penalties'    => '',
		'goals'        => '',
		'goalsagainst' => '',
		'goalsdiff'    => '',
		'points'       => '',
		'hometeam'     => false,
	];

	public function __construct(string $url)
	{
		$this->url = $url;
	}

	public static function request()
	{
		$ranking = new Ranking($_REQUEST['url']);
		print_r($ranking->getRanking());

		die();
	}

	public function getRanking($resultMode = 'render')
	{
		$result = $this->getRankingData();

		if ($resultMode === 'render')
			$result = $this->render($result);

		return $result;
	}

	private function getRankingData()
	{
		$sdom = new SDomDocument();
		$dom = $sdom->getByUrl($this->url);
		$elements = $dom->getElementsByTagName('tr');

		$result = [];
		/** @var DOMElement $element */
		foreach ($elements as $element) {
			$class = $element->getAttribute('class');
			$pos = mb_strpos($class, 'ran');
			if ($pos === false)
				continue;

			$result = array_merge($result, $this->parseCurrentRank($element));
		}

		return $result;
	}

	private function parseCurrentRank(DOMElement $element)
	{
		$result = [];
		$elements = $element->getElementsByTagName('td');
		/** @var DOMElement $element */
		foreach ($elements as $element) {
			if ($element instanceof DOMElement)
				$result = $this->parseCurrentRankLine($element, $result);
		}

		return $result;
	}

	private function parseCurrentRankLine(DOMElement $element, array $data)
	{
		$class = $element->getAttribute('class');
		$result = $data;

		if (mb_strpos($class, 'ranCrang') === 0) {
			$result[] = $this->prototypeRank;
			$result[count($result) -1]['rank'] = $element->nodeValue;
		} else if (mb_strpos($class, 'ranCteam') === 0) {
			$result[count($result) - 1]['team'] = $element->nodeValue;
			if ($element->getElementsByTagName('a')->length === 0)
				$result[count($result) -1]['hometeam'] = true;
		} else if (mb_strpos($class, 'ranCstrp') === 0) {
			$result[count($result) - 1]['penalties'] = $element->nodeValue;
		} else if (mb_strpos($class, 'ranCsp') === 0) {
			$result[count($result) - 1]['games'] = $element->nodeValue;
		} else if (mb_strpos($class, 'ranCs') === 0) {
			$result[count($result) - 1]['wins'] = $element->nodeValue;
		} else if (mb_strpos($class, 'ranCu') === 0) {
			$result[count($result) - 1]['ties'] = $element->nodeValue;
		} else if (mb_strpos($class, 'ranCn') === 0) {
			$result[count($result) - 1]['losts'] = $element->nodeValue;
		} else if (mb_strpos($class, 'ranCtg') === 0) {
			$result[count($result) - 1]['goals'] = $element->nodeValue;
		} else if (mb_strpos($class, 'ranCte') === 0) {
			$result[count($result) - 1]['goalsagainst'] = $element->nodeValue;
		} else if (mb_strpos($class, 'ranCtdf') === 0) {
			$result[count($result) - 1]['goalsdiff'] = $element->nodeValue;
		} else if (mb_strpos($class, 'ranCpt') === 0) {
			$result[count($result) - 1]['points'] = $element->nodeValue;
		}

		return $result;
	}

	private function render($ranks)
	{
		$rankContent = '';
		foreach ($ranks as $rank)
			$rankContent .= $this->renderRank($rank);

		$result = '	<table class="table tableRanking">
						<thead class="table-light">
							<tr>
								<th class="text-center">Rang</th>
								<th>Mannschaft</th>
								<th class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Spiele">S</th>
								<th class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Siege">S</th>
								<th class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Unentschieden">U</th>
								<th class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Niederlagen">N</th>
								<th class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Strafpunkte">S</th>
								<th class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Tore">T</th>
								<th class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Punkte">P</th>
							</tr>
						</thead>
						<tbody>
							'.$rankContent.'
						</tbody>
					</table>';

		return $result;
	}

	private function renderRank($rank)
	{
		$result = '	<tr class="'.($rank['hometeam'] === true ? 'table-primary' : '') .'">
						<td class="text-center" data-title="Rang">'.$rank['rank'].'</td>
						<td data-title="Mannschaft">'.$rank['team'].'</td>
						<td class="text-center" data-title="Spiele">'.$rank['games'].'</td>
						<td class="text-center" data-title="Siege">'.$rank['wins'].'</td>
						<td class="text-center" data-title="Unentschieden">'.$rank['ties'].'</td>
						<td class="text-center" data-title="Niederlagen">'.$rank['losts'].'</td>
						<td class="text-center" data-title="Strafpunkte">'.$rank['penalties'].'</td>
						<td class="text-center" data-title="Tore">'.$rank['goals'].' : '.$rank['goalsagainst'].'</td>
						<td class="text-center" data-title="Punkte">'.$rank['points'].'</td>
					</tr>';

		return $result;
	}

}