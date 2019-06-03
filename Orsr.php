<?php
namespace Sellastica\Orsr;

class Orsr
{
	use \Nette\SmartObject;

	/** @var string */
	private $url = 'http://orsr.sk/';


	/**
	 * @param string $ico
	 * @return array|null
	 */
	public function getById(string $ico): ?array
	{
		if (preg_match_all(
				"/<a href=\"([^\"]*)\" class=\"link\">Aktuálny<\/a>/",
				$this->toUtf8(file_get_contents($this->url . 'hladaj_ico.asp?ICO=' . urlencode($this->to1250($ico)) . '&SID=0')),
				$links
			) < 1) {
			return null;
		}

		foreach ($links[1] as $link) {
			$out = $this->parse($link);
			if ($out) {
				return $out;
			}
		}

		return null;
	}

	/**
	 * @param string $link
	 * @return array|bool
	 */
	private function parse(string $link)
	{
		$data = $this->toUtf8(file_get_contents(str_replace("&amp;", "&", $this->url . $link)));

		if (preg_match_all("/<div class=\"wrn2\">.*<\/div>/", $data, $match) !== 0) {
			return false;
		}

		preg_match_all("/<td align=\"left\" valign=\"top\" width=\"20%\"> <span class=\"tl\">Obchodné meno:&nbsp;<\/span><\/td>\s*<td align=\"left\" width=\"80%\"><table width=\"100%\" border=\"0\">\s*<tr>\s*<td width=\"67%\"> <span class='ra'>(?<name>[^<]*)<\/span><br><\/td>\s*<td width=\"33%\" valign='top'>&nbsp; <span class='ra'>\(od: \d{2}\.\d{2}\.\d{4}\)<\/span><\/td>\s*<\/tr>\s*<\/table><\/td>\s*<\/tr>\s*<\/table>\s*<table width=\"100%\" border=\"0\" align=\"center\" cellspacing=\"3\" cellpadding=\"0\" bgcolor='#ffffff'>\s*<tr>\s*<td align=\"left\" valign=\"top\" width=\"20%\"> <span class=\"tl\">Sídlo:&nbsp;<\/span><\/td>\s*<td align=\"left\" width=\"80%\"><table width=\"100%\" border=\"0\">\s*<tr>\s*<td width=\"67%\"> <span class='ra'>(?<street>[^<]*)<\/span> <span class='ra'>(?<number>[^<]*)<\/span><br> <span class='ra'>(?<city>[^<]*)<\/span> <span class='ra'>(?<zip>[^<]*)<\/span><br><\/td>\s*<td width=\"33%\" valign='top'>&nbsp; <span class='ra'>\(od: \d{2}\.\d{2}\.\d{4}\)<\/span><\/td>\s*<\/tr>\s*<\/table><\/td>\s*<\/tr>\s*<\/table>\s*<table width=\"100%\" border=\"0\" align=\"center\" cellspacing=\"3\" cellpadding=\"0\" bgcolor='#ffffff'>\s*<tr>\s*<td align=\"left\" valign=\"top\" width=\"20%\"> <span class=\"tl\">IČO:&nbsp;<\/span><\/td>\s*<td align=\"left\" width=\"80%\"><table width=\"100%\" border=\"0\">\s*<tr>\s*<td width=\"67%\"> <span class='ra'>(?<id>[^<]*)<\/span><br><\/td>/i", $data, $tmp);

		return [
			'name' => \Nette\Utils\Strings::trim(html_entity_decode(@$tmp['name'][0])),
			'address' => [
				'street' => \Nette\Utils\Strings::trim(@$tmp['street'][0]),
				'number' => \Nette\Utils\Strings::trim(@$tmp['number'][0]),
				'city' => \Nette\Utils\Strings::trim(@$tmp['city'][0]),
				'zip' => \Nette\Utils\Strings::trim(str_replace(' ', '', @$tmp['zip'][0])),
			],
			'id' => \Nette\Utils\Strings::trim(str_replace(' ', '', @$tmp['id'][0])),
		];
	}

	/**
	 * @param string $string
	 * @return string
	 */
	private function toUtf8(string $string): string
	{
		return iconv('windows-1250', 'utf-8', $string);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	private function to1250(string $string): string
	{
		return iconv('utf-8', 'windows-1250', $string);
	}
}
