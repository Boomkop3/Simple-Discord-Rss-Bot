<?php
ob_start();
class BlogPost
{
    // var $date;
    // var $ts;
    var $link;

    var $title;
    // var $text;
}

class BlogFeed
{
    var $posts = array();

    function __construct($file_or_url, $timestamp)
    {
        $file_or_url = $this->resolveFile($file_or_url);
        if (!($x = simplexml_load_file($file_or_url)))
            return;

        foreach ($x->channel->item as $item)
        {
            $ts = strtotime($item->pubDate);
            if ($ts < $timestamp){
              continue;
            }
            $post = new BlogPost();
            // $post->date  = (string) $item->pubDate;
            $post->link  = (string) $item->link;
            $post->title = (string) $item->title;
            // $post->text  = (string) $item->description;
            // $post->summary = $this->summarizeText($post->text);

            $this->posts[] = $post;
        }
    }

    private function resolveFile($file_or_url) {
        if (!preg_match('|^https?:|', $file_or_url))
            $feed_uri = $_SERVER['DOCUMENT_ROOT'] .'/shared/xml/'. $file_or_url;
        else
            $feed_uri = $file_or_url;

        return $feed_uri;
    }

    private function summarizeText($summary) {
        $summary = strip_tags($summary);

        // Truncate summary line to 100 characters
        $max_len = 100;
        if (strlen($summary) > $max_len)
            $summary = substr($summary, 0, $max_len) . '...';

        return $summary;
    }
}

$ts = time();
if (file_exists('timestamp.ts')){
  $ts = (int)file_get_contents('timestamp.ts');
} else {
  file_put_contents('timestamp.ts', $ts);
}

$terrariaUrl = "https://terraria-servers.com/rss/blog/";
$terrariaFeed = new BlogFeed($terrariaUrl, $ts);
$terrariaPosts = $terrariaFeed->posts;

$salvationUrl = "https://www.mtgsalvation.com/spoilers.rss";
$salvationFeed = new BlogFeed($salvationUrl, $ts);
$salvationPosts = $salvationFeed->posts;

$output = Array(
  "terraria" => $terrariaPosts, 
  "magic" => $salvationPosts
);

echo json_encode($output);
file_put_contents('timestamp.ts', time());
exit;
?>
