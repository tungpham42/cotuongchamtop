<?php
class Social
{
    private static $piUrl = 'https://api.pinterest.com/v1/urls/count.json?url=%s';

    public static function facebook($url)
    {
        $pattern = array(
            'share_count'=>0,
            'comment_count'=>0,
            'total_count'=>0,
            'comment_plugin_count'=>0,
            'reaction_count'=>0,
        );
        $response = Facebook::ins()->getLikes($url);
        if(isset($response['error'])) {
            return $pattern;
        }
        $pattern['share_count'] = isset($response['engagement']['share_count']) ? (int) $response['engagement']['share_count'] : 0;
        $pattern['comment_count'] = isset($response['engagement']['comment_count']) ? (int) $response['engagement']['comment_count'] : 0;
        $pattern['comment_plugin_count'] = isset($response['engagement']['comment_plugin_count']) ? (int) $response['engagement']['comment_plugin_count'] : 0;
        $pattern['reaction_count'] = isset($response['engagement']['reaction_count']) ? (int) $response['engagement']['reaction_count'] : 0;

        $pattern['total_count'] = $pattern['share_count'] + $pattern['comment_count'] + $pattern['comment_plugin_count'] + $pattern['reaction_count'];

        return $pattern;
    }

    public static function pinterest($url)
    {
        $url = sprintf(self::$piUrl, $url);
        if(!$response = HttpCurl::curl($url)) {
            return 0;
        }
        $response = str_replace(array('(', ')'), '', $response);
        $response = str_replace("receiveCount", '', $response);
        if(!$json = json_decode($response, true)) {
            return 0;
        }
        return isset($json['count']) ? (int)$json['count'] : 0;
    }
}