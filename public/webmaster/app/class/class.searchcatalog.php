<?php
class SearchCatalog
{
    private static $alexaExtensionUrl = 'http://www.alexa.com/minisiteinfo/%s?offset=5&version=alxg_20100607';

    public static function alexa($domain)
    {
        return self::getAlexaInfoFromExtensionPage($domain);
    }

    private static function getAlexaInfoFromExtensionPage($domain) {
        $stats = array(
            'rank'=>0,
            'linksin'=>0,
            'country_code'=>'XX',
            'country_name'=>'Unknown',
            'country_rank'=>0,
            'data'=>array(),
            'speed_time'=>0, // ms Backward compatibility
            'pct'=>0, // If PCT = 46, then 54 of all websites loads faster Backward compatibility
            'review_count'=>0, // Backward compatibility
            'review_avg'=>0, // Backward compatibility
        );

        $url = sprintf(self::$alexaExtensionUrl, $domain);
        if(!$html = HttpCurl::curl($url)) {
            return $stats;
        }

        $pattern_raw_global_rank = "#<a(?:[^>]*)class=[\"'](small|big) data[\"'](?:[^>]*)>(.*?)</a>#is";
        preg_match($pattern_raw_global_rank, $html, $raw_global_rank);

        if(isset($raw_global_rank[2])) {
            $pattern_extract_rank_global = "#(<span(?:[^>]*)>(.*?)</span>)#is";
            $stats['rank'] = (int) remove_non_int_characters(preg_replace($pattern_extract_rank_global, "",  $raw_global_rank[2]));

            $pattern_extract_delta = "#<span(?:[^>]*)class=[\"']delta rank (\w+)[\"'](?:[^>]*)>(.*)</span>#is";
            preg_match($pattern_extract_delta, $raw_global_rank[2], $delta);

            if(isset($delta[1])) {
                $stats['data']['delta_direction'] = $delta[1];
                $stats['data']['delta'] = (int) remove_non_int_characters($delta[2]);
            }
        }

        $pattern_raw_local_rank = "#<p(?:[^>]*)class=[\"']textsmall nomarginbottom margintop10[\"'](?:[^>]*)>(.*?)</p>#is";
        preg_match($pattern_raw_local_rank, $html, $raw_local_rank);

        if(isset($raw_local_rank[1])) {
            $end_country_name_pos = mb_stripos($raw_local_rank[1], "Rank");
            $country_code = EmojiFlag::emojiToIso(mb_substr($raw_local_rank[1], 0, 2));
            if($country_code !== null) {
                $stats['country_code'] = $country_code;
            }
            if($end_country_name_pos !== false) {
                $start_country_name_pos = 2;
                $stats['country_name'] = trim(mb_substr($raw_local_rank[1], $start_country_name_pos, $end_country_name_pos - $start_country_name_pos));
            }

            $pattern_local_rank = "#<span(?:[^>]*)class=[\"']small data textbig marginleft10[\"'](?:[^>]*)><span class=[\"']hash[\"']>\#</span>(.*?)</span>#is";
            preg_match($pattern_local_rank, $raw_local_rank[1], $local_rank);
            if(isset($local_rank[1])) {
                $stats['country_rank'] = (int) remove_non_int_characters($local_rank[1]);
            }
        }


        $pattern_sites_linking = "#<p(?:[^>]*)class=[\"']textbig nomargin[\"'](?:[^>]*)>(.*?)<a(?:[^>]*)>(.*?)</a></p>#is";
        preg_match($pattern_sites_linking, $html, $matches_sites_linking);
        if(isset($matches_sites_linking[2])) {
            $stats['linksin'] = (int) remove_non_int_characters($matches_sites_linking[2]);
        }


        $similar_sites = array();
        $similar_sites_pattern = "#<a(?:[^>]*)class=[\"']Block truncation Link[\"'](?:[^>]*)href=[\"'](.*?)[\"'](?:[^>]*)>(.*?)</a>#is";
        preg_match_all($similar_sites_pattern, $html, $matches_similar_sites);
        if(isset($matches_similar_sites[1], $matches_similar_sites[2])) {
            foreach ($matches_similar_sites[1] as $i => $url) {
                $similar_sites[$i]['url'] = $url;
                $similar_sites[$i]['name'] = _v($matches_similar_sites[2], $i, "Unknown");
            }
            $stats['data']['similar_sites'] = $similar_sites;
        }



        $pattern_related_keywords_container = "#<section(?:[^>]*)class=[\"']Full[\"'](?:[^>]*)>(.*?)</section>#is";
        preg_match($pattern_related_keywords_container, $html, $matches_related_keywords_raw);

        if(isset($matches_related_keywords_raw[1])) {
            $pattern_related_keywords = "#<a(?:[^>]*)class=[\"']Link[\"'](?:[^>]*)>(.*?)</a>#is";
            preg_match_all($pattern_related_keywords, $matches_related_keywords_raw[1], $matches_related_keywords);
            if(isset($matches_related_keywords[1])) {
                $stats['data']['related_keywords'] = $matches_related_keywords[1];
            }
        }

        return $stats;
    }
}