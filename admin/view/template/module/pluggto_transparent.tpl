<?php echo $header; ?>

<?php echo $column_left; ?>

<div id="content">
    <div class="page-header" style="margin-top: 5px;">

    <div class="container-fluid">

      <div class="pull-right">
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default">
          <i class="fa fa-reply"></i>
        </a>
      </div>

    </div>
  </div>

  <div class="col-md-12 text-center" style="margin-bottom: 30px;">
	<iframe id="iframe" src="<?php echo $url; ?>" style="width: 100%;overflow: hidden;margin-bottom: 120px; height: 400px; " frameborder="0" onload='resizeIframe(this)'></iframe>
  </div>
</div>

<?php echo $footer; ?>