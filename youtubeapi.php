<?php

class Youtubeapi{
	private static $self;

	public static function getInstance(){
		if(is_null(self::$self)){
			self::$self = new self;
		}
		return self::$self;
	}

	/*
	 * Converts an array of query.
	 *
	 * @param $query array   The array must have key,"q".
	 *
	 */
	public function get_movies($query_data){
		$xml = null;

		if(array_key_exists('q', $query_data)){
			$query = http_build_query($query_data, '', '&');
			$feedURL ="http://gdata.youtube.com/feeds/api/videos?{$query}";
			$xml = simplexml_load_file($feedURL);
		}

		return $xml;
	}
}