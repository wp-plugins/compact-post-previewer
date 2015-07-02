<?php 
	/**
	* Plugin Main Class
	*/
	class LA_Post_Viewer
	{
		
		function __construct()
		{
			add_action( "admin_menu", array($this,'post_viewer_admin_options'));
			add_action( 'admin_enqueue_scripts', array($this,'admin_enqueuing_scripts'));
			add_action('wp_ajax_la_save_post_viewer', array($this, 'save_admin_options'));
			add_action('wp_ajax_la_get_terms', array($this, 'get_terms'));
			add_shortcode( 'compact-post-previewer', array($this, 'render_post_view') );
			add_filter('widget_text', 'do_shortcode');
		}
	

		function post_viewer_admin_options(){
			add_menu_page( 'Compact Post Previewer', 'Compact Post Previewer', 'manage_options', 'post_viewer', array($this,'post_previewer_menu_page'), 'dashicons-format-image', $position );
		}

		function admin_enqueuing_scripts($slug){
		if ($slug == 'toplevel_page_post_viewer') {
			wp_enqueue_media();
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'post-viewer-admin-js', plugins_url( 'admin/admin.js' , __FILE__ ), array('jquery', 'jquery-ui-accordion', 'wp-color-picker') );
			wp_enqueue_style( 'post-viewer-admin-css', plugins_url( 'admin/style.css' , __FILE__ ));
			wp_localize_script( 'post-viewer-admin-js', 'laAjax', array( 'url' => admin_url( 'admin-ajax.php' ),'path' => plugin_dir_url( __FILE__ )));
			}
		}

		function get_terms(){
			extract($_REQUEST);
			$terms = get_terms( $_REQUEST['tax']);
			if (empty($terms) || $_REQUEST['tax'] == '') {
				echo __( 'Sorry! this Taxonomy has no Terms.', 'la-postviewer' );
			} else {
				echo '<select class="la-term widefat">';
				foreach ($terms as $key => $value) {
					echo '<option value="'.$value->term_id.'">'.$value->name.'('.$value->count.')</option>';
				}
				echo '</select>';
			}
			die(0);
		}

		function post_previewer_menu_page(){
			$saved_posts = get_option('la_post_viewer');
			?>
			<div class="wrap" id="compactviewer">
				<h1>Compact Post Previewer</h1>
				<hr>
				<div id="accordion">
					<?php if (isset($saved_posts['posts'])) { ?>
					<?php foreach ($saved_posts['posts'] as $key => $data) {
					 ?>
						<h3 class="tab-head"><?php echo $data['pvTitle']; ?></h3>
						<div class="tab-content">
							<h2>General Settings</h2>
							<table class="form-table">
								<hr>
								
								<tr>
									<td>
										<strong>Select Taxonomy</strong>
									</td>
									<td>
										<select name="post_catrgory"  class="widefat post-cat">
										<option value=""><?php echo esc_attr(__('Select Taxonomy')); ?></option> 
											<?php 
											
												$taxonomies = get_taxonomies();
												foreach ($taxonomies as $taxonomy) { 
													echo '<option value="'.$taxonomy.'" '.selected( $data['postCat'],  $taxonomy ).'>'. $taxonomy .'</option>';
												}
											 ?>	
										</select>
									</td>
									<td>
										<p class="description"><?php _e( 'Select Taxonomy', 'la-postviewer' ); ?>.</p>
									</td>
									
								</tr>

								<tr>
									<td>
										<strong><?php _e( 'Select Term', 'la-postviewer' ); ?></strong>
									</td>
									<td class="get-terms">
										<?php if ($data['postCat'] != '') { ?>

				  						<select class="la-term widefat"> 
										 <option value=""><?php echo esc_attr(__('Select Term')); ?></option> 
										 <?php 
										  $terms = get_terms($data['postCat']); 
										  foreach ($terms as $term) { 
										  	$option = '<option value="'.$term->term_id.'" '.selected( $data['term'], $term->term_id ).'>';
											$option .= $term->name;
											$option .= ' ('.$term->count.')';
											$option .= '</option>';
											echo $option;
										  }
										 ?>
										</select>			  						
			  							
				  						<?php } else { ?>
				  							<p class="description"><?php _e( 'Please select any taxonmy first', 'la-postviewer' ); ?>.</p>
				  						<?php } ?>
										
									</td>

									<td>
										<p class="description"><?php _e( 'Select Term whose posts will be shown in Post Viewer', 'la-postviewer' ); ?>.</p>
									</td>
								</tr>


				  				<tr>
				  					<td> <strong> <?php _e( 'Exclude Posts', 'la-postviewer' ); ?> </strong></td>
				  					<td>
				  						<input type="text" class="exclude-ids widefat" value="<?php echo $data['exclude_ids']; ?>">
				  					</td>
				  					<td>
				  						<p class="description"><?php _e( 'Comma separated ids of posts that you do not want to display', 'la-postviewer' ); ?>.</p>
				  					</td>
				  				</tr>
							</table>
							<h2><?php _e( 'Post Viewer Settings', 'la-postviewer' ); ?></h2>
							<hr>
							<table class="form-table">
								<tr>
									<td>
										<strong ><?php _e( 'Post Viewer Name', 'la-postviewer' ); ?></strong>
									</td>
									<td>
										<input type="text" class="pvtitle widefat" value="<?php echo $data['pvTitle']; ?>">
									</td>
									<td>
										<p class="description"><?php _e( 'Give name to Post Viewer for your own reference.', 'la-postviewer' ); ?></p>
									</td>
								</tr>

								<tr>
				  					<td>
				  						<strong ><?php _e( 'Background Color', 'la-postviewer' ); ?></strong>
				  					</td>
				  					<td class="insert-picker">
				  						<input type="text" class="my-colorpicker" value="<?php echo $data['color_val']; ?>">
				  					</td>
				  					<td>
				  						<p class="description"><?php _e( 'It is background color for Post Previewer', 'la-postviewer' ); ?>.</p>
				  					</td>
			  					</tr>

								<tr>
									<td>
										<strong ><?php _e( 'Read more Text', 'la-postviewer' ); ?></strong>
									</td>
									<td>
										<input type="text" class="btntext widefat" value="<?php echo $data['btntitle']; ?>">
									</td>
									<td>
										<p class="description"><?php _e( 'Text to be shown for the read more button', 'la-postviewer' ); ?></p> 
									</td>
								</tr>

								<tr>
									<td><strong>Post Previewer Width</strong></td>
									<td>
										<input type="number" class="widefat pvwidth" value="<?php echo $data['pvWidth'] ?>">
									</td>
									<td>
										<p class="description"><?php _e( 'Define width of Post Previewer', 'la-postviewer' ); ?></p>
									</td>
								</tr>
							</table>
							<div class="clearfix"></div>
							<hr style="margin-bottom: 10px;">
							<button class="button btnadd"><span title="Add New" class="dashicons dashicons-plus-alt"></span><?php _e( 'Add New', 'la-postviewer' ); ?></button>&nbsp;
							<button class="button btndelete"><span class="dashicons dashicons-dismiss" title="Delete"></span><?php _e( 'Delete', 'la-postviewer' ); ?></button>
							<button class="button-primary fullshortcode pull-right" id="<?php echo $data['counter']; ?>"><?php _e( 'Get Shortcode', 'la-postviewer' ); ?></button>
							
						</div>
						<?php } ?>
						<?php } else { ?>
						<h3 class="tab-head">Post Viewer</h3>
						<div class="tab-content">
							<h2>General Settings</h2>
							<table class="form-table">
								<hr>
								
								<tr>
									<td>
										<strong><?php echo esc_attr(__('Select Taxonomy')); ?></strong>
									</td>
									<td>
										<select name="post_catrgory"  class="widefat post-cat">
										<option value=""><?php echo esc_attr(__('Select Taxonomy')); ?></option> 
											<?php 
											
												$taxonomies = get_taxonomies();
												foreach ($taxonomies as $taxonomy) { 
													echo '<option value="'.$taxonomy.'" '.selected( $data['postCat'],  $taxonomy ).'>'. $taxonomy .'</option>';
												}
											 ?>	
										</select>
									</td>
									<td>
										<p class="description"><?php _e( 'Select Taxonomy', 'la-postviewer' ); ?>.</p>
									</td>
									
								</tr>

								<tr>
									<td>
										<strong><?php _e( 'Select Term', 'la-postviewer' ); ?></strong>
									</td>
									<td class="get-terms">
										<p class="description"><?php _e( 'Please select any taxonomy first', 'la-postviewer' ); ?>.</p>		
									</td>

									<td>
										<p class="description"><?php _e( 'Select Term whose posts will be shown in Post Viewer', 'la-postviewer' ); ?>.</p>
									</td>
								</tr>


				  				<tr>
				  					<td> <strong> <?php _e( 'Exclude Posts', 'la-postviewer' ); ?> </strong></td>
				  					<td>
				  						<input type="text" class="exclude-ids widefat" value="<?php echo $data['exclude_ids']; ?>">
				  					</td>
				  					<td>
				  						<p class="description"><?php _e( 'Comma separated ids of posts that you do not want to display', 'la-postviewer' ); ?>.</p>
				  					</td>
				  				</tr>
							</table>
							<h2><?php _e( 'Post Viewer Settings', 'la-postviewer' ); ?></h2>
							<hr>
							<table class="form-table">
								<tr>
									<td>
										<strong ><?php _e( 'Post Viewer Name', 'la-postviewer' ); ?></strong>
									</td>
									<td>
										<input type="text" class="pvtitle widefat" value="<?php echo $data['pvTitle']; ?>">
									</td>
									<td>
										<p class="description"><?php _e( 'Give name to Post Viewer for your own reference.', 'la-postviewer' ); ?></p>
									</td>
								</tr>

								<tr>
				  					<td>
				  						<strong><?php _e( 'Background Color', 'la-postviewer' ); ?></strong>
				  					</td>
				  					<td class="insert-picker">
				  						<input type="text" class="my-colorpicker" value="#bada55">
				  					</td>
				  					<td>
				  						<p class="description"><?php _e( 'It is background color for Post Previewer', 'la-postviewer' ); ?>.</p>
				  					</td>
			  					</tr>

								<tr>
									<td>
										<strong ><?php _e( 'Read more Text', 'la-postviewer' ); ?></strong>
									</td>
									<td>
										<input type="text" class="btntext widefat" value="">
									</td>
									<td>
										<p class="description"><?php _e( 'Text to be shown for the read more button', 'la-postviewer' ); ?></p> 
									</td>
								</tr>

								<tr>
									<td><strong>Post Previewer Width</strong></td>
									<td>
										<input type="number" class="widefat pvwidht" value="">
									</td>
									<td>
										<p class="description"><?php _e( 'Define width of Post Previewer', 'la-postviewer' ); ?></p>
									</td>
								</tr>
							</table>
							<div class="clearfix"></div>
							<hr style="margin-bottom: 10px;">
							<button class="button btnadd"><span title="Add New" class="dashicons dashicons-plus-alt"></span><?php _e( 'Add New', 'la-postviewer' ); ?></button>&nbsp;
							<button class="button btndelete"><span class="dashicons dashicons-dismiss" title="Delete"></span><?php _e( 'Delete', 'la-postviewer' ); ?></button>
							<button class="button-primary fullshortcode pull-right" id="1"><?php _e( 'Get Shortcode', 'la-postviewer' ); ?></button>
							
						</div>
						<?php } ?>
						</div>
							<hr style="margin-top: 20px;">
							<button class="button-primary save-meta" ><?php _e( 'Save Settings', 'la-postviewer' ); ?></button>
							<span id="la-loader"><img src="<?php echo plugin_dir_url( __FILE__ ); ?>images/7.gif"></span>
							<span id="la-saved"><strong><?php _e( 'Changes Saved', 'la-postviewer' ); ?>!</strong></span>
						</div>
						</div>
						
					</div>
			
			<?php
		}

		function save_admin_options(){
			print_r($_REQUEST);
			if (isset($_REQUEST)) {
				update_option( 'la_post_viewer', $_REQUEST );
			}
			die(0);
		}

		function render_post_view($atts, $content, $the_shortcode){
			$saved_posts = get_option('la_post_viewer');
			if (isset($saved_posts['posts'])) {
				foreach ($saved_posts['posts'] as $key => $data) {
					if ($atts['id']== $data['counter']) {
						// print_r($saved_posts);

						wp_enqueue_style( 'post-viewer-css', plugins_url( 'css/style.css' , __FILE__ ),true);
						wp_enqueue_script( 'cufon-js', plugins_url( 'js/cufon-yui.js' , __FILE__ ));
						wp_enqueue_script( 'bebas-js', plugins_url( 'js/Bebas_400.font.js' , __FILE__ ));
						wp_enqueue_script( 'easing-js', plugins_url( 'js/jquery.easing.1.3.js' , __FILE__ ), array('jquery') );
						wp_enqueue_script( 'custom-js', plugins_url( 'js/custom.js' , __FILE__ ), array('jquery','easing-js') );

						$exclude_ids = $data['exclude_ids'];
						$exclude_ids_arr = explode(",",$exclude_ids);
						$args = array(
							'posts_per_page' 	=> -1,
							'post__not_in'		=> $exclude_ids_arr,
							'tax_query' 		=> array(
								array(
									'taxonomy'         => $data['postCat'],
									'terms'            => array( $data['term'] ),
									'include_children' => true,
								),
							),
						);
						$postContents = '<style>.cn_wrapper{background-color:'.$data['color_val'].';width:'.$data['pvWidth'].'px;}</style>';
						$postContents .= '<div class="cn_wrapper">';
						$postContents .= '<div id="cn_preview" class="cn_preview">';
						$query1 = new WP_Query( $args );

						// The Loop
						
						if ( $query1->have_posts() ) {
							while ( $query1->have_posts() ) {
								$query1->the_post();
							$postContents .= '<div class="cn_content">';
							if ( has_post_thumbnail() ) {
									$post_thumbnail = get_the_post_thumbnail(  );
								} else {
									$post_thumbnail = '<img src="'.plugin_dir_url( __FILE__ ).'images/placeholder.png">';
								}
								$postContents .= $post_thumbnail;
								$postContents .= "<h1>".get_the_title()."</h1>";
								$postContents .= "<span class='cn_date'>".get_the_time('F j, Y' )."</span>";
								$postContents .= "<span class='cn_category'>".get_the_category_list( ',')."</span>";
								
								
								$postContents .= "<p>".$myExcerpt = get_the_excerpt();
													  $tags = array("<p>", "</p>");
													  $myExcerpt = str_replace($tags, "", $myExcerpt);
													  $myExcerpt."</p>";
								$postContents .= '<a href="'.get_the_permalink().'" target="_blank" class="cn_more">'.$data['btntitle'].'</a>';

						$postContents .= '</div>';
							}

						} else {
							$postContents = "<div class='cn_wrapper'><h1>404 - No Posts Found!</h1></div>";
						}
						// /* Restore original Post Data */
						wp_reset_postdata();

						$postContents .= '</div>';
						$postContents .= '<div id="cn_list" class="cn_list">';
						$postContents .= '<div class="cn_page" style="display:block;">';

							$args = array(
							'posts_per_page' 	=> -1,
							'post__not_in'		=> $exclude_ids_arr,
							'tax_query' 		=> array(
								array(
									'taxonomy'         => $data['postCat'],
									'terms'            => array( $data['term'] ),
									'include_children' => true,
								),
							),
						);
						$query1 = new WP_Query( $args );

						$counter=1;
						
							while ( $query1->have_posts() ) {
								$query1->the_post();
							$postContents .= '<div class="cn_item">';
							
								$postContents .= "<h2>".get_the_title()."</h2>";
								
								
								$postContents .= "<p>".$myExcerpt = get_the_excerpt();
													  $tags = array("<p>", "</p>");
													  $myExcerpt = str_replace($tags, "", $myExcerpt);
													  $myExcerpt."</p>";

						$postContents .= '</div>';

						if ($counter % 4 == 0) {
							$postContents .= '</div><div class="cn_page">';
						}
						$counter++;
							}
						wp_reset_postdata();
						$postContents .= '</div>';
						$postContents .= '<div class="cn_nav">
											<a id="cn_prev" class="cn_prev disabled"></a>
											<a id="cn_next" class="cn_next"></a>
										   </div>';
						$postContents .= '</div>';
						return $postContents;
					
					}
				}
			}

			?>

			<?php	
		}
	}
 ?>