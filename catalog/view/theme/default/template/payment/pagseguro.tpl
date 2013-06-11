<!-- File with the button redirect to PagSeguro  -->

<form action="<?php echo $action; ?>" method="post">
    <div class="buttons">
    <div class="right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
    </div>
  </div>
     <input type="hidden" id="url_ps" name="url_ps" value="<?php echo $url_ps; ?>">
</form>