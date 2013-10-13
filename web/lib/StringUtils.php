<?

class StringUtils {
	public static function get_freq($txt_string) {
		/* split $content into array of substrings of $content i.e wordwise */
		$txt_string_lower = strtolower($txt_string);
		$wordArray = preg_split('/[^a-zA-Z]/', $txt_string_lower, -1, PREG_SPLIT_NO_EMPTY);

		 
		/* "stop words", filter them  */
		$filteredArray = array_filter($wordArray, function($x){
		        if (strlen($x)>3) {
		        return $x;
		        }
		     });

		$filteredArray = array_filter($filteredArray, function($x){
		       return !preg_match("/^(.|youtube|about|since|About|a||A|an|An|as|As|least|only|thing|next|say|also|other|their|ast|very|when|Former|during|says||every|those|news|update|https|there|take|near|then|than|ever|should|how|cause|gets|your|where|said|been|does|come|thanks|down|reuters|live|into|before|after|sharethis|that|didn|could|want|wants|just|things|must|more|white|house|would|bring|against|him|Him|because|tgif|look|looking|will|from|under|over|they|with|have|he|He|and|like|And|you|You|it|It|the|The|this|This|what|What|that|That|at|At|on|On|in|In|amp|via|Via|or|of|is|Is|are|Are|for|to|co|tco|http)$/",$x);
		     });     
		     
		/* get associative array of values from $filteredArray as keys and their frequency count as value */
		$wordFrequencyArray = array_count_values($filteredArray);
		 
		/* Sort array from higher to lower, keeping keys */
		arsort($wordFrequencyArray);
		 
		/* grab Top 10, huh sorted? */
		$top_words = array_slice($wordFrequencyArray,0,10);
		 
		/* display them 
		foreach ($top_words as $topWord => $frequency)
		    echo "<br>$top_words --  $frequency<br/>";
		*/
		return ($top_words);
	}

	public static function gen_user_string($top_user_array) {
	    $related_users = array();
	    foreach ($top_user_array as $topWord => $frequency) {
	        if ($frequency > 1) {
	        	echo "<BR>$topWord FREQ $frequency";
	        	$related_users[$frequency] = $topWord;
	        }
	    }
	    return $related_users;
	}

	public static function gen_new_query_string($top_word_array, $q_string_orig) {
	    //echo "<br>$q_string_orig";
	    $related_queries = array();
	    foreach ($top_word_array as $topWord => $frequency) {
	        if (strpos($q_string_orig, $topWord) === false AND $frequency > 2) {
	            $related_queries[$frequency] = $topWord;   
	        }
	    }
	    return $related_queries;
	}

	public static function get_name_entities($tweet_string, $names_array) {
	    $stopword_name = array("BBC ","Another ", "BREAKING ", "The ", "THE ", "Former ","An ", "New Post","TOP STORIES","BBC " ,"Which ","Hey ", "This ","That ","These ", "Would ","You ", "RT ", " RT", "RSS ", "CNN", "Breaking News", "BREAKING NEWS","My ", "Did ", "About ", "We ", "Not ","Into ","Is ", "Only ", "If ", "So ", "THIS ", "His ","It ", "Of ", "Will ","Please ","Can ","In ", "On ", "And ", "Why","MUST", "From", "Some ", "After ", "With ", "Latest ", "Jokes" , "FUCK", " The", "LUNCH ", "Watching", " Can ", "For ","Was ","Get ","Very ", "What ","Does ","Here ", "Have ","DTN " , "How ", "Are ", " I ","Can ", "As ", "All ", "Although ");
	    $rexSafety = "/^([A-Z][\w-]*(\s+[A-Z][\w-]*)+)/";
	    $strings2 = preg_split($rexSafety, $tweet_string, 0, PREG_SPLIT_OFFSET_CAPTURE);
	    if (count($strings2) > 0) {
		    $len_str_1 = strlen($strings2[0][0]);
		    $len_name_1 = $strings2[1][1] - $len_str_1;
		    $name_string_1 = substr($tweet_string, $len_str_1, $len_name_1);
		    $name_string_final  = str_replace($stopword_name, "", $name_string_1);

		    if (substr_count($name_string_final, ' ') > 0 AND substr_count($name_string_final, ' ') < 3) {
				$add_name_1 = true;
				$words_arr = explode(" ", $name_string_final);
				foreach ($words_arr as $key => $value) {
					if (strlen($value) <3) {
						$add_name_1 = false;
					}
				}
				if ($add_name_1 === true) {
					$names_array[] = $name_string_final;
				}
		    }
		    $num_array_len = count($strings2);
	        if (count($strings2) > 1) {
	            $len_str_2 = strlen($strings2[0][0]) + $len_name_1 + strlen($strings2[1][0]);
	            $len_name_2 = $strings2[2][1] - $len_str_2;
	            $name_string_2 = substr($tweet_string, $len_str_2, $len_name_2);
	            $name_string_final_2  = str_replace($stopword_name, " ", $name_string_2);
		    	if (substr_count($name_string_final_2, ' ') > 0 AND substr_count($name_string_final_2, ' ') < 3) {
					$add_name_2 = true;
					$words_arr_2 = explode(" ", $name_string_final_2);
					foreach ($words_arr_2 as $key => $value){
						if (strlen($value) <3) {
							$add_name_2 = false;
						}
					}
					if ($add_name_2 === true) {
						$names_array[] = $name_string_final_2;
					}
			    }
			}
	    }
	    return $names_array;
	}

}

?>