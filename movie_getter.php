<?php
function action_search($search_words, $result_num){
	$search_parameters = array();

	// proxyなし
	$feedURL = "http://gdata.youtube.com/feeds/api/videos?vq={$search_words}&orderby=relevance&lr=ja&max-results={$result_num}";
	$xml = simplexml_load_file($feedURL);

	//検索にヒットした動画の数
	$hit_cnt = $xml->children('http://a9.com/-/spec/opensearchrss/1.0/');
	$total = $hit_cnt->totalResults;

	foreach($xml->entry as $entry){
		//SimpleXMLElementクラスのchildrenメソッドで、名前空間URIを引数に持つ
		$media = $entry->children('http://search.yahoo.com/mrss/');

		//タイトル
		$title = $media->group->title;

		//動画説明
		$description = $media->group->description;

		//動画URL movie_url
		$attrs = $media->group->player->attributes();
		$movie_url = $attrs['url'];

		//サムネイル thumbnail_url
		$attrs = $media->group->thumbnail->attributes();
		$thumbnail_url = $attrs['url'];

		//再生回数 view_count
		$yt = $entry->children('http://gdata.youtube.com/schemas/2007');
		$attrs = $yt->statistics->attributes();
		$view_cnt = $attrs['viewCount'];

		//再生時間
		$yt = $media->children('http://gdata.youtube.com/schemas/2007');
		$attrs = $yt->duration->attributes();
		$movie_seconds = $attrs['seconds'];

		//評価
		$gd = $entry->children('http://schemas.google.com/g/2005');
		if ($gd->rating) {
			$attrs = $gd->rating->attributes();
			$rating = $attrs['average'];
		} else {
			$rating = 0;
		}

		//動画の絞込み
		if(evaluate_movie($total, $view_cnt, $rating)){
			$search_parameters[] =array(
				'description'  =>$description,
				'movie_seconds'=>$movie_seconds,
				'movie_url'    =>$movie_url,
				'rating'       =>$rating,	
				'thumbnail_url'=>$thumbnail_url,
				'title'        =>$title,
				'view_cnt'     =>$view_cnt,
			);
		}
	}
	
	echo "検索したキーワード: {$search_words}</br>";

	foreach($search_parameters as $search_results){
		echo "<tr><td colspan=\"2\" class=\"line\"></td></tr>\n";
		echo "<tr>\n";
		echo "<td><a href=\"{$search_results['movie_url']}\"><img src=\"{$search_results['thumbnail_url']}\"/></a></td>\n";
		echo "<td><a href=\"{$search_results['movie_url']}\">{$search_results['title']}</a><br/>\n";
		echo "{$search_results['movie_seconds']} min. | {$search_results['rating']} user rating<br/>\n";
		echo "{$search_results['description']}</td>\n";
		echo "</tr>\n";
	}
}

//動画を検索にヒットした動画数、再生回数、評価により判定する関数
//通ればtrue、通らなければfalseを返す
function evaluate_movie($total, $viewcount, $rating){
	$judge = false;

	//評価4以上が最低条件
	if($rating >= 4){
		//再生回数による条件分岐
		if ($total < 10){
			if($viewcount > 300) $judge = true;
		}
		elseif ($total < 100){
			if($viewcount > 700) $judge = true;
		}
		elseif ($total < 1000) {
			if($viewcount > 4000) $judge = true;
		}
		elseif ($total < 10000){
			if ($viewcount > 10000) $judge = true;
		}
		else {
			if ($viewcount > 30000) $judge = true;
		}
	}
	return $judge;
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><?php echo $_POST ? '検索結果一覧' : 'Youtube Searcher'; ?></title>
		<style>
		img {
			padding: 2px; 
			margin-bottom: 15px;
			border: solid 1px silver; 
		}
		td {
			vertical-align: top;
		}
		td.line {
			border-bottom: solid 1px black;  
		}
		</style>
	</head>
	<body>
<?php
	if (!isset($_POST['submit'])) {
?>
	<h1>ようこそ！YoutubeSearcherへ！</h1>  
	<h4>このサイトはYoutubeの動画指定されたキーワードで検索し、<br>
	再生回数、評価などから質のいい動画のみを選定し、探し出してくれる<br>
	サービスを提供しています。</h4><br>
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
	  検索したいキーワード: <br/>
	  <input type="text" name="search_words" />
	  <p/>
	  最大何件表示しますか？: <br/>
	  <select name="result_num">
		<option value="10">10</option>
		<option value="25">25</option>
		<option value="50">50</option>
		<option value="100">100</option>
	  </select>
	  <p/>
	  <input type="submit" name="submit" value="Search"/>  
	</form>
<?php      
	} else {
		if (!isset($_POST['search_words']) || empty($_POST['search_words'])) {
			die ('ERROR: Please enter one or more search keywords');
		} else {
			$search_words = $_POST['search_words'];
		}

		//検索する件数
		if (!isset($_POST['result_num']) || empty($_POST['result_num'])) {
			$result_num = 5;
		} else {
			$result_num = $_POST['result_num'];
		}
?>

		<h1>検索結果</h1>
		<p/>
		<table>
<?php    
		action_search($search_words, $result_num);
	}
?>
		</table>
	</body>
</html>

