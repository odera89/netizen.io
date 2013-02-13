<?php get_header(); ?>
<?php global $post; ?>
	
			
		<div id="content" class="clearfix row">

			<div class="row clearfix">
      
        <div class="span3">
        
          <div class="well">
          			  
  			   <p><b>Threat Vector</b> is a tool for tracking threats to the open, innovative web.</p>
  			   
  			   <p>These threats may come from governments, or they may come private companies or other sources.
  			   </p>
  			   <p>
  			     Click through the threats listed here or <a href="<?php bloginfo('siteurl');?>/wp-admin/post-new.php?post_type=threat">add a new one</a>.
  			   </p>
  			   </div>

<ul class="nav nav-list">
  <li class="nav-header">
    List header
  </li>
  <li class="active">
    <a href="#">Home</a>
  </li>
  <li>
    <a href="#">Library</a>
  </li>
  ...
</ul>
  			   
        </div>

			 <div class="span9">
			 
			 <h2>Active threats <a class="btn pull-right" style="font-weight:normal" href="<?php bloginfo('siteurl');?>/wp-admin/post-new.php?post_type=threat">+ Add a threat</a></h2>
			 <p></p>
  			  
  		<?php /* ?>
	 <table id="threats"class="table table-striped">
  			   <?php 
                $args = array( 
                  'numberposts' => 1000, 
                  'post_type' => 'threat', 
                  'order' => 'desc',
                  'orderby' => 'meta_value_num',
                  'meta_key' => 'score'
                  );
                $myposts = get_posts( $args );
                foreach( $myposts as $key => $post ) :	setup_postdata($post); 
                $abbr = types_render_field("abbreviation", array());
                if ($abbr != '') $abbr = '('.$abbr.')';
            ?>
      	       <tr>
                 <td>
                    <?php if ($key == 2): ?>
      	             <span class="label label-important"><i class="icon-fire icon-white"></i></span> 
      	           <?php endif; ?>
                 </td>
      	         <td>
        	         <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?> <?php echo $abbr; ?>
</a></h3></td>
                 <td><?php DisplayVotes(get_the_ID()); ?></td>  
                 <td>
                 <?php if ($key == 2): ?>
      	             <i class="icon-user"></i> 348 
      	         <?php endif; ?>
                 </td>

      	       </tr>
                  
            <?php endforeach; ?>
          </table>
<?php */ ?>


	<div class="row">
 	 <?php 
                $args = array( 
                  'numberposts' => 1000, 
                  'post_type' => 'threat', 
                  'order' => 'desc',
                  'orderby' => 'meta_value_num',
                  'meta_key' => 'score'
                  );
                $myposts = get_posts( $args );
                foreach( $myposts as $key => $post ) :	setup_postdata($post); 
                $abbr = types_render_field("abbreviation", array());
                if ($abbr != '') $abbr = '('.$abbr.')';
            ?>

	<div class="span3"><?php the_title(); ?></div>

<?php endforeach; ?>
</div>

        </div>	
        <? /*
        <div class="span6" >
    			   <h2>Links
    			   <a class="btn" style="font-weight:normal" href="<?php bloginfo('siteurl');?>/wp-admin/post-new.php?post_type=article">+ Add a link</a>
    			   </h2>
    			   <p></p>

            <table class="table table-striped">
    			   <?php 
                  $args = array( 'numberposts' => 5, 'post_type' => 'article' );
                  $myposts = get_posts( $args );
                  foreach( $myposts as $key => $post ) :	setup_postdata($post); 
              ?>
        	       <tr>
        	         <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
        	         <td><?php if( function_exists('LIKEBOT_BUTTON') ) { LIKEBOT_BUTTON(); } ?></td>
        	         <td style="width: 60px"><span class="label label-info">4 <i class="icon-comment icon-white"></i></span></td>  
  
        	       </tr>
                    
              <?php endforeach; ?>
            </table>    			 
          </div>	
          */?>
			</div><!-- /.row -->
			
			<?php /*
					<div class="row-fluid clearfix">
      
        <div class="span3">
&nbsp;
  			   
        </div>

			 <div class="span9">
			 
			 <h2>Links 	<small class="pull-right">Submit an link by <a href="http://delicious.com/tag/recent/threatvector">tagging it "threatvector" in Delicious</a>.</small></h2>

			 <p></p>
			 
			 <?php
        include_once(ABSPATH . WPINC . '/feed.php');
        // Get a SimplePie feed object from the specified feed source.
        $rss = fetch_feed('http://feeds.delicious.com/v2/rss/tag/threatvector');
        if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
            // Figure out how many total items there are, but limit it to 5. 
            $maxitems = $rss->get_item_quantity(10); 
        
            // Build an array of all the items, starting with element 0 (first element).
            $rss_items = $rss->get_items(0, $maxitems); 
        endif;
        ?> 
        
  			 <table class="table table-striped">
            <?php 
            // Loop through each feed item and display each item as a hyperlink.
            foreach ( $rss_items as $item ) : 
            
            $clean_url = esc_url( $item->get_permalink() );
            
            $pieces = explode('http://', $clean_url);
            $pieces = $pieces[1];
            $pieces = explode('/', $pieces);
            $source = $pieces[0];
            
            if (strpos($source, 'www.')) {
                $pieces = explode('www.', $source);
                $source = $pieces[1];
            }
            
            ?>
            <tr><td>
                <a class="newsitem" href='<?php echo $clean_url;  ?>'
                title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
                <?php echo esc_html( $item->get_title() ); ?></a>
                <br />
                <span class="meta">
                  <?php echo $source; ?><br />
                <?php echo $item->get_date('F j Y | g:i a'); ?></span>
                <a class="btn btn-mini" href="#" onclick="alert('soon, this will add this to the site'); return false;">add</a>
                </td>
              </tr>
            <?php endforeach; ?>
        </table>

        </div>	
			</div><!-- /.row -->			
			
			*/?>		
					
					<?php /*	
			<div id="" class="row-fluid clearfix">
			
				<div id="" class="span3 clearfix" role="">
				<h3>Topics</h3>
				[categories]
				</div>
				<div class="span3 clearfix">
  				<h3>People</h3>
  				<ul>
            <?php 
              $args = array( 'numberposts' => 5, 'post_type' => 'person' );
              $myposts = get_posts( $args );
              foreach( $myposts as $post ) :	setup_postdata($post); 
            ?>
    	       <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
            <?php endforeach; ?>
          </ul>
				</div>
				
				<div id="" class="span3 clearfix" role="">
  				<h3>Places</h3>
  				<ul>
            <?php 
              $args = array( 'numberposts' => 5, 'post_type' => 'place' );
              $myposts = get_posts( $args );
              foreach( $myposts as $post ) :	setup_postdata($post); 
            ?>
    	       <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
            <?php endforeach; ?>
          </ul>
				</div>
				
				<div id="" class="span3 clearfix" role="">
				<h3>Organizations</h3>
				<ul>
          <?php 
            $args = array( 'numberposts' => 5, 'post_type' => 'organization' );
            $myposts = get_posts( $args );
            foreach( $myposts as $post ) :	setup_postdata($post); 
          ?>
  	       <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
          <?php endforeach; ?>
        </ul>
				</div>
				
		</div>
		*/?>
		      
			</div> <!-- end #content -->

<?php get_footer(); ?>