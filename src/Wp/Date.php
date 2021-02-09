<?php

namespace Silentx\Wp;

use DateTime;

class Date
{
	public static function dateFormat(DateTime $date, $format = 'd.m.Y H:i:s')
	{
		return wp_date($format, $date->getTimestamp(), $date->getTimezone());
	}
}