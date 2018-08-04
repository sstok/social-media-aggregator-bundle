<?php

declare(strict_types=1);

namespace Milosa\SocialMediaAggregatorBundle\Sites\Twitter;

use Milosa\SocialMediaAggregatorBundle\Message;
use Milosa\SocialMediaAggregatorBundle\MessageFactory;

class TwitterMessageFactory implements MessageFactory
{
    public static function createMessage(string $json): Message
    {
        $result = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON');
        }

        $message = self::createFromDecodedJson($result);

        if (isset($result->retweeted_status)) {
            $message->setRetweet(self::createFromDecodedJson($result->retweeted_status));
        }

        return $message;
    }

    /**
     * @param $result
     *
     * @return string
     */
    private static function runParsers($result): string
    {
        $parsedText = HashTagParser::parse($result->full_text);
        $parsedText = MentionParser::parse($parsedText);

        if (isset($result->entities->media[0]) && $result->entities->media[0]->type === 'photo') {
            $parsedText = PhotoParser::parse($parsedText, $result->entities->media);
        }

        return  URLParser::parse($parsedText, $result->entities->urls);
    }

    /**
     * @param $result
     *
     * @return TwitterMessage
     */
    private static function createFromDecodedJson($result): TwitterMessage
    {
        $message = new TwitterMessage('API', 'twitter.twig');
        $message->setBody($result->full_text);
        $message->setURL('https://twitter.com/statuses/'.$result->id);
        $message->setDate(\DateTime::createFromFormat('D M d H:i:s O Y', $result->created_at));
        $message->setAuthor($result->user->name);
        $message->setAuthorURL('https://twitter.com/'.$result->user->screen_name);
        $message->setAuthorDescription($result->user->description);
        $message->setScreenName($result->user->screen_name);
        $message->setAuthorThumbnail($result->user->profile_image_url_https);
        $message->setParsedBody(self::runParsers($result, $message));

        return $message;
    }
}
