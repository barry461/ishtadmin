<?php

class LibUrl
{
    const IS_REPLACE = false;

    const VOD_SORT_URI = '/videos/sort/%s/%s/';
    const VOD_CATEGORY_URI = '/videos/category/%s/%s/';
    const VOD_SEARCH_URI = '/videos/search/%s/%s/';
    const VOD_HOT_URI = '/videos/hot/%s/%s/';
    const VOD_DETAIL_URI = '/videos/%s/';
    const VOD_HOT_SEARCH_URI = '/videos/hot/%s/';
    const VOD_TAG_URI = '/videos/tag/%s/%s/';
    const VOD_AUTHOR_URI = '/videos/author/%s/%s/';
    const VOD_EMBED_URI = '/videos/embed/%s/';

    const ARTICLES_CATEGORY_URI = '/category/%s/%s/';
    const ARTICLES_SEARCH_URI = '/search/%s/%s/';
    const ARTICLES_TAG_URI = '/tag/%s/%s/';
    const ARTICLES_AUTHOR_URI = '/author/%s/%s/';
    const ARTICLES_DETAIL_URI = '/archives/%s/';

    const USER_VIDEOS_URI = '/user/videos/%s/%s/';

    const HOME_QUESTION_URI = '/home/question/';
    const HOME_CONTACT_URI = '/home/contact/';
    const HOME_PRIVACY_URI = '/home/privacy/';
    const HOME_PROTOCOL_URI = '/home/protocol/';
    const HOME_DMCA_URI = '/home/dmca/';
    const HOME_2257_URI = '/home/x2257/';

    public static function url_vod_sort($sort, $page = 1): string
    {
        return rtrim(sprintf(self::VOD_SORT_URI, $sort, $page), '/') . '/';
    }

    public static function url_vod_category($category, $page = 1): string
    {
        return rtrim(sprintf(self::VOD_CATEGORY_URI, $category, $page), '/') . '/';
    }

    public static function url_vod_search($kwy, $page = 1): string
    {
        $kwy = rawurlencode(strtolower(trim($kwy)));
        return rtrim(sprintf(self::VOD_SEARCH_URI, $kwy, $page), '/') . '/';
    }

    public static function url_vod_hot_word($page = 1): string
    {
        return rtrim(sprintf(self::VOD_HOT_SEARCH_URI, $page), '/') . '/';
    }

    public static function url_vod_detail($id): string
    {
        return rtrim(sprintf(self::VOD_DETAIL_URI, $id), '/') . '/';
    }

    public static function url_vod_hot($word = 'list', $page = 1): string
    {
        $word = rawurlencode(strtolower(trim($word)));
        return rtrim(sprintf(self::VOD_HOT_URI, $word, $page), '/') . '/';
    }

    public static function url_vod_tag($tag, $page = 1): string
    {
        $tag = rawurlencode(strtolower(trim($tag)));
        return rtrim(sprintf(self::VOD_TAG_URI, $tag, $page), '/') . '/';
    }

    public static function url_vod_author($user_id, $page = 1): string
    {
        return rtrim(sprintf(self::VOD_AUTHOR_URI, $user_id, $page), '/') . '/';
    }

    public static function url_vod_embed($id): string
    {
        return rtrim(sprintf(self::VOD_EMBED_URI, $id), '/') . '/';
    }

    public static function url_articles_category($category, $page = 1): string
    {
        return rtrim(sprintf(self::ARTICLES_CATEGORY_URI, $category, $page), '/') . '/';
    }

    public static function url_articles_search($kwy, $page = 1): string
    {
        $kwy = rawurlencode(strtolower(trim($kwy)));
        return rtrim(sprintf(self::ARTICLES_SEARCH_URI, $kwy, $page), '/') . '/';
    }

    public static function url_articles_detail($id): string
    {
        return rtrim(sprintf(self::ARTICLES_DETAIL_URI, $id), '/') . '/';
    }

    public static function url_articles_tag($tag, $page = 1): string
    {
        $tag = rawurlencode(strtolower(trim($tag)));
        return rtrim(sprintf(self::ARTICLES_TAG_URI, $tag, $page), '/') . '/';
    }

    public static function url_articles_author($author, $page = 1): string
    {
        return rtrim(sprintf(self::ARTICLES_AUTHOR_URI, $author, $page), '/') . '/';
    }

    public static function url_user_videos($user_id, $page = 1): string
    {
        return rtrim(sprintf(self::USER_VIDEOS_URI, $user_id, $page), '/') . '/';
    }

    public static function url_pc_image($url): string
    {
        return self::IS_REPLACE ? 'image[' . $url . ']' : url_image($url);
    }

    public static function url_pc_video($url, $t = 0): string
    {
        return self::IS_REPLACE ? 'video[' . $url . '?t=' . $t . ']' : url_pc_video($url, $t);
    }

    public static function url_pc_image_proxy($url): string
    {
        return self::IS_REPLACE ? 'url_image_proxy[' . $url . ']' : url_image_proxy($url);
    }
}