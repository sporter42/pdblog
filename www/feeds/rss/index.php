<?php
    header('Content-type: application/xml');
    echo('<?xml version="1.0" encoding="utf-8"?>' . "\n");

    require "_init.php";

    $blog_posts = $pdb->list_blog_posts();
?>
<feed xmlns="http://www.w3.org/2005/Atom">
<title><?=$pdb->blog_name?></title>
<link rel="alternate" type="text/html" href="<?=$pdb->blog_full_base_url?>" />
<link rel="self" type="application/atom+xml" href="<?=$pdb->blog_full_base_url?>/feeds/rss" />
<id><?=$pdb->blog_full_base_url?>/feed/rss</id>
<updated><?=$blog_posts[0]['UpdatedAtDT']->format(DATE_ATOM)?></updated>
<?php
foreach ($blog_posts as $blog_post) {
    $createdAtFormatted = (new DateTime($blog_post['CreatedAt']))->format(DATE_ATOM);
?>
    <entry>
        <title><?=($blog_post['IsExternalLink']) ? '' : '&#9783; '?><?=$blog_post['Title']?></title>
        <?php if ($blog_post['IsExternalLink']) { ?>
            <link rel="alternate" type="text/html" href="<?=$blog_post['LinkURL']?>" />
            <link rel="related" type="text/html" href="<?=$pdb->blog_full_base_url?>/<?=$blog_post['RelativeURL']?>" />
        <?php } else { ?>
            <link rel="alternate" type="text/html" href="<?=$pdb->blog_full_base_url?>/<?=$blog_post['RelativeURL']?>" />
        <?php } ?>
        <id><?=$pdb->blog_full_base_url?>/<?=$blog_post['RelativeURL']?></id>
        <published><?=$createdAtFormatted?></published>
        <updated><?=$createdAtFormatted?></updated>
        <?php if (isset($blog_post['CreatedByFullName'])) { ?>
        <author>
            <name><?=$blog_post['CreatedByFullName']?></name>
            <uri><?=$pdb->blog_full_base_url?></uri>
        </author>
        <?php } ?>
        <?php if ($blog_post['IsExternalLink']) { ?>
            <content type="html" xml:base="<?=$pdb->blog_full_base_url?>" xml:lang="en"><![CDATA[ 
                <?=$blog_post['ContentHtml']?> 
                <div><a title="Permanent link to ‘<?=$blog_post['Title']?>’" href="<?=$pdb->blog_full_base_url?>/<?=$blog_post['RelativeURL']?>">&nbsp;&#9783;&nbsp;</a></div>
            ]]></content>
        <?php } else { ?>
            <content type="html" xml:base="<?=$pdb->blog_full_base_url?>" xml:lang="en"><![CDATA[ 
                <?=$blog_post['ContentHtml']?> 
            ]]></content>
        <?php } ?>
    </entry>
<?php
    }
?>
</feed>