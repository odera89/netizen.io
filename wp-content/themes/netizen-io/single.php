<?php get_header(); ?>
			
			<div id="content" class="clearfix">
			


					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					
				  <div class="page-header">
				    <h1 class="single-title" itemprop="headline">
				    
				    <?php the_title(); ?></h1>
				  </div>
				    <p>Posted <?php the_date(''); ?> by <?php the_author_posts_link(); ?> 							<?php 
							// only show edit button if user has permission to edit posts
							if( $user_level > 0 ) { 
							?>
							<a href="<?php echo get_edit_post_link(); ?>" class="btn btn-mini pull-right"><i class="icon-pencil"></i> <?php _e("Edit description","bonestheme"); ?></a>
							<?php } ?></p>
						<p>&nbsp;</p>
						
						<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">
						
						<header>
						
							<?php the_post_thumbnail( 'wpbs-featured' ); ?>
							
						
						</header> <!-- end article header -->
					
						<section class="post_content clearfix" itemprop="articleBody">
							<?php the_content(); ?>
							
							<?php wp_link_pages(); ?>
					
						</section> <!-- end article section -->
						
						<footer>
			
							<?php the_tags('<p class="tags"><span class="tags-title">' . __("Tags","bonestheme") . ':</span> ', ' ', '</p>'); ?>
							
							
						</footer> <!-- end article footer -->
					
					</article> <!-- end article -->
					
					<hr />
					
				<?php
				// Find connected pages
				$connected = new WP_Query( array(
				  'connected_type' => 'post_to_threat',
				  'connected_items' => get_queried_object(),
				  'nopaging' => true,
				) );
				
				// Display connected pages
				if ( $connected->have_posts() ) :
				?>
				<h3>Articles:</h3>
				<ul>
				<?php while ( $connected->have_posts() ) : $connected->the_post(); ?>
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> (submitted by <?php the_author(); ?>)</li>
				<?php endwhile; ?>
				</ul>
				
				<?php 
				// Prevent weirdness
				wp_reset_postdata();
				
				endif;
				?>
				
				<hr />
				  

				  <div class="row-fluid">
					
					<div class="span7">
									
					
					
					<?php comments_template(); ?>
					
					</div><!-- /.span6 -->
					
					<?php endwhile; ?>			
					<?php endif; ?>

				<div id="news" class="span5">
             
          <?php if (get_post_meta($post->ID, 'wpcf-google_news_query', true)):
                   $query = get_post_meta($post->ID, 'wpcf-google_news_query', true);
                  cio_display_feed("https://news.google.com/news/feeds?hl=en&gl=us&q=$query&um=1&ie=UTF-8&output=rss", 10, $query, 'Google News', "https://www.google.com/search?hl=en&gl=us&tbm=nws&q=$query&oq=trans+pa&aq=1&aqi=d1g2d1&aql=&gs_l=news-cc.3.1.43j0l2j43i400.731.1760.0.2983.10.6.0.0.0.0.250.855.2j2j2.6.0...0.0.xEVhOVZ7k-E"); 
                endif;         ?>   
							
				  <?php if (get_post_meta($post->ID, 'wpcf-delicious_tag', true)):
				          $query = get_post_meta($post->ID, 'wpcf-delicious_tag', true);
				          cio_display_feed('http://feeds.delicious.com/v2/rss/tag/'. get_post_meta($post->ID, 'wpcf-delicious_tag', true), 10, $query, 'Delicious', 'http://delicious.com/tag/recent/'. get_post_meta($post->ID, 'wpcf-delicious_tag', true));
				  		  endif; ?>
   
				</div>
				</div><!-- end .row -->
    
				<?php //get_sidebar(); // sidebar 1 ?>
    
			</div> <!-- end #content -->

<?php get_footer(); ?>