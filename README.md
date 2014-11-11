MovieGetter
===========
## YoutubeAPIを用いたライブラリ

####youtubeapi.php

 **getInstance()**  
シングルトーンパターンを実装している。

 **function get_movies($query_data)**  
このfunctionは検索条件をまとめた配列(検索したい文字列、地域など)を引数に持ち、検索したものをxml形式で返してくれる。
