<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <!--  Payment Lightbox PagSeguro  -->
    <!DOCTYPE html>

    <?php if ($environment != 'production') { ?>
        <script type="text/javascript"
                src="https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js">
        </script>
    <?php }else{ ?>
        <script type="text/javascript"
                src="https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js">
        </script>
    <?php } ?>

    <script type="text/javascript">
        function checkout()
        {
            PagSeguroLightbox(
                '<?php echo $code ?>',{
                    success: function(){
                        window.location = 'index.php?route=checkout/success';
                    },
                    abort: function(){
                        window.location = 'index.php?route=payment/pagseguro_error';
                    }
                });
        }
    </script>

    <h2>Finalizando sua compra com PagSeguro</h2>

    <div>Sua compra est&aacute; em processo de finaliza&ccedil;&atilde;o, aguarde um instante.
    </div>
        <script type="text/javascript">
            checkout();
        </script>

    <!--  Payment Lightbox PagSeguro  -->

    <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>