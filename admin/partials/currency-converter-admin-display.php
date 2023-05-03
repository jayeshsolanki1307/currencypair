<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php _e("Currency Converter", "currency-pair"); ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <h4><?php _e("Currency Converter", "currency-pair"); ?></h4>
        <p><?php _e("Please copy following shortcode to convert currency in frontend.", "currency-pair"); ?></p>        
        <div class="currency-convert-shortocde">
            <p><a id="copy_convert" class="copy_btn btn btn-primary" data-clipboard-text='[currency-convert id="eur,usd"]'>[currency-convert id="eur,usd"]</a></p>
        </div>
    </body>
</html>