<?php

declare(strict_types=1);

namespace Milosa\SocialMediaAggregatorBundle\Sites;

use Abraham\TwitterOAuth\TwitterOAuth;
use Milosa\SocialMediaAggregatorBundle\Message;

class TwitterFetcher extends Fetcher
{
    /**
     * @var TwitterOAuth
     */
    private $oauth;
    /**
     * @var string
     */
    private $fetchScreenName;
    /**
     * @var int
     */
    private $numberOfMessages;

    public function __construct(TwitterOAuth $twitterOauth, string $fetchScreenName, int $numberOfMessages)
    {
        $this->fetchScreenName = $fetchScreenName;
        $this->numberOfMessages = $numberOfMessages;

        $this->oauth = $twitterOauth;
    }

    /**
     * @return Message[]
     */
    public function getData(): array
    {
        if ($this->data === null) {
            $this->data = $this->getTimeLine();
        }

        $result = [];
        foreach ($this->data as $key => $value) {
            $result[$key] = $this->createMessage($value);
        }

        return $result;
    }

    private function getTimeLine()
    {
        $this->oauth->get('statuses/user_timeline', ['screen_name' => $this->fetchScreenName, 'count' => $this->numberOfMessages]);

        return $this->oauth->getLastBody();
    }

    /**
     * @param $value
     *
     * @return Message
     */
    private function createMessage(\stdClass $value): Message
    {
        $message = new Message();
        $message->setBody($this->linkifyText($value->text));
        $message->setURL('https://twitter.com/statuses/'.$value->id);
        $message->setDate(\DateTime::createFromFormat('D M d H:i:s O Y', $value->created_at));
        $message->setAuthor($value->user->name);
        $message->setAuthorURL('https://twitter.com/'.$value->user->screen_name);
        $message->setAuthorDescription($value->user->description);
        $message->setScreenName($value->user->screen_name);
        $message->setTemplate('twitter.twig');
        $message->setAuthorThumbnail($value->user->profile_image_url_https);

        return $message;
    }

    private function linkifyText(string $text): string
    {
        $text = $this->safeReplace($text, "/\B(?<![=\/])#([\w]+[a-z]+([0-9]+)?)/i", '#');

        return $this->safeReplace($text, "/\B@(\w+(?!\/))\b/i", '@');
    }

    /**
     * @param string $text
     * @param string $regex
     * @param string $prefix
     * @param int    $index
     *
     * @return string
     */
    private function safeReplace(string $text, string $regex, string $prefix, int $index = 1): string
    {
        return preg_replace_callback($regex, function ($matches) use ($prefix, $index) {
            $name = htmlentities($matches[$index], ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return '<a href="https://twitter.com/'.$name.'">'.$prefix.$name.'</a>';
        }, $text);
    }
}