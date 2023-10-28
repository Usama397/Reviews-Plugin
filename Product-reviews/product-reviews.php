<?php
/**
 * Plugin Name:       Product Review
 * Description:       Custom Product Review
 * Version:           1.1.0
 * Text Domain:       product_review
 * @package account-dashboard-pro
 */

defined( 'ABSPATH' ) || exit;

// Check the installation of WooCommerce module if it is not a multi site.

if ( ! is_multisite() ) {
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
		function afpr_check_wocommerce() {
			// Deactivate the plugin.
			deactivate_plugins( __FILE__ );
			?>
			<div id="message" class="error">
				<p>
					<strong> 
						<?php echo esc_html__( 'Product Review plugin is inactive. WooCommerce plugin must be active in order to activate it.', 'product_review'); ?>
					</strong>
				</p>
			</div>;
			<?php
		}
		add_action( 'admin_notices', 'afpr_check_wocommerce' );
	}
}

/**
 * Account Dashboard main Class.
*/
if ( ! class_exists( 'Af_product_review_main' ) ) {

	class Af_product_review_main {

		public function __construct() {

			$this->afpr_define_constant();

			// Create folder to upload review files
			$this->afpr_create_media_dir();


			add_action( 'wp_loaded', array( $this, 'afpr_textdomain' ) );
			add_action( 'wp_head', array( $this, 'afpr_custom_reivew_css') );
			add_filter( 'woocommerce_product_tabs', array( $this, 'my_remove_all_product_tabs' ), 98 );
			add_filter('woocommerce_product_tabs', array( $this, 'custom_review_tab') );

// 			add_action( 'comment_form_top', array( $this, 'render_pros_cons_fields_for_authorized_users' ) );
			add_action('comment_post', array( $this, 'save_custom_review_data') );
			add_action('wp_footer', array($this, 'afpr_review_scripts') );

		}

		public function afpr_define_constant() {
			
			if (!defined('AF_PR_URL')) {
				define('AF_PR_URL', plugin_dir_url(__FILE__));
			}

			if (!defined('AF_PR_PTH')) {
				define('AF_PR_PTH', plugin_dir_path(__FILE__));
			}
		}

		public function afpr_create_media_dir() {
			
			$upload_url = wp_upload_dir();


			if (!is_dir($upload_url['basedir'] . '/product_review_uploads')) {
				mkdir($upload_url['basedir'] . '/product_review_uploads', 0777, true);
			}
		}




		// Register Plugin Text Domain.
		public function afpr_textdomain() {

			if ( function_exists( 'load_plugin_textdomain' ) ) {

				load_plugin_textdomain( 'product_review', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			}
		}
		public function afpr_custom_reivew_css(){ ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
			<style type="text/css">
				
				.reviews_container{
					display: block;
					clear: both;
				}
				.rating-box {
					width: 20%;
					height: 12px;
					border: 1px solid #ded7d7;
				}
				.rating-count{
					font-size: 14px;
					width: 12%;
					margin-left: 10px;
				}
				.afrp-review-form .comment-form-rating .stars a{
					position:relative!important;
					z-index:1;
				}
				.review-product-detail-info .star-rating:before{
/* 					display:none!important;
					content:""; */
				}
				.rating-box.filled{
					background: #fc2561;
				}
				.rating-box-container{
					display: flex;
					width: 85%;
				}
				.value-product .product-rating, .quantity-product .product-rating{
					margin-bottom: 5px;
					font-size: 16px;
					line-height: 24px;
				}
				.rating-box-wrap{
					align-items: center;
				}
				.rating-box-wrap, .review-container{
					display: flex;
					width: 100%;
				}
				.rating-box.empty{
					background: #cccccc;
				}
				.review-container{
					margin-bottom: 20px;
					padding-bottom: 25px;
				}
				.review-admin-info{
					width: 22%;
					display: inline;
				}
				.review-product-detail-info{
					width: 48%;
					padding-right: 20px;
				}
				.product-qnty-value-wrap{
					width: 30%;
				}

				.product-feedback-images{
					display:flex;
					flex-wrap:wrap;
					padding-top: 10px;
					padding-bottom: 10px;
				}

				.product-feedback-images img{
					display: inline-block;
				    width: 17%;
				    margin-right: 15px;
				}

				.review-admin-info h3{
					font-size: 18px;
					line-height: 28px;
					font-weight: 700;
					margin-bottom: 10px;
				}
				.review-product-detail-info img{
					width: 100px;
					height: 100px;
					margin-top: 15px;
				}
				.author-total-review, .review-product-detail-info .review-time,
				.review-product-detail-info .description{
					font-size: 14px;
					line-height: 22px;
					font-weight: 700;
					color: #666666;
					margin-bottom: 0;
				}
				.quantity-product{
					margin-bottom: 10px;
				}
				.review-product-detail-info .review-time{
					margin: 9px 0;
					display: block;
				}
				.review-product-detail-info .star-rating span:before{
					color: #fc2561;
				}
				.review-product-detail-info .star-rating{
/* 						font-size: 14px;
					    line-height: 24px;
					    margin-bottom: 21px; */
					}
					.review-product-detail-info h3{
						font-size: 20px;
						line-height: 30px;
						font-weight: 700;
						margin-bottom: 0;
					}
				#imageGrid{
					display: flex;
					flex-wrap: wrap;
					margin: 26px 0;
				}
				.image-item{
					width: 100px;
					height: 85px;
					margin-right: 21px;
					position: relative;
				}
				.author-total-review{
					margin:8px 0;
				}
				.remove-button{
					background: black;
					color: #fff;
					padding: 0px 6px 4px 5px;
					font-size: 16px;
					line-height: 15px;
					position: absolute;
					right: -3px;
					border-radius: 50%;
					top: -8px;
					cursor: pointer;
				}
					@media screen and (max-width: 600px) {
						.review-container-sec, .review-container-third{
							max-width:100%!important;
						}
						.review-container, .afpr-overall-rating, .review-rating-row{
							display: block!important;
						}
						.product-qnty-value-wrap, .rating-counts, .afpr-overall-total-rating, .afrp-rating-wrap, .overal-quality-raint,
						.overal-value-rating{
							width: 100%!important;
							margin-bottom: 20px;
						}
						.review-overl-image .product-feedback-images img, .review-product-detail-info{
							width:100%!important;
							padding: bottom 15px;;
						}
						
						.product-feedback-images img{
							width:44%!important;
						}
						.review-admin-info, .afpr-recommede-frind, .review-product-detail-info .product-feedback-images{
							display:none!important;
						}
						.popup-close-button{
							border: 2px solid black;
							position: fixed;
							top: 0;
							top: flex;
							right: 0;
							color: white;
							padding: 8px;
						}
						.popup-content{
							width:100%;
							height:100%!important;
						}
						.afpr-acc-tab .afpr-tab-label {
							flex: 0 0 100px!important;
						}
						.afpr-acc-tab h3 {
							width:100%;
							font-size:16px!important;
							line-height:26px!important;
						}
					}
					@media screen and (min-width: 601px) and (max-width: 820px){
						.review-container{
							display: block;
						}
						.product-qnty-value-wrap, .review-product-detail-info, .review-admin-info{
							width: 100%;
							margin-bottom: 20px;
						}
					}
					@media screen and (min-width: 821px) and (max-width: 1020px){
						.rating-box-container{
							width: 80%;
						}
						.rating-count {
							width: 15%;
							margin-left: 9px;
						}
					}
					.review-popup {
						display: none;
						position: fixed;
						top: 0;
						left: 0;
						width: 100%;
						height: 100%;
						background: rgba(0, 0, 0, 0.8);
						z-index: 9999;
					}

					.review-popup .popup-content {
						position: absolute;
						top: 50%;
						left: 50%;
						transform: translate(-50%, -50%);
						background: #fff;
						padding: 20px;
						border-radius: 5px;
						height: 400px!important;
						overflow-y: scroll;
					}
				#custom-review-popup #reviews #review_form{
					border:none!important;
					padding:0!important;
				}
					@media screen and (min-width: 390px) {
/* 						.popup-content { 
							height: 605px !important;
						} */
					}
/* 					@media screen and (min-width: 700px) {
						.popup-content { 
							height: 900px !important;
							width: 1100px ;
						}
					} */
				.review-container-third{
					width:100%;
					max-width:80%;
					margin: 0 auto;
				}
				.review-container-sec{
					    width: 100%;
					max-width: 45%;
					margin: 0 auto;
				}
				.review-rating-row{
					justify-content: space-between;
					display: flex;
				}
					.overal-quality-raint, .overal-value-rating{
						width:46%;
					}
					.overal-quality-raint .product-rating, .overal-value-rating  .product-rating{
						margin-bottom: 10px;
					}
					#review_form .comment-form-rating .stars a:before,
				.first-step #review_form .comment-form-rating .stars a:before{
						display: block;
						position: absolute;
						top: 0;
						left: 0;
						border-radius: 3px;
						width: 54px!important;
						color: #fff!important;
						height: 48px!important;
						line-height: 48px;
						font-family: "Font Awesome 5 Free";
						content: "★";
						font-size: 25px!important;
						color: #43454b;
						opacity: 1;
						text-indent: 0;
					}
					#review_form .comment-form-rating .stars a,
				.first-step #review_form .comment-form-rating .stars a{
						background: #cccccc;
						border: 1px solid rgb(252, 37, 97);
						border-radius: 4px;
						cursor: pointer;
						color: #fff;
						height: 49px!important;
						width: 55px!important;
						text-align: center;
					}
				    
					p.stars.selected a:not(.active):before,
					p.stars.selected a.active:before,
					p.stars:hover a:before, p.stars.selected a:not(.active):before{
						background: rgb(252, 37, 97)!important;
					}
					p.stars.selected a.active~a::before{
						background: #cccccc!important;
					}
					.progress-bar {
						width: 100%;
						border-radius: 10px;
						display: block;
						overflow: hidden;
						width: 60%;
						height: 13px;
						background-color: rgb(204, 204, 204);
						box-shadow: rgb(204, 204, 204) 0px 0px 0px 1px inset;
					}

					.progress {
						background-color: #fc2561;
						height: 100%;
						transition: width 0.3s;
					}
					.afpr-rating-bar{
						display: flex;
						align-items: center;
						justify-content: space-between;
					}
					.afpr-rating-bar p{
						width: 18%;
						margin: 0!important;
					}
					.afpr-overall-rating .star-rating span:before{
						color: #fc2561;
					}
					.afpr-overall-rating{
						width: 100%;
						display: flex;
					}
					.rating-counts{
						flex: 1 1 0%;
					}
					.afpr-overall-total-rating, .afrp-review-form{
						flex: 1 1 0%;
					}
					.afpr-review-section-heaing h2{
						font-size: 24px;
						line-height: 30px;
						font-weight: 700;
						color: #000;
					}
					.rating-counts h3, .afpr-overall-total-rating h3, .afrp-review-form h3, .review-container-sec h3, .review-container-third h3{
						font-size: 20px;
						line-height: 30px;
						font-weight: 700;
						color: #000;
						margin-bottom: 5px;
					}
				.review-container-sec h3, .review-container-third h3{
					text-align: center;
					margin-bottom:20px;
				}
					.aftrp-review-title h3{
						font-size: 24px;
						line-height: 30px;
						font-weight: 600;
						margin-top: 10px;
						margin-bottom: 0;
					}
					.rating-counts p, .aftrp-review-title p,
					.afrp-prdt-review-info p{
						color:#666666;
						margin-bottom: 10px;
						font-weight: 500;
					}
				.first-step p{
					margin-bottom:20px!important;
				}
				.first-step p label, .first-step .comment-form-rating label{
					display:block;
					margin-bottom:5px;
				}
				.first-step .comment-form-rating {
					display:block!important;
				}
				.first-step p input{
					width:100%;
					border: 1px solid #d3d3d3ab!important;
				}
					.afrp-review-form-first-row{
						justify-content: space-between;
						border-bottom: 1px solid #d3d3d396;
						padding-bottom: 20px;
					}
					.afrp-prdt-review-info{
						border-bottom: 1px solid #d3d3d396;
						padding: 15px 0;
					}
					.afrp-rating-wrap, .afrp-review-form-first-row{
						display: flex;
						margin-top: 5px;
					}
					.afrp-review-form-image{
						width: 12%;
					}
					.aftrp-review-title{
						width: 70%;
					}
					.popup-close-button{
						width: 10%;
						display: flex;
						align-items: center;
						justify-content: flex-end;
					}
					.popup-close-button svg{
						cursor: pointer;
					}
					.first-step .comment-form-rating .stars a{
						position:relative!important;
					}
					.afrp-rating-coutn{
						font-size: 40px;
						line-height: 41px;
						font-weight: 600;
						min-width: 50px;
						padding: 0px 18px 5px 0px;
						color: rgb(102, 102, 102);
					}
					.afrp-rating-wrap{
						margin-top:10px;
						align-items: center;
					}
					.afpr-star-review .star-rating{
						margin-bottom: 0!important;
					}
					.afrp-review-total{
						font-size: 14px;
						line-height: 24px;
						font-weight: 800;
					}
					.star-rating .star:before {
						color: #fc2561;
					}
					#review_form_wrapper .comment-form-custom .afpr-review-label input{
						position: absolute;
						width: 100%;
						height: 100%;
						top: 0;
						left: 0;
						opacity: 0;
					}
					#review_form_wrapper .comment-form-custom .afpr-review-label{
						display: inline-block;
						padding: 10px;
						position: relative;
						font-family: "Helvetica Neue LT Std", sans-serif-light, sans-serif;
						font-size: 13px;
						border: 1px solid rgb(102, 102, 102);
						border-radius: 5px;
						margin: 5px 10px 5px 0px;
						background-color: transparent;
						color: rgb(102, 102, 102);
						line-height: 19.5px;
						cursor: pointer;
					}
					#review_form_wrapper .comment-form-custom .afpr-review-label.active{
						background: #666666;
						color: #fff;
					}
					.afpr-review-coupon label,
					.afpr-review-skin-type label, .afpr-review-skin-tone label, .afpr-review-age label,
					.afpr-review-hair-color label, .afpr-review-eye-color label, .afpr-review-gender label,
					.afpr-review-recomend-num label, .afpr-review-recomend label{
						display: block;
						margin-bottom: 10px;
					}
					
					#respond{
						padding: 0!important;
						background: #fff;
					}
					.afpr-acc-tab{
						padding: 20px 0 10px;
						display: flex;
						justify-content: space-between;
						align-items: center; 
					}
					.second-step-accordion, .first-step-accordion, .thid-step-accordion, 
					.fourth-step-accordion{
						border-bottom: 1px solid lightgray;
						padding-bottom: 20px;
					}
					.afpr-acc-tab h3{
						font-size: 20px;
						font-weight: 600;
						line-height: 30px;
						margin-bottom: 0;
						display: flex;
						width: 87%;
						align-items: center;
					}
					#imageGrid .image-item img{
						max-width: 100px;
						height: 100%;
						width: 100%;
						object-fit: cover;
					}
					.afpr-acc-tab h3 .afpr-acc-tab-num{
						border: 1px solid rgb(0, 0, 0);
						border-radius: 50%;
						font-size: 16px;
						display: block;
						line-height: 23px;
						font-weight: 700;
						text-align: center;
						width: 24px;
						flex: 0 0 25px;
						color: rgb(0, 0, 0);
						margin-right: 10px;
					}
					.reviews_container, .review-over-all-quality{
						margin-bottom: 40px;
						border-bottom: 1px solid #d9dcd978;
						padding-bottom: 40px;
					}
					.afpr-acc-tab .afpr-tab-label{
						color: rgb(41, 99, 0);
						font-size: 14px;
						display: block;
						line-height: 24px;
						padding: 7px 11px;
						font-weight: 600;
						border: 2px solid rgb(41, 99, 0);
						border-radius: 4px;
						flex: 0 0 120px;
					}
					#review_form_wrapper button{
						color: rgb(255, 255, 255);
						font-family: "Helvetica Neue LT Std", sans-serif-light, sans-serif;
						font-size: 16px;
						padding: 15px 40px;
						margin-right: 15px;
						line-height: 18px;
						height: 48px;
						font-style: normal;
						font-weight: bold;
						text-transform: uppercase;
						background: rgb(0, 0, 0);
						filter: brightness(1);
					}
					#review_form .stars a {
						display: inline-block;
						font-size: 0;
						margin-inline-end: 4px !important;
					}
					.afpr-recommede-frind{
						display: flex;
						align-items: center;
						margin-top: 10px;
					}
					.afpr-recommede-frind p{
						margin-bottom: 0;
						margin-left: 10px;
					}
				.review-overl-image .product-feedback-images img{
					height:150px!important;
				}
				.owl-nav .owl-prev{
					left: 6px!important;
				}
				.owl-nav .owl-next{
					right: 6px!important;
				}
				.owl-nav{
					display:block!important;
				}
				.owl-carousel .owl-nav button.owl-next, .owl-carousel .owl-nav button.owl-prev{
					font-size: 36px!important;
					line-height: 20px!important;
					background: white!important;
					color: #000!important;
					padding: 7px 12px 14px!important;
					position: absolute!important;
					top: 37%!important;
					border-radius: 2px!important;
				}
				</style>

				<?php
			}
			public function afpr_review_scripts(){
				
				?>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						jQuery('.afrp-review-form .comment-form-rating .stars a').click(function(e) {
							e.preventDefault();
							jQuery('#custom-review-popup').fadeIn();
						});

					    // Close the popup when clicking outside or on a close button
						jQuery('.popup-close-button ').on('click', function(e) {
					        // if (e.target === this || $(e.target).hasClass('popup-close-button')) {
							jQuery('#custom-review-popup').fadeOut();
					        // }
						});
					});
					jQuery(document).ready(function($) {
						var ratingLabels = [
							'Very poor',
							'Not that bad',
							'Average',
							'Good',
							'Perfect'
							];

						$('#rating').change(function() {
							var selectedOption = $('option:selected', this);
							if (selectedOption.val() !== '') {
					            $('.comment-form-rating p.stars').show(); // Show the star rating form
					            $('#rating').css('opacity', 0); // Hide the select box
					        }
					    });

						$('.comment-form-rating p.stars a').click(function() {
							var ratingValue = $(this).text();
							$('#rating').val(ratingValue).change();
							$('#selected-rating-display').text(ratingValue + ' of 5 selected (' + ratingLabels[ratingValue - 1] + ')');
						});

						$('.comment-form-rating p.stars a').click(function() {
							var ratingValue = $(this).text();
							$('#rating').val(ratingValue).change();
							$('#selected-rating-display').text(ratingValue + ' of 5 stars selected (' + ratingLabels[ratingValue - 1] + ')');
							$(this).addClass('active').siblings().removeClass('active');
						});

						    // Handle changes in the select box
						$('#rating').change(function() {
							var selectedOption = $('option:selected', this);
							if (selectedOption.val() !== '') {
						            $('.comment-form-rating p.stars').show(); // Show the star rating form
						            $('#rating').css('opacity', 0); // Hide the select box
						        }
						    });
					});
					// form checkbox
					jQuery(document).ready(function ($) {
						$('#commentform').attr('enctype', 'multipart/form-data');
						$('#commentform').submit(function () {
							if (!$('#terms_and_conditions').is(':checked')) {
								alert('Please agree to the terms and conditions before submitting your review.');
					            return false; // Prevent form submission
					        }
					    });
					});
					jQuery(document).ready(function() {
						jQuery('.afpr-review-coupon input[type="radio"]').change(function() {
							jQuery('.afpr-review-coupon label').removeClass('active');
							if (jQuery(this).is(':checked')) {
								jQuery(this).parent('.afpr-review-coupon label').addClass('active');
							}
						});
						jQuery('.afpr-review-age input[type="radio"]').change(function() {
							jQuery('.afpr-review-age label').removeClass('active');
							if (jQuery(this).is(':checked')) {
								jQuery(this).parent('.afpr-review-age label').addClass('active');
							}
						});
						jQuery('.afpr-review-skin-type input[type="radio"]').change(function() {
							jQuery('.afpr-review-skin-type label').removeClass('active');
							if (jQuery(this).is(':checked')) {
								jQuery(this).parent('.afpr-review-skin-type label').addClass('active');
							}
						});
						jQuery('.afpr-review-skin-tone input[type="radio"]').change(function() {
							jQuery('.afpr-review-skin-tone label').removeClass('active');
							if (jQuery(this).is(':checked')) {
								jQuery(this).parent('.afpr-review-skin-tone label').addClass('active');
							}
						});
						jQuery('.afpr-review-eye-color input[type="radio"]').change(function() {
							jQuery('.afpr-review-eye-color label').removeClass('active');
							if (jQuery(this).is(':checked')) {
								jQuery(this).parent('.afpr-review-eye-color label').addClass('active');
							}
						});
						jQuery('.afpr-review-hair-color input[type="radio"]').change(function() {
							jQuery('.afpr-review-hair-color label').removeClass('active');
							if (jQuery(this).is(':checked')) {
								jQuery(this).parent('.afpr-review-hair-color label').addClass('active');
							}
						});
						jQuery('.afpr-review-gender input[type="radio"]').change(function() {
							jQuery('.afpr-review-gender label').removeClass('active');
							if (jQuery(this).is(':checked')) {
								jQuery(this).parent('.afpr-review-gender label').addClass('active');
							}
						});
						jQuery('.afpr-review-recomend input[type="radio"]').change(function() {
							jQuery('.afpr-review-recomend label').removeClass('active');
							if (jQuery(this).is(':checked')) {
								jQuery(this).parent('.afpr-review-recomend label').addClass('active');
							}
						});
						jQuery('.afpr-review-recomend-num input[type="radio"]').change(function() {
							jQuery('.afpr-review-recomend-num label').removeClass('active');
							if (jQuery(this).is(':checked')) {
								jQuery(this).parent('.afpr-review-recomend-num label').addClass('active');
							}
						});
					});
					jQuery(document).ready(function() {
						var currentStep = 1;
					    var totalSteps = 3; // Set this to the total number of steps

					    jQuery(".next-button").click(function(e) {
					    	e.preventDefault();
					    	if (currentStep < totalSteps) {
					    		jQuery(".first-step").hide();
								jQuery(".first-step-accordion .afpr-tab-label").text("Completed");
					    		currentStep++;
					    		jQuery(".second-step").show();
					    	}
					    });

					    jQuery(".af-third-next-button").click(function(e) {
					    	e.preventDefault();
					    	if (currentStep > 1) {
					    		jQuery(".second-step").hide();
								jQuery(".second-step-accordion .afpr-tab-label").text("Completed");
					    		currentStep--;
					    		jQuery(".thid-step").show();
					    	}
					    });
					    jQuery(".af-prev-button").click(function(e) {
					    	e.preventDefault();
					        // if (currentStep > 1) {
					    	jQuery(".thid-step").hide();
							jQuery(".thid-step-accordion .afpr-tab-label").text("Completed");
					            // currentStep--;
					    	jQuery(".four-step").show();
					        // }
					    });
					    jQuery(".four-next-button").click(function(e) {
					    	e.preventDefault();
					        // if (currentStep > 1) {
					    	jQuery(".four-step").hide();
							jQuery(".fourth-step-accordion .afpr-tab-label").text("Completed");
					            // currentStep--;
					    	jQuery(".five-step").show();
					        // }
					    });
					});
					jQuery(document).ready(function($) {
						const imageGrid = $('#imageGrid');
						const imageInput = $('#afpr_image_gallery');
					    const maxImages = 6; // Maximum allowed images

					    // Function to add a new image item to the grid
					    function addImageToGrid(imageUrl) {
					    	const imageItem = $('<div class="image-item"></div>');
					    	const img = $('<img src="' + imageUrl + '" style="max-width: 100px;" />');
					    	const removeButton = $('<i class="remove-button">x</i>');
					    	imageItem.append(img);
					    	imageItem.append(removeButton);

					        // Event listener to remove the image item
					    	removeButton.on('click', function() {
					    		imageItem.remove();
					    		checkImageCount();
					    	});

					    	imageGrid.append(imageItem);
					    	checkImageCount();
					    }

					    // Function to check and limit the number of images
					    function checkImageCount() {
					    	const imageCount = imageGrid.find('.image-item').length;

					    	if (imageCount >= maxImages) {
					            imageInput.prop('disabled', true); // Disable the input
					        } else {
					        	imageInput.prop('disabled', false);
					        }
					    }

					    // Event listener for image selection
					    imageInput.on('change', function() {
					    	const files = this.files;
					    	for (const file of files) {
					    		if (imageGrid.find('.image-item').length >= maxImages) {
					    			alert('You can only upload a maximum of 6 images.');
					    			return;
					    		}
					    		const imageUrl = URL.createObjectURL(file);
					    		addImageToGrid(imageUrl);
					    	}
					    });
					});
					jQuery(document).ready(function () {
				        const select = jQuery('#value-rating'); // Target the select element with id "value-rating"
				        const starRatingDisplay = jQuery('#selected-rating-display');

				        // Function to update the visual star rating
				        function updateStarRating() {
				        	const selectedValue = select.val();
				        	starRatingDisplay.html('★'.repeat(selectedValue));
				        }

				        // Add an event listener to the select dropdown
				        select.change(updateStarRating);

				        // Initial update
				        updateStarRating();
				    });
					jQuery(document).ready(function() {
    
					// Home Banner Slider
					jQuery('.review-overl-image .product-feedback-images').owlCarousel({
							loop:true,
							nav: true,
							dots: false,
							items:7,
							margin: 15,
							border:0,
							padding:0,
							autoplay:false,   
							smartSpeed: 1000, 
							autoplayTimeout:5000,
							autoplayHoverPause:true,
							responsive: {

								  0:{
									  items:2,
								  },
								  576:{
									  items:2,
								  },
								 768:{
									  items:4,
									  nav: true,
								  },
								  992:{
									  items:7,
									  nav: true,
								  }
							}
					});
						
					});

				</script>
				<?php
			}
			public function my_remove_all_product_tabs( $tabs ) {
				unset( $tabs['reviews'] );
				return $tabs;;
			}
			public function custom_review_tab(){
				$tabs['custom_reviews'] = array(
					'title'     => __('Reviews', 'your-text-domain'),
					'priority'  => 1,
					'callback'  => array($this, 'afpr_custom_reviews_tab'),
				);

				return $tabs;
			}
			public function afpr_custom_reviews_tab() { ?>
				<section class="reviews_container">
					<div class="afpr-review-main-container">
						<div class="afpr-review-section-heaing">
							<h2><?php esc_html_e('RATINGS & REVIEWS', 'product_review'); ?></h2>
						</div>
						<div class="afpr-overall-rating">
							<?php
							$product_id = get_the_ID();
							$reviews = get_comments(array(
								'post_id' => $product_id,
								'status' => 'approve',
							));

							$rating_counts = array(
								'5' => 0,
								'4' => 0,
								'3' => 0,
								'2' => 0,
								'1' => 0,
							);

							foreach ($reviews as $review) {
								$rating = get_comment_meta($review->comment_ID, 'rating', true);
								if (isset($rating_counts[$rating])) {
									$rating_counts[$rating]++;
								}
							}

							echo '<div class="rating-counts">';
							echo '<h3>' . esc_html__('Rating Snapshot', 'product_review') . '</h3>';
							echo '<p class="short-description">' . esc_html__('Select a row below to filter reviews.', 'product_review') . '</p>';
							foreach ($rating_counts as $rating_value => $count) {
								echo '<div class="afpr-rating-bar">';
								echo '<p>' . $rating_value . ' stars</p>';
								echo '<div class="progress-bar">';
								if (count($reviews) > 0) {
									echo '<div class="progress" style="width: ' . ($count / count($reviews) * 100) . '%;"></div>';
								} else {
								        // Handle the case where there are no reviews
									echo '<div class="progress" style="width: 0%;"></div>';
								}

								echo '</div>';
								echo '<p>' . $count . '</p>';
								echo '</div>';
							}
							echo '</div>';

							?>
							<div class="afpr-overall-total-rating">
								<h3><?php echo esc_html__('Overall Rating', 'product_review'); ?></h3>
								<?php
								global $product;
								// $args = array(
								// 	'post_type' => 'product',
								// 	'posts_per_page' => -1,
								// );

								// $products = new WP_Query($args);

								// $total_rating = 0;
								// $total_products = 0;

								// if ($products->have_posts()) {
								// 	while ($products->have_posts()) {
								// 		$products->the_post();
								// 		$product = wc_get_product(get_the_ID());

								// 		if ($product) {
								// 			$rating = $product->get_average_rating();
								// 			$total_rating += $rating;
								// 			$total_products++;
								// 		}
								// 	}
								// 	wp_reset_postdata();
								// }

// var_dump($product->get_rating_counts());


								if ($product->get_review_count() > 0) {
									// $average_rating = $total_rating / $total_products;

									echo '<div class="afrp-rating-wrap"><div class="afrp-rating-coutn">';
									echo round($product->get_average_rating(), 2);
									echo '</div><div class="afpr-star-review">';
									echo wc_get_rating_html($product->get_average_rating());
									
									echo '<div class="afrp-review-total">' . $product->get_review_count() . ' Reviews</div>';
									echo '</div></div>';
								} else {
									echo 'No products found.';
								}

								?>
							</div>
							<div id="review_form" class="afrp-review-form">
								<h3><?php echo esc_html__('Review this Product', 'product_review'); ?></h3>
								<div class="comment-form-rating">
									<p class="stars">
										<span>
											<a class="star-1" href="#">1</a>
											<a class="star-2" href="#">2</a>
											<a class="star-3" href="#">3</a>
											<a class="star-4" href="#">4</a>
											<a class="star-5" href="#">5</a>
										</span>

									</p>
								</div>
							</div>
						</div>
					</div>
				</section>
                <section class="review-over-all-quality">
					<div class="review-container-sec">
						<h3>
							AVERAGE CUSTOMER RATINGS
						</h3>
						<div class="review-rating-row">
							<div class="overal-quality-raint">
								<?php
									   global $product;
									$product_id = $product->get_id();
									$reviews = get_comments(array(
										'post_id' => $product_id,
										'meta_key' => 'quality-rating', // Replace with your custom field name
									));

									$total_ratings = 0;
									$average_rating = 0;

									foreach ($reviews as $review) {
										$rating = get_comment_meta($review->comment_ID, 'quality-rating', true);

										if ($rating) {
											$total_ratings += intval($rating);
										}
									}

									if (count($reviews) > 0) {
										$average_rating = $total_ratings / count($reviews);
									}
								   
									if ($average_rating) {
											?>
											<p class="product-rating">Quality of Product:</p>
											<div class="rating-box-wrap">
												<div class="rating-box-container">
													<?php
													$max_rating = 5;

													for ($i = 1; $i <= $max_rating; $i++) {
														$is_filled = $i <= round( $average_rating );
														$class = $is_filled ? 'filled' : 'empty';
														?>
														<div class="rating-box <?php echo $class; ?>"></div>
														<?php
													}
													?>
												</div>
												<div class="rating-count"><?php echo $average_rating; ?>.00</div>
											</div>
											<?php
										} else {
											echo 'Quality of Product Rating not available';
										} ?>
							</div>
							<div class="overal-value-rating">
								<?php
								 global $product;
									$product_id = $product->get_id();
									$reviews = get_comments(array(
										'post_id' => $product_id,
										'meta_key' => 'value-rating', // Replace with your custom field name
									));

									$total_ratings = 0;
									$average_rating = 0;

									foreach ($reviews as $review) {
										$rating = get_comment_meta($review->comment_ID, 'value-rating', true);

										if ($rating) {
											$total_ratings += intval($rating);
										}
									}

									if (count($reviews) > 0) {
										$average_rating = $total_ratings / count($reviews);
									}
								   
									if ($average_rating) {
											?>
											<p class="product-rating">Value of Product:</p>
											<div class="rating-box-wrap">
												<div class="rating-box-container">
													<?php
													$max_rating = 5;

													for ($i = 1; $i <= $max_rating; $i++) {
														$is_filled = $i <= round( $average_rating );
														$class = $is_filled ? 'filled' : 'empty';
														?>
														<div class="rating-box <?php echo $class; ?>"></div>
														<?php
													}
													?>
												</div>
												<div class="rating-count"><?php echo $average_rating; ?>.00</div>
											</div>
											<?php
										} else {
											echo 'Value of Product Rating not available';
										} ?>
							</div>
						</div>
					</div>
                </section>
<section class="review-over-all-quality">
					<div class="review-container-third">
						<h3>
							CUSTOMER IMAGES
						</h3>
						<div class="review-overl-image">
								<?php
									   global $product;
									$product_id = $product->get_id();
									$reviews = get_comments(array(
										'post_id' => $product_id,
										'meta_key' => 'afpr_image_gallery', // Replace with your custom field name
										 'meta_query' => array(
											'key' => 'afpr_image_gallery',
											'compare' => 'EXISTS',
										  ),
									));

									$gallery_images = array();

									foreach ($reviews as $review) {
										$gallery = get_comment_meta($review->comment_ID, 'afpr_image_gallery', true); // Replace with your custom field name

										if ($gallery) {
											// Convert the gallery data to an array if it's not already
											$gallery_data = is_array($gallery) ? $gallery : explode(',', $gallery);

											// Add the images to the gallery
											$gallery_images = array_merge($gallery_images, $gallery_data);
										}
									}

									// Display the gallery images
									if (!empty($gallery_images)) {
										$upload_url = wp_upload_dir();
											?>

											<div class="product-feedback-images owl-carousel">
												<?php foreach ($gallery_images as $key) {
													$current_file = '';

													// Construct the full file path
													$file = $upload_url['basedir'] . '/product_review_uploads/' . $key;

													if (file_exists($file)) {
														$current_file = esc_url($upload_url['baseurl'] . '/product_review_uploads/' . $key);

														// Display the image
														echo "<img src='" . esc_url($current_file) . "'>";
													}
												} ?>
											</div><?php
									}
							?>
						</div>
					</div>
                </section>
				<section class="reviews_container-popup">
					<!-- review form -->
					<div id="custom-review-popup" class="review-popup">
						<div class="popup-content">
							<div id="reviews" class="woocommerce-Reviews">
								<!-- <div id="comments"> -->

									<!-- <div id="reviews" class="woocommerce-Reviews"> -->
										<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
										<section id="step-1">
											<div id="review_form_wrapper">
												<div id="review_form">
													<?php
													$commenter    = wp_get_current_commenter();
													$comment_form = array(
														'title_reply'         => have_comments() ? esc_html__( 'Add a review', 'woocommerce' ) : sprintf( esc_html__( '', 'woocommerce' ) ),
														'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
														'comment_notes_after' => '',
														'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
														'logged_in_as'        => '',
														'comment_field'       => '',
													);

													$name_email_required = (bool) get_option( 'require_name_email', 1 );
													$fields              = array(
														'author' => array(
															'label'    => __( 'Name', 'woocommerce' ),
															'type'     => 'text',
															'value'    => $commenter['comment_author'],
															'required' => $name_email_required,
														),
														'email'  => array(
															'label'    => __( 'Email', 'woocommerce' ),
															'type'     => 'email',
															'value'    => $commenter['comment_author_email'],
															'required' => $name_email_required,
														),

													);

													$comment_form['fields'] = array();
													foreach ( $fields as $key => $field ) {
														$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
														$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

														if ( $field['required'] ) {
															$field_html .= '&nbsp;<span class="required">*</span>';
														}

														$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

														$comment_form['fields'][ $key ] = $field_html;
													}

													$account_page_url = wc_get_page_permalink( 'myaccount' );
													if ( $account_page_url ) {
														/* translators: %s opening and closing link tags respectively */
														$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
													}
													   $product_id = get_the_ID();
										$product_title = get_the_title($product_id);

										$product_image = get_the_post_thumbnail($product_id);

										$review_count = get_comments_number($product_id); ?>
										<div class="afrp-review-form-first-row">
											<div class="afrp-review-form-image">
												<?php echo $product_image; ?>
											</div>
											<div class="aftrp-review-title">
												<?php
												echo '<p>My Review ' . esc_html($review_count) . '</p>';
												echo '<h3>' . $product_title . '</h3>';
												?>
											</div>
											<div class="popup-close-button">
												<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21" aria-hidden="true"><path d="M2.03849 0.769828L10.5 8.89086L18.9616 0.547721C19.262 0.25152 19.9743 0.473626 20.2747 0.769828C20.5751 1.06603 20.5751 1.76844 20.2747 2.06464L12.0385 10.4078L20.2747 18.751C20.5751 19.0472 20.5751 19.7496 20.2747 20.0458C19.9743 20.342 19.262 20.5642 18.9616 20.268L10.5 11.9247L2.03849 20.268C1.73808 20.5642 1.02573 20.342 0.72533 20.0458C0.424927 19.7496 0.424927 18.7989 0.72533 18.5027L8.96156 10.4078L0.725275 2.06457C0.424872 1.76837 0.424927 1.06603 0.72533 0.769828C1.02573 0.473626 1.73808 0.473626 2.03849 0.769828Z" fill="#747474"></path></svg>
											</div>
										</div>
										<div class="afrp-prdt-review-info">
											<p>Thank you for your interest in posting a review of this product through our review tool powered by Bazaarvoice. We’d like to publish your review, so be sure to comply the Review Guidelines.</p>
											<p>Required fields are marked with *</p>
										</div> 
										<?php
													$comment_form['comment_field'] = '<div class="first-step-accordion">
													<div class="afpr-acc-tab"><h3><span class="afpr-acc-tab-num">1</span>YOUR REVIEWS</h3><span class="afpr-tab-label">In Progress</span></div>';
													$comment_form['comment_field'] .= '<div class="first-step">';

													if (wc_review_ratings_enabled()) {
														$comment_form['comment_field'] .= '<div class="comment-form-rating"><label for="rating">' . esc_html__('Your rating', 'woocommerce') . (wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '') . '</label><select name="rating" id="rating" required>
														<option value="">' . esc_html__('Rate&hellip;', 'woocommerce') . '</option>
														<option value="5">' . esc_html__('Perfect', 'woocommerce') . '</option>
														<option value="4">' . esc_html__('Good', 'woocommerce') . '</option>
														<option value="3">' . esc_html__('Average', 'woocommerce') . '</option>
														<option value="2">' . esc_html__('Not that bad', 'woocommerce') . '</option>
														<option value="1">' . esc_html__('Very poor', 'woocommerce') . '</option>
														</select></div><div id="selected-rating-display"></div>';
													}

													$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__('Your review', 'woocommerce') . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

													$comment_form['comment_field'] .= '<p class="aftrp_review_title"><label for="aftrp_review_title">Review Title*</label><input type="text" id="aftrp_review_title" name="aftrp_review_title" class="form-row-wide" /></p>';

													$comment_form['comment_field'] .= '<p class="aftrp_nick_name"><label for="aftrp_nick_name">Nick Name*</label><input type="text" id="aftrp_nick_name" name="aftrp_nick_name" class="form-row-wide" /></p>';

													$comment_form['comment_field'] .= '<p class="comment-form-custom"><label for="afpr_email">Email*</label><input type="text" id="afpr_email" name="afpr_email" class="form-row-wide" /></p>';

													$comment_form['comment_field'] .= '<p class="comment-form-custom afpr-review-coupon"><label for="afpr_review_counpon_check">I received a sample, loyalty point, coupon, or a contest entry for this review.</label>';
													$comment_form['comment_field'] .= '<label for="afpr_review_counpon_check_yes" class="afpr-review-label"><input type="radio" name="afpr_review_counpon_check" id="afpr_review_counpon_check_yes" value="Yes" />Yes</label>';
													$comment_form['comment_field'] .= '<label for="afpr_review_counpon_check_no" class="afpr-review-label"><input type="radio" name="afpr_review_counpon_check" id="afpr_review_counpon_check_no" value="No" />No</label>';
													$comment_form['comment_field'] .= '</p>';

													$comment_form['comment_field'] .= '<p class="comment-form-custom afpr-review-age"><label for="afpr_review_age">Age</label>';
													$comment_form['comment_field'] .= '<label for="afpr_review_age_0_17orUnder" class="afpr-review-label"><input type="radio" name="afpr_review_age" id="afpr_review_age_0_17orUnder" value="0_17orUnder" />17 or Under</label>';
													$comment_form['comment_field'] .= '<label for="afpr_review_age_18_24" class="afpr-review-label"><input type="radio" name="afpr_review_age" id="afpr_review_age_18_24" value="18-24" />18 to 24</label>';
													$comment_form['comment_field'] .= '<label for="afpr_review_age_25_34" class="afpr-review-label"><input type="radio" name="afpr_review_age" id="afpr_review_age_25_34" value="25-34" />25 to 34</label>';
													$comment_form['comment_field'] .= '<label for="afpr_review_age_35_44" class="afpr-review-label"><input type="radio" name="afpr_review_age" id="afpr_review_age_35_44" value="35-44" />35 to 44</label>';
													$comment_form['comment_field'] .= '<label for="afpr_review_age_45_54" class="afpr-review-label"><input type="radio" name="afpr_review_age" id="afpr_review_age_45_54" value="45-54" />45 to 54</label>';
													$comment_form['comment_field'] .= '<label for="afpr_review_age_55_64" class="afpr-review-label"><input type="radio" name="afpr_review_age" id="afpr_review_age_55_64" value="55-64" />55 to 64</label>';
													$comment_form['comment_field'] .= '<label for="afpr_review_age_65_over" class="afpr-review-label"><input type="radio" name="afpr_review_age" id="afpr_review_age_65_over" value="65-over" />65 or over</label>';
													$comment_form['comment_field'] .= '</p>';

													$comment_form['comment_field'] .= '
													<p class="afpr-before-submit">You may receive emails regarding this submission. Any emails will include the ability to opt-out of future communications.</p>';
													$comment_form['comment_field'] .= '<button class="next-button">Next</button>';
													$comment_form['comment_field'] .= '</div></div>';

													$comment_form['comment_field'] .= '<div class="second-step-accordion">
													<div class="afpr-acc-tab"><h3><span class="afpr-acc-tab-num">2</span>ADD IMAGE</h3><span class="afpr-tab-label">In Progress</span></div>';

													$comment_form['comment_field'] .= '<div class="second-step" style="display: none;">';
													$comment_form['comment_field'] .= '<p class="afpr-image-gallery"><label for="afpr_image_gallery">Image Gallery</label>';
													$comment_form['comment_field'] .= '<input type="file" name="afpr_image_gallery[]" id="afpr_image_gallery" multiple />';
													$comment_form['comment_field'] .= '<div id="imageGrid" class="image-grid"></div></p>';
													$comment_form['comment_field'] .= '<button class="af-third-next-button">Submit</button>';
													$comment_form['comment_field'] .= '<button class="af-third-next-button">Skip</button>';
													$comment_form['comment_field'] .= '</div></div>';

													$comment_form['comment_field'] .= '<div class="thid-step-accordion">
													<div class="afpr-acc-tab"><h3><span class="afpr-acc-tab-num">3</span>PERSONAL/PRODUCT INFORMATION</h3><span class="afpr-tab-label">In Progress</span></div>';

													$comment_form['comment_field'] .= '<div class="thid-step" style="display: none;">';
													$comment_form['comment_field'] .= '<p class="comment-form-custom afpr-review-skin-type"><label for="afpr_review_skin_type">Skin Type:</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_type_normal" class="afpr-review-label"><input type="radio" name="afpr_skin_type" id="afpr_skin_type_normal" value="Normal" />Normal</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_type_dry" class="afpr-review-label"><input type="radio" name="afpr_skin_type" id="afpr_skin_type_dry" value="Dry" />Dry</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_type_oily" class="afpr-review-label"><input type="radio" name="afpr_skin_type" id="afpr_skin_type_oily" value="Oily" />Oily</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_type_combination" class="afpr-review-label"><input type="radio" name="afpr_skin_type" id="afpr_skin_type_combination" value="Combination" />Combination</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_type_sensitive" class="afpr-review-label"><input type="radio" name="afpr_skin_type" id="afpr_skin_type_sensitive" value="Sensitive" />Sensitive</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_type_blemish_acne-prone" class="afpr-review-label"><input type="radio" name="afpr_skin_type" id="afpr_skin_type_blemish_acne-prone" value="Blemish/acne prone" />Blemish/acne prone</label>';
													$comment_form['comment_field'] .= '</p>';

													$comment_form['comment_field'] .= '<p class="comment-form-custom afpr-review-skin-tone"><label for="afpr_review_skin_tone">Skin Tone:</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_tone_light" class="afpr-review-label"><input type="radio" name="afpr_skin_tone" id="afpr_skin_tone_light" value="Light" />Light</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_tone_light_medium" class="afpr-review-label"><input type="radio" name="afpr_skin_tone" id="afpr_skin_tone_light_medium" value="Light Medium" />Light Medium</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_tone_medium" class="afpr-review-label"><input type="radio" name="afpr_skin_tone" id="afpr_skin_tone_medium" value="Medium" />Medium</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_tone_deep_medium" class="afpr-review-label"><input type="radio" name="afpr_skin_tone" id="afpr_skin_tone_deep_medium" value="Deep Medium" />Deep Medium</label>';
													$comment_form['comment_field'] .= '<label for="afpr_skin_tone_deep" class="afpr-review-label"><input type="radio" name="afpr_skin_tone" id="afpr_skin_tone_deep" value="Deep" />Deep</label>';
													$comment_form['comment_field'] .= '</p>';

													$comment_form['comment_field'] .= '<p class="comment-form-custom afpr-review-eye-color"><label for="afpr_review_eye_color">Eye Color:</label>';
													$comment_form['comment_field'] .= '<label for="afpr_eye_color_blue" class="afpr-review-label"><input type="radio" name="afpr_eye_color" id="afpr_eye_color_blue" value="Blue" />Blue</label>';
													$comment_form['comment_field'] .= '<label for="afpr_eye_color_green" class="afpr-review-label"><input type="radio" name="afpr_eye_color" id="afpr_eye_color_green" value="Green" />Green</label>';
													$comment_form['comment_field'] .= '<label for="afpr_eye_color_hazel" class="afpr-review-label"><input type="radio" name="afpr_eye_color" id="afpr_eye_color_hazel" value="Hazel" />Hazel</label>';
													$comment_form['comment_field'] .= '<label for="afpr_eye_color_brown" class="afpr-review-label"><input type="radio" name="afpr_eye_color" id="afpr_eye_color_brown" value="Brown" />Brown</label>';
													$comment_form['comment_field'] .= '</p>';

													$comment_form['comment_field'] .= '<p class="comment-form-custom afpr-review-hair-color"><label for="afpr_review_hair_color">Hair Color:</label>';
													$comment_form['comment_field'] .= '<label for="afpr_hair_color_blonde" class="afpr-review-label"><input type="radio" name="afpr_hair_color" id="afpr_hair_color_blonde" value="Blonde" />Blonde</label>';
													$comment_form['comment_field'] .= '<label for="afpr_hair_color_dark_brown" class="afpr-review-label"><input type="radio" name="afpr_hair_color" id="afpr_hair_color_dark_brown" value="Dark Brown" />Dark Brown</label>';
													$comment_form['comment_field'] .= '<label for="afpr_hair_color_red_anburn" class="afpr-review-label"><input type="radio" name="afpr_hair_color" id="afpr_hair_color_red_anburn" value="Red/Auburn" />Red/Auburn</label>';
													$comment_form['comment_field'] .= '<label for="afpr_hair_color_light_brown" class="afpr-review-label"><input type="radio" name="afpr_hair_color" id="afpr_hair_color_light_brown" value="Light Brown" />Light Brown</label>';
													$comment_form['comment_field'] .= '<label for="afpr_hair_color_black" class="afpr-review-label"><input type="radio" name="afpr_hair_color" id="afpr_hair_color_black" value="Black" />Black</label>';
													$comment_form['comment_field'] .= '<label for="afpr_hair_color_grey" class="afpr-review-label"><input type="radio" name="afpr_hair_color" id="afpr_hair_color_grey" value="Grey" />Grey</label>';
													$comment_form['comment_field'] .= '<label for="afpr_hair_color_white" class="afpr-review-label"><input type="radio" name="afpr_hair_color" id="afpr_hair_color_white" value="White" />White</label>';
													$comment_form['comment_field'] .= '</p>';

													$comment_form['comment_field'] .= '<p class="comment-form-custom afpr-review-gender"><label for="afpr_review_gender">Gender:</label>';
													$comment_form['comment_field'] .= '<label for="afpr_gender_Male" class="afpr-review-label"><input type="radio" name="afpr_gender" id="afpr_gender_male" value="Male" />Male</label>';
													$comment_form['comment_field'] .= '<label for="afpr_gender_female" class="afpr-review-label"><input type="radio" name="afpr_gender" id="afpr_gender_female" value="Female" />Female</label>';
													$comment_form['comment_field'] .= '</p>';

													$comment_form['comment_field'] .= '<button class="af-prev-button">Submit</button>';
													$comment_form['comment_field'] .= '<button class="af-prev-button">Skip</button>';
													$comment_form['comment_field'] .= '</div></div>';

													$comment_form['comment_field'] .= '<div class="fourth-step-accordion">
													<div class="afpr-acc-tab"><h3><span class="afpr-acc-tab-num">4</span>PRODUCT RATING</h3><span class="afpr-tab-label">In Progress</span></div>';

													$comment_form['comment_field'] .= '<div class="four-step" style="display: none;">';
													$comment_form['comment_field'] .= '<p class="comment-form-custom afpr-review-recomend"><label for="afpr_review_recomed_friend">Would you recommend this product to a friend?</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_frnd_yes" class="afpr-review-label"><input type="radio" name="afpr_recomed_frnd" id="afpr_recomed_frnd_yes" value="yes" />Yes</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_frnd_no" class="afpr-review-label"><input type="radio" name="afpr_recomed_frnd" id="afpr_recomed_frnd_no" value="no" />No</label>';
													$comment_form['comment_field'] .= '</p>';

													$comment_form['comment_field'] .= '<div class="comment-form-rating"><label for="value-rating">' . esc_html__('How would you rate the value of this product?', 'woocommerce') . '<span class="required">*</span</label>';
													$comment_form['comment_field'] .= '<select name="value-rating" id="value-rating" required>
													<option value="">' . esc_html__('Rate&hellip;', 'woocommerce') . '</option>
													<option value="5">' . esc_html__('5 stars', 'woocommerce') . '</option>
													<option value="4">' . esc_html__('4 stars', 'woocommerce') . '</option>
													<option value="3">' . esc_html__('3 stars', 'woocommerce') . '</option>
													<option value="2">' . esc_html__('2 stars', 'woocommerce') . '</option>
													<option value="1">' . esc_html__('1 star', 'woocommerce') . '</option>
													</select></div><div id="selected-rating-display"></div>';

													$comment_form['comment_field'] .= '<p class="comment-form-rating"><label for="quality-rating">' . esc_html__('How would you rate the Quality of this product?', 'woocommerce') . '<span class="required">*</span</label>';
													$comment_form['comment_field'] .= '<select name="quality-rating" id="quality-rating" required>
													<option value="">' . esc_html__('Rate&hellip;', 'woocommerce') . '</option>
													<option value="5">' . esc_html__('5 stars', 'woocommerce') . '</option>
													<option value="4">' . esc_html__('4 stars', 'woocommerce') . '</option>
													<option value="3">' . esc_html__('3 stars', 'woocommerce') . '</option>
													<option value="2">' . esc_html__('2 stars', 'woocommerce') . '</option>
													<option value="1">' . esc_html__('1 star', 'woocommerce') . '</option>
													</select></p>';
													$comment_form['comment_field'] .= '<button class="four-next-button">Submit</button>';
													$comment_form['comment_field'] .= '<button class="four-next-button">Skip</button>';
													$comment_form['comment_field'] .= '</div></div>';

													$comment_form['comment_field'] .= '<div class="five-step-accordion">
													<div class="afpr-acc-tab"><h3><span class="afpr-acc-tab-num">5</span>BRAND DETAILS</h3><span class="afpr-tab-label">In Progress</span></div>';

													$comment_form['comment_field'] .= '<div class="five-step" style="display: none;">';
													$comment_form['comment_field'] .= '<p class="comment-form-custom afpr-review-recomend-num"><label for="afpr_review_recomed_friend_number">Would you recommend Maybelline to a friend?</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_frnd_0" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_0" value="0" />0</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_1" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_1" value="1" />1</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_2" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_2" value="2" />2</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_3" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_3" value="3" />3</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_4" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_4" value="4" />4</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_5" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_5" value="5" />5</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_6" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_6" value="6" />6</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_7" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_7" value="7" />7</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_8" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_8" value="8" />8</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_9" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_9" value="9" />9</label>';
													$comment_form['comment_field'] .= '<label for="afpr_recomed_number_10" class="afpr-review-label"><input type="radio" name="afpr_recomed_number" id="afpr_recomed_number_10" value="10" />10</label>';
													$comment_form['comment_field'] .= '</p>';
													$comment_form['comment_field'] .= '<p class="comment-form-terms"><label for="terms_and_conditions"><input type="checkbox" id="terms_and_conditions" name="terms_and_conditions" required> ' . esc_html__('By posting a review you agree to the User Content Permission Terms and information about you will be collected and used subject to our Terms of Use, Privacy Policy, and CA Policy.', 'product_review') . '<span class="required">*</span></label></p>';

													$comment_form['comment_field'] .= '</div></div>';

													comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
													?>
												</div>
											</div>
										</section>

									<?php else : ?>
										<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
									<?php endif; ?>
									<div class="clear"></div>
									<?php
							    // wc_get_template('single-product-reviews.php'); ?>
							</div>
						</div>
					</div>
					<!-- 				</div> -->

					<?php
					$product_id = get_the_ID(); 
					$reviews = get_comments(array(
						'post_id' => $product_id,
						'status' => 'approve',
						'type' => 'review',
					));

					if (!empty($reviews)) {
						foreach ($reviews as $review) {
							$author_name = get_comment_author($review);
							$review_time = strtotime($review->comment_date);
							$formatted_time = human_time_diff($review_time, current_time('timestamp')) . ' ago';
							$total_reviews_by_author = count(get_comments(array(
								'user_id' => $review->user_id,
								'status' => 'approve',
							)));
							$author_image = get_avatar($review->user_id, 64);
							$review_description = $review->comment_content;
							$product_name = get_the_title($product_id);
							$average_rating = wc_get_product($product_id)->get_average_rating();
							$star_rating = get_comment_meta($review->comment_ID, 'rating', true);
							$custom_field_title = get_comment_meta($review->comment_ID, 'aftrp_review_title', true);
							$custom_field_nick_name = get_comment_meta($review->comment_ID, 'aftrp_nick_name', true);
							$custom_field_third = get_comment_meta($review->comment_ID, 'afpr_email', true);
							$custom_field_fourth = get_comment_meta($review->comment_ID, 'afpr_review_counpon_check', true);
							$custom_field_age = get_comment_meta($review->comment_ID, 'afpr_review_age', true);
							$custom_field_six = get_comment_meta($review->comment_ID, 'terms_and_conditions', true);
							$custom_field_skin_type = get_comment_meta($review->comment_ID, 'afpr_skin_type', true);
							$custom_field_skin_tone = get_comment_meta($review->comment_ID, 'afpr_skin_tone', true);
							$custom_field_eye = get_comment_meta($review->comment_ID, 'afpr_eye_color', true);
							$custom_field_hair = get_comment_meta($review->comment_ID, 'afpr_hair_color', true);
							$custom_field_gender = get_comment_meta($review->comment_ID, 'afpr_gender', true);
							$custom_field_quality_rating = get_comment_meta($review->comment_ID, 'quality-rating', true);
							$custom_field_value = get_comment_meta($review->comment_ID, 'value-rating', true);
							$custom_field_recomd = get_comment_meta($review->comment_ID, 'afpr_recomed_number', true);
							$custom_field_gallery = get_comment_meta($review->comment_ID, 'afpr_image_gallery', true);
							$custom_field_ron = get_comment_meta($review->comment_ID, 'afpr_recomed_frnd', true);
							?>
							<div class="review-container">
								<div class="review-admin-info">
									<h3><?php echo esc_html__( $author_name ); ?></h3>
									<div class="author-total-review">
										<span>Review:</span>
										<span><?php echo esc_html__( $total_reviews_by_author ); ?></span>
									</div>
									<?php
									if ( $custom_field_age ){ ?>
									<div class="author-total-review">
										<span>Age:</span>
										<span><?php echo esc_html__( $custom_field_age ); ?></span>
									</div>
									<?php
									}
									if ( $custom_field_skin_type ){ ?>
									<div class="author-total-review">
										<span>Skin Type:</span>
										<span><?php echo esc_html__( $custom_field_skin_type ); ?></span>
									</div>
									<?php
									}
									if ( $custom_field_skin_tone ){ ?>
									<div class="author-total-review">
										<span>Skin Tone</span>
										<span><?php echo esc_html__( $custom_field_skin_tone ); ?></span>
									</div>
									<?php
									}
									if ($custom_field_eye){ ?>
									<div class="author-total-review">
										<span>Eye Color:</span>
										<span><?php echo esc_html__( $custom_field_eye ); ?></span>
									</div>
									<?php
									}
							        if ($custom_field_hair){
									?>
									<div class="author-total-review">
										<span>Hair Color:</span>
										<span><?php echo esc_html__( $custom_field_hair ); ?></span>
									</div>
									<?php
									}
							        if ( $custom_field_gender ) {
									?>
									<div class="author-total-review">
										<span>Gender:</span>
										<span><?php echo esc_html__( $custom_field_gender ); ?></span>
									</div>
									<?php
									}
									?>
								</div>	
								<div class="review-product-detail-info">
									<div class="af-star-rating">
										<?php echo wc_get_rating_html($star_rating);
// 						              echo wc_get_rating_html($product_id);
										?>
									</div>
									<h3 class= "h-afr-star-rating">
										<?php
										if ( $custom_field_title ){
										    echo esc_html__( $custom_field_title );
										}else{
											echo esc_html__( $product_name );
										}
										?>
									</h3>
									<?php
										if ( $custom_field_nick_name ){ ?>
									<div class="af-nick-name">
										<h3>
											<?php echo esc_html__( $custom_field_nick_name ); ?>
										</h3>
									</div>
									<?php
									 }
									?>
									<span class="review-time"><?php echo esc_html__( $formatted_time ); ?></span>
									<p class="description"><?php echo esc_html__( $review_description ); ?></p>
									<?php if ( $custom_field_ron === 'yes') { ?>
									<div class="afpr-recommede-frind">
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 30 30" aria-hidden="true" class="bv-rnr__sc-nifwaj-0 cnQDBn"><g fill="none" fill-rule="evenodd" stroke="none" stroke-width="1"><g transform="translate(-39 -539)"><g transform="translate(39 539)"><path d="M0 0H30V30H0z"></path><g transform="translate(1 1)"><circle cx="14" cy="14" r="14" fill="#000"></circle><path fill="#FFF" stroke="#FFF" stroke-width="0.5" d="M11.0909091 17.4925373L7.27272727 13.7313433 6 14.9850746 11.0909091 20 22 9.25373134 20.7272727 8z"></path></g></g></g></g></svg>
										<p>Yes, I recommend this product.</p>
									</div>
								<?php } ?>
									<?php if (!empty($custom_field_gallery)) {
									
										$upload_url = wp_upload_dir();
										?> <div class="product-feedback-images"> <?php
										foreach ($custom_field_gallery as $key) {

											$current_file = '';

											$file = $upload_url['basedir'] . '/product_review_uploads/' . $key;

											if (file_exists($file)) {

												$current_file = esc_url($upload_url['baseurl'] . '/product_review_uploads/' . $key);

											}

											echo "<img src='" . esc_url($current_file) . "'>";
										}

										?>

									</div>
									<?php
								}?>
								</div>
								<div class="product-qnty-value-wrap">
									<div class="quantity-product">
										<?php
										if ($custom_field_quality_rating) {
											?>
											<p class="product-rating">Quality of Product:</p>
											<div class="rating-box-wrap">
												<div class="rating-box-container">
													<?php
													$max_rating = 5;

													for ($i = 1; $i <= $max_rating; $i++) {
														$is_filled = $i <= round( $custom_field_quality_rating );
														$class = $is_filled ? 'filled' : 'empty';
														?>
														<div class="rating-box <?php echo $class; ?>"></div>
														<?php
													}
													?>
												</div>
												<div class="rating-count"><?php echo $custom_field_quality_rating; ?>.00</div>
											</div>
											<?php
										} else {
											echo 'Quality of Product Rating not available';
										}

										?>
									</div>
									<div class="value-product">
										<?php
										if ($custom_field_value) {
											?>
											<p class="product-rating">Value of Product:</p>
											<div class="rating-box-wrap">
												<div class="rating-box-container">
													<?php
													$max_rating = 5;

													for ($i = 1; $i <= $max_rating; $i++) {
														$is_filled = $i <= round( $custom_field_value );
														$class = $is_filled ? 'filled' : 'empty';
														?>
														<div class="rating-box <?php echo $class; ?>"></div>
														<?php
													}
													?>
												</div>
												<div class="rating-count"><?php echo $custom_field_value; ?>.00</div>
											</div>
											<?php
										} else {
											echo 'Value of Product Rating not available';
										}
										?>
									</div>
								</div>

						</div>
						<?php
					}
				} else {
					echo 'No reviews found for this product.';
				}
				?>
			</section>
		<?php }

		public function get_pros_cons_fields_html() {
			ob_start();
			?>
			<?php
			$product_id = get_the_ID();
			$product_title = get_the_title($product_id);

			$product_image = get_the_post_thumbnail($product_id);

			$review_count = get_comments_number($product_id); ?>
			<div class="afrp-review-form-first-row">
				<div class="afrp-review-form-image">
					<?php echo $product_image; ?>
				</div>
				<div class="aftrp-review-title">
					<?php
					echo '<p>My Review ' . esc_html($review_count) . '</p>';
					echo '<h3>' . $product_title . '</h3>';
					?>
				</div>
				<div class="popup-close-button">
					<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21" aria-hidden="true"><path d="M2.03849 0.769828L10.5 8.89086L18.9616 0.547721C19.262 0.25152 19.9743 0.473626 20.2747 0.769828C20.5751 1.06603 20.5751 1.76844 20.2747 2.06464L12.0385 10.4078L20.2747 18.751C20.5751 19.0472 20.5751 19.7496 20.2747 20.0458C19.9743 20.342 19.262 20.5642 18.9616 20.268L10.5 11.9247L2.03849 20.268C1.73808 20.5642 1.02573 20.342 0.72533 20.0458C0.424927 19.7496 0.424927 18.7989 0.72533 18.5027L8.96156 10.4078L0.725275 2.06457C0.424872 1.76837 0.424927 1.06603 0.72533 0.769828C1.02573 0.473626 1.73808 0.473626 2.03849 0.769828Z" fill="#747474"></path></svg>
				</div>
			</div>
			<div class="afrp-prdt-review-info">
				<p>Thank you for your interest in posting a review of this product through our review tool powered by Bazaarvoice. We’d like to publish your review, so be sure to comply the Review Guidelines.</p>
				<p>Required fields are marked with *</p>
			</div>
			<div class="">
			</div>
			<?php
			return ob_get_clean();
		}

		public function render_pros_cons_fields_for_authorized_users() {
			if ( ! is_product() || ! is_user_logged_in() ) {
				return;
			}

			echo $this->get_pros_cons_fields_html();
		}

		public function render_pros_cons_fields_for_anonymous_users( $defaults ) {
			if ( ! is_product() || is_user_logged_in() ) {
				return;
			}

			$defaults['comment_notes_before'] .= $this->get_pros_cons_fields_html();

			return $defaults;
		}

		public function save_custom_review_data($comment_ID) {
			if (isset($_POST['aftrp_review_title'])) {
				$afpr_review_title = sanitize_text_field($_POST['aftrp_review_title']);
				add_comment_meta($comment_ID, 'aftrp_review_title', $afpr_review_title );
			}
			if (isset($_POST['aftrp_nick_name'])) {
				$afpr_nick_name = sanitize_text_field($_POST['aftrp_nick_name']);
				add_comment_meta($comment_ID, 'aftrp_nick_name', $afpr_nick_name);
			}
			if (isset($_POST['afpr_email'])) {
				$afpr_email = sanitize_text_field($_POST['afpr_email']);
				add_comment_meta($comment_ID, 'afpr_email', $afpr_email);
			}
			if (isset($_POST['afpr_review_counpon_check'])) {
				$afpr_review_check = sanitize_text_field($_POST['afpr_review_counpon_check']);
				add_comment_meta( $comment_ID, 'afpr_review_counpon_check', $afpr_review_check );
			}
			if (isset($_POST['afpr_review_age'])) {
				$afpr_review_age = sanitize_text_field($_POST['afpr_review_age']);
				add_comment_meta($comment_ID, 'afpr_review_age', $afpr_review_age);
			}
			if (isset($_POST['afpr_skin_type'])) {
				$afpr_skin_type = sanitize_text_field($_POST['afpr_skin_type']);
				add_comment_meta($comment_ID, 'afpr_skin_type', $afpr_skin_type);
			}
			if (isset($_POST['afpr_skin_tone'])) {
				$afpr_skin_tone = sanitize_text_field($_POST['afpr_skin_tone']);
				add_comment_meta($comment_ID, 'afpr_skin_tone', $afpr_skin_tone);
			}
			if (isset($_POST['afpr_eye_color'])) {
				$afpr_eye_color = sanitize_text_field($_POST['afpr_eye_color']);
				add_comment_meta($comment_ID, 'afpr_eye_color', $afpr_eye_color);
			}
			if (isset($_POST['afpr_hair_color'])) {
				$afpr_hair_color = sanitize_text_field($_POST['afpr_hair_color']);
				add_comment_meta($comment_ID, 'afpr_hair_color', $afpr_hair_color);
			}
			if (isset($_POST['afpr_gender'])) {
				$afpr_gender = sanitize_text_field($_POST['afpr_gender']);
				add_comment_meta($comment_ID, 'afpr_gender', $afpr_gender);
			}
			if (isset($_POST['quality-rating'])) {
				$quality_rating = intval($_POST['quality-rating']);
				add_comment_meta($comment_ID, 'quality-rating', $quality_rating);
			}
			if (isset($_POST['value-rating'])) {
				$value_rating = intval($_POST['value-rating']);
				add_comment_meta($comment_ID, 'value-rating', $value_rating);
			}
			if (isset($_POST['terms_and_conditions'])) {
				$terms_and_conditions = sanitize_text_field($_POST['terms_and_conditions']);
				add_comment_meta($comment_ID, 'terms_and_conditions', $terms_and_conditions);
			}
			if (isset($_POST['afpr_recomed_number'])) {
				$afpr_recomed_number = intval($_POST['afpr_recomed_number']);
				add_comment_meta($comment_ID, 'afpr_recomed_number', $afpr_recomed_number);
			}
			if (isset($_POST['afpr_recomed_frnd'])) {
		        $quality_rating = sanitize_text_field($_POST['afpr_recomed_frnd']);
		        add_comment_meta($comment_ID, 'afpr_recomed_frnd', $quality_rating);
		    }
			if (isset($_FILES['afpr_image_gallery'])) {

				$upload_url = wp_upload_dir();

				$file_names = $_FILES["afpr_image_gallery"]["name"];
				$tmp_name = $_FILES["afpr_image_gallery"]["tmp_name"];

				$i = 0;
				foreach ($file_names as $name) {

					$file        = time() . sanitize_text_field($name);
					$target_path = $upload_url['basedir'] . '/product_review_uploads/';
					$target_path = $target_path . $file;


					if (isset($tmp_name[$i])) {

						$temp = move_uploaded_file(sanitize_text_field($tmp_name[$i]), $target_path);
					} else {

						$temp = '';
					}

					// overwrite the file name
					$file_names[$i] = $file;

					$i++;

				}
				add_comment_meta($comment_ID, 'afpr_image_gallery', $file_names);

			}
		}
	}

	new Af_product_review_main();

}