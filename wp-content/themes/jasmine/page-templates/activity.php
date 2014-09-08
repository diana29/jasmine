<?php
/**
 * Template Name: Activity Page
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

get_header(); ?>

<div id="main-content" class="main-content">

 

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

    Filter by :
    <div>
    	<b> Component: </b><select id="filter-by-component">
    	<option value="">All</option>
    	<?php
    		$components = ajan_activity_get_components() ;
    		foreach($components as $component){
    			?>
    			<option value="<?php echo $component; ?>"><?php echo $component; ?></option>
    			<?php
    		}
    	?>
    	</select>
    </div>
    Or
	<!--<div>
    	<b> Type: </b><select id="filter-by-type">
    	<option value="">All</option>
    	<?php
    		$types = ajan_activity_get_types() ;
    		 
    		foreach($types as $type_key =>$type_value){
    			?>
    			<option value="<?php echo $type_key; ?>"><?php echo $type_value; ?></option>
    			<?php
    		}
    	?>
    	</select>
    </div>-->
	<div id="whats-new-avatar">
		<a href="http://localhost/ginger/members/admin/">
			<img src="http://gravatar.com/avatar/3708079636a4cfe09a16b2cab4e6ce74?d=mm&amp;s=50&amp;r=G" class="avatar user-1-avatar avatar-50 photo" width="50" height="50" alt="Profile picture of admin">		</a>
	</div>
	
	<p class="activity-greeting">What's new?</p>

	<div id="whats-new-content">
		<div id="whats-new-textarea">
			<textarea name="whats-new" id="whats-new" cols="50" rows="10" style="height: 20px;"></textarea>
		</div>

		<div  style="height: 40px;">
			<div  >
				<input type="submit" name="aw-whats-new-submit" id="add-activity" value="Post Update" >
			</div>

			 
			
			
		</div><!-- #whats-new-options -->
	</div><!-- #whats-new-content -->
 	<article id="post-2" class="post-2 page type-page status-publish hentry">
				<header class="entry-header"><h1 class="entry-title">Activities</h1></header><!-- .entry-header -->
				<div class="entry-content">
					 <ul id="activity-stream" class="activity-stream">
					 </ul>
				</div><!-- .entry-content -->
			</article>

			  
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
