<!-- View Edit PagSeguro -->

<?php echo $header; ?>
   
<!--  Home :: Payment :: PagSeguro  -->
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
          <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

 <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
 <?php } ?>

<div class="box">
    <div class="heading">
        <h1><img src="view/image/payment.png" alt=""/> <?php echo $heading_title ?></h1>
        <div class="buttons">
            <a onclick="$('#form').submit();" class="button"> <?php echo $button_save; ?> </a>
            <a href="<?php echo $cancel; ?>" class="button"> <?php echo $button_cancel;  ?> </a>
        </div>
    </div>
    
    <div class="content">
        
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
            <table class="form">
                
                <tr>
                    <td>
                        <b> <?php echo $enable_module; ?> </b> <br/>
                        <?php echo $text_module; ?>
                    </td>
                    <td>
                        <?php if( $pagseguro_status) { ?>
                            <input type="radio" name="pagseguro_status" value="1" checked="checked" /> <?php echo $text_yes; ?>  
                            <input type="radio" name="pagseguro_status" value="" /> <?php echo $text_no; ?>
                        <?php } else { ?>
                             <input type="radio" name="pagseguro_status" value="1" /> <?php echo $text_yes; ?>  
                            <input type="radio" name="pagseguro_status" value="" checked="checked"  /> <?php echo $text_no; ?>
                        <?php } ?>
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <b> <?php echo $display_order; ?> </b> <br/>
                        <?php echo $text_order; ?>
                    </td>
                    <td>
                        <input type="text" name="pagseguro_sort_order" value="<?php echo $pagseguro_sort_order;  ?>" />
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <span class="required">*</span>
                        <b> <?php echo $ps_email ?> </b> <br/>
                        <?php echo $text_email; ?>
                    </td>
                    <td>
                        <input type="text" name="pagseguro_email" value="<?php echo $pagseguro_email; ?>" />
                        <?php if ($error_email) { ?>
                            <span class="error"><?php echo $error_email; ?></span>
                        <?php } ?>
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <span class="required">*</span>
                        <b> <?php echo $ps_token; ?> </b> <br/>
                        <?php echo $text_token; ?>
                    </td>
                    <td>
                        <input type="text" name="pagseguro_token" value="<?php echo $pagseguro_token; ?>" />
                        <?php if ($error_token) { ?>
                            <span class="error"><?php echo $error_token; ?></span>
                        <?php } ?>
                    </td>
                </tr>
                    
                <tr>
                    <td>
                        <b> <?php echo $url_forwarding; ?> </b> <br/>
                        <?php echo $text_url_forwarding; ?>
                    </td>
                    <td>
                        <input type="text" name="pagseguro_forwarding" value="<?php echo $pagseguro_forwarding; ?>" />
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <b> <?php echo $url_notification; ?>  </b> <br/>
                        <?php echo $text_url_notification; ?>
                    </td>
                    <td>
                        <input type="text" name="pagseguro_url_notification" value="<?php echo $pagseguro_url_notification; ?>" />
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <b> <?php echo $charset; ?> </b> <br/>
                        <?php echo $text_charset; ?>
                    </td>
                    <td>
                        <?php if( $pagseguro_charset ) { ?>
                            <input type="radio" name="pagseguro_charset" value="1" checked="checked" /> <?php echo $iso; ?>
                            <input type="radio" name="pagseguro_charset" value="" /> <?php echo $utf; ?>
                        <?php } else { ?>
                            <input type="radio" name="pagseguro_charset" value="1"  /> <?php echo $iso; ?>
                            <input type="radio" name="pagseguro_charset" value="" checked="checked" /> <?php echo $utf; ?>
                        <?php } ?>
                    </td>
                </tr>
                
                 <tr>
                    <td>
                        <b> <?php echo $log; ?> </b> <br/>
                        <?php echo $text_log; ?>
                    </td>
                    <td>
                        <?php if ( $pagseguro_log ) { ?>
                            <input type="radio" name="pagseguro_log" value="1" checked="checked" /> <?php echo $text_yes; ?>
                            <input type="radio" name="pagseguro_log" value=""  /> <?php echo $text_no; ?>
                        <?php } else { ?>
                            <input type="radio" name="pagseguro_log" value="1" /> <?php echo $text_yes; ?>
                            <input type="radio" name="pagseguro_log" value=""  checked="checked"/> <?php echo $text_no; ?>
                        <?php } ?>
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <b> <?php echo $directory; ?> </b> <br/>
                        <?php echo $text_directory; ?>
                    </td>
                    <td>
                        <input type="text" name="pagseguro_directory" value="<?php echo $pagseguro_directory; ?>"  /> 
                    </td>
                </tr>
                
            </table>
               
        </form>
        
        
    </div>
    
    </div>
</div>





<?php echo $footer; ?>