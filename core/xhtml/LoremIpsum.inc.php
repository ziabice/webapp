<?php
/**
 * (c) 2008, 2009 by Luca Gambetta <l.gambetta@bluenine.it>
 * see LICENSE.txt for licensing informations
 */

/**
 * Crea del testo di tipo "Lorem Ipsum..."
 *
 * @author Luca Gambetta <l.gambetta@bluenine.it>
 */
class LoremIpsum {
	public static
		$lorem_ipsum = array(
			'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed pharetra dignissim dolor. Sed mollis. Nam mi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed sapien. Suspendisse sed lectus et ipsum congue ultrices. Donec sem erat, lacinia laoreet, porttitor vitae, aliquet id, nulla. Aliquam eu ipsum in quam mollis semper. Maecenas et elit. Nulla odio lacus, auctor sed, sagittis eget, porttitor eget, neque.',
			'Curabitur et nibh non augue vulputate tempor. Donec tristique, mi a consectetuer molestie, ante metus pellentesque purus, non bibendum urna quam et sapien. Nulla facilisi. Phasellus consequat. Nullam a lacus. Aenean purus arcu, auctor non, iaculis tempus, mattis sit amet, pede. Ut eu turpis vitae elit lacinia sollicitudin. Curabitur suscipit convallis turpis. Donec leo ipsum, hendrerit quis, pretium et, sollicitudin in, massa. Morbi in nisi. Sed ac ligula. Suspendisse potenti.',
			'Nulla lectus ipsum, placerat eget, dignissim quis, tempus vitae, tellus. Sed pharetra. Sed velit ligula, tempus a, porta a, commodo quis, mi. Ut mi. Pellentesque ac nunc. Phasellus elementum sagittis odio. Donec diam. Nunc nunc. Phasellus in neque. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Phasellus non odio. Vivamus id pede. Etiam faucibus, dui eu adipiscing molestie, nibh risus aliquet tortor, sed ultricies odio leo sit amet dolor. Suspendisse feugiat gravida nisl.',
			'Ut et urna. Proin semper porta magna. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vivamus ipsum tortor, dapibus nec, consequat sed, ullamcorper sed, arcu. Integer auctor pretium justo. Curabitur eget ante. In lacus erat, tristique eget, viverra a, convallis vel, magna. Nunc aliquet ipsum scelerisque turpis pellentesque tempus. In fringilla cursus libero. Aenean mattis. Integer et massa id mauris commodo tempor. Donec nibh mi, elementum vitae, semper quis, blandit vel, ligula.',
			'In justo ipsum, vestibulum id, convallis quis, dignissim fringilla, sapien. Mauris rhoncus. Vestibulum nulla justo, lobortis quis, dignissim eu, commodo ut, justo. Fusce metus dui, mollis et, laoreet nec, blandit vel, enim. Vestibulum arcu nunc, blandit id, vulputate sed, elementum et, eros. Morbi elementum felis a arcu pellentesque ultricies. Phasellus mollis, leo eget rutrum volutpat, mi urna imperdiet ante, et malesuada diam lectus sed nisi. Curabitur condimentum mollis diam. Integer et ante. Proin eu ante ac sapien posuere pharetra. Etiam enim. Nullam tempus vehicula nulla. Maecenas id nulla nec eros tempus ornare. Nulla in dolor eget felis iaculis congue. Sed justo lectus, pretium nec, ullamcorper ac, molestie eu, sem. In ante est, bibendum vitae, porta quis, elementum in, dui. Nam sed nisl. Aliquam augue lorem, egestas sed, accumsan sit amet, hendrerit et, felis. Nullam malesuada eros eu lacus. Suspendisse tincidunt eros at dui.'
		);


	/**
	 * Ritorna il testo "Lorem Ipsum...", eventualmente in un tag.
	 *
	 * @param string $tag tag XHTML in cui includere il testo, vuoto per nessun tag
	 * @return string il testo
	 */
    public static function get($tag = 'p') {
		if (strlen($tag) > 0) return "<{$tag}>".self::$lorem_ipsum[0]."</{$tag}>";
		else return self::$lorem_ipsum[0];
	}

	/**
	 * Ritorna più paragrafi di testo "Lorem Ipsum", eventualmente in un tag.
	 *
	 * Il testo viene variato con ogni paragrafo.
	 *
	 * @param integer $count il numero di paragrafi da generare
	 * @param string $tag tag XHTML in cui includere il testo, vuoto per nessun tag
	 * @return string il testo
	 */
	public static function getMany($count, $tag = 'p') {
		end(self::$lorem_ipsum);

		$out = array();
		for($c = 0; $c < $count; $c++) {
			$s = next(self::$lorem_ipsum);
			$out[] = ($s === FALSE ? reset(self::$lorem_ipsum) : $s);
		}
		if (strlen($tag) > 0) return "<{$tag}>".implode($out, "</{$tag}><{$tag}>")."</{$tag}>\n";
		else return implode($out, "\n");

	}
}

