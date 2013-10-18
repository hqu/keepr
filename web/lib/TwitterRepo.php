<?

class TwitterRepo {
    public $twitter_collection;
    public function __construct($twitter_collection) {
        $this->twitter_collection = $twitter_collection;

    }

    public function save($user_screen_name, $popular_tweets, $recent_tweets, $images_tweets) {
        $saved_tweets = array(
            "saved_id" => uniqid(),
            "popular" => $popular_tweets,
            "recent" => $recent_tweets,
            "images" => $images_tweets);

        $existed_user = $this->twitter_collection->findOne(array("twitter_name" => $user_screen_name));
        if($existed_user) {
            $this->twitter_collection->update(array("twitter_name" => $user_screen_name), array('$push' => array('saved_tweets' => $saved_tweets )));
        } else {
            $twitter_user = array( "twitter_name" => $user_screen_name,
                "saved_tweets" => array($saved_tweets)
            );

            $this->twitter_collection->insert($twitter_user);
        }
    }

    public function get_saved_tweet_ids($user_screen_name) {
        $saved_tweets = $this->twitter_collection->findOne(array("twitter_name" => $user_screen_name), array("saved_tweets" => true));

        $ids = array();
        foreach($saved_tweets["saved_tweets"] as $saved_tweet) {
            array_push($ids, $saved_tweet["saved_id"]);
        }

        return $ids;
    }
}

?>