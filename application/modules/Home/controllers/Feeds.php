<?php



/**
 * FeedsController.php
 * @author  chenmoyuan
 */
class FeedsController extends WebController
{


    public function feedAction()
    {
        $resp = $this->getResponse();
        $resp->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $resp->setHeader('X-Robots-Tag', 'noindex, follow');
        $rss = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $rss .= '<rss version="2.0"
xmlns:content="http://purl.org/rss/1.0/modules/content/"
xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
xmlns:atom="http://www.w3.org/2005/Atom"
xmlns:wfw="http://wellformedweb.org/CommentAPI/">'
            ."\n";
        $rss .= '<channel>';
        $rss .= '<title>'.options('title').'</title>';
        $rss .= '<link>'.options('siteUrl').'</link>';
        $rss .= '<description>'.options('description').'</description>';
        $rss .= '<language>zh-cn</language>';

        /** @var array<ContentsModel> $items */
        $items = cached('content:home-rss-items')
            ->group('gp:content:rss-list')
            ->chinese("RSS订阅文章列表缓存")
            ->fetchPhp(function () {
                return ContentsModel::queryPost()
                    ->with('author')
                    ->orderByDesc('cid')
                    ->limit(100)
                    ->get(['cid', 'title', 'text', 'created', 'commentsNum']);
            });

        $results = [];
        foreach ($items as $item) {
            $text = \tools\LibMarkdown::loadWebMarkdown($item->text , true);
            $url = $item->url();
            $commentRss = url('feed.comments', [$item->cid]);
            $pubDate = $item->created;
            $author = $item->authorValue();

            $rss .= '<item>';
            $rss .= '<title>'.htmlspecialchars($item->title).'</title>';
            $rss .= "<link>$url</link>";
            $rss .= "<guid>$url</guid>";
            $rss .= "<dc:creator>{$author}</dc:creator>";
            $rss .= "<description><![CDATA[\n{$text}\n]]></description>";
            $rss .= "<content:encoded xml:lang=\"zh-CN\"><![CDATA[\n{$text}\n]]></content:encoded>";
            $rss .= "<slash:comments>{$item->commentsNum}</slash:comments>";
            $rss .= "<comments>{$url}#comments</comments>";
            $rss .= "<wfw:commentRss>{$commentRss}</wfw:commentRss>";
            $rss .= "<pubDate>{$pubDate}</pubDate>";
            $rss .= '</item>';

            $results[] = [
                'title'       => $item->title,
                'link'        => $item->url(),
                'description' => \tools\LibMarkdown::loadWebMarkdown($item->text,true),
                'pubDate'     => $item->created ? date('c', is_numeric($item->getRawOriginal('created')) ? (int)$item->getRawOriginal('created') : strtotime((string)$item->created)) : '',
            ];
        }

        foreach ($results as $item) {
            $rss .= '<item>';
            $rss .= '<title>' . htmlspecialchars($item['title']) . '</title>';
            $rss .= '<link>' . htmlspecialchars($item['link']) . '</link>';
            $rss .= "<description><![CDATA[\n" . $item['description'] . "\n]]></description>";
            $rss .= '<pubDate>' . $item['pubDate'] . '</pubDate>';
            $rss .= '</item>';
        }

        $rss .= '</channel>';
        $rss .= '</rss>';

        // 使用 setBody 输出响应内容
        $resp->setBody($rss);
        return false;

    }

    public function commentFeedAction()
    {
        $cid = (int) $this->getRequest()->getParam('cid');


        // 缓存文章信息
        $post = cached("archive:{$cid}")
            ->group('gp:content:archives')
            ->chinese("RSS评论Feed文章详情缓存")
            ->fetchPhp(function () use ($cid) {
                return ContentsModel::queryPost()->where('cid', $cid)->first();
            });
            
         //echo json_encode($post);die();
        if (!$post) {
            // 文章不存在时返回空的 RSS feed，状态码 200
            $this->getResponse()->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
            $this->getResponse()->setHeader('X-Robots-Tag', 'noindex, follow');
            $emptyRss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $emptyRss .= '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
            $emptyRss .= '<channel rdf:about="' . rtrim(options('siteUrl'), '/') . '/feed/rss/archives/' . $cid . '/">' . "\n";
            $emptyRss .= '<title>' . htmlspecialchars(options('title') . ' - 评论') . '</title>' . "\n";
            $emptyRss .= '<link>' . htmlspecialchars(rtrim(options('siteUrl'), '/')) . '</link>' . "\n";
            $emptyRss .= '<description>文章不存在</description>' . "\n";
            $emptyRss .= "<items>\n<rdf:Seq>\n</rdf:Seq>\n</items>\n</channel>\n";
            $emptyRss .= '</rdf:RDF>';
            $this->getResponse()->setBody($emptyRss);
            return false;
        }

        $siteUrl = rtrim(options('siteUrl'), '/');
        $postUrl = $post->url(); 
        // $post = $post->loadWebMarkdown();

       // echo json_encode($post);die();

        // 缓存评论数据
        $comments = cached("comments:rss:{$cid}")
            ->group('gp:content:rss-comments')
            ->chinese("RSS评论Feed评论列表缓存")
            ->fetchPhp(function () use ($cid) {
                return CommentsModel::query()
                    ->where('cid', $cid)
                    ->where('status', 'approved')
                    ->orderByDesc('coid')
                    ->limit(20)
                    ->get(['coid', 'author', 'created', 'text']);
            });
          $post->text = \tools\LibMarkdown::loadWebMarkdown($post->text, true);
        
        $plainText = strip_tags($post->text);
        $summary = mb_substr($plainText, 0, 50);   
        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rdf:RDF 
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns="http://purl.org/rss/1.0/"
        xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";

        $rss .= '<channel rdf:about="' . $siteUrl . '/feed/rss/archives/' . $cid . '/">' . "\n";
        $rss .= '<title>' . htmlspecialchars(options('title') . ' - ' . $post->title . ' 的评论') . '</title>' . "\n";
        $rss .= '<link>' . htmlspecialchars($postUrl) . '</link>' . "\n";
        $rss .= '<description>' . htmlspecialchars($summary) . '</description>' . "\n";
        $rss .= "<items>\n<rdf:Seq>\n";

        foreach ($comments as $comment) {
            $rss .= '<rdf:li resource="' . htmlspecialchars($postUrl) . '#comment-' . $comment->coid . "\" />\n";
        }

        $rss .= "</rdf:Seq>\n</items>\n</channel>\n";

        foreach ($comments as $comment) {
            $link = $postUrl . '#comment-' . $comment->coid;
            $content = htmlspecialchars(strip_tags($comment->text));
            $author = htmlspecialchars($comment->author);
            $pubDate = date('c', $comment->created->getTimestamp());

            $rss .= '<item rdf:about="' . $link . "\">\n";
            $rss .= '<title>' . $author . "</title>\n";
            $rss .= '<link>' . $link . "</link>\n";
            $rss .= '<dc:date>' . $pubDate . "</dc:date>\n";
            $rss .= '<description>' . $content . "</description>\n";
            $rss .= "</item>\n";
        }

        $rss .= '</rdf:RDF>';
        // 使用 setBody 输出响应内容
        $this->getResponse()->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $this->getResponse()->setHeader(
            'Content-Disposition',
            'attachment; filename="' . rawurlencode('下载') . '.rdf"; filename*=UTF-8\'\'' . rawurlencode('下载') . '.rdf'
        );
        $this->getResponse()->setHeader('X-Robots-Tag', 'noindex, follow');
        $this->getResponse()->setBody($rss);
        return false;
    }

    public function atomAction()
    {
        $cid = (int)$this->getRequest()->getParam('cid'); 

        /** @var ContentsModel $item */
        $item = cached("archive:{$cid}")
            ->group('gp:content:archives')
            ->chinese("Atom Feed文章详情缓存")
            ->fetchPhp(function () use ($cid) {
                return ContentsModel::queryPost()
                    ->with('author')
                    ->where('cid', $cid)
                    ->first(['cid', 'title', 'text', 'created', 'commentsNum']);
            });

        if (!$item) {
            // 文章不存在时返回空的 Atom feed，状态码 200
            $resp = $this->getResponse();
            $resp->setHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
            $resp->setHeader('X-Robots-Tag', 'noindex, follow');
            $siteUrl = rtrim(options('siteUrl'), '/');
            $emptyAtom = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $emptyAtom .= '<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="zh-CN">' . "\n";
            $emptyAtom .= '<title type="text">' . htmlspecialchars(options('title') . ' - 评论') . "</title>\n";
            $emptyAtom .= '<subtitle type="text">文章不存在</subtitle>' . "\n";
            $emptyAtom .= '<updated>' . date('c') . "</updated>\n";
            $emptyAtom .= '<id>' . $siteUrl . '/feed/atom/archives/' . $cid . "/</id>\n";
            $emptyAtom .= '<link rel="self" type="application/atom+xml" href="' . $siteUrl . '/feed/atom/archives/' . $cid . "/\" />\n";
            $emptyAtom .= "</feed>";
            $resp->setBody($emptyAtom);
            return false;
        }

        $resp = $this->getResponse();
        $resp->setHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
        $resp->setHeader('X-Robots-Tag', 'noindex, follow');

        $url = $item->url();
        $author = $item->authorValue();
        $pubDate = date('c', strtotime($item->created));
        $item->text = \tools\LibMarkdown::loadWebMarkdown($item->text, true);
        $siteUrl = rtrim(options('siteUrl'), '/');

        $atom = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $atom .= '<feed xmlns="http://www.w3.org/2005/Atom"'
            . ' xmlns:thr="http://purl.org/syndication/thread/1.0"'
            . ' xml:lang="zh-CN"'
            . ' xml:base="' . htmlspecialchars($url) . '">' . "\n";

        $atom .= '<title type="text">' . htmlspecialchars(options('title') . ' - ' . $item->title . ' 的评论') . "</title>\n";
        $atom .= '<subtitle type="text">' . htmlspecialchars(mb_substr(strip_tags($item->text), 0, 150)) . "</subtitle>\n";
        $atom .= '<updated>' . $pubDate . "</updated>\n";
        $atom .= '<generator uri="http://typecho.org/" version="1.1/17.10.30">Typecho</generator>' . "\n";
        $atom .= '<link rel="alternate" type="text/html" href="' . htmlspecialchars($url) . "\" />\n";
        $atom .= '<id>' . $siteUrl . '/feed/atom/archives/' . $cid . "/</id>\n";
        $atom .= '<link rel="self" type="application/atom+xml" href="' . $siteUrl . '/feed/atom/archives/' . $cid . "/\" />\n";

        // 缓存评论数据
        $comments = cached("comments:atom:{$cid}")
            ->group('gp:content:rss-comments')
            ->chinese("Atom Feed评论列表缓存")
            ->fetchPhp(function () use ($cid) {
                return CommentsModel::query()
                    ->where('cid', $cid)
                    ->where('status', 'approved')
                    ->orderByDesc('coid')
                    ->limit(20)
                    ->get();
            });

        foreach ($comments as $comment) {
            $commentLink = $url . '#comment-' . $comment->coid;
            $commentTime = $comment->created ? date('c', is_numeric($comment->getRawOriginal('created')) ? (int)$comment->getRawOriginal('created') : strtotime((string)$comment->created)) : '';
            $commentText = strip_tags($comment->text);
            $commentAuthor = htmlspecialchars($comment->author);

            $atom .= '<entry>' . "\n";
            $atom .= '<title type="html"><![CDATA[' . $commentAuthor . ']]></title>' . "\n";
            $atom .= '<link rel="alternate" type="text/html" href="' . $commentLink . '" />' . "\n";
            $atom .= '<id>' . $commentLink . "</id>\n";
            $atom .= '<updated>' . $commentTime . "</updated>\n";
            $atom .= '<published>' . $commentTime . "</published>\n";
            $atom .= '<author><name>' . $commentAuthor . '</name><uri></uri></author>' . "\n";
            $atom .= '<summary type="html"><![CDATA[' . mb_substr($commentText, 0, 100) . ']]></summary>' . "\n";
            $atom .= '<content type="html" xml:base="' . $commentLink . '" xml:lang="zh-CN"><![CDATA[<p>' . htmlspecialchars($commentText) . '</p>]]></content>' . "\n";
            $atom .= '</entry>' . "\n";
        }

        $atom .= "</feed>";

        $resp->setBody($atom);
        return false;
    }

    public function rssCommentsAction()
    {
        $cid = (int)$this->getRequest()->getParam('cid');

        // 缓存文章信息
        $post = cached("archive:{$cid}")
            ->group('gp:content:archives')
            ->chinese("RSS评论Feed文章详情缓存")
            ->fetchPhp(function () use ($cid) {
                return ContentsModel::queryPost()
                    ->where('cid', $cid)
                    ->first(['cid', 'title', 'text', 'created']);
            });

        if (!$post) {
            // 文章不存在时返回空的 RSS feed，状态码 200
            $resp = $this->getResponse();
            $resp->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
            $resp->setHeader('X-Robots-Tag', 'noindex, follow');
            $rssLink = rtrim(options('siteUrl'), '/') . "/feed/archives/{$cid}/";
            $emptyRss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $emptyRss .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:wfw="http://wellformedweb.org/CommentAPI/">' . "\n";
            $emptyRss .= "<channel>\n";
            $emptyRss .= "<title>" . htmlspecialchars(options('title') . ' - 评论') . "</title>\n";
            $emptyRss .= "<link>" . htmlspecialchars(rtrim(options('siteUrl'), '/')) . "</link>\n";
            $emptyRss .= "<atom:link href=\"{$rssLink}\" rel=\"self\" type=\"application/rss+xml\" />\n";
            $emptyRss .= "<language>zh-CN</language>\n";
            $emptyRss .= "<description>文章不存在</description>\n";
            $emptyRss .= "<lastBuildDate>" . date('r') . "</lastBuildDate>\n";
            $emptyRss .= "<pubDate>" . date('r') . "</pubDate>\n";
            $emptyRss .= "</channel>\n</rss>";
            $resp->setBody($emptyRss);
            return false;
        }

        // 缓存评论数据
        $comments = cached("comments:rss-comments:{$cid}")
            ->group('gp:content:rss-comments')
            ->chinese("RSS评论Feed评论列表缓存")
            ->fetchPhp(function () use ($cid) {
                return CommentsModel::query()
                    ->where('cid', $cid)
                    ->where('status', 'approved')
                    ->orderByDesc('coid')
                    ->limit(20)
                    ->get();
            });

        $resp = $this->getResponse();
        $resp->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $resp->setHeader('X-Robots-Tag', 'noindex, follow');

        $postTitle = htmlspecialchars(options('title') . ' - ' . $post->title . ' 的评论');
        $postLink = $post->url();
        $post->text = \tools\LibMarkdown::loadWebMarkdown($post->text, true);
        $rssLink = rtrim(options('siteUrl'), '/') . "/feed/archives/{$cid}/";
        $description = htmlspecialchars(mb_substr(strip_tags($post->text), 0, 200));
        $pubDate = date('r', strtotime($post->created));

        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0"
            xmlns:content="http://purl.org/rss/1.0/modules/content/"
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
            xmlns:atom="http://www.w3.org/2005/Atom"
            xmlns:wfw="http://wellformedweb.org/CommentAPI/">' . "\n";
        $rss .= "<channel>\n";
        $rss .= "<title>{$postTitle}</title>\n";
        $rss .= "<link>{$postLink}</link>\n";
        $rss .= "<atom:link href=\"{$rssLink}\" rel=\"self\" type=\"application/rss+xml\" />\n";
        $rss .= "<language>zh-CN</language>\n";
        $rss .= "<description>{$description}</description>\n";
        $rss .= "<lastBuildDate>{$pubDate}</lastBuildDate>\n";
        $rss .= "<pubDate>{$pubDate}</pubDate>\n";

        foreach ($comments as $comment) {
            $commentLink = $postLink . '#comment-' . $comment->coid;
            $commentText = strip_tags($comment->text);
            $commentHtml = '<p>' . htmlspecialchars($commentText) . '</p>';
            $commentTime = $comment->created
                ? date('r', is_numeric($comment->getRawOriginal('created')) ? (int)$comment->getRawOriginal('created') : strtotime((string)$comment->created))
                : '';
            $commentAuthor = htmlspecialchars($comment->author);

            $rss .= "<item>\n";
            $rss .= "<title>{$commentAuthor}</title>\n";
            $rss .= "<link>{$commentLink}</link>\n";
            $rss .= "<guid>{$commentLink}</guid>\n";
            $rss .= "<pubDate>{$commentTime}</pubDate>\n";
            $rss .= "<dc:creator>{$commentAuthor}</dc:creator>\n";
            $rss .= "<description><![CDATA[{$commentText}]]></description>\n";
            $rss .= "<content:encoded xml:lang=\"zh-CN\"><![CDATA[{$commentHtml}]]></content:encoded>\n";
            $rss .= "<comments>{$commentLink}#comments</comments>\n";
            $rss .= "</item>\n";
        }

        $rss .= "</channel>\n</rss>";

        $resp->setBody($rss);
        return false;
    }

}