<?php

namespace Silentx\Sofv;
use DateTime;
use DateTimeZone;
use DOMElement;
use Silentx\Lib\SDomDocument;
use Silentx\Wp\Date;

class Games
{
	private string $url;
	private string $type;
	private array $cacheTeam = [];
	private DateTimeZone $timezone;
	private $prototypeGame = [
		'date'       => '',
		'teamA'      => '',
		'teamB'      => '',
		'resultA'    => '',
		'resultB'    => '',
		'status'     => '',
		'type'       => '',
		'gamenumber' => '',
		'location'   => '',
		'hometeam'   => '',
	];

	public function __construct(string $url, string $type)
	{
		$this->url = $url;
		$this->type = $type;
		$this->timezone = new DateTimeZone(wp_timezone_string());
	}

	public static function request()
	{
		$url = $_REQUEST['url'];
		$type = $_REQUEST['type'] ?? 'current';
		$groupby = $_REQUEST['groupby'] ?? 'day';
		$resultmode = $_REQUEST['resultmode'] ?? 'renderGrid';

		$games = new Games($url, $type);
		print_r($games->getGames($groupby, $resultmode));

		die();
	}

	public function getGames(string $groupBy = 'day', string $resultMode = 'renderGrid')
	{
		$result = [];

		if ($this->type === 'current')
			$result = $this->getCurrentGames();
		else if ($this->type === 'team')
			$result = $this->getTeamGames();
		else if ($this->type === 'all')
			$result = $this->getAllGames();

		foreach ($result as &$game) {
			$game['type'] = preg_replace('/[-\/]{1}.*?$/', '', $game['type']);
		}

		if ($groupBy === 'day')
			$result = $this->groupByDay($result);

		if ($resultMode === 'renderGrid')
			$result = $this->render($result, 'grid');
		else if ($resultMode === 'renderCarousel')
			$result = $this->render($result, 'carousel');

		return $result;
	}

	private function getAllGames()
	{
		$sdom = new SDomDocument();
		$dom = $sdom->getByUrl($this->url);
		$elements = $dom->getElementsByTagName('div');

		$result = [];
		/** @var DOMElement $element */
		foreach ($elements as $element) {
			$class = $element->getAttribute('class');
			$pos = mb_strpos($class, 'nisListeRD list-group');
			if ($pos === false)
				continue;

			$result = array_merge($result, $this->parseAllGame($element));
		}

		return $result;
	}

	private function getCurrentGames()
	{
		$sdom = new SDomDocument();
		$dom = $sdom->getByUrl($this->url);
		$elements = $dom->getElementsByTagName('div');

		$result = [];
		/** @var DOMElement $element */
		foreach ($elements as $element) {
			$class = $element->getAttribute('class');
			$pos = mb_strpos($class, 'nisListeRD list-group');
			if ($pos === false)
				continue;

			$result = array_merge($result, $this->parseCurrentGame($element));
		}

		return $result;
	}

	private function parseAllGame(DOMElement $element)
	{
		$result = [];
		$elements = $element->getElementsByTagName('div');
		/** @var DOMElement $element */
		foreach ($elements as $element) {
			if ($element instanceof DOMElement)
				$result = $this->parseAllGameLines($element, $result);
		}
		$result = array_filter($result, function($value) {
			$teamA = $value['teamA'] ?? null;
			return !empty($teamA);
		});

		return $result;
	}

	private function parseAllGameLines(DOMElement $element, array $data)
	{
		$class = $element->getAttribute('class');
		$result = $data;

		if (mb_strpos($class, 'list-group-item sppTitel') !== false) {
			$dateValue = explode('.', mb_substr($element->nodeValue, 3));
			$dateValue = new DateTime($dateValue[2] . "-" . $dateValue[1] . "-" . $dateValue[0], $this->timezone);
			$dateValue->setTime(0, 0, 0);
			$result[] = $this->prototypeGame;
			$result[count($result) - 1]['date'] = $dateValue;
		} else if (mb_strpos($class, 'list-group-item') !== false) {
			$lastDate = clone $result[count($result) -1]['date'];
			$result[] = $this->prototypeGame;
			$result[count($result) - 1]['date'] = $lastDate;
		} else if (mb_strpos($class, 'col-md-1 time col-xs-12') !== false) {
			/** @var DateTime $date */
			$date = $result[count($result) -1]['date'];
			$timeEx = explode(":", $element->nodeValue);
			$date->setTime($timeEx[0], $timeEx[1]);
		} else if (mb_strpos($class, 'col-md-5 col-xs-12 teamA') !== false) {
			$result[count($result) -1]['teamA'] = $element->nodeValue;
			if (mb_strpos($class, 'tabMyTeam') !== false)
				$result[count($result) -1]['hometeam'] = 'teamA';
		} else if (mb_strpos($class, 'col-md-5 col-xs-12 teamB') !== false) {
			$result[count($result) -1]['teamB'] = $element->nodeValue;
			if (mb_strpos($class, 'tabMyTeam') !== false)
				$result[count($result) -1]['hometeam'] = 'teamB';
		} else if (mb_strpos($class, 'sppStatusText') !== false) {
			$result[count($result) - 1]['status'] = $element->nodeValue;
		} else if (mb_strpos($class, 'col-xs-12 col-md-11 col-md-offset-1 font-small') !== false) {
			$elements = $element->getElementsByTagName('span');
			foreach ($elements as $elementSub)
				$result = $this->parseAllGameLines($elementSub, $result);
			if ($result[count($result) -1]['status'] ?? null)
				$result[count($result) -1]['type'] = trim(preg_replace('/'.addcslashes($result[count($result) -1]['status'], '()./\'"').'.*/s', '', $element->nodeValue));
			else
				$result[count($result) -1]['type'] = preg_replace('/Spielnummer.*/s', '', $element->nodeValue);
			$gamenumber = trim(preg_replace('/.*Spielnummer/s', '', $element->nodeValue));
			$gamenumber = preg_replace('/(&nbsp;){1,}/', '', $gamenumber);
			if (preg_match_all('/[0-9]*/s', $gamenumber, $gamenumberMatch))
				$result[count($result) -1]['gamenumber'] = $gamenumberMatch[0][2] ?? null;
			$result[count($result) -1]['location'] = trim(preg_replace('/.*'.$result[count($result) -1]['gamenumber'].'/s', '', $element->nodeValue));
		}

		return $result;
	}

	private function parseCurrentGame(DOMElement $element)
	{
		$result = [];
		$elements = $element->getElementsByTagName('div');
		/** @var DOMElement $element */
		foreach ($elements as $element) {
			if ($element instanceof DOMElement)
				$result = $this->parseCurrentGameLines($element, $result);
		}
		$result = array_filter($result, function($value) {
			$teamA = $value['teamA'] ?? null;
			return !empty($teamA);
		});

		return $result;
	}

	private function parseCurrentGameLines(DOMElement $element, array $data)
	{
		$class = $element->getAttribute('class');
		$result = $data;

		if (mb_strpos($class, 'list-group-item sppTitel') !== false) {
			$dateValue = explode('.', mb_substr($element->nodeValue, 3));
			$dateValue = new DateTime($dateValue[2] . "-" . $dateValue[1] . "-" . $dateValue[0], $this->timezone);
			$dateValue->setTime(0, 0, 0);
			$result[] = $this->prototypeGame;
			$result[count($result) - 1]['date'] = $dateValue;
		} else if (mb_strpos($class, 'list-group-item') !== false) {
			$lastDate = clone $result[count($result) -1]['date'];
			$result[] = $this->prototypeGame;
			$result[count($result) - 1]['date'] = $lastDate;
		}else if (mb_strpos($class, 'col-md-1 time col-xs-12') !== false) {
			/** @var DateTime $date */
			$date = $result[count($result) -1]['date'];
			$timeEx = explode(":", $element->nodeValue);
			$date->setTime($timeEx[0], $timeEx[1]);
		} else if (mb_strpos($class, 'col-md-5 col-xs-12 teamA') !== false) {
			$result[count($result) -1]['teamA'] = $element->nodeValue;
			if (mb_strpos($class, 'tabMyTeam') !== false)
				$result[count($result) -1]['hometeam'] = 'teamA';
		} else if (mb_strpos($class, 'col-md-5 col-xs-12 teamB') !== false) {
			$result[count($result) -1]['teamB'] = $element->nodeValue;
			if (mb_strpos($class, 'tabMyTeam') !== false)
				$result[count($result) -1]['hometeam'] = 'teamB';
		} else if (mb_strpos($class, 'sppStatusText') !== false) {
			$result[count($result) - 1]['status'] = $element->nodeValue;
		} else if (mb_strpos($class, 'col-xs-12 col-md-5 torA') !== false) {
			$result[count($result) - 1]['resultA'] = $element->nodeValue;
		} else if (mb_strpos($class, 'col-xs-12 col-md-5 torB') !== false) {
			$result[count($result) - 1]['resultB'] = $element->nodeValue;
		}else if (mb_strpos($class, 'col-xs-11 col-md-offset-1 font-small') !== false) {
			$elements = $element->getElementsByTagName('span');
			foreach ($elements as $elementSub)
				$result = $this->parseCurrentGameLines($elementSub, $result);
			if ($result[count($result) -1]['status'] ?? null)
				$result[count($result) -1]['type'] = trim(preg_replace('/'.addcslashes($result[count($result) -1]['status'], '()./\'"').'.*/s', '', $element->nodeValue));
			else
				$result[count($result) -1]['type'] = preg_replace('/Spielnummer.*/s', '', $element->nodeValue);
			$gamenumber = trim(preg_replace('/.*Spielnummer/s', '', $element->nodeValue));
			$gamenumber = preg_replace('/(&nbsp;){1,}/', '', $gamenumber);
			if (preg_match_all('/[0-9]*/s', $gamenumber, $gamenumberMatch))
				$result[count($result) -1]['gamenumber'] = $gamenumberMatch[0][2] ?? null;
			$result[count($result) -1]['location'] = trim(preg_replace('/.*'.$result[count($result) -1]['gamenumber'].'/s', '', $element->nodeValue));
		}

		return $result;
	}

	private function getTeamGames()
	{
		$this->cacheTeam = [];
		$sdom = new SDomDocument();
		$dom = $sdom->getByUrl($this->url);
		$elements = $dom->getElementsByTagName('div');

		$result = [];
		/** @var DOMElement $element */
		foreach ($elements as $element) {
			$class = $element->getAttribute('class');
			$pos = mb_strpos($class, 'nisListeRD list-group');
			if ($pos === false)
				continue;

			$result = array_merge($result, $this->parseTeamGame($element));
		}

		return $result;
	}

	private function parseTeamGame(DOMElement $element)
	{
		$result = [];
		$elements = $element->getElementsByTagName('div');
		/** @var DOMElement $element */
		foreach ($elements as $element) {
			if ($element instanceof DOMElement)
				$result = $this->parseTeamGameLines($element, $result);
		}

		return $result;
	}

	private function parseTeamGameLines(DOMElement $element, array $data)
	{
		$class = $element->getAttribute('class');
		$result = $data;

		if (mb_strpos($class, 'list-group-item sppTitel') !== false) {
			$this->cacheTeam['type'] = $element->nodeValue;
		} else if (mb_strpos($class, 'list-group-item') !== false) {
			$result[] = $this->prototypeGame;
			$result[count($result) - 1]['type'] = $this->cacheTeam['type'] ?? null;
		} else if (mb_strpos($class, 'col-md-3 date') !== false) {
			$dateValue = explode('.', mb_substr($element->nodeValue, 3, 10));
			$dateValue = new DateTime($dateValue[2] . "-" . $dateValue[1] . "-" . $dateValue[0], $this->timezone);
			$timeValue = explode(':', mb_substr($element->nodeValue, 13, 5));
			if (count($timeValue) > 1)
				$dateValue->setTime($timeValue[0], $timeValue[1]);
			else
				$dateValue->setTime(0, 0, 0);
			$result[count($result) - 1]['date'] = $dateValue;
		} else if (mb_strpos($class, 'col-md-5 col-xs-12 teamA col-12') !== false) {
			$result[count($result) -1]['teamA'] = $element->nodeValue;
			if (mb_strpos($class, 'tabMyTeam') !== false)
				$result[count($result) -1]['hometeam'] = 'teamA';
		} else if (mb_strpos($class, 'col-md-5 col-xs-12 teamB col-12') !== false) {
			$result[count($result) -1]['teamB'] = $element->nodeValue;
			if (mb_strpos($class, 'tabMyTeam') !== false)
				$result[count($result) -1]['hometeam'] = 'teamB';
		} else if (mb_strpos($class, 'col-xs-12 col-md-5 torA col-12') !== false) {
			$result[count($result) - 1]['resultA'] = $element->nodeValue;
		} else if (mb_strpos($class, 'col-xs-12 col-md-5 torB col-12') !== false) {
			$result[count($result) - 1]['resultB'] = $element->nodeValue;
		} else if (mb_strpos($class, 'sppStatusText') !== false) {
			$result[count($result) - 1]['status'] = $element->nodeValue;
		}

		return $result;
	}

	private function groupByDay($games)
	{
		$result = [];
		foreach ($games as $game) {
			/** @var DateTime $date */
			$date = clone $game['date'];
			$daykey = $date->format('Y-m-d');
			if (!isset($result[$daykey]))
				$result[$daykey] = [];
			$result[$daykey][] = $game;
		}

		return $result;
	}

	private function render($gamedays, $renderMode = 'grid')
	{
		$result = '';
		if ($renderMode === 'grid')
			$result = $this->renderGridDay($gamedays);
		else if ($renderMode === 'carousel')
			$result = $this->renderCarouselDay($gamedays);

		return $result;
	}

	private function renderCarouselDay($gamedays)
	{
		$slideId = 'slide'.rand(1, 100);
		$gamedayChunks = array_chunk($gamedays, 3, true);
		$content = '';
		$active = 'active';
		foreach ($gamedayChunks as $gamedays) {
			$content .= '<div class="carousel-item '.$active.'">';
			$content .= '<div class="container-fluid">';
			$content .= '<div class="row">';
			foreach ($gamedays as $gamedaykey => $gameday) {
				$content .= '<div class="col-md-4 col-sm-6 col-12 pb-3">';
				$content .= $this->renderGameDay($gamedaykey, $gameday);
				$content .= '</div>';
			}
			$content .= '</div>';
			$content .= '</div>';
			$content .= '</div>';
			$active = '';
		}

		$icons = '';
		if (count($gamedayChunks) > 1) {
			$icons = '	<a class="carousel-control-prev ccp" href="#'.$slideId.'" role="button" data-slide="prev" style="justify-content: left; width: 30px;">
							<i class="fas fa-3x fa-chevron-left text-info"></i>
						</a>
						<a class="carousel-control-next ccn" href="#'.$slideId.'" role="button" data-slide="next" style="justify-content: flex-end; width: 30px;">
							<i class="fas fa-3x fa-chevron-right text-info"></i>
						</a>';
		}


		$result = '	<div id="'.$slideId.'" class="carousel slide" data-ride="carousel">
						<div class="carousel-inner">
							'.$content.'
						</div>
						'.$icons.'
					</div>';

		return $result;
	}

	private function renderGame($game)
	{
		if ($game['status'])
			$status = '	<div class="text-danger">
							<i class="fas fa-exclamation-triangle"></i> '.$game['status'].'
						</div>';

		if ($game['hometeam'] === 'teamA')
			$game['homeA'] = 'fw-bold';
		if ($game['hometeam'] === 'teamB')
			$game['homeB'] = 'fw-bold';

		$result = '	<div class="">
						<div class="badge bg-primary w-100 mb-3">
							'.$game['type'].'
						</div>
						<div class="d-flex mb-2">
							<div class="align-self-start text-center ps-2 me-2">
								<i class="far fa-2x fa-clock"></i>
							</div>
							<div class="align-self-center">
								'.Date::dateFormat($game['date'], 'H:i').'
							</div>
						</div>
						<div class="d-flex justify-content-between">
							<div class="align-self-start '.($game['homeA'] ?? null).'">
								'.$game['teamA'].'
							</div>
							<div class="'.($game['homeA'] ?? null).'">
								'.$game['resultA'].'
							</div>
						</div>
						<div class="d-flex justify-content-between">
							<p class="align-self-start '.($game['homeB'] ?? null).'">
								'.$game['teamB'].'
							</p>
							<p class="'.($game['homeB'] ?? null).'">
								'.$game['resultB'].'
							</p>
						</div>
						'.($status ?? null).'
					</div>';

		return $result;
	}

	private function renderGameDay(string $gamedaykey, array $gameday)
	{
		/** @var DateTime $date */
		$date = new DateTime($gamedaykey, $this->timezone);
		$headerText = Date::dateFormat($date, 'l d. F Y');
		$bodyText = '';
		foreach ($gameday as $key => $game)
			$bodyText .= $this->renderGame($game);

		$result =   '<div class="card h-100">
						<div class="card-header text-center">
							<strong>'.$headerText.'</strong>
						</div>
						<div class="card-body">
							'.$bodyText.'
						</div>
					</div>';
		return $result;
	}

	private function renderGridDay($gamedays)
	{
		$content = '';
		foreach ($gamedays as $gamedaykey => $gameday) {
			$content .= '<div class="col-md-4 col-sm-6 col-12 pb-3">';
			$content .= $this->renderGameDay($gamedaykey, $gameday);
			$content .= '</div>';
		}

		$result = '	<div class="container-fluid">
						<div class="row">
							'.$content.'
						</div>
					</div>';

		return $result;
	}
}
