<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="currency-pair-settings-page">
    <div class="currency-pair-block">
        <!-- Insert Modal Button Start -->
        <button type="button" id="insert_button" class="button button-primary" data-toggle="modal" data-target="#currencyPairModal"><?php _e("Add New Pair", "currency-pair"); ?></button>
        <!-- Insert Modal Button End -->  

        <!-- Insert Modal Code Start -->
        <div class="modal fade" id="currencyPairModal" role="dialog">
            <div class="modal-dialog">                
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title add_title"><?php _e("Add New Currency Pair", "currency-pair"); ?></h4>
                        <h4 class="modal-title update_title"><?php _e("Update New Currency Pair", "currency-pair"); ?></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form method="post" id="insert_currency_pair_form">
                            <div class="form-group" id="select1">
                                <label for="currency1"><?php _e("Currency1:", "currency-pair"); ?></label>
                                <select class="form-select" name="currency1" id="currency1" >
                                    <option value=""><?php _e("Select Currency", "currency-pair"); ?></option>
                                    <option value="AFN">AFN</option>
                                    <option value="AFN">AFN</option>
                                    <option value="ALL">ALL</option>
                                    <option value="DZD">DZD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="AON">AON</option>
                                    <option value="XCD">XCD</option>
                                    <option value="ARS">ARS</option>
                                    <option value="AMD">AMD</option>
                                    <option value="AUD">AUD</option>
                                    <option value="AZN">AZN</option>
                                    <option value="BSD">BSD</option>
                                    <option value="BHD">BHD</option>
                                    <option value="BDT">BDT</option>
                                    <option value="BBD">BBD</option>
                                    <option value="BYN">BYN</option>
                                    <option value="BZD">BZD</option>
                                    <option value="XOF">XOF</option>
                                    <option value="BTN">BTN</option>
                                    <option value="BOB">BOB</option>
                                    <option value="BAM">BAM</option>
                                    <option value="BWP">BWP</option>
                                    <option value="BRL">BRL</option>
                                    <option value="BND">BND</option>
                                    <option value="BGL">BGL</option>
                                    <option value="BIF">BIF</option>
                                    <option value="CVE">CVE</option>
                                    <option value="KHR">KHR</option>
                                    <option value="XAF">XAF</option>
                                    <option value="CAD">CAD</option>
                                    <option value="CLP">CLP</option>
                                    <option value="CNY">CNY</option>
                                    <option value="COP">COP</option>
                                    <option value="KMF">KMF</option>
                                    <option value="NZD">NZD</option>
                                    <option value="CRC">CRC</option>
                                    <option value="HRK">HRK</option>
                                    <option value="CUP">CUP</option>
                                    <option value="KPW">KPW</option>
                                    <option value="CDF">CDF</option>
                                    <option value="DKK">DKK</option>
                                    <option value="DJF">DJF</option>
                                    <option value="DOP">DOP</option>
                                    <option value="USD">USD</option>
                                    <option value="EGP">EGP</option>
                                    <option value="ERN">ERN</option>
                                    <option value="SZL">SZL</option>
                                    <option value="ETB">ETB</option>
                                    <option value="FJD">FJD</option>
                                    <option value="GMD">GMD</option>
                                    <option value="GEL">GEL</option>
                                    <option value="GHC">GHC</option>
                                    <option value="GTQ">GTQ</option>
                                    <option value="GNF">GNF</option>
                                    <option value="GYD">GYD</option>
                                    <option value="HTG">HTG</option>
                                    <option value="HNL">HNL</option>
                                    <option value="HUF">HUF</option>
                                    <option value="ISK">ISK</option>
                                    <option value="INR">INR</option>
                                    <option value="IDR">IDR</option>
                                    <option value="IRR">IRR</option>
                                    <option value="IQD">IQD</option>
                                    <option value="ILS">ILS</option>
                                    <option value="JMD">JMD</option>
                                    <option value="JPY">JPY</option>
                                    <option value="JOD">JOD</option>
                                    <option value="KZT">KZT</option>
                                    <option value="KES">KES</option>
                                    <option value="KWD">KWD</option>
                                    <option value="KGS">KGS</option>
                                    <option value="LAK">LAK</option>
                                    <option value="LBP">LBP</option>
                                    <option value="LSL">LSL</option>
                                    <option value="LRD">LRD</option>
                                    <option value="LYD">LYD</option>
                                    <option value="MGA">MGA</option>
                                    <option value="MWK">MWK</option>
                                    <option value="MYR">MYR</option>
                                    <option value="MVR">MVR</option>
                                    <option value="MRU">MRU</option>
                                    <option value="MUR">MUR</option>
                                    <option value="MXN">MXN</option>
                                    <option value="MNT">MNT</option>
                                    <option value="MAD">MAD</option>
                                    <option value="MZM">MZM</option>
                                    <option value="MMK">MMK</option>
                                    <option value="NAD">NAD</option>
                                    <option value="NPR">NPR</option>
                                    <option value="NIO">NIO</option>
                                    <option value="NGN">NGN</option>
                                    <option value="NOK">NOK</option>
                                    <option value="OMR">OMR</option>
                                    <option value="PKR">PKR</option>
                                    <option value="PAB">PAB</option>
                                    <option value="PGK">PGK</option>
                                    <option value="PYG">PYG</option>
                                    <option value="PEN">PEN</option>
                                    <option value="PHP">PHP</option>
                                    <option value="PLN">PLN</option>
                                    <option value="QAR">QAR</option>
                                    <option value="KRW">KRW</option>
                                    <option value="MDL">MDL</option>
                                    <option value="RON">RON</option>
                                    <option value="RUB">RUB</option>
                                    <option value="RWF">RWF</option>
                                    <option value="WST">WST</option>
                                    <option value="STN">STN</option>
                                    <option value="SAR">SAR</option>
                                    <option value="RSD">RSD</option>
                                    <option value="SCR">SCR</option>
                                    <option value="SLL">SLL</option>
                                    <option value="SGD">SGD</option>
                                    <option value="SBD">SBD</option>
                                    <option value="SOS">SOS</option>
                                    <option value="ZAR">ZAR</option>
                                    <option value="SSP">SSP</option>
                                    <option value="LKR">LKR</option>
                                    <option value="SDG">SDG</option>
                                    <option value="SRD">SRD</option>
                                    <option value="SEK">SEK</option>
                                    <option value="CHF">CHF</option>
                                    <option value="SYP">SYP</option>
                                    <option value="TJS">TJS</option>
                                    <option value="THB">THB</option>
                                    <option value="MKD">MKD</option>
                                    <option value="TOP">TOP</option>
                                    <option value="TTD">TTD</option>
                                    <option value="TND">TND</option>
                                    <option value="TRY">TRY</option>
                                    <option value="TMT">TMT</option>
                                    <option value="UGS">UGS</option>
                                    <option value="UAH">UAH</option>
                                    <option value="AED">AED</option>
                                    <option value="GBP">GBP</option>
                                    <option value="TZS">TZS</option>
                                    <option value="UYU">UYU</option>
                                    <option value="UZS">UZS</option>
                                    <option value="VUV">VUV</option>
                                    <option value="VEF">VEF</option>
                                    <option value="VND">VND</option>
                                    <option value="YER">YER</option>
                                    <option value="ZMK">ZMK</option>
                                    <option value="ZWD">ZWD</option>

                                </select>
                            </div>
                            <div class="form-group" id="select2">                        
                                <label for="currency1"><?php _e("Currency2:", "currency-pair"); ?></label>
                                <select class="form-select" name="currency2" id="currency2" >
                                    <option value=""><?php _e("Select Currency", "currency-pair"); ?></option>
                                    <option value="AFN">AFN</option>
                                    <option value="AFN">AFN</option>
                                    <option value="ALL">ALL</option>
                                    <option value="DZD">DZD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="AON">AON</option>
                                    <option value="XCD">XCD</option>
                                    <option value="ARS">ARS</option>
                                    <option value="AMD">AMD</option>
                                    <option value="AUD">AUD</option>
                                    <option value="AZN">AZN</option>
                                    <option value="BSD">BSD</option>
                                    <option value="BHD">BHD</option>
                                    <option value="BDT">BDT</option>
                                    <option value="BBD">BBD</option>
                                    <option value="BYN">BYN</option>
                                    <option value="BZD">BZD</option>
                                    <option value="XOF">XOF</option>
                                    <option value="BTN">BTN</option>
                                    <option value="BOB">BOB</option>
                                    <option value="BAM">BAM</option>
                                    <option value="BWP">BWP</option>
                                    <option value="BRL">BRL</option>
                                    <option value="BND">BND</option>
                                    <option value="BGL">BGL</option>
                                    <option value="BIF">BIF</option>
                                    <option value="CVE">CVE</option>
                                    <option value="KHR">KHR</option>
                                    <option value="XAF">XAF</option>
                                    <option value="CAD">CAD</option>
                                    <option value="CLP">CLP</option>
                                    <option value="CNY">CNY</option>
                                    <option value="COP">COP</option>
                                    <option value="KMF">KMF</option>
                                    <option value="NZD">NZD</option>
                                    <option value="CRC">CRC</option>
                                    <option value="HRK">HRK</option>
                                    <option value="CUP">CUP</option>
                                    <option value="KPW">KPW</option>
                                    <option value="CDF">CDF</option>
                                    <option value="DKK">DKK</option>
                                    <option value="DJF">DJF</option>
                                    <option value="DOP">DOP</option>
                                    <option value="USD">USD</option>
                                    <option value="EGP">EGP</option>
                                    <option value="ERN">ERN</option>
                                    <option value="SZL">SZL</option>
                                    <option value="ETB">ETB</option>
                                    <option value="FJD">FJD</option>
                                    <option value="GMD">GMD</option>
                                    <option value="GEL">GEL</option>
                                    <option value="GHC">GHC</option>
                                    <option value="GTQ">GTQ</option>
                                    <option value="GNF">GNF</option>
                                    <option value="GYD">GYD</option>
                                    <option value="HTG">HTG</option>
                                    <option value="HNL">HNL</option>
                                    <option value="HUF">HUF</option>
                                    <option value="ISK">ISK</option>
                                    <option value="INR">INR</option>
                                    <option value="IDR">IDR</option>
                                    <option value="IRR">IRR</option>
                                    <option value="IQD">IQD</option>
                                    <option value="ILS">ILS</option>
                                    <option value="JMD">JMD</option>
                                    <option value="JPY">JPY</option>
                                    <option value="JOD">JOD</option>
                                    <option value="KZT">KZT</option>
                                    <option value="KES">KES</option>
                                    <option value="KWD">KWD</option>
                                    <option value="KGS">KGS</option>
                                    <option value="LAK">LAK</option>
                                    <option value="LBP">LBP</option>
                                    <option value="LSL">LSL</option>
                                    <option value="LRD">LRD</option>
                                    <option value="LYD">LYD</option>
                                    <option value="MGA">MGA</option>
                                    <option value="MWK">MWK</option>
                                    <option value="MYR">MYR</option>
                                    <option value="MVR">MVR</option>
                                    <option value="MRU">MRU</option>
                                    <option value="MUR">MUR</option>
                                    <option value="MXN">MXN</option>
                                    <option value="MNT">MNT</option>
                                    <option value="MAD">MAD</option>
                                    <option value="MZM">MZM</option>
                                    <option value="MMK">MMK</option>
                                    <option value="NAD">NAD</option>
                                    <option value="NPR">NPR</option>
                                    <option value="NIO">NIO</option>
                                    <option value="NGN">NGN</option>
                                    <option value="NOK">NOK</option>
                                    <option value="OMR">OMR</option>
                                    <option value="PKR">PKR</option>
                                    <option value="PAB">PAB</option>
                                    <option value="PGK">PGK</option>
                                    <option value="PYG">PYG</option>
                                    <option value="PEN">PEN</option>
                                    <option value="PHP">PHP</option>
                                    <option value="PLN">PLN</option>
                                    <option value="QAR">QAR</option>
                                    <option value="KRW">KRW</option>
                                    <option value="MDL">MDL</option>
                                    <option value="RON">RON</option>
                                    <option value="RUB">RUB</option>
                                    <option value="RWF">RWF</option>
                                    <option value="WST">WST</option>
                                    <option value="STN">STN</option>
                                    <option value="SAR">SAR</option>
                                    <option value="RSD">RSD</option>
                                    <option value="SCR">SCR</option>
                                    <option value="SLL">SLL</option>
                                    <option value="SGD">SGD</option>
                                    <option value="SBD">SBD</option>
                                    <option value="SOS">SOS</option>
                                    <option value="ZAR">ZAR</option>
                                    <option value="SSP">SSP</option>
                                    <option value="LKR">LKR</option>
                                    <option value="SDG">SDG</option>
                                    <option value="SRD">SRD</option>
                                    <option value="SEK">SEK</option>
                                    <option value="CHF">CHF</option>
                                    <option value="SYP">SYP</option>
                                    <option value="TJS">TJS</option>
                                    <option value="THB">THB</option>
                                    <option value="MKD">MKD</option>
                                    <option value="TOP">TOP</option>
                                    <option value="TTD">TTD</option>
                                    <option value="TND">TND</option>
                                    <option value="TRY">TRY</option>
                                    <option value="TMT">TMT</option>
                                    <option value="UGS">UGS</option>
                                    <option value="UAH">UAH</option>
                                    <option value="AED">AED</option>
                                    <option value="GBP">GBP</option>
                                    <option value="TZS">TZS</option>
                                    <option value="UYU">UYU</option>
                                    <option value="UZS">UZS</option>
                                    <option value="VUV">VUV</option>
                                    <option value="VEF">VEF</option>
                                    <option value="VND">VND</option>
                                    <option value="YER">YER</option>
                                    <option value="ZMK">ZMK</option>
                                    <option value="ZWD">ZWD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="target_url"><?php _e("Target URL:", "currency-pair"); ?></label>
                                <input type="text" class="form-control" name="target_url" id="target_url" placeholder="<?php _e("Enter Target URL", "currency-pair"); ?>">
                            </div>
                            <input type="hidden" id="formType" value="insert">
                            <input type="hidden" id="post_id" value="">
                            <button type="submit" id="save_btn" class="btn btn-success"><?php _e("Submit", "currency-pair"); ?></button>
                        </form>
                    </div>     
                    <div class="currency_pair_res"><p></p></div>           
                </div>
            </div>
        </div>
        <!-- Insert Modal Code Start -->              
    </div>
</div>

<?php
/**
 * Dispay Posts in admin side
*/
include CURRENCY_PAIRS_ROOT_DIR_ADMIN.'/partials/currency-pairs-admin-posts.php';