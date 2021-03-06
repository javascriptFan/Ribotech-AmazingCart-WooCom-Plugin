<?php
/*
Written By : Kruk8989
Website : http://codecanyon.net/user/kruk8989

Created on May,2013

Last Updated 5/2/2014

Version 2.0
*/

class amazingcart_woo_json_api{
	
	/*
	===============================
	PUBLIC METHOD
	===============================
	*/
	/**
	 * Class constructor
	 */
	public function __construct()
	{
			
			add_action('template_redirect', array($this,'amazingcart_template_redirect'), 1);
			

	}
	
	/**
	 * Redirect in an error case
	 */
	public function amazingcart_template_redirect()
	{
        
		error_reporting(0);
        if($_GET['amazingcart']=="json-api")
		{
			
			$this->compile();
			die();
		}
		
		
		
	 }
	
	/**
	 * Get countries
	 */ 
	public function getWCCountries()
	{
		
		$wc_countries = new WC_Countries();
		
		$countries = array();
		foreach($wc_countries->countries as $key => $value)
		{
			array_push($countries,array(
									"code"=>$key,
									"country"=>$value,
									"states"=>$this->getStatesByCountry($key)	
									));
		}
		
		return $countries;
	}
	
	/**
	 * Get each product
	 */
	public function get_single_product($postID)
 	{
		 
		 $product = get_post($postID); 
		 
		 return array($this->jsonSetup($product));
	 }
	
	/**
	 * Get each product category
	 */
	public function get_product_categories($parent = 0)
 	{
	  
	  
	  
	  global $wp_query;
    // get the query object
    $cat = $wp_query->get_queried_object();
    // get the thumbnail id user the term_id
   
	
	
	 	 $taxonomies = array( 
    					'product_cat'
						);
	  
		$args = array(
					  'orderby' => 'name',
					  'parent'         => $parent
					  );
  		$se = array();
		$categories = get_terms( $taxonomies, $args );
		foreach ( $categories as $category ) {
			
			 $thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true ); 
    
    $image = wp_get_attachment_url( $thumbnail_id );
			
			if($image == false)
			{
				$imgurl = "0";	
			}
			else
			{
				$imgurl = $image;
			}
			
			array_push($se,array
							(
							"term_id"=>$category->term_id,
							"thumb"=>$imgurl,
							"name"=>$category->name,
							"slug"=>$category->slug,
							"category_parent"=>$category->parent,
							"post_count"=>$category->count,
							"children"=>$this->get_product_categories($category->term_id)
							));
		}  
		
		return $se;
  	}
	
	/**
	 * Each product in any category
	 */
	public function getProductsByCategoryID($categoryID,$current_page=1,$post_per_page = 10)
	{
		
		$array = array();
		
		$term = get_term( $categoryID, "product_cat" );
	
		
		$args = array( 'post_type' => 'product', 'product_cat' => $term->slug,'posts_per_page' =>$post_per_page,'paged'=>$current_page,'meta_query' => array(
								array(
								'key' => '_visibility',
								'value' => array('visible','catalog')
								)
						    ));
		
$loop = new WP_Query( $args );

while ( $loop->have_posts() ) : $loop->the_post();


	      array_push($array,$this->jsonSetup($loop->post));

endwhile;
		
		
		
		
		
		
		//Count All Post
		$argss = array( 'post_type' => 'product', 'product_cat' => $term->slug,'posts_per_page' =>-1,'meta_query' => array(
								array(
								'key' => '_visibility',
								'value' => array('visible','catalog')
								)
						    ));
		$loops = new WP_Query( $argss );
		
		$count = 0;
		while ( $loops->have_posts() ) : $loops->the_post();

	$count++;
	    

endwhile;
		$totalPage = ceil($count/$post_per_page);
		
		return array(
					"categoryID"=>(int)$categoryID,
					"categoryName"=>$term->name,
					"categorySlug"=>$term->slug,
					"current_page"=>(int)$current_page,
					"total_page"=>(int)$totalPage,
					"post_per_page"=>(int)$post_per_page,
					"total_post"=>(int)$count,
					"products"=>$array
					);
	}
	
	/**
	 * Each featured product
	 */
	public function getFeaturedProducts()
	{
		
		$array = array();
		
		
	$args = array( 
		'post_type' => 'product',
		'posts_per_page' =>-1,
		'meta_query' => array(
							array(
							'key' => '_visibility',
							'value' => array('visible','catalog')
							),
							array(
							'key' => '_featured',
							'value' => "yes"
							)
						));
		
$loop = new WP_Query( $args );
$count = 0;
while ( $loop->have_posts() ) : $loop->the_post();

	      array_push($array,$this->jsonSetup($loop->post));
		  $count++;
	
endwhile;
		
		
		
		
		return array(
					"total_post"=>(int)$count,
					"products"=>$array
					);
	}
	
	/**
	 * Each random product
	 */
	public function getRandomProduct($current_page=1,$post_per_page = 10,$order = "DESC")
	{
		
		$array = array();
		
	    $args = array( 
		'post_type' => 'product',
		'posts_per_page' =>$post_per_page,
		'paged'=>$current_page,
		'order' => $order,
		'orderby' => 'rand',
		'meta_query' => array(
							array(
							'key' => '_visibility',
							'value' => array('visible','catalog')
							)
						));
		
$loop = new WP_Query( $args );

while ( $loop->have_posts() ) : $loop->the_post();


	      array_push($array,$this->jsonSetup($loop->post));
		 
endwhile;
		
		
		
		$args = array( 
				'post_type' => 'product',
				'posts_per_page' =>-1,
				'meta_query' => array(
									array(
									'key' => '_visibility',
									'value' => array('visible','catalog')
									)
									));
		
$loop = new WP_Query( $args );
$count = 0;
while ( $loop->have_posts() ) : $loop->the_post();

	     
		  $count++;
	
endwhile;
		
		$totalPage = ceil($count/$post_per_page);
		
		return array(
					"current_page"=>(int)$current_page,
					"total_page"=>(int)$totalPage,
					"post_per_page"=>(int)$post_per_page,
					"total_post"=>(int)$count,
					"products"=>$array
					);
	}
	
	/**
	 * Get all products in any category
	 */
	public function getAllProducts($current_page=1,$post_per_page = 10,$order = "DESC")
	{
		
		$array = array();
		
	    $args = array( 
		'post_type' => 'product',
		'posts_per_page' =>$post_per_page,
		'paged'=>$current_page,
		'order' => $order,
		'meta_query' => array(
							array(
							'key' => '_visibility',
							'value' => array('visible','catalog')
							)
						));
		
$loop = new WP_Query( $args );

while ( $loop->have_posts() ) : $loop->the_post();


	      array_push($array,$this->jsonSetup($loop->post));
		 
endwhile;
		
		
		
		$args = array( 
				'post_type' => 'product',
				'posts_per_page' =>-1,
				'meta_query' => array(
									array(
									'key' => '_visibility',
									'value' => array('visible','catalog')
									)
									));
		
$loop = new WP_Query( $args );
$count = 0;
while ( $loop->have_posts() ) : $loop->the_post();

	     
		  $count++;
	
endwhile;
		
		$totalPage = ceil($count/$post_per_page);
		
		return array(
					"current_page"=>(int)$current_page,
					"total_page"=>(int)$totalPage,
					"post_per_page"=>(int)$post_per_page,
					"total_post"=>(int)$count,
					"products"=>$array
					);
	}
	
	/**
	 * Seacrh any product(-s)
	 */
	public function getProductsByKeyWord($keyword,$current_page=1,$post_per_page = 10)
	{
		
		$array = array();
		
		
	
		
		$args = array( 
			'post_type' => 'product', 
			's' => $keyword,
			'posts_per_page' =>$post_per_page,
			'paged'=>$current_page,
			'meta_query' => array(
								array(
								'key' => '_visibility',
								'value' => array('visible','catalog')
								)
						    ));
		
$loop = new WP_Query( $args );

while ( $loop->have_posts() ) : $loop->the_post();


	      array_push($array,$this->jsonSetup($loop->post));
endwhile;
		
		
		
		
		
		
		//Count All Post
		$argss = array( 'post_type' => 'product', 's' => $keyword,'posts_per_page' =>-1,'meta_query' => array(
								array(
								'key' => '_visibility',
								'value' => array('visible','catalog')
								)
						    ));
		$loops = new WP_Query( $argss );
		
		$count = 0;
		while ( $loops->have_posts() ) : $loops->the_post();
			if(get_post_meta($loops->post->ID, '_visibility',true ) == "visible" || get_post_meta($loops->post->ID, '_visibility',true ) == "catalog" || get_post_meta($loop->post->ID, '_visibility',true ) == "search"){
	$count++;
	    
			}
endwhile;
		$totalPage = ceil($count/$post_per_page);
		
		return array(
					"keyword"=>$keyword,
					"current_page"=>(int)$current_page,
					"total_page"=>(int)$totalPage,
					"post_per_page"=>(int)$post_per_page,
					"total_post"=>(int)$count,
					"products"=>$array
					);
	}
	
	/**
	 * Get order list
	 */
	public function getMyOrder($userlogin, $password, $filter="All")
    {
 
        $user = $this->user_login_bool($userlogin, $password);

        if ($user == false) {
            return array("status" => 1, "reason" => "Not Authorized");
        } else {
            switch($filter){
                case "":
                    $filter="All";
                    break;
                case "On Hold":
                    $filter="wc-on-hold";
                    break;
                case "Processing":
                    $filter="wc-processing";
                    break;
                case "On Hold":
                    $filter="wc-on-hold";
                    break;
                case "Pending Payment":
                    $filter="wc-pending";
                    break;
                case "Completed":
                    $filter="wc-completed";
                    break;
                case "Cancelled":
                    $filter="wc-cancelled";
                    break;
                case "Refunded":
                    $filter="wc-refunded";
                    break;
                case "Failed":
                    $filter="wc-failed";
                    break;
            }
            $array = array();
            $count = 0;
            global $woocommerce;
            $args = array(
                'status' => $filter,
                'post_type' => 'shop_order',
                'posts_per_page' => -1,
                'orderby' => 'ID',
                'order' => 'DESC',
                //'post_author' => $user->ID
                );
            $loop = get_posts($args);
            wp_reset_query();
            foreach($loop as $post){
                if($post->post_author == $user->ID){
                    //return $post;
                    if($filter=="All" || $filter==$post->post_status)
                    {
                        $order = new WC_Order($post->ID);
                        $sde = $order->get_items();
                        $itemArray=array();
                        foreach ($sde as $key => $items) {

                            if (wp_get_attachment_url(get_post_meta($items['product_id'], '_thumbnail_id', true)) == false) {
                                $featured_image = "0";
                            } else {
                                $featured_image = wp_get_attachment_url(get_post_meta($items['product_id'], '_thumbnail_id', true));
                            }
                            if (wp_get_attachment_url(get_post_meta($items['variation_id'], '_thumbnail_id', true)) == false) {
                                $featured_image_variation = $featured_image;
                            } else {
                                $featured_image_variation = wp_get_attachment_url(get_post_meta($items['variation_id'], '_thumbnail_id', true));
                            }

                            array_push($itemArray, array(
                                "product_id" => $items['product_id'],
                                "product_info" => array(
                                    "featuredImages" => $featured_image,
                                    "productName" => get_the_title($items['product_id']),
                                ),
                                "variation_id" => $items['variation_id'],
                                "variation_info" => array(
                                    "featuredImages" => $featured_image_variation,
                                    "productName" => get_the_title($items['product_id']),
                                ),
                                "quantity" => $items['qty'],
                                "product_price" => ($items['item_meta']['_line_total'][0] + $items['item_meta']['_line_tax'][0]) / $items['qty'],
                                "product_price_ex_tax" => ($items['item_meta']['_line_total'][0]) / $items['qty'],
                                "total_price" => $items['item_meta']['_line_total'][0] + $items['item_meta']['_line_tax'][0],
                                "total_price_ex_tax" => $items['item_meta']['_line_total'][0])
                            );

                            $totalSubPriceWithTax = $totalSubPriceWithTax + $items['item_meta']['_line_total'][0] + $items['item_meta']['_line_tax'][0];
                            $totalSubPrice = $totalSubPrice + $items['item_meta']['_line_total'][0];
                        }
                        foreach ($woocommerce->payment_gateways->get_available_payment_gateways() as $key => $value) {
                            if ($order->payment_method == $key) {
                                $paymentDesc = $value->settings['description'];
                            }
                        }

                        $res = array(
                            "orderID" => $order->id,
                            "order_key" => $order->order_key,
                            "display-price-during-cart-checkout" => get_option('woocommerce_tax_display_cart'),
                            "orderDate" => $order->order_date,//date("d-m-Y", $unixtimeModified),//$date,//("d-m-Y", $unixtimeCreated),
                            "paymentDate" => $order->order_date,//("d/m/Y", $unixtimeModified),
                            "status" => $order->status,
                            "currency" => get_woocommerce_currency_symbol(),
                            "billing_email" => $order->billing_email,
                            "billing_phone" => $order->billing_phone,
                            "billing_address" => str_replace("<br/>", "\n", $order->get_formatted_billing_address()),
                            "shipping_address" => str_replace("<br/>", "\n", $order->get_formatted_shipping_address()),
                            "items" => $itemArray,
                            "used_coupon" => $order->get_used_coupons(),
                            "subtotalWithTax" => $totalSubPriceWithTax,
                            "subtotalExTax" => $totalSubPrice,
                            "shipping_method" => $order->shipping_method_title,
                            "shipping_cost"=>$order->calculate_shipping(),
                            "shipping_tax"=>$order->get_shipping_tax(),
                            "tax_total"=>round($order->order_tax,2)+$order->get_shipping_tax(),
                            "discount_total"=>$order->get_total_discount(),
                            "order_total"=>$order->get_total(),
                            "order_note"=>$order->customer_note,
                            "payment_method_id"=>$order->payment_method,
                            "payment_method_title"=>$order->payment_method_title,
                            "payment_desc"=>$paymentDesc,
                            "order_notes" => $this->getOrderNotes($order->id)
                        );
                        array_push($array, $res);//$this->getSingleOrder($userlogin,$password,$post->ID));
                        $count++;
                    }
                }
            }
            return array
            (
                "total_post" => (int)$count,
                "filter" => $filter,
                "my_order" => $array
            );
        }
    }
	
	/**
	 * Change own password
	 */
	public function change_password($userlogin,$currentpassword,$newpassword)
	{
		$user = $this->user_login_bool($userlogin,$currentpassword);
		
		
		if($user == false)
			{
				return array("status"=>1,"reason"=>"Not Authorized");
			}
			else
			{
				
				wp_set_password($newpassword, $user->ID);
				return array("status"=>0,"reason"=>"Password Updated");
				
			}
		
	}
	
	/**
	 * Check if login exists
	 */
	public function update_is_login($is_login,$deviceID)
	{ 
	
			global $wpdb;
			
		
			 $qry = "UPDATE `" . $wpdb->prefix . "amazingcart_user` SET is_login='".$is_login."' WHERE deviceID='".$deviceID."';";
 			 $wpdb->query($qry);
			
		
		
	}
	
	/**
	 * Check user
	 */
	public function user_login($userlogin,$userPassword,$deviceID)
	{
		
			$creds = array();
			
			if($this->check_email_address($userlogin) == true)
			{
				$user = get_user_by( 'email', $userlogin );
				$username =$user->user_login;	
			}
			else
			{
				$username = $userlogin;
			}
			
			
			$creds['user_login'] = $username;
			$creds['user_password'] = $userPassword;
			$creds['remember'] = false;
			$user = wp_signon( $creds, false );
			if ( is_wp_error($user) )
			{
			  $array = array("status"=>1,"reason"=>"Username/email/password is wrong");
			}
			else
			{
				$this->updateUseridInUserTable($user->ID,$deviceID);
				$this->update_is_login("1",$deviceID);
				if($this->stateCodeToNameConverter($user->billing_country,$user->billing_state) == false)
				{
					
					$state = $user->billing_state;
					$stateAvailable = false;
					$stateCode = "";
					
				}
				else
				{
					$state = $this->stateCodeToNameConverter($user->billing_country,$user->billing_state);
					$stateAvailable = true;
					$stateCode = $user->billing_state;
				}
				
				
				if($this->stateCodeToNameConverter($user->shipping_country,$user->shipping_state) == false)
				{
					
					$state_shipping = $user->shipping_state;
					$stateShippingAvailable = false;
					$statShippingeCode = "";
					
				}
				else
				{
					$state_shipping = $this->stateCodeToNameConverter($user->shipping_country,$user->shipping_state);
					$stateShippingAvailable = true;
					$statShippingeCode = $user->shipping_state;
				}
				
				
				$array = array(
						"status"=>0,
						"reason"=>"Successful Log",
						"user"=>array(
									"ID"=>$user->ID,
									"user_login"=>$user->user_login,
									"avatar"=>$this->wp_user_avatar_extension($user->ID),
									"first_name"=>$user->first_name,
									"last_name"=>$user->last_name,
									"email"=>$user->user_email,
									"user_nicename"=>$user->user_nicename,
									"user_nickname"=>$user->user_nicename,
									"user_status"=>$user->user_status,
									"order_count"=>$user->_order_count,
									"credit_card_management_aut_net"=>$this->get_aut_payment_profiles_fix($user->ID),
									"user_registered"=>array(
															"db_format"=>$user->user_registered,
															"unixtime"=>strtotime( $user->user_registered ),
															"servertime"=>(int)date("U"),
															"ago"=>$this->time_elapsed_string(strtotime( $user->user_registered )),
															),
									"billing_address"=>array(
															"billing_first_name"=>$user->billing_first_name,
															"billing_last_name"=>$user->billing_last_name,
															"billing_company"=>$user->billing_company,
															"billing_address_1"=>$user->billing_address_1,
															"billing_address_2"=>$user->billing_address_2,
															"billing_city"=>$user->billing_city,
															"billing_postcode"=>$user->billing_postcode,
															"billing_state"=>$state,
															"billing_state_code"=>$stateCode,
															"billing_has_state"=>$stateAvailable,
															"billing_country"=>$this->countryCodeToNameConverter($user->billing_country),
															"billing_country_code"=>$user->billing_country,
															"billing_phone"=>$user->billing_phone,
															"billing_email"=>$user->billing_email
															),
									"shipping_address"=>array(
															"shipping_first_name"=>$user->shipping_first_name,
															"shipping_last_name"=>$user->shipping_last_name,
															"shipping_company"=>$user->shipping_company,
															"shipping_address_1"=>$user->shipping_address_1,
															"shipping_address_2"=>$user->shipping_address_2,
															"shipping_city"=>$user->shipping_city,
															"shipping_postcode"=>$user->shipping_postcode,
															"shipping_state"=>$state_shipping,
															"shipping_state_code"=>$statShippingeCode,
															"shipping_has_state"=>$stateShippingAvailable,
															"shipping_country"=>$this->countryCodeToNameConverter($user->shipping_country),
															"shipping_country_code"=>$user->shipping_country
															)
								)
						);
			}
			
			
			
			return $array;
	}
	
	private function get_aut_payment_profiles_fix( $user_id )
	{

		$ary = array();
		foreach($this->get_aut_payment_profiles($user_id) as $key=>$value)
		{
			array_push($ary,
			array(
				"profile_id"=>$key,
				"active"=>$value['active'],
				"exp_date"=>$value['exp_date'],
				"last_four"=>$value['last_four'],
				"type"=>$value['type']
				
			));
		}
		
		return $ary;
	}
	
	private function get_aut_payment_profiles( $user_id )
	{

		$payment_profiles = get_user_meta( $user_id, '_wc_authorize_net_cim_payment_profiles', true );

		return is_array( $payment_profiles ) ? $payment_profiles : array();
	}
	
	//@Public User Registration API 
	//return status = 0 Successfull
	//return status = 1 Username Exist
	//return status = 2 Email Exsist
	public function userRegistration($username,$email,$first_name,$last_name,$password,$deviceID)
	{
		
	   if ( username_exists( $username ) )
	   {
           
		   return array("status"=>1,"reason"=>"Username Exist (".$username.")");
	   }
       else
	   {
		  
		  
		  if( email_exists( $email )) {
			  
			    return array("status"=>2,"reason"=>"Email Exist (".$email.")");
      
   			}
			else
			{
				
				 $user_data = array(
                'ID' => '',
                'user_pass' => wp_generate_password(),
                'user_login' => $username,
                'display_name' => $first_name." ".$last_name,
                'user_email' => $email,
                'role' => get_option('default_role') // Use default role or another role, e.g. 'editor'
            );
            $user_id = wp_insert_user( $user_data );
              wp_set_password($password, $user_id);
			  
			 update_user_meta($user_id, 'first_name', $first_name); 
			 update_user_meta($user_id, 'last_name', $last_name);
			 
			  update_user_meta($user_id, 'billing_first_name', $first_name); 
			 update_user_meta($user_id, 'billing_last_name', $last_name);
			 
			 update_user_meta($user_id, 'nickname', $first_name." ".$last_name);
			  
			$user = get_user_by( 'id', $user_id );
			  
			      if($this->stateCodeToNameConverter($user->billing_country,$user->billing_state) == false)
				{
					
					$state = $user->billing_state;
					$stateAvailable = false;
					$stateCode = "";
					
				}
				else
				{
					$state = $this->stateCodeToNameConverter($user->billing_country,$user->billing_state);
					$stateAvailable = true;
					$stateCode = $user->billing_state;
				}
				
				
				if($this->stateCodeToNameConverter($user->shipping_country,$user->shipping_state) == false)
				{
					
					$state_shipping = $user->shipping_state;
					$stateShippingAvailable = false;
					$statShippingeCode = "";
					
				}
				else
				{
					$state_shipping = $this->stateCodeToNameConverter($user->shipping_country,$user->shipping_state);
					$stateShippingAvailable = true;
					$statShippingeCode = $user->shipping_state;
				}
				$this->updateUseridInUserTable($user->ID,$deviceID);
				$this->update_is_login("1",$deviceID);
				
				$array = array(
						"status"=>0,
						"reason"=>"Successful Registered",
						"user"=>array(
									"ID"=>$user->ID,
									"user_login"=>$user->user_login,
									"avatar"=>$this->wp_user_avatar_extension($user->ID),
									"first_name"=>$user->first_name,
									"last_name"=>$user->last_name,
									"email"=>$user->user_email,
									"user_nicename"=>$user->user_nicename,
									"user_nickname"=>$user->user_nicename,
									"user_status"=>$user->user_status,
									"order_count"=>$user->_order_count,
									"credit_card_management_aut_net"=>$this->get_aut_payment_profiles_fix($user->ID),
									"user_registered"=>array(
															"db_format"=>$user->user_registered,
															"unixtime"=>strtotime( $user->user_registered ),
															"servertime"=>(int)date("U"),
															"ago"=>$this->time_elapsed_string(strtotime( $user->user_registered )),
															),
									"billing_address"=>array(
															"billing_first_name"=>$user->billing_first_name,
															"billing_last_name"=>$user->billing_last_name,
															"billing_company"=>$user->billing_company,
															"billing_address_1"=>$user->billing_address_1,
															"billing_address_2"=>$user->billing_address_2,
															"billing_city"=>$user->billing_city,
															"billing_postcode"=>$user->billing_postcode,
															"billing_state"=>$state,
															"billing_state_code"=>$stateCode,
															"billing_has_state"=>$stateAvailable,
															"billing_country"=>$this->countryCodeToNameConverter($user->billing_country),
															"billing_country_code"=>$user->billing_country,
															"billing_phone"=>$user->billing_phone,
															"billing_email"=>$user->billing_email
															),
									"shipping_address"=>array(
															"shipping_first_name"=>$user->shipping_first_name,
															"shipping_last_name"=>$user->shipping_last_name,
															"shipping_company"=>$user->shipping_company,
															"shipping_address_1"=>$user->shipping_address_1,
															"shipping_address_2"=>$user->shipping_address_2,
															"shipping_city"=>$user->shipping_city,
															"shipping_postcode"=>$user->shipping_postcode,
															"shipping_state"=>$state_shipping,
															"shipping_state_code"=>$statShippingeCode,
															"shipping_has_state"=>$stateShippingAvailable,
															"shipping_country"=>$this->countryCodeToNameConverter($user->shipping_country),
															"shipping_country_code"=>$user->shipping_country
															)
								)
						);
			
			return $array;
			
			}
		  
          
				
	   }
	}
	
	//@Public wordpress update profile API
	// Always Require User to Login To Request an Update
	
	public function update_profile($userlogin,$userPassword,$arg)
	{
		
				$user = $this->user_login_bool($userlogin,$userPassword);
				
			if($user == false)
			{
				
				return array("status"=>1,"reason"=>"Not Authorized");
			}
			else
			{
				$userID = $user->ID;
				
				
				if($arg['email'] !== $user->user_email)
				{
					if( email_exists( $arg['email'] )) {
			  
			  				update_user_meta($userID, 'first_name', $arg['first_name']);
							update_user_meta($userID, 'last_name', $arg['last_name']);
							
							wp_update_user( array ( 'ID' => $userID, 'user_nicename' => $arg['display_name'],'display_name' => $arg['display_name'] ) );
							
			  
			   			 return array("status"=>2,"reason"=>"Successfull updated but email was exsit. Not Able to update email");
      
   					}
					else
					{
						  update_user_meta($userID, 'first_name', $arg['first_name']);
							update_user_meta($userID, 'last_name', $arg['last_name']);
							
						  
						  wp_update_user( array ( 'ID' => $userID, 'user_nicename' => $arg['display_name'],'display_name' => $arg['display_name'],'user_email' => $arg['email'] ) );
						  
						  return array("status"=>0,"reason"=>"Succesful updated profile");
					}
				}
				else
				{
					
							update_user_meta($userID, 'first_name', $arg['first_name']);
							update_user_meta($userID, 'last_name', $arg['last_name']);
							
							
							 wp_update_user( array ( 'ID' => $userID, 'user_nicename' => $arg['display_name'],'display_name' => $arg['display_name']) );
							
							
							return array("status"=>0,"reason"=>"Succesful updated profile","new_user_data"=>$this->user_login($userlogin,$userPassword));
				}
				
			}
			
	}
	
	//@Public wordpress update profile API
	// Always Require User to Login To Request an Update
	public function update_billing($userlogin,$userPassword,$arg)
	{
		
			$user = $this->user_login_bool($userlogin,$userPassword);
				
			if($user == false)
			{
				
				return array("status"=>1,"reason"=>"Not Authorized");
			}
			else
			{
				$userID = $user->ID;
				
				
				update_user_meta($userID, 'billing_first_name', $arg['billing_first_name']);
				update_user_meta($userID, 'billing_last_name', $arg['billing_last_name']);
				update_user_meta($userID, 'billing_company', $arg['billing_company']);
				update_user_meta($userID, 'billing_address_1', $arg['billing_address_1']);
				update_user_meta($userID, 'billing_address_2', $arg['billing_address_2']);
				update_user_meta($userID, 'billing_city', $arg['billing_city']);
				update_user_meta($userID, 'billing_postcode', $arg['billing_postcode']);
				update_user_meta($userID, 'billing_state', $arg['billing_state']);	
				update_user_meta($userID, 'billing_country', $arg['billing_country']);			
				update_user_meta($userID, 'billing_phone', $arg['billing_phone']);
				update_user_meta($userID, 'billing_email', $arg['billing_email']);
							
				return array("status"=>0,"reason"=>"Succesfull updated billing","new_user_data"=>$this->user_login($userlogin,$userPassword));
				
			}
			
	}
	
	public function update_shipping($userlogin,$userPassword,$arg)
	{
		
			$user = $this->user_login_bool($userlogin,$userPassword);
				
			if($user == false)
			{
				
				return array("status"=>1,"reason"=>"Not Authorized");
			}
			else
			{
				$userID = $user->ID;
				
				
				update_user_meta($userID, 'shipping_first_name', $arg['shipping_first_name']);
				update_user_meta($userID, 'shipping_last_name', $arg['shipping_last_name']);
				update_user_meta($userID, 'shipping_company', $arg['shipping_company']);
				update_user_meta($userID, 'shipping_address_1', $arg['shipping_address_1']);
				update_user_meta($userID, 'shipping_address_2', $arg['shipping_address_2']);
				update_user_meta($userID, 'shipping_city', $arg['shipping_city']);
				update_user_meta($userID, 'shipping_postcode', $arg['shipping_postcode']);
				update_user_meta($userID, 'shipping_state', $arg['shipping_state']);	
				update_user_meta($userID, 'shipping_country', $arg['shipping_country']);			
				
							
				return array("status"=>0,"reason"=>"Succesfull updated shipping","new_user_data"=>$this->user_login($userlogin,$userPassword));
				
			}
			
	}
	
	public function upload_profile_image_wp_avatar($userlogin,$userPassword,$uploadedfile)
	{
			$user = $this->user_login_bool($userlogin,$userPassword);
				
			if($user == false)
			{
				return array("status"=>1,"reason"=>"Not Authorized");
			}
			else
			{
				
				 $userID = $user->ID;
				 $user_avatar_id = get_user_meta( $userID, "wp_user_avatar", true );
				 wp_delete_attachment($user_avatar_id,true);
				 
				$username = str_replace("-", " ", $user->display_name);
				if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$uploadedfile['name'] = date("U").".jpg";
				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile ) {
   				 $filename = $uploadedfile['name'];
				
				$wp_filetype = wp_check_filetype(basename($filename), null );
  				$wp_upload_dir = wp_upload_dir();
  				$attachment = array(
					 'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ), 
					 'post_mime_type' => $wp_filetype['type'],
					 'post_title' => $user->display_name." Avatar",
					 'post_content' => '',
					 'post_status' => 'inherit'
  					);
  			   $attach_id = wp_insert_attachment( $attachment, $filename, 0 );
		  
 		 		require_once(ABSPATH . 'wp-admin/includes/image.php');
				
				$wp_upload_dir2 = wp_upload_dir();
 					 $attach_data = wp_generate_attachment_metadata( $attach_id,$wp_upload_dir['path'] . '/' . basename( $filename ));
  				wp_update_attachment_metadata( $attach_id, $attach_data );
				
				add_post_meta($attach_id, '_wp_attachment_wp_user_avatar', $userID);
				
				update_post_meta($attach_id, '_wp_attached_file', $wp_upload_dir['subdir'] .'/' . basename( $filename ));
				
				update_user_meta($userID, 'wp_user_avatar', $attach_id);
			
				return array("status"=>0,"reason"=>"Successfull uploaded","new_user_data"=>$this->user_login($userlogin,$userPassword));
				 
    			
				} else {
   					 return array("status"=>2,"reason"=>"Posible file upload attack");
				}
				
				
				
				
				
			}
		
	}
	
	public function post_comment_api($userlogin,$userPassword,$comment,$parent,$postID,$starRating)
	{
		$user = $this->user_login_bool($userlogin,$userPassword);
				
			if($user == false)
			{
				return array("status"=>1,"reason"=>"Not Authorized");
			}
			else
			{
				$time = current_time('mysql');
				
					$data = array(
						'comment_post_ID' => $postID,
						'comment_author' =>  $user->display_name,
						'comment_author_email' => $user->user_email,
						'comment_author_url' => 'http://',
						'comment_content' => $comment,
						'comment_type' => '',
						'comment_parent' => $parent,
						'user_id' => $user->ID,
						'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
						'comment_agent' => 'iOS',
						'comment_date' => $time,
						'comment_approved' => 1,
					);

					$commentID = wp_insert_comment($data);
					
					update_comment_meta( $commentID, 'rating', $starRating );
					
					do_action('comment_post',$commentID);
			
				return array("status"=>0,"reason"=>"Successfully inserted new comment","commentID"=>$commentID);
		
			}
		
		
	}
	
	public function check_single_payment_gateway_meta_key($key)
	{
		if($this->check_key_if_exist($key) == true)
		{
			$result = $this->get_key($key);
			return array("payment_gateway_key"=>$key,"hideit"=>(int)$result->hideit,"safari"=>(int)$result->safari);	
		}
		else
		{
			return array("payment_gateway_key"=>$key,"hideit"=>0,"safari"=>0);	
		}
		
		
	}
	
	function get_key($key)
	{
		
		global $wpdb;
        	$sql = "SELECT * FROM `" . $wpdb->prefix . "amazingcart_paymentgateway_meta` WHERE gateway_key='".$key."'";
		
			$results = $wpdb->get_results($sql);
			
			
				foreach ($results as $result) {
					$re = $result;
				}
				return $re;
			
	}
	
	function check_key_if_exist($key)
	{
		
		global $wpdb;
        	$sql = "SELECT * FROM `" . $wpdb->prefix . "amazingcart_paymentgateway_meta` WHERE gateway_key='".$key."'";
			
			$results = $wpdb->get_results($sql);
			
			if($results)
			{
				
				return true;
			}
			else
			{
				return false;
			}
	}
	
	public function cart_api($userlogin,$userPassword,$productIDJson,$couponCodeJson)
	{
	global $woocommerce;

        $user = $this->user_login_bool($userlogin, $userPassword);


        if ($user == false) {
            return array("status" => 1, "reason" => "Not Authorized");
        } else {
            wp_set_current_user($user->ID);

            define('WOOCOMMERCE_CHECKOUT', true);

            $woocommerce->customer->set_location($user->billing_country, $user->billing_state, $user->billing_postcode, $user->billing_city);

            $woocommerce->customer->set_shipping_location($user->shipping_country, $user->shipping_state, $user->shipping_postcode, $user->shipping_city);

            $woocommerce->customer->get_taxable_address();

            $woocommerce->cart->empty_cart();
            $woocommerce->cart->remove_coupons();
            $sert = stripslashes($productIDJson);
            $coupon = stripslashes($couponCodeJson);
            $cartContent = array();
            $obj = json_decode($sert);
            $couponObject = json_decode($coupon);

            $ca = array();
            foreach ($obj as $key => $value) {

                $woocommerce->cart->add_to_cart($key, $value);
            }

            $couponary = array();
            foreach ($couponObject as $key => $value) {


                $woocommerce->cart->add_discount($value);

                $counponClass = new WC_Coupon($value);
                $counponClass->is_valid();
                array_push($couponary, array("coupon_code" => $value, "error_message" => $counponClass->error_message));
            }


            foreach ($woocommerce->cart->cart_contents as $key => $content) {

                array_push($cartContent, array(
                    "id" => $content['product_id'],
                    "product-price" => $content['line_subtotal'] / $content['quantity'],
                    "product-price-tax" => $content['line_subtotal_tax'] / $content['quantity'],
                    "total-price" => $content['line_total'],
                    "total-price-tax" => $content['line_tax'],
                    "quantity" => $content['quantity']

                ));
            }


            //$woocommerce->cart->init( );

            // $woocommerce->cart->calculate_shipping();
            //$woocommerce->cart->calculate_totals();


            //$woocommerce->cart = $this->calculateTotal($woocommerce->cart,$woocommerce,$obj);
            $woocommerce->cart->calculate_totals();


            $paymentGateway = array();


            $payment_gateway_meta = new AmazingCart_paymentgateway_meta();

            foreach ($woocommerce->payment_gateways->get_available_payment_gateways() as $key => $value) {
                $result = $payment_gateway_meta->check_key_if_exist($key);
                if ($result == true) {
                    //$res = $payment_gateway_meta->get_key($key);
                } else {
                    $res = array("hideit" => 0, "safari" => 0);
                }

                array_push($paymentGateway, array(
                    "id" => $key,
                    "title" => $value->settings['title'],
                    "Description" => $value->settings['description'],
                    "meta_key" => $res
                ));
            }

            //$available_methods = $woocommerce->shipping->get_available_shipping_methods();


            $packages = $woocommerce->cart->get_shipping_packages();
            $chosen_shipping_methods = $woocommerce->session->get('chosen_shipping_methods');

            //$all_shipping_method = $woocommerce->shipping->load_shipping_methods();

            //return $all_shipping_method['rates'];
            //return WC_Shipping()->get_shipping_methods();

            return array(
                "cart" => $cartContent,
                "coupon" => array(
                    "applied-coupon" => $woocommerce->cart->applied_coupons,
                    "discount-ammount" => $woocommerce->cart->coupon_discount_amounts,
                    "coupon-array-inserted" => $couponary
                ),
                "has_tax" => get_option('woocommerce_calc_taxes'),
                "currency" => get_woocommerce_currency_symbol(),
                "display-price-during-cart-checkout" => get_option('woocommerce_tax_display_cart'),
                "cart-subtotal" => $woocommerce->cart->subtotal,
                "cart-subtotal-ex-tax" => $woocommerce->cart->subtotal_ex_tax,
                "cart-tax-total" => $woocommerce->cart->tax_total + $woocommerce->cart->shipping_tax_total,
                "shipping-cost" => $woocommerce->cart->shipping_total + $woocommerce->cart->shipping_tax_total,
                "shipping-method" => $packages[$chosen_shipping_methods[0]]->label,
                "discount" => $woocommerce->cart->discount_total,
                "grand-total" => (float)$woocommerce->cart->total,
                "payment-method" => $paymentGateway,
                "shipping_available" => $packages['rates']
            );
        }
		
	}
	
	public function mobile_woo_pay_action_extension($orderKey,$orderID,$paymentMethodID,$post)
	{
	global $woocommerce;

	if ( !empty($orderKey ) && !empty($orderID )  ) {

		ob_start();

		// Pay for existing order
		$order_key 	= urldecode( $orderKey);
		$order_id 	= absint( $orderID );
		$order 		= new WC_Order( $order_id );

		if ( $order->id == $order_id && $order->order_key == $order_key && in_array( $order->status, array( 'pending', 'failed' ) ) ) 		
		{

			// Set customer location to order location
			if ( $order->billing_country )
				$woocommerce->customer->set_country( $order->billing_country );
			if ( $order->billing_state )
				$woocommerce->customer->set_state( $order->billing_state );
			if ( $order->billing_postcode )
				$woocommerce->customer->set_postcode( $order->billing_postcode );
			if ( $order->billing_city )
				$woocommerce->customer->set_city( $order->billing_city );

			// Update payment method
			if ( $order->order_total > 0 ) {
				$payment_method = woocommerce_clean( $paymentMethodID );

				$available_gateways = $woocommerce->payment_gateways->get_available_payment_gateways();

				// Update meta
				update_post_meta( $order_id, '_payment_method', $payment_method );

				if ( isset( $available_gateways[ $payment_method ] ) )
					$payment_method_title = $available_gateways[ $payment_method ]->get_title();

				update_post_meta( $order_id, '_payment_method_title', $payment_method_title);

				// Validate
				$available_gateways[ $payment_method ]->validate_fields();

				// Process
				if ( $woocommerce->error_count() == 0 ) {

					$result = $available_gateways[ $payment_method ]->process_payment( $order_id );

					// Redirect to success/confirmation/payment page
					if ( $result['result'] == 'success' ) {
						wp_redirect( $result['redirect'] );
						exit;
						
						
					}
					else
					{
						
						return json_encode(array(
									"Status"=>"4",
									"Reason"=>$woocommerce->get_errors()
									));;
					}

				}
				else
				{
						
					return json_encode(array(
									"Status"=>"3",
									"Reason"=>$woocommerce->get_errors()
									));
				}

			} else {

				// No payment was required for order
				$order->payment_complete();
				wp_safe_redirect( get_permalink( woocommerce_get_page_id( 'thanks' ) ) );
				exit;

			}

		}
		else
		{
						
		    return json_encode(array(
						 "Status"=>"2",
						 "Reason"=>"Order key or order id is not authorized or status is not in pending or failed"
						));
		}

	}
	else
		{
						
		    return json_encode(array(
						 "Status"=>"1",
						 "Reason"=>"Order Number or Order key is empty"
						));
		}
}
	
	public function mobile_woo_pay_action_extension_authorize_dot_net($orderKey,$orderID,$paymentMethodID,$post)
	{
	global $woocommerce;

	if ( !empty($orderKey ) && !empty($orderID )  ) {

		ob_start();

		// Pay for existing order
		$order_key 	= urldecode( $orderKey);
		$order_id 	= absint( $orderID );
		$order 		= new WC_Order( $order_id );

		if ( $order->id == $order_id && $order->order_key == $order_key && in_array( $order->status, array( 'pending', 'failed' ) ) ) 		
		{

			// Set customer location to order location
			if ( $order->billing_country )
				$woocommerce->customer->set_country( $order->billing_country );
			if ( $order->billing_state )
				$woocommerce->customer->set_state( $order->billing_state );
			if ( $order->billing_postcode )
				$woocommerce->customer->set_postcode( $order->billing_postcode );
			if ( $order->billing_city )
				$woocommerce->customer->set_city( $order->billing_city );

			// Update payment method
			if ( $order->order_total > 0 ) {
				$payment_method = woocommerce_clean( $paymentMethodID );

				$available_gateways = $woocommerce->payment_gateways->get_available_payment_gateways();

				// Update meta
				update_post_meta( $order_id, '_payment_method', $payment_method );

				if ( isset( $available_gateways[ $payment_method ] ) )
					$payment_method_title = $available_gateways[ $payment_method ]->get_title();

				update_post_meta( $order_id, '_payment_method_title', $payment_method_title);

				// Validate
				$available_gateways[ $payment_method ]->validate_fields();

				// Process
				if ( $woocommerce->error_count() == 0 ) {

					$result = $available_gateways[ $payment_method ]->process_payment( $order_id );

					// Redirect to success/confirmation/payment page
					if ( $result['result'] == 'success' ) {
						
						
						return json_encode(array(
									"Status"=>"0",
									"Reason"=>"Success",
									"redirect"=> $result['redirect']
									));;
						
					}
					else
					{
						
						return json_encode(array(
									"Status"=>"4",
									"Reason"=>$woocommerce->get_errors()
									));;
					}

				}
				else
				{
						
					return json_encode(array(
									"Status"=>"3",
									"Reason"=>$woocommerce->get_errors()
									));
				}

			} else {

				// No payment was required for order
				$order->payment_complete();
				wp_safe_redirect( get_permalink( woocommerce_get_page_id( 'thanks' ) ) );
				exit;

			}

		}
		else
		{
						
		    return json_encode(array(
						 "Status"=>"2",
						 "Reason"=>"Order key or order id is not authorized or status is not in pending or failed"
						));
		}

	}
	else
		{
						
		    return json_encode(array(
						 "Status"=>"1",
						 "Reason"=>"Order Number or Order key is empty"
						));
		}
}
	
	public function create_order_api($userlogin,$userPassword,$productIDJson,$couponCodeJson,$paymentChooseID,$orderNote)
	{
		global $woocommerce;
		
		$user = $this->user_login_bool($userlogin,$userPassword);
		
            if($user == false)
			{
				return array("status"=>1,"reason"=>"Not Authorized");
			}
			else
			{
				wp_set_current_user($user->ID);
				define( 'WOOCOMMERCE_CHECKOUT', true );
				
				$woocommerce->customer->set_location( $user->billing_country,$user->billing_state,$user->billing_postcode,$user->billing_city  );
				$woocommerce->customer->set_shipping_location( $user->shipping_country,$user->shipping_state,$user->shipping_postcode,$user->shipping_city );
				
				$woocommerce->cart->empty_cart();
				$woocommerce->cart->remove_coupons();
				$sert = stripslashes($productIDJson);
				$coupon = stripslashes($couponCodeJson);
				$cartContent = array();
				$obj = json_decode($sert);
				$couponObject = json_decode($coupon);
			
				foreach ($obj as $key=>$value)
                {
                    $woocommerce->cart->add_to_cart( $key,$value);
				}
				
				foreach ($couponObject as $key=>$value)
                {
                    $woocommerce->cart->add_discount($value);
                }
				
			    //$woocommerce->cart = $this->calculateTotal($woocommerce->cart,$woocommerce,$obj);
		        $woocommerce->cart->calculate_totals();
			
		 	    //Customer ID & UserData
                $posted["customer_id"] = $user->ID;
		  
                $posted["billing_first_name"] = $user->billing_first_name;
                $posted["billing_last_name"] = $user->billing_last_name;
                $posted["billing_company"] = $user->billing_company;
                $posted["billing_address_1"] = $user->billing_address_1;
                $posted["billing_address_2"] =$user->billing_address_2;
                $posted["billing_city"] = $user->billing_city;
                $posted["billing_postcode"] = $user->billing_postcode;
                $posted["billing_state"] = $user->billing_state;
                $posted["billing_country"] = $user->billing_country;
                $posted["billing_phone"] = $user->billing_phone;
                $posted["billing_email"] = $user->billing_email;
            
                $posted["shipping_first_name"] = $user->shipping_first_name;
                $posted["shipping_last_name"] = $user->shipping_last_name;
                $posted["shipping_company"] = $user->shipping_company;
                $posted["shipping_address_1"] = $user->shipping_address_1;
                $posted["shipping_address_2"] =$user->shipping_address_2;
                $posted["shipping_city"] = $user->shipping_city;
                $posted["shipping_postcode"] = $user->shipping_postcode;
                $posted["shipping_state"] = $user->shipping_state;
                $posted["shipping_country"] = $user->shipping_country;

                $posted['order_comments'] = $orderNote;
		  
		        //$packages = WC()->shipping->get_packages();
                //$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		        //Shipping
                $posted["shipping_method_id"] = $chosen_shipping_methods[0];
                $posted["shipping_method_title"] = $packages[0]['rates'][$chosen_shipping_methods[0]]->label;

			    //Get Payment Gateway Label
                $av = $woocommerce->payment_gateways->get_available_payment_gateways();
			    foreach($av as $key=>$value)
			    {
				    if($paymentChooseID == $key)
				    {
					    $paymentLabel = $value->settings['title'];	
    				}
	    		}	

                // Payment Method
			    $posted["payment_method_id"] = $paymentChooseID;
			    $posted["payment_method_title"] = $paymentLabel;
    		   
	    		$orderID = $this->create_order($posted);
		    	$order = new WC_Order($orderID);
		    	$order->post_author=$user->ID;
			    //return $order;
		
			    $billing_address = $order->get_formatted_billing_address();
			    $new_billing_address = str_replace("<br/>", "\n", $billing_address);
			
			    $shipping_address = $order->get_formatted_shipping_address();
			    $new_shipping_address = str_replace("<br/>", "\n", $shipping_address);
			
			    $itemArray = array();
			
			    $totalSubPriceWithTax = 0;
			    $totalSubPrice = 0;
			    $sde = $order->get_items();
			    foreach($sde as $key=>$items)
			    {
				    if(wp_get_attachment_url(get_post_meta($items['product_id'], '_thumbnail_id',true )) == false)
				    {
					    $featured_image = "0";	
				    }
				    else
				    {
					    $featured_image = wp_get_attachment_url(get_post_meta($items['product_id'], '_thumbnail_id',true ));
				    }
				    if(wp_get_attachment_url(get_post_meta($items['variation_id'], '_thumbnail_id',true )) == false)
				    {
					    $featured_image_variation = $featured_image;	
				    }
				    else
				    {
					    $featured_image_variation = wp_get_attachment_url(get_post_meta($items['variation_id'], '_thumbnail_id',true ));
				    }
				
				    array_push($itemArray,array(
				                            "product_id"=>$items['product_id'],
				                            "product_info"=>array(
									                        "featuredImages"=>$featured_image,
									                        "productName"=>get_the_title($items['product_id']),
									                        ),
				                            "variation_id"=>$items['variation_id'],
				                            "variation_info"=>array(
									                        "featuredImages"=>$featured_image_variation,
									                        "productName"=>get_the_title($items['product_id']),
								                            ),
				                            "quantity"=>$items['qty'],
				                            "product_price"=>($items['item_meta']['_line_total'][0]+$items['item_meta']['_line_tax'][0])/$items['qty'],
				                            "product_price_ex_tax"=>($items['item_meta']['_line_total'][0])/$items['qty'],
				                            "total_price"=>$items['item_meta']['_line_total'][0]+$items['item_meta']['_line_tax'][0],
				                            "total_price_ex_tax"=>$items['item_meta']['_line_total'][0]));
				
				    $totalSubPriceWithTax = $totalSubPriceWithTax + $items['item_meta']['_line_total'][0]+$items['item_meta']['_line_tax'][0];
				    $totalSubPrice = $totalSubPrice + $items['item_meta']['_line_total'][0];
				    $product = get_product($items['product_id']);
				    wc_update_product_stock($items['product_id'], $product->get_stock_quantity() - $items['qty']); //manually reduce product line on add
			}
                $shipping_m = $order->shipping_method_title;
                if($shipping_m == null || $shipping_m == ""){
                    $shipping_m = "No shipping";
                }
                return array(
                        "orderID"=>$order->id,
                        "order_key"=>$order->order_key,
						"display-price-during-cart-checkout"=>get_option('woocommerce_tax_display_cart'),
						"orderDate"=>$order->order_date,
						"paymentDate"=>$order->modified_date,
						"status"=>$order->status,
						"currency"=>get_woocommerce_currency_symbol(),
						"billing_email"=>$order->billing_email,
						"billing_phone"=>$order->billing_phone,
						"billing_address"=>$new_billing_address,
						"shipping_address"=>$new_shipping_address,
						"items"=>$itemArray,
						"used_coupon"=>$order->get_used_coupons(),
						"subtotalWithTax"=>$totalSubPriceWithTax,
						"subtotalExTax"=>$totalSubPrice,
						"shipping_method"=>$order->shipping_m,
						"shipping_cost"=>$order->calculate_shipping(),
						"shipping_tax"=>$order->get_shipping_tax(),
						"tax_total"=>round($order->order_tax,2)+$order->get_shipping_tax(),
						"discount_total"=>$order->get_total_discount() ,
						"order_total"=>$order->order_total,
						"order_note"=>$order->customer_note,
						"payment_method_id"=>$order->payment_method,
						"payment_method_title"=>$order->payment_method_title
						);
		}
	}
	
	/**
	 * NEED TO REVIEW
	 */
	public function getSingleOrder($userlogin,$userPassword,$orderID)
	{
		global $woocommerce;

        $user = $this->user_login_bool($userlogin, $userPassword);

        if ($user == false) {
            return array("status" => 1, "reason" => "Not Authorized");
        } else {
            $order = new WC_Order($orderID);
            
            //$date=$loop->post->post_date;//("d-m-Y", $unixtimeCreated);
                    
            //$billing_address = $order->get_formatted_billing_address();
            //$new_billing_address = str_replace("<br />\n", "\n", $billing_address);

            //$shipping_address = $order->get_formatted_shipping_address();
            //$new_shipping_address = str_replace("<br />\n", "\n", $shipping_address);

            $itemArray = array();
            
            $totalSubPriceWithTax = 0;
            $totalSubPrice = 0;
            $sde = $order->get_items();
            foreach ($sde as $key => $items) {

                if (wp_get_attachment_url(get_post_meta($items['product_id'], '_thumbnail_id', true)) == false) {
                    $featured_image = "0";
                } else {
                    $featured_image = wp_get_attachment_url(get_post_meta($items['product_id'], '_thumbnail_id', true));
                }


                if (wp_get_attachment_url(get_post_meta($items['variation_id'], '_thumbnail_id', true)) == false) {
                    $featured_image_variation = $featured_image;
                } else {
                    $featured_image_variation = wp_get_attachment_url(get_post_meta($items['variation_id'], '_thumbnail_id', true));
                }

                array_push($itemArray, array(
                    "product_id" => $items['product_id'],
                    "product_info" => array(
                        "featuredImages" => $featured_image,
                        "productName" => get_the_title($items['product_id']),

                    ),
                    "variation_id" => $items['variation_id'],
                    "variation_info" => array(
                        "featuredImages" => $featured_image_variation,
                        "productName" => get_the_title($items['product_id']),

                    ),
                    "quantity" => $items['qty'],
                    "product_price" => ($items['item_meta']['_line_total'][0] + $items['item_meta']['_line_tax'][0]) / $items['qty'],
                    "product_price_ex_tax" => ($items['item_meta']['_line_total'][0]) / $items['qty'],
                    "total_price" => $items['item_meta']['_line_total'][0] + $items['item_meta']['_line_tax'][0],
                    "total_price_ex_tax" => $items['item_meta']['_line_total'][0]));

                $totalSubPriceWithTax = $totalSubPriceWithTax + $items['item_meta']['_line_total'][0] + $items['item_meta']['_line_tax'][0];
                $totalSubPrice = $totalSubPrice + $items['item_meta']['_line_total'][0];
            }

            $unixtimeCreated = strtotime($order->order_date);
            $unixtimeModified = strtotime($order->order_date);

            foreach ($woocommerce->payment_gateways->get_available_payment_gateways() as $key => $value) {
                if ($order->payment_method == $key) {
                    $paymentDesc = $value->settings['description'];
                }

            }

            return array(
                "orderID" => $order->id,
                "order_key" => $order->order_key,
                "display-price-during-cart-checkout" => get_option('woocommerce_tax_display_cart'),
                "orderDate" => $order->order_date,//date("d-m-Y", $unixtimeModified),//$date,//("d-m-Y", $unixtimeCreated),
                "paymentDate" => date("d/m/Y", $unixtimeModified),
                "status" => $order->status,
                "currency" => get_woocommerce_currency_symbol(),
                "billing_email" => $order->billing_email,
                "billing_phone" => $order->billing_phone,
                "billing_address" => str_replace("<br/>", "\n", $order->get_formatted_billing_address()),
                "shipping_address" => str_replace("<br/>", "\n", $order->get_formatted_shipping_address()),
                "items" => $itemArray,
                "used_coupon" => $order->get_used_coupons(),
                "subtotalWithTax" => $totalSubPriceWithTax,
                "subtotalExTax" => $totalSubPrice,
                "shipping_method" => $order->shipping_method_title,
                "shipping_cost"=>$order->calculate_shipping(),
                "shipping_tax"=>$order->get_shipping_tax(),
                "tax_total"=>round($order->order_tax,2)+$order->get_shipping_tax(),
                "discount_total"=>$order->get_total_discount(),
                "order_total"=>(float)$order->get_total(),
                "order_note"=>$order->customer_note,
                "payment_method_id"=>$order->payment_method,
                "payment_method_title"=>$order->payment_method_title,
                "payment_desc"=>$paymentDesc,
                "order_notes" => $this->getOrderNotes($order->id)
            );
        }
    }

	private function getOrderNotes($postID)
	{
		 $se = array();
	  
	 
		
		
		
		 global $wpdb;
    $sql = "SELECT * from ".$wpdb->prefix."comments WHERE comment_post_ID = '".$postID."' AND comment_approved='1'";
    $getResult = $wpdb->get_results( $sql);
	$se = array();
	foreach ( $getResult as $result ) 
	{
		$is_customer_note = get_comment_meta ( $result->comment_ID, 'is_customer_note', true );
		if($is_customer_note == "1")
		{
		array_push($se,
		array(
			"comment_id"=>(int)$result->comment_ID,
			"date"=>$result->comment_date,
			"unixtime"=>strtotime( $result->comment_date ),
			"servertime"=>(int)date("U"),
			"ago"=>$this->time_elapsed_string(strtotime( $result->comment_date )),
			"parent"=>$result->comment_parent,
			"agent"=>$result->comment_agent,
			"content"=>strip_tags($result->comment_content)
			
		)
		);
		}
	}
		
		
		
			
		return $se;
	}
	
	public function getSettings()
	{
		$orderStatusList = array();
		$statuses = wc_get_order_statuses();
		array_push($orderStatusList,array("status_slug"=>"All","status_label"=>"All"));
		while(current($statuses))
		{
		    array_push($orderStatusList,array("status_slug"=>key($statuses),"status_label"=>current($statuses)));
		    next($statuses);
		}
		return array(
					"currency"=>get_woocommerce_currency(),
					"currency_symbol"=>get_woocommerce_currency_symbol(),
					"appearance_option"=>array(
											"category_browse_option"=>get_option('amazingcart_categories_option'),
											"category_browse_show_thumb"=>get_option('amazingcart_show_category_thumb')
											),
					"page"=>array(
								"thankyou"=>get_permalink( woocommerce_get_page_id( 'thanks' ) ),
								"cart"=>get_permalink( woocommerce_get_page_id( 'cart' ) ),
								"lost_password"=>get_permalink( woocommerce_get_page_id('lost_password'))
								),
					"status_list"=>$orderStatusList,
					"instagram_api"=>array(
								"client_id"=>get_option( 'instagramClientID' )
								)
					);
	}
	
	/*
	===============================
	PRIVATE METHOD
	===============================
	*/
	
	//Get Shipping ID.... We only had Shipping Label. So we need to convert it to shipping ID
	private function shippingLabelToShippingID($shipping_label)
	{
				global $woocommerce;
			  
			   $available_methods = $woocommerce->shipping->get_available_shipping_methods();
				$sa = array();
				foreach($available_methods  as $key=>$method)
				{
					
					
					
					
					if($shipping_label == $method->label)
					{
					
					$shippingID = $method->method_id;
					
					
					}
					
				}
				
				
				return $shippingID;
		
	}
	
	private function updateUseridInUserTable($userID,$deviceID)
	{
		
			global $wpdb;
			
			
			$sql = "UPDATE ".$wpdb->prefix."amazingcart_user SET wpuserid='".$userID."' WHERE deviceID='".$deviceID."'";
			$wpdb->query($sql);
			
			
	}
	
	//Deprecated // will not use anymore
	//Since there is some limitation in WC_checkout We created an order manualy
	//This Function was copied from WC_Checkout->create_order 
	// @version		1.6.4
	private function create_order($posted)
	{ 
		global $woocommerce, $wpdb;

		// Create Order (send cart variable so we can record items and reduce inventory). Only create if this is a new order, not if the payment was rejected.
		$order_data = apply_filters( 'woocommerce_new_order_data', array(
			'post_type' 	=> 'shop_order',
			'post_title' 	=> sprintf( __( 'Order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) ),
			'post_status' 	=> 'wc-pending',
			'ping_status'	=> 'close',
			'post_excerpt' 	=> isset( $posted['order_comments'] ) ? $posted['order_comments'] : '',
			'post_author' 	=> $posted['customer_id'],
			'post_password'	=> uniqid( 'order_' )	// Protects the post just in case
		) );

		// Insert or update the post data
		$create_new_order = true;

		

		if ( $create_new_order ) {
			$order_id = wp_insert_post( $order_data );

			do_action( 'woocommerce_new_order', $order_id );
		}

		// Store user data billing
		update_post_meta( $order_id, '_' . "billing_first_name", $posted["billing_first_name"] );
		update_post_meta( $order_id, '_' . "billing_last_name", $posted["billing_last_name"] );
		update_post_meta( $order_id, '_' . "billing_company", $posted["billing_company"] );
		update_post_meta( $order_id, '_' . "billing_address_1", $posted["billing_address_1"] );
		update_post_meta( $order_id, '_' . "billing_address_2", $posted["billing_address_2"] );
		update_post_meta( $order_id, '_' . "billing_city", $posted["billing_city"] );
		update_post_meta( $order_id, '_' . "billing_postcode", $posted["billing_postcode"] );
		update_post_meta( $order_id, '_' . "billing_state", $posted["billing_state"] );
		update_post_meta( $order_id, '_' . "billing_country", $posted["billing_country"] );
		update_post_meta( $order_id, '_' . "billing_phone", $posted["billing_phone"] );
		update_post_meta( $order_id, '_' . "billing_email", $posted["billing_email"] );
		
		// Store user data Shipping
		update_post_meta( $order_id, '_' . "shipping_first_name",$posted["shipping_first_name"] );
		update_post_meta( $order_id, '_' . "shipping_last_name",$posted["shipping_last_name"] );
		update_post_meta( $order_id, '_' . "shipping_company",$posted["shipping_company"] );
		update_post_meta( $order_id, '_' . "shipping_address_1",$posted["shipping_address_1"] );
		update_post_meta( $order_id, '_' . "shipping_address_2",$posted["shipping_address_2"] );
		update_post_meta( $order_id, '_' . "shipping_city",$posted["shipping_city"] );
		update_post_meta( $order_id, '_' . "shipping_postcode",$posted["shipping_postcode"] );
		update_post_meta( $order_id, '_' . "shipping_state",$posted["shipping_state"] );
		update_post_meta( $order_id, '_' . "shipping_country",$posted["shipping_country"] );
				
		
		/* KIV
		// Save any other user meta
		if ( $this->customer_id )
			do_action( 'woocommerce_checkout_update_user_meta', $this->customer_id, $this->posted );
		*/
		// Store the line items to the new/resumed order
		foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {

			$_product = $values['data'];

           	// Add line item
           	$item_id = woocommerce_add_order_item( $order_id, array(
		 		'order_item_name' 		=> $_product->get_title(),
		 		'order_item_type' 		=> 'line_item'
		 	) );

		 	// Add line item meta
		 	if ( $item_id ) {
			 	woocommerce_add_order_item_meta( $item_id, '_qty', apply_filters( 'woocommerce_stock_amount', $values['quantity'] ) );
			 	woocommerce_add_order_item_meta( $item_id, '_tax_class', $_product->get_tax_class() );
			 	woocommerce_add_order_item_meta( $item_id, '_product_id', $values['product_id'] );
			 	woocommerce_add_order_item_meta( $item_id, '_variation_id', $values['variation_id'] );
			 	woocommerce_add_order_item_meta( $item_id, '_line_subtotal', woocommerce_format_decimal( $values['line_subtotal'], 4 ) );
			 	woocommerce_add_order_item_meta( $item_id, '_line_total', woocommerce_format_decimal( $values['line_total'], 4 ) );
			 	woocommerce_add_order_item_meta( $item_id, '_line_tax', woocommerce_format_decimal( $values['line_tax'], 4 ) );
			 	woocommerce_add_order_item_meta( $item_id, '_line_subtotal_tax', woocommerce_format_decimal( $values['line_subtotal_tax'], 4 ) );

			 	// Store variation data in meta so admin can view it
				if ( $values['variation'] && is_array( $values['variation'] ) )
					foreach ( $values['variation'] as $key => $value )
						woocommerce_add_order_item_meta( $item_id, esc_attr( str_replace( 'attribute_', '', $key ) ), $value );

			 	// Add line item meta for backorder status
			 	if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $values['quantity'] ) )
			 		woocommerce_add_order_item_meta( $item_id, apply_filters( 'woocommerce_backordered_item_meta_name', __( 'Backordered', 'woocommerce' ), $cart_item_key, $order_id ), $values['quantity'] - max( 0, $_product->get_total_stock() ) );

			 	// Allow plugins to add order item meta
			 	do_action( 'woocommerce_add_order_item_meta', $item_id, $values );
		 	}
		}

		// Store fees
		foreach ( $woocommerce->cart->get_fees() as $fee ) {
			$item_id = woocommerce_add_order_item( $order_id, array(
		 		'order_item_name' 		=> $fee->name,
		 		'order_item_type' 		=> 'fee'
		 	) );

		 	if ( $fee->taxable )
		 		woocommerce_add_order_item_meta( $item_id, '_tax_class', $fee->tax_class );
		 	else
		 		woocommerce_add_order_item_meta( $item_id, '_tax_class', '0' );

		 	woocommerce_add_order_item_meta( $item_id, '_line_total', woocommerce_format_decimal( $fee->amount ) );
			woocommerce_add_order_item_meta( $item_id, '_line_tax', woocommerce_format_decimal( $fee->tax ) );
		}

		// Store tax rows
		foreach ( array_keys( $woocommerce->cart->taxes + $woocommerce->cart->shipping_taxes ) as $key ) {

			$item_id = woocommerce_add_order_item( $order_id, array(
		 		'order_item_name' 		=> $woocommerce->cart->tax->get_rate_code( $key ),
		 		'order_item_type' 		=> 'tax'
		 	) );

		 	// Add line item meta
		 	if ( $item_id ) {
		 		woocommerce_add_order_item_meta( $item_id, 'rate_id', $key );
		 		woocommerce_add_order_item_meta( $item_id, 'label', $woocommerce->cart->tax->get_rate_label( $key ) );
			 	woocommerce_add_order_item_meta( $item_id, 'compound', absint( $woocommerce->cart->tax->is_compound( $key ) ? 1 : 0 ) );
			 	woocommerce_add_order_item_meta( $item_id, 'tax_amount', woocommerce_clean( isset( $woocommerce->cart->taxes[ $key ] ) ? $woocommerce->cart->taxes[ $key ] : 0 ) );
			 	woocommerce_add_order_item_meta( $item_id, 'shipping_tax_amount', woocommerce_clean( isset( $woocommerce->cart->shipping_taxes[ $key ] ) ? $woocommerce->cart->shipping_taxes[ $key ] : 0 ) );
			}
		}

		// Store coupons
		if ( $applied_coupons = $woocommerce->cart->get_applied_coupons() ) {
			foreach ( $applied_coupons as $code ) {

				$item_id = woocommerce_add_order_item( $order_id, array(
			 		'order_item_name' 		=> $code,
			 		'order_item_type' 		=> 'coupon'
			 	) );

			 	// Add line item meta
			 	if ( $item_id ) {
			 		woocommerce_add_order_item_meta( $item_id, 'discount_amount', isset( $woocommerce->cart->coupon_discount_amounts[ $code ] ) ? $woocommerce->cart->coupon_discount_amounts[ $code ] : 0 );
				}
			}
		}

		// Shipping
		
			update_post_meta( $order_id, '_shipping_method', 		$posted["shipping_method_id"] );
			update_post_meta( $order_id, '_shipping_method_title', 	$posted["shipping_method_title"]);
			update_post_meta( $order_id, '_shipping_method_title', 	$posted["shipping_method_title"]);
		

		// Payment Method
			update_post_meta( $order_id, '_payment_method', 		$posted["payment_method_id"] );
			update_post_meta( $order_id, '_payment_method_title', 	$posted["payment_method_title"] );
		

		update_post_meta( $order_id, '_order_shipping', 		woocommerce_format_total( $woocommerce->cart->shipping_total ) );
		update_post_meta( $order_id, '_order_discount', 		woocommerce_format_total( $woocommerce->cart->get_order_discount_total() ) );
		update_post_meta( $order_id, '_cart_discount', 			woocommerce_format_total( $woocommerce->cart->get_cart_discount_total() ) );
		update_post_meta( $order_id, '_order_tax', 				woocommerce_clean( $woocommerce->cart->tax_total ) );
		update_post_meta( $order_id, '_order_shipping_tax', 	woocommerce_clean( $woocommerce->cart->shipping_tax_total ) );
		update_post_meta( $order_id, '_order_total', 			woocommerce_format_total( $woocommerce->cart->total ) );
		update_post_meta( $order_id, '_order_key', 				apply_filters('woocommerce_generate_order_key', uniqid('order_') ) );
		update_post_meta( $order_id, '_customer_user', 			absint( $posted["customer_id"] ) );
		update_post_meta( $order_id, '_order_currency', 		get_woocommerce_currency_symbol() );
		update_post_meta( $order_id, '_prices_include_tax', 	get_option( 'woocommerce_prices_include_tax' ) );
		update_post_meta( $order_id, '_customer_ip_address',	isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'] );
		update_post_meta( $order_id, '_customer_user_agent', 	'iOS' );

		
		// Order status
		wp_set_object_terms( $order_id, 'wc-pending', 'shop_order_status' );
			 //	do_action( 'woocommerce_product_set_stock', $product );
		return $order_id;
	}
	
	private function calculateTotal($woo,$woocommerce,$obj)
	{
		//$woo->reset();
		if ( get_option('woocommerce_tax_round_at_subtotal') == 'no' ) {
					$woo->tax_total          = $woo->tax->get_tax_total( $woo->taxes );
					$woo->taxes              = array_map( array( $woo->tax, 'round' ), $woo->taxes );
				} else {
					$woo->tax_total          = array_sum( $woo->taxes );
				}

				// Cart Shipping
				$woo->calculate_shipping();

				// Total up/round taxes for shipping
				if ( get_option('woocommerce_tax_round_at_subtotal') == 'no' ) {
					$woo->shipping_tax_total = $woo->tax->get_tax_total( $woo->shipping_taxes );
					$woo->shipping_taxes     = array_map( array( $woo->tax, 'round' ), $woo->shipping_taxes );
				} else {
					$woo->shipping_tax_total = array_sum( $woo->shipping_taxes );
				}

				// VAT exemption done at woo point - so all totals are correct before exemption
				if ( $woocommerce->customer->is_vat_exempt() )
					$woo->remove_taxes();

				// Cart Discounts (after tax)
				//$woo->apply_cart_discounts_after_tax();

				// Allow plugins to hook and alter totals before final total is calculated
				do_action( 'woocommerce_calculate_totals', $woo );

				// Grand Total - Discounted product prices, discounted tax, shipping cost + tax, and any discounts to be added after tax (e.g. store credit)
				$woo->total = max( 0, apply_filters( 'woocommerce_calculated_total', number_format( $woo->cart_contents_total + $woo->tax_total + $woo->shipping_tax_total + $woo->shipping_total - $woo->discount_total + $woo->fee_total, $woo->dp, '.', '' ), $woo ) );
				
				
				return 	$woo;
		
	}
	
	private function add_product_to_cart($productID)
	{
	
		global $woocommerce;
		$product_id = $productID;
		$found = false;
		//check if product already in cart
		if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
			foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
				$_product = $values['data'];
				if ( $_product->id == $product_id )
					$found = true;
			}
			// if product not found, add it
			if ( ! $found )
				$woocommerce->cart->add_to_cart( $product_id );
		} else {
			// if no products in cart, add it
			$woocommerce->cart->add_to_cart( $product_id );
		}
	
		return true;
	}
	
	public function jsonSetup($id)
	{
		$woo = new WC_Product($id);
		$woo->id = $id->ID;
		$woo->post = $id;
		
		if(wp_get_attachment_url(get_post_thumbnail_id($id->ID, 'full')) == false)
		{
			$featuredImage = "0";	
			
		}
		else
		{
			$featuredImage = wp_get_attachment_url(get_post_thumbnail_id($id->ID, 'full'));	
		}
		
		$string = strip_tags($id->post_excerpt);
		$string = str_replace("\r",'', $string);
		$string = str_replace("\n",'', $string);
		
		$array = array
				(
				
					"product_ID"=>$id->ID,
					"is_downloadble"=>$woo->is_downloadable( ),
					"is_virtual"=>$woo->is_virtual(),
					"is_purchasable"=>$woo->is_purchasable( ),
					"is_featured"=>$woo->is_featured( ),
					"visibility"=>get_post_meta($id->ID, '_visibility',true ),
					"general"=>array
								  (
								  "title"=>$id->post_title,
								  "link"=>get_permalink($id->ID ),
								  "content"=>array
								  			(
												"full_html"=>htmlspecialchars(do_shortcode($id->post_content)),
												"excepts"=>$string
								  			),
								  "SKU"=>$woo->get_sku(),
								  "product_type"=>$this->get_product_type($id->ID),
								   "if_external"=>array
								   				  (
												  "product_url"=>urlencode(get_post_meta($id->ID, '_product_url',true )),
												  "button_name"=>get_post_meta($id->ID, '_button_text',true )
								   				  ),
								  "pricing"=>array
											   (
											   "is_on_sale"=>$woo->is_on_sale( ),
											   "currency"=>get_woocommerce_currency_symbol(),
											   "regular_price"=>get_post_meta( $id->ID, '_regular_price',true ),
											   "sale_price"=>get_post_meta($id->ID, '_sale_price',true ),
											   "sale_start"=>array(
											   					"unixtime"=>get_post_meta($id->ID, '_sale_price_dates_from',true ),
																"day"=>date("d",get_post_meta($id->ID, '_sale_price_dates_from',true )),
																"month"=>date("m",get_post_meta($id->ID, '_sale_price_dates_from',true )),
																"year"=>date("Y",get_post_meta($id->ID, '_sale_price_dates_from',true )),
																"day_name"=>date("l",get_post_meta($id->ID, '_sale_price_dates_from',true )),
																"fulldate"=>date("d/m/Y",get_post_meta($id->ID, '_sale_price_dates_from',true )),
															),
												 "sale_end"=>array(
											   					"unixtime"=>get_post_meta($id->ID, '_sale_price_dates_to',true ),
																"day"=>date("d",get_post_meta($id->ID, '_sale_price_dates_to',true )),
																"month"=>date("m",get_post_meta($id->ID, '_sale_price_dates_to',true )),
																"year"=>date("Y",get_post_meta($id->ID, '_sale_price_dates_to',true )),
																"day_name"=>date("l",get_post_meta($id->ID, '_sale_price_dates_to',true )),
																"fulldate"=>date("d/m/Y",get_post_meta($id->ID, '_sale_price_dates_to',true )),
															),
											   ),
								   "tax_status"=>$woo->get_tax_status( ),
								   "tax_class"=>$woo->get_tax_class( )
								  ),
					"inventory"=>array
								(
									"manage_stock"=>$woo->managing_stock(),
									"quantity"=>$woo->get_stock_quantity( ),
									"stock_status"=>$woo->is_in_stock( ),
									"allow_backorder"=>$woo->backorders_allowed( ),
									"allow_backorder_require_notification"=>$woo->backorders_require_notification( ),
									"sold_individually"=>$this->get_sold_individually($id->ID)
								),
					"shipping"=>array
								 (
								 	"weight"=>array
											 (
											 	"has_weight"=>$woo->has_weight( ),
												"unit"=>get_option('woocommerce_weight_unit'),
												"value"=>get_post_meta($id->ID, '_weight',true )
											 ),
									"dimension"=>array
											 (
											 	"has_dimension"=>$woo->has_dimensions( ),
												"unit"=>get_option('woocommerce_dimension_unit'),
												"value_l"=>get_post_meta($id->ID, '_length',true ),
												"value_w"=>get_post_meta($id->ID, '_width',true ),
												"value_h"=>get_post_meta($id->ID, '_height',true )
											 ),
									"shipping_class"=>array
											 (
											 	"class_name"=>$woo->get_shipping_class( ),
												"class_id"=>$woo->get_shipping_class_id( )
											 ),
								 ),
					"linked_products"=>array
								(
									"upsells"=>$this->woo_linked_product($woo->get_upsells( )),
									"cross_sale"=>$woo->get_cross_sells( ),
									"grouped"=>$id->post_parent
								),
					"attributes"=>array
								(
									"has_attributes"=>$woo->has_attributes( ),
									"attributes"=>$this->get_product_attributes($woo->product_attributes,$id->ID)
								),
					"advanced"=>array
								(
								"purchase_note"=>get_post_meta($id->ID, '_purchase_note',true ),
								"menu_order"=>$id->menu_order,
								"comment_status"=>$id->comment_status
								),
					"ratings"=>array
								(
								"average_rating"=>$woo->get_average_rating( ),
								"rating_count"=>$woo->get_rating_count( )
								),
					"if_variants"=>$this->get_product_variants($id->ID),
					"if_group"=>$this->get_products_group($id->ID),
					"product_gallery"=>array
									   (
									   "featured_images"=>$featuredImage,
									   "other_images"=>$this->get_attachment($woo->get_gallery_attachment_ids( ))
									   ),
					"categories" =>$this->get_categories(woocommerce_get_product_terms($id->ID, 'product_cat', 'all'))
					
				);	
				
		return $array;
		
		
	}
	
	private function woo_linked_product($arys)
	{
		$new_array = array();
		
		foreach($arys as $id)
		{
			$woo = new WC_Product();
		$woo->id = $id;
			array_push($new_array,array
								(
									"ID"=>$id,
									"title"=>get_the_title($id),
									"featured_image"=>wp_get_attachment_url(get_post_thumbnail_id($id, 'full')),
									"product_type"=>$this->get_product_type($id),
									"if_variants"=>$this->get_product_variants($id),
									"if_group"=>$this->get_products_group($id),
									"pricing"=>array
											   (
											   "is_on_sale"=>$woo->is_on_sale( ),
											   "currency"=>get_woocommerce_currency_symbol(),
											   "regular_price"=>get_post_meta( $id, '_regular_price',true ),
											   "sale_price"=>get_post_meta($id, '_sale_price',true ),
											   "sale_start"=>array(
											   					"unixtime"=>get_post_meta($id, '_sale_price_dates_from',true ),
																"day"=>date("d",get_post_meta($id, '_sale_price_dates_from',true )),
																"month"=>date("m",get_post_meta($id, '_sale_price_dates_from',true )),
																"year"=>date("Y",get_post_meta($id, '_sale_price_dates_from',true )),
																"day_name"=>date("l",get_post_meta($id, '_sale_price_dates_from',true )),
																"fulldate"=>date("d/m/Y",get_post_meta($id, '_sale_price_dates_from',true )),
															),
												 "sale_end"=>array(
											   					"unixtime"=>get_post_meta($id, '_sale_price_dates_to',true ),
																"day"=>date("d",get_post_meta($id, '_sale_price_dates_to',true )),
																"month"=>date("m",get_post_meta($id, '_sale_price_dates_to',true )),
																"year"=>date("Y",get_post_meta($id, '_sale_price_dates_to',true )),
																"day_name"=>date("l",get_post_meta($id, '_sale_price_dates_to',true )),
																"fulldate"=>date("d/m/Y",get_post_meta($id, '_sale_price_dates_to',true )),
															),
											   ),
								)
			);
		}
		
		return $new_array;
	}
	
	public function get_products_group($id)
	{
		global $wpdb;
    	 $sql = "SELECT * from ".$wpdb->prefix."posts WHERE `post_parent` =  '".$id."' AND post_status='publish'";
   		 $getResult = $wpdb->get_results( $sql);
		 $ary = array();
		  foreach ( $getResult as $result ) 
		{
			array_push($ary,$this->jsonSetup($result));
		}
		
		$child_prices = array();
		foreach ( $ary as $child_id )
		{
			$child_prices[] = get_post_meta( $child_id["product_ID"], '_price', true );
		}
		$child_prices = array_unique( $child_prices );
		
		if ( ! empty( $child_prices ) ) {
			$min_price = min( $child_prices );
		} else {
			$min_price = '';
		}
		
		return array("min_price"=>array("currency"=>get_woocommerce_currency_symbol(),"price"=>$min_price),"group"=>$ary);
	}
	
	private function getVariantAttribute($id)
	{
		global $wpdb;
    	 $sql = "SELECT * from ".$wpdb->prefix."postmeta WHERE `post_id` =  '".$id."' AND  `meta_key` LIKE  '%attribute%'";
   		 $getResult = $wpdb->get_results( $sql);
		 $ary = array();
		  foreach ( $getResult as $result ) 
		{
			array_push($ary,array("key"=>$result->meta_key,"value"=>$result->meta_value));
		}
		return $ary;
	}
	
	private function get_product_variants($id)
	{
		global $wpdb;
    	 $sql = "SELECT * from ".$wpdb->prefix."posts WHERE `post_status` =  'publish' AND  `post_parent` ='".$id."'";
   		 $getResult = $wpdb->get_results( $sql);
		 $ary = array();
		 
		 
		 
		 
		 
		 foreach ( $getResult as $result ) 
		{
			$woo = new WC_Product($result);
		$woo->id = $result->ID;
		$woo->post = $result;
			
			if(wp_get_attachment_url(get_post_meta($result->ID, '_thumbnail_id',true )) == false)
			{
				$featured_image = "0";	
			}
			else
			{
				$featured_image = wp_get_attachment_url(get_post_meta($result->ID, '_thumbnail_id',true ));
			}
			
			
			array_push($ary,array
							(
							"product_ID"=>$result->ID,
							"post_title"=>$result->post_title,
							"featured_images"=>$featured_image,
							"SKU"=>get_post_meta($result->ID, '_sku',true ),
							"is_downloadble"=>$woo->is_downloadable( ),
							"is_virtual"=>$woo->is_virtual(),
							"product_attribute"=>$this->getVariantAttribute($result->ID),
							"inventory"=>array
								(
								
									"quantity"=>get_post_meta($result->ID, '_stock',true ),
									
								),
							"pricing"=>array
											   (
											   "is_on_sale"=>$woo->is_on_sale( ),
											   "currency"=>get_woocommerce_currency_symbol(),
											   "regular_price"=>get_post_meta( $result->ID, '_regular_price',true ),
											   "sale_price"=>get_post_meta($result->ID, '_sale_price',true ),
											   "sale_start"=>array(
											   					"unixtime"=>get_post_meta($result->ID, '_sale_price_dates_from',true ),
																"day"=>date("d",get_post_meta($result->ID, '_sale_price_dates_from',true )),
																"month"=>date("m",get_post_meta($result->ID, '_sale_price_dates_from',true )),
																"year"=>date("Y",get_post_meta($result->ID, '_sale_price_dates_from',true )),
																"day_name"=>date("l",get_post_meta($result->ID, '_sale_price_dates_from',true )),
																"fulldate"=>date("d/m/Y",get_post_meta($result->ID, '_sale_price_dates_from',true )),
															),
												 "sale_end"=>array(
											   					"unixtime"=>get_post_meta($result->ID, '_sale_price_dates_to',true ),
																"day"=>date("d",get_post_meta($result->ID, '_sale_price_dates_to',true )),
																"month"=>date("m",get_post_meta($result->ID, '_sale_price_dates_to',true )),
																"year"=>date("Y",get_post_meta($result->ID, '_sale_price_dates_to',true )),
																"day_name"=>date("l",get_post_meta($result->ID, '_sale_price_dates_to',true )),
																"fulldate"=>date("d/m/Y",get_post_meta($result->ID, '_sale_price_dates_to',true )),
															),
												   "tax_status"=>$woo->get_tax_status( ),
						     						"tax_class"=>$woo->get_tax_class( )
											   ),
								"shipping"=>array
												 (
													"weight"=>array
															 (
																"has_weight"=>$woo->has_weight( ),
																"unit"=>get_option('woocommerce_weight_unit'),
																"value"=>get_post_meta($result->ID, '_weight',true )
															 ),
													"dimension"=>array
															 (
																"has_dimension"=>$woo->has_dimensions( ),
																"unit"=>get_option('woocommerce_dimension_unit'),
																"value_l"=>get_post_meta($result->ID, '_length',true ),
																"value_w"=>get_post_meta($result->ID, '_width',true ),
																"value_h"=>get_post_meta($result->ID, '_height',true )
															 ),
													"shipping_class"=>array
															 (
																"class_name"=>$woo->get_shipping_class( ),
																"class_id"=>$woo->get_shipping_class_id( )
															 ),
												 )
							
							));
			
		}
		
		$child_prices = array();
		foreach ( $ary as $child_id )
		{
			if(get_post_meta( $child_id["product_ID"], '_price', true ) !== "")
			{
			$child_prices[] = get_post_meta( $child_id["product_ID"], '_price', true );
			}
		}
		$child_prices = array_unique( $child_prices );
		
		if ( ! empty( $child_prices ) ) {
			$min_price = min( $child_prices );
		} else {
			$min_price = '';
		}
		
		return array("min_price"=>array("currency"=>get_woocommerce_currency_symbol(),"price"=>$min_price),"variables"=>$ary);
	}
	
	private function get_attachment($ary)
	{
		$sa = array();
		foreach($ary as $id)
		{
			
			array_push($sa,wp_get_attachment_url($id));
		}
		
		return $sa;
	}
	
	private function get_categories($arrays)
	{
		$se = array();
		foreach(array_keys($arrays) as $key){
			
			array_push($se,$arrays[$key]);
		}
		
		return $se;
	}
	
	private function get_product_attributes($array,$id)
	{
		$se = array();
		foreach(array_keys($array) as $key){
   		  
		  list($type, $name) =  split("_",$array[$key]["name"]);
		  
		  
		  if($this->check_in_woocommerce_attribute_taxonomy($name) == true)
		  {
			  array_push($se,array(
			  				"type"=>"select",
		 					"name"=>$array[$key]["name"],
							"slug"=>$name,
							"label"=>$this->get_woocommerce_attribute_taxonomy_label($name),
							"value"=>$this->get_attribute_type($id,$array[$key]["name"]),
							"position"=>$array[$key]["position"],
							"is_visible"=>$array[$key]["is_visible"],
							"is_variation"=>$array[$key]["is_variation"],
							"is_taxonomy"=>$array[$key]["is_taxonomy"]));
		  }
		  else
		  {
			  array_push($se,array(
			  				"custom"=>"custom",
		 					"name"=>$array[$key]["name"],
							"slug"=>$array[$key]["name"],
							"label"=>$array[$key]["name"],
							"value"=>$array[$key]["value"],
							"position"=>$array[$key]["position"],
							"is_visible"=>$array[$key]["is_visible"],
							"is_variation"=>$array[$key]["is_variation"],
							"is_taxonomy"=>$array[$key]["is_taxonomy"]));
			  
		  }
		  
		 
		}
		
		return $se;
		
	}
	
	private function get_woocommerce_attribute_taxonomy_label($name)
	{
		 global $wpdb;
    	 $sql = "SELECT * from ".$wpdb->prefix."woocommerce_attribute_taxonomies WHERE attribute_name = '".$name."'";
   		 $getResult = $wpdb->get_results( $sql);
		 $count = 0;
		 
		 
		 foreach ( $getResult as $result ) 
		{
			return $result->attribute_label;
		}
	}
	
	private function check_in_woocommerce_attribute_taxonomy($name)
	{
		 global $wpdb;
    	 $sql = "SELECT * from ".$wpdb->prefix."woocommerce_attribute_taxonomies WHERE attribute_name = '".$name."'";
   		 $getResult = $wpdb->get_results( $sql);
		 $count = 0;
		 
		 
		if($getResult)
		{
			return true;
		}
		else
		{
			return false;	
		}
	}
	
	private function get_sold_individually($id)
	{
		if(get_post_meta($id, '_sold_individually',true ) == "yes")
		{
			return true;	
		}
		else
		{
			return false;	
		}
		
	}
	
    public function get_product_type($id)
    {
	 global $wpdb;
    $sql = "SELECT * from ".$wpdb->prefix."term_relationships WHERE object_id = '".$id."'";
    $getResult = $wpdb->get_results( $sql);
	
	foreach ( $getResult as $result ) 
	{
		if($this->get_product_taxonomy_product_type($result->term_taxonomy_id) == false)
		{
			
		}
		else
		{
		$get_term_id = $this->get_product_taxonomy_product_type($result->term_taxonomy_id);
		}
		
	}
	
	
	return $this->get_term($get_term_id);
	  
  }
  
    private function get_attribute_type($id,$constant_value)
    {
	 global $wpdb;
    $sql = "SELECT * from ".$wpdb->prefix."term_relationships WHERE object_id = '".$id."'";
    $getResult = $wpdb->get_results( $sql);
	$se = array();
	foreach ( $getResult as $result ) 
	{
		if($this->get_product_taxonomy_attribute_value($result->term_taxonomy_id,$constant_value) == false)
		{
			
		}
		else
		{
		
		array_push($se,array("id"=>$this->get_product_taxonomy_attribute_value($result->term_taxonomy_id,$constant_value),"term"=>$this->get_term($this->get_product_taxonomy_attribute_value($result->term_taxonomy_id,$constant_value))));
		}
		
	}
	
	
	return $se ;
	  
  }
  
    private function get_product_taxonomy_product_type($term_taxonomy_id)
    {
	 global $wpdb;
    $sql = "SELECT * from ".$wpdb->prefix."term_taxonomy WHERE term_taxonomy_id = '".$term_taxonomy_id."'";
    $getResult = $wpdb->get_results( $sql);
	$as = 0;
	foreach ( $getResult as $result ) 
	{
			if($result->taxonomy == "product_type")
			{
				
				$as = $result->term_id;
			}
			
	}
	
	if($as == 0)
	{
		return false;	
	}
	else
	{
		return $as;
	}
	  
  }
  
    private function get_product_taxonomy_attribute_value($term_taxonomy_id,$constant_val)
    {
	 global $wpdb;
    $sql = "SELECT * from ".$wpdb->prefix."term_taxonomy WHERE term_taxonomy_id = '".$term_taxonomy_id."'";
    $getResult = $wpdb->get_results( $sql);
	
	foreach ( $getResult as $result ) 
	{
			if($result->taxonomy == $constant_val)
			{
				
				
				$as = $result->term_id;
			}
			
	}
	
	if($as == 0 || !$as)
	{
		return false;	
	}
	else
	{
		return $as;
	}
	  
  }
  
    private function get_term($term_id)
    {
	  global $wpdb;
    $sql = "SELECT * from ".$wpdb->prefix."terms WHERE term_id = '".$term_id."'";
    $getResult = $wpdb->get_results( $sql);
	
	foreach ( $getResult as $result ) 
	{
		
		return $result->name;
	}
	  
  }
  
    private function time_elapsed_string($ptime) 
    {
    $etime = time() - $ptime;
    
    if ($etime < 1) {
        return '0 seconds';
    }
    
    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
                );
    
    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's ago' : ' ago');
        }
    }
	
	}
  
    //@private Return User Array
    private function user_login_bool($userlogin,$userPassword)
    {
		
			$creds = array();
			
			if($this->check_email_address($userlogin) == true)
			{
				$user = get_user_by( 'email', $userlogin );
				$username = $user->user_login;	
			}
			else
			{
				$username = $userlogin;
			}
			
			
			$creds['user_login'] = $username;
			$creds['user_password'] = $userPassword;
			$creds['remember'] = true;
			$user = wp_signon( $creds, true );
			if ( is_wp_error($user) )
			{
			  return false;
			}
			else
			{
				
				return $user;
			}
			
			
			
			
	}
  
    //Comment API
    public function get_comment_by_post_ID($postID,$parent,$username,$password)
    {
	  $se = array();
	  
	  $arg = array(
	  				"post_id"=>$postID,
					"order" => "DESC",
					'parent' => $parent,
					'status' => 'approve'
	  			  );
	  
		$comments = get_comments($arg);
foreach($comments as $comment) :
$vote = get_comment_meta ( $comment->comment_ID, 'rating', true );


	array_push($se,
		array(
			"comment_id"=>(int)$comment->comment_ID,
			"status"=>$comment->comment_approved,
			"author"=>array(
							"avatar"=>$this->wp_user_avatar_extension($comment->user_id,$comment),
							"author_id"=>(int)$comment->user_id,
							"author_name"=>$comment->comment_author
							),
			"date"=>$comment->comment_date,
			"rating"=>(float)$vote,
			"comment_author_IP"=>$comment->comment_author_IP,
			"unixtime"=>strtotime( $comment->comment_date ),
			"servertime"=>(int)date("U"),
			"ago"=>$this->time_elapsed_string(strtotime( $comment->comment_date )),
			"parent"=>$comment->comment_parent,
			"agent"=>$comment->comment_agent,
			"content"=>strip_tags($comment->comment_content),
			"childs"=>$this->get_comment_by_post_ID($postID,$comment->comment_ID,$username,$password)
			
		)
	);
endforeach;
		if($parent == 0)
		{
		return array(
					"postID"=>(int)$postID,
					"comments"=>$se 
					);
		}
		else
		{
			$comment = get_comment( $parent);
			$vote = get_comment_meta ( $comment->comment_ID, 'rating', true );
			$user = $this->user_login_bool($username,$password);
			if($user == false)
			{
			return array(
					"postID"=>(int)$postID,
					"main_comment"=>array(
									"comment_id"=>(int)$comment->comment_ID,
									"status"=>$comment->comment_approved,
									"followed"=>"unfollowed",
									"author"=>array(
													"avatar"=>$this->wp_user_avatar_extension($comment->user_id,$comment),
													"author_id"=>(int)$comment->user_id,
													"author_name"=>$comment->comment_author
													),
									"date"=>$comment->comment_date,
									"rating"=>(float)$vote,
									"comment_author_IP"=>$comment->comment_author_IP,
									"unixtime"=>strtotime( $comment->comment_date ),
									"servertime"=>(int)date("U"),
									"ago"=>$this->time_elapsed_string(strtotime( $comment->comment_date )),
									"parent"=>$comment->comment_parent,
									"agent"=>$comment->comment_agent,
									"content"=>strip_tags($comment->comment_content)
									
								),
					"comments"=>$se 
					);
			}
			else
			{
				
				if($this->check_user_comment_followed($user->ID,$comment->comment_ID) == true)
				{
					$follow = "followed";	
				}
				else
				{
					$follow = "unfollowed";
				}
				
				return array(
					"postID"=>(int)$postID,
					"main_comment"=>array(
									"comment_id"=>(int)$comment->comment_ID,
									"status"=>$comment->comment_approved,
									"followed"=>$follow,
									"author"=>array(
													"avatar"=>$this->wp_user_avatar_extension($comment->user_id,$comment),
													"author_id"=>(int)$comment->user_id,
													"author_name"=>$comment->comment_author
													),
									"date"=>$comment->comment_date,
									"rating"=>(float)$vote,
									"comment_author_IP"=>$comment->comment_author_IP,
									"unixtime"=>strtotime( $comment->comment_date ),
									"servertime"=>(int)date("U"),
									"ago"=>$this->time_elapsed_string(strtotime( $comment->comment_date )),
									"parent"=>$comment->comment_parent,
									"agent"=>$comment->comment_agent,
									"content"=>strip_tags($comment->comment_content)
									
								),
					"comments"=>$se 
					);
				
			}
		}
	}
  
    public function get_comment_by_id($commentID)
    {
	  $comment = get_comment($commentID);
	  
	  return $comment;
	  
  }
  
    private function check_user_comment_followed($wpuserID,$commentID)
    {
	  global $wpdb;
        $sql = "SELECT * FROM `".$wpdb->prefix."amazingcart_user_comment_followed` WHERE wpuserID='".$wpuserID."' AND commentID='".$commentID."'";
		$results = $wpdb->get_results($sql);
		
		if($results)
		{
			return true;	//followed
			
		}
		else
		{
			
			return false; //unfollowed
		}
		
	  
  }
  
    //@return user image link
    // comment will activate if userID = 0 ONLY
    // Need to install WP User Avatar plugin
    private function wp_user_avatar_extension($userID,$comment=0)
    {
	 
			
				
				$user_avatar_id = get_user_meta( $userID, "wp_user_avatar", true );
				$get_user = get_user_by("id",$userID );
				
					$s	= wp_get_attachment_url($user_avatar_id);
					if($s == false)
					{
						$avatar	= $this->get_avatar_url(get_avatar( $userID , 100));
					}
					else
					{
						$avatar	= wp_get_attachment_url($user_avatar_id);
					}
					
				
			
			
			return $avatar;
  }
  
    private function get_avatar_url($get_avatar){
    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
    return $matches[1];
}

    private function check_email_address($email) {
        // First, we check that there's one @ symbol, and that the lengths are right
        if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
            // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
            return false;
        }
        // Split it into sections to make life easier
        $email_array = explode("@", $email);
        $local_array = explode(".", $email_array[0]);
        for ($i = 0; $i < sizeof($local_array); $i++) {
            if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
                return false;
            }
        }
        if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
            $domain_array = explode(".", $email_array[1]);
            if (sizeof($domain_array) < 2) {
                return false; // Not enough parts to domain
            }
            for ($i = 0; $i < sizeof($domain_array); $i++) {
                if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
                    return false;
                }
            }
        }

        return true;
    }
	
	/*
	COUNTRIES API
	*/
	//@private return country->state
    private function getStatesByCountry($countryCode)
	{
		global $woocommerce,$current_user;
		$wc_countries = new WC_Countries;
		
		$countries = array();
		foreach($wc_countries->states[$countryCode] as $key => $value)
		{
			array_push($countries,array(
									"state_code"=>$key,
									"state"=>$value	
									));
		}
		
		return $countries;
	}
	
	private function countryCodeToNameConverter($countryCode){
		
		$wc_countries = new WC_Countries;
		
		
		foreach($wc_countries->countries as $key => $value)
		{
			
			if($countryCode == $key)
			{
			$country = $value;
			break;
			}
			else
			{
				$country = "";
			}
		}
		
		return $country;
	}
	
	private function stateCodeToNameConverter($countryCode,$stateCode){
		
		$wc_countries = new WC_Countries;
		
		
		
			
				foreach($wc_countries->states[$countryCode] as $key => $value)
				{
					
					if($key == $stateCode)
					{
						$state = $value;
						break;
					}
					else
					{
						$state = false;
					}
					
					
				}
			
		
		
		return $state;
	}
	
	/**
	 * Get requests from users
	 */ 
	private function compile(){
		
                  if($_GET['type'] == "product-categories")
				  {
					echo json_encode( $this->get_product_categories($_GET['parent']));
				  }
				  else if($_GET['type'] == "product-by-category-id")
				  {
					echo json_encode( $this->getProductsByCategoryID($_GET['id'],$_GET['page'],$_GET['products-per-page']));
				  }
				  else if($_GET['type'] == "comment-by-post-id")
				  {
					echo json_encode($this->get_comment_by_post_ID($_GET['id'],$_GET['parent'],$_POST['username'],$_POST['password']));
				  }
				  else if($_GET['type'] == "single-product")
				  {
					echo json_encode($this->get_single_product($_GET['id']));  
				  }
				  else if($_GET['type'] == "search-product")
				  {
					echo json_encode($this->getProductsByKeyWord($_GET['keyword'],$_GET['page'],$_GET['products-per-page']));  
				  }
				  else if($_GET['type'] == "countries")
				  {
						echo json_encode($this->getWCCountries());  
				  }
				  else if($_GET['type'] == "user-login")
				  {
					  if($_POST)
					  {
						echo json_encode($this->user_login($_POST['username'],$_POST['password'],$_POST['deviceID']));  
					  }
					  else
					  {
						echo json_encode(array("status"=>"-1","reason"=>"No Input"));  
					  }
					  
				  }
				   else if($_GET['type'] == "user-logout")
				  {
					  if($_POST)
					  {
						$this->update_is_login("0",$_POST['deviceID']);  
						echo json_encode(array("status"=>"1","reason"=>"Successful")); 
					  }
					  else
					  {
						echo json_encode(array("status"=>"-1","reason"=>"No Input"));  
					  }
					  
				  }
				  else if($_GET['type'] == "user-profile-update")
				  {
					  if($_POST)
					  {
						  $data['first_name'] = $_POST['first_name'];
						  $data['last_name'] = $_POST['last_name'];
						  $data['display_name'] = $_POST['display_name'];
						  $data['email'] = $_POST['email'];
						echo json_encode($this->update_profile($_POST['username'],$_POST['password'],$data));  
					  }
					  else
					  {
						echo json_encode(array("status"=>"-1","reason"=>"No Input"));  
					  }
					  
				  }
				  else if($_GET['type'] == "user-billing-update")
				  {
					  if($_POST)
					  {
						  $data['billing_first_name'] = $_POST['billing_first_name'];
						  $data['billing_last_name'] = $_POST['billing_last_name'];
						  $data['billing_company'] = $_POST['billing_company'];
						  $data['billing_address_1'] = $_POST['billing_address_1'];
						  $data['billing_address_2'] = $_POST['billing_address_2'];
						  $data['billing_city'] = $_POST['billing_city'];
						  $data['billing_postcode'] = $_POST['billing_postcode'];
						  $data['billing_state'] = $_POST['billing_state'];
						  $data['billing_country'] = $_POST['billing_country'];
					      $data['billing_phone'] = $_POST['billing_phone'];
						  $data['billing_email'] = $_POST['billing_email'];
						  echo json_encode($this->update_billing($_POST['username'],$_POST['password'],$data));  
					  }
					  else
					  {
						echo json_encode(array("status"=>"-1","reason"=>"No Input"));  
					  }
					  
				  }
				  else if($_GET['type'] == "user-shipping-update")
				  {
					  if($_POST)
					  {
						  $data['shipping_first_name'] = $_POST['shipping_first_name'];
						  $data['shipping_last_name'] = $_POST['shipping_last_name'];
						  $data['shipping_company'] = $_POST['shipping_company'];
						  $data['shipping_address_1'] = $_POST['shipping_address_1'];
						  $data['shipping_address_2'] = $_POST['shipping_address_2'];
						  $data['shipping_city'] = $_POST['shipping_city'];
						  $data['shipping_postcode'] = $_POST['shipping_postcode'];
						  $data['shipping_state'] = $_POST['shipping_state'];
						  $data['shipping_country'] = $_POST['shipping_country'];
						  
						  
						echo json_encode($this->update_shipping($_POST['username'],$_POST['password'],$data));  
					  }
					  else
					  {
						echo json_encode(array("status"=>"-1","reason"=>"No Input"));  
					  }
					  
				  }
				   else if($_GET['type'] == "user-profile-image-update")
				  {
					   echo json_encode($this->upload_profile_image_wp_avatar($_POST['username'],$_POST['password'],$_FILES['userfile']));  
					  
				  }
				  else if($_GET['type'] == "user-post-comment")
				  {
					   if($_POST)
					  {
						echo json_encode($this->post_comment_api($_POST['username'],$_POST['password'],$_POST['comment'],$_POST['comment_parent'],$_POST['postID'],$_POST['starRating']));
					  }
					  else
					  {
						echo json_encode(array("status"=>"-1","reason"=>"No Input"));  
					  }
						  
				  }
				  else if($_GET['type'] == "cart-api")
				  {
						echo json_encode($this->cart_api($_POST['username'],$_POST['password'],$_POST['productIDJson'],$_POST['couponCodeJson']));  
				  }
				  else if($_GET['type'] == "place-an-order-api")
				  {
						echo json_encode($this->create_order_api($_POST['username'],$_POST['password'],$_POST['productIDJson'],$_POST['couponCodeJson'],$_POST['paymentMethodID'],$_POST['orderNotes']));  
				  
				  }
				  else if($_GET['type'] == "mobile-payment-redirect-api")
				  {
						echo $this->mobile_woo_pay_action_extension($_GET['orderKey'],$_GET['orderID'],$_GET['paymentMethodID'],$_POST);
				  
				  }
				  else if($_GET['type'] == "mobile-payment-redirect-authorize-dot-net-api")
				  {
						echo $this->mobile_woo_pay_action_extension_authorize_dot_net($_GET['orderKey'],$_GET['orderID'],$_GET['paymentMethodID'],$_POST);
				  
				  }
				   else if($_GET['type'] == "get-settings")
				  {
						echo json_encode($this->getSettings()); 
				
				  }
				  else if($_GET['type'] == "get-order")
				  {
						echo json_encode($this->getSingleOrder($_POST['username'],$_POST['password'],$_POST['orderID'])); 
				  
				  }
				  else if($_GET['type'] == "get-featured-product")
				  {
					  
					  echo json_encode($this->getFeaturedProducts());
					  
				  }
				  else if($_GET['type'] == "get-recent-items")
				  {
					  
						echo json_encode($this->getAllProducts());  
						
						
				  }
				   else if($_GET['type'] == "get-random-items")
				  {
					  
						echo json_encode($this->getRandomProduct());  
						
				  }
				  else if($_GET['type'] == "get-my-order")
				  {
					  if($_POST)
					  {
 						  echo json_encode($this->getMyOrder($_POST['username'],$_POST['password'],$_POST['filter']));  
					  }
					  else
					  {
						  echo json_encode(array("status"=>"-1","reason"=>"No Input"));  
					  }
						 
				  }
				  else if($_GET['type'] == "user-registration")
				  {
					   if($_POST)
					  {
					  echo json_encode($this->userRegistration($_POST['username'],$_POST['email'],$_POST['first_name'],$_POST['last_name'],$_POST['password'],$_POST['deviceID']));
					  }
					  else
					  {
							echo json_encode(array("status"=>"-1","reason"=>"No Input"));  
					  }
				  }
				  else if($_GET['type']=="change-password")
				  {
					if($_POST)
					  {
					  echo json_encode($this->change_password($_POST['username'],$_POST['currentpassword'],$_POST['newpassword']));
					  }
					  else
					  {
							echo json_encode(array("status"=>"-1","reason"=>"No Input"));  
					  }
				  }	
				  else if($_GET['type'] == 'get-comment-by-id')
				  {
					  if(!$_GET['commentID'])
					  {
						  echo json_encode(array("status"=>"-1","reason"=>"No Input")); 
					  }
					  else
					  {
						  echo json_encode($this->get_comment_by_id($_GET['commentID']));  
					  }
				  }
				  else if($_GET['type'] == 'get-single-payment-gateway-meta')
				  {
					echo json_encode($this->check_single_payment_gateway_meta_key($_GET['key']));  
				  }
		
	}
}
?>