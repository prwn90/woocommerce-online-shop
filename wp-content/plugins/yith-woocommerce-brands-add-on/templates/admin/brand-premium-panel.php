<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly
?>

<style>
    .section{
        margin-left: -20px;
        margin-right: -20px;
        font-family: "Raleway",san-serif;
    }
    .section h1{
        text-align: center;
        text-transform: uppercase;
        color: #808a97;
        font-size: 35px;
        font-weight: 700;
        line-height: normal;
        display: inline-block;
        width: 100%;
        margin: 50px 0 0;
    }
    .section ul{
        list-style-type: disc;
        padding-left: 15px;
    }
    .section:nth-child(even){
        background-color: #fff;
    }
    .section:nth-child(odd){
        background-color: #f1f1f1;
    }
    .section .section-title img{
        display: table-cell;
        vertical-align: middle;
        width: auto;
        margin-right: 15px;
    }
    .section h2,
    .section h3 {
        display: inline-block;
        vertical-align: middle;
        padding: 0;
        font-size: 24px;
        font-weight: 700;
        color: #808a97;
        text-transform: uppercase;
    }

    .section .section-title h2{
        display: table-cell;
        vertical-align: middle;
    }

    .section-title{
        display: table;
    }

    .section h3 {
        font-size: 14px;
        line-height: 28px;
        margin-bottom: 0;
        display: block;
    }

    .section p{
        font-size: 13px;
        margin: 25px 0;
    }
    .section ul li{
        margin-bottom: 4px;
    }
    .landing-container{
        max-width: 750px;
        margin-left: auto;
        margin-right: auto;
        padding: 50px 0 30px;
    }
    .landing-container:after{
        display: block;
        clear: both;
        content: '';
    }
    .landing-container .col-1,
    .landing-container .col-2{
        float: left;
        box-sizing: border-box;
        padding: 0 15px;
    }
    .landing-container .col-1 img{
        width: 100%;
    }
    .landing-container .col-1{
        width: 55%;
    }
    .landing-container .col-2{
        width: 45%;
    }
    .premium-cta{
        background-color: #808a97;
        color: #fff;
        border-radius: 6px;
        padding: 20px 15px;
    }
    .premium-cta:after{
        content: '';
        display: block;
        clear: both;
    }
    .premium-cta p{
        margin: 7px 0;
        font-size: 14px;
        font-weight: 500;
        display: inline-block;
        width: 60%;
    }
    .premium-cta a.button{
        border-radius: 6px;
        height: 60px;
        float: right;
        background: url(<?php echo YITH_WCBR_URL?>assets/images/upgrade.png) #ff643f no-repeat 13px 13px;
        border-color: #ff643f;
        box-shadow: none;
        outline: none;
        color: #fff;
        position: relative;
        padding: 9px 50px 9px 70px;
    }
    .premium-cta a.button:hover,
    .premium-cta a.button:active,
    .premium-cta a.button:focus{
        color: #fff;
        background: url(<?php echo YITH_WCBR_URL?>assets/images/upgrade.png) #971d00 no-repeat 13px 13px;
        border-color: #971d00;
        box-shadow: none;
        outline: none;
    }
    .premium-cta a.button:focus{
        top: 1px;
    }
    .premium-cta a.button span{
        line-height: 13px;
    }
    .premium-cta a.button .highlight{
        display: block;
        font-size: 20px;
        font-weight: 700;
        line-height: 20px;
    }
    .premium-cta .highlight{
        text-transform: uppercase;
        background: none;
        font-weight: 800;
        color: #fff;
    }

    @media (max-width: 768px) {
        .section{margin: 0}
        .premium-cta p{
            width: 100%;
        }
        .premium-cta{
            text-align: center;
        }
        .premium-cta a.button{
            float: none;
        }
    }

    @media (max-width: 480px){
        .wrap{
            margin-right: 0;
        }
        .section{
            margin: 0;
        }
        .landing-container .col-1,
        .landing-container .col-2{
            width: 100%;
            padding: 0 15px;
        }
        .section-odd .col-1 {
            float: left;
            margin-right: -100%;
        }
        .section-odd .col-2 {
            float: right;
            margin-top: 65%;
        }
    }

    @media (max-width: 320px){
        .premium-cta a.button{
            padding: 9px 20px 9px 70px;
        }

        .section .section-title img{
            display: none;
        }
    }
</style>
<div class="landing">
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    <?php echo sprintf( __('Upgrade to %1$spremium version%2$s of %1$sYITH WooCommerce Brands Add-On%2$s to benefit from all features!','yith-woocommerce-brands-add-on'),'<span class="highlight">','</span>' );?>
                </p>
                <a href="<?php echo $this->get_premium_landing_uri() ?>" target="_blank" class="premium-cta-button button btn">
                    <span class="highlight"><?php _e('UPGRADE','yith-woocommerce-brands-add-on');?></span>
                    <span><?php _e('to the premium version','yith-woocommerce-brands-add-on');?></span>
                </a>
            </div>
        </div>
    </div>
    <div class="section section-even clear" style="background: url(<?php echo YITH_WCBR_URL ?>assets/images/01-bg.png) no-repeat #fff; background-position: 85% 75%">
        <h1><?php _e('Premium Features','yith-woocommerce-brands-add-on');?></h1>
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WCBR_URL ?>assets/images/01.png" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WCBR_URL ?>assets/images/01-icon.png" />
                    <h2><?php _e('Banners and links','yith-woocommerce-brands-add-on');?></h2>
                </div>
                <p>
                    <?php echo sprintf(__('Two additional details for each brand in your store: show a %1$sbanner%2$s to users when they access the page of the selected brand and specify a %1$slink%2$s redirecting users to a page different from brand archive page (e.g. brand official website).', 'yith-woocommerce-brands-add-on'), '<b>', '</b>');?>
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear" style="background: url(<?php echo YITH_WCBR_URL ?>assets/images/02-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WCBR_URL ?>assets/images/02-icon.png" />
                    <h2><?php _e('Brand taxonomy','yith-woocommerce-brands-add-on');?></h2>
                </div>
                <p>
                    <?php _e( 'With the premium version of the plugin, all taxonomies defined for products (usually categories, tags and attributes) can be turned into brands for products of your shop!','yith-woocommerce-brands-add-on' );?></p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_WCBR_URL ?>assets/images/02.png" />
            </div>
        </div>
    </div>
    <div class="section section-even clear" style="background: url(<?php echo YITH_WCBR_URL ?>assets/images/03-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WCBR_URL ?>assets/images/03.png" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WCBR_URL ?>assets/images/03-icon.png" />
                    <h2><?php _e( 'Position','yith-woocommerce-brands-add-on');?></h2>
                </div>
                <p><?php _e('Position of product brand is no longer static: it will be up to you to choose the most suitable position for brands in product detail page and in shop page.','yith-woocommerce-brands-add-on');?>
                </p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear" style="background: url(<?php echo YITH_WCBR_URL ?>assets/images/04-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WCBR_URL ?>assets/images/04-icon.png" />
                    <h2><?php _e('Content','yith-woocommerce-brands-add-on');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __( 'Name, logo or both? You can decide what information to show to your users just with a click. Whether it is shown in %1$sshop page or in product detail page%2$s, decide the information you want to show about the selected brand.','yith-woocommerce-brands-add-on' ),'<b>','</b>' );?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_WCBR_URL ?>assets/images/04.png" />
            </div>
        </div>
    </div>
    <div class="section section-even clear" style="background: url(<?php echo YITH_WCBR_URL ?>assets/images/05-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WCBR_URL ?>assets/images/05.png" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WCBR_URL?>assets/images/05-icon.png" />
                    <h2><?php _e('5 widgets','yith-woocommerce-brands-add-on');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __('5 different solutions to add and show all your brands in your sidebars! Beautiful and useful, thanks to them, %1$syour brands will become dynamic%2$s and you will be able to further value them using the numerous options availble for each widget. Discover them now!','yith-woocommerce-brands-add-on'),'<b>','</b>' );?></p>
            </div>
        </div>
    </div>
    <div class="section section-odd clear" style="background: url(<?php echo YITH_WCBR_URL ?>assets/images/06-bg.png) no-repeat #f1f1f1; background-position: 15% 100%">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WCBR_URL ?>assets/images/06-icon.png" />
                    <h2><?php _e('8 shortcodes','yith-woocommerce-brands-add-on');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __( '8 new solutions to add brands to your e-commerce pages. %1$sEight different ways to value your brands and brand products:%2$s you can show them either in a slider or as a simple list and users will also be able to filter them and restrict seach results only to those they are interested in.','yith-woocommerce-brands-add-on' ),'<b>','</b>' );?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_WCBR_URL ?>assets/images/06.png" />
            </div>
        </div>
    </div>
    <div class="section section-even clear" style="background: url(<?php echo YITH_WCBR_URL ?>assets/images/07-bg.png) no-repeat #fff; background-position: 85% 100%">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_WCBR_URL ?>assets/images/07.png" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_WCBR_URL?>assets/images/07-icon.png" />
                    <h2><?php _e('Product URL','yith-woocommerce-brands-add-on');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __('Index your products on the web in the best possible way thanks to the possibility to dynamically insert the %1$sbrand name%2$s in the product URL.%1$s Take advantage of this feature!','yith-woocommerce-brands-add-on'),'<b>','</b>' );?></p>
            </div>
        </div>
    </div>
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    <?php echo sprintf( __('Upgrade to %1$spremium version%2$s of %1$sYITH WooCommerce Brands Add-On%2$s to benefit from all features!','yith-woocommerce-brands-add-on'),'<span class="highlight">','</span>' );?>
                </p>
                <a href="<?php echo $this->get_premium_landing_uri() ?>" target="_blank" class="premium-cta-button button btn">
                    <span class="highlight"><?php _e('UPGRADE','yith-woocommerce-brands-add-on');?></span>
                    <span><?php _e('to the premium version','yith-woocommerce-brands-add-on');?></span>
                </a>
            </div>
        </div>
    </div>
</div>