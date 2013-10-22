
<?
    session_start();
    require_once("TwitterRepo.php");
    $http_www_root = $_SERVER["HTTP_HOST"];

    $m = new MongoClient();
    $db = $m->keepr;
    $keepr_col = $db->keepr_col;

    $twitter_repo = new TwitterRepo($keepr_col);
    $twitter_repo->save($_SESSION['user_screenname'], $_SESSION["popular_tweets"],
        $_SESSION["recent_tweets"], $_SESSION["images_tweets"]);

    header('Location: ' . $http_www_root . 'index.php');
?>