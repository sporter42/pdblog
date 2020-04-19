<?php require "_init.php" ?>

<?php 
$blog_posts = $pdb->list_blog_posts();
?>

<?php require "_header.php" ?>

<?php foreach ($blog_posts as $blog_post) { ?>
    <div class="blogPost">
        <?php if (isset($last_post_date) && ($last_post_date != $blog_post['Date'])) { ?>
            <div class="blogDateDivider"><span></span></div>
            <div class="blogDateSeparator"><?php echo(date_format($blog_post['Date'], "M jS, Y")); ?></div>
        <?php } ?>

        <h2><?php if($blog_post['IsExternalLink']) { ?><a href="<?=$blog_post['LinkURL']?>"><?=$blog_post['Title']?></a><?php } else { echo($blog_post['Title']); } ?> <a href="<?=$blog_post['RelativeURL']?>" class="blogPostPermalink" title="Permalink">&#9755;</a></h2>

        <div class="blogPostBody"><?=$blog_post['ContentHtml']?></div>
    </div>
<?php $last_post_date = $blog_post['Date']; } ?>

<?php if (sizeof($blog_posts) == 0) { ?><h1>There are no posts.</h1><p><a href="./login.html">Login</a> to add one.</p><?php } ?>

<?php require '_footer.php' ?>
