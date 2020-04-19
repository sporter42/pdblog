<?php require "_init.php" ?>

<?php
    $blog_post = $pdb->get_blog_post($_GET['Y'], $_GET['M'], $_GET['D'], $_GET['Slug']);
?>

<?php $title = $blog_post['Title']; require "_header.php" ?>

<div class="blogPost">
<h2><?php if($blog_post['IsExternalLink']) { ?><a href="<?=$blog_post['LinkURL']?>"><?=$blog_post['Title']?></a><?php } else { echo($blog_post['Title']); } ?></h2>

<div class="blogPostBody"><?=$blog_post['ContentHtml']?></div>

<p class="blogDateFooter"><a href="<?="{$pdb->blog_full_base_url}/{$blog_post['RelativeURL']}"?>" class="logoBlock" title="Permalink">&#9755;</a> <span class="blogDateSeparator"><?php echo(date_format($blog_post['Date'], "M jS, Y")); ?></span></p>

<?php if ($pdb->authenticated) { ?><p class="blogPostAdmin">
    <a href="<?php echo("{$pdb->blog_full_base_url}/admin/add_edit_post.html?Y={$blog_post['Y']}&M={$blog_post['M']}&D={$blog_post['D']}&Slug={$blog_post['Slug']}"); ?>" class="btn btn-primary" role="button">Edit</a>
    <button type="badge" href="#" class="btn btn-danger" data-toggle="modal" data-target="#postDeleteConfirm">Delete</button>
</p><?php } ?>
</div>

<?php if ($pdb->authenticated) { ?>
<!-- Modal -->
<div class="modal fade" id="postDeleteConfirm" tabindex="-1" role="dialog" aria-labelledby="postDeleteConfirmTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="postDeleteConfirmTitle">Confirm Post Deletion</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you wish to delete this post?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
        <a class="btn btn-danger" href="<?="{$pdb->blog_full_base_url}/admin/delete_post.html?Y={$blog_post['Y']}&M={$blog_post['M']}&D={$blog_post['D']}&Slug={$blog_post['Slug']}"?>" role="button">Yes, Delete Post</a>
      </div>
    </div>
  </div>
</div>
<?php } ?>

<?php require '_footer.php' ?>
