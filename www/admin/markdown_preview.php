<?php 
require "_init.php";

use Michelf\MarkdownExtra;
?>

<?php $title = $blog_post['Title']; require "_header.php" ?>

<div class="blogPost">
    <h2>(Preview) <?=$_POST['Title']?></h2>

    <div class="blogPostBody"><?=MarkdownExtra::defaultTransform($_POST['MarkdownText'])?></div>
</div>

<?php require '_footer.php' ?>
