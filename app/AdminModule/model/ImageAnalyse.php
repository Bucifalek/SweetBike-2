<?php
/**
 * @author Jan Kotrba <jan.kotrbaa@gmail.com>
 * @date 20:20, 11. 5. 2015
 */

namespace App\AdminModule\Model;

use Nette;
use Tracy\Debugger;

class ImageAnalyse extends Nette\Object
{

	private $records;

	public function __construct()
	{
		$this->records = [];
	}

	public function addStorage($temp)
	{
		if (is_readable($temp)) {
			$this->records[] = filesize($temp);
		}
	}

	public function resolve()
	{
		if (count($this->records)) {
			$bytes = array_values($this->records);
			$max = max($bytes);

			$bytes = array_sum($bytes);
			if ($max <= 500000) {
				return array($bytes, 'ok', 'Obrázky májí ideální velikost.');
			} else if ($bytes > 500000 AND $bytes <= 1000000) {
				return array($bytes, 'warning', 'Poměrně velké obrázky.');
			} else {
				return array($bytes, 'danger', 'Galerie je příliš velka! Zmenšete její velikost!');
			}
		}

		return false;
	}
}